<?php
namespace OffbeatWP\Tools\Acf\Layout;

class Admin {
    public function __construct($service)
    {
        add_action('admin_init',    [$this, 'disableEditorWhenLayoutIsActive'], 99);
        add_action('acf/input/admin_head', [$this, 'rdsn_acf_repeater_collapse']);
    }

    public  function disableEditorWhenLayoutIsActive()
    {
        global $pagenow, $post;

        if (
            $pagenow == 'post.php' &&
            isset($_GET['post']) &&
            is_numeric($_GET['post']) &&
            get_field('layout_enabled', $_GET['post']) === true
        ) {
            remove_post_type_support(get_post_type($_GET['post']), 'editor');
        }
    }

    public function rdsn_acf_repeater_collapse() {
    ?>
    <script type="text/javascript">
        jQuery(function($) {
            $('.acf-flexible-content .layout').addClass('-collapsed');

            $('[data-name="component"]').find('.acf-row:not(.acf-clone)').has('.-collapsed-target').addClass('-collapsed');

            $('[data-name="component"]').find('.acf-row:not(.acf-clone) .-collapsed-target').click(function () {
                $(this).closest('.acf-row').removeClass('-collapsed');
            });
        });
    </script>
    <?php
    }
}