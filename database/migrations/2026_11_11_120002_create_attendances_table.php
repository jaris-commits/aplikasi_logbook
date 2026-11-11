<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('mode', ['WFO', 'WFH']);
            $table->timestamp('check_in_at')->nullable();
            $table->string('check_in_photo')->nullable();
            $table->string('check_in_mime')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};