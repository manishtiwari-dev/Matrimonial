<?php

use Illuminate\Support\Facades\Route;

// Route::get('/clear', function () {
//     \Illuminate\Support\Facades\Artisan::call('optimize:clear');
// });


// for clear-cache
Route::get('/cc', function() {
    
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('optimize:clear');

    flash(localize('Cach-Cleared'))->success();
    return back();
})->name('clear-cache');


// User Support Ticket
Route::controller('TicketController')->prefix('ticket')->name('ticket.')->group(function () {
    Route::get('/', 'supportTicket')->name('index');
    Route::get('new', 'openSupportTicket')->name('open');
    Route::post('create', 'storeSupportTicket')->name('store');
    Route::get('view/{ticket}', 'viewTicket')->name('view');
    Route::post('reply/{ticket}', 'replyTicket')->name('reply');
    Route::post('close/{ticket}', 'closeTicket')->name('close');
    Route::get('download/{ticket}', 'ticketDownload')->name('download');
});

Route::get('app/deposit/confirm/{hash}', 'Gateway\PaymentController@appDepositConfirm')->name('deposit.app.confirm');
Route::get('payment-method/data', 'Gateway\PaymentController@paymentMethodData')->name('payment.method.data');

Route::controller('SiteController')->group(function () {

    //member search
    Route::get('members', 'members')->name('member.list');

    Route::get('/contact', 'contact')->name('contact');
    Route::post('/contact', 'contactSubmit');
    Route::get('/change/{lang?}', 'changeLanguage')->name('lang');

    Route::get('cookie-policy', 'cookiePolicy')->name('cookie.policy');

    Route::get('/cookie/accept', 'cookieAccept')->name('cookie.accept');

    Route::get('packages', 'packages')->name('packages');

    Route::get('stories', 'stories')->name('stories');
    Route::get('story/{slug}/{id}', 'storyDetails')->name('story.details');

    Route::get('policy/{slug}/{id}', 'policyPages')->name('policy.pages');

    Route::get('placeholder-image/{size}', 'placeholderImage')->name('placeholder.image');


    Route::get('/{slug}', 'pages')->name('pages');
    Route::get('/', 'index')->name('home');
});
