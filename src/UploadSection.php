<?php

namespace TinyWeb\Ultimate410;

final class UploadSection extends AbstractOptionsSection
{
    const ID = 'ultimate-410-upload';

    /**
     * BasicOptionsSection constructor.
     *
     * {@inheritdoc}
     */
    public function __construct(OptionsPage $page, $pluginPath)
    {
        $this->title           = __('CSV Upload', 'ultimate-410');
        $this->submit          = __('Upload', 'ultimate-410');
        $page->addForm($this);
        parent::__construct($page, $pluginPath);
    }
}
