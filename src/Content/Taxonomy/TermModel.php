<?php
namespace OffbeatWP\Content\Taxonomy;

use OffbeatWP\Content\Common\OffbeatModel;
use OffbeatWP\Content\Post\PostModel;
use OffbeatWP\Content\Post\WpQueryBuilder;
use OffbeatWP\Content\Traits\BaseModelTrait;
use OffbeatWP\Content\Traits\GetMetaTrait;
use OffbeatWP\Exceptions\WpErrorException;
use RuntimeException;
use WP_Error;
use WP_Taxonomy;
use WP_Term;

class TermModel extends OffbeatModel
{
    use BaseModelTrait;
    use GetMetaTrait;

    protected WP_Term $wpTerm;
    protected ?array $metas = null;

    private function __construct(WP_Term $term)
    {
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

    public function getId(): int
    {
        return $this->wpTerm->term_id;
    }

    public function getName(): string
    {
        return $this->wpTerm->name;
    }

    public function getSlug(): string
    {
        return $this->wpTerm->slug ?? '';
    }

    public function getDescription(): string
    {
        return $this->wpTerm->description;
    }

    public function getLink(): string
    {
        $link = get_term_link($this->wpTerm);

        if ($link instanceof WP_Error) {
            throw new WpErrorException($link->get_error_message());
        }

        return $link;
    }

    public function getTaxonomy(): string
    {
        return $this->wpTerm->taxonomy;
    }

    public function getParentId(): ?int
    {
        return ($this->wpTerm->parent) ?: null;
    }

    public function getParent(): ?static
    {
        if ($this->getParentId()) {
            return static::query()->findById($this->getParentId()) ?: null;
        }

        return null;
    }

    /** @return int[] */
    public function getAncestorIds(): array
    {
        return get_ancestors($this->getId(), $this->getTaxonomy(), 'taxonomy');
    }

    /**
     * Retrieves the URL for editing a given term.
     * @return string|null The edit term link URL for the given term, or null on failure.
     */
    public function getEditLink(): ?string
    {
        return get_edit_term_link($this->wpTerm);
    }

    /** @return static[] */
    public function getAncestors(): array
    {
        return array_map(fn(int $ancestorId) => static::find($ancestorId), $this->getAncestorIds());
    }

    public function getMetas(): array
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

    public function getMeta(string $key, bool $single = true): mixed
    {
        return $this->getMetas()[$key] ?? null;
    }

    public function hasMeta(string $key): bool
    {
        return array_key_exists($key, $this->getMetas());
    }

    /**
     * @param string[] $postTypes
     * @return WpQueryBuilder<PostModel>
     */
    public function getPosts(array $postTypes = []): WpQueryBuilder
    {
        // If no posttypes defined, get posttypes where the taxonomy is assigned to
        if (!$postTypes) {
            global $wp_taxonomies;
            $postTypes = isset($wp_taxonomies[static::TAXONOMY]) ? $wp_taxonomies[static::TAXONOMY]->object_type : ['any'];
        }

        return (new WpQueryBuilder(PostModel::class))->wherePostType($postTypes)->whereTerm(static::TAXONOMY, $this->getId(), 'term_id');
    }

    /** Retrieves the current term from the WordPress loop, provided the TermModel is or extends the TermModel class that it is called on. */
    public static function current(): ?static
    {
        $taxonomy = offbeat('taxonomy')->get();
        return ($taxonomy instanceof static) ? $taxonomy : null;
    }

    public function getTaxonomyObject(): WP_Taxonomy
    {
        $tax = get_taxonomy($this->wpTerm->taxonomy);
        if (!$tax) {
            throw new RuntimeException('Taxonomy ' . $tax . ' does not exist.');
        }

        return $tax;
    }

    public function count(): int
    {
        return $this->wpTerm->count;
    }

    public function edit(): TermBuilder
    {
        return TermBuilder::update($this->wpTerm->term_id, $this->wpTerm->taxonomy);
    }

    /** @return TermQueryBuilder<static> */
    public static function query(): TermQueryBuilder
    {
        return new TermQueryBuilder(static::class);
    }
}
