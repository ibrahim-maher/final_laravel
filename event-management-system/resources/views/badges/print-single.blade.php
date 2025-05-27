<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Badge - {{ $registration->user->name }}</title>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            width: 100%;
            height: 100%;
            font-family: Arial, sans-serif;
            background: white;
        }
        
        /* Page Setup for Print */
        @page {
            size: {{ $badgeTemplate->width }}cm {{ $badgeTemplate->height }}cm;
            margin: 0;
        }
        
        /* Badge Container */
        .badge-container {
            position: relative;
            width: {{ $badgeTemplate->width }}cm;
            height: {{ $badgeTemplate->height }}cm;
            background-color: {{ $badgeTemplate->background_color ?? '#ffffff' }};
            @if($badgeTemplate->background_image)
            background-image: url('{{ Storage::url($badgeTemplate->background_image) }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            @endif
            @if($badgeTemplate->border_width ?? 0 > 0)
            border: {{ $badgeTemplate->border_width }}px solid {{ $badgeTemplate->border_color ?? '#000000' }};
            @endif
            overflow: hidden;
            page-break-after: always;
        }
        
        /* Field Positioning Base Class */
        .badge-field {
            position: absolute;
            display: block;
            line-height: 1.2;
            word-wrap: break-word;
            overflow: hidden;
        }
        
        /* Text Fields */
        .badge-field.text-field {
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        
        /* QR Code Fields */
        .badge-field.qr-field {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .qr-image {
            display: block;
            object-fit: contain;
            border: none;
        }
        
        .qr-id {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            color: #000000;
            text-align: center;
            margin-top: 2px;
            background: rgba(255, 255, 255, 0.8);
            padding: 1px 4px;
            border-radius: 2px;
        }
        
        /* Print Specific Styles */
        @media print {
            html, body {
                width: {{ $badgeTemplate->width }}cm;
                height: {{ $badgeTemplate->height }}cm;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .badge-container {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .badge-field {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .qr-image {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
                image-rendering: -webkit-optimize-contrast;
                image-rendering: crisp-edges;
            }
            
            /* Hide print controls */
            .no-print {
                display: none !important;
            }
        }
        
        /* Screen Preview Styles */
        @media screen {
            body {
                background: #f0f0f0;
                padding: 20px;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }
            
            .badge-container {
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                border-radius: 4px;
                transform: scale(1.5);
                transform-origin: center;
            }
        }
        
        /* Print Controls */
        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 1000;
            border: 1px solid #ddd;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 5px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            font-family: Arial, sans-serif;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.8;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <!-- Print Controls (Hidden during print) -->
    <div class="print-controls no-print">
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Print Badge
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            ✖️ Close
        </button>
    </div>

    <!-- Badge Container -->
    <div class="badge-container">
        @foreach($badgeTemplate->contents as $content)
            @php
                $fieldData = $badgeData[$content->field_name] ?? null;
                if (!$fieldData) continue;
                
                $positionStyle = sprintf(
                    'left: %scm; top: %scm;', 
                    $content->position_x, 
                    $content->position_y
                );
                
                if ($fieldData['type'] === 'qr_code') {
                    $sizeStyle = sprintf(
                        'width: %scm; height: %scm;', 
                        $content->image_width ?? 3, 
                        $content->image_height ?? 3
                    );
                } else {
                    $fontStyle = sprintf(
                        'font-size: %spt; color: %s; font-family: %s; %s %s',
                        $content->font_size,
                        $content->font_color,
                        $content->font_family,
                        $content->is_bold ? 'font-weight: bold;' : '',
                        $content->is_italic ? 'font-style: italic;' : ''
                    );
                }
            @endphp
            
            @if($fieldData['type'] === 'qr_code')
                {{-- QR Code Field --}}
                <div class="badge-field qr-field" 
                     style="{{ $positionStyle }} {{ $sizeStyle }}">
                    @if($fieldData['value'])
                        <img src="{{ $fieldData['value'] }}" 
                             alt="QR Code" 
                             class="qr-image"
                             style="width: {{ $content->image_width ?? 3 }}cm; height: {{ $content->image_height ?? 3 }}cm;">
                        <div class="qr-id">{{ $fieldData['registration_id'] }}</div>
                    @else
                        <div style="width: {{ $content->image_width ?? 3 }}cm; 
                                    height: {{ $content->image_height ?? 3 }}cm; 
                                    background: #f0f0f0; 
                                    border: 2px dashed #ccc; 
                                    display: flex; 
                                    align-items: center; 
                                    justify-content: center; 
                                    font-size: 10px; 
                                    color: #999;">
                            NO QR CODE
                        </div>
                    @endif
                </div>
            @else
                {{-- Text Field --}}
                <div class="badge-field text-field" 
                     style="{{ $positionStyle }} {{ $fontStyle }}">
                    {{ $fieldData['value'] ?: $content->getFieldDisplayName() }}
                </div>
            @endif
        @endforeach
    </div>

    <script>
        // Auto-print functionality
        window.addEventListener('load', function() {
            // Small delay to ensure everything is loaded
            setTimeout(function() {
                // Auto-print if this is a popup window
                if (window.opener && window.opener !== window) {
                    window.print();
                }
            }, 500);
        });
        
        // Handle after print
        window.addEventListener('afterprint', function() {
            // Close window after printing if it's a popup
            if (window.opener && window.opener !== window) {
                setTimeout(function() {
                    window.close();
                }, 1000);
            }
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            if (e.key === 'Escape') {
                window.close();
            }
        });
        
        // Print function for manual trigger
        function printBadge() {
            window.print();
        }
        
        // Close function
        function closeBadge() {
            window.close();
        }
    </script>
</body>
</html>