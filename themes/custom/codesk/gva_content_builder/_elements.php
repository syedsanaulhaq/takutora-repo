<?php
function gavias_content_builder_set_elements(){
   return $shortcodes = array(
    'gva_column',
    'gva_row',
    'gva_accordion',
    'gva_box_color', 
    'gva_box_hover', 
    'gva_button',
    'gva_call_to_action',
    'gva_carousel_content',
    'gva_chart',
    'gva_counter',
    'gva_download',
    'gva_drupal_block',
    'gva_gallery',
    'gva_gmap',
    'gva_heading',
    'gva_icon_box_classic',
    'gva_icon_box_color',
    'gva_icon_box_style',
    'gva_image',
    'gva_image_content',
    'gva_images_parallax',
    'gva_job_box',
    'gva_links',
    'gva_icon_list',
    'gva_our_partners',
    'gva_our_team',
    'gva_pricing_item',
    'gva_progress',
    'gva_progress_work',
    'gva_progress_work_list',
    'gva_quote_text',
    'gva_iconbox_carousel',
    'gva_socials',
    'gva_tabs',
    'gva_tabs_content',
    'gva_quotes_rotator',
    'gva_text',
    'gva_text_noeditor',
    'gva_text_rotate',
    'gva_video_box',
    'gva_view',
  );
}

function gavias_merge_atts( $pairs, $atts, $shortcode = '' ) {
    $atts = (array)$atts;
    $out = array();
    foreach($pairs as $name => $default) {
        if ( array_key_exists($name, $atts) )
            $out[$name] = $atts[$name];
        else
            $out[$name] = $default;
    }
    return $out;
}

function gavias_carousel_fields_settings(&$fields){
    $fields[] = array(
       'id'     => "carousel_settings",
       'type'   => 'info',
       'desc'   => "Carousel Settings"
    );
    $fields['fields'][] = array(
       'id'        => "col_lg",
       'type'      => 'select',
       'title'     => t("Columns for Large Screen"),
       'class'       => 'width-1-4',
       'options'   => array(
            '1'   => '1',
            '2'   => '2',
            '3'   => '3',
            '4'   => '4',
            '5'   => '5',
            '6'   => '6',
          ),
       'default'   => '4'
    );
    $fields['fields'][] = array(
       'id'        => "col_md",
       'type'      => 'select',
       'title'     => t("Columns for Medium Screen"),
       'class'       => 'width-1-4',
       'options'   => array(
            '1'   => '1',
            '2'   => '2',
            '3'   => '3',
            '4'   => '4',
            '5'   => '5',
            '6'   => '6',
          ),
       'default'   => '3'
    );
    $fields['fields'][] = array(
       'id'        => "col_sm",
       'type'      => 'select',
       'title'     => t("Columns for Small Screen"),
       'class'       => 'width-1-4',
       'options'   => array(
            '1'   => '1',
            '2'   => '2',
            '3'   => '3',
            '4'   => '4',
            '5'   => '5',
            '6'   => '6',
          ),
       'default'   => '2'
    );
    $fields['fields'][] = array(
       'id'        => "col_xs",
       'type'      => 'select',
       'title'     => t("Columns for Extra Small"),
       'class'       => 'width-1-4',
       'options'   => array(
            '1'   => '1',
            '2'   => '2',
            '3'   => '3',
            '4'   => '4',
            '5'   => '5',
            '6'   => '6',
          ),
       'default'   => '1'
    );
    $fields['fields'][] = array(
       'id'        => "auto_play",
       'type'      => 'select',
       'title'     => t("Auto Play"),
       'class'       => 'width-1-4',
       'options'   => array(
            '0'   => 'Disable',
            '1'   => 'Enable',
          ),
       'default'   => '1'
    );
    $fields['fields'][] = array(
       'id'        => "pagination",
       'type'      => 'select',
       'title'     => t("Pagination"),
       'class'       => 'width-1-4',
       'options'   => array(
            '0'   => 'Disable',
            '1'   => 'Enable',
          ),
       'default'   => '0'
    );
    $fields['fields'][] = array(
       'id'        => "navigation",
       'type'      => 'select',
       'title'     => t("Navigation"),
       'class'       => 'width-1-4',
       'options'   => array(
            '0'   => 'Disable',
            '1'   => 'Enable',
          ),
       'default'   => '0'
    );
    return $fields;
}
function gavias_get_image_size($image){
	if(!empty($image) && $image){
	  	$image_info = getimagesize($image);
	  	if(isset($image_info[3])){
	    	return $image_info[3];
	  	}
	}
  return '';
}
function gavias_implode_classes(array $classes){
    if(isset($classes) && count($classes) > 0 ){
        return(' ' . implode(' ', $classes));
    } 
}
function gavias_render_css($screen, $class_css, $array_css){
    $result = $tmp_css = '';
    
    if($screen == 'xl'){
        if(is_array($array_css)){
            $tmp_css = count($array_css) ? implode('; ', $array_css) : '';
        }else{
            $tmp_css = $array_css;
        }
        $result .= !empty($tmp_css) ? $class_css . '{' . $tmp_css . '}' . PHP_EOL : '';
    }

    if($screen == 'lg'){
        if(is_array($array_css)){
            $tmp_css = count($array_css) ? implode('; ', $array_css) : '';
        }else{
            $tmp_css = $array_css;
        }
        $result .= !empty($tmp_css) ? '@media(max-width: 1023.98px){' . $class_css . '{' . $tmp_css . '}' . '}' . PHP_EOL : '';
    }

    if($screen == 'md'){
        if(is_array($array_css)){
                $tmp_css = count($array_css) ? implode('; ', $array_css) : '';
        }else{
            $tmp_css = $array_css;
        }
        $result .= !empty($tmp_css) ? '@media(max-width: 767.98px){' . $class_css . '{' . $tmp_css . '}' . '}' . PHP_EOL : '';
    }

    return $result;
}