<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceShare;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceShareController extends Controller
{
    /**
     * Create or update device share for a user (idempotent).
     * Body: { device_id, user_id, meta }
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|exists:devices,id',
            'user_id' => 'required|exists:users,id',
            'meta' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation errors','status'=>false,'data'=>$validator->errors()], 422);
        }

        $device = Device::find($request->device_id);
        if (! $device) {
            return response()->json(['message' => 'Device not found','status'=>false,'data'=>null], 404);
        }

        // Only owner can create device-level shares for their device
        if ($request->user()->id !== $device->user_id) {
            return response()->json(['message' => 'Not authorized','status'=>false,'data'=>null], 403);
        }

        $share = DeviceShare::updateOrCreate(
            ['device_id' => $device->id, 'user_id' => $request->user_id],
            ['meta' => $request->input('meta')]
        );

        return response()->json(['message' => 'Device share saved','status'=>true,'data'=>$share], 200);
    }

    /**
     * Update a device_share record by id.
     */
    public function update(Request $request, $id)
    {
        $share = DeviceShare::find($id);
        if (! $share) {
            return response()->json(['message' => 'Share not found','status'=>false,'data'=>null], 404);
        }

        // Only device owner can update
        if ($request->user()->id !== $share->device->user_id) {
            return response()->json(['message' => 'Not authorized','status'=>false,'data'=>null], 403);
        }

        $validator = Validator::make($request->all(), [
            'meta' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation errors','status'=>false,'data'=>$validator->errors()], 422);
        }

        if ($request->has('meta')) {
            $share->meta = $request->input('meta');
        }
        $share->save();

        return response()->json(['message' => 'Device share updated','status'=>true,'data'=>$share], 200);
    }

    /**
     * Remove a device share by id.
     */
    public function destroy(Request $request, $id)
    {
        $share = DeviceShare::find($id);
        if (! $share) {
            return response()->json(['message' => 'Share not found','status'=>false,'data'=>null], 404);
        }

        if ($request->user()->id !== $share->device->user_id) {
            return response()->json(['message' => 'Not authorized','status'=>false,'data'=>null], 403);
        }

        $share->delete();
        return response()->json(['message' => 'Device share removed','status'=>true,'data'=>null], 200);
    }
}
