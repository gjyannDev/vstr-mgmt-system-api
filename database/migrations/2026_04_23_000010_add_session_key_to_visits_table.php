<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('visits', function (Blueprint $table) {
      if (! Schema::hasColumn('visits', 'session_key')) {
        $table->uuid('session_key')->nullable()->after('qr_code');
        $table->index('session_key');
      }
    });
  }

  public function down(): void
  {
    Schema::table('visits', function (Blueprint $table) {
      if (Schema::hasColumn('visits', 'session_key')) {
        $table->dropIndex(['session_key']);
        $table->dropColumn('session_key');
      }
    });
  }
};
