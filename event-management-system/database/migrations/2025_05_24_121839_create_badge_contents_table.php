<?php

// Migration: create_badge_templates_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBadgeTemplatesTable extends Migration
{
    public function up()
    {
        Schema::create('badge_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->decimal('width', 5, 2); // Width in cm
            $table->decimal('height', 5, 2); // Height in cm
            $table->string('background_image')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->enum('default_font', ['Arial', 'Helvetica', 'Times New Roman', 'Courier', 'Verdana', 'Georgia'])->default('Arial');
            $table->timestamps();

            // Ensure one template per ticket
            $table->unique('ticket_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('badge_templates');
    }
}

// Migration: create_badge_contents_table.php
class CreateBadgeContentsTable extends Migration
{
    public function up()
    {
        Schema::create('badge_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('badge_templates')->onDelete('cascade');
            $table->enum('field_name', array_keys(\App\Models\BadgeContent::FIELD_CHOICES));

            $table->decimal('position_x', 5, 2); // X position in cm
            $table->decimal('position_y', 5, 2); // Y position in cm
            $table->integer('font_size')->default(12);
            $table->string('font_color', 7)->default('#000000'); // Hex color
            $table->enum('font_family', ['Arial', 'Helvetica', 'Times New Roman', 'Courier', 'Verdana', 'Georgia'])->default('Arial');
            $table->boolean('is_bold')->default(false);
            $table->boolean('is_italic')->default(false);
            $table->decimal('image_width', 5, 2)->nullable(); // For QR codes
            $table->decimal('image_height', 5, 2)->nullable(); // For QR codes
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('badge_contents');
    }
}