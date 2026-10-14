<?php

namespace Database\Factories;

use App\Enums\QuestionStatus;
use App\Enums\Role;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'body' => fake()->sentence(12),
        ];
    }

    public function answered(): static
    {
        return $this->afterCreating(function (Question $question) {
            $expert = User::factory()->staff(Role::Expert)->create();
            $question->answers()->create(['user_id' => $expert->id, 'body' => 'Do this and that.']);
            $question->forceFill(['status' => QuestionStatus::Answered, 'answered_at' => now()])->save();
        });
    }
}
