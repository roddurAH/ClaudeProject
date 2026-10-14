<?php

namespace App\Models;

use App\Enums\QuestionStatus;
use App\Support\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'crop_id', 'district_id', 'body', 'image_path'];

    protected $attributes = [
        'status' => 'pending',
        'is_public' => false,
    ];

    protected function casts(): array
    {
        return [
            'status' => QuestionStatus::class,
            'is_public' => 'boolean',
            'answered_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class)->oldest();
    }

    public function imageUrl(): ?string
    {
        return Media::url($this->image_path);
    }
}
