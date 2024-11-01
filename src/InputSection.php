<?php

namespace TinyWeb\Ultimate410;

final class InputSection extends AbstractOptionsSection
{
    const ID = 'ultimate-410-input';

    /**
     * BasicOptionsSection constructor.
     *
     * {@inheritdoc}
     */
    public function __construct(OptionsPage $page, $pluginPath)
    {
        $this->title = __('Add URL', 'ultimate-410');
        $page->addForm($this);
        parent::__construct($page, $pluginPath);
    }
}
