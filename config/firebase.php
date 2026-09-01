<?php
return [
    'enabled' => env('FIREBASE_ENABLED', false),
    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase.json')),
    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET', env('FIREBASE_PROJECT_ID') ? env('FIREBASE_PROJECT_ID').'.appspot.com' : null),
    'project_id' => env('FIREBASE_PROJECT_ID', 'data01-c6d26'),
    'database_url' => env('FIREBASE_DATABASE_URL', 'https://data01-c6d26-default-rtdb.firebaseio.com'),
];
