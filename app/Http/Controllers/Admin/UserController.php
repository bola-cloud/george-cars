<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(25);
        return response()->json(['message' => 'Users list','status' => true,'data' => $users], 200);
    }

    public function show($id)
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['message' => 'User not found','status' => false,'data' => null], 404);
        }
        return response()->json(['message' => 'User retrieved','status' => true,'data' => $user], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string',
            'is_admin' => 'nullable|boolean',
            'onesignal' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation errors','status' => false,'data' => $validator->errors()], 422);
        }

        $data = $request->only(['name','email','phone','is_admin','onesignal']);
        $data['password'] = Hash::make($request->password);
        $user = User::create($data);

        return response()->json(['message' => 'User created','status' => true,'data' => $user], 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['message' => 'User not found','status' => false,'data' => null], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,'.$id,
            'password' => 'sometimes|nullable|string|min:8',
            'phone' => 'nullable|string',
            'is_admin' => 'nullable|boolean',
            'onesignal' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation errors','status' => false,'data' => $validator->errors()], 422);
        }

        $data = $request->only(['name','email','phone','is_admin','onesignal']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json(['message' => 'User updated','status' => true,'data' => $user], 200);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['message' => 'User not found','status' => false,'data' => null], 404);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted','status' => true,'data' => null], 200);
    }
}
