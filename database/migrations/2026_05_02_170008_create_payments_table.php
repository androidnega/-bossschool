<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['momo', 'cash', 'bank']);
            $table->string('reference')->nullable();
            $table->date('date');
            $table->string('receipt_id')->nullable()->index();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('student_id');
            $table->index(['tenant_id', 'student_id']);
            $table->index(['tenant_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
