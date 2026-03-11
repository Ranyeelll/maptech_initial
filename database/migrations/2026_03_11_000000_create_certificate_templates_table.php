<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // courses.id in this project uses UUIDs, so match the type
            $table->uuid('course_id')->nullable()->unique();
            $table->json('template_data')->nullable();
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('courses')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('certificate_templates');
    }
};
