<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel App Developer MCP Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for the Laravel App Developer MCP Server.
    | You can customize various aspects of the MCP server behavior here.
    |
    */

    'mcp' => [
        /*
        |--------------------------------------------------------------------------
        | Tools Configuration
        |--------------------------------------------------------------------------
        |
        | Configure which tools are available in the MCP server.
        |
        */
        'tools' => [
            'exclude' => [
                // Add tool class names here to exclude them
                // Example: 'StafeGroup\LaravelAppDeveloper\Mcp\Tools\SomeToolName'
            ],
            'include' => [
                // Add custom tool class names here to include them
                // These will be added in addition to the auto-discovered tools
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Resources Configuration
        |--------------------------------------------------------------------------
        |
        | Configure which resources are available in the MCP server.
        |
        */
        'resources' => [
            'exclude' => [
                // Add resource class names here to exclude them
            ],
            'include' => [
                // Add custom resource class names here to include them
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Market Research Configuration
        |--------------------------------------------------------------------------
        |
        | Configuration for market research and competitor analysis features.
        |
        */
        'market_research' => [
            'enabled' => true,
            'cache_ttl' => 3600, // Cache results for 1 hour
            'max_competitors' => 10, // Maximum number of competitors to analyze
            'search_engines' => [
                'google' => true,
                'github' => true,
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Development Plan Configuration
        |--------------------------------------------------------------------------
        |
        | Configuration for development plan generation.
        |
        */
        'development_plans' => [
            'output_directory' => base_path('development-plans'),
            'template_style' => 'detailed', // 'detailed' or 'compact'
            'include_estimates' => true,
            'include_dependencies' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | Application Analysis Configuration
        |--------------------------------------------------------------------------
        |
        | Configuration for analyzing Laravel applications.
        |
        */
        'analysis' => [
            'scan_directories' => [
                'app',
                'resources/views',
                'routes',
                'database/migrations',
                'config',
            ],
            'ignore_patterns' => [
                '*/vendor/*',
                '*/node_modules/*',
                '*/storage/*',
                '*/.git/*',
            ],
            'extract_features' => [
                'models' => true,
                'controllers' => true,
                'routes' => true,
                'views' => true,
                'middleware' => true,
                'jobs' => true,
                'events' => true,
                'listeners' => true,
                'notifications' => true,
                'policies' => true,
                'providers' => true,
                'commands' => true,
                'migrations' => true,
            ],
        ],
    ],
];