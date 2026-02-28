<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SystemSettingsController extends Controller
{
    /**
     * Ensure only Normal Admin can access System Settings.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (!$user || !$user->isNormalAdmin()) {
                abort(403, 'Unauthorized. Normal Admin access required.');
            }
            return $next($request);
        });
    }

    /**
     * Display System Settings form.
     * GET /admin/system-settings
     */
    public function index()
    {
        $settings = SystemSetting::getMany([
            'app_name',
            'app_logo',
            'app_favicon',
            'support_email',
            'maintenance_message',
        ]);

        return view('admin.system-settings.index', compact('settings'));
    }

    /**
     * Update System Settings.
     * POST /admin/system-settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'nullable|string|max:255',
            'support_email' => 'nullable|email|max:255',
            'maintenance_message' => 'nullable|string|max:1000',
        ]);

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                SystemSetting::set($key, $value);
            }
        }

        return redirect()
            ->route('admin.system-settings.index')
            ->with('success', 'System settings updated successfully.');
    }

    /**
     * Upload app logo.
     * POST /admin/system-settings/logo
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo.' . $file->getClientOriginalExtension();
            
            // Delete old logo if exists
            $oldLogo = SystemSetting::get('app_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            // Store new logo
            $path = $file->storeAs('system', $filename, 'public');
            
            SystemSetting::set('app_logo', $path);

            return redirect()
                ->route('admin.system-settings.index')
                ->with('success', 'Logo uploaded successfully.');
        }

        return redirect()
            ->route('admin.system-settings.index')
            ->with('error', 'No logo file provided.');
    }

    /**
     * Upload app favicon.
     * POST /admin/system-settings/favicon
     */
    public function uploadFavicon(Request $request)
    {
        $request->validate([
            'favicon' => 'required|image|mimes:ico,png|max:512',
        ]);

        if ($request->hasFile('favicon')) {
            $file = $request->file('favicon');
            $filename = 'favicon.' . $file->getClientOriginalExtension();
            
            // Delete old favicon if exists
            $oldFavicon = SystemSetting::get('app_favicon');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }

            // Store new favicon
            $path = $file->storeAs('system', $filename, 'public');
            
            SystemSetting::set('app_favicon', $path);

            return redirect()
                ->route('admin.system-settings.index')
                ->with('success', 'Favicon uploaded successfully.');
        }

        return redirect()
            ->route('admin.system-settings.index')
            ->with('error', 'No favicon file provided.');
    }
}
