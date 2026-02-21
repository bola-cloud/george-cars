<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Models\Device;
use App\Models\User;

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
        $device->save();

        return response()->json([
            'message' => 'Device unassigned from user',
            'status' => true,
            'data' => $device,
        ], 200);
    }

    /**
     * Notify all users who have access to this device (owner + shared users)
     * about a status change via OneSignal.
     *
     * Body: { status: string, title?: string, message?: string }
     */
    public function notifyChange(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
            'title' => 'nullable|string',
            'message' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation errors','status'=>false,'data'=>$validator->errors()], 422);
        }

        $device = Device::with('user')->find($id);
        if (! $device) {
            return response()->json(['message' => 'Device not found','status'=>false,'data'=>null], 404);
        }

        $authUser = $request->user();
        // permission: must be owner or be a shared user of the owner
        $owner = $device->user;
        if (! $owner) {
            return response()->json(['message' => 'Device has no owner','status'=>false,'data'=>null], 422);
        }

        $allowed = false;
        if ($owner->id === $authUser->id) {
            $allowed = true;
        } else {
            // check if owner has shared with auth user
            $allowed = $owner->sharedUsers()->where('users.id', $authUser->id)->exists();
        }

        if (! $allowed) {
            return response()->json(['message' => 'Not authorized to notify for this device','status'=>false,'data'=>null], 403);
        }

        // gather recipients: owner + owner's shared users
        $recipients = collect([$owner])->merge($owner->sharedUsers()->get())->unique('id')->values();

        // collect onesignal player ids
        $playerIds = [];
        foreach ($recipients as $r) {
            if (is_array($r->onesignal) && ! empty($r->onesignal['player_id'] ?? null)) {
                $playerIds[] = $r->onesignal['player_id'];
            }
        }

        $playerIds = array_values(array_unique($playerIds));
        if (empty($playerIds)) {
            return response()->json(['message' => 'No OneSignal player ids found for recipients','status'=>false,'data'=>null], 200);
        }

        $status = $request->input('status');
        $title = $request->input('title', "Device status changed");
        $message = $request->input('message', "Device {$device->name} status: {$status}");

        $appId = config('services.onesignal.app_id') ?: env('ONESIGNAL_APP_ID');
        $restKey = config('services.onesignal.rest_api_key') ?: env('ONESIGNAL_REST_API_KEY');

        if (! $appId || ! $restKey) {
            return response()->json(['message' => 'OneSignal not configured','status'=>false,'data'=>null], 500);
        }

        $payload = [
            'app_id' => $appId,
            'include_player_ids' => $playerIds,
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
            'data' => ['device_id' => $device->id, 'status' => $status],
        ];

        $response = Http::withHeaders([
            'Authorization' => "Basic {$restKey}",
            'Content-Type' => 'application/json',
        ])->post('https://onesignal.com/api/v1/notifications', $payload);

        if ($response->failed()) {
            return response()->json(['message' => 'Failed to send OneSignal notification','status'=>false,'data'=>$response->body()], 500);
        }

        return response()->json(['message' => 'Notifications sent','status'=>true,'data'=>$response->json()], 200);
    }
}
