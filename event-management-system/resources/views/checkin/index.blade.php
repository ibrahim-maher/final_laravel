@extends('layouts.app')

@section('title', 'Check-in System')
@section('page-title', 'Event Check-in System')

@section('content')
<div class="space-y-6">
    <!-- Header Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Today's Check-ins</p>
                    <p class="text-3xl font-bold text-gray-900" id="today-checkins">{{ $todayStats['checkins'] }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-sign-in-alt text-2xl text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Today's Check-outs</p>
                    <p class="text-3xl font-bold text-gray-900" id="today-checkouts">{{ $todayStats['checkouts'] }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-sign-out-alt text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Active Visitors</p>
                    <p class="text-3xl font-bold text-gray-900" id="active-visitors">{{ $todayStats['active_visitors'] }}</p>
                </div>
                <div class="bg-orange-100 p-3 rounded-full">
                    <i class="fas fa-users text-2xl text-orange-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Active Events</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $todayStats['events_active'] }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-calendar-check text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Scanner Interface -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- QR Scanner -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-qrcode mr-2 text-blue-600"></i>
                    QR Code Scanner
                </h2>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500">Camera Status:</span>
                    <span id="camera-status" class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                        Initializing...
                    </span>
                </div>
            </div>
            
            <!-- Scanner Container -->
            <div class="relative">
                <div id="qr-reader" class="border-2 border-dashed border-gray-300 rounded-lg mb-4" style="min-height: 300px;"></div>
                <div id="qr-status" class="hidden text-center p-4 rounded-lg mb-4"></div>
            </div>

            <!-- Scanner Controls -->
            <div class="flex justify-center space-x-4">
                <button id="start-scanner" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-play mr-2"></i>Start Scanner
                </button>
                <button id="stop-scanner" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-stop mr-2"></i>Stop Scanner
                </button>
                <button id="switch-camera" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-camera-rotate mr-2"></i>Switch Camera
                </button>
            </div>
        </div>

        <!-- Manual Entry -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">
                <i class="fas fa-keyboard mr-2 text-green-600"></i>
                Manual Entry
            </h2>

            <!-- Results Display -->
            <div id="scan-result" class="hidden mb-6 p-4 rounded-lg"></div>

            <!-- Manual Form -->
            <form id="manual-checkin-form" class="space-y-4">
                @csrf
                <div>
                    <label for="registration_id" class="block text-sm font-medium text-gray-700 mb-2">Registration ID</label>
                    <div class="relative">
                        <input type="text" 
                               id="registration_id" 
                               name="registration_id"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Enter registration ID"
                               autocomplete="off">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i class="fas fa-hashtag text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="admin_note" class="block text-sm font-medium text-gray-700 mb-2">Admin Note (Optional)</label>
                    <textarea id="admin_note" 
                              name="admin_note"
                              rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Add any notes..."></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button type="button" 
                            onclick="processAction('checkin')"
                            class="w-full py-3 px-4 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i>Check In
                    </button>
                    <button type="button" 
                            onclick="processAction('checkout')"
                            class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-sign-out-alt mr-2"></i>Check Out
                    </button>
                </div>

                <button type="button" 
                        onclick="processAction('auto')"
                        class="w-full py-3 px-4 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                    <i class="fas fa-magic mr-2"></i>Auto Process
                </button>
            </form>
        </div>
    </div>

    <!-- Recent Activity & Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-history mr-2 text-blue-600"></i>
                    Recent Activity
                </h3>
                <a href="{{ route('visitor-logs.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    View All
                </a>
            </div>
            
            <div id="recent-activity" class="space-y-3">
                @forelse($recentActivity as $activity)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full {{ $activity['action'] === 'checkin' ? 'bg-green-100' : 'bg-blue-100' }} flex items-center justify-center">
                            <i class="fas {{ $activity['action'] === 'checkin' ? 'fa-sign-in-alt text-green-600' : 'fa-sign-out-alt text-blue-600' }}"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $activity['user_name'] }}</p>
                            <p class="text-sm text-gray-600">{{ $activity['event_name'] }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-1 text-xs rounded-full {{ $activity['action'] === 'checkin' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ ucfirst($activity['action']) }}
                        </span>
                        <div class="text-xs text-gray-500 mt-1">{{ $activity['timestamp'] }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-inbox text-3xl mb-3"></i>
                    <p>No recent activity</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Quick Analytics -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6">
                <i class="fas fa-chart-line mr-2 text-purple-600"></i>
                Today's Analytics
            </h3>
            
            <!-- Hourly Chart -->
            <div class="mb-6">
                <canvas id="hourly-chart" height="200"></canvas>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-2 gap-4">
                <div class="text-center p-3 bg-green-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-600" id="completion-rate">0%</div>
                    <div class="text-sm text-gray-600">Completion Rate</div>
                </div>
                <div class="text-center p-3 bg-blue-50 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600" id="avg-duration">0m</div>
                    <div class="text-sm text-gray-600">Avg Duration</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sound Effects -->
<audio id="success-sound" preload="auto">
    <source src="{{ asset('sounds/success.mp3') }}" type="audio/mpeg">
</audio>
<audio id="error-sound" preload="auto">
    <source src="{{ asset('sounds/error.mp3') }}" type="audio/mpeg">
</audio>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let qrCodeScanner = null;
let isScanning = false;
let currentCamera = 'environment';
let cameras = [];

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeQRScanner();
    initializeAnalytics();
    setupEventListeners();
    startRealTimeUpdates();
});

// QR Scanner Functions
function initializeQRScanner() {
    qrCodeScanner = new Html5Qrcode("qr-reader");
    
    // Get available cameras
    Html5Qrcode.getCameras().then(devices => {
        cameras = devices;
        if (devices && devices.length) {
            updateCameraStatus('Ready', 'green');
        } else {
            updateCameraStatus('No cameras found', 'red');
        }
    }).catch(err => {
        updateCameraStatus('Camera access denied', 'red');
        console.error('Camera initialization error:', err);
    });
}

function startScanning() {
    if (isScanning) return;
    
    const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0
    };

    qrCodeScanner.start(
        { facingMode: currentCamera },
        config,
        onScanSuccess,
        onScanFailure
    ).then(() => {
        isScanning = true;
        updateCameraStatus('Scanning...', 'green');
        document.getElementById('start-scanner').disabled = true;
        document.getElementById('stop-scanner').disabled = false;
    }).catch(err => {
        updateCameraStatus('Failed to start', 'red');
        console.error('Start scanning error:', err);
    });
}

function stopScanning() {
    if (!isScanning) return;
    
    qrCodeScanner.stop().then(() => {
        isScanning = false;
        updateCameraStatus('Stopped', 'yellow');
        document.getElementById('start-scanner').disabled = false;
        document.getElementById('stop-scanner').disabled = true;
    }).catch(err => {
        console.error('Stop scanning error:', err);
    });
}

function switchCamera() {
    if (cameras.length <= 1) return;
    
    stopScanning();
    currentCamera = currentCamera === 'environment' ? 'user' : 'environment';
    
    setTimeout(() => {
        startScanning();
    }, 500);
}

function onScanSuccess(decodedText, decodedResult) {
    // Pause scanning temporarily
    qrCodeScanner.pause(true);
    
    showQRStatus('Processing QR code...', 'info');
    
    // Process the scanned QR code
    processQRCode(decodedText);
    
    // Resume scanning after delay
    setTimeout(() => {
        if (isScanning) {
            qrCodeScanner.resume();
        }
    }, 3000);
}

function onScanFailure(error) {
    // Silent fail for continuous scanning
}

function processQRCode(registrationId) {
    fetch('{{ route("checkin.scan") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            registration_id: registrationId,
            action: 'auto',
            device_info: {
                method: 'qr_scan',
                user_agent: navigator.userAgent,
                timestamp: new Date().toISOString()
            }
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showScanResult(data, 'success');
            playSound('success');
            updateStats();
            addToRecentActivity(data);
        } else {
            showScanResult(data, 'error');
            playSound('error');
        }
    })
    .catch(error => {
        console.error('QR processing error:', error);
        showQRStatus('Network error occurred', 'error');
        playSound('error');
    });
}

// Manual Form Processing
function processAction(action) {
    const registrationId = document.getElementById('registration_id').value.trim();
    const adminNote = document.getElementById('admin_note').value.trim();
    
    if (!registrationId) {
        showScanResult({ message: 'Please enter a registration ID' }, 'error');
        return;
    }
    
    const data = {
        registration_id: registrationId,
        action: action,
        admin_note: adminNote,
        device_info: {
            method: 'manual',
            user_agent: navigator.userAgent,
            timestamp: new Date().toISOString()
        }
    };
    
    fetch('{{ route("checkin.scan") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showScanResult(data, 'success');
            playSound('success');
            updateStats();
            addToRecentActivity(data);
            
            // Clear form
            document.getElementById('registration_id').value = '';
            document.getElementById('admin_note').value = '';
        } else {
            showScanResult(data, 'error');
            playSound('error');
        }
    })
    .catch(error => {
        console.error('Manual processing error:', error);
        showScanResult({ message: 'Network error occurred' }, 'error');
        playSound('error');
    });
}

// UI Update Functions
function updateCameraStatus(status, color) {
    const statusElement = document.getElementById('camera-status');
    statusElement.textContent = status;
    statusElement.className = `px-2 py-1 text-xs rounded-full bg-${color}-100 text-${color}-800`;
}

function showQRStatus(message, type) {
    const statusElement = document.getElementById('qr-status');
    const colorClass = type === 'success' ? 'bg-green-100 text-green-800' : 
                      type === 'error' ? 'bg-red-100 text-red-800' : 
                      'bg-blue-100 text-blue-800';
    
    statusElement.className = `text-center p-4 rounded-lg ${colorClass}`;
    statusElement.textContent = message;
    statusElement.classList.remove('hidden');
    
    setTimeout(() => {
        statusElement.classList.add('hidden');
    }, 3000);
}

function showScanResult(data, type) {
    const resultElement = document.getElementById('scan-result');
    const colorClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 
                      'bg-red-100 border-red-400 text-red-700';
    
    resultElement.className = `p-4 rounded-lg border ${colorClass}`;
    
    if (type === 'success') {
        resultElement.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-3 text-xl"></i>
                <div>
                    <p class="font-bold">${data.action === 'checkin' ? 'Check-in' : 'Check-out'} Successful!</p>
                    <p class="text-sm">${data.user} - ${data.event}</p>
                    <p class="text-xs">${data.timestamp}</p>
                    ${data.duration ? `<p class="text-xs">Duration: ${data.duration}</p>` : ''}
                </div>
            </div>
        `;
    } else {
        resultElement.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
                <div>
                    <p class="font-bold">Error</p>
                    <p class="text-sm">${data.message}</p>
                </div>
            </div>
        `;
    }
    
    resultElement.classList.remove('hidden');
    
    setTimeout(() => {
        resultElement.classList.add('hidden');
    }, 5000);
}

function updateStats() {

}

function addToRecentActivity(activityData) {
    const container = document.getElementById('recent-activity');
    const activityHtml = `
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full ${activityData.action === 'checkin' ? 'bg-green-100' : 'bg-blue-100'} flex items-center justify-center">
                    <i class="fas ${activityData.action === 'checkin' ? 'fa-sign-in-alt text-green-600' : 'fa-sign-out-alt text-blue-600'}"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-900">${activityData.user}</p>
                    <p class="text-sm text-gray-600">${activityData.event}</p>
                </div>
            </div>
            <div class="text-right">
                <span class="px-2 py-1 text-xs rounded-full ${activityData.action === 'checkin' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'}">
                    ${activityData.action.charAt(0).toUpperCase() + activityData.action.slice(1)}
                </span>
                <div class="text-xs text-gray-500 mt-1">Just now</div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('afterbegin', activityHtml);
    
    // Remove oldest if more than 10
    const activities = container.children;
    if (activities.length > 10) {
        activities[activities.length - 1].remove();
    }
}

function playSound(type) {
    const audio = document.getElementById(type + '-sound');
    if (audio) {
        audio.currentTime = 0;
        audio.play().catch(e => console.log('Sound play failed:', e));
    }
}

// Real-time Updates
function startRealTimeUpdates() {
    setInterval(updateStats, 30000); // Update every 30 seconds
}

// Analytics
function initializeAnalytics() {
    loadHourlyChart();
}

function loadHourlyChart() {
}

// Event Listeners
function setupEventListeners() {
    document.getElementById('start-scanner').addEventListener('click', startScanning);
    document.getElementById('stop-scanner').addEventListener('click', stopScanning);
    document.getElementById('switch-camera').addEventListener('click', switchCamera);
    
    // Auto-start scanner
    setTimeout(startScanning, 1000);
    
    // Focus on registration ID input
    document.getElementById('registration_id').focus();
    
    // Enter key handling
    document.getElementById('registration_id').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            processAction('auto');
        }
    });
}

// Error handling
window.addEventListener('error', function(e) {
    console.error('Global error:', e);
});

// Page visibility handling
document.addEventListener('visibilitychange', function() {
    if (document.hidden && isScanning) {
        stopScanning();
    } else if (!document.hidden && !isScanning) {
        setTimeout(startScanning, 1000);
    }
});
</script>
@endpush

@push('styles')
<style>
#qr-reader video {
    border-radius: 8px;
}

.scanner-overlay {
    position: relative;
}

.scanner-overlay::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 200px;
    height: 200px;
    margin: -100px 0 0 -100px;
    border: 2px solid #3b82f6;
    border-radius: 8px;
    pointer-events: none;
    z-index: 10;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.scanning-indicator {
    animation: pulse 2s infinite;
}
</style>
@endpush