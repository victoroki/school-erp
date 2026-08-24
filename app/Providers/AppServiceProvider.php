<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Doctrine\DBAL\Types\Type;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $helperPath = app_path('Helpers/school.php');
        if (file_exists($helperPath)) {
            require_once $helperPath;
        }

        // Communication service bindings — dynamic based on active SMS provider
        $this->app->bind(
            \App\Services\Communication\SmsProviderInterface::class,
            function () {
                $activeProvider = \App\Models\CommunicationSetting::getActiveSmsProviderName();
                if ($activeProvider === 'sozuri') {
                    return new \App\Services\Communication\SozuriSmsProvider();
                }
                return new \App\Services\Communication\AfricasTalkingSmsProvider();
            }
        );
        $this->app->bind(
            \App\Services\Communication\EmailProviderInterface::class,
            \App\Services\Communication\SmtpEmailProvider::class
        );
    }

public function boot()
{
    \Illuminate\Pagination\Paginator::useBootstrapFour();

    // Fix for Doctrine DBAL ENUM type issue.
    //
    // Wrapped so the app still boots when no database is reachable —
    // composer's post-autoload-dump (package:discover) boots the framework
    // during CI builds, where there is deliberately no MySQL server yet.
    try {
        $platform = DB::connection()->getDoctrineSchemaManager()->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');

        // Alternative method as backup
        if (!Type::hasType('enum')) {
            Type::addType('enum', 'Doctrine\DBAL\Types\StringType');
        }
    } catch (\Throwable) {
        // No DB connection available in this context — the enum mapping is
        // only needed when migrations/queries actually touch the database.
    }
}
}