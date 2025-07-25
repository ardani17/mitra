<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectBilling;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectBilling>
 */
class ProjectBillingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $billingDate = $this->faker->dateTimeBetween('-6 months', 'now');
        $dueDate = (clone $billingDate)->modify('+30 days');
        
        return [
            'project_id' => Project::factory(),
            'billing_date' => $billingDate,
            'due_date' => $dueDate,
            'amount' => $this->faker->numberBetween(50000000, 500000000), // 50 juta - 500 juta
            'status' => $this->faker->randomElement(['draft', 'sent', 'paid', 'overdue']),
            'notes' => $this->faker->optional()->paragraph(),
            'paid_date' => $this->faker->optional()->dateTimeBetween($billingDate, 'now'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the billing is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }

    /**
     * Indicate that the billing is sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
        ]);
    }

    /**
     * Indicate that the billing is draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Indicate that the billing is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'due_date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }
}
