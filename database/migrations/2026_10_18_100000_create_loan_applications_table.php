<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Demandes de prêt faites par les clients depuis leur application ; l'administration les accepte ou les refuse.
        Schema::create('loan_applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->nullable()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->unsignedSmallInteger('duration_months');
            $table->string('purpose', 20);
            $table->text('message')->nullable();
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->decimal('monthly_payment', 14, 2)->default(0);
            $table->decimal('total_cost', 14, 2)->default(0);
            $table->string('currency', 10)->default('EUR');
            $table->string('status', 20)->default('pending'); // pending | approved | rejected | cancelled
            $table->text('admin_note')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_applications');
    }
};
