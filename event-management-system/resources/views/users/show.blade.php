@extends('layouts.app')

@section('title', 'User Profile')
@section('page-title', 'User Profile')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center space-x-6">
                <div class="h-24 w-24 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center">
                    <span class="text-white font-bold text-3xl">{{ $user->initials }}</span>
                </div>
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-gray-600 text-lg">{{ $user->email }}</p>
                    <div class="flex items-center space-x-3 mt-2">
                        <span class="px-4 py-2 text-sm font-medium rounded-full {{ $user->getRoleColorClass() }}">
                            {{ $user->getRoleDisplayName() }}
                        </span>
                        <span class="px-4 py-2 text-sm font-medium rounded-full {{ $user->getStatusBadgeClass() }}">
                            {{ $user->getStatusDisplayText() }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="mt-4 lg:mt-0 flex flex-col sm:flex-row gap-3">
                @can('update', $user)
                <a href="{{ route('users.edit', $user) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center justify-center">
                    <i class="fas fa-edit mr-2"></i>
                    Edit User
                </a>
                @endcan
                <a href="{{ route('users.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center justify-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Users
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Registrations</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $userStats['total_registrations'] }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-calendar-alt text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Events Attended</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $userStats['events_attended'] }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-check-circle text-2xl text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Check-ins</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $userStats['total_checkins'] }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-sign-in-alt text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Avg Duration</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($userStats['avg_event_duration'], 0) }}m</p>
                </div>
                <div class="bg-orange-100 p-3 rounded-full">
                    <i class="fas fa-clock text-2xl text-orange-600"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-user mr-2 text-blue-600"></i>
                    Personal Information
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <p class="text-gray-900">{{ $user->name }}</p>
                    </div>

                    @if($user->first_name || $user->last_name)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First & Last Name</label>
                        <p class="text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</p>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <p class="text-gray-900 flex items-center">
                            {{ $user->email }}
                            @if($user->email_verified_at)
                            <i class="fas fa-check-circle text-green-500 ml-2" title="Verified"></i>
                            @else
                            <i class="fas fa-exclamation-circle text-yellow-500 ml-2" title="Not Verified"></i>
                            @endif
                        </p>
                    </div>

                    @if($user->phone_number)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <p class="text-gray-900">{{ $user->phone_number }}</p>
                    </div>
                    @endif

                    @if($user->title)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Job Title</label>
                        <p class="text-gray-900">{{ $user->title }}</p>
                    </div>
                    @endif

                    @if($user->company)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                        <p class="text-gray-900">{{ $user->company }}</p>
                    </div>
                    @endif

                    @if($user->country)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                        <p class="text-gray-900">{{ $user->country }}</p>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Member Since</label>
                        <p class="text-gray-900">{{ $user->created_at->format('F d, Y') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Updated</label>
                        <p class="text-gray-900">{{ $user->updated_at->format('F d, Y') }}</p>
                    </div>
                </div>

                @if($user->notes)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-900 whitespace-pre-wrap">{{ $user->notes }}</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Assigned Events (for Event Managers and Ushers) -->
            @if($user->canBeAssignedToEvents() && $user->assignedEvents->count() > 0)
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-calendar-check mr-2 text-orange-600"></i>
                        Assigned Events
                    </h3>
                    @can('update', $user)
                    <a href="{{ route('users.edit', $user) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-edit mr-1"></i>
                        Manage Assignments
                    </a>
                    @endcan
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($user->assignedEvents as $event)
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900">{{ $event->name }}</h4>
                                @if($event->start_date)
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ $event->start_date->format('M d, Y') }}
                                    @if($event->end_date && $event->end_date != $event->start_date)
                                     - {{ $event->end_date->format('M d, Y') }}
                                    @endif
                                </p>
                                @endif
                                @if($event->venue)
                                <p class="text-xs text-gray-400 mt-1">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    {{ $event->venue->name }}
                                </p>
                                @endif
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full {{ $event->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $event->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Recent Registrations -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-user-check mr-2 text-green-600"></i>
                    Recent Registrations
                </h3>
                
                @if($user->registrations->count() > 0)
                <ul class="space-y-4">
                    @foreach($user->registrations->take(10) as $registration)
                    <li class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900">{{ $registration->event->name }}</h4>
                                <p class="text-sm text-gray-500 mt-1">
                                    Registered: {{ $registration->created_at->format('M d, Y') }}
                                </p>
                                @if($registration->ticketType)
                                <p class="text-xs text-gray-400 mt-1">
                                    Ticket: {{ $registration->ticketType->name }}
                                </p>
                                @endif
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full {{ $registration->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $registration->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="text-center py-8">
                    <i class="fas fa-user-check text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500">No registrations found for this user</p>
                    @can('create', App\Models\Registration::class)
                    <a href="{{ route('registrations.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i>
                        Create Registration
                    </a>
                    @endcan
                </div>
                @endif
            </div>

            <!-- Recent Activity -->
            @if(isset($recentActivity) && $recentActivity->count() > 0)
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-history mr-2 text-purple-600"></i>
                    Recent Activity
                </h3>
                
                <ul class="space-y-4">
                    @foreach($recentActivity as $activity)
                    <li class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900">{{ $activity->registration->event->name }}</h4>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ ucfirst($activity->action) }} - {{ $activity->created_at->format('M d, Y H:i') }}
                                </p>
                                @if($activity->duration_minutes)
                                <p class="text-xs text-gray-400 mt-1">
                                    Duration: {{ $activity->duration_minutes }} minutes
                                </p>
                                @endif
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full {{ $activity->action === 'checkin' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ ucfirst($activity->action) }}
                            </span>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- User Actions -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-cogs mr-2 text-gray-600"></i>
                    User Actions
                </h3>
                
                <div class="space-y-3">
                    @can('update', $user)
                    <a href="{{ route('users.edit', $user) }}" class="block w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-center transition-colors">
                        <i class="fas fa-edit mr-2"></i> Edit User
                    </a>
                    @endcan

                    @if($user->canBeAssignedToEvents())
                    <button onclick="showAssignEventsModal({{ $user->id }}, '{{ $user->name }}')" class="block w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-center transition-colors">
                        <i class="fas fa-calendar-plus mr-2"></i> Assign Events
                    </button>
                    @endif
                    
                    @can('delete', $user)
                    @if($user->id !== auth()->id())
                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-center transition-colors">
                            <i class="fas fa-trash-alt mr-2"></i> Delete User
                        </button>
                    </form>
                    @endif
                    @endcan
                    
                    <a href="{{ route('users.index') }}" class="block w-full px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg text-center transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Users
                    </a>
                </div>
            </div>

            <!-- User Statistics -->
            @if($userStats['assigned_events_count'] > 0)
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-chart-bar mr-2 text-indigo-600"></i>
                    Assignment Stats
                </h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Assigned Events</span>
                        <span class="font-semibold text-gray-900">{{ $userStats['assigned_events_count'] }}</span>
                    </div>
                    
                    @if($userStats['last_activity'])
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Last Activity</span>
                        <span class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($userStats['last_activity'])->format('M d, Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Assign Events Modal -->
<div id="assign-events-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Events</h3>
            <form id="assign-events-form" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Events</label>
                    <div class="max-h-48 overflow-y-auto border border-gray-300 rounded-lg p-2">
                        @foreach(\App\Models\Event::where('is_active', true)->orderBy('name')->get() as $event)
                        <label class="flex items-center py-2 px-3 hover:bg-gray-50 rounded">
                            <input type="checkbox" name="event_ids[]" value="{{ $event->id }}" 
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-3"
                                   {{ $user->assignedEvents->contains($event->id) ? 'checked' : '' }}>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $event->name }}</div>
                                @if($event->start_date)
                                <div class="text-xs text-gray-500">{{ $event->start_date->format('M d, Y') }}</div>
                                @endif
                                @if($event->venue)
                                <div class="text-xs text-gray-400">{{ $event->venue->name }}</div>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeAssignEventsModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                        Update Assignments
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/user-management.js') }}"></script>
@endpush