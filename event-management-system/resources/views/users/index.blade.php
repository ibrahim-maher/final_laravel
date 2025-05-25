@extends('layouts.app')

@section('title', 'User Management')
@section('page-title', 'User Management')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">User Management</h2>
                <p class="text-gray-600">Manage system users, roles, and permissions</p>
            </div>
            <div class="mt-4 lg:mt-0 flex flex-col sm:flex-row gap-3">
                <button onclick="toggleBulkActions()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-tasks mr-2"></i>
                    Bulk Actions
                </button>
                <a href="{{ route('users.export', request()->query()) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-download mr-2"></i>
                    Export Users
                </a>
                @can('create', App\Models\User::class)
                <a href="{{ route('users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Add User
                </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_users']) }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-users text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Active Users</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['active_users']) }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-user-check text-2xl text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">New This Month</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['new_users_this_month']) }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-user-plus text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Event Managers</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['role_distribution']['EVENT_MANAGER'] ?? 0) }}</p>
                </div>
                <div class="bg-orange-100 p-3 rounded-full">
                    <i class="fas fa-user-tie text-2xl text-orange-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <form method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search users..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Role Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Roles</option>
                        @foreach($roles as $key => $value)
                        <option value="{{ $key }}" {{ request('role') == $key ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Country Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                    <select name="country" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Countries</option>
                        @foreach($countries as $country)
                        <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>
                            {{ $country }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                <div class="flex items-center space-x-4">
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg inline-flex items-center">
                        <i class="fas fa-search mr-2"></i>
                        Apply Filters
                    </button>
                    <a href="{{ route('users.index') }}" class="text-gray-600 hover:text-gray-800 px-4 py-2 border border-gray-300 rounded-lg inline-flex items-center">
                        <i class="fas fa-times mr-2"></i>
                        Clear
                    </a>
                </div>
                
                <div class="flex items-center space-x-2 text-sm text-gray-600">
                    <span>Sort by:</span>
                    <select name="sort" onchange="this.form.submit()" class="border border-gray-300 rounded px-2 py-1 text-sm">
                        <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Created Date</option>
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name</option>
                        <option value="email" {{ request('sort') == 'email' ? 'selected' : '' }}>Email</option>
                        <option value="role" {{ request('sort') == 'role' ? 'selected' : '' }}>Role</option>
                    </select>
                    <select name="direction" onchange="this.form.submit()" class="border border-gray-300 rounded px-2 py-1 text-sm">
                        <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>Desc</option>
                        <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>Asc</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- Bulk Actions Panel (Hidden by default) -->
    <div id="bulk-actions-panel" class="hidden bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
        <form id="bulk-actions-form" method="POST" action="{{ route('users.bulk-action') }}">
            @csrf
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <span class="text-sm font-medium text-yellow-800">
                        <span id="selected-count">0</span> users selected
                    </span>
                    <select name="action" id="bulk-action-select" class="border border-yellow-300 rounded px-3 py-1 text-sm">
                        <option value="">Select Action</option>
                        <option value="activate">Activate Users</option>
                        <option value="deactivate">Deactivate Users</option>
                        <option value="assign_events">Assign Events</option>
                        <option value="remove_events">Remove Events</option>
                        <option value="delete">Delete Users</option>
                    </select>
                    <div id="event-selection" class="hidden">
                        <select name="event_ids[]" multiple class="border border-yellow-300 rounded px-3 py-1 text-sm">
                            @foreach(\App\Models\Event::where('is_active', true)->get() as $event)
                            <option value="{{ $event->id }}">{{ $event->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded text-sm">
                        Apply Action
                    </button>
                    <button type="button" onclick="toggleBulkActions()" class="text-yellow-600 hover:text-yellow-800 px-2 py-1">
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        @if($users->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role & Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Info</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned Events</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12">
                                    <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center">
                                        <span class="text-white font-bold text-lg">
                                            {{ $user->initials }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    @if($user->company)
                                    <div class="text-xs text-gray-400">{{ $user->company }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="space-y-2">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $user->getRoleColorClass() }}">
                                    {{ $user->getRoleDisplayName() }}
                                </span>
                                <div>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $user->getStatusBadgeClass() }}">
                                        {{ $user->getStatusDisplayText() }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="space-y-1">
                                <div class="flex items-center">
                                    <i class="fas fa-phone text-xs mr-2 text-gray-400"></i>
                                    <span>{{ $user->phone_number ?? 'N/A' }}</span>
                                </div>
                                @if($user->country)
                                <div class="flex items-center">
                                    <i class="fas fa-globe text-xs mr-2 text-gray-400"></i>
                                    <span>{{ $user->country }}</span>
                                </div>
                                @endif
                                @if($user->title)
                                <div class="flex items-center">
                                    <i class="fas fa-briefcase text-xs mr-2 text-gray-400"></i>
                                    <span class="truncate max-w-32" title="{{ $user->title }}">{{ $user->title }}</span>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="space-y-1">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar text-xs mr-2 text-gray-400"></i>
                                    <span>{{ $user->registrations_count }} events</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-clock text-xs mr-2 text-gray-400"></i>
                                    <span>{{ $user->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($user->canBeAssignedToEvents())
                                @if($user->assignedEvents->count() > 0)
                                <div class="space-y-1">
                                    @foreach($user->assignedEvents->take(2) as $event)
                                    <span class="inline-flex items-center px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                        {{ $event->name }}
                                    </span>
                                    @endforeach
                                    @if($user->assignedEvents->count() > 2)
                                    <span class="inline-flex items-center px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">
                                        +{{ $user->assignedEvents->count() - 2 }} more
                                    </span>
                                    @endif
                                </div>
                                @else
                                <span class="text-xs text-gray-400 italic">No events assigned</span>
                                @endif
                            @else
                            <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                @can('view', $user)
                                <a href="{{ route('users.show', $user) }}" 
                                   class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50" 
                                   title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                
                                @can('update', $user)
                                <a href="{{ route('users.edit', $user) }}" 
                                   class="text-yellow-600 hover:text-yellow-900 p-1 rounded hover:bg-yellow-50" 
                                   title="Edit User">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan

                                @if($user->canBeAssignedToEvents())
                                <button onclick="showAssignEventsModal({{ $user->id }}, '{{ $user->name }}')" 
                                        class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50" 
                                        title="Assign Events">
                                    <i class="fas fa-calendar-plus"></i>
                                </button>
                                @endif
                                
                                @can('delete', $user)
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user) }}" 
                                      class="inline" 
                                      onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50" 
                                            title="Delete User">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No users found</h3>
            <p class="text-gray-500 mb-6">
                @if(request()->hasAny(['search', 'role', 'status', 'country']))
                    No users match your current filters.
                @else
                    Get started by adding your first user.
                @endif
            </p>
            @can('create', App\Models\User::class)
            <a href="{{ route('users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg inline-flex items-center">
                <i class="fas fa-plus mr-2"></i>
                Add User
            </a>
            @endcan
        </div>
        @endif
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
    <div class="bg-white px-6 py-4 rounded-xl shadow-lg">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} results
            </div>
            {{ $users->links() }}
        </div>
    </div>
    @endif
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
                        @foreach(\App\Models\Event::where('is_active', true)->get() as $event)
                        <label class="flex items-center py-2 px-3 hover:bg-gray-50 rounded">
                            <input type="checkbox" name="event_ids[]" value="{{ $event->id }}" 
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-3">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $event->name }}</div>
                                <div class="text-xs text-gray-500">{{ $event->start_date->format('M d, Y') }}</div>
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
                        Assign Events
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bulk selection functionality
    const selectAllCheckbox = document.getElementById('select-all');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const selectedCountSpan = document.getElementById('selected-count');
    const bulkActionSelect = document.getElementById('bulk-action-select');
    const eventSelection = document.getElementById('event-selection');

    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        userCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectedCount();
    });

    // Individual checkbox change
    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllState();
            updateSelectedCount();
        });
    });

    // Bulk action change
    bulkActionSelect.addEventListener('change', function() {
        if (this.value === 'assign_events' || this.value === 'remove_events') {
            eventSelection.classList.remove('hidden');
        } else {
            eventSelection.classList.add('hidden');
        }
    });

    function updateSelectAllState() {
        const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
        selectAllCheckbox.checked = checkedCount === userCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < userCheckboxes.length;
    }

    function updateSelectedCount() {
        const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
        selectedCountSpan.textContent = checkedCount;
        
        // Add selected user IDs to bulk form
        const bulkForm = document.getElementById('bulk-actions-form');
        // Remove existing hidden inputs
        bulkForm.querySelectorAll('input[name="user_ids[]"]').forEach(input => input.remove());
        
        // Add new hidden inputs
        document.querySelectorAll('.user-checkbox:checked').forEach(checkbox => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'user_ids[]';
            hiddenInput.value = checkbox.value;
            bulkForm.appendChild(hiddenInput);
        });
    }

    // Bulk actions form submission
    document.getElementById('bulk-actions-form').addEventListener('submit', function(e) {
        const selectedCount = document.querySelectorAll('.user-checkbox:checked').length;
        const action = bulkActionSelect.value;
        
        if (selectedCount === 0) {
            e.preventDefault();
            alert('Please select at least one user');
            return;
        }
        
        if (!action) {
            e.preventDefault();
            alert('Please select an action');
            return;
        }
        
        if (action === 'delete') {
            if (!confirm(`Are you sure you want to delete ${selectedCount} users? This action cannot be undone.`)) {
                e.preventDefault();
                return;
            }
        }
        
        if ((action === 'assign_events' || action === 'remove_events')) {
            const selectedEvents = document.querySelectorAll('select[name="event_ids[]"] option:checked').length;
            if (selectedEvents === 0) {
                e.preventDefault();
                alert('Please select at least one event');
                return;
            }
        }
    });
});

function toggleBulkActions() {
    const panel = document.getElementById('bulk-actions-panel');
    panel.classList.toggle('hidden');
    
    if (!panel.classList.contains('hidden')) {
        // Reset selections
        document.getElementById('select-all').checked = false;
        document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selected-count').textContent = '0';
    }
}

function showAssignEventsModal(userId, userName) {
    const modal = document.getElementById('assign-events-modal');
    const form = document.getElementById('assign-events-form');
    
    form.action = `/users/${userId}/assign-events`;
    modal.querySelector('h3').textContent = `Assign Events to ${userName}`;
    
    modal.classList.remove('hidden');
}

function closeAssignEventsModal() {
    const modal = document.getElementById('assign-events-modal');
    modal.classList.add('hidden');
    
    // Reset form
    document.getElementById('assign-events-form').reset();
}

// Close modal when clicking outside
document.getElementById('assign-events-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAssignEventsModal();
    }
});
</script>
@endsection