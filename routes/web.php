<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Requires laravel/ui package (see guide). Provides login/register/logout routes.
//Auth::routes();

// Routes for any authenticated user (admin & agent)
Route::middleware(['auth'])->group(function () {

    // Common routes
    Route::resource('leads', LeadsController::class);
    Route::get('leads-list', [LeadsController::class, 'leadsList']);
    Route::get('delete-lead', [LeadsController::class, 'deleteLead']);
    Route::get('delete-matched-property', [LeadsController::class, 'deleteMatchedProperty']);

    // Matched Properties
    Route::get('matched-properties', [LeadsController::class, 'matchedProperties'])->name('matched-properties');
    Route::get('matched-properties-list', [LeadsController::class, 'getMatchedProperties']);
    Route::get('property-detail', [LeadsController::class, 'propertyDetail']);

    // Bookmarked Properties
    Route::get('bookmarked-properties', [LeadsController::class, 'bookmarkedProperties'])->name('bookmarked-properties');
    Route::get('bookmarked-properties-list', [LeadsController::class, 'getBookmarkedProperties']);
    Route::get('remove-bookmark', [LeadsController::class, 'removeBookmark']);

    // Deleted Properties
    Route::get('deleted-properties', [LeadsController::class, 'deletedProperties']);
    Route::get('deleted-properties-list', [LeadsController::class, 'getDeletedProperties']);
    Route::get('restore', [LeadsController::class, 'restore']);
});

// Only for admin
Route::middleware(['auth', 'admin'])->group(function () {
    Route::any('assign-leads', [LeadsController::class, 'assignLeads'])->name('assign-leads');
    Route::get('remove-agent', [LeadsController::class, 'removeAgent']);
});

// Only for agent
Route::middleware(['auth', 'agent'])->group(function () {
    Route::get('notification-list', [LeadsController::class, 'notificaitonView']);
    Route::get('notification-ajax', [LeadsController::class, 'getNotifications']);
    Route::get('matched-leads', [LeadsController::class, 'matchedLeadsView']);
    Route::get('matched-leads-ajax', [LeadsController::class, 'getMatchedLeads']);
    Route::get('delete-notification', [LeadsController::class, 'deleteNotifications']);
    Route::get('send-notifications', [LeadsController::class, 'sendNotification']);
});