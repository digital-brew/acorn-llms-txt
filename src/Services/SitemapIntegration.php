<?php

namespace Roots\AcornLlmsTxt\Services;

class SitemapIntegration
{
    public function __construct()
    {
        // Hook into WordPress and SEO plugin sitemap systems
        add_action('init', [$this, 'registerIntegrations']);
    }

    public function registerIntegrations(): void
    {
        // WordPress core sitemap integration
        add_filter('wp_sitemaps_add_provider', [$this, 'addToWordPressSitemaps'], 10, 2);

        // Yoast SEO integration
        add_filter('wpseo_sitemap_index', [$this, 'addToYoastSitemaps']);

        // RankMath integration
        add_filter('rank_math/sitemap/index', [$this, 'addToRankMathSitemaps']);

        // The SEO Framework integration
        add_filter('the_seo_framework_sitemap_endpoint_list', [$this, 'addToSeoFrameworkSitemaps']);
    }

    public function addToWordPressSitemaps($provider, $name): bool
    {
        // Don't interfere with existing providers
        if ($name !== 'llms') {
            return $provider;
        }

        // Register our custom sitemap provider with WordPress core
        return true;
    }

    public function addToYoastSitemaps(string $sitemapIndex): string
    {
        if (! $this->shouldIncludeSitemap()) {
            return $sitemapIndex;
        }

        $sitemapUrl = home_url().'/llms-sitemap.xml';
        $lastmod = date('Y-m-d\TH:i:s+00:00');

        $sitemapEntry = "\t<sitemap>\n";
        $sitemapEntry .= "\t\t<loc>".esc_url($sitemapUrl)."</loc>\n";
        $sitemapEntry .= "\t\t<lastmod>{$lastmod}</lastmod>\n";
        $sitemapEntry .= "\t</sitemap>\n";

        // Insert before the closing </sitemapindex> tag
        $sitemapIndex = str_replace('</sitemapindex>', $sitemapEntry.'</sitemapindex>', $sitemapIndex);

        return $sitemapIndex;
    }

    public function addToRankMathSitemaps(array $sitemaps): array
    {
        if (! $this->shouldIncludeSitemap()) {
            return $sitemaps;
        }

        $sitemaps[] = [
            'loc' => home_url().'/llms-sitemap.xml',
            'lastmod' => date('Y-m-d\TH:i:s+00:00'),
        ];

        return $sitemaps;
    }

    public function addToSeoFrameworkSitemaps(array $endpoints): array
    {
        if (! $this->shouldIncludeSitemap() || ! class_exists('The_SEO_Framework\\Load')) {
            return $endpoints;
        }

        $endpoints['llms-sitemap'] = [
            'endpoint' => 'llms-sitemap.xml',
            'epvar' => 'llms_sitemap',
            'callback' => [$this, 'serveSeoFrameworkSitemap'],
            'robots' => false, // Don't index the sitemap itself
            'regex' => '/^llms-sitemap\.xml$/', // Required by The SEO Framework
        ];

        return $endpoints;
    }

    public function serveSeoFrameworkSitemap(): void
    {
        // Redirect to our existing controller
        wp_redirect(home_url().'/llms-sitemap.xml', 301);
        exit;
    }

    protected function shouldIncludeSitemap(): bool
    {
        // Allow filtering of whether to include the sitemap
        return apply_filters('acorn/llms_txt/include_in_sitemaps', true);
    }
}
