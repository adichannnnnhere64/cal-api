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
        
        Schema::create('calendar_category_calendar_event', function (Blueprint $table) {
            $table->foreignId('calendar_category_id');
            $table->foreignId('calendar_event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendar_category_calendar_event', function (Blueprint $table) {
        });
    }
};
