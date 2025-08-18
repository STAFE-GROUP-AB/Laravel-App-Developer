<?php

uses()->in(__DIR__);

// Helper functions for tests
function createTemporaryDirectory(): string
{
    $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laravel-app-developer-tests-' . uniqid();
    mkdir($tmpDir, 0755, true);
    return $tmpDir;
}

function removeDirectory(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            removeDirectory($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}