<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['konstruksi', 'maintenance', 'other'];
        $statuses = ['planning', 'in_progress', 'completed', 'cancelled'];
        
        $startDate = $this->faker->dateTimeBetween('-2 years', '+1 month');
        $endDate = $this->faker->dateTimeBetween($startDate, '+1 year');
        
        // Generate project code
        $year = date('Y');
        $month = date('m');
        $sequence = str_pad($this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT);
        $code = "PRJ-{$year}-{$month}-{$sequence}";
        
        // Generate values
        $plannedServiceValue = $this->faker->randomFloat(2, 50000000, 500000000); // 50-500 juta
        $plannedMaterialValue = $this->faker->randomFloat(2, 30000000, 300000000); // 30-300 juta
        $plannedTotalValue = $plannedServiceValue + $plannedMaterialValue;
        
        $finalServiceValue = $this->faker->randomFloat(2, $plannedServiceValue * 0.8, $plannedServiceValue * 1.2);
        $finalMaterialValue = $this->faker->randomFloat(2, $plannedMaterialValue * 0.8, $plannedMaterialValue * 1.2);
        $finalTotalValue = $finalServiceValue + $finalMaterialValue;
        
        return [
            'name' => $this->faker->sentence(3),
            'code' => $code,
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement($types),
            'planned_service_value' => $plannedServiceValue,
            'planned_material_value' => $plannedMaterialValue,
            'planned_total_value' => $plannedTotalValue,
            'final_service_value' => $finalServiceValue,
            'final_material_value' => $finalMaterialValue,
            'final_total_value' => $finalTotalValue,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $this->faker->randomElement($statuses),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            'location' => $this->faker->city(),
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }
    
    /**
     * Indicate that the project is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }
    
    /**
     * Indicate that the project is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'on_progress',
        ]);
    }
    
    /**
     * Indicate that the project is on hold.
     */
    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'on_hold',
        ]);
    }
    
    /**
     * Indicate that the project is in planning phase.
     */
    public function planning(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'planning',
        ]);
    }
    
    /**
     * Indicate that the project is draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }
}
