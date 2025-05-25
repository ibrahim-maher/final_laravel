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
Route::middleware(['auth', 'verified'])->group(function () {
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
});

/*
|--------------------------------------------------------------------------
| Event Management Routes (Event Managers + Admins)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth',  'event.manager','admin'])->group(function () {
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
    
    // Badge Templates
    Route::resource('badge-templates', BadgeTemplateController::class);
    Route::get('/badge-templates/{badgeTemplate}/preview', [BadgeTemplateController::class, 'preview'])->name('badge-templates.preview');
    Route::post('/badge-templates/{badgeTemplate}/content', [BadgeTemplateController::class, 'addContent'])->name('badge-templates.add-content');
    Route::delete('/badge-templates/{badgeTemplate}/content/{badgeContent}', [BadgeTemplateController::class, 'removeContent'])->name('badge-templates.remove-content');
    
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
});

/*
|--------------------------------------------------------------------------
| Admin Only Routes (Venues, Categories, Users)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','admin'])->group(function () {
    // Venues Management (Admin Only)
    Route::resource('venues', VenueController::class);
    
    // Categories Management (Admin Only)
    Route::resource('categories', CategoryController::class);
    
    // Users Management (Admin Only)
    Route::resource('users', UserController::class);
});

require __DIR__.'/auth.php';