<?php

namespace OffbeatWP\Components;

use OffbeatWP\AcfCore\ComponentFields;
use WP_Widget;

class GenericWidget extends WP_Widget
{
    use ComponentInterfaceTrait;

    /** @var string|null */
    public $widgetId = null;
    /** @var mixed[]|null */
    public $settings = null;
    /** @var class-string|null */
    public $componentClass = null;

    /**
     * @param mixed[] $settings
     * @param class-string $componentClass
     */
    public function __construct($settings, $componentClass)
    {
        $this->settings = $settings;
        $this->componentClass = $componentClass;

        $options = (isset($settings['options'])) ?: [];

        parent::__construct(
            $settings['id_base'],
            $settings['name'],
            $options
        );

        add_action('init', [$this, 'registerForm']);
    }

    /**
     * @param mixed[] $args
     * @param mixed[] $instance
     * @return void
     */
    public function widget($args, $instance)
    {
        $this->widgetId = 'widget_' . $args['widget_id'];

        if (method_exists($this, 'setWidgetSettings')) {
            $this->setWidgetSettings();
        }

        $this->initWidget($args, $instance);
    }

    /**
     * TODO Quick implementation, needs to be improved
     * @param mixed[] $args
     * @param null $instance Unused
     * @return void
     */
    public function initWidget($args, $instance)
    {
        echo $args['before_widget'];

        echo $this->render($this->getFieldValues());

        echo $args['after_widget'];
    }

    /** @return \stdClass */
    public function getFieldValues()
    {
        $settings = (object)[];

        if (function_exists('get_fields') && function_exists('get_field')) {
            $fields = get_fields($this->widgetId);

            if (!$fields) {
                return $settings;
            }

            $keys = array_keys($fields);

            foreach ($keys as $key) {
                $settings->{$key} = $this->get_field($key);
            }
        }

        return $settings;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get_field($key)
    {
        return get_field($key, $this->widgetId);
    }

    /**
     * @param mixed[] $new_instance
     * @param mixed[] $old_instance
     * @return mixed[]
     */
    public function update($new_instance, $old_instance)
    {
        $instance = [];
        $instance['widget_exists'] = 1;

        return $instance;
    }

    /**
     * @param mixed[] $instance
     * @return void
     */
    public function form($instance)
    {
        echo '<br>';
    }

    /**
     * @param string $key
     * @return void
     */
    public function the_field($key)
    {
        echo $this->get_field($key);
    }

    public function registerForm()
    {
        if (function_exists('acf_add_local_field_group')) {
            $fields = ComponentFields::get($this->settings['component_name'], 'acfeditor');

            acf_add_local_field_group([
                'key' => 'group_widget_' . $this->settings['id_base'],
                'title' => 'Widget settings - ' . $this->settings['name'],
                'fields' => $fields,
                'location' => [
                    [
                        [
                            'param' => 'widget',
                            'operator' => '==',
                            'value' => $this->settings['id_base'],
                        ],
                    ],
                ],
                'menu_order' => 0,
                'position' => 'normal',
                'style' => 'default',
                'label_placement' => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen' => '',
                'active' => 1,
                'description' => '',
            ]);
        }
    }
}
