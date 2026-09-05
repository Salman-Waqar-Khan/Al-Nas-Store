<?php

use App\Http\Controllers\StoreController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'index'])->name('store.home');
Route::get('/products/{product:slug}', [StoreController::class, 'product'])->name('store.product');
Route::get('/track-order', [StoreController::class, 'trackForm'])->name('store.track');
Route::post('/track-order', [StoreController::class, 'track'])->middleware('throttle:10,1')->name('store.track.lookup');
Route::post('/checkout', [StoreController::class, 'checkout'])->name('store.checkout');
Route::get('/order/{order:public_id}/success', [StoreController::class, 'success'])->name('store.success');
Route::get('/admin/login', [AdminController::class, 'login'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'authenticate'])->middleware('throttle:5,1')->name('admin.authenticate');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');
Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{order}', [AdminController::class, 'showOrder'])->name('orders.show');
    Route::put('/homepage', [AdminController::class, 'updateHomepage'])->name('homepage.update');
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::delete('/categories/{category}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('products.store');
    Route::put('/products/{product}', [AdminController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{product}', [AdminController::class, 'destroyProduct'])->name('products.destroy');
    Route::get('/products/{product}/variants', [AdminController::class, 'variants'])->name('products.variants');
    Route::post('/products/{product}/variants', [AdminController::class, 'storeVariant'])->name('products.variants.store');
    Route::put('/products/{product}/variants/{variant}', [AdminController::class, 'updateVariant'])->name('products.variants.update');
    Route::delete('/products/{product}/variants/{variant}', [AdminController::class, 'destroyVariant'])->name('products.variants.destroy');
    Route::patch('/orders/{order}/status', [AdminController::class, 'updateOrderStatus'])->name('orders.status');
});
