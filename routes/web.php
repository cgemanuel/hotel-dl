<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Ruta de Reservas
Route::get('/reservas', \App\Livewire\Reservas\Index::class)->name('reservas.index');

Route::get('/estacionamiento', function () {
    return view('livewire.estacionamiento.index');
})->name('estacionamiento.index');

Route::get('/habitaciones', function () {
    return view('livewire.habitaciones.index');
})->name('habitaciones.index');

// Ruta de Facturación
Route::get('/facturacion', \App\Livewire\Facturacion\Index::class)->name('facturacion.index');

// Ruta de Servicios Adicionales
Route::get('/servicios-adicionales', \App\Livewire\ServiciosAdicionales\Index::class)
    ->middleware(['auth'])
    ->name('servicios-adicionales.index');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

require __DIR__.'/auth.php';
