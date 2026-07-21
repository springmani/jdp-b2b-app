<?php

namespace soflyy\wpae_acf_add_on;

class AdvancedAcfFields {
	
	public static function export_acf_field($field_value, $exportOptions, $ID, $pid, &$article, $xmlWriter, &$acfs, $element_name = '', $element_name_ns = '', $fieldSnipped = '', $group_id = '', $preview = false, $return_value = false, $is_sub_field = false, $implode_delimiter = '' )
	{
		global $acf;

		$put_to_csv = true;

		$field_name    = (!empty($exportOptions['cc_label'][$ID])) ? $exportOptions['cc_label'][$ID] : ($exportOptions['name'] ?? '');
		$field_options = (!empty($exportOptions['cc_options'][$ID])) ? maybe_unserialize($exportOptions['cc_options'][$ID]) : $exportOptions;
		$field_settings = (!empty($exportOptions['cc_settings'][$ID])) ? json_decode($exportOptions['cc_settings'][$ID], true) : false;

		$is_xml_export = false;

		if ( ! empty($xmlWriter) and \XmlExportEngine::$exportOptions['export_to'] == 'xml' and ! in_array(\XmlExportEngine::$exportOptions['xml_template_type'], array('custom', 'XmlGoogleMerchants')) ){
			$is_xml_export = true;
		}

		$is_custom_xml_export = false;

		if ( \XmlExportEngine::$exportOptions['export_to'] == 'xml' and in_array(\XmlExportEngine::$exportOptions['xml_template_type'], array('custom')) ){
			$is_custom_xml_export = true;
		}

		if ($field_options['type'] == 'message'){
			$field_value = empty($field_options['message']) ? '' : $field_options['message'];
		}

		if ( ! empty($field_value))
		{
			$field_value = maybe_unserialize($field_value);

			if ( empty($implode_delimiter) ) {
				$implode_delimiter = \XmlExportEngine::$implode;
			}

			switch ($field_options['type'])
			{
				case 'file':
				case 'image':
					if (is_numeric($field_value))
					{
						$field_value = wp_get_attachment_url($field_value);
					}
					elseif(is_array($field_value))
					{
						$field_value = empty($field_value['url']) ? '' : $field_value['url'];
					}
					break;

				case 'gallery':
					$v = array();

					foreach ($field_value as $key => $item)
					{
						if (!empty($item['url'])){
							$v[] = $item['url'];
						}
						elseif (is_numeric($item)){
							$gallery_item_url = wp_get_attachment_url($item);
							if (!empty($gallery_item_url)){
								$v[] = $gallery_item_url;
							}
						}
						elseif ( gettype( $item ) == 'string' ) {
							$v[] = $item;
						}
					}
					$field_value = implode($implode_delimiter, $v);

					break;
				case 'location-field':
					$localion_parts = explode("|", $field_value);

					if ($is_xml_export)
					{
						if ( ! empty($localion_parts) ){

							$xmlWriter->beginElement($element_name_ns, $element_name, null);
							$xmlWriter->startElement('address');
							$xmlWriter->writeData($localion_parts[0], 'address');
							$xmlWriter->closeElement();

							if (!empty($localion_parts[1])){
								$coordinates = explode(",", $localion_parts[1]);
								if (!empty($coordinates)){
									$xmlWriter->startElement('lat');
									$xmlWriter->writeData($coordinates[0], 'lat');
									$xmlWriter->closeElement();
									$xmlWriter->startElement('lng');
									$xmlWriter->writeData($coordinates[1], 'lng');
									$xmlWriter->closeElement();
								}
							}
							$xmlWriter->closeElement();

						}
					}
					else
					{
						if ( ! $return_value )
						{
							$acfs[$element_name] = array(
								$element_name . '_address',
								$element_name . '_lat',
								$element_name . '_lng'
							);

							if ( ! empty($localion_parts) )
							{
								$article[$element_name . '_address'] = $localion_parts[0];
								if (!empty($localion_parts[1]))
								{
									$coordinates = explode(",", $localion_parts[1]);
									if (!empty($coordinates))
									{
										$article[$element_name . '_lat'] = $coordinates[0];
										$article[$element_name . '_lng'] = $coordinates[1];
									}
								}
							}
						}
						else
						{
							if ( ! empty($localion_parts) )
							{
								$return_value = array(
									'address' => $localion_parts[0],
								);
								if (!empty($localion_parts[1]))
								{
									$coordinates = explode(",", $localion_parts[1]);
									if (!empty($coordinates))
									{
										$return_value['lat'] = $coordinates[0];
										$return_value['lng'] = $coordinates[1];
									}
								}
							}
						}
					}

					$put_to_csv = false;
					break;

				case 'paypal_item':

					if ($is_xml_export)
					{
						$xmlWriter->beginElement($element_name_ns, $element_name, null);
						if ( is_array($field_value) ){
							foreach ($field_value as $key => $value) {
								$xmlWriter->beginElement($element_name_ns, $key, null);
								$xmlWriter->writeData($value, $key);
								$xmlWriter->closeElement();
							}
						}
						$xmlWriter->closeElement();
					}
					else
					{
						if ( ! $return_value )
						{
							if ( $is_custom_xml_export ){
								$article[$element_name] = json_encode($field_value);
							}
							else{
								$acfs[$element_name] = array($element_name . '_item_name', $element_name . '_item_description', $element_name . '_price');

								if ( is_array($field_value) )
								{
									foreach ($field_value as $key => $value)
									{
										$article[$element_name . '_' . $key] = $value;
									}
								}
							}
						}
					}

					$put_to_csv = false;

					break;

				case 'google_map':

					if ($is_xml_export)
					{
						$xmlWriter->beginElement($element_name_ns, $element_name, null);
						$xmlWriter->startElement('address');
						$xmlWriter->writeData($field_value['address'], 'address');
						$xmlWriter->closeElement();
						$xmlWriter->startElement('lat');
						$xmlWriter->writeData($field_value['lat'], 'lat');
						$xmlWriter->closeElement();
						$xmlWriter->startElement('lng');
						$xmlWriter->writeData($field_value['lng'], 'lng');
						$xmlWriter->closeElement();
						$xmlWriter->closeElement();
					}
					else
					{
						if ( ! $return_value )
						{
							if ( $is_custom_xml_export ){
								$article[$element_name] = json_encode($field_value);
							}
							else{
								$acfs[$element_name] = array($element_name . '_address', $element_name . '_lat', $element_name . '_lng');
								$article[$element_name . '_address'] = $field_value['address'];
								$article[$element_name . '_lat'] = $field_value['lat'];
								$article[$element_name . '_lng'] = $field_value['lng'];
							}
						}
					}
					$put_to_csv = false;

					break;

				case 'link':

					if ($is_xml_export)
					{
						if(is_array($field_value)) {
							$xmlWriter->beginElement($element_name_ns, $element_name, null);
							$xmlWriter->startElement('title');
							$xmlWriter->writeData($field_value['title'], 'title');
							$xmlWriter->closeElement();
							$xmlWriter->startElement('url');
							$xmlWriter->writeData($field_value['url'], 'url');
							$xmlWriter->closeElement();
							$xmlWriter->startElement('target');
							$xmlWriter->writeData($field_value['target'], 'target');
							$xmlWriter->closeElement();
							$xmlWriter->closeElement();
						} else {

							$xmlWriter->beginElement($element_name_ns, $element_name, null);
							$xmlWriter->writeData($field_value, 'link');
							$xmlWriter->closeElement();
						}
					}
					else
					{
						if ( ! $return_value )
						{
							if ( $is_custom_xml_export ){
								$article[$element_name] = json_encode($field_value);
							}
							else{
								if(is_array($field_value)) {
									$acfs[$element_name] = array($element_name . '_title', $element_name . '_url', $element_name . '_target');
									$article[$element_name . '_title'] = $field_value['title'];
									$article[$element_name . '_url'] = $field_value['url'];
									$article[$element_name . '_target'] = $field_value['target'];
								} else {

									$acfs[$element_name] = $element_name;
									$article[$element_name] = $field_value;

								}
							}
						}
					}
					$put_to_csv = false;

					break;

				case 'acf_cf7':
				case 'gravity_forms_field':

					if ( ! empty($field_options['multiple']) )
					{
						$field_value = implode($implode_delimiter, $field_value);
					}

					break;

				case 'page_link':

					if (is_array($field_value))
					{
						$field_value = implode($implode_delimiter, $field_value);
					}

					break;

				case 'post_object':

					if ( ! empty($field_options['multiple'])){
						$v = array();

						if( !is_array($field_value) ){
							$field_value = [$field_value];
						}

						foreach ($field_value as $key => $pid) {

							if (is_numeric($pid)){
								$entry = get_post($pid);
								if ($entry)
								{
									$v[] = $entry->post_name;
								}
							}
							else{
								$v[] = $pid->post_name;
							}
						}
						$field_value = implode($implode_delimiter, $v);
					}
					else{
						if (is_numeric($field_value)){
							$entry = get_post($field_value);
							if ($entry)
							{
								$field_value = $entry->post_name;
							}
						}
						else{
							$field_value = $field_value->post_name;
						}
					}

					break;

				case 'relationship':

					$v = array();

					$field_value = is_array($field_value) ? $field_value : [$field_value];

					foreach ($field_value as $key => $pid) {
						$entry = get_post($pid);
						if ($entry)
						{
							$v[] = $entry->post_title;
						}
					}
					$field_value = implode($implode_delimiter, $v);

					break;

				case 'user':

					if ( ! empty($field_options['multiple'])){
						$v = array();
						foreach ($field_value as $key => $user) {
							if (is_numeric($user)){
								$entry = get_user_by('ID', $user);
								if ($entry)
								{
									$v[] = $entry->user_email;
								}
							}
							else{
								$v[] = is_array($user) ? $user['user_email'] : ($user->user_email ?? '');
							}
						}
						$field_value = implode($implode_delimiter, $v);
					}
					else{
						if (is_numeric($field_value)){
							$entry = get_user_by('ID', $field_value);
							if ($entry)
							{
								$field_value = $entry->user_email;
							}
						}
						else{

							$field_value = is_array($field_value) ? $field_value['user_email'] : ($field_value->user_email ?? '');
						}
					}

					break;

				case 'taxonomy':

					if ($is_xml_export)
					{
						$xmlWriter->beginElement($element_name_ns, $element_name, null);

						if ( ! in_array($field_options['field_type'], array('radio', 'select'))){
							foreach ($field_value as $key => $tid) {
								$entry = get_term($tid , $field_options['taxonomy']);
								if ($entry and !is_wp_error($entry))
								{
									$xmlWriter->startElement('term');
									$xmlWriter->writeData($entry->name, 'term');
									$xmlWriter->closeElement();
								}
							}
						}
						else{
							$entry = get_term($field_value, $field_options['taxonomy']);
							if ($entry)
							{
								$xmlWriter->startElement('term');
								$xmlWriter->writeData($entry->name, 'term');
								$xmlWriter->closeElement();
							}
						}

						$xmlWriter->closeElement();

						$put_to_csv = false;
					}
					else
					{
						if ( ! in_array($field_options['field_type'], array('radio', 'select'))){
							$v = array();
							foreach ($field_value as $key => $tid) {
								$entry = get_term($tid , $field_options['taxonomy']);
								if ($entry and !is_wp_error($entry))
								{
									$v[] = $entry->name;
								}
							}
							$field_value = implode($implode_delimiter, $v);
						}
						else{
							$entry = get_term($field_value, $field_options['taxonomy']);
							if ($entry)
							{
								$field_value = $entry->name;
							}
						}
					}

					break;

				case 'select':

					if ( ! empty($field_options['multiple']))
					{
						if(is_array($field_value)) {
							$field_value = implode($implode_delimiter, array_map(function($value) {
								return maybe_serialize($value);
							}, $field_value));
						}
					}

					if(is_array($field_value)) {
						$field_value = implode($implode_delimiter, $field_value);
					}

					break;

				case 'checkbox':
					if ( is_array($field_value)) {
						foreach ($field_value as $field_value_key => $field_value_value) {
							if (is_array($field_value_value)) {
								$field_value[$field_value_key] = $field_value_value['value'];
							}
						}
						$field_value = implode($implode_delimiter, $field_value);
					}

					break;

				case 'clone':

					if ( ! empty($field_options['clone']) ) {
						$values = maybe_unserialize($field_value);

						$sub_fields = array();
						foreach ($field_options['clone'] as $sub_field_key) {

							if (strpos($sub_field_key, 'group_') === 0){
								$acf_groups = get_posts(array(
									'posts_per_page' => 1,
									'post_type' => 'acf-field-group',
									'name' => $sub_field_key,
									'post_status' => 'publish'
								));
								if (!empty($acf_groups)){
									foreach ($acf_groups as $acf_group){
										$sub_fields = get_posts(array('posts_per_page' => -1, 'post_type' => 'acf-field', 'post_parent' => $acf_group->ID, 'post_status' => 'publish', 'orderby' => 'menu_order', 'order' => 'ASC'));
									}
								}
							}
							else{
								$args = array(
									'name' => $sub_field_key,
									'post_type' => 'acf-field',
									'post_status' => 'publish',
									'posts_per_page' => 1
								);
								$my_posts = get_posts($args);
								if ($my_posts) {
									$sub_fields[] = $my_posts[0];
								}
							}
						}
						if ( ! empty($sub_fields) ){

							foreach ($sub_fields as $sub_field){

								$field_value = isset($values[$sub_field->post_excerpt]) ? $values[$sub_field->post_excerpt] : '';

								$sub_field_name = empty($sub_field->post_excerpt) ? str_replace("-","_", sanitize_title($sub_field->post_title)) : $sub_field->post_excerpt;

								$field_options = maybe_unserialize($sub_field->post_content);

								$sub_field_value = \XMLExportACF::export_acf_field(
									$field_value,
									$field_options,
									false,
									$pid,
									$article,
									$xmlWriter,
									$acfs,
									$is_xml_export ? $sub_field_name : $element_name . '_' . $sub_field_name,
									$element_name_ns,
									'',
									'',
									$preview,
									$is_xml_export ? false : true,
									true,
									$implode_delimiter
								);

								if ( ! isset( $acfs[ $element_name ] ) || is_array( $acfs[ $element_name ] ) ) {
									$acfs[$element_name][] = $element_name . '_' . $sub_field_name;
								}

								if(is_array($sub_field_value) && empty($sub_field_value)) {
									$sub_field_value = '';
								}

								$article[$element_name . '_' . $sub_field_name] = ($preview) ? trim(preg_replace('~[\r\n]+~', ' ', htmlspecialchars($sub_field_value))) : $sub_field_value;
							}
						}
					}

					$put_to_csv = false;

					break;

				case 'group':

					if (!empty($field_options['id'])) {
						$sub_fields = get_posts(array('posts_per_page' => -1, 'post_type' => 'acf-field', 'post_parent' => $field_options['id'], 'post_status' => 'publish', 'orderby' => 'menu_order', 'order' => 'ASC'));
						if ( ! empty($sub_fields) ) {
							$values = maybe_unserialize($field_value);
							foreach ($sub_fields as $sub_field) {
								$field_value = isset($values[$sub_field->post_excerpt]) ? $values[$sub_field->post_excerpt] : '';
								$sub_field_name = empty($sub_field->post_excerpt) ? str_replace("-","_", sanitize_title($sub_field->post_title)) : $sub_field->post_excerpt;
								$sub_field_options = maybe_unserialize($sub_field->post_content);
								$sub_field_value = \XMLExportACF::export_acf_field(
									$field_value,
									$sub_field_options,
									false,
									$pid,
									$article,
									$xmlWriter,
									$acfs,
									$is_xml_export ? $sub_field_name : $element_name . '_' . $sub_field_name,
									$element_name_ns,
									'',
									'',
									$preview,
									$is_xml_export ? false : true,
									true,
									$implode_delimiter
								);

								if ( ! isset( $acfs[ $element_name ] ) || is_array( $acfs[ $element_name ] ) ) {
									$acfs[$element_name][] = $element_name . '_' . $sub_field_name;
								}


								if(is_array($sub_field_value) && empty($sub_field_value)) {
									$sub_field_value = '';
								}

								if ( $sub_field_value === "0" || $sub_field_value === 0 ) {
									$sub_field_value = "0";
								}

								if(is_array($sub_field_value)){
									$sub_field_value = json_encode($sub_field_value);
								}

								$article[$element_name . '_' . $sub_field_name] = ($preview) ? trim(preg_replace('~[\r\n]+~', ' ', htmlspecialchars($sub_field_value))) : $sub_field_value;                                }
						}
					}

					$put_to_csv = false;

					break;

				case 'repeater':


					if ($is_xml_export) $xmlWriter->beginElement($element_name_ns, $element_name, null);

					// If there are blocks that might contain acf fields, inject the values into the ACF meta stores
					// so that the default wpae functionality will work
					if(has_blocks($pid)) {
						$entry = get_post($pid);
						$blocks = parse_blocks($entry->post_content);

						foreach ($blocks as $block) {
							if (!empty($block['blockName']) && strpos($block['blockName'], 'acf/') !== false) {
								if(isset($block['attrs']['data']) && $block['attrs']['data']) {
									acf_setup_meta($block['attrs']['data'], $pid);
									// acf_setup_meta will only retain the data added via the last call of it. We must ensure our target field is in that call.
									if(array_key_exists($field_name, $block['attrs']['data'])){
										break;
									}
								}
							}
						}
					}
					if( have_rows($field_name, $pid) ):
						$rowValues = array();

						$repeater_sub_field_names = array();

						while( have_rows($field_name, $pid) ):

							the_row();

							$row = \XMLExportACF::acf_get_row();

							if ($is_xml_export) $xmlWriter->startElement('row');

							$repeater_sub_fields = [];
							foreach ($row['field']['sub_fields'] as $sub_field) {
								if ($sub_field['type'] == 'group') {
									foreach ($sub_field['sub_fields'] as $sf) {
										$sf['parent_field'] = $sub_field;
										$repeater_sub_fields[] = $sf;
									}
								} else {
									$repeater_sub_fields[] = $sub_field;
								}
							}

							foreach ($repeater_sub_fields as $sub_field) {

								if ($acf and version_compare($acf->settings['version'], '5.0.0') >= 0) {
									if (isset($sub_field['parent_field'])) {
										$v = $row['value'][ $row['i'] ][$sub_field['parent_field']['key']][ $sub_field['key'] ];
									} else {
										$v = $row['value'][ $row['i'] ][ $sub_field['key'] ];
									}
									$cache_slug = "format_value/post_id=".$row['post_id']."/name={$sub_field['name']}";
									wp_cache_delete($cache_slug, 'acf');

									if ($acf and version_compare($acf->settings['version'], '5.7.10') >= 0) {
										$store = acf_get_store('values');
										$store->remove($row['post_id'] . ":" . $sub_field['name'] . ":formatted");
									}

									if ($is_xml_export) $v = acf_format_value($v, $row['post_id'], $sub_field);
								}
								else
								{
									$v = get_sub_field($sub_field['name']);
								}

								if ($preview && ! $is_xml_export){
									switch ($sub_field['type']) {
										case 'textarea':
										case 'oembed':
										case 'wysiwyg':
										case 'wp_wysiwyg':
										case 'date_time_picker':
										case 'date_picker':
											$v = preg_replace( "/\r|\n/", "", esc_html($v) );
											break;
										default:
											break;
									}
								}

								$sub_field['delimiter'] = $implode_delimiter;

								$sub_field_name = empty($sub_field['name']) ? str_replace("-","_", sanitize_title($sub_field['label'])) : $sub_field['name'];

								$sub_field_value = \XMLExportACF::export_acf_field(
									$v,
									$sub_field,
									false,
									$pid,
									$article,
									$xmlWriter,
									$acfs,
									$is_xml_export ? $sub_field_name : $element_name . '_' . $sub_field_name,
									$element_name_ns,
									$fieldSnipped,
									'',
									$preview,
									$is_xml_export ? false : true,
									true,
									($implode_delimiter == '|' )  ? ',' : '|'
								);

								if ( ! $is_xml_export )
								{
									switch ($sub_field['type'])
									{
										case 'google_map':
										case 'paypal_item':
										case 'location-field':
											if ( ! empty($sub_field_value))
											{
												foreach ($sub_field_value as $repeater_key => $repeater_value)
												{
													$rowValues[$sub_field['name']][$repeater_key][] = (is_array($repeater_value)) ? implode($exportOptions['delimiter'], $repeater_value) : $repeater_value;
												}
											}
											break;
										case 'repeater':
											if ( ! empty($sub_field_value)){
												foreach ($sub_field_value as $repeater_key => $repeater_value) {
													if (is_array($repeater_value)){
														$rv = array();
														foreach ($repeater_value as $repeater_key2 => $repeater_value2) {
															$rv[] = (is_array($repeater_value2)) ? implode($exportOptions['delimiter'], $repeater_value2) : $repeater_value2;
														}
														$rowValues[$sub_field_name][$repeater_key][] = implode($exportOptions['delimiter'], $rv);
													}
													else{
														$rowValues[$sub_field_name][$repeater_key][] = $repeater_value;
													}
												}
											}
											break;
										case 'flexible_content':
											if( !empty($sub_field_value)){

												if(is_array($sub_field_value)) {
													$rowValues[ $sub_field_name ][] = print_r( $sub_field_value, true );
												}else{
													$rowValues[ $sub_field_name ][] = $sub_field_value;
												}

											}
											break;
										default:
											$sub_field_name = empty($sub_field['name']) ? str_replace("-","_", sanitize_title($sub_field['label'])) : $sub_field['name'];
											$rowValues[$sub_field_name][] = apply_filters('pmxe_acf_field', pmxe_filter( (is_array($sub_field_value)) ? implode($exportOptions['delimiter'], $sub_field_value) : $sub_field_value, $fieldSnipped), $sub_field_name, $pid);
											break;
									}
								}
							}

							if ($is_xml_export) $xmlWriter->closeElement();

						endwhile;

						if ($return_value) return $rowValues;

						if ( ! $is_xml_export )
						{
							$additional_articles = array();

							$sub_repeater_delimiter = apply_filters('wp_all_export_repeater_delimiter', $exportOptions['delimiter'] == ',' ? '|' : ',',  \XmlExportEngine::$exportID);
							foreach ($rowValues as $key => $values)
							{
								$is_have_subvalues = array_filter(array_keys($values), 'is_numeric');

								if (empty($is_have_subvalues))
								{
									foreach ($values as $subkey => $subvalue)
									{
										if ( ! in_array($element_name . '_' . $key . '_' . $subkey, $repeater_sub_field_names))
										{
											$repeater_sub_field_names[] = $element_name . '_' . $key . '_' . $subkey;
										}
										// Display each repeater row in its own csv line
										if ( ! empty($field_settings) and $field_settings['repeater_field_item_per_line'] )
										{
											$base_value = array_shift($subvalue);

											$article[$element_name . '_' . $key . '_' . $subkey] = ($preview) ? trim(preg_replace('~[\r\n]+~', ' ', htmlspecialchars($base_value))) : $base_value;

											if ( ! empty($subvalue))
											{
												foreach ($subvalue as $i => $addRowValue)
												{
													$additional_articles[$i]['settings'] = $field_settings;
													$additional_articles[$i]['content'][$element_name . '_' . $key . '_' . $subkey] = $addRowValue;
												}
											}
										}
										else
										{
											if ($is_custom_xml_export){
												$article[$element_name][$key . '_' . $subkey] = apply_filters('pmxe_acf_field', pmxe_filter(($preview) ? trim(preg_replace('~[\r\n]+~', ' ', htmlspecialchars(implode("|", $subvalue)))) : implode("|", $subvalue), $fieldSnipped), $key . '_' . $subkey, $pid);
											}
											else{
												$article[$element_name . '_' . $key . '_' . $subkey] = apply_filters('pmxe_acf_field', pmxe_filter(($preview) ? trim(preg_replace('~[\r\n]+~', ' ', htmlspecialchars(implode($sub_repeater_delimiter, $subvalue)))) : implode($sub_repeater_delimiter, $subvalue), $fieldSnipped), $element_name . '_' . $key . '_' . $subkey, $pid);
											}
										}
									}

								}
								else
								{
									if ( ! in_array($element_name . '_' . $key, $repeater_sub_field_names))
									{
										$repeater_sub_field_names[] = $element_name . '_' . $key;
									}
									// Display each repeater row in its own csv line
									if ( ! empty($field_settings) and $field_settings['repeater_field_item_per_line'] )
									{
										$base_value = array_shift($values);

										$article[$element_name . '_' . $key] = ($preview) ? trim(preg_replace('~[\r\n]+~', ' ', htmlspecialchars($base_value))) : $base_value;

										if ( ! empty($values))
										{
											foreach ($values as $i => $addRowValue)
											{
												$additional_articles[$i]['settings'] = $field_settings;
												$additional_articles[$i]['content'][$element_name . '_' . $key]	= $addRowValue;
											}
										}
									}
									else
									{
										if ($is_custom_xml_export){
											$article[$element_name][$key] = apply_filters('pmxe_acf_field', pmxe_filter(($preview) ? trim(preg_replace('~[\r\n]+~', ' ', htmlspecialchars(implode("|", $values)))) : implode("|", $values), $fieldSnipped), $key, $pid);
										}
										else{
											$article[$element_name . '_' . $key] = apply_filters('pmxe_acf_field', pmxe_filter(($preview) ? trim(preg_replace('~[\r\n]+~', ' ', htmlspecialchars(implode($sub_repeater_delimiter, $values)))) : implode($sub_repeater_delimiter, $values), $fieldSnipped), $element_name . '_' . $key, $pid);
										}
									}
								}
							}

							if ($is_custom_xml_export){
								$article[$element_name] = serialize($article[$element_name]);
							}

							if ( ! empty($repeater_sub_field_names)) $acfs[$element_name] = $repeater_sub_field_names;

							if ( ! empty($additional_articles) )
							{
								foreach ($additional_articles as $i => $additional_article) {
									\XMLExportACF::$additional_articles[] = $additional_article;
								}
							}
						}

					endif;

					if ($is_xml_export) $xmlWriter->closeElement();

					$put_to_csv = false;

					break;

				case 'flexible_content':

					if ( ! $is_sub_field ){
						$acf_flexible = get_option('wp_all_export_acf_flexible_' . \XmlExportEngine::$exportID, array());
						\XMLExportACF::$fc_sub_field_names = empty($acf_flexible[$element_name]) ? array() : $acf_flexible[$element_name];
					}

					if ($is_xml_export) $xmlWriter->beginElement($element_name_ns, $element_name, null);

					// check if the flexible content field has rows of data
					if( have_rows($field_name, $pid) ):

						// loop through the rows of data
						while ( have_rows($field_name, $pid) ) : the_row();

							$row = \XMLExportACF::acf_get_row();

							foreach ($row['field']['layouts'] as $layout) {

								if ($layout['name'] == $row['value'][ $row['i'] ]['acf_fc_layout']){

									if ($is_xml_export) $xmlWriter->startElement(preg_replace('#^\d+#', '', $row['value'][ $row['i'] ]['acf_fc_layout']) . '_' . $row['i']);

									foreach ($layout['sub_fields'] as $sub_field) {

										$layout_field_name = $element_name . '_' . $layout['name'] . '_' . $row['i'];

										$v = '';

										if(version_compare($acf->settings['version'], '5.0.0') >= 0) {
											$fieldValueKey = 'key';
										} else {
											$fieldValueKey = 'name';
										}

										if (isset($row['value'][ $row['i']][ $sub_field[$fieldValueKey]]))
										{
											$v = $row['value'][ $row['i']][$sub_field[$fieldValueKey]];

											if ($is_xml_export && $sub_field['value'] != 'gallery')
											{
												// apply filters
												// version 6.2.5 of ACF requires a 4th parameter for these for
												// $escape_html (boolean)
												$v = apply_filters( "acf/format_value", $v, $pid, $sub_field, false );
												$v = apply_filters( "acf/format_value/type={$sub_field['type']}", $v, $pid, $sub_field, false );
												$v = apply_filters( "acf/format_value/name={$sub_field['_name']}", $v, $pid, $sub_field, false );
												$v = apply_filters( "acf/format_value/key={$sub_field['key']}", $v, $pid, $sub_field, false );
											}
										}

										if ($preview && ! $is_xml_export){
											switch ($sub_field['type']) {
												case 'textarea':
												case 'oembed':
												case 'wysiwyg':
												case 'wp_wysiwyg':
												case 'date_time_picker':
												case 'date_picker':
													$v = preg_replace( "/\r|\n/", "", esc_html($v) );
													break;
												default:
													break;
											}
										}

										$sub_field['delimiter'] = $implode_delimiter;

										$sub_field_values = \XMLExportACF::export_acf_field(
											$v,
											$sub_field,
											false,
											$pid,
											$article,
											$xmlWriter,
											$acfs,
											$is_xml_export ? $sub_field['name'] : $layout_field_name . '_' . $sub_field['name'],
											$element_name_ns,
											$fieldSnipped,
											'',
											$preview,
											$is_xml_export ? false : true,
											true,
											$implode_delimiter
										);

										if ( ! $is_xml_export )
										{
											switch ($sub_field['type'])
											{
												case 'repeater':

													if ( ! empty($sub_field_values))
													{
														foreach ($sub_field_values as $key => $values) {
															$article[$layout_field_name . '_' . $key] =  ($preview) ? trim(preg_replace('~[\r\n]+~', ' ', htmlspecialchars(implode($implode_delimiter, $values)))) : implode($implode_delimiter, $values);
															if ( ! in_array($layout_field_name . '_' . $key, \XMLExportACF::$fc_sub_field_names)) \XMLExportACF::$fc_sub_field_names[] = $layout_field_name . '_' . $key;
														}
													}

													break;
												case 'flexible_content':


													break;

												default:

													$article[$layout_field_name . '_' . $sub_field['name']] = is_array($sub_field_values) ? implode($implode_delimiter, $sub_field_values) : $sub_field_values;

													if ( ! in_array($layout_field_name . '_' . $sub_field['name'], \XMLExportACF::$fc_sub_field_names))
														\XMLExportACF::$fc_sub_field_names[] = $layout_field_name . '_' . $sub_field['name'];

													break;
											}
										}
									}
									if ($is_xml_export) $xmlWriter->closeElement();
								}
							}

						endwhile;

					else :

						// no layouts found

					endif;

					if ($is_xml_export) $xmlWriter->closeElement();

					$put_to_csv = false;

					break;

				default:

					break;
			}
		}

		if ($return_value) return $field_value;

		if ( ! empty(\XMLExportACF::$fc_sub_field_names)){
			$acf_flexible = get_option('wp_all_export_acf_flexible_' . \XmlExportEngine::$exportID, array());
			$acfs[$element_name] = \XMLExportACF::$fc_sub_field_names;
			$acf_flexible[$element_name] = \XMLExportACF::$fc_sub_field_names;
			update_option('wp_all_export_acf_flexible_' . \XmlExportEngine::$exportID, $acf_flexible);
			\XMLExportACF::$fc_sub_field_names = array();
		}

		if ($put_to_csv)
		{
			$val = apply_filters(
				'pmxe_acf_field',
				pmxe_filter(
					( ! empty($field_value) || $field_value === 0 || $field_value === "0" )
						? ( (!is_serialized($field_value))
						? maybe_serialize($field_value)
						: $field_value )
						: '',
					$fieldSnipped
				),
				$field_name,
				$pid
			);

			if ($is_xml_export)
			{
				$elementOpenResponse = $xmlWriter->beginElement($element_name_ns, $element_name, null);
				if($elementOpenResponse) {
					if($val === 0 || $val === "0") {
						$val = "0";
					}
					$xmlWriter->writeData($val, $element_name);
					$xmlWriter->closeElement();
				}
			}
			else
			{
				if($field_value === 0 || $field_value === "0") {
					$val = 0;
				}

				// $article[$element_name] = ($preview) ? trim(preg_replace('~[\r\n]+~', ' ', htmlspecialchars($val))) : $val;
				wp_all_export_write_article( $article, $element_name, ($preview) ? trim(preg_replace('~[\r\n]+~', ' ', htmlspecialchars($val))) : $val);
				if ( ! isset($acfs[$element_name]) && ! in_array($field_options['type'], array('repeater', 'clone'))) $acfs[$element_name] = $element_name;
			}
		}
	}

}