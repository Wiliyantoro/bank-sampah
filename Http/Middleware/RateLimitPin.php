<?php

/*
 *
 * File ini bagian dari OpenSID - Modul Bank Sampah
 * Rate Limit PIN Verification Middleware
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

class RateLimitPin
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
        $memberId = $request->route('memberId') ?? $request->input('member_id');

        if ($memberId) {
            $rateLimit = $this->security->checkPinRateLimit($memberId);

            if ($rateLimit['blocked']) {
                $minutes = ceil($rateLimit['retry_after'] / 60);

                $this->security->logEvent('pin_rate_limited', [
                    'member_id'  => $memberId,
                    'retry_after' => $rateLimit['retry_after'],
                ], 'warning');

                return redirect()->back()
                    ->with('error', "Terlalu banyak percobaan PIN. Silakan tunggu {$minutes} menit lagi.");
            }

            // Store rate limit info in request for controller use
            $request->attributes->set('pin_rate_limit', $rateLimit);
        }

        return $next($request);
    }
}
