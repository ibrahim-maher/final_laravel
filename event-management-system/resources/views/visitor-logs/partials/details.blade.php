{{-- resources/views/visitor-logs/partials/details.blade.php --}}
<div class="space-y-6">
    <!-- Basic Information -->
    <div class="bg-gray-50 rounded-lg p-6">
        <h4 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-info-circle mr-2 text-blue-600"></i>
            Basic Information
        </h4>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Log ID</label>
                <p class="mt-1 text-sm text-gray-900 font-mono">#{{ $visitorLog->id }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Action</label>
                <p class="mt-1">
                    @if($visitorLog->action === 'checkin')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-sign-in-alt mr-1"></i>
                            Check-in
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-sign-out-alt mr-1"></i>
                            Check-out
                        </span>
                    @endif
                </p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Timestamp</label>
                <p class="mt-1 text-sm text-gray-900">
                    {{ $visitorLog->created_at->format('M d, Y \a\t H:i:s') }}
                    <span class="text-gray-500">({{ $visitorLog->created_at->diffForHumans() }})</span>
                </p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Duration</label>
                <p class="mt-1 text-sm text-gray-900">
                    @if($visitorLog->duration_minutes)
                        <span class="text-purple-600 font-medium">{{ $visitorLog->duration_minutes }} minutes</span>
                        <span class="text-gray-500">({{ number_format($visitorLog->duration_minutes / 60, 2) }} hours)</span>
                    @else
                        <span class="text-gray-400">Not calculated</span>
                    @endif
                </p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Method</label>
                <p class="mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $visitorLog->qr_scanned ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                        <i class="fas {{ $visitorLog->qr_scanned ? 'fa-qrcode' : 'fa-keyboard' }} mr-1"></i>
                        {{ $visitorLog->qr_scanned ? 'QR Code Scan' : 'Manual Entry' }}
                    </span>
                </p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Created By</label>
                <p class="mt-1 text-sm text-gray-900">
                    {{ $visitorLog->creator->name ?? 'System' }}
                    @if($visitorLog->creator)
                        <span class="text-gray-500">({{ $visitorLog->creator->email }})</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- User Information -->
    <div class="bg-gray-50 rounded-lg p-6">
        <h4 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-user mr-2 text-green-600"></i>
            Visitor Information
        </h4>
        
        <div class="flex items-start space-x-4">
            <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center">
                @if($visitorLog->registration->user->avatar)
                    <img src="{{ $visitorLog->registration->user->avatar }}" alt="Avatar" class="w-16 h-16 rounded-full object-cover">
                @else
                    <i class="fas fa-user text-gray-600 text-2xl"></i>
                @endif
            </div>
            
            <div class="flex-1">
                <h5 class="text-lg font-medium text-gray-900">{{ $visitorLog->registration->user->name }}</h5>
                <p class="text-gray-600">{{ $visitorLog->registration->user->email }}</p>
                
                @if($visitorLog->registration->user->phone)
                    <p class="text-gray-600">
                        <i class="fas fa-phone mr-1"></i>
                        {{ $visitorLog->registration->user->phone }}
                    </p>
                @endif
                
                @if($visitorLog->registration->user->company)
                    <p class="text-gray-600">
                        <i class="fas fa-building mr-1"></i>
                        {{ $visitorLog->registration->user->company }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Event Information -->
    <div class="bg-gray-50 rounded-lg p-6">
        <h4 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-calendar-alt mr-2 text-purple-600"></i>
            Event Information
        </h4>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Event Name</label>
                <p class="mt-1 text-sm text-gray-900 font-medium">{{ $visitorLog->registration->event->name }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Registration ID</label>
                <p class="mt-1 text-sm text-gray-900 font-mono">#{{ $visitorLog->registration->id }}</p>
            </div>
            
            @if($visitorLog->registration->event->description)
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Event Description</label>
                <p class="mt-1 text-sm text-gray-900">{{ Str::limit($visitorLog->registration->event->description, 200) }}</p>
            </div>
            @endif
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Event Start</label>
                <p class="mt-1 text-sm text-gray-900">{{ $visitorLog->registration->event->start_date->format('M d, Y H:i') }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Event End</label>
                <p class="mt-1 text-sm text-gray-900">{{ $visitorLog->registration->event->end_date->format('M d, Y H:i') }}</p>
            </div>
        </div>
    </div>

    <!-- Technical Details -->
    <div class="bg-gray-50 rounded-lg p-6">
        <h4 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-cog mr-2 text-gray-600"></i>
            Technical Details
        </h4>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if($visitorLog->ip_address)
            <div>
                <label class="block text-sm font-medium text-gray-700">IP Address</label>
                <p class="mt-1 text-sm text-gray-900 font-mono">{{ $visitorLog->ip_address }}</p>
            </div>
            @endif
            
            @if($visitorLog->user_agent)
            <div>
                <label class="block text-sm font-medium text-gray-700">User Agent</label>
                <p class="mt-1 text-sm text-gray-900 truncate" title="{{ $visitorLog->user_agent }}">{{ Str::limit($visitorLog->user_agent, 50) }}</p>
            </div>
            @endif
            
            @if($visitorLog->device_info)
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Device Information</label>
                <div class="mt-1 bg-white rounded border p-3">
                    <pre class="text-xs text-gray-700 whitespace-pre-wrap">{{ json_encode($visitorLog->device_info, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif
            
            @if($visitorLog->location_data)
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Location Data</label>
                <div class="mt-1 bg-white rounded border p-3">
                    <pre class="text-xs text-gray-700 whitespace-pre-wrap">{{ json_encode($visitorLog->location_data, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif
        </div>
    </div>

    @if($visitorLog->admin_note)
    <!-- Admin Notes -->
    <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded-lg p-6">
        <h4 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-sticky-note mr-2 text-yellow-600"></i>
            Admin Notes
        </h4>
        <p class="text-gray-700">{{ $visitorLog->admin_note }}</p>
    </div>
    @endif

    <!-- Related Logs -->
    @if($relatedLogs->count() > 0)
    <div class="bg-gray-50 rounded-lg p-6">
        <h4 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-history mr-2 text-indigo-600"></i>
            Related Activity ({{ $relatedLogs->count() }} items)
        </h4>
        
        <div class="space-y-3">
            @foreach($relatedLogs as $relatedLog)
            <div class="flex items-center justify-between bg-white rounded-lg p-3 border">
                <div class="flex items-center space-x-3">
                    @if($relatedLog->action === 'checkin')
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    @else
                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                    @endif
                    
                    <div>
                        <p class="text-sm font-medium text-gray-900">
                            {{ ucfirst($relatedLog->action) }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ $relatedLog->created_at->format('M d, Y H:i') }}
                        </p>
                    </div>
                </div>
                
                <div class="text-right">
                    @if($relatedLog->duration_minutes)
                        <p class="text-sm text-purple-600 font-medium">{{ $relatedLog->duration_minutes }}m</p>
                    @endif
                    <p class="text-xs text-gray-500">by {{ $relatedLog->creator->name ?? 'System' }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Timeline -->
    @if($timeline->count() > 1)
    <div class="bg-gray-50 rounded-lg p-6">
        <h4 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-clock mr-2 text-blue-600"></i>
            Visit Timeline
        </h4>
        
        <div class="relative">
            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-300"></div>
            
            <div class="space-y-4">
                @foreach($timeline as $timelineLog)
                <div class="relative flex items-start space-x-4">
                    <div class="relative z-10 flex items-center justify-center w-8 h-8 {{ $timelineLog->action === 'checkin' ? 'bg-green-500' : 'bg-blue-500' }} rounded-full">
                        <i class="fas {{ $timelineLog->action === 'checkin' ? 'fa-sign-in-alt' : 'fa-sign-out-alt' }} text-white text-xs"></i>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-900">
                                {{ ucfirst($timelineLog->action) }}
                                @if($timelineLog->id === $visitorLog->id)
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        Current
                                    </span>
                                @endif
                            </p>
                            @if($timelineLog->duration_minutes)
                                <span class="text-sm text-purple-600 font-medium">{{ $timelineLog->duration_minutes }}m</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500">
                            {{ $timelineLog->created_at->format('M d, Y \a\t H:i:s') }}
                            <span class="text-gray-400">• by {{ $timelineLog->creator->name ?? 'System' }}</span>
                        </p>
                        @if($timelineLog->admin_note)
                            <p class="text-xs text-gray-600 mt-1 italic">{{ Str::limit($timelineLog->admin_note, 100) }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Actions -->
    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
        <div class="flex items-center space-x-3">
            <a href="/registrations/{{ $visitorLog->registration->id }}" 
               target="_blank"
               class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-user mr-2"></i>
                View Registration
            </a>
            
            <a href="/events/{{ $visitorLog->registration->event->id }}" 
               target="_blank"
               class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors">
                <i class="fas fa-calendar-alt mr-2"></i>
                View Event
            </a>
        </div>
        
        @if(auth()->user()->isAdmin())
        <div class="flex items-center space-x-3">
            <button onclick="editLogNote({{ $visitorLog->id }})" 
                    class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 transition-colors">
                <i class="fas fa-edit mr-2"></i>
                Edit Note
            </button>
            
            <button onclick="deleteLog({{ $visitorLog->id }})" 
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                <i class="fas fa-trash mr-2"></i>
                Delete Log
            </button>
        </div>
        @endif
    </div>
</div>

<script>
function editLogNote(logId) {
    // Implementation for editing admin notes
    const currentNote = '{{ $visitorLog->admin_note ?? '' }}';
    const newNote = prompt('Edit admin note:', currentNote);
    
    if (newNote !== null && newNote !== currentNote) {
        fetch(`/visitor-logs/${logId}/note`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ admin_note: newNote })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to update note');
            }
        })
        .catch(error => {
            alert('Error updating note');
        });
    }
}
</script>