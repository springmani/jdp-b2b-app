<?php

function pmaep_pmae_prepare_import_template_field_template ($field_template, $exportOptions, $acf_list, $element_name, $field_options, $parent_delimiter, $field_tpl_key, $is_xml_template, $xpath_separator, $implode_delimiter) {

	switch ( $field_options['type'] ) {
		case 'text':
		case 'textarea':
		case 'number':
		case 'email':
		case 'password':
		case 'url':
		case 'oembed':
		case 'wysiwyg':
		case 'image':
		case 'file':
		case 'date_picker':
		case 'color_picker':
		case 'acf_cf7':
		case 'gravity_forms_field':
		case 'limiter':
		case 'wp_wysiwyg':
		case 'date_time_picker':
			$field_template = '{' . $field_tpl_key . '}';
			break;
		case 'gallery':

			$field_template = array(
				'search_in_media' => 1,
				'delim'           => $implode_delimiter,
				'gallery'         => '{' . $field_tpl_key . '}'
			);

			break;
		case 'relationship':

			$field_template = array(
				'delim' => $implode_delimiter,
				'value' => '{' . $field_tpl_key . '}'
			);

			break;
		case 'post_object':
		case 'page_link':
		case 'user':

			if ( $is_xml_template ) {
				$field_template = '{' . $field_tpl_key . '}';
			} else {
				$field_tpl_key = str_replace( "[1]", "", $field_tpl_key );

				if ( $field_options['multiple'] ) {
					if ( $implode_delimiter == "|" ) {
						$field_template = '[str_replace("|", ",",{' . $field_tpl_key . '[1]})]';
					} else {
						$field_template = '{' . $field_tpl_key . '[1]}';
					}
				} else {
					$field_template = '{' . $field_tpl_key . '[1]}';
				}
			}

			break;
		case 'select':
		case 'checkbox':

			XmlExportACF::$templateOptions['is_multiple_field_value'][ $field_options['key'] ] = "no";

			if ( $is_xml_template ) {
				if ( $implode_delimiter == "|" ) {
					$field_template = '[str_replace("|", ",",{' . $field_tpl_key . '})]';
				} else {
					$field_template = '{' . $field_tpl_key . '}';
				}
			} else {
				$field_tpl_key = str_replace( "[1]", "", $field_tpl_key );

				if ( $implode_delimiter == "|" ) {
					$field_template = '[str_replace("|", ",",{' . $field_tpl_key . '[1]})]';
				} else {
					$field_template = '{' . $field_tpl_key . '[1]}';
				}
			}

			break;
		case 'radio':
		case 'true_false':
		case 'button_group':

			XmlExportACF::$templateOptions['is_multiple_field_value'][ $field_options['key'] ] = "no";

			$field_template = '{' . $field_tpl_key . '}';

			break;
		case 'location-field':
		case 'google_map':

			if ( ! $is_xml_template ) {
				$field_tpl_key = str_replace( "[1]", "", $field_tpl_key );
			}

			$field_template = array(
				'address'                                   => '{' . $field_tpl_key . $xpath_separator . 'address[1]}',
				'address_geocode'                           => 'address_no_key',
				'address_google_developers_api_key'         => '',
				'address_google_for_work_client_id'         => '',
				'address_google_for_work_digital_signature' => '',
				'lat'                                       => '{' . $field_tpl_key . $xpath_separator . 'lat[1]}',
				'lng'                                       => '{' . $field_tpl_key . $xpath_separator . 'lng[1]}'
			);

			break;
		case 'paypal_item':

			if ( ! $is_xml_template ) {
				$field_tpl_key = str_replace( "[1]", "", $field_tpl_key );
			}

			$field_template = array(
				'item_name'        => '{' . $field_tpl_key . $xpath_separator . 'item_name[1]}',
				'item_description' => '{' . $field_tpl_key . $xpath_separator . 'item_description[1]}',
				'price'            => '{' . $field_tpl_key . $xpath_separator . 'price[1]}'
			);

			break;
		case 'link':

			if ( ! $is_xml_template ) {
				$field_tpl_key = str_replace( "[1]", "", $field_tpl_key );
			}

			$field_template = array(
				'title'  => '{' . $field_tpl_key . $xpath_separator . 'title[1]}',
				'url'    => '{' . $field_tpl_key . $xpath_separator . 'url[1]}',
				'target' => '{' . $field_tpl_key . $xpath_separator . 'target[1]}'
			);

			break;
		case 'taxonomy':

			$taxonomy_options = array();

			$single_term            = new stdClass;
			$single_term->item_id   = 1;
			$single_term->parent_id = null;
			$single_term->xpath     = $is_xml_template ? '{' . $field_tpl_key . '/term[1]}' : '{' . $field_tpl_key . '}';
			$single_term->assign    = false;

			if ( $implode_delimiter == "|" ) {
				$single_term->xpath = '[str_replace("|", ",", ' . $single_term->xpath . ')]';
			}

			$taxonomy_options[] = $single_term;

			XmlExportACF::$templateOptions['is_multiple_field_value'][ $field_options['key'] ] = "no";

			$field_template = json_encode( $taxonomy_options );

			break;

		case 'clone':

			if ( ! empty( $field_options['clone'] ) ) {
				$sub_fields = array();
				foreach ( $field_options['clone'] as $sub_field_key ) {
					if ( strpos( $sub_field_key, 'group_' ) === 0 ) {
						$acf_groups = get_posts( array(
							'posts_per_page' => 1,
							'post_type'      => 'acf-field-group',
							'name'           => $sub_field_key,
							'post_status'    => 'publish'
						) );
						if ( ! empty( $acf_groups ) ) {
							foreach ( $acf_groups as $acf_group ) {
								$sub_fields = get_posts( array(
									'posts_per_page' => - 1,
									'post_type'      => 'acf-field',
									'post_parent'    => $acf_group->ID,
									'post_status'    => 'publish',
									'orderby'        => 'menu_order',
									'order'          => 'ASC'
								) );
							}
						}
					} else {
						$args     = array(
							'name'           => $sub_field_key,
							'post_type'      => 'acf-field',
							'post_status'    => 'publish',
							'posts_per_page' => 1
						);
						$my_posts = get_posts( $args );
						if ( $my_posts ) {
							$sub_fields[] = $my_posts[0];
						}
					}
				}
				if ( ! empty( $sub_fields ) ) {

					foreach ( $sub_fields as $n => $sub_field ) {

						$sub_field_options          = unserialize( $sub_field->post_content );
						$sub_field_options['label'] = $sub_field->post_title;
						$sub_field_options['name']  = $sub_field->post_excerpt;
						$sub_field_options['ID']    = $sub_field->ID;
						$sub_field_options['key']   = $sub_field->post_name;

						$sub_field_tpl_key                       = $is_xml_template ? $sub_field->post_excerpt : $element_name . '_' . strtolower( $sub_field->post_excerpt );
						$field_template = is_array($field_template) ? $field_template : [];
						$field_template[ $sub_field->post_name ] = XmlExportACF::prepare_import_template( $exportOptions, XmlExportACF::$templateOptions, $acf_list, $sub_field_tpl_key, $sub_field_options );
					}
				}
			}

			break;

		case 'repeater':

			if ( $is_xml_template ) {
				$field_template = array(
					'is_variable' => 'yes',
					'foreach'     => '{' . $field_tpl_key . '/row}',
					'rows'        => array()
				);
			} else {
				$field_template = array(
					'is_variable' => 'csv',
					'separator'   => $implode_delimiter,
					'rows'        => array()
				);
			}

			if ( class_exists( 'acf' ) ) {

				global $acf;

				if ( $acf and version_compare( $acf->settings['version'], '5.0.0' ) >= 0 ) {

					$sub_fields = get_posts( array(
						'posts_per_page' => - 1,
						'post_type'      => 'acf-field',
						'post_parent'    => ( ( ! empty( $field_options['id'] ) ) ? $field_options['id'] : $field_options['ID'] ),
						'post_status'    => 'publish'
					) );

					if ( ! empty( $sub_fields ) ) {

						foreach ( $sub_fields as $n => $sub_field ) {

							$sub_field_options          = unserialize( $sub_field->post_content );
							$sub_field_options['label'] = $sub_field->post_title;
							$sub_field_options['name']  = $sub_field->post_excerpt;
							$sub_field_options['ID']    = $sub_field->ID;
							$sub_field_options['key']   = $sub_field->post_name;

							$sub_field_tpl_key                                    = $is_xml_template ? $sub_field->post_excerpt : $element_name . '_' . strtolower( $sub_field->post_excerpt );
							$field_template['rows']['1'][ $sub_field->post_name ] = XmlExportACF::prepare_import_template( $exportOptions, XmlExportACF::$templateOptions, $acf_list, $sub_field_tpl_key, $sub_field_options, $implode_delimiter );

							XmlExportACF::$templateOptions['is_multiple_field_value'][ $field_options['key'] ]['rows']['1'][ $sub_field->post_name ] = "no";

						}
					}

				} else {
					if ( ! empty( $field['sub_fields'] ) ) {
						foreach ( $field['sub_fields'] as $n => $sub_field ) {
							$sub_field_tpl_key = $is_xml_template ? $sub_field['name'] : $element_name . '_' . strtolower( $sub_field['name'] );

							$field_template['rows']['1'][ $sub_field['key'] ] = XmlExportACF::prepare_import_template( $exportOptions, XmlExportACF::$templateOptions, $acf_list, $sub_field_tpl_key, $sub_field, $implode_delimiter );

							XmlExportACF::$templateOptions['is_multiple_field_value'][ $field_options['key'] ]['rows']['1'][ $sub_field['key'] ] = "no";
						}
					}
				}
			}

			break;

		case 'flexible_content':

			$field_template = array(
				'layouts' => array()
			);

			if ( class_exists( 'acf' ) ) {

				global $acf;

				if ( $acf and version_compare( $acf->settings['version'], '5.0.0' ) >= 0 ) {

					$sub_fields = get_posts( array(
						'posts_per_page' => - 1,
						'post_type'      => 'acf-field',
						'post_parent'    => ( ( ! empty( $field_options['id'] ) ) ? $field_options['id'] : $field_options['ID'] ),
						'post_status'    => 'publish'
					) );

					if ( ! empty( $field_options['layouts'] ) ) {
						foreach ( $field_options['layouts'] as $key => $layout ) {
							$key = intval( $key );

							if ( ! empty( $sub_fields ) ) {
								$field_template['layouts'][ (string) ( $key + 1 ) ]['acf_fc_layout'] = $layout['name'];

								foreach ( $sub_fields as $n => $sub_field ) {
									$sub_field_options = unserialize( $sub_field->post_content );

									if ( $sub_field_options['parent_layout'] == $layout['key'] ) {
										$sub_field_options['label'] = $sub_field->post_title;
										$sub_field_options['name']  = $sub_field->post_excerpt;
										$sub_field_options['ID']    = $sub_field->ID;
										$sub_field_options['key']   = $sub_field->post_name;

										if ( $is_xml_template ) {
											$sub_field_tpl_key = $field_tpl_key . '/' . $layout['name'] . '_' . $key . '[1]/' . $sub_field->post_excerpt;
										} else {
											$sub_field_tpl_key = $element_name . '_' . $layout['name'] . '_' . $key . '_' . strtolower( $sub_field->post_excerpt );
										}

										$field_template['layouts'][ (string) ( $key + 1 ) ][ $sub_field->post_name ] = XmlExportACF::prepare_import_template( $exportOptions, XmlExportACF::$templateOptions, $acf_list, $sub_field_tpl_key, $sub_field_options );

										XmlExportACF::$templateOptions['is_multiple_field_value'][ $field_options['key'] ]['layouts'][ (string) ( $key + 1 ) ][ $sub_field->post_name ] = "no";
									}
								}
							}
						}
					}
				} else {
					if ( ! empty( $field['layouts'] ) ) {
						foreach ( $field['layouts'] as $key => $layout ) {
							$key = intval( $key );

							if ( ! empty( $layout['sub_fields'] ) ) {
								$field_template['layouts'][ (string) ( $key + 1 ) ]['acf_fc_layout'] = $layout['key'];

								foreach ( $layout['sub_fields'] as $n => $sub_field ) {

									if ( $is_xml_template ) {
										$sub_field_tpl_key = $field_tpl_key . '/' . $layout['name'] . '_' . $key . '[1]/' . $sub_field['name'];
									} else {
										$sub_field_tpl_key = $element_name . '_' . $layout['name'] . '_' . $key . '_' . strtolower( $sub_field['name'] );
									}

									$field_template['layouts'][ (string) ( $key + 1 ) ][ $sub_field['key'] ] = XmlExportACF::prepare_import_template( $exportOptions, XmlExportACF::$templateOptions, $acf_list, $sub_field_tpl_key, $sub_field );

									XmlExportACF::$templateOptions['is_multiple_field_value'][ $field_options['key'] ]['layouts'][ (string) ( $key + 1 ) ][ $sub_field['key'] ] = "no";
								}
							}
						}
					}
				}
			}

			break;
	}

	return $field_template;
}