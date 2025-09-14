<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\{
    CustomersController,
    VendorsController,
    ZonesController,
    ProductsController,
    SalesController,
    DashboardController
};
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'      => Route::has('login'),
        'canRegister'   => Route::has('register'),
        'laravelVersion'=> Application::VERSION,
        'phpVersion'    => PHP_VERSION,
    ]);
});

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Rutas protegidas (Inertia)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
  ->middleware(['auth', 'verified'])
  ->name('dashboard');

    // Recursos con controladores (Inertia)
    Route::resource('customers', CustomersController::class);
    Route::post('customers/{customer}/toggle', [CustomersController::class, 'toggle'])
    ->name('customers.toggle');

    Route::resource('vendors',   VendorsController::class);
    Route::post('vendors/{vendor}/toggle', [VendorsController::class, 'toggle'])
    ->name('vendors.toggle');

    Route::resource('zones',     ZonesController::class);
    Route::post('zones/{zone}/toggle', [ZonesController::class, 'toggle'])->name('zones.toggle');

    Route::resource('products',  ProductsController::class);
    Route::post('products/{product}/toggle', [ProductsController::class, 'toggle'])->name('products.toggle');

    Route::resource('sales',     SalesController::class);
    Route::post('sales/{sale}/cancel', [SalesController::class, 'cancel'])->name('sales.cancel');

    // Perfil
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');
});
