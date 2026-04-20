<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('locations') || ! Schema::hasColumn('locations', 'tenant_id')) {
            return;
        }

        if ($this->hasForeignKey('locations', 'locations_tenant_id_foreign')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->dropForeign('locations_tenant_id_foreign');
            });
        }

        DB::statement('ALTER TABLE `locations` MODIFY `tenant_id` CHAR(36) NOT NULL');

        if (Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'legacy_id')) {
            DB::statement(
                'UPDATE `locations` AS l JOIN `tenants` AS t ON t.`legacy_id` = l.`tenant_id` SET l.`tenant_id` = t.`id`'
            );
        }

        $invalidCount = DB::scalar(
            'SELECT COUNT(*) FROM `locations` AS l LEFT JOIN `tenants` AS t ON l.`tenant_id` = t.`id` WHERE l.`tenant_id` IS NOT NULL AND t.`id` IS NULL'
        );

        if ((int) $invalidCount > 0) {
            throw new RuntimeException("Cannot add FK: {$invalidCount} locations have invalid tenant_id values.");
        }

        Schema::table('locations', function (Blueprint $table) {
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('locations') || ! Schema::hasColumn('locations', 'tenant_id')) {
            return;
        }

        if ($this->hasForeignKey('locations', 'locations_tenant_id_foreign')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->dropForeign('locations_tenant_id_foreign');
            });
        }

        DB::statement('ALTER TABLE `locations` MODIFY `tenant_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('locations', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete()
                ->change();
        });
    }

    private function hasForeignKey(string $table, string $foreignKeyName): bool
    {
        $database = DB::getDatabaseName();

        $result = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ? LIMIT 1',
            [$database, $table, $foreignKeyName, 'FOREIGN KEY']
        );

        return count($result) > 0;
    }
};
