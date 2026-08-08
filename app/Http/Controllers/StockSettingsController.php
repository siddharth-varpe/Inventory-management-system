<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Settings\SettingsManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockSettingsController extends Controller
{
    /**
     * SettingsManager instance.
     *
     * @var SettingsManager
     */
    protected SettingsManager $settingsManager;

    /**
     * StockSettingsController constructor.
     *
     * @param SettingsManager $settingsManager
     */
    public function __construct(SettingsManager $settingsManager)
    {
        $this->settingsManager = $settingsManager;
    }

    /**
     * Display Manage Stock Portal Settings.
     */
    public function index(): View
    {
        $settings = [
            'default_reorder_level' => $this->settingsManager->get('default_reorder_level', '10', 'inventory'),
            'default_warehouse' => $this->settingsManager->get('default_warehouse', 'Main Warehouse', 'inventory'),
            'sku_prefix' => $this->settingsManager->get('sku_prefix', 'SKU-', 'inventory'),
            'sku_auto_generate' => $this->settingsManager->get('sku_auto_generate', '1', 'inventory'),
            'barcode_prefix' => $this->settingsManager->get('barcode_prefix', '890', 'inventory'),
            'barcode_type' => $this->settingsManager->get('barcode_type', 'CODE128', 'inventory'),
            'approval_threshold' => $this->settingsManager->get('approval_threshold', '50000', 'inventory'),
            'expiry_alert_days' => $this->settingsManager->get('expiry_alert_days', '30', 'inventory'),
        ];

        return view('stock.settings', compact('settings'));
    }

    /**
     * Update inventory portal settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $settings = $request->except(['_token', '_method']);

        foreach ($settings as $key => $value) {
            $this->settingsManager->set($key, (string) $value, 'inventory');
        }

        return back()->with('success', 'Manage Stock portal settings updated successfully.');
    }
}
