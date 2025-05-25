<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('badge_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('badge_templates')->onDelete('cascade');
            $table->string('field_name', 50);
            $table->float('position_x');
            $table->float('position_y');
            $table->integer('font_size')->default(12);
            $table->string('font_color', 7)->default('#000000');
            $table->enum('font_family', ['Arial', 'Helvetica', 'Times New Roman', 'Courier', 'Verdana', 'Georgia'])->default('Arial');
            $table->boolean('is_bold')->default(false);
            $table->boolean('is_italic')->default(false);
            $table->float('image_width')->nullable();
            $table->float('image_height')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('badge_contents');
    }
};