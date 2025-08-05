<?php

namespace Roots\AcornLlmsTxt\Data;

use Illuminate\Support\Collection;

class Section
{
    protected Collection $links;

    public function __construct(
        protected string $name = ''
    ) {
        $this->links = collect();
    }

    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function addLink(Link $link): static
    {
        $this->links->push($link);

        return $this;
    }

    public function link(Link $link): static
    {
        return $this->addLink($link);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLinks(): Collection
    {
        return $this->links;
    }

    public function toString(): string
    {
        $output = ["## {$this->name}", ''];

        $this->links->each(function (Link $link) use (&$output) {
            $output[] = "- {$link->toString()}";
        });

        return implode("\n", $output);
    }
}
