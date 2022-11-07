<?php

namespace OffbeatWP\Helpers;

use OffbeatWP\Content\Post\PostModel;
use OffbeatWP\Hooks\Events\BeforeDummyPostSaveEvent;
use UnexpectedValueException;

final class DummyHelper
{
    private const LOREM = ['lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing', 'elit', 'praesent', 'interdum', 'dictum', 'mi', 'non', 'egestas', 'nulla', 'in', 'lacus', 'sed', 'sapien', 'placerat', 'malesuada', 'at', 'erat', 'etiam', 'id', 'velit', 'finibus', 'viverra', 'maecenas', 'mattis', 'volutpat', 'justo', 'vitae', 'vestibulum', 'metus', 'lobortis', 'mauris', 'luctus', 'leo', 'feugiat', 'nibh', 'tincidunt', 'a', 'integer', 'facilisis', 'lacinia', 'ligula', 'ac', 'suspendisse', 'eleifend', 'nunc', 'nec', 'pulvinar', 'quisque', 'ut', 'semper', 'auctor', 'tortor', 'mollis', 'est', 'tempor', 'scelerisque', 'venenatis', 'quis', 'ultrices', 'tellus', 'nisi', 'phasellus', 'aliquam', 'molestie', 'purus', 'convallis', 'cursus', 'ex', 'massa', 'fusce', 'felis', 'fringilla', 'faucibus', 'varius', 'ante', 'primis', 'orci', 'et', 'posuere', 'cubilia', 'curae', 'proin', 'ultricies', 'hendrerit', 'ornare', 'augue', 'pharetra', 'dapibus', 'nullam', 'sollicitudin', 'euismod', 'eget', 'pretium', 'vulputate', 'urna', 'arcu', 'porttitor', 'quam', 'condimentum', 'consequat', 'tempus', 'hac', 'habitasse', 'platea', 'dictumst', 'sagittis', 'gravida', 'eu', 'commodo', 'dui', 'lectus', 'vivamus', 'libero', 'vel', 'maximus', 'pellentesque', 'efficitur', 'class', 'aptent', 'taciti', 'sociosqu', 'ad', 'litora', 'torquent', 'per', 'conubia', 'nostra', 'inceptos', 'himenaeos', 'fermentum', 'turpis', 'donec', 'magna', 'porta', 'enim', 'curabitur', 'odio', 'rhoncus', 'blandit', 'potenti', 'sodales', 'accumsan', 'congue', 'neque', 'duis', 'bibendum', 'laoreet', 'elementum', 'suscipit', 'diam', 'vehicula', 'eros', 'nam', 'imperdiet', 'sem', 'ullamcorper', 'dignissim', 'risus', 'aliquet', 'habitant', 'morbi', 'tristique', 'senectus', 'netus', 'fames', 'nisl', 'iaculis', 'cras', 'aenean'];

    public static function generateText(int $numberOfParagraphs): string
    {
        $paragraphs = [];
        for($p = 0; $p < $numberOfParagraphs; ++$p) {
            $nsentences = random_int(3, 8);
            $sentences = [];
            for($s = 0; $s < $nsentences; ++$s) {
                $sentences[] = self::generateSentence();
            }
            $paragraphs[] = implode(' ', $sentences);
        }

        return implode("\n\n", $paragraphs);
    }

    public static function generateSentence(): string
    {
        $frags = [];
        $commaChance = .33;

        while(true) {
            $numberOfWords = random_int(3, 15);
            $words = self::randomValues(self::LOREM, $numberOfWords);
            $frags[] = implode(' ', $words);
            if(self::randomFloat() >= $commaChance) {
                break;
            }
            $commaChance /= 2;
        }

        return ucfirst(implode(', ', $frags)) . '.';
    }

    public static function generateWord(): string
    {
        return self::LOREM[array_rand(self::LOREM)];
    }

    /**
     * @param string $type
     * @param positive-int $amount
     * @return void
     */
    public static function generatePosts(string $type, int $amount): void
    {
        foreach (get_object_taxonomies($type) as $taxonomy) {

        }

        $modelClass = offbeat('post-type')->getModelByPostType($type);
        if (!$modelClass) {
            throw new UnexpectedValueException("No model found for '". $type. "'");
        }

        for ($i = 0; $i < $amount; $i++)
        {
            /** @var PostModel $modelClass */
            $model = new $modelClass();
            $model->setTitle(self::generateSentence());
            $model->setContent(self::generateText(10));

            /** @var BeforeDummyPostSaveEvent $event */
            $event = apply_filters('offbeatwp/posts/dummy/before_save', new BeforeDummyPostSaveEvent($model));
            $event->model->saveOrFail();
        }
    }

    private static function randomFloat(): float
    {
        return random_int(0, PHP_INT_MAX - 1) / PHP_INT_MAX;
    }

    private static function randomValues(array $arr, int $count): array
    {
        $keys = array_rand($arr, $count);
        if($count === 1) {
            $keys = [$keys];
        }

        return array_intersect_key($arr, array_fill_keys($keys, null));
    }
}