<?php

namespace OffbeatWP\Routes;

final class WpRedirect
{
    private string $location;
    private int $status;
    private string $redirectBy;

    public function __construct(string $location, int $status = 302, string $redirectBy = 'WordPress')
    {
        $this->location = $location;
        $this->status = $status;
        $this->redirectBy = $redirectBy;
    }

    public function execute(): never
    {
        wp_safe_redirect($this->location, $this->status, $this->redirectBy);
        exit;
    }
}
