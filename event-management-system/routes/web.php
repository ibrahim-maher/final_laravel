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
use App\Http\Controllers\UserLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserControllerAuth;
use App\Http\Controllers\BadgeTemplateController;
use App\Http\Controllers\RegistrationFieldController;
use App\Http\Controllers\PublicRegistrationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ApiController;
use App\Http\Middleware\AdminMiddleware;

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

// Landing page and public event browsing
Route::get('/', [PublicRegistrationController::class, 'index'])->name('public.events.index');

// Public event routes with rate limiting
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/events/{event}', [PublicRegistrationController::class, 'show'])
        ->name('public.events.show');
    
    Route::get('/events/{event}/register', [PublicRegistrationController::class, 'register'])
        ->name('public.events.register');
    
    Route::post('/events/{event}/register', [PublicRegistrationController::class, 'store'])
        ->name('public.events.register.store')
        ->middleware(['throttle:10,1']);
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

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard - accessible to all authenticated users
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile management (Note: These conflict with users resource routes, so renamed)
    Route::get('/my-profile', [UserController::class, 'myProfile'])->name('my-profile.show');
    Route::patch('/my-profile', [UserController::class, 'updateMyProfile'])->name('my-profile.update');
    Route::delete('/my-profile', [UserController::class, 'deleteMyProfile'])->name('my-profile.delete');
    
    // Events - Read Access for All Authenticated Users
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
    
    // Check-in System - All authenticated users
    Route::prefix('checkin')->name('checkin.')->group(function () {
        Route::get('/', [CheckinController::class, 'index'])->name('index');
        Route::post('/scan', [CheckinController::class, 'scan'])->name('scan');
        Route::get('/history', [CheckinController::class, 'history'])->name('history');
        Route::post('/manual', [CheckinController::class, 'manualCheckin'])->name('manual');
        Route::get('/checkout', [CheckinController::class, 'checkout']) ->name('checkout');
    });
    
    // Visitor Logs - All authenticated users can view
    Route::resource('visitor-logs', VisitorLogController::class)->only(['index', 'show']);
    Route::get('/visitor-logs/export', [VisitorLogController::class, 'export'])->name('visitor-logs.export');
    
    // User activity logs
    Route::get('/activity-logs', [UserLogController::class, 'index'])->name('user-logs.index');
    Route::get('/activity-logs/{log}', [UserLogController::class, 'show'])->name('user-logs.show');
    
    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    });
    
    // Quick registration for authenticated users
    Route::get('/register-event', [RegistrationController::class, 'publicCreate'])->name('registration.public');
    Route::post('/register-event', [RegistrationController::class, 'publicStore'])->name('registration.public.store');
    
    // QR Code downloads
    Route::get('/registration/{registration}/qr-download', [PublicRegistrationController::class, 'downloadQR'])
        ->name('registration.qr-download');

    /*
    |--------------------------------------------------------------------------
    | User Management Routes (CONSOLIDATED)
    |--------------------------------------------------------------------------
    */
    
    // Standard Resource Routes for Users
    Route::resource('users', UserController::class);

    // Additional User Management Routes
    Route::prefix('users')->name('users.')->group(function () {
        
        // Admin-only routes
        Route::middleware([AdminMiddleware::class])->group(function () {
            Route::post('bulk-action', [UserController::class, 'bulkAction'])->name('bulk-action');
            Route::post('/{user}/activate', [UserController::class, 'activate'])->name('activate');
            Route::post('/{user}/deactivate', [UserController::class, 'deactivate'])->name('deactivate');
            Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
            Route::post('/import', [UserController::class, 'import'])->name('import');
        });
        
        // Export route (accessible to admins and event managers)
        Route::get('export', [UserController::class, 'export'])->name('export');
        
        // Event Assignment Routes
        Route::post('{user}/assign-events', [UserController::class, 'assignEvents'])->name('assign-events');
        Route::delete('{user}/events/{event}', [UserController::class, 'removeEvent'])->name('remove-event');
        
        // Profile Management Routes
        Route::get('{user}/profile', [UserController::class, 'profile'])->name('profile');
        Route::put('{user}/profile', [UserController::class, 'updateProfile'])->name('update-profile');
    });

    /*
    |--------------------------------------------------------------------------
    | Registration Fields Routes
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('events/{event}')->group(function () {
        // Main CRUD routes
        Route::get('registration-fields', [RegistrationFieldController::class, 'index'])->name('registration-fields.index');
        Route::get('registration-fields/create', [RegistrationFieldController::class, 'create'])->name('registration-fields.create');
        Route::post('registration-fields', [RegistrationFieldController::class, 'store'])->name('registration-fields.store');
        Route::get('registration-fields/{registrationField}', [RegistrationFieldController::class, 'show'])->name('registration-fields.show');
        Route::get('registration-fields/{registrationField}/edit', [RegistrationFieldController::class, 'edit'])->name('registration-fields.edit');
        Route::put('registration-fields/{registrationField}', [RegistrationFieldController::class, 'update'])->name('registration-fields.update');
        Route::delete('registration-fields/{registrationField}', [RegistrationFieldController::class, 'destroy'])->name('registration-fields.destroy');
        
        // Additional routes
        Route::post('registration-fields/reorder', [RegistrationFieldController::class, 'reorder'])->name('registration-fields.reorder');
        Route::post('registration-fields/{registrationField}/duplicate', [RegistrationFieldController::class, 'duplicate'])->name('registration-fields.duplicate');
        Route::post('registration-fields/bulk-delete', [RegistrationFieldController::class, 'bulkDelete'])->name('registration-fields.bulk-delete');
        Route::get('registration-fields-export', [RegistrationFieldController::class, 'export'])->name('registration-fields.export');
        Route::post('registration-fields/import', [RegistrationFieldController::class, 'import'])->name('registration-fields.import');
    });
});

/*
|--------------------------------------------------------------------------
| Event Management Routes (Event Managers + Admins)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'event.manager'])->group(function () {
    
    // Events Management (CRUD) - Admin can do everything, Event Managers limited
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::patch('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    
    // Additional event actions
    Route::prefix('events/{event}')->name('events.')->group(function () {
        Route::post('/duplicate', [EventController::class, 'duplicate'])->name('duplicate');
        Route::post('/publish', [EventController::class, 'publish'])->name('publish');
        Route::post('/unpublish', [EventController::class, 'unpublish'])->name('unpublish');
        Route::get('/analytics', [EventController::class, 'analytics'])->name('analytics');
        Route::get('/export', [EventController::class, 'export'])->name('export');
        Route::get('/tickets', [RegistrationController::class, 'getTickets'])->name('tickets');
    });
    
    // Tickets Management
    Route::resource('tickets', TicketController::class);
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::post('/bulk-update', [TicketController::class, 'bulkUpdate'])->name('bulk-update');
        Route::get('/export', [TicketController::class, 'export'])->name('export');
    });
    
    // Registrations Management
    Route::resource('registrations', RegistrationController::class);
    Route::prefix('registrations')->name('registrations.')->group(function () {
        Route::get('/export', [RegistrationController::class, 'export'])->name('export');
        Route::post('/import', [RegistrationController::class, 'import'])->name('import');
        Route::post('/bulk-action', [RegistrationController::class, 'bulkAction'])->name('bulk-action');
        Route::get('/search', [RegistrationController::class, 'search'])->name('search');
        Route::get('/tickets/{event_id}', [RegistrationController::class, 'getTickets'])->name('tickets');
        Route::get('/fields/{event_id}', [RegistrationController::class, 'getRegistrationFields'])->name('fields');
        Route::get('public-register', [RegistrationController::class, 'publicRegister'])->name('public-register');
        Route::post('public-register', [RegistrationController::class, 'publicRegister'])->name('public-register.store');

        // Individual registration actions
        Route::get('/{registration}/qr-code', [RegistrationController::class, 'downloadQrCode'])->name('download_qr_code');
        Route::get('/{registration}/badge', [RegistrationController::class, 'getBadge'])->name('get_badge');
        Route::get('/{registration}/badge-download', [RegistrationController::class, 'downloadBadge'])->name('download_badge');
        Route::post('/{registration}/resend-confirmation', [RegistrationController::class, 'resendConfirmation'])->name('resend-confirmation');
    });
    
    // Badge Templates Management
    Route::prefix('badge-templates')->name('badge-templates.')->group(function () {
        // AJAX endpoints - Must come before resource routes
        Route::get('/get-tickets', [BadgeTemplateController::class, 'getTickets'])->name('getTickets');
        Route::post('/content/{contentId}/update', [BadgeTemplateController::class, 'updateContent'])->name('updateContent');
        Route::post('/save-all-changes', [BadgeTemplateController::class, 'saveAllChanges'])->name('saveAllChanges');
        
        // Specific actions
        Route::match(['GET', 'POST'], '/create-or-edit', [BadgeTemplateController::class, 'createOrEdit'])->name('createOrEdit');
        Route::get('/{badgeTemplate}/preview', [BadgeTemplateController::class, 'preview'])->name('preview');
        Route::get('/{badgeTemplate}/duplicate', [BadgeTemplateController::class, 'duplicate'])->name('duplicate');
        Route::post('/{badgeTemplate}/duplicate', [BadgeTemplateController::class, 'storeDuplicate'])->name('store-duplicate');
    });
    
    // Badge Templates Resource Routes
    Route::resource('badge-templates', BadgeTemplateController::class);
    
    // Badge Printing Routes
    Route::get('/registrations/{registration}/print-badge', [BadgeTemplateController::class, 'printBadge'])
        ->name('registrations.printBadge');
    
    // Reports for Event Managers
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/events', [ReportController::class, 'events'])->name('events');
        Route::get('/registrations', [ReportController::class, 'registrations'])->name('registrations');
        Route::get('/attendance', [ReportController::class, 'attendance'])->name('attendance');
        Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
        Route::get('/export', [ReportController::class, 'export'])->name('export');
        Route::post('/generate', [ReportController::class, 'generate'])->name('generate');
        Route::get('/scheduled', [ReportController::class, 'scheduled'])->name('scheduled');
    });
    
    // Communication tools
    Route::prefix('communications')->name('communications.')->group(function () {
        Route::get('/', [NotificationController::class, 'communicationIndex'])->name('index');
        Route::post('/send-email', [NotificationController::class, 'sendBulkEmail'])->name('send-email');
        Route::post('/send-sms', [NotificationController::class, 'sendBulkSms'])->name('send-sms');
        Route::get('/templates', [NotificationController::class, 'templates'])->name('templates');
        Route::post('/templates', [NotificationController::class, 'storeTemplate'])->name('store-template');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Only Routes (System Administration)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    
    // Admin Dashboard
    Route::get('/admin', [DashboardController::class, 'admin'])->name('admin.dashboard');
    
    // Venues Management (Admin Only)
    Route::resource('venues', VenueController::class);
    Route::prefix('venues')->name('venues.')->group(function () {
        Route::post('/{venue}/toggle-status', [VenueController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/export', [VenueController::class, 'export'])->name('export');
        Route::post('/bulk-action', [VenueController::class, 'bulkAction'])->name('bulk-action');
    });
    
    // Categories Management (Admin Only)
    Route::resource('categories', CategoryController::class);
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::post('/reorder', [CategoryController::class, 'reorder'])->name('reorder');
        Route::post('/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/export', [CategoryController::class, 'export'])->name('export');
    });
    
    // System Settings (Admin Only)
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/general', [SettingsController::class, 'updateGeneral'])->name('update-general');
        Route::post('/email', [SettingsController::class, 'updateEmail'])->name('update-email');
        Route::post('/payment', [SettingsController::class, 'updatePayment'])->name('update-payment');
        Route::post('/notifications', [SettingsController::class, 'updateNotifications'])->name('update-notifications');
        Route::post('/backup', [SettingsController::class, 'createBackup'])->name('create-backup');
        Route::get('/logs', [SettingsController::class, 'logs'])->name('logs');
        Route::post('/clear-cache', [SettingsController::class, 'clearCache'])->name('clear-cache');
    });
    
    // System Logs and Monitoring (Admin Only)
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/logs', [UserLogController::class, 'systemLogs'])->name('logs');
        Route::get('/performance', [DashboardController::class, 'performance'])->name('performance');
        Route::get('/health-check', [DashboardController::class, 'healthCheck'])->name('health-check');
        Route::post('/maintenance-mode', [SettingsController::class, 'toggleMaintenance'])->name('toggle-maintenance');
    });
    
    // Advanced Reports (Admin Only)
    Route::prefix('admin/reports')->name('admin.reports.')->group(function () {
        Route::get('/system', [ReportController::class, 'systemReports'])->name('system');
        Route::get('/users', [ReportController::class, 'userReports'])->name('users');
        Route::get('/financial', [ReportController::class, 'financialReports'])->name('financial');
        Route::get('/analytics', [ReportController::class, 'analytics'])->name('analytics');
    });
});

/*
|--------------------------------------------------------------------------
| API Routes (Internal AJAX calls)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->prefix('api')->name('api.')->group(function () {
    
    // Search and autocomplete
    Route::get('/search/events', [ApiController::class, 'searchEvents'])->name('search.events');
    Route::get('/search/users', [ApiController::class, 'searchUsers'])->name('search.users');
    Route::get('/search/registrations', [ApiController::class, 'searchRegistrations'])->name('search.registrations');
    
    // Data for dropdowns and selects
    Route::get('/venues/list', [VenueController::class, 'apiList'])->name('venues.list');
    Route::get('/categories/list', [CategoryController::class, 'apiList'])->name('categories.list');
    Route::get('/tickets/by-event/{event}', [TicketController::class, 'byEvent'])->name('tickets.by-event');
    
    // Quick actions
    Route::post('/quick-checkin', [CheckinController::class, 'quickCheckin'])->name('quick-checkin');
    Route::post('/send-notification', [NotificationController::class, 'sendQuick'])->name('send-notification');
    
    // Statistics and dashboard data
    Route::get('/dashboard/stats', [DashboardController::class, 'apiStats'])->name('dashboard.stats');
    Route::get('/events/{event}/stats', [EventController::class, 'apiStats'])->name('events.stats');
});

/*
|--------------------------------------------------------------------------
| Development and Testing Routes
|--------------------------------------------------------------------------
*/

// Add this to routes/web.php temporarily for testing
Route::get('/test-permissions', function() {
    if (!auth()->check()) {
        return 'Not logged in';
    }
    
    $user = auth()->user();
    return [
        'user_id' => $user->id,
        'email' => $user->email,
        'role_raw' => $user->role,
        'isAdmin' => $user->isAdmin(),
        'isEventManager' => $user->isEventManager(),
        'canManageEvents' => $user->canManageEvents(),
    ];
})->middleware('auth');

/*
|--------------------------------------------------------------------------
| Fallback Routes
|--------------------------------------------------------------------------
*/

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});


// Authenticated routes
Route::middleware(['auth'])->group(function () {
    
    // Main Registration Routes
    Route::resource('registrations', RegistrationController::class);
    
    // Additional registration routes that need to be defined BEFORE the resource routes
    Route::post('registrations/bulk-action', [RegistrationController::class, 'bulkAction'])->name('registrations.bulk-action');
    Route::get('registrations-export', [RegistrationController::class, 'export'])->name('registrations.export');
    Route::get('registrations/public-register', [RegistrationController::class, 'publicRegister'])->name('registrations.public-register');
    Route::post('registrations/public-register', [RegistrationController::class, 'publicRegister'])->name('registrations.public-register.store');
    Route::get('registrations/success', [RegistrationController::class, 'registrationSuccess'])->name('registrations.success');
    
    // Admin Registration Management (Alternative routes)
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // Registration management
        Route::get('registrations', [RegistrationController::class, 'index'])->name('registrations.index');
        Route::get('registrations/create', [RegistrationController::class, 'create'])->name('registrations.create');
        Route::post('registrations', [RegistrationController::class, 'store'])->name('registrations.store');
        Route::get('registrations/{registration}', [RegistrationController::class, 'show'])->name('registrations.show');
        Route::get('registrations/{registration}/edit', [RegistrationController::class, 'edit'])->name('registrations.edit');
        Route::put('registrations/{registration}', [RegistrationController::class, 'update'])->name('registrations.update');
        Route::delete('registrations/{registration}', [RegistrationController::class, 'destroy'])->name('registrations.destroy');
        
        // Bulk actions and additional functionality
        Route::post('registrations/bulk-action', [RegistrationController::class, 'bulkAction'])->name('registrations.bulk-action');
        Route::get('registrations-export', [RegistrationController::class, 'export'])->name('registrations.export');
        
        // Public registration routes (admin can also use these)
        Route::get('public-register', [RegistrationController::class, 'publicRegister'])->name('registrations.public-register');
        Route::post('public-register', [RegistrationController::class, 'publicRegister'])->name('registrations.public-register.store');
        Route::get('registration-success', [RegistrationController::class, 'registrationSuccess'])->name('registrations.success');
    });
    
    // Registration Fields Management
    Route::prefix('events/{event}')->group(function () {
        Route::get('registration-fields', [RegistrationFieldController::class, 'index'])->name('registration-fields.index');
        Route::get('registration-fields/create', [RegistrationFieldController::class, 'create'])->name('registration-fields.create');
        Route::post('registration-fields', [RegistrationFieldController::class, 'store'])->name('registration-fields.store');
        Route::get('registration-fields/{registrationField}', [RegistrationFieldController::class, 'show'])->name('registration-fields.show');
        Route::get('registration-fields/{registrationField}/edit', [RegistrationFieldController::class, 'edit'])->name('registration-fields.edit');
        Route::put('registration-fields/{registrationField}', [RegistrationFieldController::class, 'update'])->name('registration-fields.update');
        Route::delete('registration-fields/{registrationField}', [RegistrationFieldController::class, 'destroy'])->name('registration-fields.destroy');
        
        // Additional registration field routes
        Route::post('registration-fields/reorder', [RegistrationFieldController::class, 'reorder'])->name('registration-fields.reorder');
        Route::post('registration-fields/{registrationField}/duplicate', [RegistrationFieldController::class, 'duplicate'])->name('registration-fields.duplicate');
        Route::post('registration-fields/bulk-delete', [RegistrationFieldController::class, 'bulkDelete'])->name('registration-fields.bulk-delete');
        Route::get('registration-fields-export', [RegistrationFieldController::class, 'export'])->name('registration-fields.export');
        Route::post('registration-fields/import', [RegistrationFieldController::class, 'import'])->name('registration-fields.import');
    });
});


// Public routes (no authentication required)
Route::prefix('public')->name('public.')->group(function () {
    // Public event listing and registration
    Route::get('events', [PublicRegistrationController::class, 'index'])->name('events.index');
    Route::get('events/{event}', [PublicRegistrationController::class, 'show'])->name('events.show');
    Route::get('events/{event}/register', [PublicRegistrationController::class, 'register'])->name('events.register');
    Route::post('events/{event}/register', [PublicRegistrationController::class, 'store'])->name('events.register.store');
    Route::get('registration/{registration}/success', [PublicRegistrationController::class, 'success'])->name('registration.success');
    Route::get('registration/{registration}/qr-download', [PublicRegistrationController::class, 'downloadQR'])->name('registration.qr-download');
});

// API routes for AJAX calls
Route::prefix('api')->name('api.')->group(function () {
    Route::get('events/{event}/tickets', [RegistrationController::class, 'getTickets'])->name('events.tickets');
    Route::get('events/{event}/registration-fields', [RegistrationController::class, 'getRegistrationFields'])->name('events.registration-fields');
});

// Legacy API routes (for backward compatibility)
Route::get('registrations/tickets/{event}', [RegistrationController::class, 'getTickets'])->name('registrations.tickets');
Route::get('registrations/fields/{event}', [RegistrationController::class, 'getRegistrationFields'])->name('registrations.fields');

    
    // Visitor Logs Routes
    Route::prefix('visitor-logs')->name('visitor-logs.')->group(function () {
        // Main index page
        Route::get('/', [VisitorLogController::class, 'index'])->name('index');
        
        // Show specific log details
        Route::get('/{visitorLog}', [VisitorLogController::class, 'show'])->name('show');
        
        // Delete log (Admin only)
        Route::delete('/{visitorLog}', [VisitorLogController::class, 'destroy'])
             ->name('destroy')
             ->middleware('can:admin');
        
        // Bulk operations
        Route::post('/bulk-delete', [VisitorLogController::class, 'bulkDelete'])
             ->name('bulk-delete')
             ->middleware('can:admin');
        
        // Export functionality
        Route::get('/export/logs', [VisitorLogController::class, 'export'])->name('export');
        
        // Real-time data endpoints
        Route::get('/api/realtime', [VisitorLogController::class, 'realtime'])->name('realtime');
        
        // Analytics
        Route::get('/analytics/dashboard', [VisitorLogController::class, 'analytics'])->name('analytics');
        
        // Reports
        Route::get('/reports/generate', [VisitorLogController::class, 'reports'])->name('reports');
        
        // Visitor timeline
        Route::get('/timeline/visitor', [VisitorLogController::class, 'visitorTimeline'])->name('timeline');
        
    });
    // Visitor Logs Routes
Route::prefix('visitor-logs')->name('visitor-logs.')->group(function () {
    
        // Main index page
        Route::get('/', [VisitorLogController::class, 'index'])->name('index');
        
        // Show specific log details
        Route::get('/{visitorLog}', [VisitorLogController::class, 'show'])->name('show');
        
        // Delete log (Admin only)
        Route::delete('/{visitorLog}', [VisitorLogController::class, 'destroy'])
             ->name('destroy')
             ->middleware('can:admin');
        
        // Bulk operations
        Route::post('/bulk-delete', [VisitorLogController::class, 'bulkDelete'])
             ->name('bulk-delete')
             ->middleware('can:admin');
        
        // Export functionality
        Route::get('/export/logs', [VisitorLogController::class, 'export'])->name('export');
        
        // Real-time data endpoints
        Route::get('/api/realtime', [VisitorLogController::class, 'realtime'])->name('realtime');
        
        // Analytics
        Route::get('/analytics/dashboard', [VisitorLogController::class, 'analytics'])->name('analytics');
        
        // Reports
        Route::get('/reports/generate', [VisitorLogController::class, 'reports'])->name('reports');
        
        // Visitor timeline
        Route::get('/timeline/visitor', [VisitorLogController::class, 'visitorTimeline'])->name('timeline');
        
        // Update admin note
        Route::patch('/{visitorLog}/update-note', [VisitorLogController::class, 'updateNote'])
             ->name('update-note')
             ->middleware('can:admin');
    });
 // Visitor Logs Routes
    Route::prefix('visitor-logs')->group(function () {
        // Main routes
        Route::get('/', [VisitorLogController::class, 'index'])->name('visitor-logs.index');
        Route::get('/export', [VisitorLogController::class, 'export'])->name('visitor-logs.export');
        Route::get('/realtime/dashboard', [VisitorLogController::class, 'realtime'])->name('visitor-logs.realtime');
        Route::get('/analytics/dashboard', [VisitorLogController::class, 'analytics'])->name('visitor-logs.analytics');
        Route::get('/reports/generate', [VisitorLogController::class, 'reports'])->name('visitor-logs.reports');
        Route::get('/timeline/visitor', [VisitorLogController::class, 'visitorTimeline'])->name('visitor-logs.timeline');
        
        // Specific log routes
        Route::get('/{id}', [VisitorLogController::class, 'show'])->name('visitor-logs.show')->where('id', '[0-9]+');
        Route::delete('/{id}', [VisitorLogController::class, 'destroy'])->name('visitor-logs.destroy')->where('id', '[0-9]+');
        Route::patch('/{id}/note', [VisitorLogController::class, 'updateNote'])->name('visitor-logs.update-note')->where('id', '[0-9]+');
        
        // Bulk operations
        Route::post('/bulk-delete', [VisitorLogController::class, 'bulkDelete'])->name('visitor-logs.bulk-delete');
    });


    Route::prefix('checkin')->name('checkin.')->group(function () {
    Route::get('/', [CheckinController::class, 'index'])->name('index');
    
    // Add the missing routes
    Route::get('/checkout', [CheckinController::class, 'checkout'])->name('checkout');
    Route::get('/scan-for-print', [CheckinController::class, 'scanForPrint'])->name('scan-for-print');
    
    // Processing routes
    Route::post('/scan', [CheckinController::class, 'scan'])->name('scan');
    Route::post('/manual', [CheckinController::class, 'manual'])->name('manual');
    Route::post('/verify-registration', [CheckinController::class, 'verifyRegistration'])->name('verify-registration');
    
    // Stats and analytics routes - ADD THESE
    Route::get('/stats', [CheckinController::class, 'getStats'])->name('stats');
    Route::get('/hourly-analytics', [CheckinController::class, 'getHourlyAnalytics'])->name('hourly-analytics');
    Route::post('/bulk-operation', [CheckinController::class, 'bulkOperation'])->name('bulk-operation');
    
    // History route
    Route::get('/history', [CheckinController::class, 'history'])->name('history');
    
    // Export route
    Route::get('/export', [CheckinController::class, 'export'])->name('export');
});