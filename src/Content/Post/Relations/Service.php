<?php
namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Services\AbstractService;
use OffbeatWP\Content\Post\PostModel;
use OffbeatWP\Content\Post\WpQueryBuilder;

class Service extends AbstractService
{
    public function register () {
        add_filter('posts_clauses', [$this, 'insertRelationshipsSql'], 10, 2 );

        if(offbeat('console')->isConsole()) {
            offbeat('console')->register(Console\Install::class);
        }
    }

    public function insertRelationshipsSql($clauses, $query) {
        if (!isset($query->query_vars['relationships']) || empty($query->query_vars['relationships'])) return $clauses;

        $sql = $this->getSql($query);
            
        if (isset($sql['join']) && !empty($sql['join']))
            $clauses['join'] .= $sql['join'];

        if (isset($sql['where']) && !empty($sql['where']))
            $clauses['where'] .= $sql['where'];

        return $clauses;
    }

    private function getSql($query) {
        global $wpdb;

        $direction = null;
        if (isset($query->query_vars['relationships']['direction']) && $query->query_vars['relationships']['direction']) $direction = $query->query_vars['relationships']['direction'];

        $columnOn = 'relation_to';
        $columnWhere = 'relation_from';

        if ($direction == 'reverse') {
            $columnOn = 'relation_from';
            $columnWhere = 'relation_to';
        }

        $sql = [];
        $sql['join'] = " INNER JOIN {$wpdb->prefix}post_relationships AS pr ON ({$wpdb->posts}.ID = pr.{$columnOn}) ";

        $sql['where'] = " AND pr.key = '" . $query->query_vars['relationships']['key'] . "' AND pr.{$columnWhere} = " . $query->query_vars['relationships']['id'];

        return $sql;
    }
}