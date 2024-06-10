<?php

namespace OffbeatWP\Form\FieldsCollections;

use OffbeatWP\Form\Fields\Select;
use OffbeatWP\Form\Fields\Text;

class Heading extends AbstractFieldsCollection
{
    /** @param string $defaultHeading */
    public function __construct($defaultHeading = 'h3')
    {
        parent::__construct();
        $this->addField(Text::make('heading_title', __('Title', 'offbeatwp')));

        $headingTypeField = Select::make('heading_type', __('Type', 'offbeatwp'));
        $headingTypeField
            ->addOptions([
                'h1' => __('h1', 'offbeatwp'),
                'h2' => __('h2', 'offbeatwp'),
                'h3' => __('h3', 'offbeatwp'),
                'h4' => __('h4', 'offbeatwp'),
                'h5' => __('h5', 'offbeatwp'),
                'h6' => __('h6', 'offbeatwp'),
                'div' => __('div', 'offbeatwp'),
            ])
            ->description(__('The heading type is used to let search indexers know what is important on a page', 'offbeatwp'))
            ->default($defaultHeading);

        $this->addField($headingTypeField);

        $headingStyleField = Select::make('heading_style', __('Style', 'offbeatwp'));
        $headingStyleField
            ->addOptions([
                '' => __('Default', 'offbeatwp'),
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
