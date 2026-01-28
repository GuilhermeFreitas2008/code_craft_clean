<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('user_progress', function (Blueprint $table) {
            // 🔒 impede progresso duplicado da mesma lição
            $table->unique(['user_id', 'lesson_id'],'user_progress_user_lesson_unique');
        });
    }

    public function down(): void
    {
        Schema::table('user_progress', function (Blueprint $table) {
            $table->dropUnique('user_progress_user_lesson_unique');
        });
    }
};
