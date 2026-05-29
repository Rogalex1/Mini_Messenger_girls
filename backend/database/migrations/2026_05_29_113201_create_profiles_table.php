<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('profiles', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')
              ->unique()               // one-to-one strict
              ->constrained()
              ->onDelete('cascade');
        $table->string('first_name', 100)->nullable();
        $table->string('last_name', 100)->nullable();
        $table->string('profile_photo')->nullable();
        $table->string('cover_photo')->nullable();
        $table->text('bio')->nullable();
        $table->enum('gender', ['male', 'female', 'other'])->nullable();
        $table->date('birth_date')->nullable();
        $table->string('country', 100)->nullable();
        $table->string('city', 100)->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
