<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            if (!Schema::hasColumn('locations', 'tenant_id')) {
                $table->foreignId('tenant_id')
                    ->constrained('tenants')
                    ->cascadeOnDelete()
                    ->after('id');
            }

            if (!Schema::hasColumn('locations', 'type')) {
                $table->string('type')->nullable()->after('name');
            }

            if (!Schema::hasColumn('locations', 'address_line1')) {
                $table->string('address_line1')->nullable()->after('type');
            }

            if (!Schema::hasColumn('locations', 'city')) {
                $table->string('city')->nullable()->after('address_line1');
            }

            if (!Schema::hasColumn('locations', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
        });

        if (Schema::hasColumn('locations', 'slug')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            if (Schema::hasColumn('locations', 'state')) {
                $table->dropColumn('state');
            }

            if (Schema::hasColumn('locations', 'city')) {
                $table->dropColumn('city');
            }

            if (Schema::hasColumn('locations', 'address_line1')) {
                $table->dropColumn('address_line1');
            }

            if (Schema::hasColumn('locations', 'type')) {
                $table->dropColumn('type');
            }

            if (Schema::hasColumn('locations', 'tenant_id')) {
                $table->dropConstrainedForeignId('tenant_id');
            }

            if (!Schema::hasColumn('locations', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
        });
    }
};
