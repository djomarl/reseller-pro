<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ParcelController;
use App\Http\Controllers\PresetController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/layout-toggle', [DashboardController::class, 'toggleLayout'])->name('dashboard.layout.toggle');

    // Voorraad (Inventory)
    Route::post('/inventory/import', [InventoryController::class, 'importText'])->name('inventory.import');
    Route::post('/inventory/import/superbuy', [InventoryController::class, 'importSuperbuy'])->name('inventory.import.superbuy');
    Route::post('/inventory/bulk-action', [InventoryController::class, 'bulkAction'])->name('inventory.bulkAction');
    Route::get('/inventory/archive', [InventoryController::class, 'index'])->name('inventory.archive');
    Route::resource('inventory', InventoryController::class)->except(['show']);

    // Pakketten (Parcels)
    Route::resource('parcels', ParcelController::class)->except(['show']);

    // Templates (Presets)
    Route::resource('presets', PresetController::class)->except(['show']);

    // Profiel
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rapport
    Route::get('/dashboard/report', [DashboardController::class, 'report'])->name('dashboard.report');
});

require __DIR__.'/auth.php';