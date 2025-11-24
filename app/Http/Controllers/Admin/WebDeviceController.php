<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WebDeviceController extends Controller
{
    public function index()
    {
        $devices = Device::with('user')->paginate(25);
        return view('admin.devices.index', compact('devices'));
    }

    public function create()
    {
        $users = User::all();
        return view('admin.devices.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'serial' => 'required|string|max:255|unique:devices,serial',
            'name' => 'nullable|string|max:255',
            'ip' => 'nullable|ip',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Device::create($request->only(['serial','name','ip','user_id']));

        return redirect()->route('admin.devices.index')->with('success', 'Device created');
    }

    public function edit($id)
    {
        $device = Device::findOrFail($id);
        $users = User::all();
        return view('admin.devices.edit', compact('device','users'));
    }

    public function update(Request $request, $id)
    {
        $device = Device::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'serial' => 'sometimes|required|string|max:255|unique:devices,serial,'.$id,
            'name' => 'nullable|string|max:255',
            'ip' => 'nullable|ip',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $device->update($request->only(['serial','name','ip','user_id']));

        return redirect()->route('admin.devices.index')->with('success', 'Device updated');
    }

    public function destroy($id)
    {
        $device = Device::findOrFail($id);
        $device->delete();
        return redirect()->route('admin.devices.index')->with('success', 'Device deleted');
    }
}
