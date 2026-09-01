<?php
return [
    'enabled' => env('FIREBASE_ENABLED', false),
    // path ke service-account json (storage/app/firebase.json) atau isi json base64
    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase.json')),
    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET', env('FIREBASE_PROJECT_ID') ? env('FIREBASE_PROJECT_ID').'.appspot.com' : null),
    'project_id' => env('FIREBASE_PROJECT_ID'),
];
