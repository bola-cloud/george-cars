<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class WebUserController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $users = User::when($q, function ($query) use ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        })->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string',
            'is_admin' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'onesignal' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only(['name','email','phone','onesignal']);
        $data['is_admin'] = $request->has('is_admin');
        $data['is_active'] = $request->has('is_active');
        $data['password'] = Hash::make($request->password);
        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'User created');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function show($id)
    {
        $user = User::with('devices')->findOrFail($id);

        $userShares = \App\Models\UserShare::where('owner_id', $id)->with('user')->get();
        $deviceShares = \App\Models\DeviceShare::whereIn('device_id', $user->devices->pluck('id'))->with(['user', 'device'])->get();

        return view('admin.users.show', compact('user', 'userShares', 'deviceShares'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,'.$id,
            'password' => 'sometimes|nullable|string|min:8',
            'phone' => 'nullable|string',
            'is_admin' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'onesignal' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only(['name','email','phone','onesignal']);
        
        // Handle boolean fields
        $data['is_admin'] = $request->has('is_admin');
        $data['is_active'] = $request->has('is_active');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User updated');
    }

    public function toggleActive($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();

        if (!$user->is_active) {
            // Revoke all tokens so the user is logged out everywhere immediately
            $user->tokens()->delete();
        }

        $status = $user->is_active ? 'activated' : 'suspended';
        return redirect()->back()->with('success', "User account {$status} successfully.");
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted');
    }
}
