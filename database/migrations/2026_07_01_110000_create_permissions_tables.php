<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catalogue of every permission the application knows about.
        // Permissions are platform-wide constants (not per-tenant).
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();        // e.g. "results.manage"
            $table->string('module', 64)->index();  // e.g. "results"
            $table->string('label');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Default role → permission map. SuperAdmin manages these from a
        // platform UI; school Admins do NOT touch this table.
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role', 32)->index();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role', 'permission_id']);
        });

        // Per-user grant. Tenant Admins can grant a permission to one user
        // (e.g. give a single teacher the Librarian permission set). Removes
        // are by deleting the row; we don't track explicit revokes here.
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('granted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
    }
};
