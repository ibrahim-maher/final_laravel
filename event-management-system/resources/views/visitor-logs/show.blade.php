{{-- resources/views/visitor-logs/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Visitor Log Details')
@section('page-title', 'Log #' . $visitorLog->id)

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Visitor Log Details</h1>
            <p class="text-gray-600">Log ID: #{{ $visitorLog->id }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="/visitor-logs" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Logs
            </a>
            @if(auth()->user()->isAdmin())
            <button onclick="deleteLog({{ $visitorLog->id }})" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                <i class="fas fa-trash mr-2"></i>Delete Log
            </button>
            @endif
        </div>
    </div>

    <!-- Log Details -->
    @include('visitor-logs.partials.details', compact('visitorLog', 'relatedLogs', 'timeline'))
</div>

<!-- Confirmation Modal -->
<div id="confirm-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-gray-900 text-center mb-2">Confirm Deletion</h3>
                <p class="text-gray-600 text-center mb-6">Are you sure you want to delete this log? This action cannot be undone.</p>
                
                <div class="flex items-center justify-center space-x-3">
                    <button onclick="closeConfirmModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button id="confirm-delete-btn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteLog(logId) {
    document.getElementById('confirm-modal').classList.remove('hidden');
    
    document.getElementById('confirm-delete-btn').onclick = function() {
        fetch(`/visitor-logs/${logId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/visitor-logs';
            } else {
                alert('Failed to delete log');
            }
        })
        .catch(error => {
            alert('Error deleting log');
        });
    };
}

function closeConfirmModal() {
    document.getElementById('confirm-modal').classList.add('hidden');
}
</script>
@endpush