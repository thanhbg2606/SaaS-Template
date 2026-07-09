<?php

return [
    'brand' => [
        'name' => env('PRODUCT_BRAND_NAME', config('app.name', 'SaaS Template')),
        'tagline' => env('PRODUCT_BRAND_TAGLINE', 'A reusable SaaS starter for product teams'),
        'primary_color' => env('PRODUCT_PRIMARY_COLOR', '#2563eb'),
        'support_email' => env('PRODUCT_SUPPORT_EMAIL', 'support@example.com'),
    ],

    'features' => [
        'demo_seed_data' => env('PRODUCT_DEMO_SEED_DATA', true),
        'projects_module' => env('PRODUCT_FEATURE_PROJECTS', true),
    ],

    'plans' => [
        [
            'name' => 'starter',
            'price_cents' => 0,
            'limits' => [
                'members' => 5,
                'projects' => 3,
            ],
        ],
    ],

    'onboarding' => [
        'activation_event' => 'product.project.created',
        'seed_demo_data' => env('PRODUCT_DEMO_SEED_DATA', true),
    ],
];
