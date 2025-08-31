<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rclone Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for rclone cloud synchronization service
    |
    */

    // Path to rclone binary
    'binary_path' => env('RCLONE_BINARY_PATH', 'rclone'),

    // Remote name configured in rclone
    'remote_name' => env('RCLONE_REMOTE', 'gdrive'),

    // Base path on remote storage
    'remote_path' => env('RCLONE_BASE_PATH', '/project-documents'),

    // Path to rclone config file
    'config_path' => env('RCLONE_CONFIG_PATH', ''),

    // Default sync options
    'sync_options' => [
        '--verbose',
        '--transfers', '4',
        '--checkers', '8',
        '--contimeout', '60s',
        '--timeout', '300s',
        '--retries', '3',
        '--low-level-retries', '10',
        '--stats', '10s',
    ],

    // Exclude patterns for sync
    'exclude_patterns' => [
        '.DS_Store',
        'Thumbs.db',
        '*.tmp',
        '~*',
    ],
];