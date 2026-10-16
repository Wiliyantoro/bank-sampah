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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        // Authorization: grup harus punya akses modul 'bank-sampah' di
        // grup_akses x setting_modul (pola hak akses inti OpenSID).
        // Fail-closed: kalau verifikasi gagal/tabel belum ada, akses ditolak.
        try {
            $boleh = DB::table('grup_akses')
                ->where('id_grup', $isAdmin->id_grup)
                ->whereIn('id_modul', function ($q) {
                    $q->select('id')->from('setting_modul')
                        ->where('slug', 'bank-sampah');
                })
                ->exists();
        } catch (\Throwable $e) {
            Log::warning('[BankSampah] Gagal memverifikasi grup_akses: ' . $e->getMessage());
            $boleh = false;
        }

        if (! $boleh) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            abort(403, 'Anda tidak memiliki hak akses ke modul Bank Sampah.');
        }

        // Admin is authenticated, share auth data for views
        view()->share('ciAuth', $isAdmin);

        return $next($request);
    }
}
