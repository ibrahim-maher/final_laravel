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
        
        .btn-success {
            background: linear-gradient(135deg, #28a745, #1e7e34);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        /* Progress indicator */
        .print-progress {
            margin-top: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
            font-size: 12px;
            color: #666;
            display: none;
        }
        
        .progress-bar {
            width: 100%;
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 5px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #007bff, #0056b3);
            width: 0%;
            transition: width 0.3s ease;
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
        
        /* Animation for loading */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .badge-container {
            animation: fadeIn 0.3s ease forwards;
        }
        
        /* Custom scrollbar for large lists */
        .bulk-print-container::-webkit-scrollbar {
            width: 8px;
        }
        
        .bulk-print-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .bulk-print-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        .bulk-print-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
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
        
        <button onclick="downloadBadges()" class="btn btn-success" style="width: 100%; margin-top: 10px;">
            💾 Save as PDF
        </button>
        
        <div class="print-progress" id="printProgress">
            <div>Preparing badges for print...</div>
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
        </div>
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
                                         style="{{ $positionStyle }} {{ $fontStyle }}"
                                         title="{{ $fieldData['value'] }}">
                                        {{ $fieldData['value'] ?: $content->getFieldDisplayName() }}
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
        // Global variables
        let isPrinting = false;
        let printStartTime = null;
        
        // Print all badges function
        function printAllBadges() {
            if (isPrinting) return;
            
            isPrinting = true;
            printStartTime = Date.now();
            
            showPrintProgress();
            
            // Small delay to show progress
            setTimeout(() => {
                window.print();
            }, 500);
        }
        
        // Show print progress
        function showPrintProgress() {
            const progress = document.getElementById('printProgress');
            const progressFill = document.getElementById('progressFill');
            
            progress.style.display = 'block';
            
            // Animate progress bar
            let width = 0;
            const interval = setInterval(() => {
                width += 2;
                progressFill.style.width = width + '%';
                
                if (width >= 100) {
                    clearInterval(interval);
                }
            }, 20);
        }
        
        // Hide print progress
        function hidePrintProgress() {
            const progress = document.getElementById('printProgress');
            progress.style.display = 'none';
            isPrinting = false;
        }
        
        // Download badges as PDF (requires browser print to PDF)
        function downloadBadges() {
            // This will open the print dialog where user can choose "Save as PDF"
            showPrintProgress();
            setTimeout(() => {
                window.print();
            }, 500);
        }
        
        // Auto-print functionality
        window.addEventListener('load', function() {
            console.log('Bulk badge print page loaded');
            
            // Add fade-in animation to badges
            const badges = document.querySelectorAll('.badge-container');
            badges.forEach((badge, index) => {
                badge.style.animationDelay = (index * 50) + 'ms';
            });
            
            // Auto-print if this is a popup window (after small delay)
            setTimeout(() => {
                if (window.opener && window.opener !== window) {
                    printAllBadges();
                }
            }, 1000);
        });
        
        // Handle after print
        window.addEventListener('afterprint', function() {
            console.log('Print dialog completed');
            hidePrintProgress();
            
            // Show completion message
            const printTime = Date.now() - printStartTime;
            console.log(`Print process took ${printTime}ms`);
            
            // Close window after printing if it's a popup
            if (window.opener && window.opener !== window) {
                setTimeout(() => {
                    window.close();
                }, 1500);
            }
        });
        
        // Handle before print
        window.addEventListener('beforeprint', function() {
            console.log('Starting print process...');
            // Hide all no-print elements
            document.querySelectorAll('.no-print').forEach(el => {
                el.style.display = 'none';
            });
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
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                downloadBadges();
            }
        });
        
        // Performance monitoring for large batches
        const badgeCount = document.querySelectorAll('.badge-container').length;
        if (badgeCount > 50) {
            console.log(`Large batch detected: ${badgeCount} badges`);
            
            // Add performance optimization for large batches
            document.addEventListener('DOMContentLoaded', function() {
                // Lazy load badge animations for better performance
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }
                    });
                });
                
                document.querySelectorAll('.badge-container').forEach(badge => {
                    badge.style.opacity = '0';
                    badge.style.transform = 'translateY(10px)';
                    badge.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    observer.observe(badge);
                });
            });
        }
        
        // Error handling
        window.addEventListener('error', function(e) {
            console.error('Badge print error:', e.error);
            hidePrintProgress();
            alert('An error occurred while preparing badges for print. Please try again.');
        });
        
        // Log statistics
        console.log('Badge Print Statistics:', {
            totalBadges: badgeCount,
            templateGroups: document.querySelectorAll('.template-group').length,
            loadTime: performance.now(),
            userAgent: navigator.userAgent
        });
    </script>
</body>
</html>