<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('visit_types', function (Blueprint $table) {
            // add camera flag to enable capturing visitor photo
            $table->boolean('is_camera_active')->default(false)->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('visit_types', function (Blueprint $table) {
            $table->dropColumn('is_camera_active');
        });
    }
};
