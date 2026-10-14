<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMessage extends Model
{
    protected $fillable = [
        'role', 'text', 'image_path', 'context', 'result', 'model', 'input_tokens', 'output_tokens',
        'cache_read_tokens', 'cache_write_tokens', 'stop_reason', 'latency_ms',
    ];

    protected function casts(): array
    {
        return ['result' => 'array'];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'ai_conversation_id');
    }

    public function imageUrl(): ?string
    {
        return Media::url($this->image_path);
    }

    /** Estimated AI spend in USD since a moment (summed per model). */
    public static function costSince(\DateTimeInterface $from): float
    {
        return static::query()
            ->where('role', 'assistant')
            ->where('created_at', '>=', $from)
            ->selectRaw('model, SUM(input_tokens) as input_tokens, SUM(output_tokens) as output_tokens, SUM(cache_read_tokens) as cache_read_tokens, SUM(cache_write_tokens) as cache_write_tokens')
            ->groupBy('model')
            ->get()
            ->sum(fn (self $row) => $row->estimatedCost());
    }

    /** Estimated cost in USD from the token counts (config krishi.ai.prices). */
    public function estimatedCost(): float
    {
        $price = config('krishi.ai.prices.'.$this->model);
        if (! $price) {
            return 0.0;
        }

        return ($this->input_tokens * $price['input'] + $this->output_tokens * $price['output']
            + $this->cache_read_tokens * $price['cache_read'] + $this->cache_write_tokens * $price['cache_write']) / 1_000_000;
    }
}
