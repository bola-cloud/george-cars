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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
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

        $device = $request->user()->devices()->create([
            'name' => $request->name,
            'serial' => $request->serial,
            'meta' => $request->meta,
            'ip' => $request->input('ip'),
        ]);

        return response()->json([
            'message' => 'Device created',
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
