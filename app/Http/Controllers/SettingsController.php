<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Settings\SettingsManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * SettingsManager instance.
     *
     * @var SettingsManager
     */
    protected SettingsManager $settingsManager;

    /**
     * SettingsController constructor.
     *
     * @param SettingsManager $settingsManager
     */
    public function __construct(SettingsManager $settingsManager)
    {
        $this->settingsManager = $settingsManager;
    }

    /**
     * Display organization settings dashboard.
     */
    public function index(): View
    {
        return view('settings.index');
    }

    /**
     * Update settings keys.
     */
    public function update(Request $request): RedirectResponse
    {
        $group = $request->input('group', 'general');
        $settings = $request->except(['_token', '_method', 'group']);

        foreach ($settings as $key => $value) {
            $this->settingsManager->set($key, $value, $group);
        }

        return back()->with('success', 'Organization settings updated successfully.');
    }
}
