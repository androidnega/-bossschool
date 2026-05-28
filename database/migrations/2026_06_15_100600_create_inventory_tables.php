<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name', 191);
            $table->string('category', 64)->nullable();
            $table->string('sku', 64)->nullable();
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('reorder_level')->default(0);
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->string('location', 64)->nullable();
            $table->string('status', 16)->default('active'); // active|archived
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'category']);
            $table->index(['tenant_id', 'sku']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('inventory_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->string('movement_type', 16); // receive|issue|adjust|return
            $table->integer('quantity'); // signed: + for receive/return, - for issue, signed for adjust
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->string('reason', 255)->nullable();
            $table->foreignId('performed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('related_student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('related_staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'inventory_item_id']);
            $table->index(['tenant_id', 'movement_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventory_items');
    }
};
