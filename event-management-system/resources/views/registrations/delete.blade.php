@extends('layouts.app')

@section('title', 'Delete Registration')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Delete Registration</h1>

    <div class="bg-white shadow-lg rounded-lg rounded-lg p-6">
        <p class="text-gray-700 mb-4">Are you sure you want to delete the registration for <strong>{{ $registration->event->name }}</strong>?</p>
        <p class="text-red-600 mb-4">This action cannot be undone.</p>

        <form action="{{ route('registrations.destroy', $registration->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <i class="fas fa-trash mr-2"></i> Confirm Delete
            </button>
            <a href="{{ route('registrations.index') }}" class="inline-flex items-center px-4 py-2 ml-4 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                <i class="fas fa-times mr-2"></i> Cancel
            </a>
        </form>
    </div>
</div>
</div>
@endsection