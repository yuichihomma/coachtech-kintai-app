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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();//PK

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();//FK→users.id

            $table->date('work_date'); //勤務日
            $table->dateTime('clock_in')->nullable(); //出勤時刻
            $table->dateTime('clock_out')->nullable(); //退勤時刻
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
