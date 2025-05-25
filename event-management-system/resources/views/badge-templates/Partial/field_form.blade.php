{{-- Badge Field Form Partial --}}
<div class="badge-field-item bg-gray-50 rounded-lg p-4 border border-gray-200" data-field-index="{{ $index ?? 0 }}">
    <div class="flex items-center justify-between mb-4">
        <h4 class="font-medium text-gray-900">Field {{ ($index ?? 0) + 1 }}</h4>
        <button type="button" class="remove-field text-red-600 hover:text-red-800 transition-colors">
            <i class="fas fa-trash"></i>
        </button>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Field Type --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Field Type</label>
            <select name="field_name[]" class="field-name-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                <option value="">Select field...</option>
                @foreach(\App\Models\BadgeContent::FIELD_CHOICES as $value => $label)
                <option value="{{ $value }}" {{ ($content->field_name ?? '') == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
                @endforeach
            </select>
            <div class="invalid-feedback text-red-600 text-sm mt-1" style="display: none;"></div>
        </div>

        {{-- X Position --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">X Position (cm)</label>
            <input type="number" 
                   name="position_x[]" 
                   step="0.1" 
                   min="0" 
                   value="{{ $content->position_x ?? '1.0' }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                   required>
            <div class="invalid-feedback text-red-600 text-sm mt-1" style="display: none;"></div>
        </div>

        {{-- Y Position --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Y Position (cm)</label>
            <input type="number" 
                   name="position_y[]" 
                   step="0.1" 
                   min="0" 
                   value="{{ $content->position_y ?? '1.0' }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                   required>
            <div class="invalid-feedback text-red-600 text-sm mt-1" style="display: none;"></div>
        </div>

        {{-- Font Size --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Font Size (pt)</label>
            <input type="number" 
                   name="font_size[]" 
                   min="6" 
                   max="72" 
                   value="{{ $content->font_size ?? '12' }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                   required>
            <div class="invalid-feedback text-red-600 text-sm mt-1" style="display: none;"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mt-4">
        {{-- Font Color --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Font Color</label>
            <input type="color" 
                   name="font_color[]" 
                   value="{{ $content->font_color ?? '#000000' }}" 
                   class="w-full h-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        {{-- Font Family --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Font Family</label>
            <select name="font_family[]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                @foreach(\App\Models\BadgeTemplate::FONT_CHOICES as $value => $label)
                <option value="{{ $value }}" {{ ($content->font_family ?? 'Arial') == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Bold Checkbox --}}
        <div class="flex items-center pt-6">
            <input type="hidden" name="is_bold[]" value="0">
            <input type="checkbox" 
                   name="is_bold[]" 
                   value="1" 
                   {{ ($content->is_bold ?? false) ? 'checked' : '' }}
                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label class="ml-2 text-sm text-gray-700">Bold</label>
        </div>

        {{-- Italic Checkbox --}}
        <div class="flex items-center pt-6">
            <input type="hidden" name="is_italic[]" value="0">
            <input type="checkbox" 
                   name="is_italic[]" 
                   value="1" 
                   {{ ($content->is_italic ?? false) ? 'checked' : '' }}
                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label class="ml-2 text-sm text-gray-700">Italic</label>
        </div>

        {{-- QR Code Size Fields (shown only for QR code fields) --}}
        <div class="qr-size-fields" style="{{ ($content->field_name ?? '') === 'qr_code__qr_image' ? '' : 'display: none;' }}">
            <label class="block text-sm font-medium text-gray-700 mb-1">QR Size (cm)</label>
            <div class="flex space-x-2">
                <input type="number" 
                       name="image_width[]" 
                       step="0.1" 
                       min="0.5" 
                       max="10" 
                       value="{{ $content->image_width ?? '2.0' }}" 
                       placeholder="W"
                       class="w-full px-2 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                <input type="number" 
                       name="image_height[]" 
                       step="0.1" 
                       min="0.5" 
                       max="10" 
                       value="{{ $content->image_height ?? '2.0' }}" 
                       placeholder="H"
                       class="w-full px-2 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
            </div>
        </div>
    </div>

    {{-- Field Preview --}}
    <div class="mt-4 p-3 bg-white rounded border">
        <label class="block text-sm font-medium text-gray-700 mb-2">Preview</label>
        <div class="field-preview text-sm" 
             style="color: {{ $content->font_color ?? '#000000' }}; 
                    font-family: {{ $content->font_family ?? 'Arial' }}; 
                    font-size: {{ $content->font_size ?? '12' }}pt;
                    {{ ($content->is_bold ?? false) ? 'font-weight: bold;' : '' }}
                    {{ ($content->is_italic ?? false) ? 'font-style: italic;' : '' }}">
            @if(($content->field_name ?? '') === 'qr_code__qr_image')
                <div class="inline-block bg-gray-200 border-2 border-dashed border-gray-400 rounded p-2 text-xs text-center" 
                     style="width: {{ ($content->image_width ?? 2) * 10 }}px; height: {{ ($content->image_height ?? 2) * 10 }}px; line-height: {{ ($content->image_height ?? 2) * 10 - 20 }}px;">
                    QR CODE
                </div>
            @else
                {{ $content ? $content->getFieldDisplayName() : 'Sample Text' }}
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle field type changes to show/hide QR size fields
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('field-name-select')) {
            const fieldItem = e.target.closest('.badge-field-item');
            const qrSizeFields = fieldItem.querySelector('.qr-size-fields');
            const preview = fieldItem.querySelector('.field-preview');
            
            if (e.target.value === 'qr_code__qr_image') {
                qrSizeFields.style.display = 'block';
                preview.innerHTML = '<div class="inline-block bg-gray-200 border-2 border-dashed border-gray-400 rounded p-2 text-xs text-center" style="width: 20px; height: 20px; line-height: 0;">QR</div>';
            } else {
                qrSizeFields.style.display = 'none';
                const fieldChoices = @json(\App\Models\BadgeContent::FIELD_CHOICES);
                preview.textContent = fieldChoices[e.target.value] || 'Sample Text';
            }
        }
    });

    // Update preview on style changes
    document.addEventListener('input', function(e) {
        if (e.target.closest('.badge-field-item')) {
            updateFieldPreview(e.target.closest('.badge-field-item'));
        }
    });

    function updateFieldPreview(fieldItem) {
        const preview = fieldItem.querySelector('.field-preview');
        const fontColor = fieldItem.querySelector('input[name="font_color[]"]').value;
        const fontSize = fieldItem.querySelector('input[name="font_size[]"]').value;
        const fontFamily = fieldItem.querySelector('select[name="font_family[]"]').value;
        const isBold = fieldItem.querySelector('input[name="is_bold[]"]:checked');
        const isItalic = fieldItem.querySelector('input[name="is_italic[]"]:checked');

        preview.style.color = fontColor;
        preview.style.fontSize = fontSize + 'pt';
        preview.style.fontFamily = fontFamily;
        preview.style.fontWeight = isBold ? 'bold' : 'normal';
        preview.style.fontStyle = isItalic ? 'italic' : 'normal';
    }
});
</script>