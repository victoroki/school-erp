<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Doctrine\DBAL\Types\Type;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

public function boot()
{
    // Fix for Doctrine DBAL ENUM type issue
    $platform = DB::connection()->getDoctrineSchemaManager()->getDatabasePlatform();
    $platform->registerDoctrineTypeMapping('enum', 'string');
    
    // Alternative method as backup
    if (!Type::hasType('enum')) {
        Type::addType('enum', 'Doctrine\DBAL\Types\StringType');
    }
}
}