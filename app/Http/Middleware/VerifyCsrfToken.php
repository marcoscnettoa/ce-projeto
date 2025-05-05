<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
// #
use Illuminate\Session\TokenMismatchException;
use Closure;
// -

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'link',
        'task',
        'task/*',
        'mra_nota_fiscal/webhook', // # -
        'mra_g_iugu/mra_g_iugu_faturas/webhook', // # -
    ];

    // #
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, Closure $next)
    {
        if (
            $this->isReading($request) ||
            $this->runningUnitTests() ||
            $this->inExceptArray($request) ||
            $this->tokensMatch($request)
        ) {
            return tap($next($request), function ($response) use ($request) {
                if ($this->shouldAddXsrfTokenCookie()) {
                    $this->addCookieToResponse($request, $response);
                }
            });
        }

        // #
        if(!$this->tokensMatch($request)){
            return redirect()->to('/');
        }
        // - #

        throw new TokenMismatchException('CSRF token mismatch.');
    }
    // -
}
