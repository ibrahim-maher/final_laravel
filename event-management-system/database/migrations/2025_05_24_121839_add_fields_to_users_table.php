<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['ADMIN', 'EVENT_MANAGER', 'USHER', 'VISITOR'])->default('VISITOR')->after('email');
            }
            if (!Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number', 15)->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'title')) {
                $table->string('title', 300)->nullable()->after('phone_number');
            }
            if (!Schema::hasColumn('users', 'country')) {
                $table->string('country', 100)->nullable()->after('title');
            }
            if (!Schema::hasColumn('users', 'company')) {
                $table->string('company', 300)->nullable()->after('country');
            }
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone_number', 'title', 'country', 'company', 'first_name', 'last_name']);
        });
    }
};