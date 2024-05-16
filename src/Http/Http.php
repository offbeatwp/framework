<?php

namespace OffbeatWP\Http;

use Symfony\Component\HttpFoundation\Request;

final class Http
{
    public function abort(int $code, string $description = ''): mixed
    {
        status_header($code, $description);

        return apply_filters('offbeatwp/http_status', null, $code);
    }

    public function redirect(string $url, int $status = 301): void
    {
        wp_redirect($url, $status);
        exit;
    }

    /**
     * @param string|null $requestUri
     * @param int $status
     * @return void
     */
    public function redirectToParentUrl($requestUri = null, int $status = 301): void
    {   
        if ($requestUri === null) {
            $request = Request::create($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $_REQUEST, $_COOKIE, [], $_SERVER);
            $requestUri = $request->getPathInfo();
        } else {
            $requestUri = parse_url($requestUri, PHP_URL_PATH);
        }

        $requestUri = untrailingslashit($requestUri);

        // If $requestUri is empty, we are already on the root, so we can't redirect deeper
        if (!$requestUri) {
            return;
        }

        // Get the request uri part until the last slash
        $requestUri = substr($requestUri, 0, strrpos($requestUri, '/'));

        // Reassemble the URL
        $url = get_home_url(null, $requestUri);

        // Add trailing slash
        $url = trailingslashit($url);

        // Add the original query parameters to the url
        if (!empty($_GET)) {
            $url .= '?' . http_build_query($_GET);
        }

        // Redirect to the parent url
        $this->redirect($url, $status);
        exit;
    }
}
