<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrowed_items', function (Blueprint $table) {
            $table->id();
            $table->string('borrower_name');
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->date('borrowed_date');
            $table->integer('quantity');
            $table->enum('status', ['pending','returned']);
            $table->dateTime('return_date')->nullable();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrowed_items');
    }
};
