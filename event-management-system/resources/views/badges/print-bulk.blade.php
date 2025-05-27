{{-- resources/views/badges/print-bulk.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Print Badges</title>
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
            margin: 0.8cm;
        }
        
        /* Container for all badge groups */
        .bulk-print-container {
            width: 100%;
            min-height: 100vh;
        }
        
        /* Template group container */
        .template-group {
            margin-bottom: 2cm;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        
        .template-group:not(:last-child) {
            page-break-after: always;
            break-after: page;
        }
        
        /* Group header */
        .group-header {
            margin-bottom: 1cm;
            padding: 0.5cm;
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }
        
        .group-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.2cm;
        }
        
        .group-info {
            font-size: 12px;
            color: #666;
        }
        
        /* Badges grid for each template group */
        .badges-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(8cm, 1fr));
            gap: 0.8cm;
            justify-content: start;
            align-content: start;
        }
        
        /* Individual Badge Container */
        .badge-container {
            position: relative;
            background-color: white;
            overflow: hidden;
            page-break-inside: avoid;
            break-inside: avoid;
            flex-shrink: 0;
            margin-bottom: 0.5cm;
        }
        
        /* Badge content positioning */
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
            font-weight: bold;
        }
        
        /* Badge numbering for bulk printing */
        .badge-number {
            position: absolute;
            top: -0.3cm;
            right: -0.3cm;
            background: #007bff;
            color: white;
            border-radius: 50%;
            width: 0.6cm;
            height: 0.6cm;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8pt;
            font-weight: bold;
            z-index: 10;
        }
        
        /* Print Specific Styles */
        @media print {
            html, body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .template-group {
                margin-bottom: 1cm;
            }
            
            .group-header {
                background: #f8f9fa !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .badges-grid {
                gap: 0.5cm;
            }
            
            .badge-container {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                box-shadow: none;
                margin-bottom: 0.3cm;
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
            
            .badge-number {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            /* Hide print controls */
            .no-print {
                display: none !important;
            }
            
            /* Force page breaks between template groups */
            .template-group:not(:last-child) {
                page-break-after: always;
                break-after: page;
            }
            
            /* Prevent orphaned badges */
            .badge-container {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
        
        /* Screen Preview Styles */
        @media screen {
            body {
                background: #f0f0f0;
                padding: 20px;
            }
            
            .bulk-print-container {
                max-width: 1400px;
                margin: 0 auto;
            }
            
            .template-group {
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                margin-bottom: 30px;
            }
            
            .badge-container {
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                border: 1px solid #ddd;
                border-radius: 4px;
                transition: transform 0.2s ease;
                position: relative;
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
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
            z-index: 1000;
            border: 1px solid #ddd;
            min-width: 250px;
        }
        
        .print-info {
            margin-bottom: 15px;
            text-align: center;
        }
        
        .total-badges {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .groups-info {
            font-size: 12px;
            color: #666;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            font-family: Arial, sans-serif;
            width: calc(50% - 10px);
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #545b62);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .badges-grid {
                grid-template-columns: repeat(auto-fit, minmax(7cm, 1fr));
                gap: 0.6cm;
            }
        }
        
        @media (max-width: 768px) {
            .badges-grid {
                grid-template-columns: repeat(auto-fit, minmax(6cm, 1fr));
                gap: 0.4cm;
            }
            
            .print-controls {
                position: relative;
                top: auto;
                right: auto;
                margin-bottom: 20px;
                width: 100%;
            }
            
            .btn {
                width: 100%;
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <!-- Print Controls -->
    <div class="print-controls no-print">
        @php
            $totalBadges = collect($badgeGroups)->sum(function($group) {
                return count($group['registrations']);
            });
            $totalGroups = count($badgeGroups);
        @endphp
        
        <div class="print-info">
            <div class="total-badges">{{ $totalBadges }} Badges Ready</div>
            <div class="groups-info">{{ $totalGroups }} template group{{ $totalGroups > 1 ? 's' : '' }}</div>
        </div>
        
        <button onclick="printAllBadges()" class="btn btn-primary">
            🖨️ Print All
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            ✖️ Close
        </button>
    </div>

    <!-- Bulk Print Container -->
    <div class="bulk-print-container">
        @foreach($badgeGroups as $groupIndex => $group)
            @php
                $badgeTemplate = $group['template'];
                $registrations = $group['registrations'];
                $badgeDataList = $group['badge_data'];
                $badgeCount = count($registrations);
            @endphp
            
            <div class="template-group" data-group-index="{{ $groupIndex }}">
                <!-- Group Header -->
                <div class="group-header no-print">
                    <div class="group-title">
                        Template: {{ $badgeTemplate->name ?? 'Badge Template #' . $badgeTemplate->id }}
                    </div>
                    <div class="group-info">
                        {{ $badgeCount }} badge{{ $badgeCount > 1 ? 's' : '' }} • 
                        Size: {{ $badgeTemplate->width }}cm × {{ $badgeTemplate->height }}cm •
                        Event: {{ $registrations->first()->event->name ?? 'Multiple Events' }}
                    </div>
                </div>
                
                <!-- Badges Grid -->
                <div class="badges-grid">
                    @foreach($registrations as $regIndex => $registration)
                        @php
                            $badgeData = $badgeDataList[$regIndex];
                            $globalBadgeNumber = collect($badgeGroups)->take($groupIndex)->sum(function($g) { 
                                return count($g['registrations']); 
                            }) + $regIndex + 1;
                            
                            // Badge styling
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
                        
                        <div class="badge-container" 
                             style="{{ $badgeStyle }}"
                             data-registration-id="{{ $registration->id }}"
                             data-badge-number="{{ $globalBadgeNumber }}">
                            
                            <!-- Badge number for identification -->
                            <div class="badge-number no-print">{{ $globalBadgeNumber }}</div>
                            
                            @foreach($badgeTemplate->contents as $content)
                                @php
                                    $fieldData = $badgeData[$content->field_name] ?? null;
                                    if (!$fieldData) continue;
                                    
                                    $positionStyle = sprintf(
                                        'left: %scm; top: %scm;', 
                                        $content->position_x, 
                                        $content->position_y
                                    );
                                @endphp
                                
                                @if($fieldData['type'] === 'qr_code')
                                    {{-- QR Code Field --}}
                                    <div class="badge-field qr-field" 
                                         style="{{ $positionStyle }} width: {{ $fieldData['width'] }}cm; height: {{ $fieldData['height'] }}cm;">
                                        @if($fieldData['value'])
                                            <img src="{{ $fieldData['value'] }}" 
                                                 alt="QR Code for {{ $registration->user->name }}" 
                                                 class="qr-image"
                                                 style="width: {{ $fieldData['width'] }}cm; height: {{ $fieldData['height'] }}cm;">
                                            <div class="qr-id">{{ $fieldData['registration_id'] }}</div>
                                        @else
                                            <div style="width: {{ $fieldData['width'] }}cm; 
                                                        height: {{ $fieldData['height'] }}cm; 
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
                                         style="{{ $positionStyle }} 
                                                font-size: {{ $fieldData['font_size'] }}pt;
                                                color: {{ $fieldData['font_color'] }};
                                                font-family: {{ $fieldData['font_family'] }};
                                                {{ $fieldData['is_bold'] ? 'font-weight: bold;' : '' }}
                                                {{ $fieldData['is_italic'] ? 'font-style: italic;' : '' }}">
                                        {{ $fieldData['value'] }}
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <script>
        // Print all badges function
        function printAllBadges() {
            window.print();
        }
        
        // Auto-print functionality
        window.addEventListener('load', function() {
            console.log('Bulk badge print page loaded');
            
            setTimeout(() => {
                if (window.opener && window.opener !== window) {
                    printAllBadges();
                }
            }, 1000);
        });
        
        // Handle after print
        window.addEventListener('afterprint', function() {
            console.log('Print dialog completed');
            
            if (window.opener && window.opener !== window) {
                setTimeout(() => {
                    window.close();
                }, 1500);
            }
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                printAllBadges();
            }
            if (e.key === 'Escape') {
                window.close();
            }
        });
        
        // Performance monitoring
        const badgeCount = document.querySelectorAll('.badge-container').length;
        console.log(`Bulk print ready: ${badgeCount} badges`);
    </script>
</body>
</html>