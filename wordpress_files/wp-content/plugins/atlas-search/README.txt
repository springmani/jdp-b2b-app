=== WP Engine AI Toolkit ===
Tags: search
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.3.25
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Author: WP Engine
Contributors: wpengine, mindctrl

Boost site conversions in just a few clicks with Smart Search AI, AI-Powered Recommendations, and Managed Vector Database.

== Description ==

Boost site performance and conversions in minutes with WP Engine's AI Toolkit. Get easily setup and configured AI-powered search, recommendations, and a vector database to super-charge your site and continuously provide real value without constant intervention. Including products that boost search performance, create tailored user recommendations, and a platform that allows you to build chatbots and other AI-powered products, the WP Engine AI Toolkit is a cutting-edge bundle of innovative, easily configured, self-maintaining AI solutions built to continuously boost your site’s conversions without constant manual intervention. And, it’s controlled right from the platform you are used to! No need for extra vendors or extra costs.With WP Engine's AI Toolkit, you can access three key products:

**Smart Search AI** helps convert your website’s most high-intent visitors with more accurate and more relevant search results.

**AI-Powered Recommendations**, included in Smart Search AI, helps increase engagement and conversions with auto-updating recommendations based only on your site’s content.

**Managed Vector Database** helps you build your own AI tools with our easily configured and self-maintaining vector database that seamlessly and continuously translates all your WordPress data for AI. We extract, vectorize, and normalize all your WordPress data for AI.


== Installation ==

This plugin and its credentials will be automatically installed and configured on your WordPress instance after purchasing WP Engine Smart Search on your WP Engine Plan and assigning a license to the environment in the user portal.

1. This plugin can be installed directly from your WordPress site.

* Log in to your WordPress site and navigate to **Plugins &rarr; Add New**.
* Type "WP Engine AI Toolkit" into the Search box.
* Locate the WP Engine AI Toolkit plugin in the list of search results and click **Install Now**.
* Once installed, click the Activate button.

2. Installing via WP-CLI Command (Using WP Engine Dev Tools Dashboard).

* Log in to your **WP Engine User Portal**.
* Navigate to your environment and open the **Advanced tools** tab to establish a secure SSH connection to your WordPress site.
* Once connected, issue the following WP-CLI command to install the plugin:
``` $ wp plugin install atlas-search --activate ```
* This command will download, install, and activate the latest version of WP Engine AI Toolkit.

3. Installing Manually Using a ZIP File

* Download the latest version of the WP Engine AI Toolkit plugin from [this link](https://plugin-updates.wpengine.com/atlas-search/atlas-search.zip)
* Log in to your WordPress site and navigate to Plugins → Add New.
* Click the Upload Plugin button.
* Click the Choose File button, select the ZIP file you downloaded, and then click Install Now.
* Click the Activate Plugin button.

Configuring the Plugin once activated

* Navigate to the Plugin Credentials page for each of your purchased AI Toolkit apps within WP Admin (e.g., "Smart Search," "Recommendations," "Vector Database").
* Enter your URL and Access Token on any one of the Plugin Credentials pages. (Plugin credentials are shared between apps if you have more than one).
* Click "Save Configuration"

**NOTE:** credentials for this plugin can only be obtained by purchasing this as an add-on as a part of your existing WP Engine Plan.
Please see [wpengine.com/support/ai-toolkit](https://wpengine.com/support/ai-toolkit/) for details.

== Changelog ==
= 0.3.25 =
* **Added:** Off platform UI created.
* **Added:** Extended features capability added for off platform.
* **Added:** Support page templates now support ACF fields.

= 0.3.24 =
* **Added:** Support for ACF fields in page templates during content sync.
* **Fixed:** Made `get_property_schemas` static to resolve issues in Site Health async tests.

= 0.3.23 =
* **Added:** Site Health integration for diagnostics and troubleshooting.
* **Fixed:** Resolved plugin packaging issue with bundled dependencies.

= 0.3.22 =
* **Fixed:** Missing vendor dependency for the plugin.

= 0.3.21 =
* **Fixed:** Sync Error when indexing so the indexing will index raw data as a backup and not breaking the index process
* **Fixed:** Two critical bugs in Atlas Search date query filter generation when datetime strings are passed in before/after parameters.

= 0.3.20 =
* **Added:** Recommendations for Multisite

= 0.3.19 =
* **Added:** Synonyms feature
* **Fixed:** Multisite shows error after successfully saving Smart Search config

= 0.3.18 =
* **Added:** GraphQL to REST API proxy
* **Added:** Orderby multifield support
* **Added:** Settings improvements for Recommendations
* **Added:** ChatKit user tracking improvements
* **Fixed:** Filter facet not working on sites without search block
* **Fixed:** Multisite unpublished documents not deleted from index

= 0.3.17 =
* **Updated:** Security updates
* **Fixed:** Issue with WooCommerce catalog visibility
* **Fixed:** Issue where unpublished posts stay in Custom Search Results and Promotions

= 0.3.16 =
* **Updated:** Security updates
* **Fixed:** Issue with WooCommerce product categories orderby

= 0.3.15 =
* **Added:** Custom Search Results feature
* **Fixed:** Issue with cart sessions in WooCommerce
* **Fixed:** Alert users when having unsaved changes in wp-admin
* **Fixed:** WooCommerce sorting variants
* **Fixed:** WooCommerce field mapping for meta queries


= 0.3.14 =
* **Added:** Search Promotions

= 0.3.13 =
* **Fixed:** Passing comma separated values into shortcode attributes

= 0.3.12 =
* **Added:** Toggle for collecting analytics data
* **Added:** Improvements to filter facet, posts filtering and taxonomy hierarchy support
* **Added:** Filter to disable search integration on certain pages


= 0.3.11 =
* **Added:** Blocks shortcode renderer support.
* **Fixed:** Add Fields Modal search bar display issue.
* **Fixed:** WooCommerce ordering by price.
* **Fixed:** Indexing of Google Maps fields in groups.
* **Fixed:** Search results respecting the hide out of stock flag.
* **Fixed:** Recommendations Block metadata mismatch.
* **Fixed:** Issues with taxonomy filtering.

= 0.3.10 =
* **Updated:** WordPress compatibility to 6.9.
* **Fixed:** Unhandled exceptions.
* **Fixed:** Post password check hardened.
* **Fixed:** Disabled autoload for index resume option.
* **Added:** Getter to prevent notices for legacy code usage.
* **Added:** On upload attachment metadata generation support.

= 0.3.9 =
* **Fixed:** Styling issues on the Smart Search config page

= 0.3.8 =
* **Added:** OpenAI Chatkit integration.
* **Added:** Positioning to tracking script.
* **Fixed:** Issue where password protected posts are being indexed under conditions.

= 0.3.7 =
* **Added:** Multimodal bulk support for image analysis and PDF extraction.
* **Added:** Cookie consent notice.
* **Added:** Legal notice for multimodal generation.
* **Updated:** Logo and banner update.
* **Fixed:** Nested ACF Flexible Content fields not being indexed correctly.
* **Fixed:** Nested ACF password fields indexing issue.

= 0.3.6 =
* **Added:** Multimodal support for image analysis and PDF extraction

= 0.3.5 =
* **Fixed:** Issue where search config fields clobbered each others settings when searching for multiple post types.

= 0.3.4 =
* **Added:** Geolocation support with ACF Google Maps field.
* **Added:** Support ACF File field for indexing and searching.

= 0.3.3 =
* **Added:** Create a recency solution in UI and connect it to API.
* **Fixed:** Fix save configuration button is sometimes active after page refresh.

= 0.3.2 =
* **Fixed:** Fix asterisk appearing in the search bar in WooCommerce sites.
* **Updated:** Text changes.
* **Added:** Disable Save button when no changes are made in the Configuration page.
* **Added:** Add `wpe_smartsearch/allow_post_content_filtering` filter.

= 0.3.1 =
* **Updated:** Plugin assets.

= 0.3.0 =
* **Updated:** Rebranded to WP Engine AI Toolkit.

= 0.2.83 =
* **Added:** Added a WordPress filter to programmatically disable search facet blocks.

= 0.2.82 =
* **Fixed:** How permalinks are handled in facet blocks
* **Fixed:** Empty date queries breaking query integration
* **Added:** WordPress 6.8.1 support
* **Fixed:** Output buffering when indexing operations are being executed
* **Added:** Filters are sent with search requests

= 0.2.81 =
* **Added:** Active filters facet block
* **Added:** Skip tracking on search results page
* **Added:** Range facet accepts numeric ACF fields
* **Fixed:** Recommendations: Don't show related products on non-single post pages
* **Fixed:** Facet dropdown filterer doesn't remove the filter when value is "Please select..."
* **Fixed:** Catch all possible exceptions and errors while indexing

= 0.2.80 =
* **Added:** Facets filters can automatically apply without pressing search
* **Added:** Tracking page views
* **Fixed:** Page number in pagination is handled correctly

= 0.2.79 =
* **Updated:** Security updates
* **Fixed:** Resume issues

= 0.2.78 =
* **Added:** Facets honor customer themes
* **Added:** Price range filter double slider for WooCommerce

= 0.2.77 =
* **Fixed:** Resume not working on larger sites
* **Fixed:** Incorrect multisite index batch size

= 0.2.76 =
* **Updated:** Recommendations Block Options
* **Fixed:** Recommendations block error handling
* **Fixed:** Set limit for posts

= 0.2.75 =
* **Added:** Semantic & Hybrid Search allows multiple fields to be selected
* **Added:** Checkbox Facet filters by Woo attributes
* **Updated:** Security updates
* **Fixed:** Recommendations block calls api for every query loop in page

= 0.2.74 =
* **Updated:** Drop search facet in favour of the built in search inputs for WordPress and WooCommerce
* **Fixed:** Support for gravitysmtp plugin

= 0.2.73 =
* **Updated:** Removed Feature Flag for Recommendations Block
* **Fixed:** Dont send woo attributes with empty name
* **Updated:** Update header logos

= 0.2.72 =
* **Added:** Checkbox Facet
* **Added:** Sort by fields such as Name A-Z/Z-A
* **Added:** Recommendations Block
* **Updated:** More Security updates

= 0.2.71 =
* **Added:** Basic support for Fusion Page Builder plugin.
* **Updated:** Security updates

= 0.2.70 =
* **Fixed:** WordPress.org contributor account list

= 0.2.69 =
* **Added:** Add post permalink to each indexed post.
* **Added:** Allow multiple dropdown facets on a single page.

= 0.2.68 =
* **Fixed:** Search Bias set to 0 in Hybrid.

= 0.2.67 =
* **Fixed:** Woo Commerce searching was rendering search template.

= 0.2.66 =
* **Added:** Support for WordPress 6.7.
* **Fixed:** Plugin installation tab text changed.
* **Fixed:** Saving Full Text Search doesn't clear Semantic/Hybrid fields.

= 0.2.65 =
* **Added:** WP_Date_Query support.
* **Fixed:** Change how we get order and orderby.

= 0.2.64 =
* **Added:** Turn off search functionality while indexing.
* **Fixed:** Removed product meta data.
* **Fixed:** Catalog visibility searches out of context.
* **Fixed:** Taxonomies grouping not filtering results correctly on search.

= 0.2.63 =
* **Fixed:** Searching with empty string returns all results instead if none.
* **Fixed:** Pressing cancel in semantic edit fields doesn't add the new value.
* **Fixed:** Requiring php files sometimes were creating an error in some environments.
* **Fixed:** Save button in settings page disabling incorrectly.

= 0.2.62 =
* **Fixed:** Prevent memory issues on large multisite.

= 0.2.61 =
* **Added:** Support for catalog visibility in WooCommerce.

= 0.2.60 =
* **Fixed:** Disabled radio cards when API returns 400.
* **Fixed:** Don't show error when no AI search enabled and save Full Text Search card in configuration page.
* **Added:** Woo Commerce default price filtering integration.
* **Added:** Woo Commerce default order by integration.
* **Added:** Getting all WP posts now limits them to 1000 as opposed to 10000.
* **Added:** Disable configuration button until settings change in config page.

= 0.2.59 =
* **Fixed:** Indexing errors when excluding post types.

= 0.2.58 =
* **Update:** WP CLI progress bar for indexing.

= 0.2.57 =
* **Added:** Bulk indexing support.
* **Updated:** wp-cli command which now takes into account multisite
* **Added:** Improved WP_Meta_Query mappings for the LIKE operator.

= 0.2.56 =
* **Added:** WP_Meta_Query support.
* **Added:** Indexing term ids.

= 0.2.55 =
* **Fixed:** Multisite session issues.
* **Fixed:** WooCommerce loading issues.
* **Added:** Scaffolding Facets code.

= 0.2.54 =
* **Added:** Experimental WooCommerce Support.
* **Added:** Experimental blocks for Faceted Search.
* **Added:** Disable save configuration button until settings change in settings page
* **Added:** Show Error Messages in Configuration Page

= 0.2.53 =
* **Added:** Standard security headers for HTTP rest calls.
* **Added:** Enable Hybrid/Semantic Search for multisite.

= 0.2.52 =
* **Added:** Support for WordPress 6.6.

= 0.2.51 =
* **Fixed:** Fixed typos.

= 0.2.50 =
* **Updated:** Renamed Sync page to Index Data.
* **Updated:** Left menu items reordered - Index Data is default page now.
* **Added:** Settings page reworked. AI-Powered Hybrid Search page was merged into Settings pages.

= 0.2.49 =
* **Fixed:** WP Engine Smart Search not running for admin searches.
* **Fixed:** All sites are indexed in multisite.

= 0.2.48 =
* **Added:** Basic support for Polylang plugin.
* **Fixed:** Corrected inaccurate text messages.

= 0.2.47 =
* **Added:** UI re-skin.

= 0.2.46 =
* **Added:** Support for taxonomy filtering.

= 0.2.45 =
* **Added:** Make clear data indexing needs to be completed.
* **Added:** Add meta in requests when multisite.

= 0.2.44 =
* **Added:** Strip html tags from post_content.
* **Added:** Minimum multisite support. Only Network admins can sync all multisite content.
* **Added:** When multisite, Search Config and AI-Powered Hybrid Search pages were removed from network admin.

= 0.2.43 =
* **Fixed:** Filtering issues when excluding post types.
* **Added:** Support for WordPress 6.5.

= 0.2.42 =
* **Fixed:** Filtering issues when more than two terms present.

= 0.2.41 =
* **Fixed:**  When selected fields are empty semantic search shouldn't return results.
* **Fixed:**  When all selected fields are unchecked for a selected post type search shouldn't return results for this post type.


= 0.2.40 =
* **Fixed:**  Update AI config page name and fixed typos.

= 0.2.39 =
* **Fixed:**  Using multiple wp-graphql queries causing issues in cursor pagination.
* **Fixed:**  When no fields are selected full text search should return 0 results.

= 0.2.38 =
* **Fixed:**  Allow pages to be queried by default.
* **Fixed:**  Only allow valid fields to be selectable for Semantic Search.

= 0.2.37 =
* **Fixed:**  New error message now appears when system initialization as opposed an authentication error.

= 0.2.36 =
* **Added:**  WPGraphQL cursor pagination support.

= 0.2.35 =
* **Added:** filter `wpe_smartsearch/acf/excluded_field_names`, for excluding ACF field names from being indexed.

= 0.2.34 =
* **Added:** AI Powered Search feature.

= 0.2.33 =
* **Updated:** The id prefix filter will now add the id prefix to indexed documents
* **Updated:** The id prefix filter will now add the prefix as a search filter to isolate WordPress doucments for the current site
* **Added:** New filter hook to allow users to exclude post types from WP Engine Smart Search

= 0.2.32 =
* **Updated:** WordPress compatibility: 6.4
* **Updated:** Security updates
* **Added:** Non-logged in admin calls now use WP Engine Smart Search

= 0.2.31 =
* **Fixed:** Pagination issues when page size is set to `-1`

= 0.2.30 =
* **Fixed:** Total found posts number was incorrect after search

= 0.2.29 =
* **Fixed:** UI not rendering on WP Admin

= 0.2.28 =
* **Updated:** Revert to old plugin file name to prevent deactivation

= 0.2.27 =
* **Fixed:** UI issue on v0.2.26

= 0.2.26 =
* **Updated:** Plugin rebranded to WP Engine Smart Search
* **Added:** filter `wpe_smartsearch/extra_search_config_fields`, for filtering the search config fields.
* **Added:** support for orderby queries.

= 0.2.25 =
* **Added:** Added filter `wpe_smartsearch/extra_fields`, for filtering the index fields before indexing content.
* **Fixed:** Show error message when 401/404 on settimgs page
* **Fixed:** ACF not needed user type fields filtered out during index. Including user_pass field
* **Updated:** WordPress compatibility: 6.3

= 0.2.24 =
* **Fixed:** wpe_smartsearch/id_prefix filter now shows errors on the sync page if the returned data is invalid from the filter
* **Fixed:** Reduce number of fields indexed by ACF

= 0.2.23 =
* **Fixed:** Prevented unnecessary real-time index requests which resulted in error messages

= 0.2.22 =
* **Fixed:** Breaking change for php7.4 users
* **Added:** Search meta data is now sent during search requests

= 0.2.21 =
* **Fixed:** Issue when unsupported fields were being indexed
* **Added:** Filter for adding prefix
* **Updated:** Cleanup old endpoints code
* **Updated:** Remove metadata calls

= 0.2.20 =
* **Fixed:** Issue when errors where not displaying properly

= 0.2.19 =
* **Updated:** Remove ACM support from README

= 0.2.18 =
* **Added:** Add excluded posts support
* **Updated:** Improve admin messages
* **Updated:** Security updates

= 0.2.17 =
* **Fixed:** Issue where post type revisions were being indexed.
* **Fixed:** Issue where unpublished data was being indexed.

= 0.2.16 =
* **Fixed:** Issue where search config could not be saved.

= 0.2.15 =
* **Updated:** Plugin now uses the new index API, This change also streamlines how data is synchronized from WordPress.
* **Updated:** Search config now uses the WordPress field names for post types
* **Added:** Support for custom taxonomies

= 0.2.14 =
* **Fixed:** sync issues with unsupported ACF subfields

= 0.2.13 =
* **Fixed:** Remove ACF keys with empty string
* **Updated:** Use new find API for searches

= 0.2.12 =
* **Fixed:** ACF issue with nested content

= 0.2.11 =
* **Fixed:** ACF field issue with empty values on fields

= 0.2.10 =
* **Added:** Extended support for ACF types

All ACF types will now be indexed and searchable except for the following, these fields are excluded:
* image
* file
* google_map
* password
* gallery

**NOTE:** To take advantage of this new feature, please delete and re sync your data.

= 0.2.8 =
* **Fixed:** Issue where assets syncing were taking too long.

= 0.2.4 =
* **Updated:** Update version headers.

= 0.2.3 =
* **Fixed:** Success toast now pops up when sync is complete.

= 0.2.2 =
* **Added:** WordPress HTML and REST search now work with WP Engine Smart Search.

= 0.2.1 =
* **Fixed:** Issue when searching multiple terms.

= 0.2.0 =
* **Notice:** Upgrading to this version requires re-syncing data.

= 0.1.52 =
* **Fixed:** Issue where ACF fields were being omitted on initial sync.

= 0.1.51 =
* **Added:** Feature to allow more complex searching.

= 0.1.50 =
* **Fixed:** Issue where weight sliders were not working.

= 0.1.49 =
* **Updated:** Update version headers.

= 0.1.48 =
* **Fixed:** Issue where post slugs were casing sync issues.

= 0.1.47 =
* **Updated:** Update version headers.

= 0.1.46 =
* **Fixed:** Issue where permalinks were casing sync issues.

= 0.1.45 =
* **Fixed:** Issue where parent posts were causing failed syncs.

= 0.1.44 =
* **Fixed:** Issue where ACM date types were causing sync issues

= 0.1.43 =
* **Updated:** Readme docs
* **Fixed:** Allow hyphens in model identifiers

= 0.1.42 =
* **Updated:** Update version headers

= 0.1.41 =
* **Updated:** Update version headers

= 0.1.40 =
* **Added:** Support for offset pagination

= 0.1.39 =
* **Fixed:** issue where field weights were not being respected in search results

= 0.1.38 =
* **Updated:** Version headers

= 0.1.37 =
* **Reverted** unintended changes to sync.

= 0.1.36 =
* **Removed** search unused capabilities check.

= 0.1.35 =
* **Fixed** Issue with nested ACF fields on CPT's.

= 0.1.34 =
* **Fixed** WP Engine Smart Search not working when ACF objects are attached to CPT's.

= 0.1.33 =
* **Fixed** ACF issue with empty field groups during sync.

= 0.1.32 =
* **Fixed** Failed sync's due to null ACF field groups.
* **Fixed** Correctly order posts and pages when syncing data.

= 0.1.31 =
* **Fixed** Sync issues with parent terms.

= 0.1.30 =
* **Fixed** Sync issues with removed and re-added ACM fields.

= 0.1.29 =
* **Fixed** Empty ACM repeatable fields causing sync issues.
* **Fixed** Post featured images would cause sync to fail.

= 0.1.28 =
* **Fixed** ACM repeatable fields causing sync issues.
* **Fixed** Front-end missing wpApiSettings object.

= 0.1.27 =
* **Fixed** Sync issue when post types had no author.
* **Fixed** Sync issue when ACF fields contained dates as strings.

= 0.1.26 =
* **Added** UI configuration for fuziness and stemming toggle.

= 0.1.25 =
* **Fixed** ACM models can now be searched correctly.
* **Fixed** Posts with no authors will be synchronized correctly.

= 0.1.24 =
* **Updated** WP Engine Smart Search minimum PHP version is now 7.4

= 0.1.23 =
* **Fixed** Posts with empty `post_name` with not be synchronized

= 0.1.22 =
* **Fixed:** Simple Feature Request plugin breaking WP Engine Smart Search sync

= 0.1.21 =

* **Fixed:** Auto drafts will no longer be automatically synchronized
* **Fixed:** User delete events are now correctly handled
* **Fixed:** Tag descriptions can now be synchronized as longtext

= 0.1.20 =
* **Updated:** Version headers

= 0.1.19 =
* **Updated:** Version headers

= 0.1.18 =
* **Fixed:** Admin error notices correctly instruct users to sync when data sync issues occur

= 0.1.17 =
* **Fixed:** ACF group names search config where they were unable to be searched
* **Fixed:** Fuzzy queries unable to search where numbers are involved


= 0.1.16 =
* **Added:** Fuzzy configuration UI

= 0.1.15 =
* **Added:** Enable fuzzy search by default

= 0.1.14 =
* **Added:** Support for ACM's email field

= 0.1.13 =
* **Fixed:** Breaking pagination in WP Admin views
* **Added:** Clear sync progress & locks when plugin is deactivated

= 0.1.12 =
* **Added:** Add button to delete search data
* **Fixed:** Sync button progress bar improvement

= 0.1.11 =
* **Fixed:** Sync button progress is reset when multiple tabs try to sync

= 0.1.10 =
* **Fixed:** Progress bar animation
* **Fixed:** Sync items correctly syncing

= 0.1.9 =
* **Added:** Sync lock to prevent more than one sync executing at a time
* **Fixed:** Progress calculation on sync progress bar
* **Fixed:** Sync can now progress when ACM is not installed

= 0.1.8 =
* **Added:** New sync button to sync content via plugin
* **Added:** Plugin Icon and Banner
* **Added:** Toast confirmations when saving settings
* **Fixed:** Importing posts via ACM
* **Fixed:** Styling issues on WP Engine Smart Search Settings

= 0.1.7 =
* Added toast confirmations on settings changes
* Show info to user about syncing data when plugin is activated
* Settings based scripts are now cached by the browser on WP Admin
* Search configuration regenerated on content changes
* Added validation to settings form

= 0.1.6 =
* Search fields now correctly search through content models
* Remove slug as an option from search config
* Url setting will correctly default to an empty string

= 0.1.5 =
* Added new settings page
* Added Search Config page

= 0.1.4 =
* Update WP CLI command prefix to `wp as`

= 0.1.1 =
* Prepare for release

= 0.1.0 =
* Add support for ACM repeater fields
* Improve error messages in wp-admin
* Sync CPT excerpt field

