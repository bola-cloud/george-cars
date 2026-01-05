<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShareController extends Controller
{
    /**
     * List users the authenticated user has shared with.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $shared = $user->sharedUsers()->paginate(20);

        return response()->json([ 'message' => 'Shared users', 'status' => true, 'data' => $shared ], 200);
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
        $user->sharedUsers()->syncWithoutDetaching($attachIds);

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
}
