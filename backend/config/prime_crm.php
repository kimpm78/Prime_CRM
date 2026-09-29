<?php

return [
    'admin' => [
        'name' => env('PRIME_CRM_ADMIN_NAME', 'Prime CRM 管理者'),
        'email' => env('PRIME_CRM_ADMIN_EMAIL', 'admin@prime-crm.test'),
        'password' => env('PRIME_CRM_ADMIN_PASSWORD'),
        'department' => env('PRIME_CRM_ADMIN_DEPARTMENT', 'システム管理'),
    ],
];
