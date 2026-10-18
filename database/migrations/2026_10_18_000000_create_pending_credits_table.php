<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Solde en attente : sommes annoncées au client mais pas encore créditées. À la validation,
        // le montant est ajouté au solde du compte (un mouvement de crédit est alors créé).
        Schema::create('pending_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('currency', 10)->default('EUR');
            $table->string('label', 120)->nullable();
            $table->string('note', 255)->nullable();
            $table->string('status', 20)->default('pending'); // pending | validated | cancelled
            $table->foreignId('movement_id')->nullable()->constrained('account_movements')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_credits');
    }
};
