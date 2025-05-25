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

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicRegistrationController::class, 'index'])->name('public.events.index');
Route::get('/public/events/{event}', [PublicRegistrationController::class, 'show'])->name('public.events.show');
Route::get('/public/events/{event}/register', [PublicRegistrationController::class, 'register'])->name('public.events.register');
Route::post('/public/events/{event}/register', [PublicRegistrationController::class, 'store'])->name('public.events.register.store');
Route::get('/registration/{registration}/success', [PublicRegistrationController::class, 'success'])->name('public.registration.success');

/*
|--------------------------------------------------------------------------
| Authenticated Routes (All Users)
|--------------------------------------------------------------------------
*/

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Events - Read Access for All Authenticated Users
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
    
    // Check-in System - All authenticated users
    Route::get('/checkin', [CheckinController::class, 'index'])->name('checkin.index');
    Route::post('/checkin/scan', [CheckinController::class, 'scan'])->name('checkin.scan');
    
    // Visitor Logs - All authenticated users
    Route::resource('visitor-logs', VisitorLogController::class)->only(['index', 'show']);
    Route::get('/visitor-logs/export', [VisitorLogController::class, 'export'])->name('visitor-logs.export');
    
    // Public registration - All authenticated users
    Route::get('/register-event', [RegistrationController::class, 'publicCreate'])->name('registration.public');
    Route::post('/register-event', [RegistrationController::class, 'publicStore'])->name('registration.public.store');
    
    // QR Code downloads - All authenticated users
    Route::get('/registration/{registration}/qr-download', [PublicRegistrationController::class, 'downloadQR'])->name('registration.qr-download');


/*
|--------------------------------------------------------------------------
| Event Management Routes (Event Managers + Admins)
|--------------------------------------------------------------------------
*/
    // Events Management
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::patch('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    
    // Tickets Management
    Route::resource('tickets', TicketController::class);
    
    // Registrations Management
    Route::resource('registrations', RegistrationController::class);
    Route::get('/events/{event}/tickets', [RegistrationController::class, 'getTickets'])->name('events.tickets');
    
    // ===== BADGE TEMPLATES - FIXED ORDER =====
    // IMPORTANT: Specific routes MUST come BEFORE resource routes!
    
    // AJAX route to get tickets by event - MUST BE FIRST
    Route::get('/badge-templates/get-tickets', [BadgeTemplateController::class, 'getTickets'])
        ->name('badge-templates.getTickets');
    
    // Create or edit template with ticket parameter
    Route::match(['GET', 'POST'], '/badge-templates/create-or-edit', [BadgeTemplateController::class, 'createOrEdit'])
        ->name('badge-templates.createOrEdit');
    
    // Update content via AJAX
    Route::post('/badge-templates/content/{contentId}/update', [BadgeTemplateController::class, 'updateContent'])
        ->name('badge-templates.updateContent');
    
    // Save all changes via AJAX
    Route::post('/badge-templates/save-all-changes', [BadgeTemplateController::class, 'saveAllChanges'])
        ->name('badge-templates.saveAllChanges');
    
    // Preview template - specific route before resource
    Route::get('/badge-templates/{badgeTemplate}/preview', [BadgeTemplateController::class, 'preview'])
        ->name('badge-templates.preview');
    
    // Badge Printing Routes
    Route::get('/registrations/{registration}/print-badge', [BadgeTemplateController::class, 'printBadge'])
        ->name('registrations.printBadge');
    
    // Badge Templates Resource Routes - MUST BE LAST
    Route::resource('badge-templates', BadgeTemplateController::class);
    
    // Registration Fields Management
    Route::get('/events/{event}/registration-fields', [RegistrationFieldController::class, 'index'])->name('registration-fields.index');
    Route::post('/events/{event}/registration-fields', [RegistrationFieldController::class, 'store'])->name('registration-fields.store');
    Route::put('/events/{event}/registration-fields/{registrationField}', [RegistrationFieldController::class, 'update'])->name('registration-fields.update');
    Route::delete('/events/{event}/registration-fields/{registrationField}', [RegistrationFieldController::class, 'destroy'])->name('registration-fields.destroy');
    Route::post('/events/{event}/registration-fields/reorder', [RegistrationFieldController::class, 'reorder'])->name('registration-fields.reorder');
    


    
    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/events', [ReportController::class, 'events'])->name('reports.events');
    Route::get('/reports/registrations', [ReportController::class, 'registrations'])->name('reports.registrations');
    Route::get('/reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

/*
|--------------------------------------------------------------------------
| Admin Only Routes (Venues, Categories, Users)
|--------------------------------------------------------------------------
*/

    // Venues Management (Admin Only)
    Route::resource('venues', VenueController::class);
    
    // Categories Management (Admin Only)
    Route::resource('categories', CategoryController::class);
    
    // Users Management (Admin Only)
    Route::resource('users', UserController::class);




    Route::get('/', [RegistrationController::class, 'index'])->name('registrations.index');
    Route::get('create', [RegistrationController::class, 'create'])->name('registrations.create');
    Route::post('/', [RegistrationController::class, 'store'])->name('registrations.store');
    Route::get('{registration}', [RegistrationController::class, 'show'])->name('registrations.show');
    Route::get('{registration}/edit', [RegistrationController::class, 'edit'])->name('registrations.edit');
    Route::put('{registration}', [RegistrationController::class, 'update'])->name('registrations.update');
    Route::delete('{registration_id}', [RegistrationController::class, 'destroy'])->name('registrations.destroy');
    Route::get('tickets/{event_id}', [RegistrationController::class, 'getTickets'])->name('registrations.tickets');
    Route::get('fields/{event_id}', [RegistrationController::class, 'getRegistrationFields'])->name('registrations.fields');
    Route::get('export', [RegistrationController::class, 'export'])->name('registrations.export');
    Route::post('import', [RegistrationController::class, 'import'])->name('registrations.import');
    Route::get('{registration}/qr-code', [RegistrationController::class, 'downloadQrCode'])->name('registrations.download_qr_code');
    Route::get('{registration}/badge', [RegistrationController::class, 'getBadge'])->name('registrations.get_badge');
    Route::get('{registration}/badge-download', [RegistrationController::class, 'downloadBadge'])->name('registrations.download_badge');
    Route::post('/events/{event}/registration-fields/import', [RegistrationFieldController::class, 'import'])
        ->name('registration-fields.import');
    Route::post('registrations/bulk-action', [RegistrationController::class, 'bulkAction'])
    ->name('registrations.bulk-action');

// User Authentication Routes
             
require __DIR__.'/auth.php';