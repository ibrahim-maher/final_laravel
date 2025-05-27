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
use App\Http\Controllers\BadgePrintController;


// Add these routes to your web.php file
Route::post('/test-simple-badge-check', function(\Illuminate\Http\Request $request) {
    try {
        \Log::info('=== SIMPLE BADGE CHECK TEST ===', [
            'request_data' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        // Basic validation
        if (!$request->has('registration_ids')) {
            return response()->json([
                'error' => 'registration_ids parameter missing',
                'received_data' => $request->all()
            ], 400);
        }

        $registrationIds = $request->registration_ids;
        
        if (!is_array($registrationIds)) {
            return response()->json([
                'error' => 'registration_ids must be an array',
                'received_type' => gettype($registrationIds),
                'received_data' => $registrationIds
            ], 400);
        }

        if (empty($registrationIds)) {
            return response()->json([
                'error' => 'registration_ids array is empty'
            ], 400);
        }

        // Check if registrations exist
        $registrations = \App\Models\Registration::whereIn('id', $registrationIds)->get();
        
        \Log::info('Found registrations', [
            'requested_ids' => $registrationIds,
            'found_count' => $registrations->count(),
            'found_ids' => $registrations->pluck('id')->toArray()
        ]);

        if ($registrations->isEmpty()) {
            return response()->json([
                'error' => 'No registrations found with provided IDs',
                'requested_ids' => $registrationIds,
                'total_registrations_in_db' => \App\Models\Registration::count()
            ], 404);
        }

        // Simple check without complex logic
        $results = [];
        foreach ($registrations as $registration) {
            $results[] = [
                'registration_id' => $registration->id,
                'has_user' => $registration->user_id !== null,
                'has_ticket_type' => $registration->ticket_type_id !== null,
                'user_name' => $registration->user->name ?? 'No user',
                'ticket_type_name' => $registration->ticketType->name ?? 'No ticket type'
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Simple test successful',
            'results' => $results,
            'debug_info' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'memory_usage' => memory_get_usage(true),
                'models_exist' => [
                    'Registration' => class_exists('App\Models\Registration'),
                    'BadgeTemplate' => class_exists('App\Models\BadgeTemplate'),
                    'BadgeContent' => class_exists('App\Models\BadgeContent')
                ]
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('=== SIMPLE BADGE CHECK TEST ERROR ===', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'request_data' => $request->all()
        ], 500);
    }
})->middleware('web');

// Basic Registration Routes
Route::resource('registrations', RegistrationController::class);

// Additional Registration Routes
Route::group(['prefix' => 'registrations'], function () {
    
    // Bulk actions
    Route::post('bulk-action', [RegistrationController::class, 'bulkAction'])
         ->name('registrations.bulk-action');
    
    // Export functionality
    Route::get('export', [RegistrationController::class, 'export'])
         ->name('registrations.export');
    
    Route::post('export-selected', [RegistrationController::class, 'exportSelected'])
         ->name('registrations.export-selected');
    
    // Import functionality
    Route::post('import', [RegistrationController::class, 'import'])
         ->name('registrations.import');
    
    // Badge printing routes
    Route::get('{registration}/print-badge', [RegistrationController::class, 'printBadge'])
         ->name('registrations.print-badge');
    
    Route::get('{registration}/preview-badge', [RegistrationController::class, 'previewBadge'])
         ->name('registrations.preview-badge');
    
    Route::post('bulk-print-badges', [RegistrationController::class, 'bulkPrintBadges'])
         ->name('registrations.bulk-print-badges');
    
    Route::post('print-multiple-badges', [RegistrationController::class, 'printMultipleBadges'])
         ->name('registrations.print-multiple-badges');
    
    // QR Code and template checking
    Route::post('generate-missing-qr-codes', [RegistrationController::class, 'generateMissingQrCodes'])
         ->name('registrations.generate-missing-qr-codes');
    
 
});

// Public registration routes
Route::get('register', [RegistrationController::class, 'publicRegister'])
     ->name('registrations.public-register');

Route::post('register', [RegistrationController::class, 'publicRegister'])
     ->name('registrations.public-register.store');

Route::get('registration-success', [RegistrationController::class, 'registrationSuccess'])
     ->name('registrations.success');

// API Routes for AJAX calls
Route::group(['prefix' => 'api'], function () {
    
    // Event-related API endpoints
    Route::get('events/{event}/tickets', [RegistrationController::class, 'getTickets'])
         ->name('api.events.tickets');
    
    Route::get('events/{event}/registration-fields', [RegistrationController::class, 'getRegistrationFields'])
         ->name('api.events.registration-fields');
    
    // Badge-related API endpoints
    Route::get('registrations/{registration}/preview-badge', [RegistrationController::class, 'previewBadge'])
         ->name('api.registrations.preview-badge');
    
    Route::post('registrations/generate-missing-qr-codes', [RegistrationController::class, 'generateMissingQrCodes'])
         ->name('api.registrations.generate-qr-codes');

});

// Debug routes (remove in production)
if (config('app.debug')) {
    Route::get('/debug-badge/{registration}', function (App\Models\Registration $registration) {
        try {
            $service = app(\App\Services\BadgePrintingService::class);
            $debugData = $service->debugRegistrationData($registration);
            
            return response()->json($debugData, 200, [], JSON_PRETTY_PRINT);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500, [], JSON_PRETTY_PRINT);
        }
    })->middleware('auth')->name('debug.badge');

    Route::get('/debug-badge-view/{registration}', function (App\Models\Registration $registration) {
        try {
            $service = app(\App\Services\BadgePrintingService::class);
            $debugData = $service->debugRegistrationData($registration);
            
            return view('debug.badge', compact('debugData', 'registration'));
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500, [], JSON_PRETTY_PRINT);
        }
    })->middleware('auth')->name('debug.badge.view');

    // Test routes
    Route::get('/test-badge-check', function() {
        try {
            $registrations = App\Models\Registration::with(['ticketType', 'qrCode', 'user'])
                                        ->limit(3)
                                        ->get();
            
            if ($registrations->isEmpty()) {
                return response()->json(['error' => 'No registrations found in database']);
            }

            $results = [];
            foreach ($registrations as $registration) {
                $hasTemplate = false;
                $templateInfo = null;
                
                if ($registration->ticket_type_id) {
                    $template = App\Models\BadgeTemplate::where('ticket_id', $registration->ticket_type_id)->first();
                    $hasTemplate = $template !== null;
                    $templateInfo = $template ? [
                        'id' => $template->id,
                        'name' => $template->name ?? 'Unnamed',
                        'ticket_id' => $template->ticket_id
                    ] : null;
                }
                
                $results[] = [
                    'registration_id' => $registration->id,
                    'user_name' => $registration->user->name ?? 'N/A',
                    'ticket_type_id' => $registration->ticket_type_id,
                    'ticket_type_name' => $registration->ticketType->name ?? 'N/A',
                    'has_qr_code' => $registration->qrCode !== null,
                    'has_template' => $hasTemplate,
                    'template_info' => $templateInfo
                ];
            }
            
            return response()->json([
                'success' => true,
                'results' => $results,
                'total_registrations' => App\Models\Registration::count(),
                'total_templates' => App\Models\BadgeTemplate::count()
            ], 200, [], JSON_PRETTY_PRINT);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500, [], JSON_PRETTY_PRINT);
        }
    })->middleware('auth');

    Route::get('/check-models', function() {
        $checks = [];
        
        // Check if models exist
        $checks['models'] = [
            'Registration' => class_exists('App\Models\Registration'),
            'BadgeTemplate' => class_exists('App\Models\BadgeTemplate'),
            'BadgeContent' => class_exists('App\Models\BadgeContent'),
            'QrCode' => class_exists('App\Models\QrCode'),
        ];
        
        // Check database tables
        try {
            $checks['tables'] = [
                'registrations' => \Schema::hasTable('registrations'),
                'badge_templates' => \Schema::hasTable('badge_templates'),
                'badge_contents' => \Schema::hasTable('badge_contents'),
                'qr_codes' => \Schema::hasTable('qr_codes'),
            ];
        } catch (\Exception $e) {
            $checks['tables_error'] = $e->getMessage();
        }
        
        // Check service
        $checks['service'] = [
            'BadgePrintingService' => class_exists('App\Services\BadgePrintingService'),
            'bound_in_container' => app()->bound(\App\Services\BadgePrintingService::class),
        ];
        
        // Check sample data
        try {
            $checks['data'] = [
                'registrations_count' => App\Models\Registration::count(),
                'badge_templates_count' => App\Models\BadgeTemplate::count(),
                'sample_registration' => App\Models\Registration::first() ? true : false,
            ];
        } catch (\Exception $e) {
            $checks['data_error'] = $e->getMessage();
        }
        
        return response()->json($checks, 200, [], JSON_PRETTY_PRINT);
    })->middleware('auth');
}

// Landing page and public event browsing
Route::get('/', [PublicRegistrationController::class, 'index'])->name('home');

// Public event registration routes
Route::post('/events/{event}/register', [PublicRegistrationController::class, 'store'])->name('public.events.store');
Route::get('/registration/{registration}/success', [PublicRegistrationController::class, 'success'])->name('public.registration.success');
Route::get('/registration/{registration}/download-qr', [PublicRegistrationController::class, 'downloadQR'])->name('public.registration.download-qr');

// Debug routes (remove in production)
Route::get('/debug-events', [PublicRegistrationController::class, 'debug']);
Route::get('/fix-event-times', [PublicRegistrationController::class, 'fixEventTimes']);





// API endpoints for dynamic functionality (optional)
Route::prefix('api')->group(function () {
    Route::get('/events/{event}/tickets', [RegistrationController::class, 'getTickets'])->name('api.events.tickets');
    Route::get('/events/{event}/registration-fields', [RegistrationController::class, 'getRegistrationFields'])->name('api.events.fields');
});
// API endpoints for AJAX functionality (optional)
Route::prefix('api')->group(function () {
    Route::get('/events/{event}/tickets', [RegistrationController::class, 'getTickets']);
    Route::get('/events/{event}/registration-fields', [RegistrationController::class, 'getRegistrationFields']);
});



Route::middleware(['auth'])->group(function () {
    Route::get('/registrations/{registration}/print-badge', [BadgePrintController::class, 'printSingleBadge'])
        ->name('registrations.print-badge');
    Route::get('/registrations/print-badges', [BadgePrintController::class, 'printMultipleBadges'])
        ->name('registrations.print-multiple-badges');
    Route::post('/registrations/bulk-print-badges', [BadgePrintController::class, 'bulkPrintBadges'])
        ->name('registrations.bulk-print-badges');
    Route::get('/registrations/{registration}/preview-badge', [BadgePrintController::class, 'previewBadge'])
        ->name('registrations.preview-badge');
   
    Route::post('/registrations/generate-qr-codes', [BadgePrintController::class, 'generateMissingQrCodes'])
        ->name('registrations.generate-qr-codes');


        
});

Route::prefix('registrations')->name('registrations.')->group(function () {
    // Check badge templates
    Route::post('/check-badge-templates', [RegistrationController::class, 'checkBadgeTemplates'])
        ->name('check-badge-templates');
    
  
    
    // Bulk print badges
    Route::post('/bulk-print-badges', [RegistrationController::class, 'bulkPrintBadges'])
        ->name('bulk-print-badges');
});

Route::prefix('events')->name('public.events.')->group(function () {
    Route::get('/', [PublicRegistrationController::class, 'index'])->name('index');
    Route::get('/{event}', [PublicRegistrationController::class, 'show'])->name('show');
    Route::get('/{event}/register', [PublicRegistrationController::class, 'register'])->name('register');
    Route::post('/{event}/register', [PublicRegistrationController::class, 'store'])->name('store');
});

// Registration success and utilities
Route::prefix('registration')->name('public.registration.')->group(function () {
    Route::get('/{registration}/success', [PublicRegistrationController::class, 'success'])->name('success');
    Route::get('/{registration}/download-qr', [PublicRegistrationController::class, 'downloadQR'])->name('download-qr');
});

// API endpoints for dynamic form loading
Route::prefix('api')->group(function () {
    Route::get('/events/{event}/tickets', [RegistrationController::class, 'getTickets']);
    Route::get('/events/{event}/registration-fields', [RegistrationController::class, 'getRegistrationFields']);
});

 Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
   
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
            
            // Import/Export routes
            Route::post('import', [RegistrationController::class, 'import'])->name('import');
            Route::post('export-selected', [RegistrationController::class, 'exportSelected'])->name('export-selected');
            
            // Badge printing
            Route::get('{registration}/print-badge', [RegistrationController::class, 'printBadge'])->name('print-badge');
            Route::post('bulk-print-badges', [RegistrationController::class, 'bulkPrintBadges'])->name('bulk-print-badges');
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

// Public registration routes (no auth required)
Route::match(['get', 'post'], 'register-for-event', [RegistrationController::class, 'publicRegister'])->name('registrations.public-register');
Route::get('registration-success', [RegistrationController::class, 'registrationSuccess'])->name('registrations.success');

/*
|--------------------------------------------------------------------------
| API Routes for AJAX calls
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    Route::get('/visitor-logs/realtime', [VisitorLogController::class, 'apiLogs'])->name('visitor-logs');
    
    // Add these new API routes for registration form
    Route::get('/events/{event}/tickets', [RegistrationController::class, 'getTickets'])->name('events.tickets');
    Route::get('/events/{event}/registration-fields', [RegistrationController::class, 'getRegistrationFields'])->name('events.registration-fields');
});

/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});


// Add this to your web.php temporarily
Route::get('/test-events-create', function() {
    return 'Test route works - you are ' . (auth()->check() ? 'authenticated' : 'not authenticated');
})->middleware('auth');


Route::prefix('registrations')->name('registrations.')->group(function () {
    Route::get('{registration}/print-badge', [RegistrationController::class, 'printBadge'])->name('print-badge');
    Route::get('{registration}/preview-badge', [RegistrationController::class, 'previewBadge'])->name('preview-badge');
    Route::post('bulk-print-badges', [RegistrationController::class, 'bulkPrintBadges'])->name('bulk-print-badges');
});
