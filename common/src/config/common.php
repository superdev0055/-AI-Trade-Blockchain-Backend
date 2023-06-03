<?php

return [
    'perPageAllow' =>[10, 20, 50, 100],
    'responseDoNotWrap' => [
//        '/api/app/version/check',
    ],
    'iSeedBackupList' => [
        'admins',
        'sys_permissions',
        'sys_roles',
        'sys_role_has_permissions',
        'sys_model_has_roles',
        'personal_access_tokens',
    ],
    'docs' => [
        'foldersSubTitleConfig' => [
            'Admin' => 'Admin',
        ]
    ],
    'skipLogPathInfo' => [
        '/api/admin/auth/me'
    ]
];
