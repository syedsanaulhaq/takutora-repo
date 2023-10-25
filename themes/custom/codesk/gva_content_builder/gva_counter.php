<?php 

if(!class_exists('element_gva_counter')):
   class element_gva_counter{
      public function render_form(){
         $fields = array(
            'type' => 'element_gva_counter',
            'title' => ('Counter'),
            'fields' => array(
               array(
                  'id'        => 'title',
                  'title'     => t('Title'),
                  'type'      => 'text',
                  'admin'     => true
               ),
               array(
                  'id'        => 'icon',
                  'title'     => t('Icon'),
                  'type'      => 'text',
                  'std'       => '',
                  'desc'     => t('Use class icon font <a target="_blank" href="http://fontawesome.io/icons/">Icon Awesome</a> or <a target="_blank" href="http://gaviasthemes.com/icons/">Custom icon</a>'),
               ),
               array(
                  'id'        => 'number',
                  'title'     => t('Number'),
                  'type'      => 'text',
               ),
               array(
                  'id'        => 'font_size',
                  'type'      => 'select',
                  'title'     => t('Number Font Size'),
                  'options'   => array(
                     '00'   => '--Default--',
                     '18'   => '18',
                     '20'   => '20',
                     '22'   => '22',
                     '24'   => '24',
                     '26'   => '26',
                     '28'   => '28',
                     '30'   => '30',
                     '32'   => '32',
                     '34'   => '34',
                     '36'   => '36',
                     '38'   => '38',
                     '40'   => '40',
                     '42'   => '42',
                     '44'   => '44',
                     '46'   => '46',
                     '48'   => '48',
                     '50'   => '50',
                     '60'   => '60',
                     '70'   => '70',
                     '80'   => '80',
                     '90'   => '90',
                     '100'   => '100',
                  ),
                  'default'   => '00'
               ),
               array(
                  'id'        => 'font_weight',
                  'type'      => 'select',
                  'title'     => t('Title Font Weight'),
                  'options'   => array(
                     'fw-400'   => '400',
                     'fw-500'   => '500',
                     'fw-600'   => '600',
                     'fw-700'   => '700',
                     'fw-900'   => '900'
                  ),
                  'default' => 'fw-500'
               ),
               array(
                  'id'        => 'symbol',
                  'title'     => t('Symbol'),
                  'type'      => 'text',
               ),
               array(
                  'id'        => 'type',
                  'title'     => t('Style'),
                  'type'      => 'select',
                  'options'   => array(
                     'icon-left'         => 'Icon Left',
                     'icon-top'          => 'Icon Top',
                     'no-icon'           => 'Without Icon',
                     'no-icon-left'      => 'Without Icon Left',
                  ),
                  'std'    => 'icon-left',
               ),
               array(
                  'id'        => 'box_background',
                  'type'      => 'text',
                  'title'     => t('Box Background'),
                  'desc'      => t('Box Background, e.g: #f5f5f5')
               ),
               array(
                  'id'        => 'style_text',
                  'type'      => 'select',
                  'title'     => t('Skin Text for box'),
                  'options'   => array(
                     'text-dark'   => 'Text dark',
                     'text-light'   => 'Text light'
                  ),
                  'std'       => 'text-dark'
               ),
               array(
                  'id'        => 'el_class',
                  'type'      => 'text',
                  'title'     => t('Extra class name'),
                  'desc'      => t('Style particular content element differently - add a class name and refer to it in custom CSS.'),
               ),
               array(
                  'id'        => 'animate',
                  'type'      => 'select',
                  'title'     => t('Animation'),
                  'desc'      => t('Entrance animation for element'),
                  'options'   => gavias_content_builder_animate(),
                  'class'     => 'width-1-2'
               ), 
               array(
                  'id'        => 'animate_delay',
                  'type'      => 'select',
                  'title'     => t('Animation Delay'),
                  'options'   => gavias_content_builder_delay_wow(),
                  'desc'      => '0 = default',
                  'class'     => 'width-1-2'
               ), 
         
            ),                                      
         );
         return $fields;
      }


      public function render_content( $attr = array(), $content = '' ){
         extract(gavias_merge_atts(array(
            'title'         => '',
            'font_size'     => '00',
            'font_weight'   => 'fw-500',
            'icon'          => '',
            'number'        => '',
            'symbol'        => '',
            'type'          => 'icon-top',
            'el_class'      => '',
            'style_text'    => 'text-dark',
            'box_background'  => '',
            'animate'       => '',
            'animate_delay' => ''
         ), $attr));
         $class = array();
         $class[] = $el_class;
         $class[] = 'position-'.$type;
         $class[] = $style_text;
         $style = '';
         if($style) $style = 'style="'.$style.'"';
         if($animate) $class[] = 'wow ' . $animate; 
         ob_start();
         ?>
            <div class="widget milestone-block <?php if(count($class) > 0){ print implode(' ', $class); } ?>" <?php print gavias_content_builder_print_animate_wow('', $animate_delay) ?> <?php if($box_background){ ?> style="background-color: <?php print $box_background ?>;"<?php } ?>>
               <?php if($icon){ ?>
                  <div class="milestone-icon"><span class="icon <?php print $icon; ?>"></span></div>
               <?php } ?>   
               <div <?php print $style ?> class="milestone-right">
                  <div class="milestone-number-inner">
                     <span class="milestone-number fsize-<?php print $font_size ?> <?php print $font_weight ?>"><?php print $number; ?></span>
                     <?php if($symbol){ ?>
                        <span class="symbol"><?php print $symbol; ?></span>
                     <?php } ?>
                  </div>
                  <h3 class="milestone-text "><?php print $title ?></h3>
               </div>
            </div>
         <?php return ob_get_clean() ?>
         <?php
      }

   }
endif;
   



