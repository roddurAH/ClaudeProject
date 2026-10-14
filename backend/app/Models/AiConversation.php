<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One plant problem discussed with the AI doctor (photo + follow-ups). */
class AiConversation extends Model
{
    protected $fillable = ['user_id', 'crop_id', 'district_id', 'language'];

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

    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class)->orderBy('id');
    }

    public function escalatedQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'escalated_question_id');
    }
}
