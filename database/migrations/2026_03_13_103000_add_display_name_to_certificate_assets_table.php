<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('certificate_assets', function (Blueprint $table) {
            if (!Schema::hasColumn('certificate_assets', 'display_name')) {
                $table->string('display_name')->nullable()->after('path');
            }
        });
    }

    public function down()
    {
        Schema::table('certificate_assets', function (Blueprint $table) {
            if (Schema::hasColumn('certificate_assets', 'display_name')) {
                $table->dropColumn('display_name');
            }
        });
    }
};
