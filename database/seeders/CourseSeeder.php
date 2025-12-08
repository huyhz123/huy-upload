<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            [
                'name' => 'Mobile Phone Repair Masterclass',
                'slug' => 'mobile-repair-masterclass',
                'description' => 'Complete course on mobile phone repair from beginner to advanced level',
                'short_description' => 'Learn professional phone repair skills',
                'price' => 199.00,
                'category' => 'Technical',
                'curriculum' => json_encode([
                    'Introduction to Mobile Repair',
                    'Tools and Equipment',
                    'Screen Replacement',
                    'Battery Replacement',
                    'Software Troubleshooting',
                    'Advanced Repairs',
                ]),
                'duration_hours' => 12,
                'level' => 'beginner',
                'status' => 'active',
                'is_featured' => true,
                'enrolled_count' => 156,
                'rating' => 4.8,
            ],
            [
                'name' => 'Advanced Phone Unlocking Techniques',
                'slug' => 'advanced-phone-unlocking',
                'description' => 'Master phone unlocking using professional tools and techniques',
                'short_description' => 'Professional phone unlocking course',
                'price' => 149.00,
                'category' => 'Advanced',
                'curriculum' => json_encode([
                    'Understanding Network Locks',
                    'Using Unlock Tools',
                    'API Integration',
                    'Troubleshooting',
                ]),
                'duration_hours' => 8,
                'level' => 'intermediate',
                'status' => 'active',
                'is_featured' => false,
                'enrolled_count' => 89,
                'rating' => 4.6,
            ],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
