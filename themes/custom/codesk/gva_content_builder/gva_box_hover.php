<?php 

if(!class_exists('element_gva_box_hover')):
   class element_gva_box_hover{
      
      public function render_form(){
         $fields = array(
            'type'            => 'gsc_box_hover',
            'title'           => t('Box Hover'),
            'fields' => array(
               array(
                  'id'        => 'title',
                  'type'      => 'text',
                  'title'     => 'Title',
                  'admin'     => true
               ),
               array(
                  'id'        => 'content',
                  'type'      => 'textarea',
                  'title'     => t('Content'),
               ),
               
               array(
                  'id'        => 'icon',
                  'type'      => 'text',
                  'title'     => t('Icon class'),
                  'default'   => 'ion-social-html5-outline',
                  'desc'     => t('Use class icon font <a target="_blank" href="http://fontawesome.io/icons/">Icon Awesome</a> or <a target="_blank" href="http://gaviasthemes.com/icons/">Custom icon</a>'),
               ),
               array(
                  'id'        => 'image',
                  'type'      => 'upload',
                  'title'     => t('Background Image'),
                  'std'       => '',
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
            )                                    
         );
         return $fields;
      }

      public static function render_content( $attr = array(), $content = '' ){
         global $base_url;
         extract(gavias_merge_atts(array(
            'icon'               => '',
            'title'              => '',
            'content'            => '',
            'image'              => '',
            'el_class'           => '',
            'animate'            => '',
            'animate_delay'      => ''
         ), $attr));

         if($image) $image = $base_url . $image;  
         
         
         if($animate) $el_class .= ' wow ' . $animate; 

         ob_start();
         ?>
         <div class="widget gsc-box-hover clearfix <?php print $el_class; ?>" <?php print gavias_content_builder_print_animate_wow('', $animate_delay) ?>>
            <?php if($image){?>
              <div class="box-background" style="background-image:url('<?php echo $image ?>');"></div>
            <?php }  ?>
            <div class="box-content">   
               <div class="content-inner">
                  <?php if($icon){ ?><div class="icon"><span class="<?php print $icon ?>"></span></div> <?php } ?>
                  <?php if($title){ ?><h3 class="title"><?php print $title; ?></h3><?php } ?>
                  <?php if($content){ ?><div class="desc"><?php print $content ?></div><?php } ?>
               </div>
            </div>
         </div>  
         <?php return ob_get_clean() ?>
         <?php
      }
   }
endif;   




