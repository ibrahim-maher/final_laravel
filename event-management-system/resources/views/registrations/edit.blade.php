@extends('layouts.app')

@section('title', 'Edit Registration')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Edit Registration for {{ $registration->event->name }}</h1>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('registrations.update', $registration->id) }}" method="POST" class="bg-white shadow rounded-lg p-6">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Registration Fields -->
            @foreach($registration->event->registration_fields as $field)
                @php
                    $fieldKey = Str::slug($field->field_name, '_');
                    $value = $registration->registration_data[$field->field_name] ?? '';
                @endphp
                <div>
                    <label for="{{ $fieldKey }}" class="block text-sm font-medium text-gray-700">{{ $field->field_name }}</label>
                    @if($field->field_type === 'dropdown')
                        <select name="{{ $fieldKey }}" id="{{ $fieldKey }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" {{ $field->is_required ? 'required' : '' }}>
                            <option value="">Select {{ $field->field_name }}</option>
                            @foreach(explode(',', $field->options) as $option)
                                <option value="{{ $option }}" {{ $value == $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                    @elseif($field->field_type === 'checkbox')
                        <input type="checkbox" name="{{ $fieldKey }}" id="{{ $fieldKey }}" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" {{ $value ? 'checked' : '' }} {{ $field->is_required ? 'required' : '' }}>
                    @else
                        <input type="{{ $field->field_type }}" name="{{ $fieldKey }}" id="{{ $fieldKey }}" value="{{ $value }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" {{ $field->is_required ? 'required' : '' }}>
                    @endif
                </div>
            @endforeach

            <!-- Ticket Type Selection -->
            <div>
                <label for="ticket_type_id" class="block text-sm font-medium text-gray-700">Ticket Type</label>
                <select name="ticket_type_id" id="ticket_type_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Select a Ticket</option>
                    @foreach($registration->event->ticket_types as $ticket)
                        <option value="{{ $ticket->id }}" {{ $registration->ticket_type_id == $ticket->id ? 'selected' : '' }}>{{ $ticket->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-save mr-2"></i> Save Changes
            </button>
            <a href="{{ route('registrations.show', $registration->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 ml-4">
                <i class="fas fa-times mr-2"></i> Cancel
            </a>
        </div>
    </form>
</div>
@endsection