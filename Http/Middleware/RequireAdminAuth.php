<?php

/*
 *
 * File ini bagian dari OpenSID - Modul Bank Sampah
 * Auth Middleware - Checks CI3 session for admin login
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

class RequireAdminAuth
{
    /**
     * Handle an incoming request.
     * Checks if admin is logged in via CI3 session.
     * CI3 session is shared with Laravel via the 'web' middleware group.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if CI3 admin session is active
        // CI3 stores session data in the same session as Laravel (via 'web' middleware)
        // The isAdmin object contains the logged-in admin data including id_grup
        $isAdmin = $request->session()->get('isAdmin');

        // If not authenticated, redirect to siteman login
        if (! $isAdmin || empty($isAdmin->id_grup)) {
            // For AJAX requests, return JSON 401
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Redirect to CI3 login page
            return redirect(url('siteman'))
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Admin is authenticated, share auth data for views
        view()->share('ciAuth', $isAdmin);

        return $next($request);
    }
}
