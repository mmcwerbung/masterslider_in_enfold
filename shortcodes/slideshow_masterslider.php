<?php
if ( !class_exists( 'avia_sc_masterslider' ) && defined('MSWP_AVERTA_VERSION'))
{
  class avia_sc_masterslider extends aviaShortcodeTemplate
	{		
			static $slide_count = 0;
			
			/**
			 * Create the config array for the shortcode button
			 */
			function shortcode_insert_button()
			{
				$this->config['name']		= __('Fullwidth MasterSlider Slider', 'avia_framework' );
				$this->config['tab']		= __('Plugin Additions', 'avia_framework' );
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-slideshow-layer.png";
				$this->config['order']		= 10;
				$this->config['target']		= 'avia-target-insert';
				$this->config['shortcode'] 	= 'av_masterslider';
				$this->config['tooltip'] 	= __('Display a fullwidth MasterSlider Slider', 'avia_framework' );
				$this->config['tinyMCE'] 	= array('disable' => "true");
				$this->config['drag-level'] = 1;
			}


			/**
			 * Register fullwidth shortcode
			 */
			function extra_assets()
			{
				AviaBuilder::$full_el_no_section[] = $this->config['shortcode'];
				AviaBuilder::$full_el[] = $this->config['shortcode'];
			}

			
			/**
			 * Editor Element - this function defines the visual appearance of an element on the AviaBuilder Canvas
			 * Most common usage is to define some markup in the $params['innerHtml'] which is then inserted into the drag and drop container
			 * Less often used: $params['data'] to add data attributes, $params['class'] to modify the className
			 *
			 *
			 * @param array $params this array holds the default values for $content and $args. 
			 * @return $params the return array usually holds an innerHtml key that holds item specific markup.
			 */
			function editor_element($params)
			{	
				//fetch all registered slides and save them to the slides array
                $slides = get_masterslider_names(false);
				if(empty($params['args']['id']) && is_array($slides)) $params['args']['id'] = reset($slides);

                $element = array(
					'subtype' => $slides, 
					'type'=>'select', 
					'std' => $params['args']['id'],
					'class' => 'avia-recalc-shortcode',
					'data'	=> array('attr'=>'id')
				);
				
				$inner		 = "<img src='".$this->config['icon']."' title='".$this->config['name']."' />";
				
				
				if(empty($slides))
				{
					$inner.= "<div><a target='_blank' href='".admin_url( 'admin.php?page=masterslider' )."'>".__('No MasterSlider Found. Click here to create one','avia_framework' )."</a></div>";
				}
				else
				{
					$inner .= "<div class='avia-element-label'>".$this->config['name']."</div>";
					$inner .= AviaHtmlHelper::render_element($element);
					$inner .= "<a target='_blank' href='".admin_url( 'admin.php?page=masterslider' )."'>".__('Edit MasterSlider here','avia_framework' )."</a>";
				}
				
				
				$params['class'] = "av_sidebar";
				$params['content']	 = NULL;
				$params['innerHtml'] = $inner;
				
				return $params;
			}
			
			/**
			 * Frontend Shortcode Handler
			 *
			 * @param array $atts array of attributes
			 * @param string $content text within enclosing form of shortcode element 
			 * @param string $shortcodename the shortcode found, when == callback name
			 * @return string $output returns the modified html string 
			 */
			function shortcode_handler($atts, $content = "", $shortcodename = "", $meta = "")
			{
				$output  = "";
				
				$skipSecond = false;
                avia_sc_masterslider::$slide_count++;
				
				global $mspdb;
				$result = $mspdb->get_slider_field_val($atts['id'], 'params');

                // get slider height
				if(!empty($result))
				{		
					$slider_settings = json_decode(base64_decode($result), true);
 
                    if(!empty($slider_settings['MSPanel.Settings'][1]))
                    {
                    	$slider_settings = json_decode($slider_settings['MSPanel.Settings'][1], true);
 
						if(!empty($slider_settings['height']))
						{
							$height = (int)$slider_settings['height'];
							$params['style'] = " style='height: ".($height+1)."px;' ";
						}
					}
				}
				
				$params['class'] = "avia-layerslider main_color avia-shadow ".$meta['el_class'];
				$params['open_structure'] = false;
				
				//we dont need a closing structure if the element is the first one or if a previous fullwidth element was displayed before
				if($meta['index'] == 0) $params['close'] = false;
				if(!empty($meta['siblings']['prev']['tag']) && in_array($meta['siblings']['prev']['tag'], AviaBuilder::$full_el_no_section)) $params['close'] = false;
				
				if($meta['index'] != 0) $params['class'] .= " slider-not-first";
				$params['id'] = "layer_slider_".(avia_sc_masterslider::$slide_count);
				
				
				$output .=  avia_new_section($params);
				$output .= get_masterslider($atts['id']);
				$output .= "</div>"; //close section
				
				
				//if the next tag is a section dont create a new section from this shortcode
				if(!empty($meta['siblings']['next']['tag']) && in_array($meta['siblings']['next']['tag'],  AviaBuilder::$full_el))
				{
				    $skipSecond = true;
				}

				//if there is no next element dont create a new section.
				if(empty($meta['siblings']['next']['tag']))
				{
				    $skipSecond = true;
				}
				
				if(empty($skipSecond)) {
				
				$output .= avia_new_section(array('close'=>false, 'id' => "after_layer_slider_".avia_sc_masterslider::$slide_count));
				
				}
				
				return $output;
			}
	
	}
}