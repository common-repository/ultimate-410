<?php

namespace TinyWeb\Ultimate410;

class UrlTable extends \WP_List_Table
{
    /**
     * @var CustomTable
     */
    private $custom_table;

    public function __construct(CustomTable $custom_table, $args = [])
    {
        $this->custom_table = $custom_table;
        $this->prepare_items();
        parent::__construct($args);
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();

        $this->_column_headers = [$columns, [], []];
        $perPage               = 100;
        $paged                 = (int)(array_key_exists('paged', $_GET) ? $_GET['paged'] : 0);
        $rules                 = $this->custom_table->getAllRulesDisplay(100, $paged * $perPage);
        $this->set_pagination_args([
            'total_items' => $rules['total'],
            'per_page'    => $perPage,
        ]);

        $this->items = $rules['rules'];
    }

    public function column_url($rule)
    {
        $request = htmlspecialchars(rawurldecode($rule->request));

        return $rule->regex ? sprintf('<code>%s</code>', $request) : '/' . $request;
    }

    public function column_regex($rule)
    {
        return $rule->regex
            ? sprintf(
                '<span class="dashicons dashicons-yes" title="%1$s" style="color: green;"></span><span class="screen-reader-text">%1$s</span>',
                __('This is a regex pattern.', 'ultimate-410')
            )
            : sprintf(
                '<span class="dashicons dashicons-no-alt" title="%1$s" style="color: lightgrey"></span><span class="screen-reader-text">%1$s</span>',
                __('This is not a regex pattern.', 'ultimate-410')
            );
    }

    protected function column_actions($rule)
    {
        $deleteAction = 'delete_410_entry';
        $actions      = [
            'delete' => sprintf(<<<'FORM'
<form style="display:inline-block;" method="post" action="%1$s" class="delete-410-entry">
%3$s
<input type="hidden" value="%4$s" name="%5$s[]">
<input type="hidden" value="%5$s" name="action">
<button type="submit" title="%2$s" style="cursor: pointer; padding: 0;"><span class="dashicons dashicons-no" style="color:red"></span></button>
</form>
FORM
                ,
                admin_url('admin-ajax.php'),
                __('Delete entry', 'ultimate-410'),
                wp_nonce_field($deleteAction, '_wpnonce', true, false),
                htmlspecialchars(rawurldecode($rule->request)),
                $deleteAction
            ),
        ];

        if (! $rule->regex) {
            $actions['test'] = sprintf(
                '<a href="%1$s" target="_blank" title="%2$s"><span class="dashicons dashicons-external"></span><span class="screen-reader-text">%2$s</span>',
                htmlspecialchars(rawurldecode(get_home_url(null, $rule->request))),
                __('Test this URL (opens in new tab).', 'ultimate-410')
            );
        }

        return implode(' ', $actions);
    }

    public function get_columns()
    {
        return [
            'cb'      => '<input type="checkbox" autocomplete="off" />',
            'url'     => __('URL', 'ultimate-410'),
            'regex'   => __('Regex', 'ultimate-410'),
            'actions' => __('Actions', 'ultimate-410'),
        ];
    }

    /**
     * @param \stdClass $item
     *
     * @return void
     */
    public function column_cb($item)
    {
        ?>
        <label class="screen-reader-text" for="cb-select-<?= (int) $item->id; ?>">
            <?php _e('Select Rule', 'ultimate-410'); ?>
        </label>
        <input id="cb-select-<?= (int) $item->id; ?>" type="checkbox" name="delete_ultimate_410_rules[]" value="<?= (int) $item->id; ?>"/>
        <?php
    }

    protected function bulk_actions($which = '')
    {
        if ($which === 'bottom') {
            $deleteAction = 'delete_410_entry';
            $ajaxUrl      = admin_url('admin-ajax.php');
            $buttonText   = __('Delete Selected Rules', 'ultimate-410');
            $nonceField   = wp_nonce_field($deleteAction, '_wpnonce', true, false);
            echo <<<BULK_DELETE
<form style="display:inline-block;" method="post" action="{$ajaxUrl}" id="ultimate-410-bulk-delete">
    {$nonceField}
    <input type="hidden" value="{$deleteAction}" name="action">
    <button type="submit" class="button action hide-if-no-js">
        {$buttonText}
    </button>
</form>
BULK_DELETE;

            $deleteAllAction = 'delete_410_all';
            $buttonText      = __('Delete All Rules', 'ultimate-410');
            $nonceField      = wp_nonce_field($deleteAllAction, '_wpnonce', true, false);

            echo <<<BULK_DELETE
<form style="display:inline-block;" method="post" action="{$ajaxUrl}" id="ultimate-410-delete-all">
    {$nonceField}
    <input type="hidden" value="{$deleteAllAction}" name="action">
    <button type="submit" class="button action hide-if-no-js" style="margin-left: 12px">
        {$buttonText}
    </button>
</form>
BULK_DELETE;
        }
    }
}
