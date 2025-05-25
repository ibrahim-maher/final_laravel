<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;

class QRCode extends Model
{
    use HasFactory;

    protected $table = 'qr_codes';

    protected $fillable = [
        'registration_id', 'ticket_type_id', 'qr_image_path', 'qr_data'
    ];

    protected $casts = [
        'qr_data' => 'array',
    ];

    // Relationships
    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function ticketType()
    {
        return $this->belongsTo(Ticket::class, 'ticket_type_id');
    }

    // Accessors
    public function getQrImageUrlAttribute()
    {
        return $this->qr_image_path ? Storage::url($this->qr_image_path) : null;
    }

    // Methods
    public function generateQRCode()
    {
        $registration = $this->registration;
        
        // Prepare QR code data
        $qrData = [
            'user_id' => $registration->user_id,
            'event_id' => $registration->event_id,
            'registration_id' => $registration->id,
            'ticket_type' => $this->ticketType ? $this->ticketType->name : 'No Ticket',
            'generated_at' => now()->toISOString(),
        ];

        // Generate QR code
        $qrCode = QrCodeGenerator::size(300)
            ->format('png')
            ->generate(json_encode($qrData));

        // Create filename and path
        $filename = "qr_code_{$registration->id}.png";
        $path = "qr_codes/{$filename}";

        // Store the QR code image
        Storage::disk('public')->put($path, $qrCode);

        // Update the model
        $this->update([
            'qr_image_path' => $path,
            'qr_data' => $qrData,
        ]);

        return $this;
    }

    public function regenerateQRCode()
    {
        // Delete old QR code if exists
        if ($this->qr_image_path && Storage::disk('public')->exists($this->qr_image_path)) {
            Storage::disk('public')->delete($this->qr_image_path);
        }

        // Generate new QR code
        return $this->generateQRCode();
    }

    public function deleteQRCodeFile()
    {
        if ($this->qr_image_path && Storage::disk('public')->exists($this->qr_image_path)) {
            Storage::disk('public')->delete($this->qr_image_path);
        }
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($qrCode) {
            $qrCode->generateQRCode();
        });

        static::deleting(function ($qrCode) {
            $qrCode->deleteQRCodeFile();
        });
    }
}