<?php
namespace OffbeatWP\Services\HttpStatusPages;

use OffbeatWP\Services\AbstractService;
use OffbeatWP\Contracts\SiteSettings;

class HttpStatusPagesService extends AbstractService
{
    /** @var SiteSettings|null */
    protected $settings;

    public function register(SiteSettings $settings)
    {
        $this->settings = $settings;

        $settings->addPage(HttpStatusPagesSettings::class);

        add_filter('offbeatwp/http_status', [$this, 'renderHttpStatusPage'], 20, 2);
    }

    /**
     * @param mixed $return
     * @param int $code
     * @return mixed
     */
    public function renderHttpStatusPage($return, int $code) {
        global $wp_query, $wp_the_query;

        $pageId = setting("http-status-page-{$code}");

        if (!$pageId || !is_numeric($pageId)) {
            return $return;
        }

        query_posts(['page_id' => $pageId]);
        the_post();

        $wp_the_query = $wp_query;

        $route = offbeat('routes')->findCallbackRoute();

        return offbeat()->runRoute($route);
    }
}