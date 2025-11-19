<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceController extends Controller
{
    /**
     * Store a new device for the authenticated user.
     */
    public function store(Request $request)
    {
        // Mobile claim mode: request must contain 'serial'. Optional: 'ip' and 'name' to update device on claim.
        $validator = Validator::make($request->all(), [
            'serial' => 'required|string|max:255',
            'ip' => 'required|ip',
            'name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'status' => false,
                'data' => $validator->errors(),
            ], 422);
        }

        $serial = $request->input('serial');

        $device = \App\Models\Device::where('serial', $serial)->first();

        if (! $device) {
            return response()->json([
                'message' => 'Device with provided serial not found',
                'status' => false,
                'data' => null,
            ], 404);
        }

        if ($device->user_id && $device->user_id != $request->user()->id) {
            return response()->json([
                'message' => 'Device already claimed',
                'status' => false,
                'data' => null,
            ], 409);
        }

        $device->user_id = $request->user()->id;
        // Update name if provided
        if ($request->filled('name')) {
            $device->name = $request->input('name');
        }
        // Use provided ip if present, otherwise use request IP
        $device->ip = $request->input('ip', $request->ip());
        $device->save();

        return response()->json([
            'message' => 'Device claimed',
            'status' => true,
            'data' => $device,
        ], 200);
    }

    /**
     * Update a device belonging to the authenticated user.
     */
    public function update(Request $request, $id)
    {
        $device = $request->user()->devices()->where('id', $id)->first();

        if (! $device) {
            return response()->json([
                'message' => 'Device not found or not owned by user',
                'status' => false,
                'data' => null,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'serial' => 'nullable|string|max:255',
            'meta' => 'nullable|array',
            'ip' => 'nullable|ip',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'status' => false,
                'data' => $validator->errors(),
            ], 422);
        }

        $device->fill($request->only(['name', 'serial', 'meta', 'ip']));
        $device->save();

        return response()->json([
            'message' => 'Device updated',
            'status' => true,
            'data' => $device,
        ], 200);
    }
}
