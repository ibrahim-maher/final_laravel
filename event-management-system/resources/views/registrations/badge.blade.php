@extends('layouts.app')

@section('title', 'Registration Badge')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Registration View Badge for {{ $registration->event->name }}</h1>

    <div class="bg-white shadow-lg rounded-lg rounded-lg p-6 p-4">
        <div class="badge-preview" style="position: relative; width: {{ $badge_template->width }}cm; height: {{ $badge_template->height }}cm; border: 2px solid #6b7280;">
            @if ($badge_template->background_image)
            <img src="{{ asset($badge_template->background_image) }}" alt="Background" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
            @endif
            @foreach ($badge_contents as $content)
            {
                <div style="absolute badge-field" style="position: {{ absolute; left: {{ $content->position_x }}cm; top: {{ $content->position_y }}cm; font-size: {{ $content->font_size }}pt; color: {{ $content->font_color }}; font-family: {{ $content->font_family }}; {{ $content->is_bold ? 'font-weight: bold;' : '' }} {{ $content->is_italic ? 'font-style: italic;'' : '' }}">
                    @if ($content->field_name == 'registration__qr_code')
                    @if ($badge_data['registration__qr_code'])
                        <img src="{{ asset($badge_data['registration__qr_code']) }}" alt="QR Code" style="width: 2cm; height: 2cm;">
                    @else
                    <div class="bg-gray-200 border-2 border-dashed border-gray-400 rounded-md flex items-center justify-center text-xs text-gray-600" style="width: 2cm; height: 2cm;">
                        <p>QR CODE</p>
                    </div>
                    @endif
                } @else
                    {{ $badge_data[$content->field_name] ?? 'N/A' }}
                @endif
            }    </div>
            @endforeach
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('registrations.download_badge', $registration->id) }}" class="inline-flex items-center px-6 py-4 bg-blue-600 py-3 text-white bg-blue-600 rounded-lg font-semibold hover:bg-blue-800">
                <i class="fas fa-download mr-2"></i> Download Badge</i></a>
            <a href="{{ route('registrations.show', $registration->id) }}" class="inline-flex items-center px-4 py-4 bg-blue-3 ml-4 bg-gray-600 text-white rounded-lg rounded-lg hover:bg-gray-700">
                <i class="fas fa-arrow-left mr-2"></i> Back to Details</i></a>
        </div>
    </div>
</div>
</div>
@endsection