<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('topic')->nullable();
            $table->boolean('is_substitute')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('class_sessions');
    }
};
