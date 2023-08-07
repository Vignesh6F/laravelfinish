<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecureHeaders
{
    // Enumerate headers which you do not want in your application's responses.
    // Great starting point would be to go check out https://securityheaders.com/
    private $unwantedHeaderList = [
        'X-Powered-By',
        'Server',
    ];

    public function handle($request, Closure $next)
    {
        $this->removeUnwantedHeaders($this->unwantedHeaderList);
        $response = $next($request);
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Content-Security-Policy', "style-src 'self' 'unsafe-inline' *.googleapis.com *.bootstrapcdn.com https://*.tawk.to wss://*.tawk.to;");
        $response->headers->set('Permissions-Policy', "geolocation=(self '".url('/')."'), microphone=()"); // Clearly, you will be more elaborate here.
        return $response;
    }
    
    private function removeUnwantedHeaders($headerList)
    {
        foreach ($headerList as $header)
            header_remove($header);
    }
}
