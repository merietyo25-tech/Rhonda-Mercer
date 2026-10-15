<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Membres présentés sur la page « À propos » (direction + équipe) : gérés depuis l'administration. */
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('title', 120);
            $table->text('bio')->nullable();
            $table->string('photo_path')->nullable();
            $table->boolean('is_leader')->default(false);   // mis en avant dans le bloc « Notre direction »
            $table->boolean('is_visible')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
