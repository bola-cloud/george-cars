<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'status' => false,
                'data' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'status' => true,
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    public function login(Request $request)
    {
        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login details',
                'status' => false,
                'data' => null,
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'status' => true,
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }


    /**
     * Return the authenticated user with devices.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        // own devices
        $ownDevices = $user->devices()->get()->map(function ($d) {
            $arr = $d->toArray();
            $arr['shared'] = false;
            $arr['shared_owner_id'] = null;
            $arr['shared_owner_name'] = null;
            return $arr;
        })->values();

        // devices shared to this user by owners
        // fetch owner->meta mapping from user_shares for this authenticated user
        $ownerMeta = UserShare::where('user_id', $user->id)->get()->keyBy('owner_id');

        $sharedDevicesQuery = $user->sharedDevices();
        $sharedDevices = [];
        if ($sharedDevicesQuery) {
            $sharedDevicesCollection = $sharedDevicesQuery->with('user')->get();

            // collect device ids and query device_shares for this authenticated user in one query
            $deviceIds = $sharedDevicesCollection->pluck('id')->toArray();
            $deviceShareMap = [];
            if (! empty($deviceIds)) {
                $deviceShares = \App\Models\DeviceShare::whereIn('device_id', $deviceIds)
                    ->where('user_id', $user->id)
                    ->get()
                    ->keyBy('device_id');
                $deviceShareMap = $deviceShares->toArray();
            }

            $sharedDevices = $sharedDevicesCollection->map(function ($d) use ($ownerMeta, $deviceShareMap) {
                $arr = $d->toArray();
                $arr['shared'] = true;
                $arr['shared_owner_id'] = $d->user ? $d->user->id : null;
                $arr['shared_owner_name'] = $d->user ? $d->user->name : null;

                // prefer device-level meta if present, otherwise fallback to owner->user meta
                $shareMeta = null;
                if (isset($deviceShareMap[$d->id]) && isset($deviceShareMap[$d->id]['meta'])) {
                    $shareMeta = $deviceShareMap[$d->id]['meta'];
                } else {
                    if ($d->user && isset($ownerMeta[$d->user->id])) {
                        $shareMeta = $ownerMeta[$d->user->id]->meta;
                    }
                }

                $arr['share_meta'] = $shareMeta;
                return $arr;
            })->values();
        }

        $devices = $ownDevices->concat($sharedDevices)->values();

        return response()->json([
            'message' => 'User retrieved',
            'status' => true,
            'data' => [
                'user' => $user,
                'devices' => $devices,
            ],
        ], 200);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out',
            'status' => true,
            'data' => null,
        ], 200);
    }

    /**
     * Update authenticated user data.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:255',
            'onesignal' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'status' => false,
                'data' => $validator->errors(),
            ], 422);
        }

        $data = $request->only(['name', 'email', 'phone', 'onesignal']);

        // If email is being updated, you may want to reset verification; left as-is for now.
        $user->update($data);

        return response()->json([
            'message' => 'User updated',
            'status' => true,
            'data' => [
                'user' => $user->load('devices'),
            ],
        ], 200);
    }
}
