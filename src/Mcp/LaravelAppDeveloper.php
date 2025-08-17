<?php

declare(strict_types=1);

namespace StafeGroup\LaravelAppDeveloper\Mcp;

use Laravel\Mcp\Server;

class LaravelAppDeveloper extends Server
{
    public string $serverName = 'Laravel App Developer';

    public string $serverVersion = '1.0.0';

    public string $instructions = 'Laravel Application Designer MCP Server for researching features, comparing with market leaders, and generating comprehensive development plans. This MCP excels at analyzing your Laravel application, identifying missing features by comparing with top competitors, and creating detailed development roadmaps for any type of application.';

    public int $defaultPaginationLength = 50;

    /**
     * @var string[]
     */
    public array $resources = [
        // Resources will be auto-discovered
    ];

    public function boot(): void
    {
        $this->discoverTools();
        $this->discoverResources();
        $this->discoverPrompts();
    }

    /**
     * Discover and register all available tools.
     *
     * @return array<string>
     */
    protected function discoverTools(): array
    {
        $excludedTools = config('laravel-app-developer.mcp.tools.exclude', []);
        $toolDir = new \DirectoryIterator(__DIR__.DIRECTORY_SEPARATOR.'Tools');
        
        foreach ($toolDir as $toolFile) {
            if ($toolFile->isFile() && $toolFile->getExtension() === 'php') {
                $fqdn = 'StafeGroup\\LaravelAppDeveloper\\Mcp\\Tools\\'.$toolFile->getBasename('.php');
                if (class_exists($fqdn) && ! in_array($fqdn, $excludedTools, true)) {
                    $this->addTool($fqdn);
                }
            }
        }

        $extraTools = config('laravel-app-developer.mcp.tools.include', []);
        foreach ($extraTools as $toolClass) {
            if (class_exists($toolClass)) {
                $this->addTool($toolClass);
            }
        }

        return $this->registeredTools;
    }

    /**
     * Discover and register all available resources.
     *
     * @return array<string>
     */
    protected function discoverResources(): array
    {
        $excludedResources = config('laravel-app-developer.mcp.resources.exclude', []);
        $resourcesPath = __DIR__.DIRECTORY_SEPARATOR.'Resources';
        
        if (is_dir($resourcesPath)) {
            $resourceDir = new \DirectoryIterator($resourcesPath);
            foreach ($resourceDir as $resourceFile) {
                if ($resourceFile->isFile() && $resourceFile->getExtension() === 'php') {
                    $fqdn = 'StafeGroup\\LaravelAppDeveloper\\Mcp\\Resources\\'.$resourceFile->getBasename('.php');
                    if (class_exists($fqdn) && ! in_array($fqdn, $excludedResources, true)) {
                        $this->addResource($fqdn);
                    }
                }
            }
        }

        $extraResources = config('laravel-app-developer.mcp.resources.include', []);
        foreach ($extraResources as $resourceClass) {
            if (class_exists($resourceClass)) {
                $this->addResource($resourceClass);
            }
        }

        return $this->registeredResources;
    }

    /**
     * Discover and register all available prompts.
     *
     * @return array<string>
     */
    protected function discoverPrompts(): array
    {
        $promptsPath = __DIR__.DIRECTORY_SEPARATOR.'Prompts';
        
        if (is_dir($promptsPath)) {
            $promptDir = new \DirectoryIterator($promptsPath);
            foreach ($promptDir as $promptFile) {
                if ($promptFile->isFile() && $promptFile->getExtension() === 'php') {
                    $fqdn = 'StafeGroup\\LaravelAppDeveloper\\Mcp\\Prompts\\'.$promptFile->getBasename('.php');
                    if (class_exists($fqdn)) {
                        $this->addPrompt($fqdn);
                    }
                }
            }
        }

        return $this->registeredPrompts;
    }
}