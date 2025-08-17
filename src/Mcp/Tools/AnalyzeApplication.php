<?php

declare(strict_types=1);

namespace StafeGroup\LaravelAppDeveloper\Mcp\Tools;

use Illuminate\Support\Facades\File;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Symfony\Component\Finder\Finder;

#[IsReadOnly]
class AnalyzeApplication extends Tool
{
    public function description(): string
    {
        return 'Analyzes your Laravel application to identify all features, components, and functionality. This tool scans your application code to understand models, controllers, routes, views, jobs, events, middleware, and other Laravel components to create a comprehensive feature inventory.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->boolean('include_code_samples')
            ->description('Include code samples in the analysis output')
            ->optional()
            ->boolean('deep_analysis')
            ->description('Perform deep analysis including method signatures and relationships')
            ->optional()
            ->string('focus_area')
            ->description('Focus analysis on specific area: all, models, controllers, routes, views, middleware, jobs, events, policies, commands')
            ->optional();
    }

    /**
     * @param array<string, mixed> $arguments
     */
    public function handle(array $arguments): ToolResult
    {
        $includeCodeSamples = $arguments['include_code_samples'] ?? false;
        $deepAnalysis = $arguments['deep_analysis'] ?? false;
        $focusArea = $arguments['focus_area'] ?? 'all';

        try {
            $analysis = $this->performAnalysis($includeCodeSamples, $deepAnalysis, $focusArea);
            
            return ToolResult::json([
                'application_info' => [
                    'name' => config('app.name', 'Laravel Application'),
                    'environment' => config('app.env'),
                    'laravel_version' => app()->version(),
                    'php_version' => PHP_VERSION,
                    'analysis_timestamp' => now()->toISOString(),
                ],
                'feature_analysis' => $analysis,
                'summary' => $this->generateSummary($analysis),
            ]);
        } catch (\Exception $e) {
            return ToolResult::error('Failed to analyze application: '.$e->getMessage());
        }
    }

    /**
     * Perform comprehensive application analysis.
     */
    protected function performAnalysis(bool $includeCodeSamples, bool $deepAnalysis, string $focusArea): array
    {
        $analysis = [];
        $scanDirs = config('laravel-app-developer.mcp.analysis.scan_directories', [
            'app', 'resources/views', 'routes', 'database/migrations', 'config'
        ]);

        if ($focusArea === 'all' || $focusArea === 'models') {
            $analysis['models'] = $this->analyzeModels($includeCodeSamples, $deepAnalysis);
        }

        if ($focusArea === 'all' || $focusArea === 'controllers') {
            $analysis['controllers'] = $this->analyzeControllers($includeCodeSamples, $deepAnalysis);
        }

        if ($focusArea === 'all' || $focusArea === 'routes') {
            $analysis['routes'] = $this->analyzeRoutes($includeCodeSamples);
        }

        if ($focusArea === 'all' || $focusArea === 'views') {
            $analysis['views'] = $this->analyzeViews($includeCodeSamples);
        }

        if ($focusArea === 'all' || $focusArea === 'middleware') {
            $analysis['middleware'] = $this->analyzeMiddleware($includeCodeSamples, $deepAnalysis);
        }

        if ($focusArea === 'all' || $focusArea === 'jobs') {
            $analysis['jobs'] = $this->analyzeJobs($includeCodeSamples, $deepAnalysis);
        }

        if ($focusArea === 'all' || $focusArea === 'events') {
            $analysis['events'] = $this->analyzeEvents($includeCodeSamples, $deepAnalysis);
        }

        if ($focusArea === 'all' || $focusArea === 'policies') {
            $analysis['policies'] = $this->analyzePolicies($includeCodeSamples, $deepAnalysis);
        }

        if ($focusArea === 'all' || $focusArea === 'commands') {
            $analysis['commands'] = $this->analyzeCommands($includeCodeSamples, $deepAnalysis);
        }

        if ($focusArea === 'all') {
            $analysis['migrations'] = $this->analyzeMigrations($includeCodeSamples);
            $analysis['config'] = $this->analyzeConfiguration();
            $analysis['packages'] = $this->analyzePackages();
        }

        return $analysis;
    }

    /**
     * Analyze Eloquent models.
     */
    protected function analyzeModels(bool $includeCodeSamples, bool $deepAnalysis): array
    {
        $modelsPath = app_path('Models');
        if (! File::exists($modelsPath)) {
            return ['count' => 0, 'models' => []];
        }

        $finder = new Finder();
        $models = [];

        foreach ($finder->files()->in($modelsPath)->name('*.php') as $file) {
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $namespace = 'App\\Models\\' . $className;

            $modelInfo = [
                'name' => $className,
                'file_path' => $file->getRelativePathname(),
                'namespace' => $namespace,
            ];

            if ($deepAnalysis && class_exists($namespace)) {
                try {
                    $reflection = new \ReflectionClass($namespace);
                    $modelInfo['methods'] = array_map(fn($method) => $method->getName(), $reflection->getMethods(\ReflectionMethod::IS_PUBLIC));
                    $modelInfo['traits'] = array_map(fn($trait) => $trait->getName(), $reflection->getTraits());
                } catch (\Exception $e) {
                    $modelInfo['analysis_error'] = $e->getMessage();
                }
            }

            if ($includeCodeSamples) {
                $content = File::get($file->getRealPath());
                $modelInfo['code_sample'] = $this->extractCodeSample($content, 50);
            }

            $models[] = $modelInfo;
        }

        return [
            'count' => count($models),
            'models' => $models,
        ];
    }

    /**
     * Analyze controllers.
     */
    protected function analyzeControllers(bool $includeCodeSamples, bool $deepAnalysis): array
    {
        $controllersPath = app_path('Http/Controllers');
        if (! File::exists($controllersPath)) {
            return ['count' => 0, 'controllers' => []];
        }

        $finder = new Finder();
        $controllers = [];

        foreach ($finder->files()->in($controllersPath)->name('*.php') as $file) {
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $relativePath = $file->getRelativePathname();
            $namespace = 'App\\Http\\Controllers\\' . str_replace('/', '\\', pathinfo($relativePath, PATHINFO_DIRNAME));
            if ($namespace === 'App\\Http\\Controllers\\.') {
                $namespace = 'App\\Http\\Controllers';
            }
            $fullNamespace = $namespace . '\\' . $className;

            $controllerInfo = [
                'name' => $className,
                'file_path' => $relativePath,
                'namespace' => $fullNamespace,
            ];

            if ($deepAnalysis && class_exists($fullNamespace)) {
                try {
                    $reflection = new \ReflectionClass($fullNamespace);
                    $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
                    $controllerInfo['actions'] = array_filter(
                        array_map(fn($method) => $method->getName(), $methods),
                        fn($method) => !in_array($method, ['__construct', '__call', '__callStatic'])
                    );
                } catch (\Exception $e) {
                    $controllerInfo['analysis_error'] = $e->getMessage();
                }
            }

            if ($includeCodeSamples) {
                $content = File::get($file->getRealPath());
                $controllerInfo['code_sample'] = $this->extractCodeSample($content, 50);
            }

            $controllers[] = $controllerInfo;
        }

        return [
            'count' => count($controllers),
            'controllers' => $controllers,
        ];
    }

    /**
     * Analyze application routes.
     */
    protected function analyzeRoutes(bool $includeCodeSamples): array
    {
        $routes = [];
        
        try {
            $routeCollection = app('router')->getRoutes();
            
            foreach ($routeCollection as $route) {
                $routeInfo = [
                    'method' => implode('|', $route->methods()),
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'action' => $route->getActionName(),
                    'middleware' => $route->middleware(),
                ];

                $routes[] = $routeInfo;
            }
        } catch (\Exception $e) {
            return ['error' => 'Could not analyze routes: ' . $e->getMessage()];
        }

        return [
            'count' => count($routes),
            'routes' => $routes,
        ];
    }

    /**
     * Analyze Blade views.
     */
    protected function analyzeViews(bool $includeCodeSamples): array
    {
        $viewsPath = resource_path('views');
        if (! File::exists($viewsPath)) {
            return ['count' => 0, 'views' => []];
        }

        $finder = new Finder();
        $views = [];

        foreach ($finder->files()->in($viewsPath)->name('*.blade.php') as $file) {
            $viewInfo = [
                'name' => $file->getRelativePathname(),
                'file_path' => $file->getRelativePathname(),
                'size' => $file->getSize(),
            ];

            if ($includeCodeSamples) {
                $content = File::get($file->getRealPath());
                $viewInfo['code_sample'] = $this->extractCodeSample($content, 30);
            }

            $views[] = $viewInfo;
        }

        return [
            'count' => count($views),
            'views' => $views,
        ];
    }

    /**
     * Analyze middleware.
     */
    protected function analyzeMiddleware(bool $includeCodeSamples, bool $deepAnalysis): array
    {
        $middlewarePath = app_path('Http/Middleware');
        if (! File::exists($middlewarePath)) {
            return ['count' => 0, 'middleware' => []];
        }

        $finder = new Finder();
        $middleware = [];

        foreach ($finder->files()->in($middlewarePath)->name('*.php') as $file) {
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            
            $middlewareInfo = [
                'name' => $className,
                'file_path' => $file->getRelativePathname(),
            ];

            if ($includeCodeSamples) {
                $content = File::get($file->getRealPath());
                $middlewareInfo['code_sample'] = $this->extractCodeSample($content, 30);
            }

            $middleware[] = $middlewareInfo;
        }

        return [
            'count' => count($middleware),
            'middleware' => $middleware,
        ];
    }

    /**
     * Analyze jobs.
     */
    protected function analyzeJobs(bool $includeCodeSamples, bool $deepAnalysis): array
    {
        $jobsPath = app_path('Jobs');
        if (! File::exists($jobsPath)) {
            return ['count' => 0, 'jobs' => []];
        }

        $finder = new Finder();
        $jobs = [];

        foreach ($finder->files()->in($jobsPath)->name('*.php') as $file) {
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            
            $jobInfo = [
                'name' => $className,
                'file_path' => $file->getRelativePathname(),
            ];

            if ($includeCodeSamples) {
                $content = File::get($file->getRealPath());
                $jobInfo['code_sample'] = $this->extractCodeSample($content, 30);
            }

            $jobs[] = $jobInfo;
        }

        return [
            'count' => count($jobs),
            'jobs' => $jobs,
        ];
    }

    /**
     * Analyze events.
     */
    protected function analyzeEvents(bool $includeCodeSamples, bool $deepAnalysis): array
    {
        $eventsPath = app_path('Events');
        if (! File::exists($eventsPath)) {
            return ['count' => 0, 'events' => []];
        }

        $finder = new Finder();
        $events = [];

        foreach ($finder->files()->in($eventsPath)->name('*.php') as $file) {
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            
            $eventInfo = [
                'name' => $className,
                'file_path' => $file->getRelativePathname(),
            ];

            if ($includeCodeSamples) {
                $content = File::get($file->getRealPath());
                $eventInfo['code_sample'] = $this->extractCodeSample($content, 30);
            }

            $events[] = $eventInfo;
        }

        return [
            'count' => count($events),
            'events' => $events,
        ];
    }

    /**
     * Analyze policies.
     */
    protected function analyzePolicies(bool $includeCodeSamples, bool $deepAnalysis): array
    {
        $policiesPath = app_path('Policies');
        if (! File::exists($policiesPath)) {
            return ['count' => 0, 'policies' => []];
        }

        $finder = new Finder();
        $policies = [];

        foreach ($finder->files()->in($policiesPath)->name('*.php') as $file) {
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            
            $policyInfo = [
                'name' => $className,
                'file_path' => $file->getRelativePathname(),
            ];

            if ($includeCodeSamples) {
                $content = File::get($file->getRealPath());
                $policyInfo['code_sample'] = $this->extractCodeSample($content, 30);
            }

            $policies[] = $policyInfo;
        }

        return [
            'count' => count($policies),
            'policies' => $policies,
        ];
    }

    /**
     * Analyze Artisan commands.
     */
    protected function analyzeCommands(bool $includeCodeSamples, bool $deepAnalysis): array
    {
        $commandsPath = app_path('Console/Commands');
        if (! File::exists($commandsPath)) {
            return ['count' => 0, 'commands' => []];
        }

        $finder = new Finder();
        $commands = [];

        foreach ($finder->files()->in($commandsPath)->name('*.php') as $file) {
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            
            $commandInfo = [
                'name' => $className,
                'file_path' => $file->getRelativePathname(),
            ];

            if ($includeCodeSamples) {
                $content = File::get($file->getRealPath());
                $commandInfo['code_sample'] = $this->extractCodeSample($content, 30);
            }

            $commands[] = $commandInfo;
        }

        return [
            'count' => count($commands),
            'commands' => $commands,
        ];
    }

    /**
     * Analyze database migrations.
     */
    protected function analyzeMigrations(bool $includeCodeSamples): array
    {
        $migrationsPath = database_path('migrations');
        if (! File::exists($migrationsPath)) {
            return ['count' => 0, 'migrations' => []];
        }

        $finder = new Finder();
        $migrations = [];

        foreach ($finder->files()->in($migrationsPath)->name('*.php') as $file) {
            $migrationInfo = [
                'name' => $file->getFilename(),
                'file_path' => $file->getRelativePathname(),
                'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
            ];

            if ($includeCodeSamples) {
                $content = File::get($file->getRealPath());
                $migrationInfo['code_sample'] = $this->extractCodeSample($content, 30);
            }

            $migrations[] = $migrationInfo;
        }

        return [
            'count' => count($migrations),
            'migrations' => $migrations,
        ];
    }

    /**
     * Analyze configuration files.
     */
    protected function analyzeConfiguration(): array
    {
        $configPath = config_path();
        if (! File::exists($configPath)) {
            return ['count' => 0, 'config_files' => []];
        }

        $finder = new Finder();
        $configFiles = [];

        foreach ($finder->files()->in($configPath)->name('*.php') as $file) {
            $configFiles[] = [
                'name' => pathinfo($file->getFilename(), PATHINFO_FILENAME),
                'file_path' => $file->getRelativePathname(),
            ];
        }

        return [
            'count' => count($configFiles),
            'config_files' => $configFiles,
        ];
    }

    /**
     * Analyze installed packages.
     */
    protected function analyzePackages(): array
    {
        $composerPath = base_path('composer.json');
        if (! File::exists($composerPath)) {
            return ['error' => 'composer.json not found'];
        }

        $composer = json_decode(File::get($composerPath), true);
        
        return [
            'require' => $composer['require'] ?? [],
            'require_dev' => $composer['require-dev'] ?? [],
            'total_packages' => count($composer['require'] ?? []) + count($composer['require-dev'] ?? []),
        ];
    }

    /**
     * Extract a code sample from file content.
     */
    protected function extractCodeSample(string $content, int $lines = 20): string
    {
        $lines = explode("\n", $content);
        return implode("\n", array_slice($lines, 0, $lines));
    }

    /**
     * Generate a summary of the analysis.
     */
    protected function generateSummary(array $analysis): array
    {
        $summary = [
            'total_components' => 0,
            'feature_areas' => [],
            'complexity_score' => 'low',
        ];

        foreach ($analysis as $type => $data) {
            if (isset($data['count'])) {
                $summary['total_components'] += $data['count'];
                if ($data['count'] > 0) {
                    $summary['feature_areas'][] = $type;
                }
            }
        }

        // Simple complexity scoring
        if ($summary['total_components'] > 100) {
            $summary['complexity_score'] = 'high';
        } elseif ($summary['total_components'] > 50) {
            $summary['complexity_score'] = 'medium';
        }

        return $summary;
    }
}