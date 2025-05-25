@extends('layouts.app')

@section('title', 'Create Registration')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Create New Registration</h1>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('registrations.store') }}" method="POST" class="bg-white shadow rounded-lg p-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Event Selection -->
            <div>
                <label for="event_id" class="block text-sm font-medium text-gray-700">Event</label>
                <select name="event_id" id="event_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Select an Event</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}">{{ $event->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Ticket Type Selection -->
            <div id="ticket-types-container">
                <label for="ticket_type_id" class="block text-sm font-medium text-gray-700">Ticket Type</label>
                <select name="ticket_type_id" id="ticket_type_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Select a Ticket</option>
                </select>
            </div>
        </div>

        <!-- Dynamic Fields -->
        <div id="dynamic-fields" class="mt-6 space-y-4"></div>

        <div class="mt-6">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-save mr-2"></i> Create Registration
            </button>
        </div>
    </form>
</div>

@section('scripts')
<script>
document.getElementById('event_id').addEventListener('change', function() {
    const eventId = this.value;
    if (!eventId) {
        document.getElementById('ticket-types-container').innerHTML = `
            <label for="ticket_type_id" class="block text-sm font-medium text-gray-700">Ticket Type</label>
            <select name="ticket_type_id" id="ticket_type_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">Select a Ticket</option>
            </select>`;
        document.getElementById('dynamic-fields').innerHTML = '';
        return;
    }

    // Fetch ticket types
    fetch(`{{ url('registrations/tickets') }}/${eventId}`)
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('ticket_type_id');
            select.innerHTML = '<option value="">Select a Ticket</option>';
            data.forEach(ticket => {
                select.innerHTML += `<option value="${ticket.id}">${ticket.name} - $${ticket.price}</option>`;
            });
        });

    // Fetch registration fields
    fetch(`{{ url('registrations/fields') }}/${eventId}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('dynamic-fields');
            container.innerHTML = '';
            data.forEach(field => {
                const fieldKey = field.field_name.toLowerCase().replace(/\s+/g, '_');
                let input = '';
                if (field.field_type === 'dropdown') {
                    input = `<select name="${fieldKey}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" ${field.is_required ? 'required' : ''}>
                        <option value="">Select ${field.field_name}</option>
                        ${field.options.split(',').map(option => `<option value="${option.trim()}">${option.trim()}</option>`).join('')}
                    </select>`;
                } else if (field.field_type === 'checkbox') {
                    input = `<input type="checkbox" name="${fieldKey}" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" ${field.is_required ? 'required' : ''}>`;
                } else {
                    input = `<input type="${field.field_type}" name="${fieldKey}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" ${field.is_required ? 'required' : ''}>`;
                }
                container.innerHTML += `
                    <div>
                        <label for="${fieldKey}" class="block text-sm font-medium text-gray-700">${field.field_name}</label>
                        ${input}
                    </div>`;
            });
        });
});
</script>
@endsection