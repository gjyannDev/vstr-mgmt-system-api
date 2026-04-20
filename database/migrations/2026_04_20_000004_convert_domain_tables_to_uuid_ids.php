<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * @var array<int, string>
     */
    private array $domainTables = [
        'users',
        'locations',
        'tenants',
        'hosts',
        'visitors',
        'visit_types',
        'form_fields',
        'visits',
        'visit_responses',
        'kiosks',
        'kiosk_activation_codes',
        'admin_locations',
    ];

    /**
     * @var array<int, array{table:string,column:string,references:string,nullable:bool,onDelete:string}>
     */
    private array $foreignMappings = [
        ['table' => 'users', 'column' => 'tenant_id', 'references' => 'tenants', 'nullable' => true, 'onDelete' => 'SET NULL'],
        ['table' => 'users', 'column' => 'location_id', 'references' => 'locations', 'nullable' => true, 'onDelete' => 'SET NULL'],

        ['table' => 'locations', 'column' => 'tenant_id', 'references' => 'tenants', 'nullable' => false, 'onDelete' => 'CASCADE'],

        ['table' => 'hosts', 'column' => 'tenant_id', 'references' => 'tenants', 'nullable' => false, 'onDelete' => 'CASCADE'],
        ['table' => 'hosts', 'column' => 'location_id', 'references' => 'locations', 'nullable' => false, 'onDelete' => 'CASCADE'],

        ['table' => 'visitors', 'column' => 'tenant_id', 'references' => 'tenants', 'nullable' => false, 'onDelete' => 'CASCADE'],
        ['table' => 'visitors', 'column' => 'location_id', 'references' => 'locations', 'nullable' => false, 'onDelete' => 'CASCADE'],

        ['table' => 'visit_types', 'column' => 'tenant_id', 'references' => 'tenants', 'nullable' => false, 'onDelete' => 'CASCADE'],
        ['table' => 'visit_types', 'column' => 'location_id', 'references' => 'locations', 'nullable' => false, 'onDelete' => 'CASCADE'],

        ['table' => 'form_fields', 'column' => 'tenant_id', 'references' => 'tenants', 'nullable' => false, 'onDelete' => 'CASCADE'],
        ['table' => 'form_fields', 'column' => 'location_id', 'references' => 'locations', 'nullable' => false, 'onDelete' => 'CASCADE'],
        ['table' => 'form_fields', 'column' => 'visit_type_id', 'references' => 'visit_types', 'nullable' => false, 'onDelete' => 'CASCADE'],

        ['table' => 'visits', 'column' => 'tenant_id', 'references' => 'tenants', 'nullable' => false, 'onDelete' => 'CASCADE'],
        ['table' => 'visits', 'column' => 'location_id', 'references' => 'locations', 'nullable' => false, 'onDelete' => 'CASCADE'],
        ['table' => 'visits', 'column' => 'visitor_id', 'references' => 'visitors', 'nullable' => false, 'onDelete' => 'CASCADE'],
        ['table' => 'visits', 'column' => 'host_id', 'references' => 'hosts', 'nullable' => true, 'onDelete' => 'SET NULL'],
        ['table' => 'visits', 'column' => 'visit_type_id', 'references' => 'visit_types', 'nullable' => false, 'onDelete' => 'CASCADE'],
        ['table' => 'visits', 'column' => 'check_in_by', 'references' => 'users', 'nullable' => true, 'onDelete' => 'SET NULL'],
        ['table' => 'visits', 'column' => 'check_out_by', 'references' => 'users', 'nullable' => true, 'onDelete' => 'SET NULL'],

        ['table' => 'visit_responses', 'column' => 'visit_id', 'references' => 'visits', 'nullable' => false, 'onDelete' => 'CASCADE'],
        ['table' => 'visit_responses', 'column' => 'form_field_id', 'references' => 'form_fields', 'nullable' => false, 'onDelete' => 'CASCADE'],
        ['table' => 'visit_responses', 'column' => 'location_id', 'references' => 'locations', 'nullable' => false, 'onDelete' => 'CASCADE'],

        ['table' => 'kiosks', 'column' => 'tenant_id', 'references' => 'tenants', 'nullable' => false, 'onDelete' => 'CASCADE'],
        ['table' => 'kiosks', 'column' => 'location_id', 'references' => 'locations', 'nullable' => false, 'onDelete' => 'CASCADE'],
        ['table' => 'kiosk_activation_codes', 'column' => 'kiosk_id', 'references' => 'kiosks', 'nullable' => false, 'onDelete' => 'CASCADE'],

        ['table' => 'admin_locations', 'column' => 'user_id', 'references' => 'users', 'nullable' => false, 'onDelete' => 'CASCADE'],
        ['table' => 'admin_locations', 'column' => 'location_id', 'references' => 'locations', 'nullable' => false, 'onDelete' => 'CASCADE'],
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            throw new RuntimeException('UUID ID conversion migration currently supports MySQL only.');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            $this->addUuidPrimaryColumns();
            $this->populateUuidPrimaryColumns();

            $this->addUuidForeignColumns();
            $this->populateUuidForeignColumns();

            $this->convertSessionsUserIdToUuid();
            $this->convertPersonalAccessTokenableIdToUuid();

            $this->dropExistingForeignKeys();

            $this->swapPrimaryKeys();
            $this->swapForeignKeys();
            $this->rebuildForeignKeys();
            $this->rebuildAdminLocationsUnique();
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
        throw new RuntimeException('This migration is irreversible. Restore from backup to rollback.');
    }

    private function addUuidPrimaryColumns(): void
    {
        foreach ($this->domainTables as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'id_uuid')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->uuid('id_uuid')->nullable()->unique()->after('id');
            });
        }
    }

    private function populateUuidPrimaryColumns(): void
    {
        foreach ($this->domainTables as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'id_uuid')) {
                continue;
            }

            DB::statement("UPDATE `{$tableName}` SET `id_uuid` = UUID() WHERE `id_uuid` IS NULL");
        }
    }

    private function addUuidForeignColumns(): void
    {
        foreach ($this->foreignMappings as $mapping) {
            $tableName = $mapping['table'];
            $oldColumn = $mapping['column'];
            $newColumn = $oldColumn . '_uuid';

            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, $oldColumn) || Schema::hasColumn($tableName, $newColumn)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($oldColumn, $newColumn) {
                $table->uuid($newColumn)->nullable()->after($oldColumn);
            });
        }
    }

    private function populateUuidForeignColumns(): void
    {
        foreach ($this->foreignMappings as $mapping) {
            $tableName = $mapping['table'];
            $column = $mapping['column'];
            $references = $mapping['references'];
            $uuidColumn = $column . '_uuid';

            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, $uuidColumn)) {
                continue;
            }

            DB::statement(
                "UPDATE `{$tableName}` t
                LEFT JOIN `{$references}` r ON t.`{$column}` = r.`id`
                SET t.`{$uuidColumn}` = r.`id_uuid`
                WHERE t.`{$column}` IS NOT NULL"
            );
        }
    }

    private function convertSessionsUserIdToUuid(): void
    {
        if (! Schema::hasTable('sessions') || ! Schema::hasColumn('sessions', 'user_id')) {
            return;
        }

        if (! Schema::hasColumn('sessions', 'user_id_uuid')) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->uuid('user_id_uuid')->nullable()->after('user_id');
            });
        }

        DB::statement("UPDATE `sessions` s LEFT JOIN `users` u ON s.`user_id` = u.`id` SET s.`user_id_uuid` = u.`id_uuid` WHERE s.`user_id` IS NOT NULL");

        $this->dropIndexIfExists('sessions', 'sessions_user_id_index');

        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->renameColumn('user_id_uuid', 'user_id');
        });

        DB::statement('ALTER TABLE `sessions` MODIFY `user_id` CHAR(36) NULL');

        Schema::table('sessions', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    private function convertPersonalAccessTokenableIdToUuid(): void
    {
        if (! Schema::hasTable('personal_access_tokens') || ! Schema::hasColumn('personal_access_tokens', 'tokenable_id')) {
            return;
        }

        // Keep personal_access_tokens.id unchanged. Only convert tokenable_id references
        // so Sanctum continues to work after domain models switch to UUID primary keys.

        if (! Schema::hasColumn('personal_access_tokens', 'tokenable_id_uuid')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->uuid('tokenable_id_uuid')->nullable()->after('tokenable_id');
            });
        }

        DB::statement("UPDATE `personal_access_tokens` p LEFT JOIN `users` u ON p.`tokenable_type` = 'App\\\\Models\\\\User' AND p.`tokenable_id` = u.`id` SET p.`tokenable_id_uuid` = u.`id_uuid` WHERE p.`tokenable_type` = 'App\\\\Models\\\\User'");
        DB::statement("UPDATE `personal_access_tokens` p LEFT JOIN `kiosks` k ON p.`tokenable_type` = 'App\\\\Models\\\\Kiosk' AND p.`tokenable_id` = k.`id` SET p.`tokenable_id_uuid` = k.`id_uuid` WHERE p.`tokenable_type` = 'App\\\\Models\\\\Kiosk'");

        // Preserve legacy/non-domain token rows by storing their current id value as string.
        DB::statement("UPDATE `personal_access_tokens` SET `tokenable_id_uuid` = CAST(`tokenable_id` AS CHAR(36)) WHERE `tokenable_id_uuid` IS NULL");

        $this->dropIndexIfExists('personal_access_tokens', 'personal_access_tokens_tokenable_type_tokenable_id_index');

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropColumn('tokenable_id');
        });

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->renameColumn('tokenable_id_uuid', 'tokenable_id');
        });

        DB::statement('ALTER TABLE `personal_access_tokens` MODIFY `tokenable_id` CHAR(36) NOT NULL');

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->index(['tokenable_type', 'tokenable_id']);
        });
    }

    private function dropExistingForeignKeys(): void
    {
        foreach ($this->foreignMappings as $mapping) {
            $this->dropForeignKeyByColumn($mapping['table'], $mapping['column']);
        }
    }

    private function swapPrimaryKeys(): void
    {
        foreach ($this->domainTables as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'id_uuid')) {
                continue;
            }

            if (Schema::hasColumn($tableName, 'legacy_id')) {
                continue;
            }

            // Rename the auto-incrementing id first to remove AUTO_INCREMENT safely,
            // then switch the primary key to UUID.
            DB::statement("ALTER TABLE `{$tableName}` CHANGE `id` `legacy_id` BIGINT UNSIGNED NOT NULL");
            DB::statement("ALTER TABLE `{$tableName}` CHANGE `id_uuid` `id` CHAR(36) NOT NULL");
            DB::statement("ALTER TABLE `{$tableName}` DROP PRIMARY KEY, ADD PRIMARY KEY (`id`)");

            DB::statement("ALTER TABLE `{$tableName}` MODIFY `legacy_id` BIGINT UNSIGNED NULL");

            $legacyIndexName = "{$tableName}_legacy_id_unique";
            if (! $this->hasIndex($tableName, $legacyIndexName)) {
                DB::statement("ALTER TABLE `{$tableName}` ADD UNIQUE `{$legacyIndexName}` (`legacy_id`)");
            }
        }
    }

    private function swapForeignKeys(): void
    {
        foreach ($this->foreignMappings as $mapping) {
            $tableName = $mapping['table'];
            $column = $mapping['column'];
            $uuidColumn = $column . '_uuid';
            $nullable = $mapping['nullable'];

            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, $uuidColumn)) {
                continue;
            }

            if (Schema::hasColumn($tableName, $column)) {
                Schema::table($tableName, function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }

            Schema::table($tableName, function (Blueprint $table) use ($column, $uuidColumn) {
                $table->renameColumn($uuidColumn, $column);
            });

            DB::statement(
                "ALTER TABLE `{$tableName}` MODIFY `{$column}` CHAR(36) " . ($nullable ? 'NULL' : 'NOT NULL')
            );
        }
    }

    private function rebuildForeignKeys(): void
    {
        foreach ($this->foreignMappings as $mapping) {
            $tableName = $mapping['table'];
            $column = $mapping['column'];
            $references = $mapping['references'];
            $onDelete = $mapping['onDelete'];

            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, $column)) {
                continue;
            }

            $constraintName = "{$tableName}_{$column}_foreign";

            DB::statement(
                "ALTER TABLE `{$tableName}` ADD CONSTRAINT `{$constraintName}` FOREIGN KEY (`{$column}`) REFERENCES `{$references}`(`id`) ON DELETE {$onDelete}"
            );
        }
    }

    private function rebuildAdminLocationsUnique(): void
    {
        if (! Schema::hasTable('admin_locations') || ! Schema::hasColumn('admin_locations', 'user_id') || ! Schema::hasColumn('admin_locations', 'location_id')) {
            return;
        }

        $indexName = 'admin_locations_user_id_location_id_unique';

        if (! $this->hasIndex('admin_locations', $indexName)) {
            Schema::table('admin_locations', function (Blueprint $table) {
                $table->unique(['user_id', 'location_id']);
            });
        }
    }

    private function dropForeignKeyByColumn(string $tableName, string $column): void
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, $column)) {
            return;
        }

        $constraints = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->select('CONSTRAINT_NAME')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->pluck('CONSTRAINT_NAME')
            ->unique();

        foreach ($constraints as $constraintName) {
            DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$constraintName}`");
        }
    }

    private function dropIndexIfExists(string $tableName, string $indexName): void
    {
        if (! $this->hasIndex($tableName, $indexName)) {
            return;
        }

        DB::statement("ALTER TABLE `{$tableName}` DROP INDEX `{$indexName}`");
    }

    private function hasIndex(string $tableName, string $indexName): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('INDEX_NAME', $indexName)
            ->exists();
    }
};
