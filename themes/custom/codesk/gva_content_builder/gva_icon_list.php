<?php 
if(!class_exists('element_gva_icon_list')):
   class element_gva_icon_list{

      public function render_form(){
         $fields = array(
            'type' => 'gva_icon_list',
            'title' => t('List Icon'),
            'size' => 3,
            'fields' => array(
               array(
                  'id'        => 'title',
                  'type'      => 'text',
                  'title'     => t('Title For Admin'),
                  'admin'     => true
               ),
               array(
                 'id'        => 'skin',
                 'type'      => 'select',
                 'title'     => 'SKin',
                 'class'     => 'width-1-2',
                 'options'   => array(
                     'skin-v1'       => 'SKin V1',
                     'skin-v2'       => 'SKin V2'
                 )
               ),
               array(
                  'id'        => 'el_class',
                  'type'      => 'text',
                  'title'     => t('Extra class name'),
                  'class'     => 'width-1-2'
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

         for($i=1; $i<=10; $i++){
            $fields['fields'][] = array(
               'id'     => "info_{$i}",
               'type'   => 'info',
               'desc'   => "Information for item {$i}"
            );
            $fields['fields'][] = array(
               'id'        => "icon_{$i}",
               'type'      => 'text',
               'title'     => t("Icon {$i}")
            );
            $fields['fields'][] = array(
               'id'        => "title_{$i}",
               'type'      => 'text',
               'title'     => t("Title {$i}")
            );
            $fields['fields'][] = array(
               'id'        => "link_{$i}",
               'type'      => 'text',
               'title'     => t("link {$i}")
            );
         }
         return $fields;
      }

      public static function render_content( $attr = array(), $content = '' ){
         global $base_url;
         $default = array(
            'title'           => '',
            'skin'            => 'skin-v1',
            'el_class'        => '',
            'animate'         => '',
            'animate_delay'   => ''
         );

         for($i=1; $i<=10; $i++){
            $default["icon_{$i}"] = '';
            $default["title_{$i}"] = '';
            $default["link_{$i}"] = '';
         }

         extract(gavias_merge_atts($default, $attr));

         $_id = gavias_content_builder_makeid();
         
         if($animate) $el_class .= ' wow ' . $animate; 
         ob_start();
         ?>
         <div class="gsc-icon-list <?php echo $skin ?> <?php echo $el_class ?>" <?php print gavias_content_builder_print_animate_wow('', $animate_delay) ?>> 
            <a class="btn-hidden-links" href="#"><span class="ion-ios-close-outline"></span></a>
            <div class="content-wrapper">
               <ul class="list">
               <?php for($i=1; $i<=10; $i++){ ?>
                  <?php 
                     $icon = "icon_{$i}";
                     $title = "title_{$i}";
                     $link = "link_{$i}";
                  ?>
                  <?php if($$link || $$title){ ?>
                     <li class="list-item">
                        <div class="list-icon">
                           <?php if($$link){ ?><a href="<?php print $$link ?>"><?php } ?>
                              <?php if($$icon){ ?><span class="icon"><i class="<?php print $$icon ?>"></i></span><?php } ?>   
                              <span class="title"><?php print $$title ?></span>
                           <?php if($$link){ ?></a><?php } ?>
                        </div>      
                     </li>
                  <?php } ?>    
               <?php } ?> 
               </ul>
            </div>    
         </div>   
         <?php return ob_get_clean();
      }
   }
 endif;  



