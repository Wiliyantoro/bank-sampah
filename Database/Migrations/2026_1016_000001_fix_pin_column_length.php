<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bank_sampah_member')) {
            return;
        }

        Schema::table('bank_sampah_member', function (Blueprint $table) {
            $table->string('pin', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bank_sampah_member', function (Blueprint $table) {
            $table->string('pin', 6)->nullable()->change();
        });
    }
};
