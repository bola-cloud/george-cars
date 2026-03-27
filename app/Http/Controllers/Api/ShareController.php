<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShareController extends Controller
{
    /**
     * List users the authenticated user has shared with.
     */
    public function index(Request $request)
    {
        $owner = $request->user();

        // Get all users this owner has shared with
        $userShares = UserShare::where('owner_id', $owner->id)->with('user')->get();

        // Get devices owned by owner
        $devices = \App\Models\Device::where('user_id', $owner->id)->get();
        $deviceIds = $devices->pluck('id')->toArray();

        // Preload all device_shares for these devices and shared users
        $sharedUserIds = $userShares->pluck('user_id')->toArray();
        $deviceShares = [];
        if (! empty($deviceIds) && ! empty($sharedUserIds)) {
            $deviceShares = \App\Models\DeviceShare::whereIn('device_id', $deviceIds)
                ->whereIn('user_id', $sharedUserIds)
                ->get()
                ->groupBy('user_id');
        }

        // Build response: for each shared user, include devices with effective meta
        $result = [];
        foreach ($userShares as $us) {
            $row = [
                'user_id' => $us->user_id,
                'user' => $us->user,
                'devices' => [],
            ];

            // for each device owned by owner, compute effective meta for this shared user
            foreach ($devices as $dev) {
                $effectiveMeta = null;

                // device-specific meta if exists
                if (isset($deviceShares[$us->user_id])) {
                    $dsForUser = $deviceShares[$us->user_id]->keyBy('device_id');
                    if (isset($dsForUser[$dev->id])) {
                        $effectiveMeta = $dsForUser[$dev->id]->meta;
                    }
                }

                // fallback to owner-level share meta (applies to all devices unless overridden)
                if (is_null($effectiveMeta) && ! empty($us->meta)) {
                    $effectiveMeta = $us->meta;
                }

                // only include device if there is some meta/permission defined
                if (! is_null($effectiveMeta)) {
                    $row['devices'][] = [
                        'device_id' => $dev->id,
                        'serial' => $dev->serial,
                        'permissions' => $effectiveMeta['permissions'] ?? $effectiveMeta,
                    ];
                }
            }

            $result[] = $row;
        }

        return response()->json(['message' => 'Shared users', 'status' => true, 'data' => $result], 200);
    }

    /**
     * Share with an existing user by email or phone.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            // allow single or multiple targets
            'email' => 'nullable|email|required_without_all:phone,emails,phones',
            'phone' => 'nullable|string|required_without_all:email,emails,phones',
            'emails' => 'nullable|array',
            'emails.*' => 'email',
            'phones' => 'nullable|array',
            'phones.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation errors','status'=>false,'data'=>$validator->errors()], 422);
        }

        $targets = [];
        $notFound = [];
        $selfSkipped = [];

        // gather emails/phones from both singular and plural fields
        $emails = [];
        $phones = [];
        if ($request->filled('email')) { $emails[] = $request->email; }
        if ($request->filled('phone')) { $phones[] = $request->phone; }
        if (is_array($request->get('emails'))) { $emails = array_merge($emails, $request->get('emails')); }
        if (is_array($request->get('phones'))) { $phones = array_merge($phones, $request->get('phones')); }

        // dedupe
        $emails = array_values(array_unique(array_filter($emails)));
        $phones = array_values(array_unique(array_filter($phones)));

        // resolve by email
        foreach ($emails as $em) {
            $t = User::where('email', $em)->first();
            if (! $t) {
                $notFound[] = ['email' => $em];
                continue;
            }
            if ($t->id === $user->id) { $selfSkipped[] = $t->id; continue; }
            $targets[$t->id] = $t;
        }

        // resolve by phone
        foreach ($phones as $ph) {
            $t = User::where('phone', $ph)->first();
            if (! $t) {
                $notFound[] = ['phone' => $ph];
                continue;
            }
            if ($t->id === $user->id) { $selfSkipped[] = $t->id; continue; }
            $targets[$t->id] = $t;
        }

        if (count($targets) === 0) {
            return response()->json(['message' => 'No target users found','status'=>false,'data'=>['not_found'=>$notFound]], 404);
        }

        $attachIds = array_keys($targets);

        // allow a single `meta` to be applied to all targets in this request
        $meta = $request->get('meta');
        $attachMap = [];
        foreach ($attachIds as $aid) {
            $attachMap[$aid] = [];
            if (is_array($meta)) {
                $attachMap[$aid]['meta'] = json_encode($meta);
            }
        }

        // attach with pivot meta where provided
        $user->sharedUsers()->syncWithoutDetaching($attachMap);

        return response()->json([
            'message' => 'Users shared',
            'status' => true,
            'data' => [
                'shared' => array_values($targets),
                'not_found' => $notFound,
                'self_skipped' => array_values(array_unique($selfSkipped)),
            ],
        ], 201);
    }

    /**
     * Remove a shared user. $id is the child user id.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $exists = $user->sharedUsers()->where('users.id', $id)->exists();
        if (! $exists) {
            return response()->json(['message' => 'Share not found','status'=>false,'data'=>null], 404);
        }

        $user->sharedUsers()->detach($id);

        return response()->json(['message' => 'Share removed','status'=>true,'data'=>null], 200);
    }

    /**
     * Update a user_share record: meta and/or target user_id
     * {id} is the user_shares id
     */
    public function update(Request $request, $id)
    {
        $auth = $request->user();
        $share = UserShare::find($id);
        if (! $share) {
            return response()->json(['message' => 'Share not found','status'=>false,'data'=>null], 404);
        }

        // only owner can update
        if ($share->owner_id !== $auth->id) {
            return response()->json(['message' => 'Not authorized','status'=>false,'data'=>null], 403);
        }

        $validator = Validator::make($request->all(), [
            'meta' => 'nullable|array',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation errors','status'=>false,'data'=>$validator->errors()], 422);
        }

        if ($request->filled('user_id')) {
            $newUserId = $request->input('user_id');
            if ($newUserId == $auth->id) {
                return response()->json(['message' => 'Cannot share to owner','status'=>false,'data'=>null], 422);
            }
            // check unique constraint: owner_id + user_id
            $exists = UserShare::where('owner_id', $auth->id)->where('user_id', $newUserId)->where('id', '!=', $share->id)->exists();
            if ($exists) {
                return response()->json(['message' => 'Share already exists for this user','status'=>false,'data'=>null], 422);
            }
            $share->user_id = $newUserId;
        }

        if ($request->has('meta')) {
            $share->meta = $request->input('meta');
        }

        $share->save();

        return response()->json(['message' => 'Share updated','status'=>true,'data'=>$share->fresh()], 200);
    }
}
