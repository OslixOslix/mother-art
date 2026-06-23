<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_admin_user_when_password_is_configured(): void
    {
        config([
            'gallery.admin_email' => 'elena-burkaltseva@yandex.ru',
            'gallery.admin_name' => 'Елена Буркальцева',
            'gallery.admin_password' => 'ART1949ART1949',
        ]);

        $this->seed(DatabaseSeeder::class);

        $user = User::query()->where('email', 'elena-burkaltseva@yandex.ru')->first();

        $this->assertNotNull($user);
        $this->assertSame('Елена Буркальцева', $user->name);
        $this->assertTrue(Hash::check('ART1949ART1949', $user->password));
    }

    public function test_database_seeder_skips_admin_user_when_password_is_missing(): void
    {
        config([
            'gallery.admin_email' => 'elena-burkaltseva@yandex.ru',
            'gallery.admin_name' => 'Елена Буркальцева',
            'gallery.admin_password' => null,
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseMissing('users', [
            'email' => 'elena-burkaltseva@yandex.ru',
        ]);
    }
}
