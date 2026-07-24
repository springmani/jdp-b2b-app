<?php

namespace Wpe_Content_Engine\Helper\Acf_Support;

use Wpe_Content_Engine\Helper\Acf_Support\Acf as Acf_Helper;
use Wpe_Content_Engine\Helper\Acf_Support\Types\Abstract_Type;
use Wpe_Content_Engine\Helper\Acf_Support\Types\Email;
use Wpe_Content_Engine\Helper\Acf_Support\Types\Google_Map;
use Wpe_Content_Engine\Helper\Acf_Support\Types\Number;
use Wpe_Content_Engine\Helper\Acf_Support\Types\Text;

class Acf_Factory {
	public const EMAIL            = 'email';
	public const NUMBER           = 'number';
	public const TEXT             = 'text';
	public const TEXTAREA         = 'textarea';
	public const FLEXIBLE_CONTENT = 'flexible_content';
	public const GROUP            = 'group';
	public const POST_OBJECT      = 'post_object';
	public const RELATIONSHIP     = 'relationship';
	public const LINK             = 'link';
	public const TAXONOMY         = 'taxonomy';
	public const REPEATER         = 'repeater';
	public const USER             = 'user';
	public const IMAGE            = 'image';
	public const FILE             = 'file';
	public const GOOGLE_MAP       = 'google_map';
	public const PASSWORD         = 'password';
	public const GALLERY          = 'gallery';
	public const TRUE_FALSE       = 'true_false';
	public const URL              = 'url';
	public const RANGE            = 'range';

	/**
	 * @param string $type Type.
	 * @param string $name Name.
	 *
	 * @return \Wpe_Content_Engine\Helper\Acf_Support\Types\Abstract_Type
	 */
	public static function build( string $type, string $name ): ?Abstract_Type {
		switch ( $type ) {
			case self::EMAIL:
				return new Email( $name );
			case self::NUMBER:
				return new Number( $name );
			case self::TEXT:
			case self::TEXTAREA:
				return new Text( $name );
			case self::GOOGLE_MAP:
				return new Google_Map( $name );
			default:
				return null;
		}
	}

	/**
	 * @param int    $post_id Post ID.
	 * @param string $post_type Post Type.
	 *
	 * @return \Wpe_Content_Engine\Helper\Acf_Support\Acf|null
	 */
	public static function build_acf_helper_for_type( int $post_id, string $post_type ): ?Acf {
		if ( ! is_acf_loaded() ) {
			return null;
		}

		$screen = [ 'post_type' => $post_type ];

		$page_template = get_page_template_slug( $post_id );

		if ( $page_template ) {
			$screen['page_template'] = $page_template;
		}

		$acf_field_groups = acf_get_field_groups( $screen );

		if ( empty( $acf_field_groups ) ) {
			return null;
		}

		foreach ( $acf_field_groups as $key => $acf_field_group ) {
			if ( empty( $acf_field_group ) || ! $acf_field_group['active'] ) {
				continue;
			}

			$acf_field_groups[ $key ]['fields'] = \acf_get_fields( $acf_field_group );
		}

		$data = get_fields( $post_id );

		return new Acf_Helper( $acf_field_groups, is_array( $data ) ? $data : [] );
	}
}
