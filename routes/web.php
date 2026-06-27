<?php

use App\Http\Controllers\GalleryController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\OrderRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/img/{preset}/{path}', [ImageController::class, 'show'])
    ->where('path', '.*')
    ->name('images.show');

Route::get('/', [GalleryController::class, 'home'])->name('home');
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('/gallery/load-more', [GalleryController::class, 'loadMore'])->name('gallery.load-more');
Route::get('/category/{category}', [GalleryController::class, 'category'])->name('gallery.category');
Route::get('/artworks/{artwork}', [GalleryController::class, 'show'])->name('artworks.show');
Route::get('/artworks/{artwork}/order', [OrderRequestController::class, 'create'])->name('orders.create');
Route::post('/artworks/{artwork}/order', [OrderRequestController::class, 'store'])->name('orders.store');
