<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ChunkUploadController;

use App\Http\Controllers\Pay\CheckoutController;
use App\Http\Controllers\Pay\StripeWebhookController;
use App\Http\Controllers\Pay\MerchController;
use App\Http\Middleware\EnsureUserIsAdmin;





/*
|--------------------------------------------------------------------------
| Public Routes (No Auth Required)
|--------------------------------------------------------------------------
*/
Route::view('/', 'dashboard')->name('home');

// Moved outside so anyone can view the merch
Route::get('/merch/{id}', [PlatformController::class, 'displaymerch'])
    ->name('merchandise.show');
Route::view('dashboard', 'dashboard')->name('dashboard');
Route::get('/Merchandise', [PlatformController::class, 'merchall'])->name('store.all');

/**
 * Catalogue: Open to everyone for browsing
 */
Route::prefix('catalogue')->name('store.')->group(function () {
    Route::get('/male', [PlatformController::class, 'male'])->name('male');
    Route::get('/female', [PlatformController::class, 'female'])->name('female');
    Route::get('/sale', [PlatformController::class, 'sale'])->name('sale');
    Route::get('/unisex', [PlatformController::class, 'unisex'])->name('unisex');
//    Route::get('/all', [PlatformController::class, 'all'])->name('all');
    Route::get('/all', [PlatformController::class, 'all'])->name('catalogue');

    Route::get('/pulse', [PlatformController::class, 'pulse'])->name('pulse');
});



/*
|--------------------------------------------------------------------------
| Authenticated User Routes (Profile, Cart, Dashboard)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Future auth-only routes like 'cart', 'favorites', or 'settings' go here

    Route::get('/favorites', function () {
    return view('platform.wishlist');
})->name('store.favorites');

});


/*
|--------------------------------------------------------------------------
| Admin Routes (Restricted)
|--------------------------------------------------------------------------
*/


Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    
    Route::get('/', [AdminController::class, 'administrator'])->name('admin.dashboard');
    Route::get('/merchandise/list', [AdminController::class, 'merchandize'])
        ->name('admin.merchandise.index');
    Route::get('/merchandise/show/{id}', [AdminController::class, 'merchshow'])
        ->name('admin.merchandise.show');

    // Nested media routes inside admin prefix
Route::prefix('media/vault/upload')->group(function () {
   // Route::post('/chunk', [ChunkUploadController::class, 'chunk']);
   // Route::post('/complete', [ChunkUploadController::class, 'complete']);
   // Route::delete('/revert', [ChunkUploadController::class, 'revert']);
});



    Route::post('/upload/chunk', [ChunkUploadController::class, 'upload']);
    Route::post('/upload/complete', [ChunkUploadController::class, 'complete']);
    Route::delete('/upload/revert', [ChunkUploadController::class, 'revert']);


});









/*
|--------------------------------------------------------------------------
| Cart & Checkout: Open to everyone (Guest logic handled in Controller)
|--------------------------------------------------------------------------
*/


Route::get('/selection', [MerchController::class, 'cart'])->name('store.cart');
Route::post('/checkout', CheckoutController::class)->name('checkout');

Route::get('/checkout/success', function (Request $request) {
    $session = Cashier::stripe()->checkout->sessions->retrieve($request->get('session_id'));
    return view('checkout.success', [
        'customer' => $session->customer_details->name ?? 'Guest',
    ]);
})->name('checkout.success');

Route::view('/checkout/cancel', 'checkout.cancel')->name('checkout.cancel');

/**
 * Stripe Webhook (No Auth / No CSRF)
 */
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handleWebhook']);



require __DIR__.'/settings.php';
