<?php
if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/
 /****************************************************************************************
 *
 *
 *
 *	 [before]
 *	class :  set in shortcode attributes
 *
 *	[after]
 *	('wppizza_filter_additives_widget_markup', $markup, $atts): filters markup ($markup = array(),$atts = array(), $additives=array())
 ****************************************************************************************/
?>
<?php
	$markup['div_'] = '<div id="' . $id . '" class="' . $class . '">';

		foreach($allergens as $key=>$allergen){
			$markup['allergen_'.$key] = '<span class="' . $allergen['class'] . '"><sup>' . $allergen['ident'] . '</sup>' . $allergen['name'] . '</span> ';
		}

		foreach($additives as $key=>$additive){
			$markup['additive_'.$key] = '<span class="' . $additive['class'] . '"><sup>' . $additive['ident'] . '</sup>' . $additive['name'] . '</span> ';
		}
	$markup['_div'] = '</div>';
?>