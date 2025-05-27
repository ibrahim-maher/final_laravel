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
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Enhanced Sidebar -->
        <div class="bg-gradient-to-b from-slate-900 to-slate-800 w-72 min-h-screen shadow-2xl flex flex-col" id="sidebar">
            <!-- Logo Section -->
            <div class="p-6 border-b border-slate-700">
                
            </div>
            
            <!-- User Profile Section -->
            <div class="px-6 py-4 border-b border-slate-700">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-lg">{{ auth()->user()->name ? substr(auth()->user()->name, 0, 1) : 'U' }}</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-white font-medium">{{ auth()->user()->name }}</p>
                        <p class="text-slate-400 text-sm">{{ auth()->user()->email }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Main Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" class="group flex items-center px-4 py-3 text-white rounded-xl {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg' : 'hover:bg-slate-700' }} transition-all duration-200">
                    <i class="fas fa-tachometer-alt mr-3 w-5 text-center {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
                    <span class="font-medium">Dashboard</span>
                    @if(request()->routeIs('dashboard'))
                        <i class="fas fa-chevron-right ml-auto text-xs"></i>
                    @endif
                </a>
                
                <!-- Events Section -->
                <div class="pt-4">
                    <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Events Management</p>
                </div>
                
                <a href="{{ route('events.index') }}" class="group flex items-center px-4 py-3 text-white rounded-xl {{ request()->routeIs('events.*') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg' : 'hover:bg-slate-700' }} transition-all duration-200">
                    <i class="fas fa-calendar-alt mr-3 w-5 text-center {{ request()->routeIs('events.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
                    <span class="font-medium">Events</span>
                    <span class="ml-auto bg-slate-600 text-xs px-2 py-1 rounded-full">{{ \App\Models\Event::count() }}</span>
                </a>
                
                <a href="{{ route('venues.index') }}" class="group flex items-center px-4 py-3 text-white rounded-xl {{ request()->routeIs('venues.*') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg' : 'hover:bg-slate-700' }} transition-all duration-200">
                    <i class="fas fa-map-marker-alt mr-3 w-5 text-center {{ request()->routeIs('venues.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
                    <span class="font-medium">Venues</span>
                </a>
                
                <a href="{{ route('categories.index') }}" class="group flex items-center px-4 py-3 text-white rounded-xl {{ request()->routeIs('categories.*') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg' : 'hover:bg-slate-700' }} transition-all duration-200">
                    <i class="fas fa-tags mr-3 w-5 text-center {{ request()->routeIs('categories.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
                    <span class="font-medium">Categories</span>
                </a>
                
                <a href="{{ route('tickets.index') }}" class="group flex items-center px-4 py-3 text-white rounded-xl {{ request()->routeIs('tickets.*') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg' : 'hover:bg-slate-700' }} transition-all duration-200">
                    <i class="fas fa-ticket-alt mr-3 w-5 text-center {{ request()->routeIs('tickets.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
                    <span class="font-medium">Tickets</span>
                </a>
                
                <a href="{{ route('badge-templates.index') }}" class="group flex items-center px-4 py-3 text-white rounded-xl {{ request()->routeIs('badge-templates.*') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg' : 'hover:bg-slate-700' }} transition-all duration-200">
                    <i class="fas fa-id-badge mr-3 w-5 text-center {{ request()->routeIs('badge-templates.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
                    <span class="font-medium">Badge Templates</span>
                </a>
                
                <!-- Registration & Check-in Section -->
                <div class="pt-4">
                    <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Registration & Check-in</p>
                </div>
                
                <a href="{{ route('registrations.index') }}" class="group flex items-center px-4 py-3 text-white rounded-xl {{ request()->routeIs('registrations.*') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg' : 'hover:bg-slate-700' }} transition-all duration-200">
                    <i class="fas fa-user-plus mr-3 w-5 text-center {{ request()->routeIs('registrations.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
                    <span class="font-medium">Registrations</span>
                    <span class="ml-auto bg-green-600 text-xs px-2 py-1 rounded-full">{{ \App\Models\Registration::whereDate('created_at', today())->count() }} today</span>
                </a>
                
                <a href="{{ route('checkin.index') }}" class="group flex items-center px-4 py-3 text-white rounded-xl {{ request()->routeIs('checkin.index') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg' : 'hover:bg-slate-700' }} transition-all duration-200">
                    <i class="fas fa-qrcode mr-3 w-5 text-center {{ request()->routeIs('checkin.index') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
                    <span class="font-medium">QR Check-in</span>
                    <div class="ml-auto w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                </a>
                
                <a href="{{ route('checkin.checkout') }}" class="group flex items-center px-4 py-3 text-white rounded-xl {{ request()->routeIs('checkin.checkout') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg' : 'hover:bg-slate-700' }} transition-all duration-200">
                    <i class="fas fa-sign-out-alt mr-3 w-5 text-center {{ request()->routeIs('checkin.checkout') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
                    <span class="font-medium">Check-out</span>
                </a>
                
                <a href="{{ route('checkin.scan-for-print') }}" class="group flex items-center px-4 py-3 text-white rounded-xl {{ request()->routeIs('checkin.scan-for-print') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg' : 'hover:bg-slate-700' }} transition-all duration-200">
                    <i class="fas fa-print mr-3 w-5 text-center {{ request()->routeIs('checkin.scan-for-print') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
                    <span class="font-medium">Scan & Print Badge</span>
                </a>
                
                <!-- Analytics Section -->
                <div class="pt-4">
                    <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Analytics & Reports</p>
                </div>
                
                <a href="{{ route('visitor-logs.index') }}" class="group flex items-center px-4 py-3 text-white rounded-xl {{ request()->routeIs('visitor-logs.*') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg' : 'hover:bg-slate-700' }} transition-all duration-200">
                    <i class="fas fa-history mr-3 w-5 text-center {{ request()->routeIs('visitor-logs.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
                    <span class="font-medium">Visitor Logs</span>
                </a>
                
                <a href="{{ route('visitor-logs.realtime') }}" class="group flex items-center px-4 py-3 text-white rounded-xl {{ request()->routeIs('visitor-logs.realtime') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg' : 'hover:bg-slate-700' }} transition-all duration-200">
                    <i class="fas fa-stream mr-3 w-5 text-center {{ request()->routeIs('visitor-logs.realtime') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
                    <span class="font-medium">Real-time Monitor</span>
                    <div class="ml-auto w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                </a>
                
                <a href="{{ route('visitor-logs.analytics') }}" class="group flex items-center px-4 py-3 text-white rounded-xl {{ request()->routeIs('visitor-logs.analytics') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg' : 'hover:bg-slate-700' }} transition-all duration-200">
                    <i class="fas fa-chart-line mr-3 w-5 text-center {{ request()->routeIs('visitor-logs.analytics') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
                    <span class="font-medium">Analytics Dashboard</span>
                </a>
                
          
                
                <!-- Administration Section -->
                @if(auth()->user() && auth()->user()->isAdmin())
                <div class="pt-4">
                    <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Administration</p>
                </div>
                
                <a href="{{ route('users.index') }}" class="group flex items-center px-4 py-3 text-white rounded-xl {{ request()->routeIs('users.*') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg' : 'hover:bg-slate-700' }} transition-all duration-200">
                    <i class="fas fa-users mr-3 w-5 text-center {{ request()->routeIs('users.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
                    <span class="font-medium">User Management</span>
                </a>
                
               
                @endif
            </nav>
            
            <!-- Bottom Section -->
            <div class="p-4 border-t border-slate-700">
                <!-- Quick Stats -->
                <div class="bg-slate-700/50 rounded-xl p-4 mb-4">
                    <p class="text-xs text-slate-400 mb-2">Active Visitors</p>
                    <p class="text-2xl font-bold text-white">{{ \App\Models\VisitorLog::getActiveVisitorsCount() }}</p>
                    <p class="text-xs text-green-400 mt-1">
                        <i class="fas fa-arrow-up mr-1"></i>
                        12% from yesterday
                    </p>
                </div>
                
                <!-- User Actions -->
                <div class="space-y-2">
                   
                    
                   
                    
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-4 py-2 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center">
                        <!-- Mobile Menu Toggle -->
                        <button class="lg:hidden mr-4 text-gray-600 hover:text-gray-900" onclick="toggleSidebar()">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                            <p class="text-sm text-gray-500 mt-1">{{ now()->format('l, F j, Y') }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                    
                       
                        
                    </div>
                </div>
            </header>
            
            <!-- Main Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
                <!-- Alerts -->
                @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg animate-fade-in">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <p class="text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
                @endif
                
                @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg animate-fade-in">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <p class="text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
                @endif
                
                @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg animate-fade-in">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-3 mt-1"></i>
                        <div>
                            <p class="text-red-700 font-semibold mb-2">There were some errors:</p>
                            <ul class="list-disc list-inside text-red-600 text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- Include Alpine.js for dropdown functionality -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script>
        // Toggle sidebar for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.animate-fade-in');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
    
    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
        
        /* Custom scrollbar for sidebar */
        #sidebar nav::-webkit-scrollbar {
            width: 6px;
        }
        
        #sidebar nav::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        
        #sidebar nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }
        
        #sidebar nav::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>
    
    @stack('scripts')
</body>
</html>