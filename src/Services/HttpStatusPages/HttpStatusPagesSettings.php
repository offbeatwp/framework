<?php
namespace OffbeatWP\Services\HttpStatusPages;

use Illuminate\Support\Collection;
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
        if (!$httpStatusPagesCodes instanceof Collection) {
            $httpStatusPagesCodes = collect(['404']);
        }

        $httpStatusPagesCodes->each(function($statusCode) use ($form) {
            $form->addField(Post::make('http-status-page-' . $statusCode, 'Page for ' . $statusCode)->fromPostTypes(['page']));
        });

        return $form;
    }
}
