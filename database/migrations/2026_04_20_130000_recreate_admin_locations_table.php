<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    if (Schema::hasTable('admin_locations')) {
      return;
    }

    Schema::create('admin_locations', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->char('user_id', 36);
      $table->char('location_id', 36);
      $table->timestamps();

      $table->foreign('user_id')
        ->references('id')
        ->on('users')
        ->cascadeOnDelete();

      $table->foreign('location_id')
        ->references('id')
        ->on('locations')
        ->cascadeOnDelete();

      $table->unique(['user_id', 'location_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('admin_locations');
  }
};
