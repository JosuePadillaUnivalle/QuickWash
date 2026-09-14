<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->string('role')->default('estudiante'));
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('type')->default('lavadora');
            $table->unsignedSmallInteger('capacity');
            $table->string('location');
            $table->string('status')->default('disponible');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('machine_id')->constrained()->restrictOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status')->default('pendiente');
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });
        // A unique constraint protects each slot, including simultaneous requests.
        Schema::create('reservation_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained()->restrictOnDelete();
            $table->dateTime('starts_at');
            $table->unique(['machine_id', 'starts_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('reservation_slots');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('machines');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('role'));
    }
};
