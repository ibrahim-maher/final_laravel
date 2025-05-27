<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Multiple Badges</title>
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
            size: A4;
            margin: 1cm;
        }
        
        /* Container for multiple badges */
        .badges-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1cm;
            padding: 0.5cm;
            justify-content: flex-start;
            align-content: flex-start;
        }
        
        /* Individual Badge Container */
        .badge-container {
            position: relative;
            background-color: white;
            overflow: hidden;
            page-break-inside: avoid;
            break-inside: avoid;
            flex-shrink: 0;
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
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .badges-container {
                gap: 0.5cm;
                padding: 0;
            }
            
            .badge-container {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                margin-bottom: 0.5cm;
                box-shadow: none;
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
            
            /* Force page breaks when needed */
            .page-break {
                page-break-before: always;
                break-before: page;
            }
        }
        
        /* Screen Preview Styles */
        @media screen {
            body {
                background: #f0f0f0;
                padding: 20px;
            }
            
            .badges-container {
                max-width: 1200px;
                margin: 0 auto;
            }
            
            .badge-container {
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                border: 1px solid #ddd;
                border-radius: 4px;
                transition: transform 0.2s ease;
            }
            
            .badge-container:hover {
                transform: scale(1.02);
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
        
        .print-info {
            margin-bottom: 10px;
            font-size: 13px;
            color: #666;
            text-align: center;
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
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .badges-container {
                justify-content: center;
            }
            
            .print-controls {
                position: relative;
                top: auto;
                right: auto;
                margin-bottom: 20px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Print Controls (Hidden during print) -->
    <div class="print-controls no-print">
        @php
            $totalBadges = collect($badgeGroups)->sum(function($group) {
                return count($group['registrations']);
            });
        @endphp
        <div class="print-info">
            <strong>{{ $totalBadges }}</strong> badges ready to print
        </div>
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Print All Badges
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            ✖️ Close
        </button>
    </div>

    <!-- Badges Container -->
    <div class="badges-container">
        @foreach($badgeGroups as $groupIndex => $group)
            @php
                $badgeTemplate = $group['template'];
                $registrations = $group['registrations'];
                $badgeDataList = $group['badge_data'];
            @endphp
            
            @foreach($registrations as $regIndex => $registration)
                @php
                    $badgeData = $badgeDataList[$regIndex];
                    
                    $badgeStyle = sprintf(
                        'width: %scm; height: %scm; background-color: %s;',
                        $badgeTemplate->width,
                        $badgeTemplate->height,
                        $badgeTemplate->background_color ?? '#ffffff'
                    );
                    
                    if ($badgeTemplate->background_image) {
                        $badgeStyle .= sprintf(
                            ' background-image: url(%s); background-size: cover; background-position: center; background-repeat: no-repeat;',
                            Storage::url($badgeTemplate->background_image)
                        );
                    }
                    
                    if (($badgeTemplate->border_width ?? 0) > 0) {
                        $badgeStyle .= sprintf(
                            ' border: %spx solid %s;',
                            $badgeTemplate->border_width,
                            $badgeTemplate->border_color ?? '#000000'
                        );
                    }
                @endphp
                
                {{-- Add page break every 6 badges for better printing --}}
                @if($regIndex > 0 && $regIndex % 6 === 0)
                    <div class="page-break"></div>
                @endif
                
                <div class="badge-container" style="{{ $badgeStyle }}">
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
                                         alt="QR Code for {{ $registration->user->name }}" 
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
                                                font-size: 8px; 
                                                color: #999;">
                                        NO QR
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
            @endforeach
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
            }, 1000);
        });
        
        // Handle after print
        window.addEventListener('afterprint', function() {
            console.log('Print dialog completed');
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
        function printBadges() {
            window.print();
        }
        
        // Close function
        function closeBadges() {
            window.close();
        }
        
        // Progress indicator for large batches
        if (document.querySelectorAll('.badge-container').length > 20) {
            console.log('Large batch detected, optimizing for print...');
            
            // Add loading indicator
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'no-print';
            loadingDiv.style.cssText = `
                position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
                background: rgba(255,255,255,0.9); padding: 20px; border-radius: 10px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.2); z-index: 9999;
                text-align: center; font-size: 16px;
            `;
            loadingDiv.innerHTML = `
                <div style="margin-bottom: 15px;">📋 Preparing badges for print...</div>
                <div style="font-size: 14px; color: #666;">This may take a moment for large batches</div>
            `;
            document.body.appendChild(loadingDiv);
            
            // Remove loading after delay
            setTimeout(() => {
                if (loadingDiv.parentNode) {
                    loadingDiv.parentNode.removeChild(loadingDiv);
                }
            }, 2000);
        }
    </script>
</body>
</html>