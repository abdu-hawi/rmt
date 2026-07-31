<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::create([
            'slug' => 'enterprise-software',
            'name_en' => 'Enterprise Software',
            'name_ar' => 'برامج المؤسسات',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Category::create([
            'slug' => 'ecommerce-solutions',
            'name_en' => 'E-Commerce Solutions',
            'name_ar' => 'حلول التجارة الإلكترونية',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Category::create([
            'slug' => 'hr-systems',
            'name_en' => 'HR Systems',
            'name_ar' => 'أنظمة الموارد البشرية',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        Category::create([
            'slug' => 'learning-management',
            'name_en' => 'Learning Management',
            'name_ar' => 'إدارة التعلم',
            'is_active' => true,
            'sort_order' => 4,
        ]);
    }
}
