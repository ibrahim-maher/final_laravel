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
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;
use App\Models\Registration;
use App\Models\QRCode;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

// Landing page and public event browsing
Route::get('/', [PublicRegistrationController::class, 'index'])->name('home');

// Public event routes
Route::prefix('events')->name('public.events.')->group(function () {
    Route::get('/', [PublicRegistrationController::class, 'index'])->name('index');
    Route::get('/{event}', [PublicRegistrationController::class, 'show'])->name('show');
    Route::get('/{event}/register', [PublicRegistrationController::class, 'register'])->name('register');
    Route::post('/{event}/register', [PublicRegistrationController::class, 'store'])->name('store');
});

// Public registration routes (legacy support)
Route::get('register', [RegistrationController::class, 'publicRegister'])->name('registrations.public-register');
Route::post('register', [RegistrationController::class, 'publicRegister'])->name('registrations.public-register.store');
Route::match(['get', 'post'], 'register-for-event', [RegistrationController::class, 'publicRegister'])->name('registrations.public-register-alt');

// Registration success and QR download
Route::prefix('registration')->name('public.registration.')->group(function () {
    Route::get('/{registration}/success', [PublicRegistrationController::class, 'success'])->name('success');
    Route::get('/{registration}/download-qr', [PublicRegistrationController::class, 'downloadQR'])->name('download-qr');
    Route::get('/{registration}/qr-download', [PublicRegistrationController::class, 'downloadQR'])->name('qr-download');
});

Route::get('registration-success', [RegistrationController::class, 'registrationSuccess'])->name('registrations.success');

// Public API endpoints (no auth required)
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/events/{event}/tickets', [RegistrationController::class, 'getTickets'])->name('events.tickets');
    Route::get('/events/{event}/registration-fields', [RegistrationController::class, 'getRegistrationFields'])->name('events.registration-fields');
});

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
    
    /*
    |--------------------------------------------------------------------------
    | Dashboard and Profile
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    /*
    |--------------------------------------------------------------------------
    | Events Management Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('events', EventController::class);
    Route::prefix('events')->name('events.')->group(function () {
        Route::get('/{event}/analytics', [EventController::class, 'analytics'])->name('analytics');
        Route::post('/{event}/toggle-status', [EventController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{event}/duplicate', [EventController::class, 'duplicate'])->name('duplicate');
    });
    
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
        
        // Bulk actions and export
        Route::post('bulk-action', [RegistrationController::class, 'bulkAction'])->name('bulk-action');
        Route::get('export', [RegistrationController::class, 'export'])->name('export');
        Route::post('export-selected', [RegistrationController::class, 'exportSelected'])->name('export-selected');
        Route::post('import', [RegistrationController::class, 'import'])->name('import');
        
        // Search and individual actions
        Route::get('search', [RegistrationController::class, 'search'])->name('search');
        Route::get('public-register', [RegistrationController::class, 'publicRegister'])->name('public-register');
        Route::post('public-register', [RegistrationController::class, 'publicRegister'])->name('public-register.store');
        Route::get('success', [RegistrationController::class, 'registrationSuccess'])->name('success');
        Route::get('{registration}/badge', [RegistrationController::class, 'getBadge'])->name('badge');
        Route::get('{registration}/badge-download', [RegistrationController::class, 'downloadBadge'])->name('download-badge');
        Route::get('{registration}/qr-code', [RegistrationController::class, 'downloadQrCode'])->name('download-qr-code');
        Route::post('{registration}/resend-confirmation', [RegistrationController::class, 'resendConfirmation'])->name('resend-confirmation');
        
        // Badge printing and preview
        Route::get('{registration}/print-badge', [RegistrationController::class, 'printBadge'])->name('print-badge');
        Route::get('{registration}/preview-badge', [RegistrationController::class, 'previewBadge'])->name('preview-badge');
        Route::post('bulk-print-badges', [RegistrationController::class, 'bulkPrintBadges'])->name('bulk-print-badges');
        Route::post('print-multiple-badges', [RegistrationController::class, 'printMultipleBadges'])->name('print-multiple-badges');
        
        // Badge template checking and QR generation
        Route::post('check-badge-templates', [RegistrationController::class, 'checkBadgeTemplates'])->name('check-badge-templates');
        Route::post('generate-missing-qr-codes', [RegistrationController::class, 'generateMissingQrCodes'])->name('generate-missing-qr-codes');
        Route::post('generate-qr-codes', [RegistrationController::class, 'generateQrCodes'])->name('generate-qr-codes');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Badge Printing Controller Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('badge-printing')->name('badge-printing.')->group(function () {
        Route::get('/registrations/{registration}/print-badge', [BadgePrintController::class, 'printSingleBadge'])->name('print-single');
        Route::get('/registrations/print-badges', [BadgePrintController::class, 'printMultipleBadges'])->name('print-multiple');
        Route::post('/registrations/bulk-print-badges', [BadgePrintController::class, 'bulkPrintBadges'])->name('bulk-print');
        Route::get('/registrations/{registration}/preview-badge', [BadgePrintController::class, 'previewBadge'])->name('preview');
        Route::post('/registrations/generate-qr-codes', [BadgePrintController::class, 'generateMissingQrCodes'])->name('generate-qr-codes');
    });
    
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
    Route::resource('badge-templates', BadgeTemplateController::class);
    Route::prefix('badge-templates')->name('badge-templates.')->group(function () {
        // AJAX endpoints
        Route::get('/get-tickets', [BadgeTemplateController::class, 'getTickets'])->name('getTickets');
        Route::post('/content/{contentId}/update', [BadgeTemplateController::class, 'updateContent'])->name('updateContent');
        Route::post('/save-all-changes', [BadgeTemplateController::class, 'saveAllChanges'])->name('saveAllChanges');
        
        // Main routes
        Route::match(['GET', 'POST'], '/create-or-edit', [BadgeTemplateController::class, 'createOrEdit'])->name('createOrEdit');
        Route::get('/{badgeTemplate}/preview', [BadgeTemplateController::class, 'preview'])->name('preview');
    });
    
    // Badge Printing from Template Controller
    Route::get('/registrations/{registration}/print-badge-template', [BadgeTemplateController::class, 'printBadge'])->name('registrations.printBadge');
    
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
    
    /*
    |--------------------------------------------------------------------------
    | Authenticated API Routes for AJAX calls
    |--------------------------------------------------------------------------
    */
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/visitor-logs/realtime', [VisitorLogController::class, 'apiLogs'])->name('visitor-logs');
        Route::get('/registrations/{registration}/preview-badge', [RegistrationController::class, 'previewBadge'])->name('registrations.preview-badge');
        Route::post('/registrations/generate-missing-qr-codes', [RegistrationController::class, 'generateMissingQrCodes'])->name('registrations.generate-qr-codes');
        Route::get('events/{event}/tickets', [RegistrationController::class, 'getTickets'])->name('events.tickets');
        Route::get('events/{event}/registration-fields', [RegistrationController::class, 'getRegistrationFields'])->name('events.registration-fields');
    });
});

/*
|--------------------------------------------------------------------------
| Special Test Routes for Badge Check (Always Available)
|--------------------------------------------------------------------------
*/

// Simple badge check test route
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

/*
|--------------------------------------------------------------------------
| Debug and Test Routes (Only in Debug Mode)
|--------------------------------------------------------------------------
*/
if (config('app.debug')) {
    
    // Debug routes for public registration
    Route::get('/debug-events', [PublicRegistrationController::class, 'debug']);
    Route::get('/fix-event-times', [PublicRegistrationController::class, 'fixEventTimes']);
    
    // Badge debugging routes
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

    // Badge template checking route
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

    // Model and database checking route
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

    // Basic functionality test routes
    Route::get('/test-basic', function() {
        return 'Basic routing works!';
    });

    Route::get('/test-auth-status', function() {
        return response()->json([
            'authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email ?? 'Not logged in',
            'session_id' => session()->getId(),
            'csrf_token' => csrf_token(),
        ]);
    });

    Route::get('/test-event-controller', function() {
        try {
            $controller = new \App\Http\Controllers\EventController();
            
            $venues = \App\Models\Venue::all();
            $categories = \App\Models\Category::all();
            
            return response()->json([
                'controller_created' => true,
                'create_method_exists' => method_exists($controller, 'create'),
                'venues_count' => $venues->count(),
                'categories_count' => $categories->count(),
                'venue_model_exists' => class_exists('\App\Models\Venue'),
                'category_model_exists' => class_exists('\App\Models\Category'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    });

    Route::get('/test-view-exists', function() {
        try {
            $viewExists = view()->exists('events.create');
            $layoutExists = view()->exists('layouts.app');
            
            return response()->json([
                'events_create_view_exists' => $viewExists,
                'layouts_app_exists' => $layoutExists,
                'view_paths' => config('view.paths'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }
    });

    Route::get('/test-with-auth', function() {
        return response()->json([
            'message' => 'Auth middleware passed!',
            'user' => auth()->user(),
            'timestamp' => now(),
        ]);
    })->middleware('auth');

    Route::get('/events-create', [\App\Http\Controllers\EventController::class, 'create'])
        ->name('test.events.create')
        ->middleware('auth');

    Route::get('/test-events-create-no-auth', [\App\Http\Controllers\EventController::class, 'create'])
        ->name('test.events.create.noauth');

    Route::get('/test-events-create', function() {
        return 'Test route works - you are ' . (auth()->check() ? 'authenticated' : 'not authenticated');
    })->middleware('auth');

    Route::get('/check-dependencies', function() {
        $checks = [];
        
        // Check Models
        $checks['models'] = [
            'Event' => class_exists('\App\Models\Event'),
            'Venue' => class_exists('\App\Models\Venue'),
            'Category' => class_exists('\App\Models\Category'),
            'RegistrationField' => class_exists('\App\Models\RegistrationField'),
            'User' => class_exists('\App\Models\User'),
        ];
        
        // Check Controllers
        $checks['controllers'] = [
            'EventController' => class_exists('\App\Http\Controllers\EventController'),
            'EventController_create_method' => method_exists('\App\Http\Controllers\EventController', 'create'),
        ];
        
        // Check Database Tables
        try {
            $checks['database'] = [
                'venues_table' => \Schema::hasTable('venues'),
                'categories_table' => \Schema::hasTable('categories'),
                'events_table' => \Schema::hasTable('events'),
                'users_table' => \Schema::hasTable('users'),
            ];
            
            $checks['data_counts'] = [
                'venues_count' => \App\Models\Venue::count(),
                'categories_count' => \App\Models\Category::count(),
                'events_count' => \App\Models\Event::count(),
                'users_count' => \App\Models\User::count(),
            ];
        } catch (\Exception $e) {
            $checks['database_error'] = $e->getMessage();
        }
        
        // Check Views
        $checks['views'] = [
            'events_create_view' => view()->exists('events.create'),
            'layouts_app_view' => view()->exists('layouts.app'),
            'welcome_view' => view()->exists('welcome'),
        ];
        
        // Check Configuration
        $checks['config'] = [
            'app_debug' => config('app.debug'),
            'app_env' => config('app.env'),
            'auth_default_guard' => config('auth.defaults.guard'),
            'session_driver' => config('session.driver'),
        ];
        
        return response()->json($checks, 200, [], JSON_PRETTY_PRINT);
    });

    // QR Code Testing Routes
    Route::get('/test-qr', function () {
        try {
            // Test 1: Basic QR generation
            echo "<h2>Test 1: Basic QR Generation</h2>";
            $basicQR = QrCodeGenerator::size(200)->generate('Hello World');
            echo "Basic QR: " . $basicQR . "<br><br>";

            // Test 2: PNG format
            echo "<h2>Test 2: PNG Format</h2>";
            $pngQR = QrCodeGenerator::format('png')->size(200)->generate('Test PNG');
            $base64PNG = 'data:image/png;base64,' . base64_encode($pngQR);
            echo "<img src='{$base64PNG}' alt='Test QR'><br>";
            echo "PNG size: " . strlen($pngQR) . " bytes<br><br>";

            // Test 3: Registration QR
            echo "<h2>Test 3: Registration QR</h2>";
            $registration = Registration::with(['user', 'event', 'ticketType'])->first();
            
            if ($registration) {
                echo "Using registration ID: {$registration->id}<br>";
                echo "User: " . ($registration->user->name ?? 'N/A') . "<br>";
                echo "Event: " . ($registration->event->name ?? 'N/A') . "<br>";

                $qrData = [
                    'registration_id' => $registration->id,
                    'user_id' => $registration->user_id,
                    'event_id' => $registration->event_id,
                    'test' => true,
                    'timestamp' => now()->toISOString(),
                ];

                $regQR = QrCodeGenerator::format('png')->size(200)->generate(json_encode($qrData));
                $regBase64 = 'data:image/png;base64,' . base64_encode($regQR);
                echo "<img src='{$regBase64}' alt='Registration QR'><br>";

                // Try to save to database
                try {
                    $saved = QRCode::updateOrCreate(
                        ['registration_id' => $registration->id],
                        [
                            'ticket_type_id' => $registration->ticket_type_id,
                            'qr_image' => $regBase64,
                            'qr_data' => $qrData,
                        ]
                    );
                    echo "✅ Saved to database (ID: {$saved->id})<br>";
                } catch (\Exception $e) {
                    echo "❌ Save failed: " . $e->getMessage() . "<br>";
                }
            } else {
                echo "No registration found<br>";
            }

            // Test 4: Check database
            echo "<h2>Test 4: Database Check</h2>";
            $qrCodes = QRCode::with('registration')->get();
            echo "QR codes in database: " . $qrCodes->count() . "<br>";
            
            foreach ($qrCodes as $qr) {
                echo "QR ID: {$qr->id}, Registration: {$qr->registration_id}, Has Image: " . 
                     (!empty($qr->qr_image) ? 'Yes (' . strlen($qr->qr_image) . ' chars)' : 'No') . "<br>";
            }

        } catch (\Exception $e) {
            echo "ERROR: " . $e->getMessage() . "<br>";
            echo "Trace: " . $e->getTraceAsString();
        }
    });

    // Test individual registration QR generation
    Route::get('/test-registration-qr/{registration}', function (Registration $registration) {
        try {
            echo "<h2>Testing QR Generation for Registration #{$registration->id}</h2>";
            
            // Load relationships
            $registration->load(['user', 'event', 'ticketType']);
            
            echo "User: " . ($registration->user->name ?? 'N/A') . "<br>";
            echo "Event: " . ($registration->event->name ?? 'N/A') . "<br>";
            echo "Status: " . $registration->status . "<br><br>";

            // Generate QR using model method
            $result = $registration->generateQRCode();
            
            if ($result) {
                echo "✅ QR generation succeeded<br>";
                $registration->refresh();
                
                if ($registration->qrCode && $registration->qrCode->qr_image) {
                    echo "✅ QR code found in database<br>";
                    echo "<img src='{$registration->qrCode->qr_image}' alt='Generated QR' style='max-width: 300px;'><br>";
                } else {
                    echo "❌ QR code not found in database after generation<br>";
                }
            } else {
                echo "❌ QR generation failed<br>";
            }

        } catch (\Exception $e) {
            echo "ERROR: " . $e->getMessage() . "<br>";
            echo "Trace: " . $e->getTraceAsString();
        }
    });
}

/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});