<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Registration;
use App\Models\QRCode;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DebugQRGeneration extends Command
{
    protected $signature = 'qr:debug {registration_id?}';
    protected $description = 'Debug QR code generation issues';

    public function handle()
    {
        $this->info('🔍 Starting QR code generation debug...');

        // Step 1: Check if QR package is working
        $this->info('Step 1: Testing QR code package...');
        try {
            $testQR = QrCodeGenerator::size(100)->generate('test');
            $this->info('✅ QR code package is working');
        } catch (\Exception $e) {
            $this->error('❌ QR code package error: ' . $e->getMessage());
            return;
        }

        // Step 2: Check database tables
        $this->info('Step 2: Checking database tables...');
        try {
            $registrationCount = Registration::count();
            $qrCodeCount = QRCode::count();
            $this->info("✅ Registrations table: {$registrationCount} records");
            $this->info("✅ QR codes table: {$qrCodeCount} records");
        } catch (\Exception $e) {
            $this->error('❌ Database error: ' . $e->getMessage());
            return;
        }

        // Step 3: Test with specific registration or find one
        $registrationId = $this->argument('registration_id');
        
        if ($registrationId) {
            $registration = Registration::find($registrationId);
        } else {
            $registration = Registration::with(['user', 'event', 'ticketType'])->first();
        }

        if (!$registration) {
            $this->error('❌ No registration found to test with');
            return;
        }

        $this->info("Step 3: Testing with registration ID: {$registration->id}");

        // Step 4: Check registration data
        $this->info('Step 4: Checking registration data...');
        $this->table(['Field', 'Value'], [
            ['ID', $registration->id],
            ['User ID', $registration->user_id],
            ['Event ID', $registration->event_id],
            ['User Name', $registration->user->name ?? 'NULL'],
            ['Event Name', $registration->event->name ?? 'NULL'],
            ['Ticket Type', $registration->ticketType->name ?? 'NULL'],
            ['Status', $registration->status],
        ]);

        // Step 5: Check existing QR code
        $this->info('Step 5: Checking existing QR code...');
        $existingQR = $registration->qrCode;
        if ($existingQR) {
            $this->warn("QR code record exists (ID: {$existingQR->id})");
            $this->info("QR image data length: " . strlen($existingQR->qr_image ?? ''));
        } else {
            $this->info("No existing QR code record");
        }

        // Step 6: Manual QR generation test
        $this->info('Step 6: Testing manual QR generation...');
        try {
            $qrData = [
                'registration_id' => $registration->id,
                'user_id' => $registration->user_id,
                'event_id' => $registration->event_id,
                'test' => 'manual_generation',
                'timestamp' => now()->toISOString(),
            ];

            $this->info('QR Data: ' . json_encode($qrData));

            // Generate QR code
            $qrCodePng = QrCodeGenerator::format('png')
                ->size(300)
                ->margin(1)
                ->errorCorrection('M')
                ->generate(json_encode($qrData));

            $this->info('✅ QR code PNG generated, size: ' . strlen($qrCodePng) . ' bytes');

            // Convert to base64
            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrCodePng);
            $this->info('✅ Base64 encoded, size: ' . strlen($qrCodeBase64) . ' characters');

            // Try to save to database
            DB::beginTransaction();
            
            $qrCodeRecord = QRCode::updateOrCreate(
                ['registration_id' => $registration->id],
                [
                    'ticket_type_id' => $registration->ticket_type_id,
                    'qr_image' => $qrCodeBase64,
                    'qr_data' => $qrData,
                ]
            );

            DB::commit();
            
            $this->info("✅ QR code saved to database (ID: {$qrCodeRecord->id})");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Manual generation failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }

        // Step 7: Test model method
        $this->info('Step 7: Testing Registration model method...');
        try {
            $result = $registration->generateQRCode();
            if ($result) {
                $this->info('✅ Model method succeeded');
                
                // Refresh and check
                $registration->refresh();
                if ($registration->qrCode && $registration->qrCode->qr_image) {
                    $this->info('✅ QR code is now available in database');
                    $this->info('QR image length: ' . strlen($registration->qrCode->qr_image));
                } else {
                    $this->error('❌ QR code not found after generation');
                }
            } else {
                $this->error('❌ Model method returned null/false');
            }
        } catch (\Exception $e) {
            $this->error('❌ Model method failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }

        // Step 8: Check final state
        $this->info('Step 8: Final verification...');
        $registration->refresh();
        $finalQR = $registration->qrCode;
        
        if ($finalQR && !empty($finalQR->qr_image)) {
            $this->info('🎉 SUCCESS: QR code is now available!');
            $this->info("QR Code ID: {$finalQR->id}");
            $this->info("Image length: " . strlen($finalQR->qr_image));
            $this->info("Starts with: " . substr($finalQR->qr_image, 0, 50) . '...');
        } else {
            $this->error('❌ FAILED: QR code is still not available');
        }

        $this->newLine();
        $this->info('Debug completed!');
    }
}