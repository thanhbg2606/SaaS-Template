<?php

namespace Tests\Feature;

use Tests\TestCase;

class TemplateSmokeTest extends TestCase
{
    /** GET / returns 200 for guests */
    public function test_home_page_is_reachable(): void
    {
        $response = $this->get('/');

        $response->assertSuccessful();
    }

    /** GET /login returns 200 for guests */
    public function test_login_page_is_reachable(): void
    {
        $response = $this->get('/login');

        $response->assertSuccessful();
    }

    /** GET /dashboard redirects unauthenticated users to /login */
    public function test_dashboard_redirects_guests_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirectToRoute('login');
    }
}
