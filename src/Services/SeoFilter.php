<?php

namespace Roots\AcornLlmsTxt\Services;

class SeoFilter
{
    public function shouldExcludePost(int $postId): bool
    {
        // Check if post is set to noindex by any SEO plugin
        return $this->isYoastNoindex($postId)
            || $this->isRankMathNoindex($postId)
            || $this->isSeoFrameworkNoindex($postId)
            || $this->hasCustomNoindexMeta($postId);
    }

    protected function isYoastNoindex(int $postId): bool
    {
        if (! $this->isYoastActive()) {
            return false;
        }

        // Check Yoast meta for noindex
        $noindex = get_post_meta($postId, '_yoast_wpseo_meta-robots-noindex', true);

        return $noindex === '1';
    }

    protected function isRankMathNoindex(int $postId): bool
    {
        if (! $this->isRankMathActive()) {
            return false;
        }

        // Check RankMath meta for noindex
        $robots = get_post_meta($postId, 'rank_math_robots', true);

        if (is_array($robots)) {
            return in_array('noindex', $robots);
        }

        return false;
    }

    protected function isSeoFrameworkNoindex(int $postId): bool
    {
        if (! $this->isSeoFrameworkActive()) {
            return false;
        }

        // Check The SEO Framework meta for noindex
        $noindex = get_post_meta($postId, '_genesis_noindex', true);

        return $noindex === '1';
    }

    protected function isYoastActive(): bool
    {
        return defined('WPSEO_VERSION') || class_exists('WPSEO_Options');
    }

    protected function isRankMathActive(): bool
    {
        return defined('RANK_MATH_VERSION') || class_exists('RankMath');
    }

    protected function isSeoFrameworkActive(): bool
    {
        return defined('THE_SEO_FRAMEWORK_VERSION') || class_exists('The_SEO_Framework');
    }

    protected function hasCustomNoindexMeta(int $postId): bool
    {
        $customMetaKeys = config('llms-txt.seo.custom_noindex_meta_keys', []);

        if (empty($customMetaKeys)) {
            return false;
        }

        foreach ($customMetaKeys as $metaKey) {
            $value = get_post_meta($postId, $metaKey, true);

            // Check for common "noindex" values
            if ($value === '1' || $value === 1 || $value === 'noindex' ||
                (is_array($value) && in_array('noindex', $value))) {
                return true;
            }
        }

        return false;
    }
}
