<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['ADMIN', 'EVENT_MANAGER', 'USHER', 'VISITOR'])->default('VISITOR');
            $table->string('phone_number', 15)->nullable();
            $table->string('title', 300)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('company', 300)->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone_number', 'title', 'country', 'company']);
        });
    }
};