<?php

namespace WpUserSession\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use WpUserSession\WpSession;

class WpSessionMiddleware
{

    /**
     * The Wordpress logged_in session
     *
     * @var WpSession
     */
    protected $session;

    /**
     * @param WpSession
     */
    public function __construct(WpSession $session)
    {
        $this->session = $session;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->session->isValid()) {
            Auth::login($this->session->getUser());
        } else {
            Auth::logout();
        }

        return $next($request);
    }
}
