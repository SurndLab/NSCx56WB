<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publisher_id')->constrained('publishers')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('book_name', 255);
            $table->text('book_description');
            $table->string('book_author', 255);

            $table->char('isbn13_digits', 13)->unique()->index();
            $table->string('isbn13_hyphenated', 32)->unique();
            $table->char('isbn_prefix', 3);
            $table->string('registration_group', 8);
            $table->string('publisher_code', 12);
            $table->string('publication_code', 16);
            $table->char('check_digit', 1);

            $table->boolean('is_hidden')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['publisher_id', 'is_hidden']);
            $table->index('isbn13_hyphenated');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
