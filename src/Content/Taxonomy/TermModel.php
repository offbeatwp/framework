<?php
namespace OffbeatWP\Content\Taxonomy;

use Error;
use Illuminate\Support\Traits\Macroable;
use OffbeatWP\Content\Common\OffbeatModel;
use OffbeatWP\Content\Post\PostModel;
use OffbeatWP\Content\Post\WpQueryBuilder;
use OffbeatWP\Content\Traits\BaseModelTrait;
use OffbeatWP\Content\Traits\GetMetaTrait;
use WP_Error;
use WP_Taxonomy;
use WP_Term;

class TermModel extends OffbeatModel
{
    use BaseModelTrait;
    use GetMetaTrait;
    use Macroable {
        Macroable::__call as macroCall;
        Macroable::__callStatic as macroCallStatic;
    }

    protected WP_Term $wpTerm;
    protected ?array $meta = null;

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

    public function getLink(): string|WP_Error
    {
        return get_term_link($this->wpTerm);
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
        if ($this->meta === null) {
            $this->meta = get_term_meta($this->getId()) ?: [];
        }

        return $this->meta;
    }

    /**
     * @param string $key
     * @param bool $single
     * @return mixed
     */
    public function getMeta(string $key, bool $single = true)
    {
        return get_term_meta($this->getId(), $key, $single);
    }

    /**
     * @param string|string[]|null $postTypes
     * @return WpQueryBuilder<PostModel>
     */
    public function getPosts($postTypes = null): WpQueryBuilder
    {
        global $wp_taxonomies;

        // If no posttypes defined, get posttypes where the taxonomy is assigned to
        if (!$postTypes) {
            $postTypes = isset($wp_taxonomies[static::TAXONOMY]) ? $wp_taxonomies[static::TAXONOMY]->object_type : ['any'];
        }

        return (new WpQueryBuilder())->wherePostType($postTypes)->whereTerm(static::TAXONOMY, $this->getId(), 'term_id');
    }

    /**
     * Removes a term from the database.<br>
     * If the term is a parent of other terms, then the children will be updated to that term's parent.<br>
     * Metadata associated with the term will be deleted.
     * @return bool Whetever the term was deleted.
     */
    public function delete(): bool
    {
        return wp_delete_term($this->getId(), $this->getTaxonomy()) === true;
    }

    /**
     * Retrieves the current post from the wordpress loop, provided the PostModel is or extends the PostModel class that it is called on.
     * @return static|null
     */
    public static function current()
    {
        $taxonomy = offbeat('taxonomy')->get();
        return ($taxonomy instanceof static) ? $taxonomy : null;
    }

    public function getTaxonomyInstance(): ?WP_Taxonomy
    {
        return get_taxonomy($this->wpTerm->taxonomy) ?: null;
    }

    public function count(): int
    {
        return $this->wpTerm->count;
    }

    /** @return TermsCollection<static> Empty terms <b>will</b> be included. */
    public static function all(): TermsCollection
    {
        return static::query()->excludeEmpty(false)->get();
    }

    /**
     * Checks if a model with the given ID exists.
     * @param int|null $id
     * @return bool
     */
    public static function exists(?int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        return static::query()->include([$id])->exists();
    }

    /** @return TermQueryBuilder<static> */
    public static function query(): TermQueryBuilder
    {
        return new TermQueryBuilder(static::class);
    }
}
