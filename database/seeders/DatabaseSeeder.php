<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $categories = [
            'Картины маслом',
            'Гравюры',
            'Керамика',
            'Куклы',
        ];

        foreach ($categories as $index => $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }

        $adminPassword = config('gallery.admin_password');

        if (filled($adminPassword)) {
            User::updateOrCreate(['email' => config('gallery.admin_email')], [
                'name' => config('gallery.admin_name'),
                'password' => Hash::make($adminPassword),
            ]);
        }
    }
}
