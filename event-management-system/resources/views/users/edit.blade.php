@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Edit User</h2>
                <p class="text-gray-600">Update user information, role, and permissions</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('users.show', $user) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-eye mr-2"></i>
                    View Profile
                </a>
                <a href="{{ route('users.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Users
                </a>
            </div>
        </div>
    </div>

    <!-- User Avatar & Basic Info -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center space-x-6">
            <div class="h-20 w-20 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center">
                <span class="text-white font-bold text-2xl">{{ $user->initials }}</span>
            </div>
            <div>
                <h3 class="text-xl font-bold text-gray-900">{{ $user->name }}</h3>
                <p class="text-gray-600">{{ $user->email }}</p>
                <div class="flex items-center space-x-3 mt-2">
                    <span class="px-3 py-1 text-sm font-medium rounded-full {{ $user->getRoleColorClass() }}">
                        {{ $user->getRoleDisplayName() }}
                    </span>
                    <span class="px-3 py-1 text-sm font-medium rounded-full {{ $user->getStatusBadgeClass() }}">
                        {{ $user->getStatusDisplayText() }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Form -->
    <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6">
        @csrf
        @method('PUT')
        
        <!-- Basic Information -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-user mr-2 text-blue-600"></i>
                Basic Information
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                           placeholder="Enter full name">
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                           placeholder="Enter email address">
                    @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $user->first_name) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('first_name') border-red-500 @enderror"
                           placeholder="Enter first name">
                    @error('first_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}"
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
                        <option value="{{ $key }}" {{ old('role', $user->role) == $key ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                        @endforeach
                    </select>
                    @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Account Status</label>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" 
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-3"
                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                        <label for="is_active" class="text-sm font-medium text-gray-700">
                            Active Account
                        </label>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Inactive accounts cannot log in to the system</p>
                </div>

                <div class="md:col-span-2">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-yellow-800 mb-2">Change Password</h4>
                        <p class="text-sm text-yellow-700 mb-4">Leave blank to keep current password</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                <div class="relative">
                                    <input type="password" name="password" id="password"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror pr-10"
                                           placeholder="Enter new password" minlength="8">
                                    <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                        <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="password-toggle"></i>
                                    </button>
                                </div>
                                @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                <div class="relative">
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-10"
                                           placeholder="Confirm new password" minlength="8">
                                    <button type="button" onclick="togglePassword('password_confirmation')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                        <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="password_confirmation-toggle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" onclick="generatePassword()" class="mt-3 text-sm text-blue-600 hover:text-blue-800 flex items-center">
                            <i class="fas fa-random mr-1"></i>
                            Generate Strong Password
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
                    <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number', $user->phone_number) }}"
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
                        <option value="{{ $name }}" {{ old('country', $user->country) == $name ? 'selected' : '' }}>
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
                    <input type="text" name="title" id="title" value="{{ old('title', $user->title) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('title') border-red-500 @enderror"
                           placeholder="Enter job title">
                    @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="company" class="block text-sm font-medium text-gray-700 mb-2">Company</label>
                    <input type="text" name="company" id="company" value="{{ old('company', $user->company) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('company') border-red-500 @enderror"
                           placeholder="Enter company name">
                    @error('company')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Current Event Assignments -->
        @if($user->canBeAssignedToEvents() && $user->assignedEvents->count() > 0)
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-calendar-check mr-2 text-orange-600"></i>
                Current Event Assignments
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($user->assignedEvents as $event)
                <div class="border border-gray-200 rounded-lg p-4 flex items-center justify-between">
                    <div>
                        <div class="font-medium text-gray-900">{{ $event->name }}</div>
                        <div class="text-sm text-gray-500">{{ $event->start_date->format('M d, Y') }}</div>
                        @if($event->venue)
                        <div class="text-xs text-gray-400">{{ $event->venue->name }}</div>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('users.remove-event', [$user, $event]) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="text-red-600 hover:text-red-800 p-1"
                                onclick="return confirm('Remove this event assignment?')"
                                title="Remove Assignment">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Event Assignment (for Event Managers and Ushers) -->
        <div id="event-assignment-section" class="{{ $user->canBeAssignedToEvents() ? '' : 'hidden' }} bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-calendar-plus mr-2 text-orange-600"></i>
                Assign New Events
            </h3>
            
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-4">Select additional events this user will have access to manage or assist with.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-4">
                    @php
                        $assignedEventIds = $user->assignedEvents->pluck('id')->toArray();
                        $availableEvents = $events->whereNotIn('id', $assignedEventIds);
                    @endphp
                    
                    @forelse($availableEvents as $event)
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
                    @empty
                    <div class="col-span-full text-center py-8 text-gray-500">
                        <i class="fas fa-calendar-check text-4xl mb-2"></i>
                        <p>All available events are already assigned to this user</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Additional Settings -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-cog mr-2 text-gray-600"></i>
                Additional Information
            </h3>
            
            <div class="space-y-4">
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" id="notes" rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('notes') border-red-500 @enderror"
                              placeholder="Add any additional notes about this user...">{{ old('notes', $user->notes) }}</textarea>
                    @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- User Statistics (Read-only) -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Account Statistics</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <div class="text-gray-500">Member Since</div>
                            <div class="font-medium">{{ $user->created_at->format('M d, Y') }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Last Updated</div>
                            <div class="font-medium">{{ $user->updated_at->format('M d, Y') }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Total Registrations</div>
                            <div class="font-medium">{{ $user->registrations_count ?? 0 }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Assigned Events</div>
                            <div class="font-medium">{{ $user->assigned_events_count ?? 0 }}</div>
                        </div>
                    </div>
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
                        <i class="fas fa-save mr-2"></i>
                        Update User
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
    // Initialize event assignment visibility
    toggleEventAssignment();
});

function toggleEventAssignment() {
    const roleSelect = document.getElementById('role');
    const eventSection = document.getElementById('event-assignment-section');
    
    if (roleSelect.value === 'EVENT_MANAGER' || roleSelect.value === 'USHER') {
        eventSection.classList.remove('hidden');
    } else {
        eventSection.classList.add('hidden');
        // Uncheck all new event assignments
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
    
    if (password && password !== confirmPassword) {
        e.preventDefault();
        showNotification('Passwords do not match', 'error');
        return;
    }
    
    if (password && password.length < 8) {
        e.preventDefault();
        showNotification('Password must be at least 8 characters long', 'error');
        return;
    }
});
</script>
@endsection