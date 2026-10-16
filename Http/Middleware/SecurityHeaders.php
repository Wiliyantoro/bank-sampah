<?php

/*
 *
 * File ini bagian dari OpenSID - Modul Bank Sampah
 * Security Headers Middleware
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

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Nonce hanya dibagikan ke dokumen standalone milik modul (view modul
        // merender dokumen HTML penuh). Halaman admin yang di-render di dalam
        // layout inti (admin::layouts.index) memuat inline script milik inti
        // yang di luar kendali modul — CSP ketat untuk dokumen tertanam
        // harus diselesaikan di level inti OpenSID (BS-018).
        $isStandaloneDoc = $request->is('bank-sampah/warga*')
            || $request->is('bank-sampah/member/kartu*')
            || $request->is('bank-sampah/transaksi/struk*');

        $nonce = null;
        if ($isStandaloneDoc) {
            $nonce = bin2hex(random_bytes(16));
            view()->share('cspNonce', $nonce);
        }

        $response = $next($request);

        // Security Headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Content Security Policy — hanya dokumen standalone (modul kontrol penuh).
        // script-src memakai nonce; style-src tetap 'unsafe-inline' (blok <style>
        // inline di banyak view — risiko CSS jauh lebih rendah dari JS).
        if ($isStandaloneDoc) {
            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'nonce-{$nonce}' https://cdn.jsdelivr.net",
                "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com",
                "img-src 'self' data: https:",
                "font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com",
                "connect-src 'self'",
                "frame-ancestors 'none'",
            ]);
            $response->headers->set('Content-Security-Policy', $csp);
        }

        // HSTS (only for HTTPS)
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Remove server signature
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
