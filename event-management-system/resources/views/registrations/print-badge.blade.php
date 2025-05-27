<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Badge - {{ $registration->user->name }}</title>
    <style>
        @page {
            size: {{ $badgeTemplate->width }}cm {{ $badgeTemplate->height }}cm;
            margin: 0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: white;
            overflow: hidden;
        }
        
        .badge-container {
            position: relative;
            width: {{ $badgeTemplate->width }}cm;
            height: {{ $badgeTemplate->height }}cm;
            background: {{ $badgeTemplate->background_color ?? '#ffffff' }};
            @if($badgeTemplate->background_image)
            background-image: url('{{ Storage::url($badgeTemplate->background_image) }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            @endif
            border: {{ $badgeTemplate->border_width ?? 0 }}px solid {{ $badgeTemplate->border_color ?? '#000000' }};
            page-break-after: always;
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
            
            .badge-container {
                break-inside: avoid;
            }
        }
        
        /* Print controls */
        .print-controls {
            position: fixed;
            top: 10px;
            right: 10px;
            background: white;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            margin: 0 5px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            border: none;
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
        }
    </style>
</head>
<body>
    <!-- Print Controls -->
    <div class="print-controls no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Print Badge
        </button>
        <a href="{{ route('registrations.show', $registration) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <!-- Badge Container -->
    <div class="badge-container">
        @foreach($badgeTemplate->contents as $content)
            @if($content->isQrCodeField())
                {{-- QR Code Field --}}
                <div class="qr-code-field" 
                     style="{{ $content->getPositionStyles() }} width: {{ $content->image_width }}cm; height: {{ $content->image_height }}cm;">
                    @if($registration->qrCode && $registration->qrCode->qr_image)
                        <img src="data:image/png;base64,{{ $registration->qrCode->qr_image }}" 
                             alt="QR Code"
                             style="width: {{ $content->image_width }}cm; height: {{ $content->image_height }}cm;">
                    @else
                        <div style="width: {{ $content->image_width }}cm; height: {{ $content->image_height }}cm; 
                                    background: #f0f0f0; border: 2px dashed #ccc; 
                                    display: flex; align-items: center; justify-content: center; 
                                    font-size: 10px; color: #666;">
                            QR CODE
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

    <script>
        // Auto-print when page loads (optional)
        // window.addEventListener('load', function() {
        //     setTimeout(() => window.print(), 500);
        // });
        
        // Handle print dialog
        window.addEventListener('afterprint', function() {
            // Optional: redirect back after printing
            // window.location.href = '{{ route("registrations.show", $registration) }}';
        });
    </script>
</body>
</html>