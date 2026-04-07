<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publisher_id')->nullable()->constrained('publishers')->nullOnDelete()->cascadeOnUpdate();
            $table->string('username', 64)->unique();
            $table->string('password');
            $table->string('display_name', 255);
            $table->enum('role', [User::ROLE_SUPER_ADMIN, User::ROLE_PUBLISHER_ADMIN]);
            $table->boolean('is_active')->default(true)->index();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['publisher_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
