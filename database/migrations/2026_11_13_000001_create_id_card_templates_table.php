<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('id_card_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->default('student');
            $table->json('design')->nullable();
            $table->timestamps();

            $table->unique(['institute_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('id_card_templates');
    }
};
