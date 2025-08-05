<?php

namespace Roots\AcornLlmsTxt\Data;

class Link
{
    public function __construct(
        protected string $title = '',
        protected string $url = '',
        protected string $details = ''
    ) {}

    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function url(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function details(string $details): static
    {
        $this->details = $details;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getDetails(): string
    {
        return $this->details;
    }

    public function toString(): string
    {
        $link = "[{$this->title}]({$this->url})";

        return $this->details
            ? "{$link}: {$this->details}"
            : $link;
    }
}
