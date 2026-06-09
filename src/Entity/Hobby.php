<?php

namespace App\Entity;

class Hobby
{
    public function __construct(
        private string $language,
        private string $title,
        private string $description,
        private string $icon,
    ) {
        // Intentionally left blank.
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }
}
