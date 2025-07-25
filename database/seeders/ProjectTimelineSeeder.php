<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\ProjectTimeline;
use Carbon\Carbon;

class ProjectTimelineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::all();

        foreach ($projects as $project) {
            // Create multiple timeline milestones for each project
            $milestones = [
                [
                    'milestone' => 'Survey Lokasi',
                    'description' => 'Melakukan survey lokasi untuk persiapan proyek',
                    'planned_date' => $project->start_date ? $project->start_date->copy()->addDays(1) : Carbon::now()->addDays(1),
                    'actual_date' => null,
                    'status' => 'completed',
                    'progress_percentage' => 100,
                ],
                [
                    'milestone' => 'Persiapan Material',
                    'description' => 'Mempersiapkan material yang dibutuhkan untuk proyek',
                    'planned_date' => $project->start_date ? $project->start_date->copy()->addDays(3) : Carbon::now()->addDays(3),
                    'actual_date' => null,
                    'status' => 'in_progress',
                    'progress_percentage' => 75,
                ],
                [
                    'milestone' => 'Instalasi Perangkat',
                    'description' => 'Melakukan instalasi perangkat telekomunikasi',
                    'planned_date' => $project->start_date ? $project->start_date->copy()->addDays(7) : Carbon::now()->addDays(7),
                    'actual_date' => null,
                    'status' => 'in_progress',
                    'progress_percentage' => 45,
                ],
                [
                    'milestone' => 'Testing & Commissioning',
                    'description' => 'Melakukan testing dan commissioning sistem',
                    'planned_date' => $project->start_date ? $project->start_date->copy()->addDays(12) : Carbon::now()->addDays(12),
                    'actual_date' => null,
                    'status' => 'planned',
                    'progress_percentage' => 0,
                ],
                [
                    'milestone' => 'Handover',
                    'description' => 'Serah terima proyek kepada klien',
                    'planned_date' => $project->end_date ?: ($project->start_date ? $project->start_date->copy()->addDays(15) : Carbon::now()->addDays(15)),
                    'actual_date' => null,
                    'status' => 'planned',
                    'progress_percentage' => 0,
                ],
            ];

            foreach ($milestones as $milestone) {
                ProjectTimeline::create([
                    'project_id' => $project->id,
                    'milestone' => $milestone['milestone'],
                    'description' => $milestone['description'],
                    'planned_date' => $milestone['planned_date'],
                    'actual_date' => $milestone['actual_date'],
                    'status' => $milestone['status'],
                    'progress_percentage' => $milestone['progress_percentage'],
                ]);
            }
        }
    }
}
