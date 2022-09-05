<?php
namespace OffbeatWP\Form\Fields;

class File extends AbstractField {
    public const FIELD_TYPE = 'file';

    public function allowedFileTypes(string $allowedFileTypes): File
    {
        $this->setAttribute('allowed_file_types', $allowedFileTypes);

        return $this;
    }
}