<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('kiosk_activation_codes')) {
            return;
        }

        // Ensure the column exists as CHAR(36) to accept UUID strings
        if (Schema::hasColumn('kiosk_activation_codes', 'created_by')) {
            // Try to alter column type to CHAR(36) (idempotent if already correct)
            try {
                DB::statement('ALTER TABLE `kiosk_activation_codes` MODIFY `created_by` CHAR(36) NULL');
            } catch (\Throwable $e) {
                // ignore errors (may require different SQL privileges or already correct)
            }
        } else {
            Schema::table('kiosk_activation_codes', function (Blueprint $table) {
                $table->uuid('created_by')->nullable()->after('id');
            });
        }

        // Drop any existing foreign key constraints on created_by
        $constraints = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->select('CONSTRAINT_NAME')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'kiosk_activation_codes')
            ->where('COLUMN_NAME', 'created_by')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->pluck('CONSTRAINT_NAME')
            ->unique();

        foreach ($constraints as $constraint) {
            try {
                DB::statement("ALTER TABLE `kiosk_activation_codes` DROP FOREIGN KEY `{$constraint}`");
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // Recreate foreign key to users(id) if users table exists
        if (Schema::hasTable('users')) {
            try {
                DB::statement('ALTER TABLE `kiosk_activation_codes` ADD CONSTRAINT `kiosk_activation_codes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL');
            } catch (\Throwable $e) {
                // ignore if already exists
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('kiosk_activation_codes')) {
            return;
        }

        if (Schema::hasColumn('kiosk_activation_codes', 'created_by')) {
            try {
                Schema::table('kiosk_activation_codes', function (Blueprint $table) {
                    $table->dropForeign(['created_by']);
                });
            } catch (\Throwable $e) {
                // ignore
            }

            Schema::table('kiosk_activation_codes', function (Blueprint $table) {
                $table->dropColumn('created_by');
            });
        }
    }
};
