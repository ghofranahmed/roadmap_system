<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Roadmap;
use App\Models\LearningUnit;
use App\Models\Lesson;
use App\Models\SubLesson;
use App\Models\Resource;
use App\Models\RoadmapEnrollment;
use App\Models\LessonTracking;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Admin
        User::factory()->admin()->create([
            'username' => 'admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        // 2) Users عاديين
        $users = User::factory()->count(40)->create();

        // 3) Roadmaps + Units + Lessons + SubLessons + Resources
        $roadmaps = Roadmap::factory()->count(8)->create();

        foreach ($roadmaps as $roadmap) {
            // Learning Units
            $units = LearningUnit::factory()
                ->count(rand(4, 8))
                ->create(['roadmap_id' => $roadmap->id])
                ->sortBy('position')
                ->values();

            foreach ($units as $i => $unit) {
                // ضمان ترتيب position بدون تكرار كبير
                $unit->update(['position' => $i + 1]);

                // Lessons
                $lessons = Lesson::factory()
                    ->count(rand(3, 7))
                    ->create(['learning_unit_id' => $unit->id])
                    ->sortBy('position')
                    ->values();

                foreach ($lessons as $j => $lesson) {
                    $lesson->update(['position' => $j + 1]);

                    // SubLessons
                    $subLessons = SubLesson::factory()
                        ->count(rand(2, 6))
                        ->create(['lesson_id' => $lesson->id])
                        ->sortBy('position')
                        ->values();

                    foreach ($subLessons as $k => $sub) {
                        $sub->update(['position' => $k + 1]);

                        // Resources
                        Resource::factory()
                            ->count(rand(1, 4))
                            ->create(['sub_lesson_id' => $sub->id]);
                    }
                }
            }

            // 4) Enrollments (مع unique user_id+roadmap_id)
            $pickedUsers = $users->random(rand(10, 25));
            foreach ($pickedUsers as $user) {
                RoadmapEnrollment::firstOrCreate(
                    ['user_id' => $user->id, 'roadmap_id' => $roadmap->id],
                    [
                        'started_at' => now()->subDays(rand(1, 60)),
                        'completed_at' => null,
                        'xp_points' => rand(0, 2500),
                        'status' => collect(['active','paused','completed'])->random(),
                    ]
                );
            }
        }

        // 5) Lesson tracking (اختياري قوي للاختبار)
        $allLessons = Lesson::query()->pluck('id');
        foreach ($users->random(20) as $user) {
            foreach ($allLessons->random(min(25, $allLessons->count())) as $lessonId) {
                LessonTracking::firstOrCreate(
                    ['user_id' => $user->id, 'lesson_id' => $lessonId],
                    [
                        'is_complete' => (bool)rand(0, 1),
                        'last_updated_at' => now()->subDays(rand(0, 20)),
                    ]
                );
            }
        }
    }
}
