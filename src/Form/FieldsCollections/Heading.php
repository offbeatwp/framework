<?php
namespace OffbeatWP\Form\FieldsCollections;

class Heading extends AbstractFieldsCollection {
    public function __construct($defaultHeading = 'h3', $includeLead = false)
    {
        $this->addField(\OffbeatWP\Form\Fields\Text::make('heading_title', __('Title', 'offbeatwp')));

        $headingTypeField = \OffbeatWP\Form\Fields\Select::make('heading_type', __('Type', 'offbeatwp'));
        $headingTypeField
            ->addOptions([
                'h1' => __('h1', 'offbeatwp'),
                'h2' => __('h2', 'offbeatwp'),
                'h3' => __('h3', 'offbeatwp'),
                'h4' => __('h4', 'offbeatwp'),
                'h5' => __('h5', 'offbeatwp'),
                'h6' => __('h6', 'offbeatwp'),
            ])
            ->description(__('The heading type is used to let search indexers know what is important on a page', 'offbeatwp'));

        $this->addField($headingTypeField);

        $headingStyleField = \OffbeatWP\Form\Fields\Select::make('heading_style', __('Style', 'offbeatwp'));
        $headingStyleField
            ->addOptions([
                'h1' => __('h1', 'offbeatwp'),
                'h2' => __('h2', 'offbeatwp'),
                'h3' => __('h3', 'offbeatwp'),
                'h4' => __('h4', 'offbeatwp'),
                'h5' => __('h5', 'offbeatwp'),
                'h6' => __('h6', 'offbeatwp'),
            ])
            ->description(__('The heading style is used to override the default styling of the header type', 'offbeatwp'));

        $this->addField($headingStyleField);
    }
}