<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('kiosk_activation_codes', 'created_by')) {
            Schema::table('kiosk_activation_codes', function (Blueprint $table) {
                // Use UUID to match users.id which may have been converted to CHAR(36)
                $table->uuid('created_by')->nullable()->after('id');
            });

            Schema::table('kiosk_activation_codes', function (Blueprint $table) {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('SET NULL');
            });
        }

        if (! Schema::hasColumn('kiosk_activation_codes', 'created_ip')) {
            Schema::table('kiosk_activation_codes', function (Blueprint $table) {
                $table->string('created_ip', 45)->nullable()->after('code_hash');
            });
        }
    }

    public function down(): void
    {
        Schema::table('kiosk_activation_codes', function (Blueprint $table) {
            if (Schema::hasColumn('kiosk_activation_codes', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }

            if (Schema::hasColumn('kiosk_activation_codes', 'created_ip')) {
                $table->dropColumn('created_ip');
            }
        });
    }
};
