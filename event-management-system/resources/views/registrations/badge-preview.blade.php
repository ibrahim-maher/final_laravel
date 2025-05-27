@extends('layouts.app')

@section('title', 'Registration Badge')

@section('content')
{{-- Badge Preview Partial - resources/views/registrations/badge-preview.blade.php --}}
<div class="badge-preview-container" style="display: inline-block; margin: 10px;">
    <div class="badge-preview" 
         style="position: relative; 
                width: {{ $badgeTemplate->width * 10 }}px; 
                height: {{ $badgeTemplate->height * 10 }}px; 
                background: {{ $badgeTemplate->background_color ?? '#ffffff' }};
                @if($badgeTemplate->background_image)
                background-image: url('{{ Storage::url($badgeTemplate->background_image) }}');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                @endif
                border: {{ ($badgeTemplate->border_width ?? 0) * 2 }}px solid {{ $badgeTemplate->border_color ?? '#000000' }};
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                transform-origin: top left;
                overflow: hidden;">
        
        @foreach($badgeTemplate->contents as $content)
            @if($content->isQrCodeField())
                {{-- QR Code Field Preview --}}
                <div style="position: absolute; 
                           left: {{ $content->position_x * 10 }}px; 
                           top: {{ $content->position_y * 10 }}px; 
                           width: {{ $content->image_width * 10 }}px; 
                           height: {{ $content->image_height * 10 }}px;
                           display: flex; 
                           align-items: center; 
                           justify-content: center;">
                    @if($registration->qrCode && $registration->qrCode->qr_image)
                        <img src="data:image/png;base64,{{ $registration->qrCode->qr_image }}" 
                             alt="QR Code"
                             style="width: {{ $content->image_width * 10 }}px; 
                                    height: {{ $content->image_height * 10 }}px; 
                                    object-fit: contain;">
                    @else
                        <div style="width: {{ $content->image_width * 10 }}px; 
                                    height: {{ $content->image_height * 10 }}px; 
                                    background: #f0f0f0; 
                                    border: 2px dashed #ccc; 
                                    display: flex; 
                                    align-items: center; 
                                    justify-content: center; 
                                    font-size: 10px; 
                                    color: #666;">
                            QR
                        </div>
                    @endif
                </div>
            @else
                {{-- Text Field Preview --}}
                <div style="position: absolute; 
                           left: {{ $content->position_x * 10 }}px; 
                           top: {{ $content->position_y * 10 }}px;
                           font-size: {{ $content->font_size }}px;
                           color: {{ $content->font_color }};
                           font-family: {{ $content->font_family }};
                           {{ $content->is_bold ? 'font-weight: bold;' : '' }}
                           {{ $content->is_italic ? 'font-style: italic;' : '' }}
                           white-space: nowrap;
                           overflow: hidden;
                           text-overflow: ellipsis;
                           max-width: {{ ($badgeTemplate->width * 10) - ($content->position_x * 10) - 10 }}px;">
                    {{ $content->getFormattedFieldValue($registration) }}
                </div>
            @endif
        @endforeach
    </div>
    
    {{-- Badge Info --}}
    <div class="badge-info text-center mt-2" style="font-size: 11px; color: #666;">
        <div><strong>{{ $registration->user->name }}</strong></div>
        <div>{{ $registration->event->name }}</div>
        @if($registration->ticketType)
            <div>{{ $registration->ticketType->name }}</div>
        @endif
    </div>
</div>
</div>
@endsection