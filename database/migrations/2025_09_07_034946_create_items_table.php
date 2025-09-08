<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')           // Add this
                  ->constrained('users')           // references 'id' in users
                  ->onDelete('cascade');          // delete items if user deleted
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
