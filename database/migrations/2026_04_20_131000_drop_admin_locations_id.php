<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    if (! Schema::hasTable('admin_locations') || ! Schema::hasColumn('admin_locations', 'id')) {
      return;
    }

    Schema::table('admin_locations', function (Blueprint $table) {
      $table->dropColumn('id');
    });
  }

  public function down(): void
  {
    if (! Schema::hasTable('admin_locations') || Schema::hasColumn('admin_locations', 'id')) {
      return;
    }

    Schema::table('admin_locations', function (Blueprint $table) {
      $table->uuid('id')->first();
    });

    DB::statement('UPDATE admin_locations SET id = UUID() WHERE id IS NULL OR id = ""');
    DB::statement('ALTER TABLE admin_locations ADD PRIMARY KEY (id)');
  }
};
