<?php 

if(!class_exists('element_gva_call_to_action')):
   class element_gva_call_to_action{
      public function render_form(){
         $fields = array(
            'type' => 'gsc_call_to_action',
            'title' => t('Call to Action'),
            'fields' => array(
               array(
                  'id'        => 'title',
                  'type'      => 'text',
                  'title'     => t('Title'),
                  'admin'     => true
               ),
               array(
                  'id'        => 'sub_title',
                  'type'      => 'text',
                  'title'     => t('Sub Title'),
               ),
               array(
                  'id'        => 'content',
                  'type'      => 'textarea',
                  'title'     => t('Content'),
                  'desc'      => t('HTML tags allowed.'),
               ),
               array(
                  'id'           => 'button_align',
                  'type'         => 'select',
                  'title'        => 'Style',
                  'options'      => array(
                     'button-left'              => t('Button Left'),
                     'button-right'             => t('Button Right'),
                     'button-bottom'            => t('Button Bottom Left'),
                     'button-bottom-right'      => t('Button Bottom Right'),
                     'button-center'            => t('Button Bottom Center'),
                  )
               ),
               array(
                  'id'        => 'font_size',
                  'type'      => 'select',
                  'title'     => t('Font Size'),
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
                     '50'   => '54',
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
                  'title'     => t('Font Weight'),
                  'options'   => array(
                     'fw-400'   => '400',
                     'fw-500'   => '500',
                     'fw-600'   => '600',
                     'fw-700'   => '700',
                     'fw-900'   => '900'
                  ),
                  'default' => 'fw-700'
               ),
               array(
                  'id'        => 'box_background',
                  'type'      => 'text',
                  'title'     => t('Box Background'),
                  'desc'      => t('Box Background, e.g: #f5f5f5')
               ),
               array(
                  'id'        => 'border',
                  'type'      => 'select',
                  'title'     => t('Border Box'),
                  'options'   => array(
                        ''         => 'no',
                        'border-style'   => 'yes',
                  ),
               ),
               array(
                  'id'        => 'width',
                  'type'      => 'text',
                  'title'     => t('Max width for content'),
                  'desc'      => 'e.g 660px'
               ),
               array(
                  'id'        => 'style_text',
                  'type'      => 'select',
                  'title'     => 'Skin Text for box',
                  'options'   => array(
                        'text-light'  => 'Text light',
                        'text-dark'   => 'Text dark',
                  ),
                  'std'       => 'text-dark'
               ),
               array(
                  'id'        => 'size',
                  'type'      => 'select',
                  'title'     => t('Size Button'),
                  'options'   => array(
                        ''            => 'Medium',
                        'btn-large'   => 'Large',
                        'btn-small'   => 'Small'
                  )
               ),
               array(
                 'id'        => 'info',
                 'type'      => 'info',
                 'title'      => 'Settings Button #1'
               ),
               array(
                  'id'        => 'link',
                  'type'      => 'text',
                  'title'     => t('Link'),
               ),
               array(
                  'id'        => 'button_title',
                  'type'      => 'text',
                  'title'     => t('Button Title'),
                  'desc'      => t('Leave this field blank if you want Call to Action with Big Icon'),
               ),
               array(
                  'id'        => 'style_button',
                  'type'      => 'select',
                  'title'     => 'Style button #1',
                  'options'   => array(
                        'btn-theme'           => 'Button default of theme',
                        'btn-theme-second'    => 'Button second of theme',
                        'btn-white'           => 'Button white',
                        'btn btn-black'       => 'Button black',
                        'btn btn-theme-2'     => 'Button theme 2',
                  ),
                  'default'    => 'text-dark'
               ),
               
               array(
                  'id'        => 'target',
                  'type'      => 'select',
                  'title'     => t('Open in new window'),
                  'desc'      => t('Adds a target="_blank" attribute to the link'),
                  'options'   => array( 'off' => 'Off', 'on' => 'On' ),
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

      function render_content( $attr = array(), $content = ''  ){
         extract(gavias_merge_atts(array(
            'title'           => '',
            'sub_title'       => '',
            'content'         => '',
            'button_align'    => '',
            'font_size'       => '00',
            'font_weight'     => 'fw-700',
            'width'           => '',
            'size'            => '',
            'link'            => '',
            'button_title'    => '',
            'style_button'    => 'btn-theme',
            'target'          => '',
            'el_class'        => '',
            'animate'         => '',
            'animate_delay'   => '',
            'style_text'      => 'text-dark',
            'box_background'  => '',
            'border'          => '',
            'video'           => ''
         ), $attr));
         
         // target
         if( $target =='on' ){
            $target = 'target="_blank"';
         } else {
            $target = false;
         }
         
         $class = array();
         $class[] = $el_class;
         $class[] = $button_align;
         $class[] = $style_text;
         $class[] = $border;
         if($animate) $class[] = 'wow ' . $animate; 
         if($box_background) $class[] = 'has-background';

         $style = '';
         if($width) $style .= "max-width: {$width};";
         if($box_background) $style .= "background: {$box_background};";
         $style = !empty($style) ? "style=\"".$style ."\"" : '';
         ob_start();
         ?>

         <div class="widget gsc-call-to-action <?php print implode(' ', $class) ?>" <?php print $style ?> <?php print gavias_content_builder_print_animate_wow('', $animate_delay) ?>>
            <div class="content-inner clearfix" >
               <div class="content">
                  <?php if($sub_title){ ?><div class="sub-title"><span><?php print $sub_title; ?></span></div><?php } ?>
                  <?php if($title){?><h2 class="title fsize-<?php print $font_size ?> <?php print $font_weight ?>"><span><?php print $title; ?></span></h2>
                  <?php } ?>
                  <div class="desc"><?php print $content; ?></div>
               </div>
               <div class="button-action">
                  <?php if($link){?>
                     <a href="<?php print $link ?>" class="<?php print $style_button ?> <?php print $size ?>" <?php print $target ?>><span><?php print $button_title ?></span></a>   
                  <?php } ?>
               </div>
            </div>
         </div>
         <?php return ob_get_clean() ?>
      <?php
      }

   }
endif;   



