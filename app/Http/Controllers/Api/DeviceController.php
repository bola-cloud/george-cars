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
        ]);

        return response()->json([
            'message' => 'Device created',
            'status' => true,
            'data' => $device,
        ], 200);
    }
}
