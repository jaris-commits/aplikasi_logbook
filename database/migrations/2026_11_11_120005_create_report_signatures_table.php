<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('report_type');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->string('signature_path');
            $table->string('document_hash', 64);
            $table->timestamp('signed_at');
            $table->timestamps();
            $table->index(['user_id', 'report_type', 'signed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_signatures');
    }
};