<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'publisher_id',
        'book_name',
        'book_description',
        'book_author',
        'isbn13_digits',
        'isbn13_hyphenated',
        'isbn_prefix',
        'registration_group',
        'publisher_code',
        'publication_code',
        'check_digit',
        'is_hidden',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    public function scopePublicVisible($query)
    {
        return $query
            ->where('is_hidden', false)
            ->whereHas('publisher', fn ($q) => $q->where('is_active', true));
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(BookImage::class)->orderBy('sort_order');
    }

    public function coverImage(): HasMany
    {
        return $this->hasMany(BookImage::class)->orderBy('sort_order')->limit(1);
    }
}
