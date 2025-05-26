@extends('layouts.app')

@section('title', 'Scan for Print')
@section('page-title', 'Registration Lookup & Print')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold mb-2">Registration Lookup</h2>
                <p class="text-indigo-100">Scan QR codes or search registrations for printing badges and materials</p>
            </div>
            <div class="text-right">
                <i class="fas fa-print text-4xl text-indigo-200"></i>
            </div>
        </div>
    </div>

    <!-- Main Interface -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- QR Scanner -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-qrcode mr-2 text-indigo-600"></i>
                    QR Code Scanner
                </h3>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500">Scanner:</span>
                    <span id="scanner-status" class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                        Ready
                    </span>
                </div>
            </div>
            
            <!-- Scanner Container -->
            <div class="relative mb-6">
                <div id="qr-reader" class="border-2 border-dashed border-gray-300 rounded-lg" style="min-height: 300px;"></div>
                <div id="qr-status" class="hidden text-center p-4 rounded-lg mt-4"></div>
                
                <!-- Scanner Overlay -->
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="w-48 h-48 border-2 border-indigo-500 rounded-lg relative">
                        <div class="absolute top-0 left-0 w-6 h-6 border-t-4 border-l-4 border-indigo-500"></div>
                        <div class="absolute top-0 right-0 w-6 h-6 border-t-4 border-r-4 border-indigo-500"></div>
                        <div class="absolute bottom-0 left-0 w-6 h-6 border-b-4 border-l-4 border-indigo-500"></div>
                        <div class="absolute bottom-0 right-0 w-6 h-6 border-b-4 border-r-4 border-indigo-500"></div>
                    </div>
                </div>
            </div>

            <!-- Scanner Controls -->
            <div class="flex justify-center space-x-4">
                <button id="start-scanner" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-play mr-2"></i>Start Scanner
                </button>
                <button id="stop-scanner" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors" disabled>
                    <i class="fas fa-stop mr-2"></i>Stop Scanner
                </button>
                <button id="toggle-camera" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-camera-rotate mr-2"></i>Switch Camera
                </button>
            </div>
        </div>

        <!-- Manual Search -->
        <div class="space-y-6">
            <!-- Registration ID Lookup -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-6">
                    <i class="fas fa-search mr-2 text-green-600"></i>
                    Manual Lookup
                </h3>

                <div id="lookup-result" class="hidden mb-4 p-4 rounded-lg"></div>

                <form id="lookup-form" class="space-y-4">
                    @csrf
                    <div>
                        <label for="registration_id" class="block text-sm font-medium text-gray-700 mb-2">Registration ID</label>
                        <div class="relative">
                            <input type="text" 
                                   id="registration_id" 
                                   name="registration_id"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="Enter registration ID">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-hashtag text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3 px-4 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-search mr-2"></i>Find Registration
                    </button>
                </form>
            </div>

            <!-- Advanced Search -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-6">
                    <i class="fas fa-filter mr-2 text-purple-600"></i>
                    Advanced Search
                </h3>

                <form id="advanced-search-form" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="user_name" class="block text-sm font-medium text-gray-700 mb-2">User Name</label>
                            <input type="text" 
                                   id="user_name" 
                                   name="user_name"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="Search by name">
                        </div>

                        <div>
                            <label for="user_email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" 
                                   id="user_email" 
                                   name="user_email"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="Search by email">
                        </div>
                    </div>

                    <div>
                        <label for="event_id" class="block text-sm font-medium text-gray-700 mb-2">Event</label>
                        <select id="event_id" 
                                name="event_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">All Events</option>
                            @foreach(\App\Models\Event::orderBy('name')->get() as $event)
                                <option value="{{ $event->id }}">{{ $event->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="w-full py-3 px-4 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-search mr-2"></i>Advanced Search
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Search Results -->
    <div id="search-results" class="hidden bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">
                <i class="fas fa-list mr-2 text-blue-600"></i>
                Search Results
            </h3>
            <button onclick="clearResults()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div id="results-container" class="space-y-3">
            <!-- Results will be populated here -->
        </div>
        
        <!-- Pagination -->
        <div id="pagination-container" class="mt-6 flex justify-center">
            <!-- Pagination will be populated here -->
        </div>
    </div>

    <!-- Registration Details Modal -->
    <div id="registration-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Registration Details</h3>
                        <button onclick="closeRegistrationModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <div id="modal-content">
                        <!-- Content will be populated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Lookups -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-900">
                <i class="fas fa-history mr-2 text-orange-600"></i>
                Recent Lookups
            </h3>
            <button onclick="clearRecentLookups()" class="text-gray-500 hover:text-gray-700 text-sm">
                Clear History
            </button>
        </div>
        
        <div id="recent-lookups" class="space-y-3">
            <!-- Recent lookups will be populated here -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let qrCodeScanner = null;
let isScanning = false;
let currentCamera = 'environment';
let recentLookups = JSON.parse(localStorage.getItem('recentLookups') || '[]');

document.addEventListener('DOMContentLoaded', function() {
    initializeQRScanner();
    setupEventListeners();
    displayRecentLookups();
});

// QR Scanner Functions
function initializeQRScanner() {
    qrCodeScanner = new Html5Qrcode("qr-reader");
    
    Html5Qrcode.getCameras().then(devices => {
        if (devices && devices.length) {
            updateScannerStatus('Ready', 'green');
        } else {
            updateScannerStatus('No cameras', 'red');
        }
    }).catch(err => {
        updateScannerStatus('Access denied', 'red');
        console.error('Camera initialization error:', err);
    });
}

function startScanning() {
    if (isScanning) return;
    
    const config = {
        fps: 10,
        qrbox: { width: 200, height: 200 },
        aspectRatio: 1.0
    };

    qrCodeScanner.start(
        { facingMode: currentCamera },
        config,
        onScanSuccess,
        onScanFailure
    ).then(() => {
        isScanning = true;
        updateScannerStatus('Scanning...', 'green');
        document.getElementById('start-scanner').disabled = true;
        document.getElementById('stop-scanner').disabled = false;
    }).catch(err => {
        updateScannerStatus('Failed to start', 'red');
        console.error('Start scanning error:', err);
    });
}

function stopScanning() {
    if (!isScanning) return;
    
    qrCodeScanner.stop().then(() => {
        isScanning = false;
        updateScannerStatus('Stopped', 'yellow');
        document.getElementById('start-scanner').disabled = false;
        document.getElementById('stop-scanner').disabled = true;
    }).catch(err => {
        console.error('Stop scanning error:', err);
    });
}

function toggleCamera() {
    stopScanning();
    currentCamera = currentCamera === 'environment' ? 'user' : 'environment';
    setTimeout(() => {
        startScanning();
    }, 500);
}

function onScanSuccess(decodedText, decodedResult) {
    qrCodeScanner.pause(true);
    
    showQRStatus('Processing QR code...', 'info');
    
    lookupRegistration(decodedText, 'qr_scan');
    
    setTimeout(() => {
        if (isScanning) {
            qrCodeScanner.resume();
        }
    }, 3000);
}

function onScanFailure(error) {
    // Silent fail for continuous scanning
}

// Lookup Functions
function lookupRegistration(registrationId, method = 'manual') {
    fetch('{{ route("checkin.verify-registration") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ registration_id: registrationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showRegistrationDetails(data.registration);
            addToRecentLookups(data.registration, method);
            
            if (method === 'qr_scan') {
                showQRStatus('Registration found!', 'success');
            } else {
                showLookupResult('Registration found!', 'success');
            }
        } else {
            if (method === 'qr_scan') {
                showQRStatus(data.message, 'error');
            } else {
                showLookupResult(data.message, 'error');
            }
        }
    })
    .catch(error => {
        console.error('Lookup error:', error);
        const message = 'Network error occurred';
        if (method === 'qr_scan') {
            showQRStatus(message, 'error');
        } else {
            showLookupResult(message, 'error');
        }
    });
}

function advancedSearch(formData) {
    const params = new URLSearchParams();
    
    for (let [key, value] of formData.entries()) {
        if (value.trim()) {
            params.append(key, value);
        }
    }
    
    fetch(`{{ route("registrations.search") }}?${params.toString()}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displaySearchResults(data.registrations, data.pagination);
        } else {
            showLookupResult('No registrations found', 'error');
        }
    })
    .catch(error => {
        console.error('Search error:', error);
        showLookupResult('Search failed', 'error');
    });
}

// Display Functions
function showRegistrationDetails(registration) {
    const modalContent = document.getElementById('modal-content');
    
    modalContent.innerHTML = `
        <div class="space-y-6">
            <!-- User Information -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-3">User Information</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-gray-600">Name:</span>
                        <p class="font-medium">${registration.user_name}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Email:</span>
                        <p class="font-medium">${registration.user_email}</p>
                    </div>
                </div>
            </div>

            <!-- Event Information -->
            <div class="bg-blue-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-3">Event Information</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-gray-600">Event:</span>
                        <p class="font-medium">${registration.event_name}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Registration ID:</span>
                        <p class="font-medium text-blue-600">${registration.id}</p>
                    </div>
                </div>
            </div>

            <!-- Status Information -->
            <div class="bg-green-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-3">Current Status</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-gray-600">Check-in Status:</span>
                        <p class="font-medium ${registration.is_checked_in ? 'text-green-600' : 'text-gray-600'}">
                            ${registration.is_checked_in ? 'Checked In' : 'Not Checked In'}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Last Action:</span>
                        <p class="font-medium">${registration.last_action || 'None'}</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-3 pt-4 border-t">
                <button onclick="printBadge(${registration.id})" 
                        class="flex-1 py-2 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-print mr-2"></i>Print Badge
                </button>
                <button onclick="viewFullDetails(${registration.id})" 
                        class="flex-1 py-2 px-4 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-eye mr-2"></i>Full Details
                </button>
                <button onclick="checkInOut(${registration.id})" 
                        class="flex-1 py-2 px-4 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    <i class="fas fa-sign-in-alt mr-2"></i>Check In/Out
                </button>
            </div>
        </div>
    `;
    
    document.getElementById('registration-modal').classList.remove('hidden');
}

function displaySearchResults(registrations, pagination) {
    const container = document.getElementById('results-container');
    const resultsSection = document.getElementById('search-results');
    
    if (registrations.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-search text-3xl mb-3"></i>
                <p>No registrations found</p>
            </div>
        `;
    } else {
        container.innerHTML = registrations.map(registration => `
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer"
                 onclick="showRegistrationDetails(${JSON.stringify(registration).replace(/"/g, '&quot;')})">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-blue-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">${registration.user_name}</p>
                        <p class="text-sm text-gray-600">${registration.user_email}</p>
                        <p class="text-xs text-gray-500">Event: ${registration.event_name}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-900">ID: ${registration.id}</p>
                    <span class="px-2 py-1 text-xs rounded-full ${registration.is_checked_in ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                        ${registration.is_checked_in ? 'Checked In' : 'Not Checked In'}
                    </span>
                </div>
            </div>
        `).join('');
        
        // Update pagination if needed
        if (pagination && pagination.total > pagination.per_page) {
            updatePagination(pagination);
        }
    }
    
    resultsSection.classList.remove('hidden');
}

function updatePagination(pagination) {
    // Implementation for pagination controls
    const container = document.getElementById('pagination-container');
    // Add pagination controls here
}

// UI Helper Functions
function updateScannerStatus(status, color) {
    const statusElement = document.getElementById('scanner-status');
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

function showLookupResult(message, type) {
    const resultElement = document.getElementById('lookup-result');
    const colorClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 
                      'bg-red-100 border-red-400 text-red-700';
    
    resultElement.className = `p-4 rounded-lg border ${colorClass}`;
    resultElement.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-3"></i>
            <span>${message}</span>
        </div>
    `;
    resultElement.classList.remove('hidden');
    
    setTimeout(() => {
        resultElement.classList.add('hidden');
    }, 5000);
}

// Modal Functions
function closeRegistrationModal() {
    document.getElementById('registration-modal').classList.add('hidden');
}

function clearResults() {
    document.getElementById('search-results').classList.add('hidden');
}

// Action Functions
function printBadge(registrationId) {
    window.open(`{{ route('registrations.badge', '') }}/${registrationId}`, '_blank');
}

function viewFullDetails(registrationId) {
    window.open(`{{ route('registrations.show', '') }}/${registrationId}`, '_blank');
}

function checkInOut(registrationId) {
    window.location.href = `{{ route('checkin.index') }}?registration_id=${registrationId}`;
}

// Recent Lookups Management
function addToRecentLookups(registration, method) {
    const lookup = {
        id: registration.id,
        user_name: registration.user_name,
        event_name: registration.event_name,
        method: method,
        timestamp: new Date().toISOString()
    };
    
    // Remove existing entry if present
    recentLookups = recentLookups.filter(item => item.id !== registration.id);
    
    // Add to beginning
    recentLookups.unshift(lookup);
    
    // Keep only last 10
    recentLookups = recentLookups.slice(0, 10);
    
    // Save to localStorage
    localStorage.setItem('recentLookups', JSON.stringify(recentLookups));
    
    displayRecentLookups();
}

function displayRecentLookups() {
    const container = document.getElementById('recent-lookups');
    
    if (recentLookups.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4 text-gray-500">
                <p>No recent lookups</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = recentLookups.map(lookup => `
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer"
             onclick="lookupRegistration('${lookup.id}')">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                    <i class="fas ${lookup.method === 'qr_scan' ? 'fa-qrcode' : 'fa-search'} text-orange-600 text-xs"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-900">${lookup.user_name}</p>
                    <p class="text-sm text-gray-600">${lookup.event_name}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500">${new Date(lookup.timestamp).toLocaleString()}</p>
                <p class="text-xs text-gray-400">ID: ${lookup.id}</p>
            </div>
        </div>
    `).join('');
}

function clearRecentLookups() {
    recentLookups = [];
    localStorage.removeItem('recentLookups');
    displayRecentLookups();
}

// Event Listeners
function setupEventListeners() {
    document.getElementById('start-scanner').addEventListener('click', startScanning);
    document.getElementById('stop-scanner').addEventListener('click', stopScanning);
    document.getElementById('toggle-camera').addEventListener('click', toggleCamera);
    
    // Manual lookup form
    document.getElementById('lookup-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const registrationId = document.getElementById('registration_id').value.trim();
        
        if (!registrationId) {
            showLookupResult('Please enter a registration ID', 'error');
            return;
        }
        
        lookupRegistration(registrationId, 'manual');
    });
    
    // Advanced search form
    document.getElementById('advanced-search-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        advancedSearch(formData);
    });
    
    // Enter key handling
    document.getElementById('registration_id').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('lookup-form').dispatchEvent(new Event('submit'));
        }
    });
    
    // Modal close on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeRegistrationModal();
        }
    });
    
    // Auto-start scanner
    setTimeout(startScanning, 1000);
}
</script>
@endpush

@push('styles')
<style>
.scanner-overlay {
    animation: scanner-line 2s linear infinite;
}

@keyframes scanner-line {
    0%, 100% { top: 0; }
    50% { top: calc(100% - 2px); }
}

#qr-reader video {
    border-radius: 8px;
}

.modal-backdrop {
    backdrop-filter: blur(4px);
}

.lookup-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
</style>
@endpush