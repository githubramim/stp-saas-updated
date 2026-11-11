<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('channel')->default('whatsapp');
            $table->string('direction')->default('outbound');
            $table->string('status')->nullable();
            $table->text('body');
            $table->text('provider_response')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['lead_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_messages');
    }
};
