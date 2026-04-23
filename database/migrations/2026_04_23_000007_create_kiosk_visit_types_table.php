<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    if (Schema::hasTable('kiosk_visit_types')) {
      return;
    }

    Schema::create('kiosk_visit_types', function (Blueprint $table) {
      $table->char('kiosk_id', 36);
      $table->char('visit_type_id', 36);
      $table->primary(['kiosk_id', 'visit_type_id']);

      $table->foreign('kiosk_id')->references('id')->on('kiosks')->cascadeOnDelete();
      $table->foreign('visit_type_id')->references('id')->on('visit_types')->cascadeOnDelete();
    });
  }

  public function down(): void
  {
    if (! Schema::hasTable('kiosk_visit_types')) {
      return;
    }

    Schema::dropIfExists('kiosk_visit_types');
  }
};
