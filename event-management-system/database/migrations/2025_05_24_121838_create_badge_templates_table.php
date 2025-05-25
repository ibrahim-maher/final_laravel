<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('badge_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->string('name', 100);
            $table->float('width');
            $table->float('height');
            $table->string('background_image')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->enum('default_font', ['Arial', 'Helvetica', 'Times New Roman', 'Courier', 'Verdana', 'Georgia'])->default('Arial');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('badge_templates');
    }
};