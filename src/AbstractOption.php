<?php

namespace TinyWeb\Ultimate410;

abstract class AbstractOption
{
    const ID = '';

    /**
     * @var string
     */
    protected $title = '';

    /**
     * Extra arguments passed to the view.
     * @var array
     */
    protected $args;

    /**
     * Extra arguments used when outputting the field wrapper.
     *
     * @var array
     */
    protected $outputArgs = [];

    /**
     * @var string path to views dir
     */
    protected $viewsPath;

    /**
     * @var OptionsPage
     */
    protected $optionsPage;

    /**
     * @var AbstractOptionsSection
     */
    protected $optionsGroup;

    /**
     * AbstractOption constructor.
     *
     * @param OptionsPage $page The parent page
     * @param AbstractOptionsSection $section The containing section
     * @param string $pluginPath Plugin base path
     */
    public function __construct(OptionsPage $page, AbstractOptionsSection $section, $pluginPath)
    {
        $this->optionsPage  = $page;
        $this->optionsGroup = $section;
        $this->viewsPath    = trailingslashit($pluginPath).'views/';

        add_action('admin_init', [$this, 'addField']);
        add_action('admin_init', [$this, 'registerSetting']);
        add_action('sanitize_option_'.static::ID, [$this, 'sanitize'], 10, 3);
    }

    /**
     * Register the option.
     */
    public function registerSetting()
    {
        register_setting($this->optionsGroup->getId(), static::ID);
    }

    /**
     * Add settings field.
     */
    public function addField()
    {
        add_settings_field(
            static::ID,
            $this->getTitle(),
            [$this, 'callback'],
            $this->optionsPage->getId(),
            $this->optionsGroup->getId(),
            array_merge([
                'label_for' => $this->getId(),
            ], $this->outputArgs)
        );
    }

    public function getId()
    {
        return static::ID;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getView($name = '')
    {
        if (empty($name)) {
            $name = $this->getId();
        }
        $fileArr = preg_split('/(?=[A-Z-_])/', $name);
        $fileArr = array_map(function ($value) {
            return trim($value, '-_');
        }, $fileArr);
        $fileArr = array_map('strtolower', $fileArr);

        return $this->viewsPath.'field/'.implode('-', $fileArr).'.php';
    }

    /**
     * Include the view and pass optional variables.
     *
     * @param string $name name for view to use
     */
    protected function includeView($name = '')
    {
        $args = array_merge([
            'id'    => $this->getId(),
            'title' => $this->getTitle(),
        ], $this->args);

        include $this->getView($name);
    }

    abstract public function callback();

    abstract public function save($value, $oldValue);

    abstract public function sanitize($value, $option, $original);
}
