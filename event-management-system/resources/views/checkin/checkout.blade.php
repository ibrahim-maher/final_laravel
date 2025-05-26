@extends('layouts.app')

@section('title', 'Check-out System')
@section('page-title', 'Event Check-out System')

@section('content')
<div class="space-y-6">
    <!-- Header with Active Visitors Summary -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold mb-2">Check-out System</h2>
                <p class="text-blue-100">Process visitor departures and track duration</p>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold" id="total-active">{{ count($activeVisitors) }}</div>
                <div class="text-sm text-blue-100">Active Visitors</div>
            </div>
        </div>
    </div>

    <!-- Main Interface -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Scanner & Manual Entry -->
        <div class="space-y-6">
            <!-- QR Scanner -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900">
                        <i class="fas fa-qrcode mr-2 text-blue-600"></i>
                        QR Code Scanner
                    </h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">Status:</span>
                        <span id="scanner-status" class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                            Ready
                        </span>
                    </div>
                </div>
                
                <div class="relative">
                    <div id="qr-reader" class="border-2 border-dashed border-gray-300 rounded-lg mb-4" style="min-height: 250px;"></div>
                    <div id="qr-result" class="hidden mb-4 p-4 rounded-lg"></div>
                </div>

                <div class="flex justify-center space-x-3">
                    <button id="start-scanner" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-play mr-2"></i>Start
                    </button>
                    <button id="stop-scanner" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors" disabled>
                        <i class="fas fa-stop mr-2"></i>Stop
                    </button>
                </div>
            </div>

            <!-- Manual Check-out -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6">
                    <i class="fas fa-edit mr-2 text-green-600"></i>
                    Manual Check-out
                </h3>

                <div id="checkout-result" class="hidden mb-4 p-4 rounded-lg"></div>

                <form id="manual-checkout-form" class="space-y-4">
                    @csrf
                    <div>
                        <label for="registration_id" class="block text-sm font-medium text-gray-700 mb-2">Registration ID</label>
                        <input type="text" 
                               id="registration_id" 
                               name="registration_id"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Enter registration ID">
                    </div>

                    <div>
                        <label for="admin_note" class="block text-sm font-medium text-gray-700 mb-2">Departure Note (Optional)</label>
                        <textarea id="admin_note" 
                                  name="admin_note"
                                  rows="2"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Any additional notes..."></textarea>
                    </div>

                    <button type="submit" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-sign-out-alt mr-2"></i>Process Check-out
                    </button>
                </form>
            </div>
        </div>

        <!-- Active Visitors List -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-users mr-2 text-orange-600"></i>
                    Currently Active Visitors
                </h3>
                <button onclick="refreshActiveVisitors()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    <i class="fas fa-sync-alt mr-1"></i>Refresh
                </button>
            </div>

            <!-- Search/Filter -->
            <div class="mb-4">
                <div class="relative">
                    <input type="text" 
                           id="visitor-search" 
                           placeholder="Search active visitors..."
                           class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
            </div>

            <!-- Active Visitors List -->
            <div id="active-visitors-list" class="space-y-3 max-h-96 overflow-y-auto">
                @forelse($activeVisitors as $visitor)
                <div class="visitor-item flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors" 
                     data-registration-id="{{ $visitor['registration_id'] }}"
                     data-user-name="{{ strtolower($visitor['user_name']) }}">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-green-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $visitor['user_name'] }}</p>
                            <p class="text-sm text-gray-600">{{ $visitor['event_name'] }}</p>
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-clock mr-1"></i>
                                Checked in: {{ $visitor['checked_in_at'] ?? 'Unknown' }}
                                @if($visitor['duration'])
                                    <span class="ml-2 text-orange-600">({{ $visitor['duration'] }})</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <button onclick="quickCheckout('{{ $visitor['registration_id'] }}', '{{ $visitor['user_name'] }}')" 
                            class="px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-sign-out-alt mr-1"></i>Check Out
                    </button>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-users-slash text-3xl mb-3"></i>
                    <p>No active visitors</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Check-outs -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-900">
                <i class="fas fa-history mr-2 text-purple-600"></i>
                Recent Check-outs
            </h3>
            <a href="{{ route('visitor-logs.index') }}?action=checkout" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                View All Check-outs
            </a>
        </div>
        
        <div id="recent-checkouts" class="space-y-3">
            @forelse($recentActivity->where('action', 'checkout')->take(5) as $activity)
            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-sign-out-alt text-blue-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ $activity['user_name'] }}</p>
                        <p class="text-sm text-gray-600">{{ $activity['event_name'] }}</p>
                        @if($activity['duration'])
                        <p class="text-xs text-blue-600">
                            <i class="fas fa-stopwatch mr-1"></i>
                            Duration: {{ $activity['duration'] }}
                        </p>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm font-medium text-gray-900">{{ $activity['timestamp'] }}</div>
                    <div class="text-xs text-gray-500">by {{ $activity['created_by'] }}</div>
                </div>
            </div>
            @empty
            <div class="text-center py-4 text-gray-500">
                <p>No recent check-outs</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Duration Analytics -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-6">
            <i class="fas fa-chart-bar mr-2 text-green-600"></i>
            Duration Analytics
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <div class="text-2xl font-bold text-green-600" id="avg-duration">0m</div>
                <div class="text-sm text-gray-600">Average Duration</div>
            </div>
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <div class="text-2xl font-bold text-blue-600" id="total-completed">0</div>
                <div class="text-sm text-gray-600">Completed Visits</div>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <div class="text-2xl font-bold text-purple-600" id="min-duration">0m</div>
                <div class="text-sm text-gray-600">Shortest Visit</div>
            </div>
            <div class="text-center p-4 bg-orange-50 rounded-lg">
                <div class="text-2xl font-bold text-orange-600" id="max-duration">0m</div>
                <div class="text-sm text-gray-600">Longest Visit</div>
            </div>
        </div>

        <div class="mb-4">
            <canvas id="duration-chart" height="100"></canvas>
        </div>
    </div>
</div>

<!-- Quick Action Modal -->
<div id="quick-checkout-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Quick Check-out</h3>
                <button onclick="closeQuickCheckoutModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="modal-visitor-info" class="mb-4 p-3 bg-gray-50 rounded-lg">
                <!-- Visitor info will be populated here -->
            </div>

            <div class="mb-4">
                <label for="modal-admin-note" class="block text-sm font-medium text-gray-700 mb-2">Departure Note (Optional)</label>
                <textarea id="modal-admin-note" 
                          rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          placeholder="Any additional notes..."></textarea>
            </div>

            <div class="flex space-x-3">
                <button onclick="closeQuickCheckoutModal()" 
                        class="flex-1 py-2 px-4 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
                <button onclick="confirmQuickCheckout()" 
                        class="flex-1 py-2 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-sign-out-alt mr-2"></i>Check Out
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let qrCodeScanner = null;
let isScanning = false;
let quickCheckoutData = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeQRScanner();
    initializeDurationChart();
    setupEventListeners();
    loadAnalytics();
    startRealTimeUpdates();
});

// QR Scanner Functions
function initializeQRScanner() {
    qrCodeScanner = new Html5Qrcode("qr-reader");
}

function startScanning() {
    if (isScanning) return;
    
    const config = {
        fps: 10,
        qrbox: { width: 200, height: 200 }
    };

    qrCodeScanner.start(
        { facingMode: "environment" },
        config,
        onScanSuccess,
        onScanFailure
    ).then(() => {
        isScanning = true;
        updateScannerStatus('Scanning...', 'green');
        document.getElementById('start-scanner').disabled = true;
        document.getElementById('stop-scanner').disabled = false;
    }).catch(err => {
        console.error('Scanner start error:', err);
        updateScannerStatus('Failed to start', 'red');
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
        console.error('Scanner stop error:', err);
    });
}

function onScanSuccess(decodedText, decodedResult) {
    qrCodeScanner.pause(true);
    
    showQRResult('Processing...', 'info');
    
    processCheckout(decodedText, null, 'qr_scan');
    
    setTimeout(() => {
        if (isScanning) {
            qrCodeScanner.resume();
        }
    }, 3000);
}

function onScanFailure(error) {
    // Silent fail for continuous scanning
}

// Checkout Processing
function processCheckout(registrationId, adminNote = null, method = 'manual') {
    const data = {
        registration_id: registrationId,
        action: 'checkout',
        admin_note: adminNote,
        device_info: {
            method: method,
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
            showResult(data, 'success', method === 'qr_scan' ? 'qr' : 'form');
            updateActiveVisitors();
            addToRecentCheckouts(data);
            loadAnalytics();
            
            // Clear form if manual
            if (method === 'manual') {
                document.getElementById('registration_id').value = '';
                document.getElementById('admin_note').value = '';
            }
        } else {
            showResult(data, 'error', method === 'qr_scan' ? 'qr' : 'form');
        }
    })
    .catch(error => {
        console.error('Checkout error:', error);
        showResult({ message: 'Network error occurred' }, 'error', method === 'qr_scan' ? 'qr' : 'form');
    });
}

// Quick Checkout Functions
function quickCheckout(registrationId, userName) {
    quickCheckoutData = { registrationId, userName };
    
    document.getElementById('modal-visitor-info').innerHTML = `
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-user text-blue-600"></i>
            </div>
            <div>
                <p class="font-medium text-gray-900">${userName}</p>
                <p class="text-sm text-gray-600">Registration ID: ${registrationId}</p>
            </div>
        </div>
    `;
    
    document.getElementById('quick-checkout-modal').classList.remove('hidden');
}

function confirmQuickCheckout() {
    if (!quickCheckoutData) return;
    
    const adminNote = document.getElementById('modal-admin-note').value.trim();
    
    processCheckout(quickCheckoutData.registrationId, adminNote, 'quick_action');
    closeQuickCheckoutModal();
}

function closeQuickCheckoutModal() {
    document.getElementById('quick-checkout-modal').classList.add('hidden');
    document.getElementById('modal-admin-note').value = '';
    quickCheckoutData = null;
}

// UI Update Functions
function updateScannerStatus(status, color) {
    const statusElement = document.getElementById('scanner-status');
    statusElement.textContent = status;
    statusElement.className = `px-2 py-1 text-xs rounded-full bg-${color}-100 text-${color}-800`;
}

function showQRResult(message, type) {
    const resultElement = document.getElementById('qr-result');
    const colorClass = type === 'success' ? 'bg-green-100 text-green-800' : 
                      type === 'error' ? 'bg-red-100 text-red-800' : 
                      'bg-blue-100 text-blue-800';
    
    resultElement.className = `p-4 rounded-lg ${colorClass}`;
    resultElement.textContent = message;
    resultElement.classList.remove('hidden');
    
    setTimeout(() => {
        resultElement.classList.add('hidden');
    }, 3000);
}

function showResult(data, type, target) {
    const resultElement = document.getElementById(target === 'qr' ? 'qr-result' : 'checkout-result');
    const colorClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 
                      'bg-red-100 border-red-400 text-red-700';
    
    resultElement.className = `p-4 rounded-lg border ${colorClass}`;
    
    if (type === 'success') {
        resultElement.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-3 text-xl"></i>
                <div>
                    <p class="font-bold">Check-out Successful!</p>
                    <p class="text-sm">${data.user} - ${data.event}</p>
                    <p class="text-xs">${data.timestamp}</p>
                    ${data.duration ? `<p class="text-xs font-medium">Visit Duration: ${data.duration}</p>` : ''}
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

function updateActiveVisitors() {
    // Remove checked out visitor from the list
    if (quickCheckoutData) {
        const visitorElement = document.querySelector(`[data-registration-id="${quickCheckoutData.registrationId}"]`);
        if (visitorElement) {
            visitorElement.remove();
        }
        
        // Update total count
        const totalElement = document.getElementById('total-active');
        const currentCount = parseInt(totalElement.textContent) - 1;
        totalElement.textContent = Math.max(0, currentCount);
    }
}

function addToRecentCheckouts(checkoutData) {
    const container = document.getElementById('recent-checkouts');
    const checkoutHtml = `
        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-sign-out-alt text-blue-600"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-900">${checkoutData.user}</p>
                    <p class="text-sm text-gray-600">${checkoutData.event}</p>
                    ${checkoutData.duration ? `<p class="text-xs text-blue-600"><i class="fas fa-stopwatch mr-1"></i>Duration: ${checkoutData.duration}</p>` : ''}
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm font-medium text-gray-900">Just now</div>
                <div class="text-xs text-gray-500">by You</div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('afterbegin', checkoutHtml);
    
    // Remove oldest if more than 5
    const checkouts = container.children;
    if (checkouts.length > 5) {
        checkouts[checkouts.length - 1].remove();
    }
}

function refreshActiveVisitors() {
    location.reload();
}

// Analytics Functions
function loadAnalytics() {
    fetch('{{ route("checkin.stats") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('avg-duration').textContent = Math.round(data.average_duration || 0) + 'm';
            document.getElementById('total-completed').textContent = data.total_checkouts || 0;
            // Additional analytics can be added here
        })
        .catch(error => console.error('Analytics loading error:', error));
}

function initializeDurationChart() {
    const ctx = document.getElementById('duration-chart').getContext('2d');
    
    // This would be populated with real data from the server
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['0-30m', '31-60m', '1-2h', '2-4h', '4h+'],
            datasets: [{
                label: 'Number of Visits',
                data: [0, 0, 0, 0, 0], // Will be updated with real data
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

// Search Functionality
function setupEventListeners() {
    document.getElementById('start-scanner').addEventListener('click', startScanning);
    document.getElementById('stop-scanner').addEventListener('click', stopScanning);
    
    // Manual form submission
    document.getElementById('manual-checkout-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const registrationId = document.getElementById('registration_id').value.trim();
        const adminNote = document.getElementById('admin_note').value.trim();
        
        if (!registrationId) {
            showResult({ message: 'Please enter a registration ID' }, 'error', 'form');
            return;
        }
        
        processCheckout(registrationId, adminNote, 'manual');
    });
    
    // Visitor search
    document.getElementById('visitor-search').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const visitorItems = document.querySelectorAll('.visitor-item');
        
        visitorItems.forEach(item => {
            const userName = item.dataset.userName;
            if (userName.includes(searchTerm)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    // Auto-start scanner
    setTimeout(startScanning, 1000);
}

// Real-time Updates
function startRealTimeUpdates() {
    setInterval(loadAnalytics, 60000); // Update every minute
}

// Modal close on escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeQuickCheckoutModal();
    }
});
</script>
@endpush

@push('styles')
<style>
.visitor-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

#qr-reader video {
    border-radius: 8px;
}

.modal-backdrop {
    backdrop-filter: blur(4px);
}
</style>
@endpush