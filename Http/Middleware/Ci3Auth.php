<?php

/**
 * CI3 Authentication Middleware for BankSampah Module
 * 
 * Bridges CI3 session auth with Laravel routes.
 * Checks if user is logged in via CI3 session.
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 */

namespace Modules\BankSampah\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Ci3Auth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is logged in via CI3 session
        // CI3 stores session in cookie named 'sistem-informasi-desa-session'
        $sessionCookie = $request->cookie('sistem-informasi-desa-session');

        if (! $sessionCookie) {
            return redirect('/siteman');
        }

        // Try to decode the session to verify it's valid
        try {
            // CI3 session is encrypted, we check if session table has valid entry
            // Or we can check if the session cookie exists and CI3 considers it valid
            $sessionName = config('session.cookie', 'sistem-informasi-desa-session');
            $ciSession = $request->cookie($sessionName);

            if (empty($ciSession)) {
                return redirect('/siteman');
            }

            // Check session in database (CI3 stores sessions in 'sessions' table)
            $sessionExists = \DB::table('sessions')
                ->where('id', $ciSession)
                ->exists();

            if (! $sessionExists) {
                return redirect('/siteman');
            }

        } catch (\Exception $e) {
            // If sessions table doesn't exist or other error, redirect to login
            return redirect('/siteman');
        }

        // Share CI3 user data with views
        view()->share('ci_user', $this->getCI3User());

        return $next($request);
    }

    /**
     * Get current CI3 user data
     */
    protected function getCI3User()
    {
        try {
            $sessionName = config('session.cookie', 'sistem-informasi-desa-session');
            $ciSession = request()->cookie($sessionName);

            if (empty($ciSession)) {
                return null;
            }

            $sessionData = \DB::table('sessions')
                ->where('id', $ciSession)
                ->value('data');

            if (empty($sessionData)) {
                return null;
            }

            // Decode CI3 session data (serialized)
            $data = unserialize($sessionData);

            return (object) [
                'id'       => $data['user_id'] ?? null,
                'username' => $data['username'] ?? null,
                'nama'     => $data['nama'] ?? null,
                'id_grup'  => $data['id_grup'] ?? null,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
