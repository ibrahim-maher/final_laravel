<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::when(request('search'), function($query) {
                       $query->where('name', 'like', '%' . request('search') . '%')
                             ->orWhere('email', 'like', '%' . request('search') . '%');
                   })
                   ->when(request('role'), function($query) {
                       $query->where('role', request('role'));
                   })
                   ->orderBy('created_at', 'desc')
                   ->paginate(15);
        
        $roles = User::getRoles();
        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = User::getRoles();
        $events = Event::orderBy('name')->get();
        return view('users.create', compact('roles', 'events'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:ADMIN,EVENT_MANAGER,USHER,VISITOR',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:15',
            'title' => 'nullable|string|max:300',
            'country' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:300',
            'assigned_events' => 'nullable|array',
            'assigned_events.*' => 'exists:events,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        
        $user = User::create($validated);
        
        if ($request->has('assigned_events')) {
            $user->assignedEvents()->sync($request->assigned_events);
        }
        
        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    public function show(User $user)
    {
        $user->load(['assignedEvents', 'registrations.event']);
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = User::getRoles();
        $events = Event::orderBy('name')->get();
        $user->load('assignedEvents');
        return view('users.edit', compact('user', 'roles', 'events'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:ADMIN,EVENT_MANAGER,USHER,VISITOR',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:15',
            'title' => 'nullable|string|max:300',
            'country' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:300',
            'assigned_events' => 'nullable|array',
            'assigned_events.*' => 'exists:events,id',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        
        $user->update($validated);
        
        if ($request->has('assigned_events')) {
            $user->assignedEvents()->sync($request->assigned_events);
        } else {
            $user->assignedEvents()->detach();
        }
        
        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account');
        }
        
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully');
    }
}