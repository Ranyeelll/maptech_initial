<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->unsignedBigInteger('lesson_id')->nullable()->after('module_id');
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropForeign(['lesson_id']);
            $table->dropColumn('lesson_id');
        });
    }
};
