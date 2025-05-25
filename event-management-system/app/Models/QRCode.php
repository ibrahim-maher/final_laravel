<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;
use Illuminate\Support\Facades\Storage;

class QRCode extends Model
{
    use HasFactory;

    protected $table = 'q_r_codes';

    protected $fillable = ['registration_id', 'ticket_id', 'qr_image'];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function generateQRCode()
    {
        $qrData = [
            'user_id' => $this->registration->user_id,
            'event_id' => $this->registration->event_id,
            'registration_id' => $this->registration->id,
            'ticket_type' => $this->ticket ? $this->ticket->name : 'No Ticket',
        ];

        $qrCodeContent = json_encode($qrData);
        $fileName = 'qr_' . $this->registration->id . '.png';
        
        // Generate QR code
        $qrCode = QrCodeGenerator::format('png')->size(300)->generate($qrCodeContent);
        
        // Store QR code
        Storage::disk('public')->put('qr_codes/' . $fileName, $qrCode);
        
        $this->qr_image = 'qr_codes/' . $fileName;
        $this->save();
    }
}