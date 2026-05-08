<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->string('quiz_type')->default('regular')->after('lesson_id');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('quiz_type');
        });
    }
};
