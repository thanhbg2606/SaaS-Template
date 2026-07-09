<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! config('product.features.demo_seed_data', true)) {
            return;
        }

        $this->seedDemoUser('Template Owner', 'owner@demo.test');
        $this->seedDemoUser('Template Admin', 'admin@demo.test');
        $this->seedDemoUser('Template Member', 'member@demo.test');
        $this->seedDemoUser('Template Viewer', 'viewer@demo.test');
    }

    private function seedDemoUser(string $name, string $email): void
    {
        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'email' => $email,
                'password' => 'password',
            ],
        );
    }
}
