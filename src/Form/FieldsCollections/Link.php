<?php
namespace OffbeatWP\Form\FieldsCollections;

use OffbeatWP\Form\Fields\Select;
use OffbeatWP\Form\Fields\Text;

class Link extends AbstractFieldsCollection {
    public function __construct()
    {
        parent::__construct();
        $this->addField(Text::make('link_label', __('Link label', 'offbeatwp')));
        $this->addField(Text::make('link_url', __('Link url', 'offbeatwp')));

        $linkTargetField = Select::make('link_target', __('Link target', 'offbeatwp'));
        $linkTargetField
            ->setOptions([
                '_self' => __('Self', 'offbeatwp'),
                '_blank' => __('Blank', 'offbeatwp'),
                '_parent' => __('Parent', 'offbeatwp'),
                '_top' => __('Top', 'offbeatwp'),
            ])
            ->description(__('The heading type is used to let search indexers know what is important on a page', 'offbeatwp'));

        $this->addField($linkTargetField);
    }
}