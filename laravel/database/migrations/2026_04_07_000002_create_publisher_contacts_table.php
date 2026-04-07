<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('publisher_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publisher_id')->constrained('publishers')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('contact_name', 255);
            $table->string('contact_phone', 32);
            $table->string('contact_email', 255);
            $table->timestamps();

            $table->index('publisher_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publisher_contacts');
    }
};
