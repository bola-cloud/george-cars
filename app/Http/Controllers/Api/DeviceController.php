<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceController extends Controller
{
    /**
     * Store a new device - no authentication required.
     * Accepts user_id in request to assign device to a user.
    */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'serial' => 'required|string|max:255',
            'ip' => 'required|ip',
            'name' => 'nullable|string|max:255',
            'user_id' => 'required|exists:users,id',
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

        if ($device->user_id && $device->user_id != $request->input('user_id')) {
            return response()->json([
                'message' => 'Device already claimed by another user',
                'status' => false,
                'data' => null,
            ], 409);
        }

        $device->user_id = $request->input('user_id');
        // Update name if provided
        if ($request->filled('name')) {
            $device->name = $request->input('name');
        }
        // Use provided ip
        $device->ip = $request->input('ip');
        $device->save();

        return response()->json([
            'message' => 'Device claimed',
            'status' => true,
            'data' => $device,
        ], 200);
    }

    /**
     * Generate a unique 14-character alphanumeric serial.
     * Public endpoint (no auth required).
     */
    public function generateSerial()
    {
        try {
            $serial = \App\Models\Device::generateUniqueSerial(14);
            return response()->json(['serial' => $serial], 200);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => 'Unable to generate unique serial'], 500);
        }
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

    /**
     * Delete a device that belongs to the authenticated user.
     */
    public function destroy(Request $request, $id)
    {
        $device = $request->user()->devices()->where('id', $id)->first();

        if (! $device) {
            return response()->json([
                'message' => 'Device not found or not owned by user',
                'status' => false,
                'data' => null,
            ], 404);
        }

        // Instead of deleting the device, unassign it from the user, clear the IP and serial.
        $device->user_id = null;
        $device->ip = null;
        $device->serial = null;
        $device->save();

        return response()->json([
            'message' => 'Device unassigned from user',
            'status' => true,
            'data' => $device,
        ], 200);
    }
}
