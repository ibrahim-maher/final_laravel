@extends('layouts.app')

@section('title', 'Create User')
@section('page-title', 'Create New User')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Create New User</h2>
                <p class="text-gray-600">Add a new user to the system with specific role and permissions</p>
            </div>
            <a href="{{ route('users.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Users
            </a>
        </div>
    </div>

    <!-- Create User Form -->
    <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
        @csrf
        
        <!-- Basic Information -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-user mr-2 text-blue-600"></i>
                Basic Information
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                           placeholder="Enter full name">
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                           placeholder="Enter email address">
                    @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('first_name') border-red-500 @enderror"
                           placeholder="Enter first name">
                    @error('first_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('last_name') border-red-500 @enderror"
                           placeholder="Enter last name">
                    @error('last_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Account & Role Information -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-shield-alt mr-2 text-green-600"></i>
                Account & Role Information
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">User Role *</label>
                    <select name="role" id="role" required onchange="toggleEventAssignment()"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('role') border-red-500 @enderror">
                        <option value="">Select Role</option>
                        @foreach($roles as $key => $value)
                        <option value="{{ $key }}" {{ old('role') == $key ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                        @endforeach
                    </select>
                    @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    
                    <!-- Role Descriptions -->
                    <div class="mt-3 space-y-2 text-sm">
                        <div id="role-desc-ADMIN" class="hidden p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-red-800"><strong>Administrator:</strong> Full system access including user management, venue management, and all administrative functions.</p>
                        </div>
                        <div id="role-desc-EVENT_MANAGER" class="hidden p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-blue-800"><strong>Event Manager:</strong> Can create and manage events, view reports, and manage registrations for assigned events.</p>
                        </div>
                        <div id="role-desc-USHER" class="hidden p-3 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-green-800"><strong>Usher:</strong> Can check-in/check-out attendees and view basic event information for assigned events.</p>
                        </div>
                        <div id="role-desc-VISITOR" class="hidden p-3 bg-gray-50 border border-gray-200 rounded-lg">
                            <p class="text-gray-800"><strong>Visitor:</strong> Basic user who can register for events and view their registration history.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror pr-12"
                               placeholder="Enter password" minlength="8">
                        <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="password-toggle"></i>
                        </button>
                    </div>
                    @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <button type="button" onclick="generatePassword()" class="mb-2 text-sm text-blue-600 hover:text-blue-800 flex items-center">
                        <i class="fas fa-random mr-1"></i>
                        Generate Strong Password
                    </button>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password *</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-12"
                               placeholder="Confirm password" minlength="8">
                        <button type="button" onclick="togglePassword('password_confirmation')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="password_confirmation-toggle"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-address-book mr-2 text-purple-600"></i>
                Contact Information
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone_number') border-red-500 @enderror"
                           placeholder="Enter phone number">
                    @error('phone_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                    <select name="country" id="country"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('country') border-red-500 @enderror">
                        <option value="">Select Country</option>
                        @foreach($countries as $code => $name)
                        <option value="{{ $name }}" {{ old('country') == $name ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                        @endforeach
                    </select>
                    @error('country')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Job Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('title') border-red-500 @enderror"
                           placeholder="Enter job title">
                    @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="company" class="block text-sm font-medium text-gray-700 mb-2">Company</label>
                    <input type="text" name="company" id="company" value="{{ old('company') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('company') border-red-500 @enderror"
                           placeholder="Enter company name">
                    @error('company')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Event Assignment (for Event Managers and Ushers) -->
        <div id="event-assignment-section" class="hidden bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-calendar-check mr-2 text-orange-600"></i>
                Event Assignment
            </h3>
            
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-4">Select events this user will have access to manage or assist with.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-4">
                    @foreach($events as $event)
                    <label class="flex items-start p-3 hover:bg-gray-50 rounded-lg cursor-pointer border border-gray-100">
                        <input type="checkbox" name="assigned_events[]" value="{{ $event->id }}" 
                               class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-3"
                               {{ in_array($event->id, old('assigned_events', [])) ? 'checked' : '' }}>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900">{{ $event->name }}</div>
                            <div class="text-xs text-gray-500 mt-1">
                                <div>{{ $event->start_date->format('M d, Y') }} - {{ $event->end_date->format('M d, Y') }}</div>
                                @if($event->venue)
                                <div>{{ $event->venue->name }}</div>
                                @endif
                            </div>
                            <span class="inline-flex items-center px-2 py-1 text-xs rounded-full mt-2 {{ $event->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $event->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </label>
                    @endforeach
                </div>
                
                @if($events->isEmpty())
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-calendar-times text-4xl mb-2"></i>
                    <p>No active events available for assignment</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Additional Settings -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-cog mr-2 text-gray-600"></i>
                Additional Settings
            </h3>
            
            <div class="space-y-4">
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" id="notes" rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('notes') border-red-500 @enderror"
                              placeholder="Add any additional notes about this user...">{{ old('notes') }}</textarea>
                    @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="send_welcome_email" id="send_welcome_email" value="1" 
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-3"
                           {{ old('send_welcome_email', true) ? 'checked' : '' }}>
                    <label for="send_welcome_email" class="text-sm font-medium text-gray-700">
                        Send welcome email with login credentials
                    </label>
                </div>
                
                <div class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    If checked, the user will receive an email with their login credentials and getting started information.
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Fields marked with * are required
                </div>
                
                <div class="flex space-x-4">
                    <a href="{{ route('users.index') }}" 
                       class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium inline-flex items-center">
                        <i class="fas fa-user-plus mr-2"></i>
                        Create User
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show role description when role is selected
    const roleSelect = document.getElementById('role');
    const roleDescriptions = document.querySelectorAll('[id^="role-desc-"]');
    
    roleSelect.addEventListener('change', function() {
        // Hide all descriptions
        roleDescriptions.forEach(desc => desc.classList.add('hidden'));
        
        // Show selected role description
        if (this.value) {
            const desc = document.getElementById('role-desc-' + this.value);
            if (desc) {
                desc.classList.remove('hidden');
            }
        }
        
        toggleEventAssignment();
    });
    
    // Initialize on page load if role is pre-selected
    if (roleSelect.value) {
        roleSelect.dispatchEvent(new Event('change'));
    }
});

function toggleEventAssignment() {
    const roleSelect = document.getElementById('role');
    const eventSection = document.getElementById('event-assignment-section');
    
    if (roleSelect.value === 'EVENT_MANAGER' || roleSelect.value === 'USHER') {
        eventSection.classList.remove('hidden');
    } else {
        eventSection.classList.add('hidden');
        // Uncheck all event assignments
        document.querySelectorAll('input[name="assigned_events[]"]').forEach(checkbox => {
            checkbox.checked = false;
        });
    }
}

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggleIcon = document.getElementById(fieldId + '-toggle');
    
    if (field.type === 'password') {
        field.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

function generatePassword() {
    const length = 12;
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
    let password = "";
    
    for (let i = 0; i < length; i++) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
    }
    
    document.getElementById('password').value = password;
    document.getElementById('password_confirmation').value = password;
    
    // Show password temporarily
    document.getElementById('password').type = 'text';
    document.getElementById('password_confirmation').type = 'text';
    
    // Hide password after 3 seconds
    setTimeout(() => {
        document.getElementById('password').type = 'password';
        document.getElementById('password_confirmation').type = 'password';
    }, 3000);
    
    // Show notification
    showNotification('Strong password generated and filled in both fields', 'success');
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
    
    const colors = {
        success: 'bg-green-500 text-white',
        error: 'bg-red-500 text-white',
        info: 'bg-blue-500 text-white',
        warning: 'bg-yellow-500 text-black'
    };
    
    notification.className += ` ${colors[type] || colors.info}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Slide in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Slide out and remove
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('password_confirmation').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        showNotification('Passwords do not match', 'error');
        return;
    }
    
    if (password.length < 8) {
        e.preventDefault();
        showNotification('Password must be at least 8 characters long', 'error');
        return;
    }
});
</script>
@endsection