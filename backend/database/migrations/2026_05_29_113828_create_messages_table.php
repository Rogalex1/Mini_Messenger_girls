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
    Schema::create('messages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
        $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
        $table->text('message')->nullable();
        $table->enum('type', ['text', 'image', 'video', 'audio', 'file', 'gif'])->default('text');
        $table->string('file_url')->nullable();
        $table->boolean('is_seen')->default(false);
        $table->timestamp('seen_at')->nullable();
        $table->boolean('is_single_view')->default(false);
        $table->softDeletes();
        $table->timestamps();
    });

    // Ajouter la FK différée pour last_message_id
    Schema::table('conversations', function (Blueprint $table) {
        $table->foreign('last_message_id')->references('id')->on('messages')->onDelete('set null');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
