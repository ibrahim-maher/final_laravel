<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\VisitorLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BadgeTemplateController;
use App\Http\Controllers\RegistrationFieldController;
use App\Http\Controllers\PublicRegistrationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

// Landing page and public event browsing
Route::get('/', [PublicRegistrationController::class, 'index'])->name('home');

// Public event routes
Route::prefix('events')->name('public.')->group(function () {
    Route::get('/', [PublicRegistrationController::class, 'index'])->name('events.index');
    Route::get('/{event}', [PublicRegistrationController::class, 'show'])->name('events.show');
    Route::get('/{event}/register', [PublicRegistrationController::class, 'register'])->name('events.register');
    Route::post('/{event}/register', [PublicRegistrationController::class, 'store'])->name('events.register.store');
});

// Registration success and QR download
Route::get('/registration/{registration}/success', [PublicRegistrationController::class, 'success'])
    ->name('public.registration.success');
Route::get('/registration/{registration}/qr-download', [PublicRegistrationController::class, 'downloadQR'])
    ->name('public.registration.qr-download');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Authenticated Routes (All Users)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    /*
    |--------------------------------------------------------------------------
    | Events Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('events', EventController::class);
    Route::get('/events/{event}/analytics', [EventController::class, 'analytics'])->name('events.analytics');
    
    /*
    |--------------------------------------------------------------------------
    | Check-in System Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('checkin')->name('checkin.')->group(function () {
        Route::get('/', [CheckinController::class, 'index'])->name('index');
        Route::get('/checkout', [CheckinController::class, 'checkout'])->name('checkout');
        Route::get('/scan-for-print', [CheckinController::class, 'scanForPrint'])->name('scan-for-print');
        Route::post('/scan', [CheckinController::class, 'scan'])->name('scan');
        Route::post('/manual', [CheckinController::class, 'manual'])->name('manual');
        Route::post('/verify-registration', [CheckinController::class, 'verifyRegistration'])->name('verify-registration');
        Route::get('/stats', [CheckinController::class, 'getStats'])->name('stats');
        Route::get('/hourly-analytics', [CheckinController::class, 'getHourlyAnalytics'])->name('hourly-analytics');
        Route::post('/bulk-operation', [CheckinController::class, 'bulkOperation'])->name('bulk-operation');
        Route::get('/export', [CheckinController::class, 'export'])->name('export');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Admin/Manager Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth'])->group(function () {
        
        /*
        |--------------------------------------------------------------------------
        | User Management Routes
        |--------------------------------------------------------------------------
        */
        Route::resource('users', UserController::class);
        Route::prefix('users')->name('users.')->group(function () {
            Route::post('bulk-action', [UserController::class, 'bulkAction'])->name('bulk-action');
            Route::get('export', [UserController::class, 'export'])->name('export');
            Route::post('{user}/assign-events', [UserController::class, 'assignEvents'])->name('assign-events');
            Route::delete('{user}/events/{event}', [UserController::class, 'removeEvent'])->name('remove-event');
            Route::get('{user}/profile', [UserController::class, 'profile'])->name('profile');
            Route::put('{user}/profile', [UserController::class, 'updateProfile'])->name('update-profile');
        });
        
        /*
        |--------------------------------------------------------------------------
        | Venues Management Routes
        |--------------------------------------------------------------------------
        */
        Route::resource('venues', VenueController::class);
        
        /*
        |--------------------------------------------------------------------------
        | Categories Management Routes
        |--------------------------------------------------------------------------
        */
        Route::resource('categories', CategoryController::class);
        
        /*
        |--------------------------------------------------------------------------
        | Tickets Management Routes
        |--------------------------------------------------------------------------
        */
        Route::resource('tickets', TicketController::class);
        
        /*
        |--------------------------------------------------------------------------
        | Registrations Management Routes
        |--------------------------------------------------------------------------
        */
        Route::resource('registrations', RegistrationController::class);
        Route::prefix('registrations')->name('registrations.')->group(function () {
            Route::post('bulk-action', [RegistrationController::class, 'bulkAction'])->name('bulk-action');
            Route::get('export', [RegistrationController::class, 'export'])->name('export');
            Route::get('public-register', [RegistrationController::class, 'publicRegister'])->name('public-register');
            Route::post('public-register', [RegistrationController::class, 'publicRegister'])->name('public-register.store');
            Route::get('success', [RegistrationController::class, 'registrationSuccess'])->name('success');
            Route::get('search', [RegistrationController::class, 'search'])->name('search');
            Route::get('{registration}/badge', [RegistrationController::class, 'getBadge'])->name('badge');
            Route::get('{registration}/badge-download', [RegistrationController::class, 'downloadBadge'])->name('download-badge');
            Route::get('{registration}/qr-code', [RegistrationController::class, 'downloadQrCode'])->name('download-qr-code');
            Route::post('{registration}/resend-confirmation', [RegistrationController::class, 'resendConfirmation'])->name('resend-confirmation');
        });
        
        // AJAX endpoints for registrations
        Route::get('events/{event}/tickets', [RegistrationController::class, 'getTickets'])->name('events.tickets');
        Route::get('events/{event}/registration-fields', [RegistrationController::class, 'getRegistrationFields'])->name('events.registration-fields');
        
        /*
        |--------------------------------------------------------------------------
        | Registration Fields Management Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('events/{event}/registration-fields')->name('registration-fields.')->group(function () {
            Route::get('/', [RegistrationFieldController::class, 'index'])->name('index');
            Route::get('/create', [RegistrationFieldController::class, 'create'])->name('create');
            Route::post('/', [RegistrationFieldController::class, 'store'])->name('store');
            Route::get('/{registrationField}', [RegistrationFieldController::class, 'show'])->name('show');
            Route::get('/{registrationField}/edit', [RegistrationFieldController::class, 'edit'])->name('edit');
            Route::put('/{registrationField}', [RegistrationFieldController::class, 'update'])->name('update');
            Route::delete('/{registrationField}', [RegistrationFieldController::class, 'destroy'])->name('destroy');
            Route::post('/reorder', [RegistrationFieldController::class, 'reorder'])->name('reorder');
            Route::post('/{registrationField}/duplicate', [RegistrationFieldController::class, 'duplicate'])->name('duplicate');
            Route::post('/bulk-delete', [RegistrationFieldController::class, 'bulkDelete'])->name('bulk-delete');
            Route::get('/export', [RegistrationFieldController::class, 'export'])->name('export');
            Route::post('/import', [RegistrationFieldController::class, 'import'])->name('import');
        });
        
        /*
        |--------------------------------------------------------------------------
        | Badge Templates Management Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('badge-templates')->name('badge-templates.')->group(function () {
            // AJAX endpoints
            Route::get('/get-tickets', [BadgeTemplateController::class, 'getTickets'])->name('getTickets');
            Route::post('/content/{contentId}/update', [BadgeTemplateController::class, 'updateContent'])->name('updateContent');
            Route::post('/save-all-changes', [BadgeTemplateController::class, 'saveAllChanges'])->name('saveAllChanges');
            
            // Main routes
            Route::match(['GET', 'POST'], '/create-or-edit', [BadgeTemplateController::class, 'createOrEdit'])->name('createOrEdit');
            Route::get('/{badgeTemplate}/preview', [BadgeTemplateController::class, 'preview'])->name('preview');
        });
        Route::resource('badge-templates', BadgeTemplateController::class);
        
        // Badge Printing
        Route::get('/registrations/{registration}/print-badge', [BadgeTemplateController::class, 'printBadge'])
            ->name('registrations.printBadge');
        
        /*
        |--------------------------------------------------------------------------
        | Visitor Logs Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('visitor-logs')->name('visitor-logs.')->group(function () {
            Route::get('/', [VisitorLogController::class, 'index'])->name('index');
            Route::get('/export', [VisitorLogController::class, 'export'])->name('export');
            Route::get('/realtime', [VisitorLogController::class, 'realtime'])->name('realtime');
            Route::get('/analytics', [VisitorLogController::class, 'analytics'])->name('analytics');
            Route::get('/reports', [VisitorLogController::class, 'reports'])->name('reports');
            Route::get('/timeline', [VisitorLogController::class, 'visitorTimeline'])->name('timeline');
            Route::get('/{id}', [VisitorLogController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::delete('/{id}', [VisitorLogController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::post('/bulk-delete', [VisitorLogController::class, 'bulkDelete'])->name('bulk-delete');
        });
        
        /*
        |--------------------------------------------------------------------------
        | Reports Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/events', [ReportController::class, 'events'])->name('events');
            Route::get('/registrations', [ReportController::class, 'registrations'])->name('registrations');
            Route::get('/attendance', [ReportController::class, 'attendance'])->name('attendance');
            Route::get('/export', [ReportController::class, 'export'])->name('export');
        });
    });
});

/*
|--------------------------------------------------------------------------
| API Routes for AJAX calls
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    Route::get('/visitor-logs/realtime', [VisitorLogController::class, 'apiLogs'])->name('visitor-logs');
});

/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});