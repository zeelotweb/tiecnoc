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

    Route::get('/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::get('/team', [AdminController::class, 'team'])->name('admin.team');
    Route::get('/partners', [AdminController::class, 'partners'])->name('admin.partners');
    Route::get('/vendors', [AdminController::class, 'vendors'])->name('admin.vendors');

    Route::post('/upload/chunk', [ChunkUploadController::class, 'upload']);
    Route::post('/upload/complete', [ChunkUploadController::class, 'complete']);
    Route::delete('/upload/revert', [ChunkUploadController::class, 'revert']);


});




/*
|--------------------------------------------------------------------------
| Terms, Privacy, Misc: Open to everyone (Guest logic handled in Controller)
|--------------------------------------------------------------------------
*/



    Route::post('/terms', [ChunkUploadController::class, 'terms'])->name('terms');
    Route::post('privacy', [ChunkUploadController::class, 'privacy'])->name('privacy');
    Route::delete('pertner', [ChunkUploadController::class, 'partner'])->name('partner');
    Route::delete('contractor', [ChunkUploadController::class, 'contractor'])->name('contractor');

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
