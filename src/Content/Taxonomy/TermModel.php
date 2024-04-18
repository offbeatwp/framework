<?php
namespace OffbeatWP\Content\Taxonomy;

use InvalidArgumentException;
use OffbeatWP\Content\Common\AbstractOffbeatModel;
use OffbeatWP\Content\Post\PostModel;
use OffbeatWP\Content\Post\PostQueryBuilder;
use OffbeatWP\Exceptions\WpErrorException;
use OffbeatWP\Support\Wordpress\Taxonomy;
use RuntimeException;
use WP_Error;
use WP_Taxonomy;
use WP_Term;

class TermModel extends AbstractOffbeatModel
{
    public const TAXONOMY = '';

    protected readonly WP_Term $wpTerm;
    protected ?array $metas = null;

    final private function __construct(WP_Term $term)
    {
        if ($term->term_id <= 0) {
            throw new InvalidArgumentException('Cannot create TermModel object: Invalid ID ' . $term->term_id);
        }

        if (!in_array($term->taxonomy, (array)static::TAXONOMY, true)) {
            throw new InvalidArgumentException('Cannot create TermModel object: Invalid Post Type');
        }

        $this->wpTerm = $term;
        $this->init();
    }

    /** This method is called at the end of the TermModel constructor */
    protected function init(): void
    {
        // Does nothing unless overriden by parent
    }

    public function __clone()
    {
        $this->wpTerm = clone $this->wpTerm;
    }

    final public function getId(): int
    {
        return $this->wpTerm->term_id;
    }

    final public function getName(): string
    {
        return $this->wpTerm->name;
    }

    final public function getSlug(): string
    {
        return $this->wpTerm->slug ?? '';
    }

    final public function getDescription(): string
    {
        return $this->wpTerm->description;
    }

    final public function getLink(): string
    {
        $link = get_term_link($this->wpTerm);

        if ($link instanceof WP_Error) {
            throw new WpErrorException($link->get_error_message());
        }

        return $link;
    }

    final public function getTaxonomy(): string
    {
        return $this->wpTerm->taxonomy;
    }

    final public function getParentId(): int
    {
        return $this->wpTerm->parent;
    }

    final public function getParent(): ?static
    {
        if ($this->getParentId()) {
            return static::query()->findById($this->getParentId()) ?: null;
        }

        return null;
    }

    /** @return int[] */
    final public function getAncestorIds(): array
    {
        return get_ancestors($this->getId(), $this->getTaxonomy(), 'taxonomy');
    }

    /**
     * Retrieves the URL for editing a given term.
     * @return string|null The edit term link URL for the given term, or null on failure.
     */
    final public function getEditLink(): ?string
    {
        return get_edit_term_link($this->wpTerm);
    }

    /** @return static[] */
    final public function getAncestors(): array
    {
        return array_map(fn(int $ancestorId) => static::find($ancestorId), $this->getAncestorIds());
    }

    final public function getMetas(): array
    {
        if ($this->metas === null) {
            $meta = get_term_meta($this->getId());
            if (!is_array($meta)) {
                throw new RuntimeException('Could not retrieve meta for non-existent term #' . $this->getId());
            }

            $this->metas = $meta;
        }

        return $this->metas;
    }

    final public function getMeta(string $key, bool $single = true): mixed
    {
        return $this->getMetas()[$key] ?? null;
    }

    /**
     * @param string[] $postTypes
     * @return PostQueryBuilder<PostModel>
     */
    final public function getPosts(array $postTypes = []): PostQueryBuilder
    {
        // If no posttypes defined, get posttypes where the taxonomy is assigned to
        if (!$postTypes) {
            global $wp_taxonomies;
            $postTypes = isset($wp_taxonomies[static::TAXONOMY]) ? $wp_taxonomies[static::TAXONOMY]->object_type : ['any'];
        }

        return (new PostQueryBuilder(PostModel::class))->wherePostType($postTypes)->whereTerm(static::TAXONOMY, [$this->getId()], 'term_id');
    }

    /** Retrieves the current term from the WordPress loop, provided the TermModel is or extends the TermModel class that it is called on. */
    final public static function current(): ?static
    {
        $taxonomy = offbeat(Taxonomy::class)->get();
        return ($taxonomy instanceof static) ? $taxonomy : null;
    }

    final public function getTaxonomyObject(): WP_Taxonomy
    {
        $tax = get_taxonomy($this->wpTerm->taxonomy);
        if (!$tax) {
            throw new RuntimeException('Taxonomy ' . $tax . ' does not exist.');
        }

        return $tax;
    }

    final public function count(): int
    {
        return $this->wpTerm->count;
    }

    final public function edit(): TermBuilder
    {
        return TermBuilder::update($this->wpTerm->term_id, $this->wpTerm->taxonomy);
    }

    /** @return mixed[] */
    public static function defaultQueryArgs(): array
    {
        return ['hide_empty' => false];
    }

    /** @return TermQueryBuilder<static> */
    final public static function query(): TermQueryBuilder
    {
        return new TermQueryBuilder(static::class);
    }
}
