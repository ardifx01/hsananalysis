<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use Spatie\FlysystemDropbox\DropboxAdapter;
use Spatie\Dropbox\Client;
use Illuminate\Filesystem\FilesystemAdapter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Storage::extend('dropbox', function ($app, array $config) {
            $client = new Client($config['authorization_token']);
            $adapter = new DropboxAdapter($client);
    
            return new FilesystemAdapter(
                new \League\Flysystem\Filesystem($adapter),
                $adapter,
                $config
            );
        });
    }
}

