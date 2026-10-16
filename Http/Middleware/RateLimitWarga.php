<?php

/*
 *
 * File ini bagian dari OpenSID - Modul Bank Sampah
 * Rate Limit Warga Login Middleware
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 *
 */

namespace Modules\BankSampah\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\BankSampah\Libraries\SecurityManager;

class RateLimitWarga
{
    protected SecurityManager $security;

    public function __construct(SecurityManager $security)
    {
        $this->security = $security;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();
        $rateLimit = $this->security->checkLoginRateLimit($ip);

        if ($rateLimit['blocked']) {
            $minutes = ceil($rateLimit['retry_after'] / 60);

            $this->security->logEvent('warga_login_rate_limited', [
                'ip'          => $ip,
                'retry_after' => $rateLimit['retry_after'],
            ], 'warning');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Terlalu banyak percobaan login. Silakan tunggu {$minutes} menit lagi.",
                ], 429);
            }

            return redirect()->route('bank-sampah/warga')
                ->with('error', "Terlalu banyak percobaan login. Silakan tunggu {$minutes} menit lagi.");
        }

        // Store rate limit info
        $request->attributes->set('login_rate_limit', $rateLimit);

        return $next($request);
    }
}
