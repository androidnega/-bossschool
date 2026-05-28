<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_books', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('author', 191)->nullable();
            $table->string('isbn', 32)->nullable();
            $table->string('category', 64)->nullable();
            $table->unsignedInteger('copies_total')->default(1);
            $table->unsignedInteger('copies_available')->default(1);
            $table->string('shelf_location', 64)->nullable();
            $table->string('status', 16)->default('active'); // active|archived
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'category']);
            $table->index(['tenant_id', 'isbn']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('library_loans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('library_book_id')->constrained('library_books')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();

            $table->date('borrowed_at');
            $table->date('due_at')->nullable();
            $table->date('returned_at')->nullable();
            $table->decimal('fine_amount', 12, 2)->default(0);
            $table->string('status', 16)->default('borrowed'); // borrowed|returned|overdue|lost

            $table->foreignId('issued_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'library_book_id']);
            $table->index(['tenant_id', 'student_id']);
            $table->index(['tenant_id', 'staff_id']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_loans');
        Schema::dropIfExists('library_books');
    }
};
