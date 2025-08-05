<?php

namespace Roots\AcornLlmsTxt;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Roots\AcornLlmsTxt\Services\SeoFilter;

class ContentFetcher
{
    protected array $postTypes;

    protected SeoFilter $seoFilter;

    public function __construct(array $postTypes = ['post', 'page'], ?SeoFilter $seoFilter = null)
    {
        $this->postTypes = $postTypes;
        $this->seoFilter = $seoFilter ?: new SeoFilter;
    }

    public function getPosts(int $limit = -1): Collection
    {
        // Apply configuration limits
        $maxPosts = config('llms-txt.limits.max_posts', 1000);
        if ($maxPosts > 0 && ($limit === -1 || $limit > $maxPosts)) {
            $limit = $maxPosts;
        }

        // Get ordering configuration
        $orderBy = config('llms-txt.ordering.posts_order', 'date');
        $order = config('llms-txt.ordering.posts_direction', 'DESC');

        // Get exclusion configuration
        $excludeStatuses = config('llms-txt.exclude.post_statuses', ['draft', 'private', 'trash']);
        $excludeIds = config('llms-txt.exclude.post_ids', []);
        $excludePasswordProtected = config('llms-txt.exclude.password_protected', true);

        // Allow filtering of post types
        $postTypes = apply_filters('acorn/llms_txt/post_types', $this->postTypes);

        // Validate and log invalid post types
        $postTypes = $this->validatePostTypes($postTypes);

        $args = [
            'post_type' => $postTypes,
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => $orderBy,
            'order' => $order,
            'post__not_in' => $excludeIds,
        ];

        if ($excludePasswordProtected) {
            $args['has_password'] = false;
        }

        $posts = get_posts($args);

        $posts = collect($posts)
            ->filter(function ($post) {
                // SEO filter - exclude posts that are set to noindex by SEO plugins
                if ($this->seoFilter->shouldExcludePost($post->ID)) {
                    return false;
                }

                // Sticky posts filter - exclude sticky posts if configured
                if (config('llms-txt.exclude.sticky_posts', false) && is_sticky($post->ID)) {
                    return false;
                }

                // Custom filter hook - allow developers to exclude specific posts
                return ! apply_filters('acorn/llms_txt/exclude_post', false, $post);
            })
            ->map(function ($post) {
                $data = [
                    'id' => $post->ID,
                    'title' => $post->post_title,
                    'url' => get_permalink($post->ID),
                    'excerpt' => $this->getExcerpt($post),
                    'content' => $this->processContent($post->post_content),
                    'type' => get_post_type_object($post->post_type)?->labels?->singular_name ?? ucfirst($post->post_type),
                    'parent_id' => $post->post_parent,
                ];

                // Add optional metadata based on configuration
                if (config('llms-txt.content.include_date', true)) {
                    $data['date'] = $post->post_date;
                }

                if (config('llms-txt.content.include_author', true)) {
                    $data['author'] = get_the_author_meta('display_name', $post->post_author);
                }

                if (config('llms-txt.content.include_taxonomies', true)) {
                    $data['taxonomies'] = $this->getAllTaxonomies($post->ID);
                }

                if (config('llms-txt.content.include_featured_image', false)) {
                    $featuredImage = get_the_post_thumbnail_url($post->ID, 'full');
                    if ($featuredImage) {
                        $data['featured_image'] = $featuredImage;
                    }
                }

                // Add WooCommerce product data if enabled and WooCommerce is active
                if (config('llms-txt.woocommerce.enabled', true) && $this->isWooCommerceActive() && $post->post_type === 'product') {
                    $data = array_merge($data, $this->getWooCommerceProductData($post->ID));
                }

                // Allow filtering of individual post data
                return apply_filters('acorn/llms_txt/post_data', $data, $post);
            });

        // Allow filtering of the entire posts collection
        return apply_filters('acorn/llms_txt/posts', $posts);
    }

    protected function getExcerpt($post): string
    {
        if (! empty($post->post_excerpt)) {
            return $post->post_excerpt;
        }

        $excerptLength = config('llms-txt.content.excerpt_length', 20);

        // Use WordPress built-in excerpt generation
        return wp_trim_words($post->post_content, $excerptLength, '...');
    }

    protected function processContent(string $content): string
    {
        // Process blocks and shortcodes if configured (default true)
        if (config('llms-txt.content.process_shortcodes', true)) {
            // Parse blocks first (Gutenberg content)
            $content = do_blocks($content);
            // Then process any remaining shortcodes
            $content = do_shortcode($content);
        }

        if (! config('llms-txt.content.process_shortcodes', true)) {
            // Strip both blocks and shortcodes
            $content = strip_shortcodes($content);
            // Remove block markup patterns
            $content = preg_replace('/<!-- wp:.*?-->/s', '', $content);
            $content = preg_replace('/<!-- \/wp:.*?-->/s', '', $content);
        }

        // Apply content length limit if configured
        $maxLength = config('llms-txt.limits.max_content_length', 0);
        if ($maxLength > 0 && Str::length($content) > $maxLength) {
            $content = Str::limit($content, $maxLength);
        }

        return $content;
    }

    protected function getTerms(int $postId, string $taxonomy): array
    {
        $terms = wp_get_post_terms($postId, $taxonomy);

        if (is_wp_error($terms) || empty($terms)) {
            return [];
        }

        return array_map(fn ($term) => $term->name, $terms);
    }

    protected function getAllTaxonomies(int $postId): array
    {
        $taxonomies = get_object_taxonomies($postId);
        $result = [];

        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_post_terms($postId, $taxonomy);

            if (is_wp_error($terms) || empty($terms)) {
                continue;
            }

            $taxonomyObj = get_taxonomy($taxonomy);
            $termNames = array_map(fn ($term) => $term->name, $terms);

            $result[] = [
                'name' => $taxonomyObj->labels->singular_name,
                'terms' => $termNames,
            ];
        }

        return $result;
    }

    protected function validatePostTypes(array $postTypes): array
    {
        $validPostTypes = [];

        foreach ($postTypes as $postType) {
            if (! is_string($postType)) {
                error_log('Invalid post type (not string): '.var_export($postType, true));

                continue;
            }

            if (! post_type_exists($postType)) {
                error_log("Post type '{$postType}' does not exist. Skipping.");

                continue;
            }

            $validPostTypes[] = $postType;
        }

        // Fallback to default if no valid post types
        if (empty($validPostTypes)) {
            error_log("No valid post types found. Falling back to default: ['post', 'page']");

            return ['post', 'page'];
        }

        return $validPostTypes;
    }

    protected function isWooCommerceActive(): bool
    {
        return class_exists('WooCommerce');
    }

    protected function getWooCommerceProductData(int $productId): array
    {
        if (! function_exists('wc_get_product')) {
            return [];
        }

        try {
            $product = wc_get_product($productId);
            if (! $product || ! is_object($product)) {
                return [];
            }
        } catch (\Exception $e) {
            // Log the error but don't break the process
            error_log("WooCommerce product fetch error for ID {$productId}: ".$e->getMessage());

            return [];
        }

        $data = [];

        try {
            // Add SKU if available
            $sku = $product->get_sku();
            if (! empty($sku) && is_string($sku)) {
                $data['sku'] = sanitize_text_field($sku);
            }

            // Add price information if configured
            if (config('llms-txt.woocommerce.include_price', true)) {
                $price = $product->get_price();
                if (is_numeric($price) && $price > 0) {
                    $currency = function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : 'USD';
                    $data['price'] = number_format((float) $price, 2).' '.sanitize_text_field($currency);
                }
            }

            // Add stock status if configured
            if (config('llms-txt.woocommerce.include_stock_status', false)) {
                $stockStatus = $product->get_stock_status();
                if (! empty($stockStatus) && is_string($stockStatus)) {
                    $data['stock_status'] = sanitize_text_field($stockStatus);
                }
            }

            // Add product type
            if (config('llms-txt.woocommerce.include_product_type', false)) {
                $productType = $product->get_type();
                if (! empty($productType) && is_string($productType)) {
                    $data['product_type'] = sanitize_text_field($productType);
                }
            }
        } catch (\Exception $e) {
            // Log the error but continue with partial data
            error_log("WooCommerce product data extraction error for ID {$productId}: ".$e->getMessage());
        }

        return $data;
    }
}
