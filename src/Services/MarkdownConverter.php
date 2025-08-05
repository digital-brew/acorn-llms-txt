<?php

namespace Roots\AcornLlmsTxt\Services;

use League\HTMLToMarkdown\HtmlConverter;

class MarkdownConverter
{
    protected HtmlConverter $converter;

    public function __construct()
    {
        $this->converter = new HtmlConverter([
            'header_style' => 'atx', // Use # style headers instead of underline
            'strip_tags' => true,
            'remove_nodes' => 'script style',
            'hard_break' => true,
            'strip_placeholder_links' => true,
        ]);
    }

    public function convert(string $html): string
    {
        // Process WordPress shortcodes first
        $html = $this->processShortcodes($html);

        // Convert HTML to Markdown
        $markdown = $this->converter->convert($html);

        // Clean up the markdown
        return $this->cleanMarkdown($markdown);
    }

    protected function processShortcodes(string $html): string
    {
        // Remove WordPress shortcodes for cleaner output
        $html = preg_replace('/\[.*?\]/', '', $html);

        return $html;
    }

    protected function cleanMarkdown(string $markdown): string
    {
        // Remove excessive newlines
        $markdown = preg_replace('/\n{3,}/', "\n\n", $markdown);

        // Remove unnecessary underscore escaping (for things like WP_DEBUG)
        $markdown = preg_replace('/(\w)\\\_(\w)/', '$1_$2', $markdown);

        // Trim whitespace
        $markdown = trim($markdown);

        return $markdown;
    }
}
