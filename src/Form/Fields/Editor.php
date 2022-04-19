<?php
namespace OffbeatWP\Form\Fields;

class Editor extends AbstractField {
    public const FIELD_TYPE = 'editor';

    public function basicToolbar(bool $useBasicToolbar = true) {
        $this->attribute('toolbar', ($useBasicToolbar) ? 'basic' : 'full');
        return $this;
    }

    public function visualEditor()
    {
        $this->attribute('tabs', 'visual');
        return $this;
    }

    public function textEditor()
    {
        $this->attribute('tabs', 'text');
        return $this;
    }

    public function mediaUpload(bool $showMediaUploadButtons)
    {
        $this->attribute('media_upload', (int)$showMediaUploadButtons);
        return $this;
    }
}