<?php

declare(strict_types=1);

namespace StafeGroup\LaravelAppDeveloper\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('laravel-app-developer:install', 'Install Laravel App Developer MCP Server')]
class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-app-developer:install {--force : Force installation even if already installed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Laravel App Developer MCP Server';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing Laravel App Developer MCP Server...');

        // Publish configuration file
        $this->publishConfiguration();

        // Create development plans directory
        $this->createDevelopmentPlansDirectory();

        // Display installation instructions
        $this->displayInstructions();

        $this->info('✅ Laravel App Developer MCP Server installed successfully!');

        return 0;
    }

    /**
     * Publish the configuration file.
     */
    protected function publishConfiguration(): void
    {
        $configPath = config_path('laravel-app-developer.php');
        
        if (! File::exists($configPath) || $this->option('force')) {
            $this->call('vendor:publish', [
                '--tag' => 'laravel-app-developer-config',
                '--force' => $this->option('force'),
            ]);
            $this->info('📝 Configuration file published.');
        } else {
            $this->warn('Configuration file already exists. Use --force to overwrite.');
        }
    }

    /**
     * Create the development plans directory.
     */
    protected function createDevelopmentPlansDirectory(): void
    {
        $directory = config('laravel-app-developer.mcp.development_plans.output_directory', base_path('development-plans'));
        
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
            $this->info("📁 Created development plans directory: {$directory}");
        }
    }

    /**
     * Display installation and configuration instructions.
     */
    protected function displayInstructions(): void
    {
        $this->newLine();
        $this->info('🚀 Next Steps:');
        $this->line('');
        $this->line('1. Add the MCP server to your AI assistant configuration:');
        $this->line('');
        $this->info('   JSON Configuration:');
        $this->line('   {');
        $this->line('       "mcpServers": {');
        $this->line('           "laravel-app-developer": {');
        $this->line('               "command": "php",');
        $this->line('               "args": ["./artisan", "laravel-app-developer:mcp"]');
        $this->line('           }');
        $this->line('       }');
        $this->line('   }');
        $this->line('');
        $this->line('2. Available tools after installation:');
        $this->line('   • analyze-application: Analyze your Laravel app structure and features');
        $this->line('   • research-market-leaders: Research top competitors in your app category');
        $this->line('   • compare-features: Compare your features with market leaders');
        $this->line('   • generate-development-plan: Create comprehensive development plans');
        $this->line('   • design-system: Design complete systems from requirements');
        $this->line('   • suggest-features: Get feature suggestions based on market analysis');
        $this->line('');
        $this->line('3. Try these example commands with your AI assistant:');
        $this->line('   • "Analyze my Laravel application and tell me what features it has"');
        $this->line('   • "Research the top 10 CRM applications and compare them with my app"');
        $this->line('   • "Generate a development plan for building a modern e-commerce platform"');
        $this->line('   • "Design the best project management system and create a development roadmap"');
    }
}