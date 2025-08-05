<?php

namespace Roots\AcornLlmsTxt\Data;

use Illuminate\Support\Collection;

class LlmsTxtDocument
{
    protected Collection $sections;

    public function __construct(
        protected string $title = '',
        protected string $description = '',
        protected string $details = ''
    ) {
        $this->sections = collect();
    }

    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function details(string $details): static
    {
        $this->details = $details;

        return $this;
    }

    public function addSection(Section $section): static
    {
        $this->sections->push($section);

        return $this;
    }

    public function section(Section $section): static
    {
        return $this->addSection($section);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDetails(): string
    {
        return $this->details;
    }

    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function getSectionByName(string $name): ?Section
    {
        return $this->sections->first(fn (Section $section) => $section->getName() === $name);
    }

    public function toString(): string
    {
        $output = ["# {$this->title}"];

        if ($this->description) {
            $output[] = '';
            $output[] = "> {$this->description}";
        }

        if ($this->details) {
            $output[] = '';
            $output[] = $this->details;
        }

        $this->sections->each(function (Section $section) use (&$output) {
            $output[] = '';
            $output[] = $section->toString();
        });

        return implode("\n", $output);
    }

    public function toFile(string $path): bool
    {
        return file_put_contents($path, $this->toString()) !== false;
    }
}
