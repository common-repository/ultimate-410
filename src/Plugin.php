<?php

namespace TinyWeb\Ultimate410;

class Plugin
{
    /**
     * @var string
     */
    private static $home_url;
    /**
     * @var CustomTable
     */
    private $customTable;

    public function __construct(CustomTable $customTable)
    {
        $this->customTable = $customTable;
        self::$home_url    = get_home_url();
        add_action('parse_request', [$this, 'parseRequest']);
    }

    public function add410Template($templates)
    {
        // TODO add filter to allow filtering templates
        return $templates;
    }

    public function templateRedirect()
    {
        status_header(410, 'Gone');
        header('X-Robots-Tag: noindex, follow', true, 410);

        if ('HEAD' === $_SERVER['REQUEST_METHOD'] && apply_filters('exit_on_http_head', true)) {
            exit();
        }

        add_filter('410_template_hierarchy', [$this, 'add410Template'], 1);
        $template = get_query_template('410');
        $template = apply_filters('template_include', $template);

        if (empty($template)) {
            exit('410 - Gone');
        }

        require_once $template;
        exit();
    }

    public function parseRequest(\WP $wp)
    {
        $is410   = false;
        $request = strlen(strrchr($_SERVER['REQUEST_URI'], '/')) === 1
            ? trailingslashit($wp->request)
            : untrailingslashit($wp->request);

        if ($request === '') {
            return;
        }

        foreach ($this->customTable->getAllRules() as $rule) {
            $tester = new RuleTester($rule);
            if ($tester->test($request)) {
                $is410 = true;
                break;
            }
        }

        if (!$is410) {
            return;
        }

        add_action('parse_query', [$this, 'parseQuery']);
    }

    public static function sanitize($value)
    {
        $value = trim($value);
        $value = str_replace(self::$home_url, '', $value);
        $value = ltrim($value, '/');
        $value = rawurlencode($value);

        $convertBack = [
            '%5C' => '/',
            '%2F' => '/',
            '%3F' => '?',
            '%21' => '!',
            '%23' => '#',
            '%26' => '&',
            '%27' => "'",
            '%28' => '(',
            '%29' => ')',
            '%3A' => ':',
            '%3D' => '=',
            '%40' => '@',
            '%5B' => '[',
            '%5D' => ']',
        ];

        $value = str_replace(array_keys($convertBack), $convertBack, $value);
        $value = parse_url($value, PHP_URL_PATH);
        $value = ltrim($value, '/');

        return $value;
    }

    public function parseQuery(\WP_Query $query)
    {
        if (!$query->is_main_query()) {
            return;
        }

        add_action('template_redirect', [$this, 'templateRedirect']);
    }
}
