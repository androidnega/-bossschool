<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->restrictOnDelete();
            $table->foreignId('term_id')->constrained('terms')->restrictOnDelete();
            $table->string('fee_type', 128);
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->index(['tenant_id', 'class_id', 'term_id']);
            $table->index(['tenant_id', 'fee_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
