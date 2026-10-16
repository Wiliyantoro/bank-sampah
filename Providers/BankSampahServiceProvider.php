<?php

/*
 *
 * File ini bagian dari OpenSID - Modul Bank Sampah
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 *
 */

namespace Modules\BankSampah\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Migrations\Migrator;

class BankSampahServiceProvider extends ServiceProvider
{
    /**
     * Module name
     */
    protected string $moduleName = 'BankSampah';

    /**
     * Module path
     */
    protected string $modulePath = __DIR__ . '/..';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerMigrations();
        $this->loadModuleHelpers();
        $this->registerMiddleware();
        $this->registerRoutes();
        $this->shareViewVariables();
        $this->seedAdminMenuCache();
    }

    /**
     * Register module middleware alias.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('banksampah.auth', \Modules\BankSampah\Http\Middleware\RequireAdminAuth::class);
    }

    /**
     * Seed admin_menu cache for Laravel module routes.
     * 
     * Problem: admin_menu() uses ci_auth()->id_grup as cache key.
     * On CI3 routes (beranda), ci_auth() works → key = '454_admin_menu' → full menu.
     * On Laravel routes (bank-sampah), ci_auth() returns null → key = '_admin_menu' → empty.
     * 
     * Fix: Ensure both cache keys have the correct data.
     */
    protected function seedAdminMenuCache(): void
    {
        try {
            $grupId = 454; // Administrator group ID
            $correctKey = "{$grupId}_admin_menu";
            $nullKey = '_admin_menu';

            // If the correct cache already exists, just copy it to the null key
            $cached = \cache()->get($correctKey);
            if ($cached) {
                // Only set if missing or different
                $nullCached = \cache()->get($nullKey);
                if (! $nullCached || $nullCached !== $cached) {
                    \cache()->rememberForever($nullKey, fn () => $cached);
                }
                return;
            }

            // If neither cache exists, build from DB
            $modul = \App\Models\Modul::with(['childrens' => function ($q) use ($grupId) {
                $q->select(['id', 'parent', 'modul', 'slug', 'url', 'ikon'])
                    ->whereIn('id', function ($query) use ($grupId) {
                        $query->select('id_modul')->from('grup_akses')->where('id_grup', $grupId);
                    })
                    ->isChild()->isShow()->isActive()
                    ->orderBy('urut');
            }])
            ->select(['id', 'parent', 'modul', 'slug', 'url', 'ikon'])
            ->whereIn('id', function ($query) use ($grupId) {
                $query->select('id_modul')->from('grup_akses')->where('id_grup', $grupId);
            })
            ->isParent()->isShow()->isActive()
            ->orderBy('urut')
            ->get();

            $defaultModul = collect(\App\Models\Modul::DEFAULT_MODUL);
            $result = $defaultModul->merge($modul)->map(function ($item) {
                if (isset($item['childrens'])) {
                    $item['childrens'] = $item['childrens']->map(fn ($c) => $c)->values();
                }
                return $item;
            })->values()->toArray();

            \cache()->rememberForever($correctKey, fn () => $result);
            \cache()->rememberForever($nullKey, fn () => $result);
        } catch (\Throwable $e) {
            // Silently fail - don't break the module
        }
    }

    /**
     * Share variables needed by OpenSID admin layout.
     */
    protected function shareViewVariables(): void
    {
        \view()->composer('*', function ($view) {
            $defaults = [
                'notif'                  => ['langganan' => null],
                'notif_list'             => collect(),
                'notif_counts'           => [],
                'total_notifications'    => 0,
                'auth'                   => (object) ['nama' => 'Admin', 'foto' => 'kuser.png', 'id' => 0],
                'akses_modul'            => [],
                'modul_ini'              => 'bank-sampah',
                'sub_modul_ini'          => '',
                'is_mobile'              => false,
                'kategori_pengaturan'    => [],
                'token_name'             => 'sidcsrf',
                'token_value'            => \csrf_token(),
                'session'                => [],
                'desa'                   => [
                    'nama_desa'      => 'Desa',
                    'logo'           => '',
                    'nama_kecamatan' => '',
                    'nama_kabupaten' => '',
                ],
            ];

            try {
                $desa = \DB::table('config')->where('id', 17)->first();
                if ($desa) {
                    $defaults['desa'] = (array) $desa;
                }
                $defaults['sebKec'] = $defaults['desa']['nama_kecamatan'] ?? '';
                $defaults['sebKab'] = $defaults['desa']['nama_kabupaten'] ?? '';
                $defaults['namKec'] = $defaults['desa']['nama_kecamatan'] ?? '';
                $defaults['namKab'] = $defaults['desa']['nama_kabupaten'] ?? '';
            } catch (\Exception $e) {
                // ignore
            }

            foreach ($defaults as $key => $value) {
                try {
                    if (! $view->offsetExists($key) || is_null($view->offsetGet($key))) {
                        $view->with($key, $value);
                    }
                } catch (\Exception $e) {
                    $view->with($key, $value);
                }
            }
        });
    }

    /**
     * Register module routes.
     */
    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'banksampah');

        // Register WhatsApp Notification
        $this->app->singleton(\Modules\BankSampah\Libraries\WhatsAppNotification::class, function ($app) {
            return new \Modules\BankSampah\Libraries\WhatsAppNotification();
        });

        // Register Security Manager
        $this->app->singleton(\Modules\BankSampah\Libraries\SecurityManager::class, function ($app) {
            return new \Modules\BankSampah\Libraries\SecurityManager();
        });
    }

    /**
     * Load helper files manually
     */
    protected function loadModuleHelpers(): void
    {
        $helpers = [
            __DIR__ . '/../Helpers/banksampah_helper.php',
            __DIR__ . '/../Helpers/security_helper.php',
        ];

        foreach ($helpers as $file) {
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([
            $this->modulePath . '/Config/config.php' => config_path('banksampah.php'),
        ], 'banksampah-config');

        // Shortcut tidak diperlukan, modul sudah didaftarkan via ModulSeeder
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $viewPath = $this->modulePath . '/Views';

        $this->loadViewsFrom($viewPath, 'banksampah');

        // Register admin namespace so @extends('admin::layouts.index') works
        \View::addNamespace('admin', base_path('resources/views/admin'));

        $this->publishes([
            $viewPath => resource_path('views/modules/banksampah'),
        ], 'banksampah-views');
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $langPath = $this->modulePath . '/Resources/lang';

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'banksampah');
        }
    }

    /**
     * Register migrations.
     */
    protected function registerMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->modulePath . '/Database/Migrations' => database_path('migrations'),
            ], 'banksampah-migrations');

            $this->publishes([
                $this->modulePath . '/Database/Seeders' => database_path('seeders'),
            ], 'banksampah-seeders');
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'banksampah',
            \Modules\BankSampah\Libraries\WhatsAppNotification::class,
        ];
    }
}
