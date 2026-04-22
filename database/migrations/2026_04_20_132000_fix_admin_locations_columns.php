<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    if (! Schema::hasTable('admin_locations')) {
      return;
    }

    $this->dropForeignKeyIfExists('admin_locations', 'admin_locations_user_id_foreign');
    $this->dropForeignKeyIfExists('admin_locations', 'admin_locations_location_id_foreign');

    Schema::table('admin_locations', function (Blueprint $table) {
      $table->char('user_id', 36)->change();
      $table->char('location_id', 36)->change();
    });

    Schema::table('admin_locations', function (Blueprint $table) {
      $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
      $table->foreign('location_id')->references('id')->on('locations')->cascadeOnDelete();
    });
  }

  public function down(): void
  {
    if (! Schema::hasTable('admin_locations')) {
      return;
    }

    $this->dropForeignKeyIfExists('admin_locations', 'admin_locations_user_id_foreign');
    $this->dropForeignKeyIfExists('admin_locations', 'admin_locations_location_id_foreign');

    Schema::table('admin_locations', function (Blueprint $table) {
      $table->unsignedBigInteger('user_id')->change();
      $table->unsignedBigInteger('location_id')->change();
    });

    Schema::table('admin_locations', function (Blueprint $table) {
      $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
      $table->foreign('location_id')->references('id')->on('locations')->cascadeOnDelete();
    });
  }

  private function dropForeignKeyIfExists(string $table, string $constraintName): void
  {
    $exists = DB::table('information_schema.TABLE_CONSTRAINTS')
      ->where('TABLE_SCHEMA', DB::getDatabaseName())
      ->where('TABLE_NAME', $table)
      ->where('CONSTRAINT_NAME', $constraintName)
      ->exists();

    if ($exists) {
      DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraintName}`");
    }
  }
};
