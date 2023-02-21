<?php
namespace OffbeatWP\Form\Fields;

class Editor extends AbstractField {
    public const FIELD_TYPE = 'editor';

    public function basicToolbar(bool $useBasicToolbar = true): self
    {
        $this->attribute('toolbar', ($useBasicToolbar) ? 'basic' : 'full');
        return $this;
    }

    public function visualEditor(): self
    {
        $this->attribute('tabs', 'visual');
        return $this;
    }

    public function textEditor(): self
    {
        $this->attribute('tabs', 'text');
        return $this;
    }

    public function mediaUpload(bool $showMediaUploadButtons): self
    {
        $this->attribute('media_upload', (int)$showMediaUploadButtons);
        return $this;
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}