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
        Schema::create('meeting_rooms_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('memo')->nullable();
            $table->unsignedBigInteger('meeting_rooms_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('reservation_start')->nullable();
            $table->timestamp('reservation_end')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_rooms_reservations');
    }
};
