<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Rutas para Recepcionistas, Gerentes y Superusuario
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:recepcionista,gerente,superusuario'])->group(function () {
    Route::get('/reservas', \App\Livewire\Reservas\Index::class)->name('reservas.index');
    Route::get('/estacionamiento', fn () => view('livewire.estacionamiento.index'))->name('estacionamiento.index');
    Route::get('/habitaciones', fn () => view('livewire.habitaciones.index'))->name('habitaciones.index');
    Route::get('/facturacion', \App\Livewire\Facturacion\Index::class)->name('facturacion.index');
    Route::get('/servicios-adicionales', \App\Livewire\ServiciosAdicionales\Index::class)->name('servicios-adicionales.index');
    Route::get('/reportes/ingresos', \App\Livewire\Reportes\ReportesIngresos::class)->name('reportes.ingresos');
    Route::get('/reservas/calendario-visual', \App\Livewire\Reservas\CalendarioVisual::class)->name('reservas.calendario-visual');
    Route::get('/busqueda-avanzada', \App\Livewire\Reservas\BusquedaAvanzada::class)->name('reservas.busqueda-avanzada');
});

/*
|--------------------------------------------------------------------------
| Rutas exclusivas para Gerentes y Superusuario
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:gerente,superusuario'])->group(function () {
    Route::get('/gerente/habitaciones', \App\Livewire\Gerente\GestionHabitaciones::class)->name('gerente.habitaciones');
    Route::get('/gerente/estacionamiento', \App\Livewire\Gerente\GestionEstacionamiento::class)->name('gerente.estacionamiento');
    Route::get('/reportes/reportes-avanzados', \App\Livewire\Reportes\ReportesAvanzados::class)->name('reportes.reportes-avanzados');
    Route::get('/audit-log', \App\Livewire\AuditLog\Index::class)->name('audit-log.index');
});

/*
|--------------------------------------------------------------------------
| Rutas exclusivas para Superusuario
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'superuser'])->group(function () {
    Route::get('/superuser/usuarios', \App\Livewire\Superuser\GestionUsuarios::class)
        ->name('superuser.usuarios');
});

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
*/
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

require __DIR__ . '/auth.php';
