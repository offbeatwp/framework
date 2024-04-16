<?php
namespace OffbeatWP\Services\HttpStatusPages;

use OffbeatWP\Form\Fields\Post;
use OffbeatWP\Form\Form;

class HttpStatusPagesSettings
{
    public const ID = 'http-status-pages';
    public const PRIORITY = 90;

    public function title(): string
    {
        return __('Http status pages', 'offbeatwp');
    }

    public function form(): Form
    {
        $form = new Form();

        $httpStatusPagesCodes = config('app.http_status_pages_codes');
        if (!is_iterable($httpStatusPagesCodes)) {
            $httpStatusPagesCodes = [404];
        }

        foreach ($httpStatusPagesCodes as $statusCode) {
            $form->addField(Post::make('http-status-page-' . $statusCode, 'Page for ' . $statusCode)->fromPostTypes(['page']));
        }

        return $form;
    }
}
