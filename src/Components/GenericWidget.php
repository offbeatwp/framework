<?php
namespace OffbeatWP\Components;

use OffbeatWP\AcfCore\FieldsMapper as AcfFieldsMapper;
use OffbeatWP\Components\ComponentInterfaceTrait;
use OffbeatWP\Form\Fields\Helper as FieldsHelper;
use OffbeatWP\AcfCore\ComponentFields;

class GenericWidget extends \WP_Widget
{
    use ComponentInterfaceTrait;

    public $widgetId = null;
    public $settings = null;
    public $componentClass = null;

    public function __construct($settings, $componentClass)
    {
        $this->settings =       $settings;
        $this->componentClass = $componentClass;

        $options = (isset($settings['options'])) ? : [];

        parent::__construct(
            $settings['id_base'],
            $settings['name'],
            $options
        );

        $componentSettings = $componentClass::settings();

        add_action('init', [$this, 'registerForm']);
    }

    public function widget($args, $instance)
    {
        $this->widgetId = "widget_" . $args["widget_id"];

        if (method_exists($this, 'setWidgetSettings')) {
            $this->setWidgetSettings();
        }

        $this->initWidget($args, $instance);
    }

    // TODO Quick implementation, needs to be improved
    public function initWidget($args, $instance)
    {
        echo $args['before_widget'];

        echo $this->render($this->getFieldValues());

        echo $args['after_widget'];
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['widget_exists'] = 1;

        return $instance;
    }

    public function form($instance)
    {
        echo "<br>";
    }

    public function the_field($key)
    {
        echo $this->get_field($key);
    }

    public function get_field($key)
    {
        return get_field($key, $this->widgetId);
    }

    public function getFieldValues()
    {
        $settings = (object)[];

        $fields = get_fields($this->widgetId);

        if (empty($fields)) return $settings;

        $keys = array_keys($fields);

        foreach ($keys as $key) {
            $settings->{$key} = $this->get_field($key);
        }

        return $settings;
    }

    public function registerForm () {
        if( ! function_exists('acf_add_local_field_group') ) return null;

        $fields = ComponentFields::get($this->settings['component_name'], 'acfeditor');

        acf_add_local_field_group(array (
            'key' => 'group_widget_' . $this->settings['id_base'],
            'title' => 'Widget settings - ' . $this->settings['name'],
            'fields' => $fields,
            'location' => array (
                array (
                    array (
                        'param' => 'widget',
                        'operator' => '==',
                        'value' => $this->settings['id_base'],
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => 1,
            'description' => '',
        ));
    }
}
