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
        Schema::create('breaks', function (Blueprint $table) {
            $table->id(); // PK

            $table->foreignId('attendance_id')
                ->constrained()
                ->cascadeOnDelete(); // FK → attendance.id

            $table->timestamp('break_start'); //　休憩開始
            $table->timestamp('break_end')->nullable(); //休憩終了
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('breaks');
    }
};
