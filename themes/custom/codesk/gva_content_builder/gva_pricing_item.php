<?php
if(!class_exists('element_gva_pricing_item')):
   class element_gva_pricing_item{
      public function render_form(){
         $fields = array(
            'type' => 'gsc_pricing_item',
            'title' => ('Pricing Item'), 
            'fields' => array(
               array(
                  'id'        => 'image',
                  'type'      => 'upload',
                  'title'     => t('Image Icon'),
                  'desc'      => t('Use image icon instead of icon class'),
               ),
               array(
                  'id'        => 'title',
                  'type'      => 'text',
                  'title'     => t('Title'),
                  'desc'      => t('Pricing item title'),
                  'admin'     => true
               ),
               array(
                  'id'        => 'sub_title',
                  'type'      => 'text',
                  'title'     => t('Sub Title'),
               ),
               array(
                  'id'        => 'price',
                  'type'      => 'text',
                  'title'     => t('Price'),
               ),
               
               array(
                  'id'        => 'currency',
                  'type'      => 'text',
                  'title'     => t('Currency'),
               ),
                  
               array(
                  'id'        => 'period',
                  'type'      => 'text',
                  'title'     => t('Period'),
               ),
            
             
               array(
                  'id'        => 'content',
                  'type'      => 'textarea',
                  'title'     => t('Content'),
                  'desc'      => t('HTML tags allowed.'),
                  'std'       => '<ul><li><strong>List</strong> item</li></ul>',
               ),
               array(
                  'id'        => 'link_title',
                  'type'      => 'text',
                  'title'     => t('Link text'),
                  'desc'      => t('Link will appear only if this field will be filled.'),
               ),
               
               array(
                  'id'        => 'link',
                  'type'      => 'text',
                  'title'     => t('Link'),
                  'desc'      => t('Link will appear only if this field will be filled.'),
               ),

               array(
                  'id'        => 'featured',
                  'type'      => 'select',
                  'title'     => t('Featured'),
                  'options'   => array( 'off' => 'No', 'on' => 'Yes' ),
               ),

               array(
                  'id'            => 'style',
                  'type'          => 'select',
                  'title'         => t('Style'),
                  'options'       => array(
                     'style-1'               => 'Style 1', 
                     'style-2'               => 'Style 2' 
                  ),
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

      public static function render_content( $attr = array(), $content = '' ){
         global $base_url;
         extract(gavias_merge_atts(array(
            'image'        => '',
            'title'        => '',
            'sub_title'    => '',
            'currency'     => '',
            'price'        => '',
            'period'       => '',
            'content'      => '',
            'link_title'   => 'Sign Up Now',
            'link'         => '',
            'featured'     => 'off',
            'style'        => '',
            'el_class'     => '',
            'animate'      => '',
            'animate_delay'   => ''
         ), $attr));

         if($image) $image = $base_url . $image;
         if($featured == 'on') $el_class .= ' highlight-plan'; 
         if($animate) $el_class .= ' wow ' . $animate; 
         ob_start();
         ?>

         <?php if($style == 'style-1'){ ?>
         <div class="pricing-table <?php print $el_class ?> <?php print $style ?>" <?php print gavias_content_builder_print_animate_wow('', $animate_delay) ?>>
               
            <div class="content-inner">
               <div class="content-wrap">
                  <div class="plan-name"><h3 class="title"><?php print $title; ?></h3></div>
                  <div class="plan-price">
                     <div class="price-value clearfix">
                        <?php if($currency){ ?><span class="dollar"><?php print $currency ?></span><?php } ?>  
                        <?php if($price){ ?><span class="value"><?php print $price; ?></span><?php } ?>  
                        <?php if($period){ ?><span class="interval"><?php print $period ?></span><?php } ?>  
                     </div>
                  </div>
                  <?php if($content){ ?>
                     <div class="plan-list">
                        <?php print $content ?>
                     </div>
                  <?php } ?>   
                  <?php if($link){ ?>
                     <div class="plan-signup">
                        <a class="btn-theme" href="<?php print $link; ?>"><span><?php print $link_title ?></span></a>
                     </div>
                  <?php } ?>  
               </div> 
            </div>      
         </div>
         <?php } ?>
         <?php if($style == 'style-2'){ ?>
            <div class="pricing-table <?php print $el_class ?> <?php print $style ?>" <?php print gavias_content_builder_print_animate_wow('', $animate_delay) ?>>
               <?php if($featured=='on'){ ?>
                  <div class="recommended-plan"><?php print t('Recommended Plan') ?></div>
               <?php } ?>   
               <div class="content-inner">
                  <?php if($image){ ?>
                  <div class="content-left">
                     <div class="image"><img src="<?php print $image ?>" alt="<?php print strip_tags($title) ?>"/> </div> 
                  </div>
                  <?php } ?>
                  <div class="content-wrap">
                     <div class="plan-name"><h3 class="title"><?php print $title; ?></h3></div>
                     <?php if($content){ ?>
                        <div class="plan-desc">
                           <?php print $content ?>
                        </div>
                     <?php } ?>
                     <div class="price-meta">
                        <?php if($sub_title){ ?><div class="subtitle"><?php print $sub_title; ?></div><?php } ?>
                        <div class="plan-price">
                           <div class="price-value clearfix">
                              <?php if($currency){ ?><span class="dollar"><?php print $currency ?></span><?php } ?>  
                              <?php if($price){ ?><span class="value"><?php print $price; ?></span><?php } ?>  
                              <?php if($period){ ?><span class="interval"><?php print $period ?></span><?php } ?>  
                           </div>
                        </div>
                     </div> 
                     <?php if($link){ ?>
                        <div class="plan-signup">
                           <a class="btn-theme btn-small" href="<?php print $link; ?>"><span><?php print $link_title ?></span></a>
                        </div>
                     <?php } ?>  
                  </div> 
               </div>      
            </div>
         <?php } ?>
   	<?php return ob_get_clean();
      }
   }   
endif;   



