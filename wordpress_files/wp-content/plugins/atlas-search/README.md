# Smart Search

## Hooks and Filters

### `wpe_smartsearch/extra_fields`

Filters the post fields before indexing.

```php
apply_filters( 'wpe_smartsearch/extra_fields', string $data, WP_Post $post )
```

#### Description

Use this filter to add or remove fields before an object is indexed.

#### Parameters

```php
$data array
```

Contains all of the data before indexing.

```php
$post WP_Post
```

The current post object being indexed.

#### Examples

Adding a custom field.

```php
add_filter(
    'wpe_smartsearch/extra_fields',
    function( array $data, WP_Post $post ) {
        $data['custom-field'] = 'custom field test';
        return $data;
    },
    10,
    2
);
```

Adding the post permalink.

```php
add_filter(
    'wpe_smartsearch/extra_fields',
    function( array $data, WP_Post $post ) {
        //sample value http://localhost:8000/hello-world
        $data['url'] = get_permalink($post);
        return $data;
    },
    10,
    2
);
```

Adding the post locale with a language plugin, i.e. `polylang`

```php
add_filter(
    'wpe_smartsearch/extra_fields',
    function( array $data, WP_Post $post ) {
        //sample valus EN | ES
        $data['locale'] = pll_get_post_language($post->ID);
        return $data;
    },
    10,
    2
);
```


### `wpe_smartsearch/extra_search_config_fields`

Filters the search config fields.

```php
apply_filters( 'wpe_smartsearch/extra_search_config_fields', array $fields, string $post_type )
```

#### Description

Use this filter to add or remove fields to the Search Config.

#### Parameters

```php
$fields array
```

Contains all of the search config fields for the given `$post_type`.

```php
$post WP_Post
```

The current post_type being processed for Search Config fields.

#### Examples

Adding a custom search config field.


```php
add_filter(
    'wpe_smartsearch/extra_search_config_fields',
    function ( $fields, $post_type ) {
        if ($post_type === 'post' ) {
            $fields[] = 'my-custom-field';
        }

        return $fields;
    },
    10,
    2
);

```

**NOTE:** This hook runs multiple times for each post type.

The following search config field will get added to all post types.

```php
add_filter(
    'wpe_smartsearch/extra_search_config_fields',
    function ( $fields, $post_type ) {
        $fields[] = 'all-post-types-custom-field';
        return $fields;
    },
    10,
    2
);

```


### `wpe_smartsearch/excluded_post_types`

Filters the post types ban list.

```php
apply_filters( 'wpe_smartsearch/excluded_post_types' );
```

#### Description

Filters a list of post types that won't be considered for WP Engine Smart Search

#### Examples

```php
add_filter(
    'wpe_smartsearch/excluded_post_types',
    function (  ) {
        return array(
            'zombie',
            'rabbit',
            'page'
        );
    },
    10,
    2
);
```

### `wpe_smartsearch/search/facet_blocks_enabled`

Enables or disables WPE Engine Smart Search search-related Facets.

```php
apply_filters( 'wpe_smartsearch/search/facet_blocks_enabled', true )
```

#### Description

This filter enables or disables the search-related Facet Blocks provided by WP Engine Smart Search. They are enabled by default.
It would be useful to disable them if you are not using them, or if there is a conflict with your custom facets, theme or plugin.

#### Examples
Disabling WPE Engine Smart Search Facets.
```php
add_filter( 'wpe_smartsearch/search/facet_blocks_enabled', '__return_false' );
```

### Facet Filter Modes (AND/OR)

Each filter facet block has a "Match Mode" setting in the block editor that controls how multiple selections are handled:

**Behavior:**
- **OR mode (default)**: Posts matching ANY of the selected terms are returned
  - URL format: `?tag=tag1,tag2` (comma-separated)
  - Example: Selecting "tag1" and "tag2" returns posts with tag1 OR tag2

- **AND mode**: Posts matching ALL of the selected terms are returned
  - URL format: `?tag=tag1+tag2` (plus-separated, native WordPress format)
  - Example: Selecting "tag1" and "tag2" returns only posts that have both tag1 AND tag2

The mode is automatically detected from the URL format, making it compatible with native WordPress taxonomy queries.

### `wpe_smartsearch/acf/excluded_field_names`

Filter ACF fields from being indexed to WPE Engine Smart Search.

```php
$excluded_field_names = apply_filters( 'wpe_smartsearch/acf/excluded_field_names', array() );
```

#### Description

This filter prevents ACF fields to be indexed using the field name. This is very useful for a number of reasons:
*  Preventing unnecessary data from being indexed, increases performance.
*  Prevents errors from being thrown when indexing data ( Errors like: Limit of total fields [1000] has been exceeded )

#### Examples
You would want to prevent ACF fields with names 'acf_field_name1', 'acf_field_name2', 'acf_field_name3' are not indexed.  

```php
add_filter( 'wpe_smartsearch/acf/excluded_field_names', function ( $excluded_field_names ) {
    $custom_excluded_field_names= array(
       'acf_field_name1',
       'acf_field_name2',
       'acf_field_name3',
    );

    return array_merge($excluded_field_names,$custom_excluded_field_names );
   },
 10,
 1
);
```

### `wpe_smartsearch/allow_post_content_filtering`

Allow post content filtering before it is indexed by WP Engine Smart Search.
```php
	$allow_post_content_filtering    = apply_filters( 'wpe_smartsearch/allow_post_content_filtering', true );

	if ( $allow_post_content_filtering ) {
		$post_array['post_content'] = handle_tags( $post_array['post_content'] );
	}
```

#### Description

This filter enables or disables the default content stripping before it is indexed. By default, WP Engine Smart Search strips all HTML tags, shortcodes, and block editor comments from the content via the handle_tags function.

You can use this filter to disable the default content stripping. This is useful if you want to:
* Preserve certain HTML tags or implement your own content cleaning logic. 
* Use a custom content processing function, as the default stripping might be memory-intensive for very large post content
* Custom post content processing function can be implemented by using the `wpe_smartsearch/extra_fields` filter.

**Note:** This filter is enabled by default. If you encounter indexing issues, such as out-of-memory errors, it may indicate that your hosting plan needs an upgrade to handle the higher memory requirements for processing large posts.
#### Examples
You want to prevent the default HTML stripping for all posts to preserve the HTML structure in the search index.

```php
		add_filter(
			'wpe_smartsearch/allow_post_content_filtering',
			'__return_false'
		);
```

### `wpe_aitk/enable_search`

Controls whether Smart Search is enabled for a query.

```php
apply_filters( 'wpe_aitk/enable_search', bool $enabled, WP_Query $query )
```

#### Description

By default, Smart Search is enabled for all search queries and WooCommerce product queries (when WooCommerce support is active). Use this filter to disable Smart Search for specific queries if needed, such as for specific post types, categories, custom query scenarios, or to fallback to WordPress's native search functionality in certain contexts.

#### Parameters

```php
$enabled bool
```

Whether Smart Search is enabled for the query. Default is `true`.

```php
$query WP_Query
```

The current WP_Query object being processed.

#### Examples

Disable Smart Search for all queries.

```php
add_filter(
    'wpe_aitk/enable_search',
    '__return_false'
);
```

Disable Smart Search only for specific post types.

```php
add_filter(
    'wpe_aitk/enable_search',
    function( $enabled, $query ) {
        // Disable Smart Search for the 'product' post type
        if ( isset( $query->query_vars['post_type'] ) && $query->query_vars['post_type'] === 'product' ) {
            return false;
        }

        return $enabled;
    },
    10,
    2
);
```

Disable Smart Search for WooCommerce product queries on specific pages.

```php
add_filter(
    'wpe_aitk/enable_search',
    function( $enabled, $query ) {
        // Disable on the shop page only
        if ( is_shop() ) {
            return false;
        }

        return $enabled;
    },
    10,
    2
);
```

Disable Smart Search based on custom query variables.

```php
add_filter(
    'wpe_aitk/enable_search',
    function( $enabled, $query ) {
        // Disable for queries with a custom flag
        if ( isset( $query->query_vars['use_native_search'] ) && $query->query_vars['use_native_search'] ) {
            return false;
        }

        return $enabled;
    },
    10,
    2
);
```

### `wpe_smartsearch\attachment_source_format`

Filters the attachment source format for media analysis.

```php
apply_filters( 'wpe_smartsearch\attachment_source_format', string $format, int $attachment_id )
```

#### Description

Use this filter to control how attachment sources are provided to the analysis service. By default, attachments are sent as URLs, but you can change this to base64 encoding if needed.

#### Parameters

```php
$format string
```

The source format. Default is 'URL'. Accepts 'URL' or 'base64'.

```php
$attachment_id int
```

The attachment ID being processed.

#### Examples

Force all attachments to use base64 encoding.

```php
add_filter(
    'wpe_smartsearch\attachment_source_format',
    function( $format, $attachment_id ) {
        return 'base64';
    },
    10,
    2
);
```

Use base64 for specific attachment types only.

```php
add_filter(
    'wpe_smartsearch\attachment_source_format',
    function( $format, $attachment_id ) {
        $mime_type = get_post_mime_type($attachment_id);
        
        // Use base64 for PDFs, URL for images
        if (strpos($mime_type, 'application/pdf') !== false) {
            return 'base64';
        }
        
        return 'URL';
    },
    10,
    2
);
```

Use base64 for attachments larger than a certain size.

```php
add_filter(
    'wpe_smartsearch\attachment_source_format',
    function( $format, $attachment_id ) {
        $file = get_attached_file($attachment_id);
        $file_size = filesize($file);
        
        // Use base64 for files smaller than 5MB
        if ($file_size < 5 * 1024 * 1024) {
            return 'base64';
        }
        
        return 'URL';
    },
    10,
    2
);
```

**Note:** Using base64 encoding will increase memory usage as the entire file content is loaded into memory and encoded. This may not be suitable for very large files or hosting environments with limited memory.

### `wpe_aitk_logging_enabled`

Controls whether Smart Search debug logging is enabled.

```php
apply_filters( 'wpe_aitk_logging_enabled', bool $enabled )
```

#### Description

By default, Smart Search logging is enabled only when both `WP_DEBUG` and `WP_DEBUG_LOG` constants are set to `true`. Use this filter to override that behavior — for example, to enable logging in staging environments without changing `wp-config.php`, or to disable it even when debug constants are active.

When logging is enabled, log entries are written to the PHP error log and buffered for the Support > Debug Logs panel. When disabled, a no-op logger is used and no log output is produced.

#### Parameters

```php
$enabled bool
```

Whether logging is enabled. Default is `true` when `WP_DEBUG` and `WP_DEBUG_LOG` are both `true`, `false` otherwise.

#### Examples

Enable logging regardless of `WP_DEBUG` / `WP_DEBUG_LOG` constants.

```php
add_filter( 'wpe_aitk_logging_enabled', '__return_true' );
```

Disable logging even when debug constants are active.

```php
add_filter( 'wpe_aitk_logging_enabled', '__return_false' );
```

Enable logging only for administrators.

```php
add_filter(
    'wpe_aitk_logging_enabled',
    function( $enabled ) {
        return current_user_can( 'manage_options' );
    }
);
```
