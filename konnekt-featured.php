<?php
/*
Plugin Name: Kollekted Featured Posts
Version: 0.1.1
Plugin URI: http://dpg.se/
Description: Displays links to featured articles with background image.
Author: David Hall
Author URI: http://dpg.se/
*/

/*  Copyright 2008  David Hall (david[in]kollekted.se)
*/


function kollekted_featured_extract_image($html) {
	if(stristr($html, '<img ') !== FALSE) 
	{
		$start = stripos($html,'<img ');
		$end = stripos($html,'>',$start);
		$imgtag = substr($html, $start, $end-$start+1);
		$new = str_replace('&',"&amp;",substr($imgtag,4,-2));
		$new = str_replace('" ',"&",$new);
		$new = str_replace('="',"=",$new);
		parse_str($new, $output);
		return $output;
	}
	else {
		return;
	}
}

$kollektedfeatured_options['widget_fields']['title'] = array('label'=>'Title:', 'type'=>'text', 'default'=>__('Featured posts'));
$kollektedfeatured_options['widget_fields']['metakey'] = array('label'=>'Meta key:', 'type'=>'text', 'default'=>'featured');
$kollektedfeatured_options['prefix'] = 'kollektedfeatured';


function widget_init_kollektedfeatured() {

// settings to fix:
// number of posts
// sort: date, random

	// Check for required functions
	if (!function_exists('register_sidebar_widget'))
		return;
		
		// This is the function that outputs the form.
	function widget_kollektedfeatured_control($number=1) {
		
		global $kollektedfeatured_options;
		
		// Get our options and see if we're handling a form submission.
		$options = get_option('widget_kollektedfeatured');


		if ( isset($_POST['kollektedfeatured-submit']) ) {

			foreach($kollektedfeatured_options['widget_fields'] as $key => $field) {
				$options[$number][$key] = $field['default'];
				$field_name = sprintf('%s_%s_%s', $kollektedfeatured_options['prefix'], $key, $number);

				if ($field['type'] == 'text') {
					$options[$number][$key] = strip_tags(stripslashes($_POST[$field_name]));
				} elseif ($field['type'] == 'checkbox') {
					$options[$number][$key] = isset($_POST[$field_name]);
				}
			}

			update_option('widget_kollektedfeatured', $options);
		}

		foreach($kollektedfeatured_options['widget_fields'] as $key => $field) {
			
			$field_name = sprintf('%s_%s_%s', $kollektedfeatured_options['prefix'], $key, $number);
			$field_checked = '';
			if ($field['type'] == 'text') {
				$field_value = htmlspecialchars($options[$number][$key], ENT_QUOTES);
			} elseif ($field['type'] == 'checkbox') {
				$field_value = 1;
				if (! empty($options[$number][$key])) {
					$field_checked = 'checked="checked"';
				}
			}
			
			printf('<p style="text-align:right;" class="kollektedfeatured_field"><label for="%s">%s <input id="%s" name="%s" type="%s" value="%s" class="%s" %s /></label></p>',
				$field_name, __($field['label']), $field_name, $field_name, $field['type'], $field_value, $field['type'], $field_checked);
		}
		echo '<input type="hidden" id="kollektedfeatured-submit" name="kollektedfeatured-submit" value="1" />';
	}


	function widget_kollektedfeatured($args) {
		global $kollektedfeatured_options;
		
	    extract($args);
	    	$options = get_option('widget_kollektedfeatured');
		
		// fill options with default values if value is not set
		$number=1;
		$item = $options[$number];
		foreach($kollektedfeatured_options['widget_fields'] as $key => $field) {
			if (! isset($item[$key])) {
				$item[$key] = $field['default'];
			}
		}
		
		if($item['metakey']=='') {$item['metakey']=$kollektedfeatured_options['widget_fields']['metakey']['default'];}

?>
		<?php echo $before_widget; ?>
	    <?php echo $before_title
	    . $item['title']
	    . $after_title; ?>
<?php
		global $post;
		$myposts = get_posts('numberposts=5&offset=0&meta_key='.$item['metakey'].'&meta_value=1');
		foreach($myposts as $posta) :
			$output=kollekted_featured_extract_image($posta->post_content);
			$color=substr(md5($posta->post_title),0,6);
			if($output) {echo '<div style="background-image: url('.$output['src'].')">';} else {echo '<div style="background-color:#'.$color.';">';}
?><div>
			<h3><a href="<?php echo get_permalink($posta->ID); ?>"><?php echo $posta->post_title; ?></a></h3>
</div>
</div>
		<?php endforeach; ?>
	    <?php echo $after_widget; ?>
		<?php
		}
	$name = sprintf(__('Kollekted Featured'));
	$id = "kollekted-featured"; 
	wp_register_sidebar_widget($id, $name, 'widget_kollektedfeatured');
	wp_register_widget_control($id, $name, 'widget_kollektedfeatured_control');
}


add_action('widgets_init', 'widget_init_kollektedfeatured');
?>