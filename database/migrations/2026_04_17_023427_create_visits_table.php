<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignId('visitor_id')->constrained('visitors')->cascadeOnDelete();
            $table->foreignId('host_id')->nullable()->constrained('hosts')->nullOnDelete();
            $table->foreignId('visit_type_id')->constrained('visit_types')->cascadeOnDelete();

            $table->string('purpose')->nullable();
            $table->string('status')->default('checked_in');
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->foreignId('check_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('check_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('qr_code')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
