<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * School Setup Templates — platform-level catalogue.
 *
 * Templates are BLUEPRINTS that the onboarding flow copies into a real
 * tenant's classes / subjects / terms / fees. They are NOT tenant-scoped
 * and never carry tenant_id.
 *
 * Hierarchy:
 *   school_templates
 *     └─ template_levels   (KG, Lower Primary, Upper Primary, JHS …)
 *     │    └─ template_classes  (KG 1, Primary 3, JHS 2 …)
 *     ├─ template_subjects (anchored to template OR level OR class)
 *     ├─ template_terms    (Term 1/2/3)
 *     └─ template_fee_items (Tuition Fee, PTA Dues …, amount nullable)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('school_templates')) {
            Schema::create('school_templates', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 128);
                $table->string('code', 64)->unique();
                $table->text('description')->nullable();
                $table->string('country', 64)->default('GH');
                $table->string('curriculum_label', 128)->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['is_active', 'sort_order']);
                $table->index('country');
            });
        }

        if (! Schema::hasTable('template_levels')) {
            Schema::create('template_levels', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('school_template_id')->constrained('school_templates')->cascadeOnDelete();
                $table->string('name', 64);
                $table->string('code', 32);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->boolean('is_optional')->default(false);
                $table->timestamps();

                $table->unique(['school_template_id', 'code']);
                $table->index(['school_template_id', 'sort_order']);
            });
        }

        if (! Schema::hasTable('template_classes')) {
            Schema::create('template_classes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('school_template_id')->constrained('school_templates')->cascadeOnDelete();
                $table->foreignId('template_level_id')->nullable()->constrained('template_levels')->cascadeOnDelete();
                $table->string('name', 64);
                $table->string('short_name', 32)->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['school_template_id', 'template_level_id']);
                $table->index(['school_template_id', 'sort_order']);
            });
        }

        if (! Schema::hasTable('template_subjects')) {
            Schema::create('template_subjects', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('school_template_id')->constrained('school_templates')->cascadeOnDelete();
                $table->foreignId('template_level_id')->nullable()->constrained('template_levels')->cascadeOnDelete();
                $table->foreignId('template_class_id')->nullable()->constrained('template_classes')->cascadeOnDelete();
                $table->string('name', 96);
                $table->string('short_name', 32)->nullable();
                $table->string('code', 32)->nullable();
                $table->boolean('is_core')->default(true);
                $table->boolean('is_editable')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['school_template_id', 'template_level_id']);
                $table->index(['school_template_id', 'template_class_id']);
                $table->index(['school_template_id', 'sort_order']);
            });
        }

        if (! Schema::hasTable('template_terms')) {
            Schema::create('template_terms', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('school_template_id')->constrained('school_templates')->cascadeOnDelete();
                $table->string('name', 64);
                $table->string('short_name', 32)->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->boolean('is_active_default')->default(false);
                $table->timestamps();

                $table->unique(['school_template_id', 'name']);
                $table->index(['school_template_id', 'sort_order']);
            });
        }

        if (! Schema::hasTable('template_fee_items')) {
            Schema::create('template_fee_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('school_template_id')->constrained('school_templates')->cascadeOnDelete();
                $table->string('name', 96);
                $table->text('description')->nullable();
                $table->decimal('amount', 14, 2)->nullable();
                $table->boolean('is_optional')->default(false);
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['school_template_id', 'name']);
                $table->index(['school_template_id', 'sort_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('template_fee_items');
        Schema::dropIfExists('template_terms');
        Schema::dropIfExists('template_subjects');
        Schema::dropIfExists('template_classes');
        Schema::dropIfExists('template_levels');
        Schema::dropIfExists('school_templates');
    }
};
