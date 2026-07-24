<?php

function pmaep_pmae_acf_modify_acfs_array($acfs, $field_names, $element_name){
	if(!empty($field_names)){
		$acf_flexible = get_option('wp_all_export_acf_flexible_' . XmlExportEngine::$exportID);
		$acfs[$element_name] = XmlExportACF::$fc_sub_field_names;
		$acf_flexible[$element_name] = XmlExportACF::$fc_sub_field_names;
		update_option('wp_all_export_acf_flexible_' . XmlExportEngine::$exportID, $acf_flexible);
		XmlExportACF::$fc_sub_field_names = array();
	}

	return $acfs;
}
