<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for Global Audit Trail
 * 
 * This table stores all auditable events (create, update, delete, forceDelete)
 * across the system using polymorphic relationships.
 * 
 * The audit record persists even after the auditable entity is deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // User who made the change (nullable for system/background jobs)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            
            // Event type: created, updated, deleted, forceDeleted
            $table->string('event', 20);
            
            // Polymorphic relationship (NOT a foreign key - allows orphaned audits)
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            
            // Data delta - JSON storage for changed values only
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            // Request context
            $table->string('url', 2048)->nullable();
            $table->string('ip_address', 45)->nullable(); // IPv6 max length
            $table->string('user_agent')->nullable();
            
            // Only created_at (no updated_at - audit logs are immutable)
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes for common queries
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('user_id');
            $table->index('event');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
