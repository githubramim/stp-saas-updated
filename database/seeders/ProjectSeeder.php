<?php

namespace Database\Seeders;

use App\Models\Institute;
use App\Models\Project;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Student;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $defaultProjects = ['ASSES', 'NHRDF', 'ISEC'];

        foreach (Institute::all() as $institute) {
            foreach ($defaultProjects as $i => $name) {
                $project = Project::firstOrCreate(
                    ['institute_id' => $institute->id, 'code' => $name],
                    [
                        'name' => $name,
                        'status' => 'active',
                    ]
                );

                // Assign existing orphan records to the first project
                if ($i === 0) {
                    Course::where('institute_id', $institute->id)->whereNull('project_id')->update(['project_id' => $project->id]);
                    Batch::where('institute_id', $institute->id)->whereNull('project_id')->update(['project_id' => $project->id]);
                    Student::where('institute_id', $institute->id)->whereNull('project_id')->update(['project_id' => $project->id]);
                }
            }
        }
    }
}
