@extends('layouts.app')

@section('title', 'Check-in System')
@section('page-title', 'QR Code Scanner')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Scanner Card -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-center mb-6">
            <i class="fas fa-qrcode text-6xl text-blue-600 mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-900">QR Code Scanner</h2>
            <p class="text-gray-600">Scan visitor QR codes for check-in/check-out</p>
        </div>
        
        <!-- Manual Input -->
        <div class="space-y-4">
            <div>
                <label for="registration_id" class="block text-sm font-medium text-gray-700 mb-2">Registration ID</label>
                <div class="flex gap-2">
                    <input type="text" id="registration_id" placeholder="Enter or scan registration ID"
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <button onclick="processCheckin()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                        Process
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Results -->
    <div id="results" class="hidden bg-white rounded-lg shadow p-6">
        <div id="success-result" class="hidden text-center">
            <i class="fas fa-check-circle text-6xl text-green-600 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Success!</h3>
            <div id="result-details" class="text-gray-600"></div>
        </div>
        
        <div id="error-result" class="hidden text-center">
            <i class="fas fa-times-circle text-6xl text-red-600 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Error</h3>
            <div id="error-details" class="text-gray-600"></div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Recent Activity</h3>
        <div id="recent-activity" class="space-y-2">
            <!-- Activity items will be added here -->
        </div>
    </div>
</div>

@push('scripts')
<script>
let recentActivity = [];

function processCheckin() {
    const registrationId = document.getElementById('registration_id').value.trim();
    
    if (!registrationId) {
        showError('Please enter a registration ID');
        return;
    }
    
    fetch('{{ route("checkin.scan") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            registration_id: registrationId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data);
            addToRecentActivity(data);
            document.getElementById('registration_id').value = '';
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('An error occurred. Please try again.');
    });
}

function showSuccess(data) {
    const resultsDiv = document.getElementById('results');
    const successDiv = document.getElementById('success-result');
    const errorDiv = document.getElementById('error-result');
    const detailsDiv = document.getElementById('result-details');
    
    detailsDiv.innerHTML = `
        <p><strong>${data.user}</strong> has been <strong>${data.action === 'checkin' ? 'checked in' : 'checked out'}</strong></p>
        <p>Event: ${data.event}</p>
        <p>Time: ${data.timestamp}</p>
    `;
    
    errorDiv.classList.add('hidden');
    successDiv.classList.remove('hidden');
    resultsDiv.classList.remove('hidden');
    
    setTimeout(() => {
        resultsDiv.classList.add('hidden');
    }, 5000);
}

function showError(message) {
    const resultsDiv = document.getElementById('results');
    const successDiv = document.getElementById('success-result');
    const errorDiv = document.getElementById('error-result');
    const detailsDiv = document.getElementById('error-details');
    
    detailsDiv.textContent = message;
    
    successDiv.classList.add('hidden');
    errorDiv.classList.remove('hidden');
    resultsDiv.classList.remove('hidden');
    
    setTimeout(() => {
        resultsDiv.classList.add('hidden');
    }, 5000);
}

function addToRecentActivity(data) {
    recentActivity.unshift(data);
    if (recentActivity.length > 10) {
        recentActivity.pop();
    }
    
    updateRecentActivity();
}

function updateRecentActivity() {
    const container = document.getElementById('recent-activity');
    
    if (recentActivity.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center">No recent activity</p>';
        return;
    }
    
    container.innerHTML = recentActivity.map(activity => `
        <div class="flex justify-between items-center py-2 border-b border-gray-200">
            <div>
                <span class="font-medium">${activity.user}</span>
                <span class="text-sm text-gray-500 ml-2">${activity.event}</span>
            </div>
            <div class="text-right">
                <span class="px-2 py-1 text-xs rounded-full ${activity.action === 'checkin' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'}">
                    ${activity.action === 'checkin' ? 'Check-in' : 'Check-out'}
                </span>
                <div class="text-xs text-gray-500">${activity.timestamp}</div>
            </div>
        </div>
    `).join('');
}

// Allow Enter key to process
document.getElementById('registration_id').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        processCheckin();
    }
});

// Focus on input field when page loads
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('registration_id').focus();
});
</script>
@endpush
@endsection