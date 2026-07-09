<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_respects_the_demo_seed_feature_flag(): void
    {
        config()->set('product.features.demo_seed_data', false);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount(User::class, 0);
    }

    public function test_demo_seeder_can_run_multiple_times_without_creating_duplicate_users(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $emails = User::query()
            ->orderBy('email')
            ->pluck('email')
            ->all();

        $this->assertSame([
            'admin@demo.test',
            'member@demo.test',
            'owner@demo.test',
            'viewer@demo.test',
        ], $emails);
    }
}
