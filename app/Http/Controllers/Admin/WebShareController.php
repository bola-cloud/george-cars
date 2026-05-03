<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserShare;
use App\Models\DeviceShare;
use Illuminate\Http\Request;

class WebShareController extends Controller
{
    public function updateUserShare(Request $request, $id)
    {
        $share = UserShare::findOrFail($id);

        $request->validate([
            'permissions' => 'required|array',
        ]);

        $meta = $share->meta ?? [];
        $meta['permissions'] = [];

        foreach ($request->permissions as $key => $val) {
            $meta['permissions'][$key] = filter_var($val, FILTER_VALIDATE_BOOLEAN);
        }

        $share->update(['meta' => $meta]);

        return redirect()->back()->with('success', 'User Share permissions updated.');
    }

    public function destroyUserShare($id)
    {
        UserShare::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'User Share revoked.');
    }

    public function updateDeviceShare(Request $request, $id)
    {
        $share = DeviceShare::findOrFail($id);

        $request->validate([
            'permissions' => 'required|array',
        ]);

        $meta = $share->meta ?? [];
        $meta['permissions'] = [];

        foreach ($request->permissions as $key => $val) {
            $meta['permissions'][$key] = filter_var($val, FILTER_VALIDATE_BOOLEAN);
        }

        $share->update(['meta' => $meta]);

        return redirect()->back()->with('success', 'Device Share permissions updated.');
    }

    public function destroyDeviceShare($id)
    {
        DeviceShare::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Device Share revoked.');
    }
}
