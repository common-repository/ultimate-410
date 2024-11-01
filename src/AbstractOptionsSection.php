<?php

namespace TinyWeb\Ultimate410;

abstract class AbstractOptionsSection
{
    const ID = '';
    /**
     * @var string
     */
    protected $title = '';
    /**
     * @var OptionsPage
     */
    protected $optionsPage;
    /**
     * @var string
     */
    protected $viewsPath;
    /**
     * @var string
     */
    protected $submit;

    /**
     * AbstractOptionsSection constructor.
     *
     * @param OptionsPage $page
     * @param string $pluginPath
     */
    public function __construct(OptionsPage $page, $pluginPath)
    {
        $this->viewsPath   = trailingslashit($pluginPath).'views/';
        $this->optionsPage = $page;

        add_action('admin_init', [$this, 'addSection']);
    }

    public function getId()
    {
        return static::ID;
    }

    public function getTitle()
    {
        return $this->title;
    }

    /**
     *
     */
    public function addSection()
    {
        add_settings_section($this->getId(), $this->getTitle(), [
            $this,
            'callback',
        ], $this->optionsPage->getId());
    }

    /**
     * Callback for section - include the view.
     */
    public function callback()
    {
        $this->includeView();
    }

    /**
     * @param array $args
     * @param string $name
     */
    protected function includeView($args = [], $name = '')
    {
        if (empty($name)) {
            $name = $this->getId();
        }
        $fileArr = preg_split('/(?=[A-Z-_])/', $name);
        $fileArr = array_map(function ($value) {
            return trim($value, '-_');
        }, $fileArr);
        $fileArr = array_map('strtolower', $fileArr);

        $args = array_merge([
            'id'    => $this->getId(),
            'title' => $this->getTitle(),
            'page'  => $this->optionsPage->getId(),
        ], $args);

        include $this->viewsPath.'section/'.implode('-', $fileArr).'.php';
    }

    /**
     * Get submit button value.
     *
     * @return string
     */
    public function getSubmit()
    {
        return empty($this->submit) ? __('Update Options', 'ultimate-410') : $this->submit;
    }
}
