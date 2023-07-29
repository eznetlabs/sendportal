<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;

class PublicPortalRedirect
{
    private $whitelist = [
        '^webview',
        '^subscriptions',
    ];
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if ($user) {
            return $next($request);
        }


        $host = $request->getHost();
        $path = $request->path();

        foreach ($this->whitelist as $whitelist) {
            $whitelisted = preg_match("/$whitelist/i", $path);

            if ($whitelisted) {
                return $next($request);
            }
        }

        $adminuri = config('sendportal.admin.host');
        $redirect = config('sendportal.admin.redirect');
        $adminurl = parse_url($adminuri);

        if (!$adminuri) {
            throw new Exception('SENDPORTAL_ADMIN_HOST is required');
        } else if (!isset($adminurl['host'])) {
            throw new Exception('SENDPORTAL_ADMIN_HOST is an invalid URL');
        } else if (!$redirect) {
            throw new Exception('SENDPORTAL_ADMIN_REDIRECT is required');
        }

        $adminhost = $adminurl['host'];

        if (strcasecmp($host, $adminhost) != 0) {
            return redirect()->away($redirect);
        }

        return $next($request);
    }
}