<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists('XmlExportACF') )
{
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Class name is part of the API contract with WP All Export Pro which references it directly.
	final class XmlExportACF
	{

		private const PRO_FIELDS = [
			'button_group',
			'checkbox',
			'clone',
			'group',
			'radio',
			'repeater',
			'select',
			'true_false',
			'acf_cf7',
			'file',
			'flexible_content',
			'fontawesome',
			'gallery',
			'google_map',
			'google_map_extended',
			'gravity_forms_field',
			'image',
			'image_aspect_ratio_crop',
			'image_crop',
			'limiter',
			'link',
			'location-field',
			'oembed',
			'page_link',
			'paypal_item',
			'post_object',
			'range',
			'relationship',
			'star_rating',
			'table',
			'taxonomy',
			'user',
			'validated_field',
			'vimeo'
		];

        public static $acfs,$put_to_csv,$article,$return_value,$templateOptions;

		private $_existing_acf_meta_keys = array();

		private $_acf_groups = array();

		public function __construct()
		{
			add_filter("wp_all_export_csv_rows", array( &$this, "filter_csv_rows"), 10, 3);
		}

		public function init( & $existing_meta_keys = array() ){

			if ( ! class_exists( 'acf' ) ) return;

			global $acf;

			if ($acf and version_compare($acf->settings['version'], '5.0.0') >= 0){

				$saved_acfs = get_posts(array('posts_per_page' => -1, 'post_type' => 'acf-field-group'));

				if (function_exists('acf_local')) {
					$acfs = acf_local()->groups;
				}
				if (empty($acfs ) && function_exists('acf_get_local_field_groups')) {
					$acfs  = acf_get_local_field_groups();
				}

				if ( ! empty($acfs) and is_array($acfs)) $this->_acf_groups = $acfs;

			}
			else{

				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- ACF core filter hook
			$this->_acf_groups = apply_filters('acf/get_field_groups', array());

			}

			if ( ! empty($saved_acfs) ){
				foreach ($saved_acfs as $key => $obj) {
					if ( ! isset($acfs[$obj->post_name]))
					{
						$this->_acf_groups[] = array(
							'ID' => $obj->ID,
							'title' => $obj->post_title
						);
					}
				}
			}

			if ( ! empty($this->_acf_groups) ){

				foreach ($this->_acf_groups as $key => $acfObj)
				{
					if (empty($this->_acf_groups[$key]['ID']) and ! empty($this->_acf_groups[$key]['key']))
					{
						$this->_acf_groups[$key]['ID'] = $acfs[$key]['key'];
					}
                    elseif (empty($this->_acf_groups[$key]['ID']) and ! empty($this->_acf_groups[$key]['id']))
					{
						$this->_acf_groups[$key]['ID'] = $this->_acf_groups[$key]['id'];
					}
				}

				// get all ACF fields
				if ($acf->settings['version'] and version_compare($acf->settings['version'], '5.0.0') >= 0)
				{

					foreach ($this->_acf_groups as $key => $acf_obj) {

						if ( is_numeric($acf_obj['ID'])){

							$acf_fields = get_posts(array('posts_per_page' => -1, 'post_type' => 'acf-field', 'post_parent' => $acf_obj['ID'], 'post_status' => 'publish', 'orderby' => 'menu_order', 'order' => 'ASC'));

							if ( ! empty($acf_fields) ){

								foreach ($acf_fields as $field) {

									$fieldData = (!empty($field->post_content)) ? maybe_unserialize($field->post_content) : array();

									$fieldData['ID']    = $field->ID;
									$fieldData['id']    = $field->ID;
									$fieldData['label'] = $field->post_title;
									$fieldData['key']   = $field->post_name;

									if (in_array($fieldData['type'], array('tab'))) continue;

									if (empty($fieldData['name'])) $fieldData['name'] = $field->post_excerpt;

									if ( ! empty($fieldData['name'])){
										$this->_existing_acf_meta_keys[] = $fieldData['name'];
									}

									$this->_acf_groups[$key]['fields'][] = $fieldData;

								}
							}
						}
						else
						{
							if (function_exists('acf_local')) {
								$acf_fields = acf_local()->fields;
							}
							if (empty($acf_fields) && function_exists('acf_get_local_fields')) {
								$acf_fields = acf_get_local_fields();
							}

							if ( ! empty($acf_fields) )
							{
								foreach ($acf_fields as $field_key => $field)
								{
									if ($field['parent'] == $acf_obj['key'])
									{
										$fieldData = $field;

										// Exclude block message fields.
										if( strpos($fieldData['name'], 'block') !== false && $fieldData['type'] == 'message') continue;

										// Don't include non-data field types (such as those used for visual organization).
										if (in_array($fieldData['type'], array('tab'))) continue;

										if (empty($fieldData['ID']))
										{
											$fieldData['ID'] = $fieldData['id'] = uniqid();
										}

										if ( ! empty($fieldData['name'])){
											$this->_existing_acf_meta_keys[] = $fieldData['name'];
										}

										$this->_acf_groups[$key]['fields'][] = $fieldData;
									}
								}
							}
						}
					}
				}
				else
				{
					foreach ($this->_acf_groups as $key => $acf_obj) {

						$fields = array();

						if (is_numeric($acf_obj['id'])){

							foreach (get_post_meta($acf_obj['id'], '') as $cur_meta_key => $cur_meta_val)
							{
								if (strpos($cur_meta_key, 'field_') !== 0) continue;

								$fields[] = (!empty($cur_meta_val[0])) ? maybe_unserialize($cur_meta_val[0]) : array();

							}
						}
						else
						{
							global $acf_register_field_group;

							if ( ! empty($acf_register_field_group) )
							{
								foreach ($acf_register_field_group as $group)
								{
									if ($group['id'] == $acf_obj['ID'])
									{
										foreach ($group['fields'] as $field)
										{
											$fields[] = $field;
										}
									}
								}
							}
						}

						if (count($fields)){

							$sortArray = array();

							foreach($fields as $field){
								foreach($field as $key2=>$value){
									if(!isset($sortArray[$key2])){
										$sortArray[$key2] = array();
									}
									$sortArray[$key2][] = $value;
								}
							}

							$orderby = "order_no";

							if(isset($sortArray[$orderby]) && is_array($sortArray[$orderby])) {
								array_multisort($sortArray[$orderby], SORT_ASC, $fields);
							}
							foreach ($fields as $field){
								if (in_array($field['type'], array('tab'))) continue;
								$this->_acf_groups[$key]['fields'][] = $field;
								if ( ! empty($field['name'])) $this->_existing_acf_meta_keys[] = $field['name'];
							}
						}
					}
				}

				if ( ! empty($existing_meta_keys)){
					foreach ($existing_meta_keys as $key => $meta_key) {
						foreach ($this->_existing_acf_meta_keys as $acf_key => $acf_value) {
							if (in_array($meta_key, array($acf_value, "_" . $acf_value))) {
								unset($existing_meta_keys[$key]);
							}
						}
					}
				}
			}
		}

		public static $additional_articles = array();

		public static $fc_sub_field_names = array();

		public static function export_acf_field($field_value, $exportOptions, $ID, $pid, &$article, $xmlWriter, &$acfs, $element_name = '', $element_name_ns = '', $fieldSnipped = '', $group_id = '', $preview = false, $return_value = false, $is_sub_field = false, $implode_delimiter = '' )
		{
			global $acf;

			$put_to_csv = true;

			$field_name    = (!empty($exportOptions['cc_label'][$ID])) ? $exportOptions['cc_label'][$ID] : '';
			$field_options = (!empty($exportOptions['cc_options'][$ID])) ? maybe_unserialize($exportOptions['cc_options'][$ID]) : $exportOptions;
			$field_settings = (!empty($exportOptions['cc_settings'][$ID])) ? json_decode($exportOptions['cc_settings'][$ID], true) : false;

			// Delegate pro field types to Pro add-on when available, regardless of whether the value is empty.
			// Free handles simple fields (text, textarea, number, dates, etc.) directly.
			// Return immediately after delegation to avoid double-filtering or interfering with Pro's $acfs state.
			if(in_array($field_options['type'], self::PRO_FIELDS)) {
				if(class_exists('soflyy\wpae_acf_add_on\AdvancedAcfFields')) {
					$pro_result = \soflyy\wpae_acf_add_on\AdvancedAcfFields::export_acf_field( $field_value, $exportOptions, $ID, $pid, $article, $xmlWriter, $acfs, $element_name, $element_name_ns, $fieldSnipped, $group_id, $preview, $return_value, $is_sub_field, $implode_delimiter );
					if ($return_value) {
						return $pro_result;
					}
					return;
				} else {
					return '';
				}
			}

			$is_xml_export = false;

			if ( ! empty($xmlWriter) and XmlExportEngine::$exportOptions['export_to'] == 'xml' and ! in_array(XmlExportEngine::$exportOptions['xml_template_type'], array('custom', 'XmlGoogleMerchants')) ){
				$is_xml_export = true;
			}

			$is_custom_xml_export = false;

			if ( XmlExportEngine::$exportOptions['export_to'] == 'xml' and in_array(XmlExportEngine::$exportOptions['xml_template_type'], array('custom')) ){
				$is_custom_xml_export = true;
			}

			if ($field_options['type'] == 'message'){
				$field_value = empty($field_options['message']) ? '' : $field_options['message'];
			}

			if ( ! empty($field_value)) {
				$field_value = maybe_unserialize($field_value);

				if ( empty($implode_delimiter) ) {
					$implode_delimiter = XmlExportEngine::$implode;
				}

				switch ($field_options['type'])
				{
					case 'date_time_picker':
						$format = empty($field_options['return_format']) ? 'Y-m-d H:i:s' : $field_options['return_format'];
						// ACF documentation shows the value should always be stored as 'Y-m-d H:i:s' in the database
						$field_value = gmdate($format, (is_numeric($field_value) ? $field_value : strtotime($field_value)));
						break;
					case 'date_picker':

						// In case the date is in format Y-m-d H:i:s, convert it to Ymd
						if(strpos($field_value,'-') !== false) {
							$dateParts = explode(" ", $field_value);
							$field_value = $dateParts[0];
							$dateParts = explode("-", $field_value);
							$year = $dateParts[0];
							$month = $dateParts[1];
							$day = $dateParts[2];
							$field_value = $year.$month.$day;
						}

						// retain this filter's control over the database format to avoid breaking existing workarounds
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WP All Export plugin filter hook
					$format = apply_filters('pmxe_acf_date_picker_format', 'Ymd', $field_value);

						// separately calculate the output format to solve the original problem
						$output_format = empty($field_options['return_format']) ? 'Ymd' : $field_options['return_format'];
						// Try to generate the date object using the database format specified
						// According to ACF documentation this value should always be saved in the database as 'Ymd'

						$date_obj = DateTime::createFromFormat($format, $field_value);

						if(!$date_obj) {
							// We probably are in a field group
							$date_obj = DateTime::createFromFormat($output_format, $field_value);
						}
						// avoid fatal errors by confirming there is a date object before using it
						$field_value = gmdate($output_format, ( !$date_obj ) ? strtotime($field_value): $date_obj->getTimestamp()); // strtotime is left to deal with any invalid/unexpected dates

						break;
				}
			}

			$field_value = apply_filters( 'pmae_acf_format_field_value', $field_value, $field_name, $field_options, $implode_delimiter, $is_xml_export, $xmlWriter, $element_name_ns, $element_name, $is_custom_xml_export, $pid, $preview, $fieldSnipped, $exportOptions, $is_sub_field );

			if ($return_value) return $field_value;

            $acfs = apply_filters('pmae_acf_modify_acfs_array', $acfs, self::$fc_sub_field_names, $element_name);

			if ($put_to_csv)
			{
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WP All Export plugin filter hook
			$val = apply_filters('pmxe_acf_field', pmxe_filter( ( ! empty($field_value) || $field_value === 0 || $field_value === "0" ) ? maybe_serialize($field_value) : '', $fieldSnipped), $field_name, $pid);

				if ($is_xml_export)
				{
					$elementOpenResponse = $xmlWriter->beginElement($element_name_ns, $element_name, null);
					if($elementOpenResponse) {
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

		public function filter_csv_rows($articles, $options, $export_id)
		{
			if ( ! empty(self::$additional_articles) and $options['export_to'] == 'csv')
			{
				$base_article = $articles[count($articles) - 1];

				if ( ! empty(self::$additional_articles ) )
				{
					foreach (self::$additional_articles as $article)
					{
						if ($article['settings']['repeater_field_fill_empty_columns'])
						{
							foreach ($article['content'] as $key => $value) {
								unset($base_article[$key]);
							}
							$articles[] = array_merge($base_article, $article['content']);
						}
						else
						{
							$articles[] = $article['content'];
						}
					}
					self::$additional_articles = array();
				}
			}

			return $articles;
		}

		public function get_fields_options( &$fields, $field_keys = array() ){

			if ( ! empty($this->_acf_groups) )
			{
				foreach ($this->_acf_groups as $key => $group)
				{
					if ( ! empty($group['fields']))
					{
						foreach ($group['fields'] as $field)
						{
							$field_key = $field['label'];

							if ( ! in_array($field_key, $field_keys) ) continue;

							$fields['ids'][] = 1;
							$fields['cc_label'][] = $field['name'];
							$fields['cc_php'][] = '';
							$fields['cc_code'][] = '';
							$fields['cc_sql'][] = '';
							$fields['cc_options'][] = serialize(array_merge($field, array('group_id' => ((!empty($group['ID'])) ? $group['ID'] : $group['id']) )));
							$fields['cc_type'][] = 'acf';
							$fields['cc_value'][] = $field['name'];
							$fields['cc_name'][] = $field_key;
							$fields['cc_settings'][] = '';
						}
					}
				}
			}
		}

		public function render( & $i ){

			if ( ! empty($this->_acf_groups) )
			{
				?>
                <p class="wpae-available-fields-group"><?php esc_html_e("ACF", "wp-all-export-csv-excel-xml-for-acf"); ?>
                <span class="wpae-expander">+</span></p>
                <div class="wp-all-export-acf-wrapper wpae-custom-field">
					<?php

					foreach ($this->_acf_groups as $key => $group)
					{
						$rules_array          = array();
						$is_acf_group_visible = false;
						$rules                = false;

						if ( ! empty( $group['location'] ) && is_array( $group['location'] ) && ! isset( $group['location']['rules'] ) ) {
							$rules_array = $group['location'];
						} elseif ( ! empty( $group['location'] ) && is_array( $group['location'] ) && isset( $group['location']['rules'] ) && is_array( $group['location']['rules'] ) ) {
							$rules_array = $group['location']['rules'];
							$rules       = true;
						}

						if ( ! empty( $rules_array ) ) {

							foreach ( $rules_array as $locationRuleOuter ){

								if ( $rules === false ) {
									$rule_details = $locationRuleOuter;
								} else {
									$rule_details = [$locationRuleOuter];
								}

								foreach($rule_details as $rule) {

									if ( XmlExportEngine::$is_user_export && $rule['param'] == 'user_form' ) {
										$is_acf_group_visible = true;
										break;
									} elseif ( XmlExportEngine::$is_taxonomy_export && $rule['param'] == 'taxonomy' ) {
										$is_acf_group_visible = true;
										break;
									} elseif ( 'specific' == XmlExportEngine::$exportOptions['export_type'] && $rule['param'] == 'post_type' ) {
										if ( $rule['operator'] == '==' && in_array( $rule['value'], XmlExportEngine::$post_types ) ) {
											$is_acf_group_visible = true;
											break;
										} elseif ( $rule['operator'] != '==' && ! in_array( $rule['value'], XmlExportEngine::$post_types ) ) {
											$is_acf_group_visible = true;
											break;
										}
									} elseif ( 'advanced' == XmlExportEngine::$exportOptions['export_type'] ) {
										$is_acf_group_visible = true;
										break;
									} // Include local ACF blocks field groups except when exporting Users.
                                    elseif ( ! XmlExportEngine::$is_user_export && $rule['param'] == 'block' ) {
										$is_acf_group_visible = true;
										break;
									}
								}
							}
						}
						else{
							$is_acf_group_visible = true;
						}

						if ( ! $is_acf_group_visible ) continue;

						?>
                        <div class="wpae-acf-field">
                            <ul>
                                <li>
                                    <div class="default_column" rel="">
                                        <label class="wpallexport-element-label"><?php echo esc_html($group['title']); ?></label>
                                        <input type="hidden" name="rules[]" value="pmxe_acf_<?php echo esc_attr((!empty($group['ID'])) ? $group['ID'] : $group['id']);?>"/>
                                    </div>
                                </li>
								<?php
								if ( ! empty($group['fields']))
								{
									foreach ($group['fields'] as $field)
									{
										do_action('pmae_render_individual_acf_field', $field, $group);
                                        $show_notice = apply_filters('pmae_is_show_pro_notice_for_field', true, $field, $group);

										// Skip Pro only fields to avoid processing errors and show upgrade message instead.
										if($show_notice && in_array($field['type'], self::PRO_FIELDS)){
											?>
                                            <li>
                                                <div class="custom_column">
                                                    <label class="wpallexport-xml-element"><?php echo esc_attr($field['label']); ?></label>
                                                    <div class="wpallexport-free-edition-notice" style="display: block; width: auto;">
                                                        <a class="upgrade_link" style="font-size:1.15em" target="_blank"
                                                           href="<?php 
                                                           
                                                           if(defined('PMXE_EDITION') && PMXE_EDITION == 'free'){
                                                               echo esc_url('https://www.wpallimport.com/?edd_action=add_to_cart&download_id=5839953&edd_options%5Bprice_id%5D=1&discount=welcome-169&utm_source=export-plugin-pro&utm_medium=upgrade-notice&utm_campaign=export-advanced-custom-fields');
                                                           }else {
	                                                           echo esc_url('https://www.wpallimport.com/portal/discounts/?utm_source=export-plugin-pro&utm_medium=upgrade-notice&utm_campaign=export-advanced-custom-fields');
                                                           }
                                                           ?>">Purchase
                                                            the ACF Export Add-On Pro to Export `<?php echo esc_attr(ucwords(str_replace('_',' ', $field['type']))); ?>` Fields</a>

                                                    </div>
                                                    <input type="hidden" name="cc_type[]" value="pro_only"/>
                                                    <input type="hidden" name="cc_name[]" value="<?php echo esc_attr($field['label']);?>"/>
                                                </div>
                                            </li>
											<?php
											continue;
										}
										?>
                                        <li class="pmxe_acf_<?php echo esc_attr((!empty($group['ID'])) ? $group['ID'] : $group['id']);?> wp_all_export_auto_generate">
                                            <div class="custom_column" rel="<?php echo esc_attr(($i + 1));?>">
                                                <label class="wpallexport-xml-element"><?php echo esc_attr($field['label']); ?></label>
                                                <input type="hidden" name="ids[]" value="1"/>
                                                <input type="hidden" name="cc_label[]" value="<?php echo esc_attr($field['name']); ?>"/>
                                                <input type="hidden" name="cc_php[]" value=""/>
                                                <input type="hidden" name="cc_code[]" value=""/>
                                                <input type="hidden" name="cc_sql[]" value=""/>
                                                <input type="hidden" name="cc_options[]" value="<?php echo esc_attr(serialize(array_merge($field, array('group_id' => ((!empty($group['ID'])) ? $group['ID'] : $group['id']) ))));?>"/>
                                                <input type="hidden" name="cc_type[]" value="acf"/>
                                                <input type="hidden" name="cc_value[]" value="<?php echo esc_attr($field['name']); ?>"/>
                                                <input type="hidden" name="cc_name[]" value="<?php echo esc_attr($field['label']);?>"/>
                                                <input type="hidden" name="cc_settings[]" value=""/>
                                            </div>
                                        </li>
										<?php
										$i++;
									}
								}
								?>
                            </ul>
                        </div>
						<?php
					}
					?>
                </div>
				<?php
			}
		}

		public function render_new_field(){

			if ( ! empty($this->_acf_groups) )
			{
				foreach ($this->_acf_groups as $key => $group)
				{
					?>
                    <optgroup label="<?php esc_html_e("ACF", "wp-all-export-csv-excel-xml-for-acf"); ?> - <?php echo esc_attr($group['title']); ?>">
						<?php
						if ( ! empty($group['fields']))
						{
							foreach ($group['fields'] as $field)
							{
								$field_options = serialize(array_merge($field, array('group_id' => ((!empty($group['ID'])) ? $group['ID'] : $group['id']) )));
								?>
                                <option
                                        value="acf"
                                        label="<?php echo esc_attr($field['name']);?>"
                                        options="<?php echo esc_attr($field_options); ?>"><?php echo esc_html($field['label']);?></option>
								<?php
							}
						}
						?>
                    </optgroup>
					<?php
				}
			}
		}

		public function render_filters(){

			if ( ! empty($this->_acf_groups) ){
				?>
                <optgroup label="<?php esc_html_e("ACF", "wp-all-export-csv-excel-xml-for-acf"); ?>">
					<?php
					foreach ($this->_acf_groups as $key => $group) {
						if ( ! empty($group['fields'])){
							foreach ($group['fields'] as $field) {
								?>
                                <option value="<?php echo 'cf_' . esc_attr($field['name']); ?>"><?php echo esc_html($field['label']); ?></option>
								<?php
							}
						}
					}
					?>
                </optgroup>
				<?php
			}

		}

		public static function prepare_import_template( $exportOptions, &$templateOptions, &$acf_list, $element_name, $field_options, $parent_delimiter = false)
		{
			$field_tpl_key = (preg_match('/^[0-9]/', $element_name)) ? 'el_' . $element_name . '[1]' : $element_name . '[1]';

			$acf_list[] = '[' . $field_options['name'] . '] ' . $field_options['label'];

			$field_template = false;

			$is_xml_template = $exportOptions['export_to'] == 'xml';

			$xpath_separator = $is_xml_template ? '/' : '_';

			$implode_delimiter = XmlExportEngine::$implode;

			if (!empty($parent_delimiter)){
				$implode_delimiter = ($parent_delimiter == '|' ? ',' : '|');
			}

			switch ($field_options['type'])
			{

				default:

					$field_template = '{' . $field_tpl_key . '}';

					break;

			}
            self::$templateOptions = $templateOptions;
			$field_template = apply_filters('pmae_prepare_import_template_field_template',$field_template, $exportOptions, $acf_list, $element_name, $field_options, $parent_delimiter, $field_tpl_key, $is_xml_template, $xpath_separator, $implode_delimiter) ;
            $templateOptions = self::$templateOptions;

            return $field_template;
		}

		public function auto_generate_export_fields( & $fields ){

			if ( ! empty($this->_acf_groups) )
			{
				foreach ($this->_acf_groups as $key => $group)
				{
					if ( ! empty($group['fields']))
					{
						foreach ($group['fields'] as $field)
						{
							$fields['ids'][] 	    	= 1;
							$fields['cc_label'][]   	= $field['name'];
							$fields['cc_php'][] 	   	= 0;
							$fields['cc_code'][]    	= '';
							$fields['cc_sql'][]     	= '';
							$fields['cc_settings'][]    = '';
							$fields['cc_type'][]    	= 'acf';
							$fields['cc_options'][] 	= serialize(array_merge($field, array('group_id' => ((!empty($group['ID'])) ? $group['ID'] : $group['id']) )));
							$fields['cc_value'][]   	= $field['name'];
							$fields['cc_name'][]    	= $field['label'];
						}
					}
				}
			}
		}

		/**
		 * __get function.
		 *
		 * @access public
		 * @param mixed $key
		 * @return mixed
		 */
		public function __get( $key ) {
			return $this->get( $key );
		}

		/**
		 * Get a session variable
		 *
		 * @param string $key
		 * @param  mixed $default used if the session variable isn't set
		 * @return mixed value of session variable
		 */
		public function get( $key, $default = null ) {
			return isset( $this->{$key} ) ? $this->{$key} : $default;
		}

		public static function acf_get_row() {

			global $acf;

			if ($acf and version_compare($acf->settings['version'], '5.3.6.0') >= 0)
			{
				return acf_get_loop('active');
			}
			// check and return row
            elseif( !empty($GLOBALS['acf_field']) ) {

				return end( $GLOBALS['acf_field'] );

			}

			// return
			return false;

		}

		/**
		 * Return ACF value for simple fields (text, etc.)
		 *
		 * @param $entry
		 * @param $field_name
		 * @return string
		 */
		public static function get_acf_block_value($entry, $field_name){

			$field_value = '';

			if ( has_blocks( $entry ) ) {
				$blocks = parse_blocks( $entry->post_content );
				foreach ( $blocks as $block ) {
					if (strpos($block['blockName'], 'acf/') !== false) {
						if (isset($block['attrs']['data'][$field_name])) {
							$field_value = $block['attrs']['data'][$field_name];
						}
					}
				}
			}

			return $field_value;
		}
	}
}
