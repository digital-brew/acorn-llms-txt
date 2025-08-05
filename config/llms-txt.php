<?php

return [

    /*
    |--------------------------------------------------------------------------
    | LLMs Text Package Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which post types to include in the LLMs text endpoints
    | and other settings for content generation.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Post Types
    |--------------------------------------------------------------------------
    |
    | Define which WordPress post types should be included in the
    | /llms.txt and /llms-full.txt endpoints.
    |
    */
    'post_types' => ['post', 'page'],

    /*
    |--------------------------------------------------------------------------
    | Individual Post Endpoints
    |--------------------------------------------------------------------------
    |
    | Enable individual .txt endpoints for posts/pages (e.g., /hello-world.txt).
    | Disabled by default for performance and security.
    |
    */
    'individual_posts' => [
        'enabled' => false, // Enable individual post .txt endpoints
        'post_types' => ['post', 'page'], // Which post types to enable for
        'cache_ttl' => 3600, // Cache duration for individual posts
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure cache duration in seconds. Default is 1 hour (3600 seconds).
    |
    */
    'cache_ttl' => 3600,

    /*
    |--------------------------------------------------------------------------
    | Content Exclusions
    |--------------------------------------------------------------------------
    |
    | Configure which content should be excluded from the endpoints.
    |
    */
    'exclude' => [
        'post_ids' => [], // Specific post IDs to exclude
        'post_statuses' => ['draft', 'private', 'trash'], // Post statuses to exclude
        'password_protected' => true, // Exclude password protected posts
        'sticky_posts' => false, // Include/exclude sticky posts
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Limits
    |--------------------------------------------------------------------------
    |
    | Configure limits for content processing and output.
    |
    */
    'limits' => [
        'max_posts' => 1000, // Maximum number of posts to include (0 = no limit)
        'max_content_length' => 50000, // Max characters per post content (0 = no limit)
        'posts_per_section' => 50, // Max posts per section in listing
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Settings
    |--------------------------------------------------------------------------
    |
    | Configure how content is processed and displayed.
    |
    */
    'content' => [
        'excerpt_length' => 20, // Number of words for auto-generated excerpts
        'process_shortcodes' => true, // Process WordPress shortcodes (true) or strip them (false)
        'include_featured_image' => false, // Include featured image URLs in metadata
        'include_author' => true, // Include author information
        'include_date' => true, // Include publication date
        'include_taxonomies' => true, // Include categories, tags, and custom taxonomies
        'date_format' => 'Y-m-d', // Date format for output
    ],

    /*
    |--------------------------------------------------------------------------
    | Section Ordering
    |--------------------------------------------------------------------------
    |
    | Configure how content sections are ordered in the listing.
    |
    */
    'ordering' => [
        'posts_order' => 'date', // date, title, menu_order, or custom
        'posts_direction' => 'DESC', // ASC or DESC
        'sections_order' => ['pages', 'posts'], // Order of sections in listing
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Settings
    |--------------------------------------------------------------------------
    |
    | Configure SEO-related options and custom meta key support.
    |
    */
    'seo' => [
        'custom_noindex_meta_keys' => [], // Custom meta keys to check for noindex (e.g., '_custom_noindex', 'my_seo_robots')
    ],

    /*
    |--------------------------------------------------------------------------
    | WooCommerce Integration
    |--------------------------------------------------------------------------
    |
    | Configure WooCommerce product data inclusion.
    |
    */
    'woocommerce' => [
        'enabled' => true, // Enable WooCommerce integration
        'include_price' => true, // Include product price in output
        'include_stock_status' => false, // Include stock status (instock, outofstock, etc.)
        'include_product_type' => false, // Include product type (simple, variable, etc.)
    ],
];
