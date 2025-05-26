{{-- resources/views/visitor-logs/timeline.blade.php --}}
@extends('layouts.app')

@section('title', 'Visitor Timeline')
@section('page-title', 'Visitor Activity Timeline')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold mb-2">Visitor Timeline</h2>
                <p class="text-indigo-100">Track visitor activity chronologically</p>
            </div>
            <div class="flex items-center space-x-4">
                <i class="fas fa-timeline text-4xl text-indigo-200"></i>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
                <select name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Event</label>
                <select name="event_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Events</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" {{ $eventId == $event->id ? 'selected' : '' }}>
                            {{ $event->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Registration ID</label>
                <input type="number" name="registration_id" value="{{ $registrationId }}" 
                       placeholder="Enter registration ID..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-filter mr-2"></i>Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Timeline -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-900">
                <i class="fas fa-clock mr-2 text-indigo-600"></i>
                Activity Timeline ({{ $timeline->total() }} activities)
            </h3>
            <div class="flex items-center space-x-3">
                <button onclick="exportTimeline()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-download mr-2"></i>Export
                </button>
            </div>
        </div>

        @if($timeline->count() > 0)
            <div class="relative">
                <!-- Timeline line -->
                <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-300"></div>
                
                <div class="space-y-6">
                    @foreach($timeline as $log)
                    <div class="relative flex items-start space-x-6">
                        <!-- Timeline dot -->
                        <div class="relative z-10 flex items-center justify-center w-16 h-16 {{ $log->action === 'checkin' ? 'bg-green-500' : 'bg-blue-500' }} rounded-full shadow-lg">
                            <i class="fas {{ $log->action === 'checkin' ? 'fa-sign-in-alt' : 'fa-sign-out-alt' }} text-white text-xl"></i>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 bg-gray-50 rounded-lg p-6 shadow-sm">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-4">
                                    <h4 class="text-lg font-semibold text-gray-900">
                                        {{ $log->registration->user->name }}
                                    </h4>
                                    <span class="px-3 py-1 text-sm rounded-full {{ $log->action === 'checkin' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">{{ $log->created_at->format('M d, Y') }}</p>
                                    <p class="text-sm text-gray-500">{{ $log->created_at->format('H:i:s') }}</p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">Event:</span>
                                    <p class="text-gray-900">{{ $log->registration->event->name }}</p>
                                </div>
                                
                                <div>
                                    <span class="font-medium text-gray-700">Registration:</span>
                                    <p class="text-gray-900">#{{ $log->registration->id }}</p>
                                </div>
                                
                                <div>
                                    <span class="font-medium text-gray-700">Method:</span>
                                    <p class="text-gray-900">{{ $log->qr_scanned ? 'QR Code' : 'Manual' }}</p>
                                </div>
                                
                                @if($log->duration_minutes)
                                <div>
                                    <span class="font-medium text-gray-700">Duration:</span>
                                    <p class="text-purple-600 font-medium">{{ $log->duration_minutes }} minutes</p>
                                </div>
                                @endif
                                
                                <div>
                                    <span class="font-medium text-gray-700">Created by:</span>
                                    <p class="text-gray-900">{{ $log->creator->name ?? 'System' }}</p>
                                </div>
                                
                                <div>
                                    <span class="font-medium text-gray-700">Time ago:</span>
                                    <p class="text-gray-500">{{ $log->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            
                            @if($log->admin_note)
                            <div class="mt-4 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                                <p class="text-sm text-gray-700">
                                    <span class="font-medium">Note:</span> {{ $log->admin_note }}
                                </p>
                            </div>
                            @endif
                            
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center space-x-2 text-xs text-gray-500">
                                    <span>Log ID: #{{ $log->id }}</span>
                                    @if($log->ip_address)
                                        <span>•</span>
                                        <span>IP: {{ $log->ip_address }}</span>
                                    @endif
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <button onclick="viewLogDetails({{ $log->id }})" class="text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-eye mr-1"></i>Details
                                    </button>
                                    <button onclick="viewRegistration({{ $log->registration->id }})" class="text-green-600 hover:text-green-800 text-sm">
                                        <i class="fas fa-user mr-1"></i>Registration
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Pagination -->
            @if($timeline->hasPages())
            <div class="mt-8 border-t border-gray-200 pt-6">
                {{ $timeline->appends(request()->query())->links() }}
            </div>
            @endif

        @else
            <div class="text-center py-12">
                <div class="text-gray-500">
                    <i class="fas fa-clock text-6xl mb-4"></i>
                    <h3 class="text-xl font-medium mb-2">No timeline data found</h3>
                    <p class="text-gray-400">Try adjusting your filters or check back later</p>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Log Details Modal -->
<div id="log-details-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl max-w-4xl w-full max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Activity Details</h3>
                    <button onclick="closeLogModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div id="log-modal-content">
                    <!-- Content will be populated here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewLogDetails(logId) {
    document.getElementById('log-details-modal').classList.remove('hidden');
    
    fetch(`/visitor-logs/${logId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('log-modal-content').innerHTML = data.html;
    })
    .catch(error => {
        document.getElementById('log-modal-content').innerHTML = '<p class="text-red-600">Error loading details</p>';
    });
}

function closeLogModal() {
    document.getElementById('log-details-modal').classList.add('hidden');
}

function viewRegistration(registrationId) {
    window.open(`/registrations/${registrationId}`, '_blank');
}

function exportTimeline() {
    const params = new URLSearchParams(window.location.search);
    params.set('format', 'csv');
    params.set('export', 'timeline');
    
    window.open(`{{ route('visitor-logs.export') }}?${params.toString()}`, '_blank');
}
</script>
@endpush

@push('styles')
<style>
/* Timeline styles */
.timeline-item:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease;
}

/* Responsive timeline */
@media (max-width: 768px) {
    .timeline-item {
        margin-left: 0;
    }
    
    .timeline-dot {
        position: relative;
        left: 0;
    }
    
    .timeline-line {
        display: none;
    }
}

/* Animation for timeline items */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.timeline-item {
    animation: fadeInUp 0.5s ease-out;
}
</style>
@endpush