<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Event Registration - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                        'display': ['Space Grotesk', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 8s ease-in-out infinite',
                        'float-delayed': 'float 8s ease-in-out infinite 2s',
                        'float-slow': 'float 12s ease-in-out infinite 4s',
                        'glow': 'glow 3s ease-in-out infinite alternate',
                        'glow-pulse': 'glowPulse 2s ease-in-out infinite',
                        'slide-up': 'slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1)',
                        'slide-down': 'slideDown 0.6s cubic-bezier(0.16, 1, 0.3, 1)',
                        'fade-in': 'fadeIn 1s ease-out',
                        'fade-in-up': 'fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1)',
                        'bounce-gentle': 'bounceGentle 3s infinite',
                        'shimmer': 'shimmer 3s linear infinite',
                        'shimmer-fast': 'shimmer 1.5s linear infinite',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'spin-slow': 'spin 3s linear infinite',
                        'wiggle': 'wiggle 1s ease-in-out infinite',
                        'scale-in': 'scaleIn 0.5s cubic-bezier(0.16, 1, 0.3, 1)',
                        'morph': 'morph 8s ease-in-out infinite',
                        'gradient-shift': 'gradientShift 6s ease-in-out infinite',
                        'particle-float': 'particleFloat 20s linear infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px) rotate(0deg)' },
                            '33%': { transform: 'translateY(-15px) rotate(1deg)' },
                            '66%': { transform: 'translateY(-8px) rotate(-1deg)' }
                        },
                        glow: {
                            'from': { boxShadow: '0 0 30px rgba(59, 130, 246, 0.5), 0 0 60px rgba(147, 51, 234, 0.3)' },
                            'to': { boxShadow: '0 0 40px rgba(139, 92, 246, 0.6), 0 0 80px rgba(59, 130, 246, 0.4)' }
                        },
                        glowPulse: {
                            '0%, 100%': { 
                                boxShadow: '0 0 20px rgba(59, 130, 246, 0.4), 0 0 40px rgba(147, 51, 234, 0.2)',
                                transform: 'scale(1)'
                            },
                            '50%': { 
                                boxShadow: '0 0 30px rgba(139, 92, 246, 0.6), 0 0 60px rgba(59, 130, 246, 0.4)',
                                transform: 'scale(1.02)'
                            }
                        },
                        slideUp: {
                            'from': { transform: 'translateY(50px)', opacity: '0' },
                            'to': { transform: 'translateY(0)', opacity: '1' }
                        },
                        slideDown: {
                            'from': { transform: 'translateY(-30px)', opacity: '0' },
                            'to': { transform: 'translateY(0)', opacity: '1' }
                        },
                        fadeIn: {
                            'from': { opacity: '0' },
                            'to': { opacity: '1' }
                        },
                        fadeInUp: {
                            'from': { transform: 'translateY(30px)', opacity: '0' },
                            'to': { transform: 'translateY(0)', opacity: '1' }
                        },
                        bounceGentle: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-8px)' }
                        },
                        shimmer: {
                            '0%': { backgroundPosition: '-200% 0' },
                            '100%': { backgroundPosition: '200% 0' }
                        },
                        wiggle: {
                            '0%, 100%': { transform: 'rotate(-2deg)' },
                            '50%': { transform: 'rotate(2deg)' }
                        },
                        scaleIn: {
                            'from': { transform: 'scale(0.9)', opacity: '0' },
                            'to': { transform: 'scale(1)', opacity: '1' }
                        },
                        morph: {
                            '0%, 100%': { borderRadius: '60% 40% 30% 70% / 60% 30% 70% 40%' },
                            '50%': { borderRadius: '30% 60% 70% 40% / 50% 60% 30% 60%' }
                        },
                        gradientShift: {
                            '0%, 100%': { backgroundPosition: '0% 50%' },
                            '50%': { backgroundPosition: '100% 50%' }
                        },
                        particleFloat: {
                            '0%': { transform: 'translateY(100vh) translateX(0px)', opacity: '0' },
                            '10%': { opacity: '1' },
                            '90%': { opacity: '1' },
                            '100%': { transform: 'translateY(-100px) translateX(100px)', opacity: '0' }
                        }
                    },
                    backgroundImage: {
                        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
                        'gradient-conic': 'conic-gradient(from 180deg at 50% 50%, var(--tw-gradient-stops))',
                        'shimmer': 'linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent)',
                        'mesh-gradient': 'radial-gradient(at 40% 20%, hsla(28,100%,74%,1) 0px, transparent 50%), radial-gradient(at 80% 0%, hsla(189,100%,56%,1) 0px, transparent 50%), radial-gradient(at 0% 50%, hsla(355,100%,93%,1) 0px, transparent 50%), radial-gradient(at 80% 50%, hsla(340,100%,76%,1) 0px, transparent 50%), radial-gradient(at 0% 100%, hsla(22,100%,77%,1) 0px, transparent 50%), radial-gradient(at 80% 100%, hsla(242,100%,70%,1) 0px, transparent 50%), radial-gradient(at 0% 0%, hsla(343,100%,76%,1) 0px, transparent 50%)',
                    },
                    backgroundSize: {
                        '300%': '300% 300%',
                    },
                    dropShadow: {
                        'glow': '0 0 20px rgba(59, 130, 246, 0.5)',
                        'glow-purple': '0 0 20px rgba(147, 51, 234, 0.5)',
                    }
                }
            }
        }
    </script>
    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .glass-effect-strong {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        }
        
        .shimmer-effect {
            background-image: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            background-size: 200% 100%;
            animation: shimmer 3s linear infinite;
        }
        
        .hero-mesh {
            background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
            background-size: 300% 300%;
            animation: gradientShift 8s ease-in-out infinite;
        }
        
        .card-hover {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 32px 64px -12px rgba(0, 0, 0, 0.3);
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            background-size: 200% 200%;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .btn-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }
        
        .btn-gradient:hover {
            background-position: 100% 100%;
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }
        
        .btn-gradient:hover::before {
            left: 100%;
        }
        
        .floating-particle {
            position: absolute;
            pointer-events: none;
            border-radius: 50%;
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4, #feca57);
            animation: particleFloat 20s linear infinite;
        }
        
        .text-shadow {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }
        
        .input-glow:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3), 0 0 20px rgba(59, 130, 246, 0.2);
        }
        
        .morphing-bg {
            animation: morph 8s ease-in-out infinite;
        }
        
        .gradient-text {
            background: linear-gradient(45deg, #667eea, #764ba2, #f093fb);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradientShift 6s ease-in-out infinite;
        }
        
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        
        .ticket-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform-origin: center;
        }
        
        .ticket-card:hover {
            transform: translateY(-4px) scale(1.02);
        }
        
        .pulse-ring {
            position: absolute;
            border: 3px solid rgba(59, 130, 246, 0.3);
            border-radius: 50%;
            animation: pulseRing 2s cubic-bezier(0.455, 0.03, 0.515, 0.955) infinite;
        }
        
        @keyframes pulseRing {
            0% {
                transform: scale(0.33);
                opacity: 1;
            }
            80%, 100% {
                transform: scale(1.33);
                opacity: 0;
            }
        }
        
        .neon-border {
            border: 2px solid transparent;
            background: linear-gradient(45deg, #667eea, #764ba2) border-box;
            -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: destination-out;
            mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
            mask-composite: exclude;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 min-h-screen font-sans overflow-x-hidden">
    <!-- Enhanced Animated Background -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <!-- Primary floating elements -->
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float morphing-bg"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float-delayed morphing-bg"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float-slow morphing-bg"></div>
        
        <!-- Secondary accent elements -->
        <div class="absolute top-1/4 right-1/4 w-32 h-32 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full mix-blend-multiply filter blur-2xl opacity-30 animate-bounce-gentle"></div>
        <div class="absolute bottom-1/4 left-1/4 w-24 h-24 bg-gradient-to-br from-red-400 to-pink-500 rounded-full mix-blend-multiply filter blur-2xl opacity-25 animate-pulse-slow"></div>
        
        <!-- Floating particles -->
        <div class="floating-particle" style="width: 4px; height: 4px; left: 10%; animation-delay: 0s; animation-duration: 25s;"></div>
        <div class="floating-particle" style="width: 6px; height: 6px; left: 20%; animation-delay: 5s; animation-duration: 30s;"></div>
        <div class="floating-particle" style="width: 3px; height: 3px; left: 80%; animation-delay: 10s; animation-duration: 20s;"></div>
        <div class="floating-particle" style="width: 5px; height: 5px; left: 70%; animation-delay: 15s; animation-duration: 35s;"></div>
    </div>

    <!-- Global Messages with Enhanced Design -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 animate-slide-down">
            <div class="glass-effect-strong rounded-2xl p-6 mb-6 border-l-4 border-green-400 animate-glow-pulse">
                <div class="flex items-center">
                    <div class="relative">
                        <div class="w-12 h-12 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-check text-white text-lg"></i>
                        </div>
                        <div class="pulse-ring"></div>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-white font-semibold text-lg">Success!</h3>
                        <p class="text-white/80">{{ session('success') }}</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-white/60 hover:text-white transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 animate-slide-down">
            <div class="glass-effect-strong rounded-2xl p-6 mb-6 border-l-4 border-red-400 animate-glow-pulse">
                <div class="flex items-center">
                    <div class="relative">
                        <div class="w-12 h-12 bg-gradient-to-r from-red-400 to-pink-500 rounded-full flex items-center justify-center animate-wiggle">
                            <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-white font-semibold text-lg">Error</h3>
                        <p class="text-white/80">{{ session('error') }}</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-white/60 hover:text-white transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Events Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        @if(isset($availableEvents) && $availableEvents->count() > 0)
            
            <div class="space-y-20">
                @foreach($availableEvents as $event)
                <div class="group card-hover glass-effect-strong rounded-3xl overflow-hidden shadow-2xl border border-white/30 neon-border" id="event-{{ $event->id }}">
                    <!-- Enhanced Event Header -->
                    <div class="relative h-96 overflow-hidden">
                        <div class="absolute inset-0 hero-mesh">
                            @if($event->logo)
                            <img src="{{ Storage::url($event->logo) }}" alt="{{ $event->name }}" class="w-full h-full object-cover mix-blend-overlay opacity-80">
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                        </div>
                        
                        <!-- Enhanced Floating Badge -->
                        <div class="absolute top-8 right-8 animate-bounce-gentle">
                            <div class="glass-effect px-6 py-3 rounded-full text-white font-medium backdrop-blur-md">
                                <i class="fas fa-star mr-2 text-yellow-400 animate-pulse"></i>
                                <span class="gradient-text font-display font-bold">Featured Event</span>
                            </div>
                        </div>
                        
                        <!-- Countdown Timer (if event is upcoming) -->
                        @if($event->start_date->isFuture())
                        <div class="absolute top-8 left-8">
                            <div class="glass-effect px-6 py-3 rounded-full text-white backdrop-blur-md">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-clock text-blue-400"></i>
                                    <span class="font-mono font-bold countdown-timer" data-target="{{ $event->start_date->toISOString() }}">
                                        Loading...
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="absolute inset-0 flex items-center justify-center text-center p-8">
                            <div class="animate-fade-in-up max-w-4xl">
                                <h2 class="text-6xl md:text-7xl font-display font-bold text-white mb-8 group-hover:scale-105 transition-transform duration-500 text-shadow leading-tight">
                                    {{ $event->name }}
                                </h2>
                                <p class="text-2xl text-white/90 max-w-3xl mx-auto leading-relaxed font-light">
                                    {{ Str::limit($event->description ?? '', 150) }}
                                </p>
                                
                                <!-- Quick stats -->
                                <div class="flex items-center justify-center space-x-8 mt-8">
                                    <div class="flex items-center space-x-2 text-white/80">
                                        <i class="fas fa-users"></i>
                                        <span class="font-medium">
                                            @php
                                                $totalRegistrations = \App\Models\Registration::whereIn('ticket_type_id', $event->tickets->pluck('id'))->where('status', '!=', 'cancelled')->count();
                                            @endphp
                                            {{ $totalRegistrations }} Registered
                                        </span>
                                    </div>
                                    <div class="flex items-center space-x-2 text-white/80">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span class="font-medium">{{ $event->venue->name ?? 'TBD' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Enhanced Animated Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/30 to-purple-500/30 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                        
                        <!-- Shimmer effect -->
                        <div class="absolute inset-0 shimmer-effect opacity-0 group-hover:opacity-30 transition-opacity duration-500"></div>
                    </div>
                    
                    <!-- Enhanced Event Content -->
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-12 p-12">
                        <!-- Event Information -->
                        <div class="space-y-10">
                            <div class="flex items-center space-x-4 mb-10">
                                <div class="w-16 h-16 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-2xl flex items-center justify-center animate-glow-pulse">
                                    <i class="fas fa-info-circle text-white text-2xl"></i>
                                </div>
                                <h3 class="text-4xl font-display font-bold text-white">Event Details</h3>
                            </div>
                            
                            <div class="space-y-8">
                                <!-- Enhanced Date & Time Card -->
                                <div class="glass-effect p-8 rounded-3xl hover:bg-white/20 transition-all duration-500 group/item border border-white/20">
                                    <div class="flex items-center space-x-6">
                                        <div class="relative">
                                            <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center group-hover/item:scale-110 transition-transform duration-500 shadow-lg">
                                                <i class="fas fa-calendar-alt text-white text-2xl"></i>
                                            </div>
                                            <div class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 rounded-full animate-pulse"></div>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm text-white/60 uppercase tracking-wider font-bold mb-2">Date & Time</p>
                                            <p class="text-2xl font-display font-bold text-white mb-1">{{ $event->start_date->format('F j, Y') }}</p>
                                            <p class="text-white/80 text-lg">{{ $event->start_date->format('g:i A') }} - {{ $event->end_date->format('g:i A') }}</p>
                                            <p class="text-sm font-medium mt-2">
                                                <span class="bg-blue-500/20 text-blue-300 px-3 py-1 rounded-full border border-blue-400/30">
                                                    {{ $event->start_date->diffForHumans() }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Enhanced Venue Card -->
                                <div class="glass-effect p-8 rounded-3xl hover:bg-white/20 transition-all duration-500 group/item border border-white/20">
                                    <div class="flex items-center space-x-6">
                                        <div class="w-20 h-20 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl flex items-center justify-center group-hover/item:scale-110 transition-transform duration-500 shadow-lg">
                                            <i class="fas fa-map-marker-alt text-white text-2xl"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm text-white/60 uppercase tracking-wider font-bold mb-2">Venue</p>
                                            <p class="text-2xl font-display font-bold text-white mb-1">{{ $event->venue->name ?? 'TBD' }}</p>
                                            @if($event->venue && $event->venue->address)
                                            <p class="text-white/80 text-lg">{{ $event->venue->address }}</p>
                                            @endif
                                            <button class="text-blue-400 hover:text-blue-300 text-sm font-medium mt-2 flex items-center">
                                                <i class="fas fa-directions mr-2"></i>Get Directions
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Enhanced Category Card -->
                                <div class="glass-effect p-8 rounded-3xl hover:bg-white/20 transition-all duration-500 group/item border border-white/20">
                                    <div class="flex items-center space-x-6">
                                        <div class="w-20 h-20 bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center group-hover/item:scale-110 transition-transform duration-500 shadow-lg">
                                            <i class="fas fa-tag text-white text-2xl"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm text-white/60 uppercase tracking-wider font-bold mb-2">Category</p>
                                            <p class="text-2xl font-display font-bold text-white">{{ $event->category->name ?? 'General' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Enhanced Event Description -->
                            @if($event->description)
                            <div class="glass-effect p-8 rounded-3xl border border-white/20">
                                <h4 class="text-2xl font-display font-bold text-white mb-6 flex items-center">
                                    <i class="fas fa-align-left mr-4 text-blue-400"></i>
                                    About This Event
                                </h4>
                                <div class="prose prose-invert max-w-none">
                                    <p class="text-white/80 leading-relaxed text-lg">{{ $event->description }}</p>
                                </div>
                            </div>
                            @endif

                            <!-- Event Features/Highlights -->
                            <div class="glass-effect p-8 rounded-3xl border border-white/20">
                                <h4 class="text-2xl font-display font-bold text-white mb-6 flex items-center">
                                    <i class="fas fa-sparkles mr-4 text-yellow-400"></i>
                                    Event Highlights
                                </h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="flex items-center space-x-3 p-4 bg-white/5 rounded-xl">
                                        <i class="fas fa-wifi text-blue-400"></i>
                                        <span class="text-white/80">Free WiFi</span>
                                    </div>
                                    <div class="flex items-center space-x-3 p-4 bg-white/5 rounded-xl">
                                        <i class="fas fa-parking text-green-400"></i>
                                        <span class="text-white/80">Free Parking</span>
                                    </div>
                                    <div class="flex items-center space-x-3 p-4 bg-white/5 rounded-xl">
                                        <i class="fas fa-utensils text-orange-400"></i>
                                        <span class="text-white/80">Refreshments</span>
                                    </div>
                                    <div class="flex items-center space-x-3 p-4 bg-white/5 rounded-xl">
                                        <i class="fas fa-certificate text-purple-400"></i>
                                        <span class="text-white/80">Certificate</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Enhanced Registration Form -->
                        <div class="glass-effect-strong rounded-3xl p-10 border border-white/30 relative overflow-hidden">
                            <!-- Background decoration -->
                            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-500/20 to-purple-500/20 rounded-full filter blur-3xl"></div>
                            
                            <div class="relative z-10">
                                <div class="flex items-center space-x-4 mb-10">
                                    <div class="w-16 h-16 bg-gradient-to-r from-emerald-500 to-green-600 rounded-2xl flex items-center justify-center animate-glow-pulse">
                                        <i class="fas fa-user-plus text-white text-2xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-4xl font-display font-bold text-white">Register Now</h3>
                                        <p class="text-white/60 mt-1">Secure your spot at this amazing event</p>
                                    </div>
                                </div>

                                <form action="{{ route('public.events.store', $event) }}" method="POST"
                                      class="space-y-10 registration-form" data-event-id="{{ $event->id }}">
                                    @csrf

                                    <!-- Enhanced Ticket Selection -->
                                    @if($event->tickets->where('is_active', true)->count() > 0)
                                    <div class="space-y-8">
                                        <h4 class="text-2xl font-display font-bold text-white flex items-center">
                                            <i class="fas fa-ticket-alt mr-4 text-yellow-400 animate-pulse"></i>
                                            Choose Your Ticket
                                        </h4>
                                        
                                        <div class="space-y-6">
                                            @foreach($event->tickets->where('is_active', true) as $index => $ticket)
                                            @php
                                                $currentRegistrations = \App\Models\Registration::where('ticket_type_id', $ticket->id)
                                                                       ->where('status', '!=', 'cancelled')
                                                                       ->count();
                                                $availableSpaces = isset($ticket->capacity) && $ticket->capacity > 0 ? ($ticket->capacity - $currentRegistrations) : null;
                                                $isAvailable = !isset($ticket->capacity) || !$ticket->capacity || $availableSpaces > 0;
                                            @endphp
                                            
                                            <div class="relative group/ticket">
                                                <input type="radio" 
                                                       name="ticket_type_id" 
                                                       value="{{ $ticket->id }}" 
                                                       id="ticket_{{ $event->id }}_{{ $ticket->id }}"
                                                       class="sr-only peer"
                                                       {{ $index === 0 && $isAvailable ? 'checked' : '' }}
                                                       {{ !$isAvailable ? 'disabled' : '' }}
                                                       required>
                                                <label for="ticket_{{ $event->id }}_{{ $ticket->id }}" 
                                                       class="ticket-card flex items-center justify-between w-full p-8 glass-effect rounded-3xl cursor-pointer hover:bg-white/20 peer-checked:bg-gradient-to-r peer-checked:from-blue-500/30 peer-checked:to-purple-500/30 peer-checked:border-blue-400 peer-disabled:cursor-not-allowed peer-disabled:opacity-50 border border-white/20 relative overflow-hidden">
                                                    
                                                    <!-- Ticket shimmer effect -->
                                                    <div class="absolute inset-0 shimmer-effect opacity-0 peer-checked:opacity-20 transition-opacity duration-500"></div>
                                                    
                                                    <div class="flex-1 relative z-10">
                                                        <div class="flex items-center justify-between mb-4">
                                                            <h5 class="text-2xl font-display font-bold text-white">{{ $ticket->name }}</h5>
                                                            <div class="text-right">
                                                                @if($ticket->price == 0)
                                                                    <span class="text-3xl font-display font-bold bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent">FREE</span>
                                                                @else
                                                                    <span class="text-3xl font-display font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">${{ number_format($ticket->price, 2) }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        
                                                        @if($ticket->description)
                                                        <p class="text-white/70 text-base mb-6 leading-relaxed">{{ $ticket->description }}</p>
                                                        @endif
                                                        
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center space-x-2">
                                                                @if($availableSpaces !== null)
                                                                <span class="flex items-center {{ $isAvailable ? 'text-green-400' : 'text-red-400' }} font-medium">
                                                                    <i class="fas fa-users mr-2"></i>
                                                                    {{ $isAvailable ? $availableSpaces . ' seats left' : 'Sold out' }}
                                                                </span>
                                                                @else
                                                                <span class="flex items-center text-green-400 font-medium">
                                                                    <i class="fas fa-infinity mr-2"></i>
                                                                    Unlimited seats
                                                                </span>
                                                                @endif
                                                            </div>
                                                            
                                                            @if($isAvailable)
                                                            <span class="bg-green-500/20 text-green-300 px-4 py-2 rounded-full text-sm font-bold border border-green-400/30 backdrop-blur-sm">
                                                                Available
                                                            </span>
                                                            @else
                                                            <span class="bg-red-500/20 text-red-300 px-4 py-2 rounded-full text-sm font-bold border border-red-400/30 backdrop-blur-sm">
                                                                Sold Out
                                                            </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="ml-8 relative z-10">
                                                        <div class="relative">
                                                            <div class="w-8 h-8 border-3 border-white/40 rounded-full peer-checked:border-blue-400 peer-checked:bg-blue-500 transition-all duration-300"></div>
                                                            <div class="w-4 h-4 bg-white rounded-full absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 opacity-0 peer-checked:opacity-100 transition-opacity duration-300"></div>
                                                            <!-- Radio button glow effect -->
                                                            <div class="absolute inset-0 rounded-full bg-blue-400 opacity-0 peer-checked:opacity-30 peer-checked:animate-ping"></div>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Enhanced Dynamic Registration Fields -->
                                    @if($event->registrationFields && $event->registrationFields->count() > 0)
                                    <div class="space-y-8">
                                        <h4 class="text-2xl font-display font-bold text-white flex items-center">
                                            <i class="fas fa-user-edit mr-4 text-blue-400"></i>
                                            Your Information
                                        </h4>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                            @foreach($event->registrationFields->sortBy('order') as $field)
                                            @php
                                                $fieldKey = \Illuminate\Support\Str::slug($field->field_name, '_');
                                                $isRequired = $field->is_required;
                                                $fieldId = $event->id . '_' . $fieldKey;
                                                $oldValue = old($fieldKey);
                                            @endphp
                                            
                                            <div class="space-y-4 {{ in_array($field->field_type, ['textarea', 'checkbox']) ? 'md:col-span-2' : '' }}">
                                                <label for="{{ $fieldId }}" class="block text-base font-bold text-white/90">
                                                    {{ $field->field_name }}
                                                    @if($isRequired)
                                                        <span class="text-red-400 ml-1 animate-pulse">*</span>
                                                    @endif
                                                </label>
                                                
                                                @switch($field->field_type)
                                                    @case('text')
                                                    @case('email')
                                                    @case('phone')
                                                    @case('number')
                                                    @case('url')
                                                        <input type="{{ $field->field_type === 'phone' ? 'tel' : $field->field_type }}" 
                                                               name="{{ $fieldKey }}" 
                                                               id="{{ $fieldId }}"
                                                               value="{{ $oldValue }}"
                                                               class="w-full px-6 py-4 glass-effect border border-white/20 rounded-2xl text-white placeholder-white/50 input-glow transition-all duration-300 text-lg"
                                                               placeholder="Enter {{ strtolower($field->field_name) }}"
                                                               {{ $isRequired ? 'required' : '' }}>
                                                        @break
                                                        
                                                    @case('date')
                                                        <input type="date" 
                                                               name="{{ $fieldKey }}" 
                                                               id="{{ $fieldId }}"
                                                               value="{{ $oldValue }}"
                                                               class="w-full px-6 py-4 glass-effect border border-white/20 rounded-2xl text-white input-glow transition-all duration-300 text-lg"
                                                               {{ $isRequired ? 'required' : '' }}>
                                                        @break
                                                        
                                                    @case('textarea')
                                                        <textarea name="{{ $fieldKey }}" 
                                                                  id="{{ $fieldId }}"
                                                                  rows="4"
                                                                  class="w-full px-6 py-4 glass-effect border border-white/20 rounded-2xl text-white placeholder-white/50 input-glow transition-all duration-300 resize-none text-lg"
                                                                  placeholder="Enter {{ strtolower($field->field_name) }}"
                                                                  {{ $isRequired ? 'required' : '' }}>{{ $oldValue }}</textarea>
                                                        @break
                                                        
                                                    @case('dropdown')
                                                        <select name="{{ $fieldKey }}" 
                                                                id="{{ $fieldId }}"
                                                                class="w-full px-6 py-4 glass-effect border border-white/20 rounded-2xl text-white input-glow transition-all duration-300 text-lg"
                                                                {{ $isRequired ? 'required' : '' }}>
                                                            <option value="" class="bg-gray-800">Select {{ $field->field_name }}</option>
                                                            @if($field->options_array)
                                                                @foreach($field->options_array as $option)
                                                                    <option value="{{ $option }}" {{ $oldValue == $option ? 'selected' : '' }} class="bg-gray-800">{{ $option }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        @break
                                                        
                                                    @case('radio')
                                                        @if($field->options_array)
                                                            <div class="space-y-4">
                                                                @foreach($field->options_array as $option)
                                                                    <label class="flex items-center p-6 glass-effect border border-white/20 rounded-2xl hover:bg-white/10 cursor-pointer transition-all duration-300 group">
                                                                        <input type="radio" 
                                                                               name="{{ $fieldKey }}" 
                                                                               value="{{ $option }}"
                                                                               class="h-5 w-5 text-blue-600 border-white/40 focus:ring-blue-500 bg-transparent"
                                                                               {{ $oldValue == $option ? 'checked' : '' }}
                                                                               {{ $isRequired ? 'required' : '' }}>
                                                                        <span class="ml-4 text-white group-hover:text-blue-300 transition-colors font-medium">{{ $option }}</span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        @break
                                                        
                                                    @case('checkbox')
                                                        @if($field->options_array)
                                                            <div class="space-y-4">
                                                                @foreach($field->options_array as $option)
                                                                    @php
                                                                        $isChecked = is_array($oldValue) && in_array($option, $oldValue);
                                                                    @endphp
                                                                    <label class="flex items-center p-6 glass-effect border border-white/20 rounded-2xl hover:bg-white/10 cursor-pointer transition-all duration-300 group">
                                                                        <input type="checkbox" 
                                                                               name="{{ $fieldKey }}[]" 
                                                                               value="{{ $option }}"
                                                                               class="h-5 w-5 text-blue-600 border-white/40 rounded focus:ring-blue-500 bg-transparent"
                                                                               {{ $isChecked ? 'checked' : '' }}>
                                                                        <span class="ml-4 text-white group-hover:text-blue-300 transition-colors font-medium">{{ $option }}</span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <label class="flex items-center p-6 glass-effect border border-white/20 rounded-2xl hover:bg-white/10 cursor-pointer transition-all duration-300 group">
                                                                <input type="checkbox" 
                                                                       name="{{ $fieldKey }}" 
                                                                       value="1"
                                                                       class="h-5 w-5 text-blue-600 border-white/40 rounded focus:ring-blue-500 bg-transparent"
                                                                       {{ $oldValue ? 'checked' : '' }}
                                                                       {{ $isRequired ? 'required' : '' }}>
                                                                <span class="ml-4 text-white group-hover:text-blue-300 transition-colors font-medium">Yes, I agree</span>
                                                            </label>
                                                        @endif
                                                        @break
                                                        
                                                    @default
                                                        <input type="text" 
                                                               name="{{ $fieldKey }}" 
                                                               id="{{ $fieldId }}"
                                                               value="{{ $oldValue }}"
                                                               class="w-full px-6 py-4 glass-effect border border-white/20 rounded-2xl text-white placeholder-white/50 input-glow transition-all duration-300 text-lg"
                                                               placeholder="Enter {{ strtolower($field->field_name) }}"
                                                               {{ $isRequired ? 'required' : '' }}>
                                                @endswitch
                                                
                                                @error($fieldKey)
                                                    <div class="flex items-center space-x-3 p-4 bg-red-500/20 border border-red-400/30 rounded-xl animate-wiggle">
                                                        <i class="fas fa-exclamation-circle text-red-400"></i>
                                                        <p class="text-red-300 font-medium">{{ $message }}</p>
                                                    </div>
                                                @enderror
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Enhanced Submit Button -->
                                    <div class="pt-8">
                                        <button type="submit" 
                                                class="w-full btn-gradient text-white font-display font-bold py-8 px-10 rounded-3xl transform hover:scale-105 transition-all duration-500 shadow-2xl submit-btn relative overflow-hidden group/btn text-2xl">
                                            <div class="absolute inset-0 bg-gradient-to-r from-white/20 to-transparent opacity-0 group-hover/btn:opacity-100 transition-opacity duration-500"></div>
                                            <div class="relative flex items-center justify-center space-x-4">
                                                <i class="fas fa-rocket text-2xl animate-bounce-gentle"></i>
                                                <span>Register for {{ $event->name }}</span>
                                                <i class="fas fa-arrow-right text-xl group-hover/btn:translate-x-2 transition-transform duration-300"></i>
                                            </div>
                                        </button>
                                        
                                        <!-- Enhanced Trust Indicators -->
                                        <div class="flex items-center justify-center space-x-8 mt-6 text-white/60">
                                            <div class="flex items-center space-x-3 group">
                                                <i class="fas fa-shield-check text-green-400 text-lg group-hover:scale-110 transition-transform"></i>
                                                <span class="font-medium">Secure Registration</span>
                                            </div>
                                            <div class="flex items-center space-x-3 group">
                                                <i class="fas fa-bolt text-yellow-400 text-lg group-hover:scale-110 transition-transform animate-pulse"></i>
                                                <span class="font-medium">Instant Confirmation</span>
                                            </div>
                                            <div class="flex items-center space-x-3 group">
                                                <i class="fas fa-mobile-alt text-blue-400 text-lg group-hover:scale-110 transition-transform"></i>
                                                <span class="font-medium">Mobile Ticket</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Registration count -->
                                        <div class="text-center mt-6">
                                            <p class="text-white/50 text-sm">
                                                <span class="font-bold text-blue-300">{{ $totalRegistrations }}</span> people have already registered
                                            </p>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
        <!-- Enhanced Empty State -->
        <div class="text-center py-32 animate-fade-in">
            <div class="glass-effect-strong rounded-3xl p-20 max-w-2xl mx-auto border border-white/30 relative overflow-hidden">
                <!-- Background decoration -->
                <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-blue-500/10 to-purple-500/10 rounded-3xl"></div>
                
                <div class="relative z-10">
                    <div class="relative mb-12">
                        <div class="w-40 h-40 bg-gradient-to-br from-blue-500 via-purple-500 to-indigo-500 rounded-full flex items-center justify-center mx-auto animate-glow shadow-2xl">
                            <i class="fas fa-calendar-plus text-7xl text-white"></i>
                        </div>
                        <div class="absolute -top-6 -right-6 w-12 h-12 bg-yellow-400 rounded-full animate-bounce-gentle flex items-center justify-center">
                            <i class="fas fa-star text-yellow-900"></i>
                        </div>
                        <div class="absolute -bottom-6 -left-6 w-10 h-10 bg-pink-400 rounded-full animate-pulse"></div>
                    </div>
                    
                    <h2 class="text-5xl font-display font-bold text-white mb-8">No Events Available</h2>
                    <p class="text-2xl text-white/70 mb-12 leading-relaxed max-w-lg mx-auto">
                        We're crafting amazing experiences for you. Check back soon for exciting opportunities!
                    </p>
                    
                    <div class="space-y-6">
                        <button onclick="window.location.reload()" 
                                class="px-12 py-6 btn-gradient text-white rounded-2xl hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 font-display font-bold text-xl shadow-2xl">
                            <i class="fas fa-refresh mr-4"></i>Check Again
                        </button>
                        
                        <div class="flex items-center justify-center space-x-8 text-white/60">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-bell text-blue-400"></i>
                                <span>Get Notified</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-calendar text-purple-400"></i>
                                <span>Save the Date</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Enhanced Footer -->
    <footer class="bg-black/30 backdrop-blur-xl border-t border-white/20 py-16 relative overflow-hidden">
        <!-- Footer background decoration -->
        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center space-y-8">
                <div class="flex items-center justify-center space-x-6">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-white text-xl"></i>
                    </div>
                    <h3 class="text-3xl font-display font-bold text-white">{{ config('app.name') }}</h3>
                </div>
                
                <p class="text-white/60 text-lg max-w-2xl mx-auto">
                    Creating unforgettable experiences and bringing people together through amazing events.
                </p>
                
                <div class="flex items-center justify-center space-x-8">
                    <a href="#" class="text-white/60 hover:text-white transition-colors p-3 rounded-full hover:bg-white/10">
                        <i class="fab fa-facebook-f text-xl"></i>
                    </a>
                    <a href="#" class="text-white/60 hover:text-white transition-colors p-3 rounded-full hover:bg-white/10">
                        <i class="fab fa-twitter text-xl"></i>
                    </a>
                    <a href="#" class="text-white/60 hover:text-white transition-colors p-3 rounded-full hover:bg-white/10">
                        <i class="fab fa-instagram text-xl"></i>
                    </a>
                    <a href="#" class="text-white/60 hover:text-white transition-colors p-3 rounded-full hover:bg-white/10">
                        <i class="fab fa-linkedin-in text-xl"></i>
                    </a>
                </div>
                
                <div class="border-t border-white/20 pt-8">
                    <p class="text-white/40">&copy; 2025 {{ config('app.name') }}. All rights reserved. Made with ❤️ for amazing events.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Enhanced JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Enhanced form submission handling with loading states
            document.querySelectorAll('.registration-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('.submit-btn');
                    const eventId = this.dataset.eventId;
                    
                    // Animate button with enhanced loading state
                    submitBtn.innerHTML = `
                        <div class="relative flex items-center justify-center space-x-4">
                            <div class="w-8 h-8 border-4 border-white/20 border-t-white rounded-full animate-spin"></div>
                            <span class="text-2xl">Processing Registration...</span>
                        </div>
                    `;
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-75', 'cursor-not-allowed', 'animate-pulse');
                    
                    // Add processing overlay
                    const overlay = document.createElement('div');
                    overlay.className = 'absolute inset-0 bg-blue-500/20 backdrop-blur-sm rounded-3xl flex items-center justify-center';
                    overlay.innerHTML = '<div class="text-white font-bold">Processing...</div>';
                    this.appendChild(overlay);
                });
            });

            // Countdown Timer Functionality
            function updateCountdowns() {
                document.querySelectorAll('.countdown-timer').forEach(timer => {
                    const targetDate = new Date(timer.dataset.target);
                    const now = new Date();
                    const difference = targetDate - now;
                    
                    if (difference > 0) {
                        const days = Math.floor(difference / (1000 * 60 * 60 * 24));
                        const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((difference % (1000 * 60)) / 1000);
                        
                        timer.innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                    } else {
                        timer.innerHTML = 'Event Started!';
                        timer.classList.add('text-green-400', 'animate-pulse');
                    }
                });
            }
            
            // Update countdown every second
            updateCountdowns();
            setInterval(updateCountdowns, 1000);

            // Enhanced scroll animations with staggered reveals
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-fade-in-up');
                        entry.target.style.opacity = '1';
                    }
                });
            }, observerOptions);

            // Observe all event cards with staggered delays
            document.querySelectorAll('[id^="event-"]').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.animationDelay = `${index * 0.3}s`;
                observer.observe(card);
            });

            // Enhanced parallax effect for background elements
            let ticking = false;
            function updateParallax() {
                const scrolled = window.pageYOffset;
                const rate = scrolled * -0.3;
                
                document.querySelectorAll('[class*="animate-float"]').forEach((el, index) => {
                    const speed = (index + 1) * 0.1;
                    el.style.transform = `translate3d(0, ${rate * speed}px, 0) rotate(${scrolled * 0.02}deg)`;
                });
                
                ticking = false;
            }
            
            function requestParallaxUpdate() {
                if (!ticking) {
                    requestAnimationFrame(updateParallax);
                    ticking = true;
                }
            }
            
            window.addEventListener('scroll', requestParallaxUpdate);

            // Enhanced form input animations and validation
            document.querySelectorAll('input, textarea, select').forEach(input => {
                // Focus animations
                input.addEventListener('focus', function() {
                    const container = this.closest('.space-y-4') || this.closest('.space-y-3');
                    if (container) {
                        container.classList.add('scale-105', 'z-20');
                        container.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                    }
                    
                    // Add glow effect to input
                    this.classList.add('animate-glow-pulse');
                });
                
                // Blur animations
                input.addEventListener('blur', function() {
                    const container = this.closest('.space-y-4') || this.closest('.space-y-3');
                    if (container) {
                        container.classList.remove('scale-105', 'z-20');
                    }
                    
                    this.classList.remove('animate-glow-pulse');
                });
                
                // Real-time validation feedback
                input.addEventListener('input', function() {
                    if (this.checkValidity()) {
                        this.classList.remove('border-red-400');
                        this.classList.add('border-green-400');
                    } else if (this.value.length > 0) {
                        this.classList.remove('border-green-400');
                        this.classList.add('border-red-400');
                    } else {
                        this.classList.remove('border-red-400', 'border-green-400');
                    }
                });
            });

            // Enhanced ticket selection animations
            document.querySelectorAll('input[type="radio"][name="ticket_type_id"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    // Remove selection effects from all tickets
                    document.querySelectorAll('.ticket-card').forEach(card => {
                        card.classList.remove('animate-glow-pulse', 'scale-105');
                    });
                    
                    // Add selection effect to chosen ticket
                    const selectedCard = this.closest('.ticket-card');
                    if (selectedCard) {
                        selectedCard.classList.add('animate-glow-pulse', 'scale-105');
                        
                        // Create success ripple effect
                        const ripple = document.createElement('div');
                        ripple.className = 'absolute inset-0 bg-blue-400/20 rounded-3xl animate-ping';
                        selectedCard.style.position = 'relative';
                        selectedCard.appendChild(ripple);
                        
                        setTimeout(() => ripple.remove(), 1000);
                    }
                });
            });

            // Auto-scroll to event with errors with enhanced animation
            @if($errors->any())
                setTimeout(() => {
                    const errorElement = document.querySelector('.text-red-300');
                    if (errorElement) {
                        const eventCard = errorElement.closest('[id^="event-"]');
                        if (eventCard) {
                            // Smooth scroll with offset
                            const offset = 100;
                            const elementPosition = eventCard.getBoundingClientRect().top;
                            const offsetPosition = elementPosition + window.pageYOffset - offset;
                            
                            window.scrollTo({
                                top: offsetPosition,
                                behavior: 'smooth'
                            });
                            
                            // Enhanced highlight effect
                            eventCard.classList.add('animate-glow', 'ring-4', 'ring-red-400/50');
                            setTimeout(() => {
                                eventCard.classList.remove('animate-glow', 'ring-4', 'ring-red-400/50');
                            }, 3000);
                        }
                    }
                }, 1000);
            @endif

            // Progressive form enhancement
            const forms = document.querySelectorAll('.registration-form');
            forms.forEach(form => {
                // Add form progress indication
                const steps = form.querySelectorAll('input[required], select[required], textarea[required]');
                let completedSteps = 0;
                
                function updateProgress() {
                    completedSteps = 0;
                    steps.forEach(step => {
                        if (step.checkValidity() && step.value.trim() !== '') {
                            completedSteps++;
                        }
                    });
                    
                    const progress = (completedSteps / steps.length) * 100;
                    
                    // Update submit button state
                    const submitBtn = form.querySelector('.submit-btn');
                    if (progress === 100) {
                        submitBtn.classList.add('animate-glow-pulse');
                        submitBtn.querySelector('i').classList.add('animate-bounce-gentle');
                    } else {
                        submitBtn.classList.remove('animate-glow-pulse');
                        submitBtn.querySelector('i').classList.remove('animate-bounce-gentle');
                    }
                }
                
                // Monitor form changes
                steps.forEach(step => {
                    step.addEventListener('input', updateProgress);
                    step.addEventListener('change', updateProgress);
                });
                
                // Initial progress check
                updateProgress();
            });

            // Dynamic particle system
            function createParticle() {
                const particle = document.createElement('div');
                particle.className = 'floating-particle';
                particle.style.width = Math.random() * 6 + 2 + 'px';
                particle.style.height = particle.style.width;
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDuration = (Math.random() * 15 + 20) + 's';
                particle.style.animationDelay = Math.random() * 5 + 's';
                
                document.body.appendChild(particle);
                
                // Remove particle after animation
                setTimeout(() => {
                    if (particle.parentNode) {
                        particle.parentNode.removeChild(particle);
                    }
                }, 25000);
            }
            
            // Create particles periodically
            setInterval(createParticle, 3000);

            // Enhanced keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Tab') {
                    // Add focus styles for keyboard navigation
                    document.body.classList.add('keyboard-navigation');
                }
            });
            
            document.addEventListener('mousedown', function() {
                document.body.classList.remove('keyboard-navigation');
            });

            // Performance optimization: Reduce animations on slower devices
            const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
            if (mediaQuery.matches) {
                document.documentElement.style.setProperty('--animation-duration', '0.1s');
            }

            // Easter egg: Konami code
            const konamiCode = [38, 38, 40, 40, 37, 39, 37, 39, 66, 65];
            let konamiIndex = 0;
            
            document.addEventListener('keydown', function(e) {
                if (e.keyCode === konamiCode[konamiIndex]) {
                    konamiIndex++;
                    if (konamiIndex === konamiCode.length) {
                        // Activate special effects
                        document.body.classList.add('party-mode');
                        
                        // Create confetti effect
                        for (let i = 0; i < 50; i++) {
                            setTimeout(() => createParticle(), i * 100);
                        }
                        
                        konamiIndex = 0;
                        setTimeout(() => {
                            document.body.classList.remove('party-mode');
                        }, 5000);
                    }
                } else {
                    konamiIndex = 0;
                }
            });

            // Smart loading states
            window.addEventListener('beforeunload', function() {
                document.body.classList.add('page-loading');
            });

            // Accessibility enhancements
            function announceToScreenReader(message) {
                const announcement = document.createElement('div');
                announcement.setAttribute('aria-live', 'polite');
                announcement.setAttribute('aria-atomic', 'true');
                announcement.className = 'sr-only';
                announcement.textContent = message;
                document.body.appendChild(announcement);
                
                setTimeout(() => {
                    document.body.removeChild(announcement);
                }, 1000);
            }

            // Announce successful form interactions
            document.querySelectorAll('input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.name === 'ticket_type_id') {
                        const ticketName = this.closest('label').querySelector('h5').textContent;
                        announceToScreenReader(`Selected ticket: ${ticketName}`);
                    }
                });
            });

            console.log('🎉 Event Registration System Enhanced - All animations and interactions loaded!');
        });

        // Additional CSS for enhanced states
        const additionalStyles = `
            <style>
                .keyboard-navigation *:focus {
                    outline: 3px solid rgba(59, 130, 246, 0.6) !important;
                    outline-offset: 2px !important;
                }
                
                .party-mode {
                    animation: rainbow 2s linear infinite !important;
                }
                
                @keyframes rainbow {
                    0% { filter: hue-rotate(0deg); }
                    100% { filter: hue-rotate(360deg); }
                }
                
                .page-loading * {
                    pointer-events: none !important;
                    opacity: 0.7 !important;
                }
                
                .sr-only {
                    position: absolute !important;
                    width: 1px !important;
                    height: 1px !important;
                    padding: 0 !important;
                    margin: -1px !important;
                    overflow: hidden !important;
                    clip: rect(0, 0, 0, 0) !important;
                    white-space: nowrap !important;
                    border: 0 !important;
                }
                
                @media (max-width: 768px) {
                    .floating-particle {
                        display: none;
                    }
                }
            </style>
        `;
        
        document.head.insertAdjacentHTML('beforeend', additionalStyles);
    </script>
</body>
</html>