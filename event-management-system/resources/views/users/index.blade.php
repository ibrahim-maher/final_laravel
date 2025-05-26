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
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
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

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-indigo-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Ushers</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['role_distribution']['USHER'] ?? 0) }}</p>
                </div>
                <div class="bg-indigo-100 p-3 rounded-full">
                    <i class="fas fa-hand-paper text-2xl text-indigo-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Countries Widget -->
    @if(isset($stats['users_by_country']) && $stats['users_by_country']->count() > 0)
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Countries</h3>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            @foreach($stats['users_by_country'] as $country => $count)
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-gray-900">{{ $count }}</div>
                <div class="text-sm text-gray-600">{{ $country }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Filters Section -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <form method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by name, email, company..." 
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
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active (last 30 days)</option>
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

            <!-- Per Page and Sorting -->
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
                
                <div class="flex items-center space-x-4 text-sm text-gray-600">
                    <div class="flex items-center space-x-2">
                        <span>Show:</span>
                        <select name="per_page" onchange="this.form.submit()" class="border border-gray-300 rounded px-2 py-1 text-sm">
                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span>Sort by:</span>
                        <select name="sort" onchange="this.form.submit()" class="border border-gray-300 rounded px-2 py-1 text-sm">
                            <option value="created_at" {{ request('sort', 'created_at') == 'created_at' ? 'selected' : '' }}>Created Date</option>
                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name</option>
                            <option value="email" {{ request('sort') == 'email' ? 'selected' : '' }}>Email</option>
                            <option value="role" {{ request('sort') == 'role' ? 'selected' : '' }}>Role</option>
                        </select>
                        <select name="direction" onchange="this.form.submit()" class="border border-gray-300 rounded px-2 py-1 text-sm">
                            <option value="desc" {{ request('direction', 'desc') == 'desc' ? 'selected' : '' }}>Desc</option>
                            <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>Asc</option>
                        </select>
                    </div>
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
                    <select name="action" id="bulk-action-select" class="border border-yellow-300 rounded px-3 py-1 text-sm" required>
                        <option value="">Select Action</option>
                        <option value="activate">Activate Users</option>
                        <option value="deactivate">Deactivate Users</option>
                        <option value="assign_events">Assign Events</option>
                        <option value="remove_events">Remove Events</option>
                        <option value="delete">Delete Users</option>
                    </select>
                    <div id="event-selection" class="hidden">
                        <select name="event_ids[]" multiple class="border border-yellow-300 rounded px-3 py-1 text-sm" size="3">
                            @foreach(\App\Models\Event::where('is_active', true)->orderBy('name')->get() as $event)
                            <option value="{{ $event->id }}">{{ $event->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-yellow-700 mt-1">Hold Ctrl/Cmd to select multiple events</p>
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
                                            {{ $user->initials ?? strtoupper(substr($user->name, 0, 1)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $user->name }}
                                        @if($user->first_name || $user->last_name)
                                        <span class="text-xs text-gray-500">({{ trim($user->first_name . ' ' . $user->last_name) }})</span>
                                        @endif
                                    </div>
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
                                    <span>{{ $user->registrations_count }} registrations</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-clock text-xs mr-2 text-gray-400"></i>
                                    <span>{{ $user->created_at->format('M d, Y') }}</span>
                                </div>
                                @if($user->email_verified_at)
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle text-xs mr-2 text-green-400"></i>
                                    <span class="text-green-600">Verified</span>
                                </div>
                                @else
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-circle text-xs mr-2 text-red-400"></i>
                                    <span class="text-red-600">Unverified</span>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($user->canBeAssignedToEvents())
                                @if($user->assignedEvents->count() > 0)
                                <div class="space-y-1">
                                    @foreach($user->assignedEvents->take(2) as $event)
                                    <div class="flex items-center justify-between">
                                        <span class="inline-flex items-center px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                            {{ $event->name }}
                                        </span>
                                        <form method="POST" action="{{ route('users.remove-event', [$user, $event]) }}" class="inline ml-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 text-xs" 
                                                    onclick="return confirm('Remove this event assignment?')" 
                                                    title="Remove Event">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
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

                                <!-- Profile link for current user -->
                                @if($user->id === auth()->id())
                                <a href="{{ route('users.profile', $user) }}" 
                                   class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-50" 
                                   title="My Profile">
                                    <i class="fas fa-user-circle"></i>
                                </a>
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
                    No users match your current filters. Try adjusting your search criteria.
                @else
                    Get started by adding your first user to the system.
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
                        @foreach(\App\Models\Event::where('is_active', true)->orderBy('name')->get() as $event)
                        <label class="flex items-center py-2 px-3 hover:bg-gray-50 rounded">
                            <input type="checkbox" name="event_ids[]" value="{{ $event->id }}" 
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-3">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $event->name }}</div>
                                @if($event->start_date)
                                <div class="text-xs text-gray-500">{{ $event->start_date->format('M d, Y') }}</div>
                                @endif
                                @if($event->venue)
                                <div class="text-xs text-gray-400">{{ $event->venue->name }}</div>
                                @endif
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

@push('styles')
<style>
/* Additional custom styles for user management */
.user-checkbox:checked {
    background-color: #3B82F6;
    border-color: #3B82F6;
}

.transition-colors {
    transition: background-color 0.2s ease, color 0.2s ease;
}

.hover-scale:hover {
    transform: scale(1.02);
    transition: transform 0.2s ease;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/user-management.js') }}"></script>
@endpush