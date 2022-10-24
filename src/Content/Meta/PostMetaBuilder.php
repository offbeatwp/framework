<?php

namespace OffbeatWP\Content\Meta;

class PostMetaBuilder extends MetaBuilder
{
    public function register(): bool
    {
        $success = register_post_meta($this->subType, $this->metaKey, [
            'type' => strtolower($this->metaType)
        ]);

        if ($success && function_exists('register_graphql_field') && function_exists('get_field')) {
            add_action('graphql_register_types', function () {
                register_graphql_field($this->subType, 'cardsAction', [
                    'type' => $this->metaType,
                    'description' => $this->description,
                    'resolve' => fn($post) => wp_json_encode(get_field('cards_action', $post->ID) ?: [])
                ]);
            });
        }

        return $success;
    }

    protected function getType(): string
    {
        return 'post';
    }
}