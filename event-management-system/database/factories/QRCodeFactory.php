<?php
// database/factories/QRCodeFactory.php

namespace Database\Factories;

use App\Models\QRCode;
use App\Models\Registration;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;


class QRCodeFactory extends Factory
{
    protected $model = QRCode::class;

    public function definition()
    {
        return [
            'registration_id' => Registration::factory(),
            'ticket_type_id'  => Ticket::factory(),
            'qr_image_path'   => null, // generated in boot()
            'qr_data'         => [],   // populated by generateQRCode()
        ];
    }
}
