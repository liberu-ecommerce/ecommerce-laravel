<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SiteSettingController extends Controller
{
    /** Site settings are store-wide config — staff only. */
    private function ensureAdmin(): void
    {
        abort_unless(auth()->user()?->hasRole(['super_admin', 'admin']), 403);
    }

    public function index()
    {
        $this->ensureAdmin();

        $settings = SiteSetting::all();

        return response()->json($settings);
    }

    public function edit($id)
    {
        $this->ensureAdmin();

        $setting = SiteSetting::findOrFail($id);

        return response()->json($setting);
    }

    public function update(Request $request, $id)
    {
        $this->ensureAdmin();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:site_settings,name,'.$id,
            'value' => 'required|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $setting = SiteSetting::findOrFail($id);
        $setting->update($request->all());

        return response()->json($setting);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:site_settings',
            'value' => 'required|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $setting = SiteSetting::create($request->all());

        return response()->json($setting, 201);
    }
}
