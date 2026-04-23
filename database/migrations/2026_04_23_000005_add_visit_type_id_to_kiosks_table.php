<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    if (! Schema::hasTable('kiosks')) {
      return;
    }

    if (! Schema::hasColumn('kiosks', 'visit_type_id')) {
      Schema::table('kiosks', function (Blueprint $table) {
        $table->char('visit_type_id', 36)->nullable()->after('location_id');
        $table->index('visit_type_id');
        $table->foreign('visit_type_id')->references('id')->on('visit_types')->nullOnDelete();
      });
    }
  }

  public function down(): void
  {
    if (! Schema::hasTable('kiosks') || ! Schema::hasColumn('kiosks', 'visit_type_id')) {
      return;
    }

    Schema::table('kiosks', function (Blueprint $table) {
      $table->dropForeign(['visit_type_id']);
      $table->dropColumn('visit_type_id');
    });
  }
};
