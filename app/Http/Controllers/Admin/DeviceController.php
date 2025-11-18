<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::with('user')->paginate(25);
        return response()->json(['message'=>'Devices list','status'=>true,'data'=>$devices],200);
    }

    public function show($id)
    {
        $device = Device::with('user')->find($id);
        if (! $device) {
            return response()->json(['message'=>'Device not found','status'=>false,'data'=>null],404);
        }
        return response()->json(['message'=>'Device retrieved','status'=>true,'data'=>$device],200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'serial' => 'required|string|max:255|unique:devices,serial',
            'meta' => 'nullable|array',
            'ip' => 'nullable|ip',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message'=>'Validation errors','status'=>false,'data'=>$validator->errors()],422);
        }

        $device = Device::create($request->only(['name','serial','meta','ip','user_id']));
        return response()->json(['message'=>'Device created','status'=>true,'data'=>$device],201);
    }

    public function update(Request $request, $id)
    {
        $device = Device::find($id);
        if (! $device) {
            return response()->json(['message'=>'Device not found','status'=>false,'data'=>null],404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'serial' => 'sometimes|required|string|max:255|unique:devices,serial,'.$id,
            'meta' => 'nullable|array',
            'ip' => 'nullable|ip',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message'=>'Validation errors','status'=>false,'data'=>$validator->errors()],422);
        }

        $device->update($request->only(['name','serial','meta','ip','user_id']));
        return response()->json(['message'=>'Device updated','status'=>true,'data'=>$device],200);
    }

    public function destroy($id)
    {
        $device = Device::find($id);
        if (! $device) {
            return response()->json(['message'=>'Device not found','status'=>false,'data'=>null],404);
        }
        $device->delete();
        return response()->json(['message'=>'Device deleted','status'=>true,'data'=>null],200);
    }
}
