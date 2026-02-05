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

    // Voorraad (Inventory) & Import
    Route::resource('inventory', InventoryController::class);
    Route::post('/inventory/import', [InventoryController::class, 'importText'])->name('inventory.import');

    // Pakketten (Parcels)
    Route::resource('parcels', ParcelController::class);

    // Templates (Presets)
    Route::resource('presets', PresetController::class);

    // Profiel
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';