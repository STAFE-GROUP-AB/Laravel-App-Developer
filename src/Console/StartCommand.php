<?php

declare(strict_types=1);

namespace StafeGroup\LaravelAppDeveloper\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('laravel-app-developer:mcp', 'Starts Laravel App Developer MCP Server (usually from mcp.json)')]
class StartCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-app-developer:mcp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts Laravel App Developer MCP Server';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return Artisan::call('mcp:start laravel-app-developer');
    }
}