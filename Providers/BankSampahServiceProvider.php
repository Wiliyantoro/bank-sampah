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

class BankSampahServiceProvider extends ServiceProvider
{
    /**
     * Module name
     */
    protected $moduleName = 'BankSampah';

    /**
     * Module name (lowercase)
     */
    protected $moduleNameLower = 'banksampah';

    /**
     * Boot the application events.
     *
     * Migrasi dan route modul sudah dijalankan inti (RouterHook).
     * Middleware Laravel tidak dijalankan router CI3 — otorisasi memakai
     * AdminModulController + isCan() di controller (issue #43 poin 1.5).
     * seedAdminMenuCache() dan view()->composer('*') dihapus (poin 2.1/2.2):
     * AdminModulController sudah menyediakan variabel layout dan menu.
     */
    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom($this->pathConfig('config.php'), 'banksampah');
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $this->loadViewsFrom($this->pathViews(), 'banksampah');
    }

    /**
     * Path ke folder Config modul.
     */
    protected function pathConfig(string $file = ''): string
    {
        return __DIR__ . '/../Config' . ($file ? "/{$file}" : '');
    }

    /**
     * Path ke folder Views modul.
     */
    protected function pathViews(): string
    {
        return __DIR__ . '/../Views';
    }
}
