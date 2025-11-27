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

    /**
     * Return a generated unique serial (JSON) for AJAX use.
     */
    public function generateSerial()
    {
        $serial = Device::generateUniqueSerial(14);
        return response()->json(['serial' => $serial], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'serial' => 'nullable|string|max:255|unique:devices,serial',
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
            'serial' => 'sometimes|nullable|string|max:255|unique:devices,serial,'.$id,
            'name' => 'nullable|string|max:255',
            'ip' => 'nullable|ip',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only(['serial','name','ip','user_id']);
        // Remove keys that are explicitly null so we don't attempt to set NOT NULL columns to null.
        foreach ($data as $k => $v) {
            if (is_null($v)) {
                unset($data[$k]);
            }
        }

        $device->update($data);

        return redirect()->route('admin.devices.index')->with('success', 'Device updated');
    }

    public function destroy($id)
    {
        $device = Device::findOrFail($id);
        $device->delete();
        return redirect()->route('admin.devices.index')->with('success', 'Device deleted');
    }
}
