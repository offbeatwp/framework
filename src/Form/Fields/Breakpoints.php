<?php
namespace OffbeatWP\Form\Fields;

class Breakpoints extends Select {
    public function getOptions () {
        return [
            '0px'    => __('Extra Small (Mobile)', 'offbeatwp'),
            '576px'  => __('Small (Mobile)', 'offbeatwp'),
            '768px'  => __('Medium (Tablet)', 'offbeatwp'),
            '992px'  => __('Large (Desktop)', 'offbeatwp'),
            '1200px' => __('Extra Large (Desktop)', 'offbeatwp')
        ];
    }
}