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
    Schema::create('calls', function (Blueprint $table) {
        $table->id();
        $table->foreignId('caller_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
        $table->enum('type', ['audio', 'video'])->default('audio');
        $table->enum('status', ['ringing', 'accepted', 'declined', 'ended'])->default('ringing');
        $table->timestamp('started_at')->nullable();
        $table->timestamp('ended_at')->nullable();
        $table->timestamp('created_at')->useCurrent();
    });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
