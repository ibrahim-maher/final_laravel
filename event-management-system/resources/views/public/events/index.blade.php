<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Event Registration - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'glow': 'glow 2s ease-in-out infinite alternate',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'fade-in': 'fadeIn 0.6s ease-out',
                        'bounce-subtle': 'bounceSubtle 2s infinite',
                        'shimmer': 'shimmer 2s linear infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' }
                        },
                        glow: {
                            'from': { boxShadow: '0 0 20px #3b82f6' },
                            'to': { boxShadow: '0 0 30px #8b5cf6, 0 0 40px #3b82f6' }
                        },
                        slideUp: {
                            'from': { transform: 'translateY(30px)', opacity: '0' },
                            'to': { transform: 'translateY(0)', opacity: '1' }
                        },
                        fadeIn: {
                            'from': { opacity: '0' },
                            'to': { opacity: '1' }
                        },
                        bounceSubtle: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-5px)' }
                        },
                        shimmer: {
                            '0%': { backgroundPosition: '-200% 0' },
                            '100%': { backgroundPosition: '200% 0' }
                        }
                    },
                    backgroundImage: {
                        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
                        'shimmer': 'linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent)',
                    }
                }
            }
        }
    </script>
    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .shimmer-effect {
            background-image: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            background-size: 200% 100%;
            animation: shimmer 2s linear infinite;
        }
        
        .hero-pattern {
            background-image: 
                radial-gradient(circle at 25px 25px, rgba(255,255,255,0.2) 2px, transparent 0),
                radial-gradient(circle at 75px 75px, rgba(255,255,255,0.1) 2px, transparent 0);
            background-size: 100px 100px;
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }
        
        .btn-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-gradient:hover::before {
            left: 100%;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 min-h-screen">
    <!-- Animated Background -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-float"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-float" style="animation-delay: 2s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-pink-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-float" style="animation-delay: 4s;"></div>
    </div>

 


    <!-- Global Messages -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 animate-slide-up">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-4 rounded-xl mb-6 shadow-lg">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-check text-sm"></i>
                    </div>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 animate-slide-up">
            <div class="bg-gradient-to-r from-red-500 to-pink-600 text-white px-6 py-4 rounded-xl mb-6 shadow-lg">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-exclamation-triangle text-sm"></i>
                    </div>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Events Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        @if(isset($availableEvents) && $availableEvents->count() > 0)
            
            <div class="space-y-16">
                @foreach($availableEvents as $event)
                <div class="group card-hover bg-white/10 backdrop-blur-lg rounded-3xl overflow-hidden shadow-2xl border border-white/20" id="event-{{ $event->id }}">
                    <!-- Event Header -->
                    <div class="relative h-80 overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700">
                            @if($event->logo)
                            <img src="{{ Storage::url($event->logo) }}" alt="{{ $event->name }}" class="w-full h-full object-cover">
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                        </div>
                        
                        <!-- Floating Event Badge -->
                        <div class="absolute top-6 right-6">
                            <div class="glass-effect px-4 py-2 rounded-full text-white text-sm font-medium">
                                <i class="fas fa-star mr-2 text-yellow-400"></i>
                                Featured Event
                            </div>
                        </div>
                        
                        <div class="absolute inset-0 flex items-center justify-center text-center p-8">
                            <div class="animate-fade-in">
                                <h2 class="text-5xl md:text-6xl font-bold text-white mb-6 group-hover:scale-105 transition-transform duration-300">
                                    {{ $event->name }}
                                </h2>
                                <p class="text-xl text-white/90 max-w-2xl mx-auto leading-relaxed">
                                    {{ Str::limit($event->description ?? '', 120) }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Animated Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-500/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    </div>
                    
                    <!-- Event Content -->
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 p-8">
                        <!-- Event Information -->
                        <div class="space-y-8">
                            <div class="flex items-center space-x-4 mb-8">
                                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-info-circle text-white text-xl"></i>
                                </div>
                                <h3 class="text-3xl font-bold text-white">Event Details</h3>
                            </div>
                            
                            <div class="space-y-6">
                                <!-- Date & Time -->
                                <div class="glass-effect p-6 rounded-2xl hover:bg-white/20 transition-all duration-300 group/item">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-14 h-14 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center group-hover/item:scale-110 transition-transform duration-300">
                                            <i class="fas fa-calendar-alt text-white text-xl"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm text-white/60 uppercase tracking-wide font-medium">Date & Time</p>
                                            <p class="text-xl font-bold text-white">{{ $event->start_date->format('F j, Y') }}</p>
                                            <p class="text-white/80">{{ $event->start_date->format('g:i A') }} - {{ $event->end_date->format('g:i A') }}</p>
                                            <p class="text-sm text-blue-300 font-medium">{{ $event->start_date->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Venue -->
                                <div class="glass-effect p-6 rounded-2xl hover:bg-white/20 transition-all duration-300 group/item">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-14 h-14 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl flex items-center justify-center group-hover/item:scale-110 transition-transform duration-300">
                                            <i class="fas fa-map-marker-alt text-white text-xl"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm text-white/60 uppercase tracking-wide font-medium">Venue</p>
                                            <p class="text-xl font-bold text-white">{{ $event->venue->name ?? 'TBD' }}</p>
                                            @if($event->venue && $event->venue->address)
                                            <p class="text-white/80">{{ $event->venue->address }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Category -->
                                <div class="glass-effect p-6 rounded-2xl hover:bg-white/20 transition-all duration-300 group/item">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-14 h-14 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center group-hover/item:scale-110 transition-transform duration-300">
                                            <i class="fas fa-tag text-white text-xl"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm text-white/60 uppercase tracking-wide font-medium">Category</p>
                                            <p class="text-xl font-bold text-white">{{ $event->category->name ?? 'General' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Event Description -->
                            @if($event->description)
                            <div class="glass-effect p-6 rounded-2xl">
                                <h4 class="text-lg font-bold text-white mb-3 flex items-center">
                                    <i class="fas fa-align-left mr-3 text-blue-400"></i>
                                    About This Event
                                </h4>
                                <p class="text-white/80 leading-relaxed">{{ $event->description }}</p>
                            </div>
                            @endif

                        
                        </div>

                        <!-- Registration Form -->
                        <div class="glass-effect rounded-3xl p-8 border border-white/20">
                            <div class="flex items-center space-x-4 mb-8">
                                <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-user-plus text-white text-xl"></i>
                                </div>
                                <h3 class="text-3xl font-bold text-white">Register Now</h3>
                            </div>

                            <form action="{{ route('public.events.store', $event) }}" method="POST"
                                  class="space-y-8 registration-form" data-event-id="{{ $event->id }}">
                                @csrf

                                <!-- Ticket Selection -->
                                @if($event->tickets->where('is_active', true)->count() > 0)
                                <div class="space-y-6">
                                    <h4 class="text-xl font-bold text-white flex items-center">
                                        <i class="fas fa-ticket-alt mr-3 text-yellow-400"></i>Choose Your Ticket
                                    </h4>
                                    
                                    <div class="space-y-4">
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
                                                   class="flex items-center justify-between w-full p-6 glass-effect rounded-2xl cursor-pointer hover:bg-white/20 peer-checked:bg-gradient-to-r peer-checked:from-blue-500/30 peer-checked:to-purple-500/30 peer-checked:border-blue-400 peer-disabled:cursor-not-allowed peer-disabled:opacity-50 transition-all duration-300 border border-white/20 group-hover/ticket:scale-105">
                                                <div class="flex-1">
                                                    <div class="flex items-center justify-between mb-3">
                                                        <h5 class="text-xl font-bold text-white">{{ $ticket->name }}</h5>
                                                        <div class="text-right">
                                                            @if($ticket->price == 0)
                                                                <span class="text-2xl font-bold bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent">FREE</span>
                                                            @else
                                                                <span class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">${{ number_format($ticket->price, 2) }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    @if($ticket->description)
                                                    <p class="text-white/70 text-sm mb-4">{{ $ticket->description }}</p>
                                                    @endif
                                                    
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center space-x-2">
                                                            @if($availableSpaces !== null)
                                                            <span class="flex items-center {{ $isAvailable ? 'text-green-400' : 'text-red-400' }}">
                                                                <i class="fas fa-users mr-2"></i>
                                                                {{ $isAvailable ? $availableSpaces . ' left' : 'Sold out' }}
                                                            </span>
                                                            @else
                                                            <span class="flex items-center text-green-400">
                                                                <i class="fas fa-infinity mr-2"></i>
                                                                Unlimited
                                                            </span>
                                                            @endif
                                                        </div>
                                                        
                                                        @if($isAvailable)
                                                        <span class="bg-green-500/20 text-green-300 px-3 py-1 rounded-full text-xs font-medium border border-green-400/30">
                                                            Available
                                                        </span>
                                                        @else
                                                        <span class="bg-red-500/20 text-red-300 px-3 py-1 rounded-full text-xs font-medium border border-red-400/30">
                                                            Sold Out
                                                        </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <div class="ml-6">
                                                    <div class="w-6 h-6 border-2 border-white/40 rounded-full peer-checked:border-blue-400 peer-checked:bg-blue-500 relative transition-all duration-300">
                                                        <div class="w-3 h-3 bg-white rounded-full absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 opacity-0 peer-checked:opacity-100 transition-opacity duration-300"></div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                <!-- Dynamic Registration Fields -->
                                @if($event->registrationFields && $event->registrationFields->count() > 0)
                                <div class="space-y-6">
                                    <h4 class="text-xl font-bold text-white flex items-center">
                                        <i class="fas fa-user-edit mr-3 text-blue-400"></i>Your Information
                                    </h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        @foreach($event->registrationFields->sortBy('order') as $field)
                                        @php
                                            $fieldKey = \Illuminate\Support\Str::slug($field->field_name, '_');
                                            $isRequired = $field->is_required;
                                            $fieldId = $event->id . '_' . $fieldKey;
                                            $oldValue = old($fieldKey);
                                        @endphp
                                        
                                        <div class="space-y-3 {{ in_array($field->field_type, ['textarea', 'checkbox']) ? 'md:col-span-2' : '' }}">
                                            <label for="{{ $fieldId }}" class="block text-sm font-bold text-white/90">
                                                {{ $field->field_name }}
                                                @if($isRequired)
                                                    <span class="text-red-400 ml-1">*</span>
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
                                                           class="w-full px-4 py-3 glass-effect border border-white/20 rounded-xl text-white placeholder-white/50 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300"
                                                           placeholder="Enter {{ strtolower($field->field_name) }}"
                                                           {{ $isRequired ? 'required' : '' }}>
                                                    @break
                                                    
                                                @case('date')
                                                    <input type="date" 
                                                           name="{{ $fieldKey }}" 
                                                           id="{{ $fieldId }}"
                                                           value="{{ $oldValue }}"
                                                           class="w-full px-4 py-3 glass-effect border border-white/20 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300"
                                                           {{ $isRequired ? 'required' : '' }}>
                                                    @break
                                                    
                                                @case('textarea')
                                                    <textarea name="{{ $fieldKey }}" 
                                                              id="{{ $fieldId }}"
                                                              rows="4"
                                                              class="w-full px-4 py-3 glass-effect border border-white/20 rounded-xl text-white placeholder-white/50 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300 resize-none"
                                                              placeholder="Enter {{ strtolower($field->field_name) }}"
                                                              {{ $isRequired ? 'required' : '' }}>{{ $oldValue }}</textarea>
                                                    @break
                                                    
                                                @case('dropdown')
                                                    <select name="{{ $fieldKey }}" 
                                                            id="{{ $fieldId }}"
                                                            class="w-full px-4 py-3 glass-effect border border-white/20 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300"
                                                            {{ $isRequired ? 'required' : '' }}>
                                                        <option value="">Select {{ $field->field_name }}</option>
                                                        @if($field->options_array)
                                                            @foreach($field->options_array as $option)
                                                                <option value="{{ $option }}" {{ $oldValue == $option ? 'selected' : '' }} class="bg-gray-800">{{ $option }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    @break
                                                    
                                                @case('radio')
                                                    @if($field->options_array)
                                                        <div class="space-y-3">
                                                            @foreach($field->options_array as $option)
                                                                <label class="flex items-center p-4 glass-effect border border-white/20 rounded-xl hover:bg-white/10 cursor-pointer transition-all duration-300">
                                                                    <input type="radio" 
                                                                           name="{{ $fieldKey }}" 
                                                                           value="{{ $option }}"
                                                                           class="h-4 w-4 text-blue-600 border-white/40 focus:ring-blue-500 bg-transparent"
                                                                           {{ $oldValue == $option ? 'checked' : '' }}
                                                                           {{ $isRequired ? 'required' : '' }}>
                                                                    <span class="ml-3 text-white">{{ $option }}</span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                    @break
                                                    
                                                @case('checkbox')
                                                    @if($field->options_array)
                                                        <div class="space-y-3">
                                                            @foreach($field->options_array as $option)
                                                                @php
                                                                    $isChecked = is_array($oldValue) && in_array($option, $oldValue);
                                                                @endphp
                                                                <label class="flex items-center p-4 glass-effect border border-white/20 rounded-xl hover:bg-white/10 cursor-pointer transition-all duration-300">
                                                                    <input type="checkbox" 
                                                                           name="{{ $fieldKey }}[]" 
                                                                           value="{{ $option }}"
                                                                           class="h-4 w-4 text-blue-600 border-white/40 rounded focus:ring-blue-500 bg-transparent"
                                                                           {{ $isChecked ? 'checked' : '' }}>
                                                                    <span class="ml-3 text-white">{{ $option }}</span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <label class="flex items-center p-4 glass-effect border border-white/20 rounded-xl hover:bg-white/10 cursor-pointer transition-all duration-300">
                                                            <input type="checkbox" 
                                                                   name="{{ $fieldKey }}" 
                                                                   value="1"
                                                                   class="h-4 w-4 text-blue-600 border-white/40 rounded focus:ring-blue-500 bg-transparent"
                                                                   {{ $oldValue ? 'checked' : '' }}
                                                                   {{ $isRequired ? 'required' : '' }}>
                                                            <span class="ml-3 text-white">Yes, I agree</span>
                                                        </label>
                                                    @endif
                                                    @break
                                                    
                                                @default
                                                    <input type="text" 
                                                           name="{{ $fieldKey }}" 
                                                           id="{{ $fieldId }}"
                                                           value="{{ $oldValue }}"
                                                           class="w-full px-4 py-3 glass-effect border border-white/20 rounded-xl text-white placeholder-white/50 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300"
                                                           placeholder="Enter {{ strtolower($field->field_name) }}"
                                                           {{ $isRequired ? 'required' : '' }}>
                                            @endswitch
                                            
                                            @error($fieldKey)
                                                <p class="text-red-400 text-sm flex items-center">
                                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                <!-- Submit Button -->
                                <div class="pt-6">
                                    <button type="submit" 
                                            class="w-full bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 text-white font-bold py-6 px-8 rounded-2xl hover:from-blue-700 hover:via-purple-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-300 shadow-2xl submit-btn relative overflow-hidden group/btn">
                                        <div class="absolute inset-0 bg-gradient-to-r from-white/20 to-transparent opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                        <div class="relative flex items-center justify-center space-x-3">
                                            <i class="fas fa-rocket text-xl"></i>
                                            <span class="text-xl">Register for {{ $event->name }}</span>
                                        </div>
                                    </button>
                                    <div class="flex items-center justify-center space-x-6 mt-4 text-sm text-white/60">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-shield-check text-green-400"></i>
                                            <span>Secure Registration</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-bolt text-yellow-400"></i>
                                            <span>Instant Confirmation</span>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
        <!-- Enhanced Empty State -->
        <div class="text-center py-20 animate-fade-in">
            <div class="glass-effect rounded-3xl p-16 max-w-lg mx-auto border border-white/20">
                <div class="relative mb-8">
                    <div class="w-32 h-32 bg-gradient-to-br from-blue-500 via-purple-500 to-indigo-500 rounded-full flex items-center justify-center mx-auto animate-glow">
                        <i class="fas fa-calendar-plus text-6xl text-white"></i>
                    </div>
                    <div class="absolute -top-4 -right-4 w-8 h-8 bg-yellow-400 rounded-full animate-bounce-subtle"></div>
                </div>
                <h2 class="text-4xl font-bold text-white mb-6">No Events Available</h2>
                <p class="text-xl text-white/70 mb-8 leading-relaxed">
                    We're working on bringing you amazing events. Check back soon for exciting opportunities!
                </p>
                <div class="space-y-4">
                    <button onclick="window.location.reload()" 
                            class="w-full px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 font-medium">
                        <i class="fas fa-refresh mr-3"></i>Check Again
                    </button>
                    <div class="grid grid-cols-2 gap-4">
                        <a href="/debug-events" 
                           class="px-6 py-3 glass-effect border border-white/20 text-white rounded-xl hover:bg-white/20 transition-all duration-300 text-center">
                            <i class="fas fa-bug mr-2"></i>Debug Info
                        </a>
                        <a href="/fix-event-times" 
                           class="px-6 py-3 glass-effect border border-white/20 text-white rounded-xl hover:bg-white/20 transition-all duration-300 text-center">
                            <i class="fas fa-clock mr-2"></i>Fix Times
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    

    <!-- Footer -->
    <footer class="bg-black/20 backdrop-blur-lg border-t border-white/10 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-white/60">&copy; 2025 {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Enhanced form submission handling
            document.querySelectorAll('.registration-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('.submit-btn');
                    const eventId = this.dataset.eventId;
                    
                    // Animate button
                    submitBtn.innerHTML = `
                        <div class="relative flex items-center justify-center space-x-3">
                            <i class="fas fa-spinner fa-spin text-xl"></i>
                            <span class="text-xl">Processing Registration...</span>
                        </div>
                    `;
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                    
                    // Add loading effect
                    submitBtn.classList.add('animate-pulse');
                });
            });

            // Enhanced scroll animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -100px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-slide-up');
                        entry.target.style.opacity = '1';
                    }
                });
            }, observerOptions);

            // Observe all event cards
            document.querySelectorAll('[id^="event-"]').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.animationDelay = `${index * 0.2}s`;
                observer.observe(card);
            });

            // Auto-scroll to event with errors with smooth animation
            @if($errors->any())
                setTimeout(() => {
                    const errorElement = document.querySelector('.text-red-400');
                    if (errorElement) {
                        const eventCard = errorElement.closest('[id^="event-"]');
                        if (eventCard) {
                            eventCard.scrollIntoView({ 
                                behavior: 'smooth', 
                                block: 'center' 
                            });
                            
                            // Add highlight effect
                            eventCard.classList.add('animate-glow');
                            setTimeout(() => {
                                eventCard.classList.remove('animate-glow');
                            }, 3000);
                        }
                    }
                }, 800);
            @endif

            // Add parallax effect to background elements
            window.addEventListener('scroll', () => {
                const scrolled = window.pageYOffset;
                const rate = scrolled * -0.5;
                
                document.querySelectorAll('.animate-float').forEach((el, index) => {
                    el.style.transform = `translate3d(0, ${rate * (index + 1) * 0.1}px, 0)`;
                });
            });

            // Add hover effects to form inputs
            document.querySelectorAll('input, textarea, select').forEach(input => {
                input.addEventListener('focus', function() {
                    this.closest('.space-y-3')?.classList.add('scale-105', 'z-10');
                });
                
                input.addEventListener('blur', function() {
                    this.closest('.space-y-3')?.classList.remove('scale-105', 'z-10');
                });
            });
        });
    </script>
</body>
</html>