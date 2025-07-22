<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <!-- Animated Background -->
        <div class="fixed inset-0 -z-10 overflow-hidden">
            <!-- Gradient Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-indigo-900"></div>
            
            <!-- Animated Shapes -->
            <div class="absolute top-0 -left-4 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob dark:bg-purple-900 dark:opacity-30"></div>
            <div class="absolute top-0 -right-4 w-72 h-72 bg-yellow-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000 dark:bg-yellow-900 dark:opacity-30"></div>
            <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000 dark:bg-pink-900 dark:opacity-30"></div>
            
            <!-- Grid Pattern -->
            <div class="absolute inset-0 bg-grid-slate-100 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.6))] dark:bg-grid-slate-700/25 dark:[mask-image:linear-gradient(0deg,rgba(255,255,255,0.1),rgba(255,255,255,0.5))]"></div>
        </div>

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 relative">
            <!-- Logo Section -->
            <div class="mb-8 transform hover:scale-105 transition-transform duration-300">
                <a href="/" class="group flex flex-col items-center space-y-4">
                    <!-- Logo with glow effect -->
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full blur-2xl opacity-25 group-hover:opacity-40 transition-opacity duration-300"></div>
                        <x-application-logo class="relative w-20 h-20 fill-current text-indigo-600 dark:text-indigo-400 drop-shadow-lg" />
                    </div>
                    
                    <!-- App Name -->
                    <div class="text-center">
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-600 bg-clip-text text-transparent">
                            {{ config('app.name', 'Laravel') }}
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Welcome back to your dashboard</p>
                    </div>
                </a>
            </div>

            <!-- Main Card -->
            <div class="w-full sm:max-w-md">
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl overflow-hidden sm:rounded-2xl border border-white/20 dark:border-gray-700/50 relative">
                    <!-- Card header decoration -->
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
                    
                    <!-- Card content -->
                    <div class="px-8 py-8">
                        {{ $slot }}
                    </div>
                    
                    <!-- Card footer decoration -->
                    <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-gray-300 dark:via-gray-600 to-transparent"></div>
                </div>
                
                <!-- Additional Links/Footer -->
                <div class="mt-6 text-center">
                 
                    <p class="mt-4 text-xs text-gray-400 dark:text-gray-500">
                        © {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
                    </p>
                </div>
            </div>
        </div>

        <!-- Custom CSS for animations -->
        <style>
            @keyframes blob {
                0% {
                    transform: translate(0px, 0px) scale(1);
                }
                33% {
                    transform: translate(30px, -50px) scale(1.1);
                }
                66% {
                    transform: translate(-20px, 20px) scale(0.9);
                }
                100% {
                    transform: translate(0px, 0px) scale(1);
                }
            }
            
            .animate-blob {
                animation: blob 7s infinite;
            }
            
            .animation-delay-2000 {
                animation-delay: 2s;
            }
            
            .animation-delay-4000 {
                animation-delay: 4s;
            }
            
            .bg-grid-slate-100 {
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(148 163 184 / 0.05)'%3e%3cpath d='m0 .5h32m-32 8h32m-32 8h32m-32 8h32'/%3e%3cpath d='m.5 0v32m8-32v32m8-32v32m8-32v32'/%3e%3c/svg%3e");
            }
            
            .bg-grid-slate-700\/25 {
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(148 163 184 / 0.25)'%3e%3cpath d='m0 .5h32m-32 8h32m-32 8h32m-32 8h32'/%3e%3cpath d='m.5 0v32m8-32v32m8-32v32m8-32v32'/%3e%3c/svg%3e");
            }
        </style>
    </body>
</html>