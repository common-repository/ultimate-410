<?php

namespace TinyWeb\Ultimate410;

final class OptionsPage
{
    /**
     * @var string
     */
    private $viewsPath;

    const SLUG = 'ultimate-410';
    const PAGE_TITLE = 'Ultimate 410';
    private $forms = [];
    private $urlTable;

    /**
     * OptionsPage constructor.
     *
     * @param string $pluginPath
     */
    public function __construct($pluginPath)
    {
        $this->viewsPath = trailingslashit($pluginPath).'views/';
        add_action('admin_menu', [$this, 'addPage']);
    }


    /**
     * Set WP_List_Table if frontend request.
     *
     * @param \WP_List_Table $urlTable
     */
    public function setTable(\WP_List_Table $urlTable)
    {
        $this->urlTable = $urlTable;
    }

    /**
     * Wrapper for add_media_section.
     *
     * Add the page in media section
     */
    public function addPage()
    {
        add_options_page(self::PAGE_TITLE, 'Ultimate 410', 'manage_options', self::SLUG, [
            $this,
            'callback',
        ]);
    }

    /**
     * @param AbstractOptionsSection $form id and title for section
     *
     * @return self
     */
    public function addForm(AbstractOptionsSection $form)
    {
        $this->forms[$form->getId()] = [
            'title'  => $form->getTitle(),
            'submit' => $form->getSubmit(),
        ];

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return self::SLUG;
    }

    /**
     * Include the view
     */
    public function callback()
    {
        $args    = [
            'id'         => self::SLUG,
            'title'      => self::PAGE_TITLE,
            'forms'      => $this->forms,
            'active_tab' => array_key_exists('tab', $_GET) ? sanitize_text_field($_GET['tab']) : key($this->forms),
        ];
        $entries = $this->urlTable;

        include $this->viewsPath.'page/'.self::SLUG.'.php';
    }
}
