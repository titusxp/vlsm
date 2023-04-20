<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\RedirectResponse;

class SystemAdminMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Start the session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }


        $redirect = null;
        if ($this->isAjaxRequest($request) || $this->isCliRequest() || $this->shouldExcludeFromAuthCheck($request)) {

            // Skip the authentication check if the request is an AJAX request,
            // a CLI request, or if the requested URI is excluded from the
            // authentication check
            return $handler->handle($request);
        } elseif (!isset($_SESSION['adminUserId']) || empty($_SESSION['adminUserId'])) {

            // Redirect to the login page if the system user is not logged in
            $redirect = new RedirectResponse('/system-admin/login/login.php');
        }

        if (!is_null($redirect)) {
            return $redirect;
        } else {
            return $handler->handle($request);
        }
    }

    private function isAjaxRequest(ServerRequestInterface $request): bool
    {
        return strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest';
    }

    private function isCliRequest(): bool
    {
        return (php_sapi_name() === 'cli');
    }

    private function shouldExcludeFromAuthCheck(ServerRequestInterface $request): bool
    {
        // Get the requested URI
        $uri = $request->getUri()->getPath();

        // Clean up the URI
        $uri = preg_replace('/([\/.])\1+/', '$1', $uri);


        //error_log($uri);

        $excludedRoutes = [
            '/system-admin/login/login.php',
            '/system-admin/setup/index.php',
            // Add other routes to exclude from the authentication check here
        ];

        return in_array($uri, $excludedRoutes, true);
    }
}
