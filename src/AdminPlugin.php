<?php

namespace TinyWeb\Ultimate410;

class AdminPlugin
{
    const TRANSIENT = 'ultimate-410-notice';
    /**
     * @var CustomTable
     */
    private $customTable;

    public function __construct(CustomTable $customTable)
    {
        $this->customTable = $customTable;
        add_action('admin_notices', [$this, 'missingTableNotice']);
        add_action('admin_notices', [$this, 'printNotice']);
        add_action('wp_trash_post', [$this, 'postTrashed']);
        add_action('untrashed_post', [$this, 'postRestored']);
        add_action('before_delete_post', [$this, 'postDelete']);
        add_action('delete_term_taxonomy', [$this, 'termDelete']);
    }

    public function postRestored($postId)
    {
        if (! $this->isPostViewable($postId)) {
            return;
        }

        $this->customTable->deleteMultiple([$this->getPostPath($postId)]);
    }

    public function postTrashed($postId)
    {
        if (! $this->isPostViewable($postId)) {
            return;
        }

        $this->registerNotice(
        /** translators: %1$s is the label of the post type. */
            sprintf(__('You just trashed a %1$s.', 'wordpress-seo'), $this->getPostTypeLabel($postId)),
            $this->getPostPath($postId)
        );
    }

    public function termDelete($termId)
    {
        $term = get_term($termId);

        if (! $term || is_wp_error($term)) {
            return;
        }

        $taxonomy = get_taxonomy($term->taxonomy);
        if (! $taxonomy) {
            return;
        }

        if (! $taxonomy->publicly_queryable || ! $taxonomy->public) {
            return;
        }

        $this->registerNotice(
        /** translators: %1$s is the label of the post type. */
            sprintf(__('You just trashed a %1$s.', 'wordpress-seo'), $taxonomy->labels->singular_name),
            Plugin::sanitize(get_term_link($term, $taxonomy))
        );
    }

    public function postDelete($postId)
    {
        if (! $this->isPostViewable($postId, ['trash'])) {
            return;
        }

        $path = $this->getPostPath($postId);
        if ($this->checkIfPresent($path)) {
            return;
        }

        $this->registerNotice(
        /** translators: %1$s is the label of the post type. */
            sprintf(__('You just deleted a %1$s.', 'wordpress-seo'), $this->getPostTypeLabel($postId)),
            $path
        );
    }

    /**
     * Checks if the post is viewable.
     *
     * @param int $postId
     * @param array $additionalStatus
     *
     * @return bool
     */
    private function isPostViewable($postId, $additionalStatus = [])
    {
        $postType  = get_post_type($postId);
        $postTypes = array_keys(array_filter(get_post_types(['public' => true], 'objects'), function ($postType) {
            return $postType->publicly_queryable || ($postType->_builtin && $postType->public);
        }));
        if (! in_array($postType, $postTypes)) {
            return false;
        }

        if (! in_array(get_post_status($postId), array_merge(['publish', 'static', 'private'], $additionalStatus), true)) {
            return false;
        }

        return true;
    }

    /**
     * Gets the singular post_type label.
     *
     * @param $postId
     *
     * @return mixed
     */
    private function getPostTypeLabel($postId)
    {
        $postType = get_post_type_object(get_post_type($postId));

        return $postType->labels->singular_name;
    }

    private function registerNotice($message, $path)
    {
        $addAction = 'add_410_entry';
        $message   = sprintf('%s %s %s',
            $message,
            sprintf(__('If you want add this URL (%s) to the list of 410 URLs: ', 'ultimate-410'), '<code>/'.$path.'</code>'),
            sprintf(<<<'FORM'
<form style="display:inline-block;" method="post" action="%1$s" class="add-410-entry">
%2$s
<input type="hidden" value="%3$s" name="%4$s">
<input type="hidden" value="%4$s" name="action">
<button class="button button-secondary" type="submit" style="vertical-align: middle;">%5$s</button>
</form>
FORM
                ,
                admin_url('admin-ajax.php'),
                wp_nonce_field($addAction, '_wpnonce', true, false),
                $path,
                $addAction,
                __('Click here', 'ultimate-410')
            )
        );

        $transient = get_transient(self::TRANSIENT);
        if (empty($transient)) {
            $transient = [];
        }
        $transient[] = $message;
        set_transient(self::TRANSIENT, $transient);
    }

    public function printNotice()
    {
        $messages = get_transient(self::TRANSIENT);
        if (empty($messages)) {
            return;
        }

        require_once __DIR__.'/../views/admin-notice.php';

        delete_transient(self::TRANSIENT);
    }

    public function missingTableNotice()
    {
        if (!$this->customTable->tableExists()) {
            $tableName  = $this->customTable->getTableName();
            require_once __DIR__.'/../views/missing-table-notice.php';
        }
    }

    private function getPostPath($postId)
    {
        return preg_replace('/(.+?)__trashed(\/)?$/', '$1$2', Plugin::sanitize(get_the_permalink($postId)));
    }

    private function checkIfPresent($path)
    {
        $allRules = array_map(function ($rule) {
            return $rule->request;
        }, $this->customTable->getAllRules());

        return in_array(Plugin::sanitize($path), $allRules, true);
    }
}
