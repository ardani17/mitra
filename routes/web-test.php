<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Test Routes for Development
|--------------------------------------------------------------------------
|
| These routes are for testing purposes during development.
| They should be removed or disabled in production.
|
*/

// Mobile responsiveness test page
Route::get('/test/mobile-responsive', function () {
    return view('test-mobile-responsive');
})->name('test.mobile-responsive');