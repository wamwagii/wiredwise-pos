<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                // Drop and recreate the data column with proper JSON type
                $table->dropColumn('data');
            });
            
            Schema::table('notifications', function (Blueprint $table) {
                $table->json('data')->after('read_at');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropColumn('data');
            });
            
            Schema::table('notifications', function (Blueprint $table) {
                $table->text('data')->nullable()->after('read_at');
            });
        }
    }
};