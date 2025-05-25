<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('registration_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('field_name', 50);
            $table->enum('field_type', ['text', 'email', 'number', 'dropdown', 'checkbox', 'textarea', 'date', 'phone']);
            $table->boolean('is_required')->default(true);
            $table->text('options')->nullable(); // For dropdown options (comma-separated)
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['event_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_fields');
    }
};