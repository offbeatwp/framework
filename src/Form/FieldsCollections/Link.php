<?php
namespace OffbeatWP\Form\FieldsCollections;

class Link extends AbstractFieldsCollection {
    public function __construct()
    {
        $this->addField(\OffbeatWP\Form\Fields\Text::make('link_label', __('Link label', 'offbeatwp')));
        $this->addField(\OffbeatWP\Form\Fields\Text::make('link_url', __('Link url', 'offbeatwp')));

        $linkTargetField = \OffbeatWP\Form\Fields\Select::make('link_target', __('Link target', 'offbeatwp'));
        $linkTargetField
            ->addOptions([
                '_self' => __('Self', 'offbeatwp'),
                '_blank' => __('Blank', 'offbeatwp'),
                '_parent' => __('Parent', 'offbeatwp'),
                '_top' => __('Top', 'offbeatwp'),
            ])
            ->description(__('The heading type is used to let search indexers know what is important on a page', 'offbeatwp'));

        $this->addField($linkTargetField);
    }
}