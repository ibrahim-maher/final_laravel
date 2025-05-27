<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Print Badges</title>
    <style>
        @page {
            margin: 0.5cm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: white;
        }
        
        .badges-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(8cm, 1fr));
            gap: 1cm;
            padding: 1cm;
        }
        
        .badge-container {
            position: relative;
            background: white;
            border: 1px solid #ddd;
            page-break-inside: avoid;
            margin-bottom: 1cm;
        }
        
        .badge-field {
            position: absolute;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .qr-code-field {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .qr-code-field img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .no-print {
                display: none !important;
            }
            
            .badges-grid {
                padding: 0;
                gap: 0.5cm;
            }
            
            .badge-container {
                border: none;
                margin-bottom: 0.5cm;
            }
        }
        
        /* Print controls */
        .print-controls {
            position: fixed;
            top: 10px;
            right: 10px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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
        
        .print-info {
            margin-bottom: 10px;
            font-size: 13px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Print Controls -->
    <div class="print-controls no-print">
        <div class="print-info">
            <strong>{{ count($badgeData) }}</strong> badges ready to print
        </div>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Print All Badges
        </button>
        <a href="{{ route('registrations.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Registrations
        </a>
    </div>

    <!-- Badges Grid -->
    <div class="badges-grid">
        @foreach($badgeData as $item)
            @php
                $registration = $item['registration'];
                $badgeTemplate = $item['template'];
            @endphp
            
            <div class="badge-container" 
                 style="width: {{ $badgeTemplate->width }}cm; 
                        height: {{ $badgeTemplate->height }}cm; 
                        background: {{ $badgeTemplate->background_color ?? '#ffffff' }};
                        @if($badgeTemplate->background_image)
                        background-image: url('{{ Storage::url($badgeTemplate->background_image) }}');
                        background-size: cover;
                        background-position: center;
                        background-repeat: no-repeat;
                        @endif
                        border: {{ $badgeTemplate->border_width ?? 0 }}px solid {{ $badgeTemplate->border_color ?? '#000000' }};">
                
                @foreach($badgeTemplate->contents as $content)
                    @if($content->isQrCodeField())
                        {{-- QR Code Field --}}
                        <div class="qr-code-field" 
                             style="{{ $content->getPositionStyles() }} width: {{ $content->image_width }}cm; height: {{ $content->image_height }}cm;">
                            @if($registration->qrCode && $registration->qrCode->qr_image)
                                <img src="data:image/png;base64,{{ $registration->qrCode->qr_image }}" 
                                     alt="QR Code for {{ $registration->user->name }}"
                                     style="width: {{ $content->image_width }}cm; height: {{ $content->image_height }}cm;">
                            @else
                                <div style="width: {{ $content->image_width }}cm; height: {{ $content->image_height }}cm; 
                                            background: #f0f0f0; border: 2px dashed #ccc; 
                                            display: flex; align-items: center; justify-content: center; 
                                            font-size: 8px; color: #666;">
                                    NO QR
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- Text Field --}}
                        <div class="badge-field" 
                             style="{{ $content->getPositionStyles() }} {{ $content->getFontStyles() }}">
                            {{ $content->getFieldValue($registration) ?: $content->getFieldDisplayName() }}
                        </div>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>

    <script>
        // Print functionality
        function printBadges() {
            window.print();
        }
        
        // Optional: Auto-print after a delay
        // window.addEventListener('load', function() {
        //     setTimeout(() => window.print(), 1000);
        // });
        
        // Handle print completion
        window.addEventListener('afterprint', function() {
            console.log('Print dialog completed');
            // Optional: redirect back after printing
            // setTimeout(() => {
            //     window.location.href = '{{ route("registrations.index") }}';
            // }, 1000);
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>