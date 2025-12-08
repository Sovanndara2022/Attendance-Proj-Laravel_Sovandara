<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('present_status')->default(2);   // 2 present, 1 late, 0 absent
            $table->unsignedTinyInteger('online_status')->default(2);    // 2 yes, 1 partial, 0 no
            $table->unsignedTinyInteger('classwork_status')->default(2);
            $table->unsignedTinyInteger('control_status')->default(2);
            $table->unsignedTinyInteger('thematic_status')->default(2);
            $table->unsignedTinyInteger('stars')->default(0);
            $table->text('comment')->nullable();

            $table->timestamps();
            $table->unique(['class_session_id', 'student_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('attendances');
    }
};
