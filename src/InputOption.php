<?php

namespace TinyWeb\Ultimate410;

final class InputOption extends AbstractOption
{
    const ID = 'ultimate_410_regex';
    private $customTable;

    /**
     * AbstractOption constructor.
     *
     * @inheritDoc
     */
    public function __construct(OptionsPage $page, AbstractOptionsSection $section, CustomTable $customTable, $pluginPath)
    {
        $this->customTable = $customTable;
        $this->title       = __('Add URL/Regex', 'ultimate-410');
        $this->args        = [
            'blogurl' => trailingslashit(get_home_url()),
        ];
        add_filter('pre_update_option_'.self::ID, [$this, 'save'], 10, 2);
        parent::__construct($page, $section, $pluginPath);
    }

    public function callback()
    {
        $this->includeView();
    }

    public function sanitize($value, $option, $original)
    {
        return Plugin::sanitize($value);
    }

    public function save($value, $oldValue)
    {
        if (empty($value)) {
            add_settings_error($this->getId(), 'not-saved', __('Empty entry.', 'ultimate-410'));

            return $oldValue;
        }
        $regex = isset($_POST[$this->getId().'_regex']);
        if ($regex) {
            $delimiter = sanitize_text_field($_POST[$this->getId().'_regex_delimiter']);
            $modifier  = sanitize_text_field($_POST[$this->getId().'_regex_modifier']);
            // make sure delimiters in regex are escaped
            $value = preg_replace('/(.*?)(?<!\\\)'.preg_quote($delimiter, '/').'(.*?)/', '$1\\'.$delimiter.'$2', $value);
            // put the
            $value = $delimiter.$value.$delimiter.$modifier;

            $test = @preg_match($value, '');
            if ($test === false) {
                $error   = preg_last_error();
                $message = sprintf(__('Invalid regex %s', 'ultimate-410'), '<code>'.$value.'</code>');
                if (! empty($error)) {
                    $message .= ' <code>'.array_flip(get_defined_constants(true)['pcre'])[$error].'</code>.';
                }
                add_settings_error($this->getId(), 'invalid-regex', $message);

                return $oldValue;
            }
        }

        if (preg_match('|^https?://|i', $value)) {
            add_settings_error($this->getId(), 'invalid-path', sprintf(
            /** Translators: %s is home_url() */
                __('You can only add URLs on the WordPress domain: %s', 'ultimate-410'), get_home_url()
            ));

            return $oldValue;
        }

        if (! $this->customTable->insert($value, $regex)) {
            add_settings_error($this->getId(), 'not-saved', __('Entry already exists.', 'ultimate-410'));
        }

        return $oldValue;
    }
}
