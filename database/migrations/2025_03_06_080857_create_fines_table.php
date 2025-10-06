<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fines', function (Blueprint $table) {
            $table->id(); // fine ID is still auto-increment
            $table->uuid('driver_id'); // UUID for driver
            $table->unsignedBigInteger('officer_id'); // integer for officer (user)
            $table->string('violation');
            $table->decimal('amount', 10, 2);
            $table->date('date');
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->timestamps();

            // Foreign key references
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('officer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
};
