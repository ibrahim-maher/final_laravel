{{-- Badge Preview Template - resources/views/badges/preview.blade.php --}}
<div class="badge-preview-wrapper" style="display: inline-block; margin: 10px;">
    <div class="badge-preview-container" 
         style="position: relative; 
                width: {{ $badgeTemplate->width * 37.795 }}px; 
                height: {{ $badgeTemplate->height * 37.795 }}px; 
                background-color: {{ $badgeTemplate->background_color ?? '#ffffff' }};
                @if($badgeTemplate->background_image)
                background-image: url('{{ Storage::url($badgeTemplate->background_image) }}');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                @endif
                @if(($badgeTemplate->border_width ?? 0) > 0)
                border: {{ $badgeTemplate->border_width * 2 }}px solid {{ $badgeTemplate->border_color ?? '#000000' }};
                @endif
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                border-radius: 6px;
                overflow: hidden;
                transform-origin: top left;">
        
        @foreach($badgeTemplate->contents as $content)
            @php
                $fieldData = $badgeData[$content->field_name] ?? null;
                if (!$fieldData) continue;
                
                // Convert cm to px for preview (1cm = 37.795px at 96dpi)
                $leftPx = $content->position_x * 37.795;
                $topPx = $content->position_y * 37.795;
            @endphp
            
            @if($fieldData['type'] === 'qr_code')
                {{-- QR Code Field Preview --}}
                <div style="position: absolute; 
                           left: {{ $leftPx }}px; 
                           top: {{ $topPx }}px; 
                           width: {{ ($content->image_width ?? 3) * 37.795 }}px; 
                           height: {{ ($content->image_height ?? 3) * 37.795 }}px;
                           display: flex; 
                           flex-direction: column;
                           align-items: center; 
                           justify-content: center;">
                    @if($fieldData['value'])
                        <img src="{{ $fieldData['value'] }}" 
                             alt="QR Code Preview"
                             style="width: {{ ($content->image_width ?? 3) * 37.795 }}px; 
                                    height: {{ ($content->image_height ?? 3) * 37.795 }}px; 
                                    object-fit: contain;
                                    border: none;">
                        <div style="font-family: Arial, sans-serif;
                                    font-size: {{ max(8, $content->font_size ?? 12) }}px;
                                    color: #000000;
                                    text-align: center;
                                    margin-top: 2px;
                                    background: rgba(255, 255, 255, 0.8);
                                    padding: 1px 4px;
                                    border-radius: 2px;">
                            {{ $fieldData['registration_id'] }}
                        </div>
                    @else
                        <div style="width: {{ ($content->image_width ?? 3) * 37.795 }}px; 
                                    height: {{ ($content->image_height ?? 3) * 37.795 }}px; 
                                    background: #f0f0f0; 
                                    border: 2px dashed #ccc; 
                                    display: flex; 
                                    align-items: center; 
                                    justify-content: center; 
                                    font-size: 10px; 
                                    color: #999;
                                    border-radius: 4px;">
                            QR CODE
                        </div>
                    @endif
                </div>
            @else
                {{-- Text Field Preview --}}
                <div style="position: absolute; 
                           left: {{ $leftPx }}px; 
                           top: {{ $topPx }}px;
                           font-size: {{ $content->font_size * 1.33 }}px;
                           color: {{ $content->font_color }};
                           font-family: {{ $content->font_family }};
                           {{ $content->is_bold ? 'font-weight: bold;' : '' }}
                           {{ $content->is_italic ? 'font-style: italic;' : '' }}
                           white-space: nowrap;
                           overflow: hidden;
                           text-overflow: ellipsis;
                           max-width: {{ ($badgeTemplate->width * 37.795) - $leftPx - 10 }}px;
                           line-height: 1.2;">
                    {{ $fieldData['value'] ?: $content->getFieldDisplayName() }}
                </div>
            @endif
        @endforeach
    </div>
    
    {{-- Badge Info --}}
    <div class="badge-info" style="text-align: center; margin-top: 8px; font-size: 11px; color: #666;">
        <div style="font-weight: bold; margin-bottom: 2px;">{{ $registration->user->name }}</div>
        <div style="margin-bottom: 1px;">{{ $registration->event->name }}</div>
        @if($registration->ticketType)
            <div style="color: #007bff;">{{ $registration->ticketType->name }}</div>
        @endif
    </div>
</div>

<style>
/* Additional hover effects for preview */
.badge-preview-wrapper:hover .badge-preview-container {
    transform: scale(1.05);
    transition: transform 0.3s ease;
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
}

/* Responsive preview */
@media (max-width: 768px) {
    .badge-preview-container {
        transform: scale(0.8);
    }
}
</style>