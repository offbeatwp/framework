<?php

namespace OffbeatWP\Form\FieldsCollections;

use OffbeatWP\Form\Fields\Select;
use OffbeatWP\Form\Fields\Text;

class Link extends AbstractFieldsCollection
{
    public function __construct()
    {
        parent::__construct();
        $this->addField(Text::make('link_label', __('Link label', 'pinowp')));
        $this->addField(Text::make('link_url', __('Link url', 'pinowp')));

        $linkTargetField = Select::make('link_target', __('Link target', 'pinowp'));
        $linkTargetField
            ->addOptions([
                '_self' => __('Self', 'pinowp'),
                '_blank' => __('Blank', 'pinowp'),
                '_parent' => __('Parent', 'pinowp'),
                '_top' => __('Top', 'pinowp'),
            ])
            ->description(__('The heading type is used to let search indexers know what is important on a page', 'pinowp'));

        $this->addField($linkTargetField);
    }
}
