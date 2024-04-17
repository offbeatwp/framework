<?php
namespace OffbeatWP\Form\Fields;

final class EditorField extends AbstractField {
    public const FIELD_TYPE = 'editor';

    /** @return $this */
    public function basicToolbar(bool $useBasicToolbar = true)
    {
        $this->attribute('toolbar', ($useBasicToolbar) ? 'basic' : 'full');
        return $this;
    }

    /** @return $this */
    public function visualEditor()
    {
        $this->attribute('tabs', 'visual');
        return $this;
    }

    /** @return $this */
    public function textEditor()
    {
        $this->attribute('tabs', 'text');
        return $this;
    }

    /** @return $this */
    public function mediaUpload(bool $showMediaUploadButtons)
    {
        $this->attribute('media_upload', (int)$showMediaUploadButtons);
        return $this;
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}