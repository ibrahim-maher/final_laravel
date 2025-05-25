<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upcoming Events - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-900">{{ config('app.name') }}</h1>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-gray-900">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-700 hover:text-gray-900">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900">Login</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="bg-blue-600 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl font-bold mb-4">Upcoming Events</h1>
            <p class="text-xl">Discover and register for amazing events</p>
        </div>
    </div>

    <!-- Events -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if($events->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($events as $event)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                @if($event->logo)
                <img src="{{ Storage::url($event->logo) }}" alt="{{ $event->name }}" class="w-full h-48 object-cover">
                @else
                <div class="w-full h-48 bg-gradient-to-r from-blue-400 to-purple-500 flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-4xl text-white"></i>
                </div>
                @endif
                
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $event->name }}</h3>
                    <p class="text-gray-600 mb-4">{{ Str::limit($event->description, 100) }}</p>
                    
                    <div class="space-y-2 text-sm text-gray-500 mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-calendar mr-2"></i>
                            {{ $event->start_date->format('M d, Y H:i') }}
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            {{ $event->venue->name }}
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-tag mr-2"></i>
                            {{ $event->category->name }}
                        </div>
                    </div>
                    
                    @if($event->tickets->count() > 0)
                    <div class="mb-4">
                        <p class="text-sm text-gray-500">Starting from:</p>
                        <p class="text-2xl font-bold text-blue-600">
                            @if($event->tickets->min('price') == 0)
                                Free
                            @else
                                ${{ number_format($event->tickets->min('price'), 2) }}
                            @endif
                        </p>
                    </div>
                    @endif
                    
                    <div class="flex justify-between items-center">
                        <a href="{{ route('public.events.show', $event) }}" class="text-blue-600 hover:text-blue-800">
                            Learn More
                        </a>
                        <a href="{{ route('public.events.register', $event) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            Register Now
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12">
            <i class="fas fa-calendar-alt text-6xl text-gray-400 mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">No Upcoming Events</h2>
            <p class="text-gray-600">Check back soon for new events!</p>
        </div>
        @endif
    </div>
</body>
</html>