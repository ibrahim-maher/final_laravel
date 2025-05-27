<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('visitor_logs', function (Blueprint $table) {
        $table->timestamp('visited_at')->nullable()->after('created_by');
    });
}

public function down()
{
    Schema::table('visitor_logs', function (Blueprint $table) {
        $table->dropColumn('visited_at');
    });
}

};
