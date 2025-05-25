<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <script src="https://cdn.tailwindcss.com"></script>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Event Management') }} - @yield('title')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="bg-gradient-to-b from-blue-600 to-blue-800 w-64 min-h-screen shadow-lg">
            <div class="p-6">
                <h2 class="text-white text-xl font-bold">Event Management</h2>
                <p class="text-blue-200 text-sm">{{ auth()->user()->role ?? 'Guest' }}</p>
            </div>
            
            <nav class="mt-6">
                <div class="px-4 space-y-2">
                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('dashboard') ? 'bg-blue-700' : '' }} hover:bg-blue-700">
                        <i class="fas fa-tachometer-alt mr-3"></i>
                        Dashboard
                    </a>
                    <a href="{{ route('events.index') }}" class="flex items-center px-4 py-2 text-white rounded-lg {{ request()->routeIs('events.*') ? 'bg-blue-700' : '' }} hover:bg-blue-700">
                        <i class="fas fa-calendar-alt mr-3"></i>
                        Events
                    </a>
                    <a href="{{ route('venues.index') }}" class="flex items-center px-4 py-2 text-white rounded-lg {{ request()->routeIs('venues.*') ? 'bg-blue-700' : '' }} hover:bg-blue-700">
                        <i class="fas fa-map-marker-alt mr-3"></i>
                        Venues
                    </a>
                    <a href="{{ route('categories.index') }}" class="flex items-center px-4 py-2 text-white rounded-lg {{ request()->routeIs('categories.*') ? 'bg-blue-700' : '' }} hover:bg-blue-700">
                        <i class="fas fa-tags mr-3"></i>
                        Categories
                    </a>
                    <a href="{{ route('tickets.index') }}" class="flex items-center px-4 py-2 text-white rounded-lg {{ request()->routeIs('tickets.*') ? 'bg-blue-700' : '' }} hover:bg-blue-700">
                        <i class="fas fa-ticket-alt mr-3"></i>
                        Tickets
                    </a>
                      <a href="{{ route('badge-templates.index') }}" class="flex items-center px-4 py-2 text-white rounded-lg {{ request()->routeIs('badge-templates.*') ? 'bg-blue-700' : '' }} hover:bg-blue-700">
                        <i class="fas fa-id-badge mr-3"></i>
                        Badge Templates
                    </a>
        
                    <a href="{{ route('registrations.index') }}" class="flex items-center px-4 py-2 text-white rounded-lg {{ request()->routeIs('registrations.*') ? 'bg-blue-700' : '' }} hover:bg-blue-700">
                        <i class="fas fa-user-plus mr-3"></i>
                        Registrations
                    </a>
                    <a href="{{ route('checkin.index') }}" class="flex items-center px-4 py-2 text-white rounded-lg {{ request()->routeIs('checkin.*') ? 'bg-blue-700' : '' }} hover:bg-blue-700">
                        <i class="fas fa-qrcode mr-3"></i>
                        QR Scanner
                    </a>
                    <a href="{{ route('visitor-logs.index') }}" class="flex items-center px-4 py-2 text-white rounded-lg {{ request()->routeIs('visitor-logs.*') ? 'bg-blue-700' : '' }} hover:bg-blue-700">
                        <i class="fas fa-history mr-3"></i>
                        Visitor Logs
                    </a>
                    @if(auth()->user() && auth()->user()->isAdmin())
                    <a href="{{ route('users.index') }}" class="flex items-center px-4 py-2 text-white rounded-lg {{ request()->routeIs('users.*') ? 'bg-blue-700' : '' }} hover:bg-blue-700">
                        <i class="fas fa-users mr-3"></i>
                        All Users
                    </a>
                    @endif
                </div>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-6 py-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-700">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-700 hover:text-gray-900">
                                <i class="fas fa-sign-out-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </header>
            
            <!-- Main Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <!-- Alerts -->
                @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
                @endif
                
                @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
                @endif
                
                @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>