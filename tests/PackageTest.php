<?php

use StafeGroup\LaravelAppDeveloper\LaravelAppDeveloperServiceProvider;

it('can be loaded as a service provider', function () {
    $provider = new LaravelAppDeveloperServiceProvider(app());
    
    expect($provider)->toBeInstanceOf(LaravelAppDeveloperServiceProvider::class);
});

it('registers configuration', function () {
    $provider = new LaravelAppDeveloperServiceProvider(app());
    $provider->register();
    
    expect(config('laravel-app-developer'))->toBeArray();
});

it('has correct package name in composer.json', function () {
    $composerJson = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
    
    expect($composerJson['name'])->toBe('stafe-group-ab/laravel-app-developer');
    expect($composerJson['type'])->toBe('library');
    expect($composerJson['license'])->toBe('MIT');
});

it('has proper autoload configuration', function () {
    $composerJson = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
    
    expect($composerJson['autoload']['psr-4']['StafeGroup\\LaravelAppDeveloper\\'])->toBe('src/');
});

it('has required dependencies', function () {
    $composerJson = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
    
    expect($composerJson['require'])->toHaveKey('php');
    expect($composerJson['require'])->toHaveKey('laravel/mcp');
    expect($composerJson['require'])->toHaveKey('illuminate/support');
});