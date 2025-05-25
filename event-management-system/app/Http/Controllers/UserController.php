<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use App\Models\Registration;
use App\Models\VisitorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserWelcomeEmail;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;  


class UserController extends Controller
{
        use AuthorizesRequests;

    public function index(Request $request)
    {

        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('last_name', 'like', "%{$searchTerm}%")
                  ->orWhere('company', 'like', "%{$searchTerm}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereHas('registrations', function($q) {
                    $q->where('created_at', '>=', now()->subDays(30));
                });
            } elseif ($request->status === 'inactive') {
                $query->whereDoesntHave('registrations', function($q) {
                    $q->where('created_at', '>=', now()->subDays(30));
                });
            }
        }

        // Country filter
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        // Sort
        $sortBy = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Load relationships and counts
        $users = $query->withCount(['registrations', 'assignedEvents'])
                      ->with(['assignedEvents' => function($q) {
                          $q->select('id', 'name')->limit(3);
                      }])
                      ->paginate($request->input('per_page', 15))
                      ->withQueryString();

        // Get filter options
        $roles = User::getRoles();
        $countries = User::select('country')
                        ->whereNotNull('country')
                        ->distinct()
                        ->orderBy('country')
                        ->pluck('country');

        // Get statistics
        $stats = $this->getUserStatistics();

        return view('users.index', compact('users', 'roles', 'countries', 'stats'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $roles = User::getRoles();
        $events = Event::where('is_active', true)->orderBy('name')->get();
        $countries = $this->getCountriesList();

        return view('users.create', compact('roles', 'events', 'countries'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:ADMIN,EVENT_MANAGER,USHER,VISITOR',
            'phone_number' => 'nullable|string|max:20',
            'title' => 'nullable|string|max:300',
            'country' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:300',
            'assigned_events' => 'nullable|array',
            'assigned_events.*' => 'exists:events,id',
            'send_welcome_email' => 'boolean',
            'notes' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();

        try {
            // Create user
            $validated['password'] = Hash::make($validated['password']);
            $validated['email_verified_at'] = now(); // Auto-verify admin created users
            
            $user = User::create($validated);

            // Assign events (only for EVENT_MANAGER and USHER roles)
            if (in_array($validated['role'], ['EVENT_MANAGER', 'USHER']) && $request->has('assigned_events')) {
                $user->assignedEvents()->sync($request->assigned_events);
            }

            // Send welcome email if requested
            if ($request->boolean('send_welcome_email')) {
                try {
                    Mail::to($user->email)->send(new UserWelcomeEmail($user, $request->password));
                } catch (\Exception $e) {
                    logger()->error('Failed to send welcome email: ' . $e->getMessage());
                }
            }

            DB::commit();

            return redirect()->route('users.index')
                           ->with('success', 'User created successfully' . 
                                  ($request->boolean('send_welcome_email') ? ' and welcome email sent' : ''));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                        ->withErrors(['error' => 'Failed to create user: ' . $e->getMessage()]);
        }
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load([
            'assignedEvents.venue',
            'registrations.event',
            'registrations.ticketType'
        ]);

        // Get user activity statistics
        $userStats = [
            'total_registrations' => $user->registrations->count(),
            'events_attended' => VisitorLog::whereHas('registration', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('action', 'checkin')->distinct('registration_id')->count(),
            'total_checkins' => VisitorLog::whereHas('registration', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('action', 'checkin')->count(),
            'avg_event_duration' => $this->getUserAverageEventDuration($user->id),
            'last_activity' => $user->registrations->max('created_at'),
            'assigned_events_count' => $user->assignedEvents->count()
        ];

        // Get recent activity
        $recentActivity = VisitorLog::whereHas('registration', function($q) use ($user) {
                                    $q->where('user_id', $user->id);
                                  })
                                  ->with(['registration.event'])
                                  ->orderBy('created_at', 'desc')
                                  ->limit(10)
                                  ->get();

        return view('users.show', compact('user', 'userStats', 'recentActivity'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = User::getRoles();
        $events = Event::where('is_active', true)->orderBy('name')->get();
        $countries = $this->getCountriesList();
        
        $user->load('assignedEvents');

        return view('users.edit', compact('user', 'roles', 'events', 'countries'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:ADMIN,EVENT_MANAGER,USHER,VISITOR',
            'phone_number' => 'nullable|string|max:20',
            'title' => 'nullable|string|max:300',
            'country' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:300',
            'assigned_events' => 'nullable|array',
            'assigned_events.*' => 'exists:events,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();

        try {
            // Update password if provided
            if ($request->filled('password')) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);

            // Update event assignments
            if (in_array($validated['role'], ['EVENT_MANAGER', 'USHER'])) {
                if ($request->has('assigned_events')) {
                    $user->assignedEvents()->sync($request->assigned_events);
                } else {
                    $user->assignedEvents()->detach();
                }
            } else {
                // Remove all event assignments for other roles
                $user->assignedEvents()->detach();
            }

            DB::commit();

            return redirect()->route('users.index')
                           ->with('success', 'User updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                        ->withErrors(['error' => 'Failed to update user: ' . $e->getMessage()]);
        }
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                           ->with('error', 'You cannot delete your own account');
        }

        // Check if user has registrations
        if ($user->registrations()->count() > 0) {
            return redirect()->route('users.index')
                           ->with('error', 'Cannot delete user with existing registrations. Please transfer or cancel registrations first.');
        }

        DB::beginTransaction();

        try {
            // Detach assigned events
            $user->assignedEvents()->detach();
            
            // Delete user
            $user->delete();

            DB::commit();

            return redirect()->route('users.index')
                           ->with('success', 'User deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('users.index')
                           ->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    public function bulkAction(Request $request)
    {
        $this->authorize('update', User::class);

        $request->validate([
            'action' => 'required|in:activate,deactivate,assign_events,remove_events,delete',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'event_ids' => 'nullable|array',
            'event_ids.*' => 'exists:events,id'
        ]);

        $userIds = $request->user_ids;
        $users = User::whereIn('id', $userIds)->get();
        $count = 0;

        DB::beginTransaction();

        try {
            foreach ($users as $user) {
                // Skip current user for certain actions
                if ($user->id === auth()->id() && in_array($request->action, ['deactivate', 'delete'])) {
                    continue;
                }

                switch ($request->action) {
                    case 'activate':
                        $user->update(['is_active' => true]);
                        $count++;
                        break;
                    case 'deactivate':
                        $user->update(['is_active' => false]);
                        $count++;
                        break;
                    case 'assign_events':
                        if ($request->has('event_ids') && in_array($user->role, ['EVENT_MANAGER', 'USHER'])) {
                            $user->assignedEvents()->syncWithoutDetaching($request->event_ids);
                            $count++;
                        }
                        break;
                    case 'remove_events':
                        if ($request->has('event_ids')) {
                            $user->assignedEvents()->detach($request->event_ids);
                            $count++;
                        }
                        break;
                    case 'delete':
                        if ($user->registrations()->count() === 0) {
                            $user->assignedEvents()->detach();
                            $user->delete();
                            $count++;
                        }
                        break;
                }
            }

            DB::commit();

            $actionName = ucfirst(str_replace('_', ' ', $request->action));
            return redirect()->route('users.index')
                           ->with('success', "{$actionName} applied to {$count} users successfully");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('users.index')
                           ->with('error', 'Bulk action failed: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::withCount(['registrations', 'assignedEvents']);

        // Apply same filters as index
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->get();

        $csvContent = "Name,First Name,Last Name,Email,Role,Phone,Title,Company,Country,Registrations,Assigned Events,Created At\n";
        
        foreach ($users as $user) {
            $csvContent .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s,%s,%d,%d,%s\n",
                $user->name,
                $user->first_name ?? '',
                $user->last_name ?? '',
                $user->email,
                User::getRoles()[$user->role] ?? $user->role,
                $user->phone_number ?? '',
                $user->title ?? '',
                $user->company ?? '',
                $user->country ?? '',
                $user->registrations_count,
                $user->assigned_events_count ?? 0,
                $user->created_at->format('Y-m-d H:i:s')
            );
        }

        return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="users_export_' . now()->format('Y_m_d') . '.csv"');
    }

    public function assignEvents(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate([
            'event_ids' => 'required|array|min:1',
            'event_ids.*' => 'exists:events,id'
        ]);

        if (!in_array($user->role, ['EVENT_MANAGER', 'USHER'])) {
            return back()->withErrors(['error' => 'Only Event Managers and Ushers can be assigned to events']);
        }

        $user->assignedEvents()->syncWithoutDetaching($request->event_ids);

        return back()->with('success', 'Events assigned successfully');
    }

    public function removeEvent(Request $request, User $user, Event $event)
    {
        $this->authorize('update', $user);

        $user->assignedEvents()->detach($event->id);

        return back()->with('success', 'Event assignment removed successfully');
    }

    public function profile(User $user)
    {
        if ($user->id !== auth()->id() && !auth()->user()->canManageUsers()) {
            abort(403);
        }

        return $this->show($user);
    }

    public function updateProfile(Request $request, User $user)
    {
        if ($user->id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone_number' => 'nullable|string|max:20',
            'title' => 'nullable|string|max:300',
            'country' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:300',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully');
    }

    // Private helper methods
    private function getUserStatistics()
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::whereHas('registrations', function($q) {
                $q->where('created_at', '>=', now()->subDays(30));
            })->count(),
            'role_distribution' => User::selectRaw('role, COUNT(*) as count')
                                     ->groupBy('role')
                                     ->pluck('count', 'role'),
            'new_users_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            'users_by_country' => User::selectRaw('country, COUNT(*) as count')
                                     ->whereNotNull('country')
                                     ->groupBy('country')
                                     ->orderBy('count', 'desc')
                                     ->limit(5)
                                     ->pluck('count', 'country')
        ];
    }

    private function getUserAverageEventDuration($userId)
    {
        return VisitorLog::whereHas('registration', function($q) use ($userId) {
                         $q->where('user_id', $userId);
                     })
                     ->where('action', 'checkout')
                     ->whereNotNull('duration_minutes')
                     ->avg('duration_minutes') ?? 0;
    }

    private function getCountriesList()
    {
        return [
            'US' => 'United States',
            'CA' => 'Canada',
            'UK' => 'United Kingdom',
            'AU' => 'Australia',
            'DE' => 'Germany',
            'FR' => 'France',
            'ES' => 'Spain',
            'IT' => 'Italy',
            'NL' => 'Netherlands',
            'BE' => 'Belgium',
            'CH' => 'Switzerland',
            'AT' => 'Austria',
            'SE' => 'Sweden',
            'NO' => 'Norway',
            'DK' => 'Denmark',
            'FI' => 'Finland',
            'JP' => 'Japan',
            'KR' => 'South Korea',
            'CN' => 'China',
            'IN' => 'India',
            'BR' => 'Brazil',
            'AR' => 'Argentina',
            'MX' => 'Mexico',
            'EG' => 'Egypt',
            'SA' => 'Saudi Arabia',
            'AE' => 'United Arab Emirates'
        ];
    }
}