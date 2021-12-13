<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php
/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*
*
*
*	static helper functions
*
*
*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\*/

/*********************************************************
*	[check if debug is on and logging only ]
*********************************************************/
function wppizza_debug(){
	static $debug = null;
	if($debug === null){
		$debug=false;
		if(defined('WP_DEBUG') && defined('WP_DEBUG_LOG') && defined('WP_DEBUG_DISPLAY') && WP_DEBUG === true && WP_DEBUG_LOG === true && WP_DEBUG_DISPLAY === false){
			$debug=true;
		}
	}
return $debug;
}

/*********************************************************
*	[get wppizza version]
*********************************************************/
function wppizza_major_version(){
	static $version = null;
	if($version === null){

 		if ( version_compare( WPPIZZA_VERSION, '3', '>=' ) ) {
           	$version = 3;
           	return $version;
        }
 		/* future versions */
 		if ( version_compare( WPPIZZA_VERSION, '4', '>=' ) ) {
           	$version = 4;
           	return $version;
        }
 		/* future versions */
 		if ( version_compare( WPPIZZA_VERSION, '5', '>=' ) ) {
           	$version = 5;
           	return $version;
        }
	}
return $version;
}

/*********************************************************
*	[get active wppizza widgets ]
*********************************************************/
function wppizza_active_widgets(){
	static $active_wppizza_widgets = null;
	if($active_wppizza_widgets === null){

		/* get all wppizza widgets */
		$get_wppizza_widgets =  get_option('widget_wppizza_widgets');

		/* make array of (unique) type of wppizza widgets in use in sidebar */
		$active_wppizza_widgets = array();
		$all_active_sidebar_widgets = wp_get_sidebars_widgets();
		unset($all_active_sidebar_widgets['wp_inactive_widgets']);
		if(!empty($all_active_sidebar_widgets) && is_array($all_active_sidebar_widgets)){
			foreach($all_active_sidebar_widgets as $sbID=>$widgets){
				if(!empty($widgets)){
				foreach($widgets as $widget){
					$xWidget = explode('-',$widget);
					if($xWidget[0] == 'wppizza_widgets'){
						/** get type of widget **/
						$type = $get_wppizza_widgets[$xWidget[1]]['type'];
						/** add to array **/
						$active_wppizza_widgets[$type] = true ;
					}
				}}
			}
		}
	}
return $active_wppizza_widgets;
}

/*********************************************************
*	[orderpage widget on page ?]
*********************************************************/
function wppizza_has_orderpage_widget(){
	static $has_orderpage_widget = null;
	if($has_orderpage_widget === null){

		$has_orderpage_widget = false;

		$active_wppizza_widgets = wppizza_active_widgets();
		/* if there's an active orderpage widget */
		if(!empty($active_wppizza_widgets['orderpage'])){
			$has_orderpage_widget = true;
		}
	}

return $has_orderpage_widget;
}

/***********************************************************
	get registered and enabled gateway objects
	should be used/run later than init hook |  priority:9
	@param void
	@return obj
	@since 3.9
***********************************************************/
function wppizza_get_active_gateways(){
	static $registered_gateways = null;
	if($registered_gateways === null){
		$registered_gateways = WPPIZZA() -> gateways -> gwobjects;
		/* for the time being - loose some overkill data */
		if(!empty($registered_gateways)){
		foreach($registered_gateways as $k => $obj){
			unset($registered_gateways -> $k -> gateway_settings);
		}}
	}
return $registered_gateways;
}

/*********************************************************
	[check if we are on orderpage]
*********************************************************/
function wppizza_is_orderpage(){
	static $is_orderpage = null;

	if($is_orderpage === null){


		global $wppizza_options, $post;
		/*
			the set orderpage in admin
		*/
		$order_page = $wppizza_options['order_settings']['orderpage'];

		/*ini as false*/
		$is_orderpage = false;

		/**
			set flag that we are on order page to not do any redirection for example
			provided we have a post object and ID
		**/
		if(is_object($post) && $post->ID==$order_page){
			$is_orderpage = true;
		}

		/**
			if called before post object is available, get post_id from url
		**/
		if( ( !is_object($post) || empty($post->ID) ) && (!defined('DOING_AJAX') || !DOING_AJAX)){

			$REQUEST_SCHEME = is_ssl() ? 'https' : 'http';
			$current_url = $REQUEST_SCHEME . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ;
			$post_id = url_to_postid($current_url);
			if($post_id == $order_page){
				$is_orderpage = true;
			}
		}
		/**
			if called from ajax and distinctly set to be checkout or not posting
			$_POST['vars']['isCheckout'] without having a post object
		**/
		if( !is_object($post) && empty($post->ID) && defined('DOING_AJAX') && DOING_AJAX && isset($_POST['vars']['isCheckout'])){
			// js may return a true/false string
			if(filter_var($_POST['vars']['isCheckout'], FILTER_VALIDATE_BOOLEAN)){
				$is_orderpage = true;
			}
		}
	}

return $is_orderpage;
}
/* alias of wppizza_is_orderpage */
function wppizza_is_checkout(){
	return wppizza_is_orderpage();
}

/*********************************************************
	[check if it's the "cancelled" orderpage]
	@since 3.13
*********************************************************/
function wppizza_is_cancelpage(){
	static $is_cancelled = null;

	if($is_cancelled === null){

		$is_cancelled = false;

		if(wppizza_is_checkout() && isset($_GET[WPPIZZA_TRANSACTION_CANCEL_PREFIX])){
			$is_cancelled = true;
		}
	}

return $is_cancelled;
}
/* alias of wppizza_is_cancelpage */
function wppizza_is_cancelled(){
	return wppizza_is_cancelpage();
}


/*********************************************************
	[check if it's the "thankyou" orderpage]
	@since 3.13
*********************************************************/
function wppizza_is_thankyoupage(){
	static $is_thankyou = null;

	if($is_thankyou === null){

		$is_thankyou = false;

		if(wppizza_is_checkout() && isset($_GET[WPPIZZA_TRANSACTION_GET_PREFIX])){
			$is_thankyou = true;
		}
	}

return $is_thankyou;
}
/* alias of wppizza_is_thankyoupage */
function wppizza_is_thankyou(){
	return wppizza_is_thankyoupage();
}

/*********************************************************
	[check if we are on users order history page
	within a hook or elsehwere that has global $post availabe
	bypass by default if already logged in ]
*********************************************************/
function wppizza_is_orderhistory($check_for_login = true){
	global $post;
	static $is_orderhistory = null;

	if($is_orderhistory === null && is_object($post)){
		/* if we are logged in already, there's no login form */
		if($check_for_login){
			if(is_user_logged_in()){
				$is_orderhistory = false;
				return $is_orderhistory;
			}
		}

		/* check if it has ANY wppizza shortcode to start off with */
		if( has_shortcode( $post->post_content, 'wppizza' ) ) {
			$pattern = get_shortcode_regex();

			/* basic match */
			if(
				preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )
				&& array_key_exists( 2, $matches )
				&& in_array( 'wppizza', $matches[2] )
			){

				/** check if its a wppizza shortcode and type 'orderhistory' **/
				if(!empty($matches[2])){
					foreach($matches[2] as $k=>$val)
					if($val == 'wppizza' && strpos($matches[3][$k], 'orderhistory') !== false){
						$is_orderhistory =  true;
						return $is_orderhistory;
					}
				}
			}
		$is_orderhistory = false;
		return $is_orderhistory;
		}
		$is_orderhistory = false;
		return $is_orderhistory;
	}
	return $is_orderhistory;
}

/***********************************************************
	get all wordpress pages for current blog
***********************************************************/
function wppizza_get_wordpress_pages() {
	static $wordpress_pages = null;
	if($wordpress_pages === null){
		/*get all pages - possibly get these hierarchical to save some child queries for 'category_parent_page' **/
		$wordpress_pages=get_pages(array('post_type'=> 'page', 'echo'=>0, 'title_li'=>''));
	}
	return $wordpress_pages;
}

/***********************************************************
	get wordpress defined image sizes
***********************************************************/
function wppizza_get_wordpress_image_sizes() {
	static $sizes = null;

	if($sizes === null){

		global $_wp_additional_image_sizes;

	    $sizes = array();

	    foreach ( get_intermediate_image_sizes() as $_size ) {

	        // skip post-thumbnail here (for now) it only confuses the issue
	        // as it's actually the full size featured images from what i can tell
	        // and not really a thumbnail at all
	        if($_size == 'post-thumbnail'){continue;}

	        if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
	            $sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
	            $sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
	            $sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
	            $sizes[ $_size ]['label']   = ucwords(str_replace('_',' ',$_size)) . ' ['.$sizes[ $_size ]['width'].'x'.$sizes[ $_size ]['height'].']';
	        } elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
	            $sizes[ $_size ] = array(
	                'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
	                'height' => $_wp_additional_image_sizes[ $_size ]['height'],
	                'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
	                'label'   => '['.$_size.']',
	            );
	        }
	    }

	}
return $sizes;
}

/***********************************************************
	get network sites/pages (current blog only if not in network setup)
***********************************************************/
function wppizza_get_networkpages(){
	static $network_pages = null;
	if($network_pages === null){

		$network_pages=array();

		/*multisite*/
		if(is_multisite()){
			$args=array();
			$allsites=wp_get_sites( $args );
			/*get all published pages*/
			foreach($allsites as $nws=>$blog){
				if($blog['public']==1){
					switch_to_blog($blog['blog_id']);

					$network_pages[$nws]['blog_id']=$blog['blog_id'];
					$network_pages[$nws]['site_id']=$blog['site_id'];
					$network_pages[$nws]['blogname']=get_bloginfo('name');
					$network_pages[$nws]['url']=site_url();
					/*get pages*/
					$pages=get_pages(array('post_type'=> 'page','echo'=>0,'title_li'=>''));
					foreach($pages as $a=>$b){
						$pageids[$b->ID]=array('title'=>$b->post_title);
					}
					$network_pages[$nws]['pageids']=$pageids;
					restore_current_blog();
				}
			}
		}

		/*single site*/
		if(!is_multisite()){
			global $blog_id;
			$network_pages[$blog_id]['blog_id']=$blog_id;
			$network_pages[$blog_id]['site_id']=1;
			$network_pages[$blog_id]['blogname']=get_bloginfo('name');
			$network_pages[$blog_id]['url']=site_url();
			/*get pages*/
			$pages=get_pages(array('post_type'=> 'page','echo'=>0,'title_li'=>''));
			foreach($pages as $a=>$b){
				$pageids[$b->ID]=array('title'=>$b->post_title);
			}
			$network_pages[$blog_id]['pageids']=$pageids;
		}
	}

	return $network_pages;
}

/***********************************************************
	get wppizza categories
***********************************************************/
function wppizza_get_categories() {
	static $wppizza_categories = null;
	if($wppizza_categories === null){
		$args = array('taxonomy' => ''.WPPIZZA_TAXONOMY.'');
		$wppizza_categories=get_categories($args);
	}
return $wppizza_categories;
}

/***********************************************************
	get wppizza menu items
***********************************************************/
function wppizza_get_menu_items() {
	static $wppizza_menu_items = null;
	if($wppizza_menu_items === null){
		$args = array('post_type' => ''.WPPIZZA_POST_TYPE.'','posts_per_page' => -1, 'orderby'=>'title' ,'order' => 'ASC');
		$query = new WP_Query( $args );
		$wppizza_menu_items=$query->posts;
	}

	/*wp_reset_query(); probably not needed*/

	return $wppizza_menu_items;
}

/***********************************************************
	get all wppizza additives
***********************************************************/
function wppizza_all_additives() {
	static $additives = null;
	if($additives === null){
		global $wppizza_options;
		$set_additives = $wppizza_options['additives'];

		$additives = array();
		if(is_array($set_additives)){
			asort($set_additives);

			/**add key as ident in case there's no sorting set yet*/
			foreach($set_additives as $key=>$value){
				$ident = empty($value['sort']) ? $key : $value['sort'] ;

				$additives[$key] = $value;

				$additives[$key]['ident'] = $ident;

				$additives[$key]['id'] = '' . WPPIZZA_PREFIX . '-additive-' . $key . '';

				/*set classes*/
				$additives_class[$key]['class'] = array();
				$additives_class[$key]['class'][] = '' . WPPIZZA_PREFIX . '-additive';
				$additives_class[$key]['class'][] = '' . WPPIZZA_PREFIX . '-additive-' . $key . '';

				/*implode for output*/
				$additives[$key]['class'] = implode(' ', $additives_class[$key]['class']);
			}
		}

	}
	return $additives;
}

/***********************************************************
	get all wppizza allergens
***********************************************************/
function wppizza_all_allergens($classIdent = 'allergen') {
	static $allergens = null;
	if($allergens === null){
		global $wppizza_options;
		$set_allergens = $wppizza_options['allergens'];

		$allergens = array();
		if(is_array($set_allergens)){
			asort($set_allergens);

			/**add key as ident in case there's no sorting set yet*/
			foreach($set_allergens as $key=>$value){
				$ident = empty($value['sort']) ? $key : $value['sort'] ;

				$allergens[$key] = $value;

				$allergens[$key]['ident'] = $ident;

				$allergens[$key]['id'] = '' . WPPIZZA_PREFIX . '-'.$classIdent.'-' . $key . '';

				/*set classes*/
				$allergens_class[$key]['class'] = array();
				$allergens_class[$key]['class'][] = '' . WPPIZZA_PREFIX . '-'.$classIdent.'';
				$allergens_class[$key]['class'][] = '' . WPPIZZA_PREFIX . '-'.$classIdent.'-' . $key . '';
				if(!empty($value['icon'])){
				$allergens_class[$key]['class'][] = '' . WPPIZZA_PREFIX . '-'.$classIdent.'-' . $value['icon'] . '';
				$allergens_class[$key]['class'][] = '' . WPPIZZA_PREFIX . '-'.$classIdent.'-icon';
				}

				/*implode for output*/
				$allergens[$key]['class'] = implode(' ', $allergens_class[$key]['class']);
			}
		}

	}
	return $allergens;
}

/***********************************************************
	get all wppizza foodtype
***********************************************************/
function wppizza_all_foodtype($classIdent = 'ftype') {
	static $foodtype = null;
	if($foodtype === null){
		global $wppizza_options;
		$set_foodtype = $wppizza_options['foodtype'];

		$foodtype = array();
		if(is_array($set_foodtype)){

			asort($set_foodtype);

			/**add key as ident in case there's no sorting set yet*/
			foreach($set_foodtype as $key=>$value){
				$ident = empty($value['sort']) ? $key : $value['sort'] ;

				$foodtype[$key] = $value;

				$foodtype[$key]['ident'] = $ident;

				$foodtype[$key]['id'] = '' . WPPIZZA_PREFIX . '-'.$classIdent.'-' . $key . '';

				/*set classes*/
				$foodtype_class[$key]['class'] = array();
				$foodtype_class[$key]['class'][] = '' . WPPIZZA_PREFIX . '-'.$classIdent.'';
				$foodtype_class[$key]['class'][] = '' . WPPIZZA_PREFIX . '-'.$classIdent.'-' . $key . '';
				if(!empty($value['icon'])){
				$foodtype_class[$key]['class'][] = '' . WPPIZZA_PREFIX . '-'.$classIdent.'-' . $value['icon'] . '';
				$foodtype_class[$key]['class'][] = '' . WPPIZZA_PREFIX . '-'.$classIdent.'-icon';
				}

				/*implode for output*/
				$foodtype[$key]['class'] = implode(' ', $foodtype_class[$key]['class']);
			}
		}

	}
	return $foodtype;
}



/***********************************************************
	delivery set to pickup ?
	Note: this should NOT run statically as the change
	from pickup to delivery and vice versa may happen further
	down the chain of events.
	make sure to use the right filter hook in sequence if
	you rely on this being accurate
***********************************************************/
function wppizza_is_pickup(){
	$is_pickup = WPPIZZA() -> session -> is_pickup();
return $is_pickup;
}

/***********************************************************
	for some reason people insist on using the non existing
	function wppizza_is_delivery() and are then surprised if they are
	getting fatal errors, so let's just define it now to stop
	unnecessary support requests (since 3.13.2)
***********************************************************/
function wppizza_is_delivery(){
	$is_pickup  = WPPIZZA() -> session -> is_pickup();
	$is_delivery = !empty($is_pickup) ? false : true;
return $is_delivery;
}

/***********************************************************
	get full cart contents
	@since 3.2.7
***********************************************************/
function wppizza_get_cart($is_checkout = null, $recalculate = false){
	$cart_contents = WPPIZZA() -> session -> get_cart($is_checkout, $recalculate);
return $cart_contents;
}

/***********************************************************
	get full cart html markup
	@since 3.10.2
***********************************************************/
function wppizza_get_cart_markup($is_checkout = null){
	$cart_markup = WPPIZZA() -> markup_maincart -> cart_contents_markup_from_session($is_checkout);
return $cart_markup;
}

/***********************************************************
	get cart summary only
	@since 3.2.7
***********************************************************/
function wppizza_cart_summary($is_checkout = null, $recalculate = false){
	$cart_summary = WPPIZZA() -> session -> get_cart_summary($is_checkout, $recalculate);
return $cart_summary;
}

/***********************************************************
	get cart items only
	@since 3.10.2
***********************************************************/
function wppizza_get_cart_items($is_checkout = null, $recalculate = false){
	$cart_items = WPPIZZA() -> session -> get_cart_items($is_checkout, $recalculate);
return $cart_items;
}

/***********************************************************
	is cart empty ?
***********************************************************/
function wppizza_cart_is_empty(){
	static $cart_is_empty = null;

	if($cart_is_empty === null){
		$cart_is_empty = WPPIZZA() -> session -> cart_is_empty();
	}

return $cart_is_empty;
}

/***********************************************************
	are there products added to cart ?
	(inverted alias of wppizza_cart_is_empty really )
***********************************************************/
function wppizza_cart_has_items(){
	static $cart_has_items = null;

	if($cart_has_items === null){
		$cart_has_items = WPPIZZA() -> session -> cart_has_items();
	}

return $cart_has_items;
}

/***********************************************************
	admin shortcode on frontend page ?
	@since 3.5
***********************************************************/
function wppizza_has_admin_shortcode(){
	static $has_admin_shortcode = null;
	/* allow filtering */
	if($has_admin_shortcode === null){
		$has_admin_shortcode = apply_filters('wppizza_has_admin_shortcode', false);
	}

return $has_admin_shortcode;
}

/***********************************************************
	get user personal info session data
	@since 3.7
***********************************************************/
function wppizza_user_session_personal_info(){
	static $user_session = null;
	/* allow filtering */
	if($user_session === null){
		/* get session user data */
		$user_session = WPPIZZA()->session->get_userdata();
		/* unset superfluous data that would only confuse the issue here*/
    	if(isset($user_session['wppizza_hash'])){
    		unset($user_session['wppizza_hash']);
    	}
    	if(isset($user_session['wppizza_order_id'])){
    		unset($user_session['wppizza_order_id']);
    	}
	}
return $user_session;
}

/***********************************************************
	get user email from session data
	@since 3.7
***********************************************************/
function wppizza_user_session_email(){
	static $user_session_email = null;
	/* run once */
	if($user_session_email === null){
		/* get session user data */
		$user_session = WPPIZZA()->session->get_userdata();
		/* set email or empty */
		$user_session_email = isset($user_session['cemail']) ? $user_session['cemail'] : '';
	}
return $user_session_email;
}

/***********************************************************
	get current order id in db associated with a session 
	- for use outside hooks that should have this already available anyway
	(this will only be available once the user has been to the order page at least once)
	@since 3.13.5
***********************************************************/
function wppizza_order_id(){
	static $current_order_id = null;
	/* run once */
	if($current_order_id === null){
		/* get session user data */
		$user_session = WPPIZZA()->session->get_userdata();
		/* set email or empty */
		$current_order_id = isset($user_session['wppizza_order_id']) ? $user_session['wppizza_order_id'] : false;
	}
return $current_order_id;
}

/***********************************************************
	is shop currently open ?
***********************************************************/
function wppizza_is_shop_open(){
	global $wppizza_options;
	static $shop_open = null;

	/* allow filtering */
	$shop_open = apply_filters('wppizza_shop_is_open', $shop_open);

	/* we have forcefully closed the shop overriding everything else */
	$shop_open = !empty($wppizza_options['openingtimes']['close_shop_now']) ? false : $shop_open;

	if($shop_open === null){


		$todayWday=date("w",WPPIZZA_WP_TIME);

		$d=date("d",WPPIZZA_WP_TIME);
		$m=date("m",WPPIZZA_WP_TIME);
		$Y=date("Y",WPPIZZA_WP_TIME);

		$standard = $wppizza_options['openingtimes']['opening_times_standard'];
		$custom = $wppizza_options['openingtimes']['opening_times_custom'];
		$breaks = $wppizza_options['openingtimes']['times_closed_standard'];


		/**make sunday 7 instead of 0 to aid sorting**/
		if($todayWday==0){$yesterdayWday=6;}else{$yesterdayWday=($todayWday-1);}
		/**get the opening times today, as well as the spillover from yesterday
		in case its very early in the morning and we dont close until after midnight on the previous day**/
		$todayTimes=$standard[$todayWday];
		$yesterdayTimes=$standard[$yesterdayWday];
		$todayStartTime	= mktime(0, 0, 0, $m , $d, $Y);
		$todayEndTime	= mktime(23, 59, 59, $m , $d, $Y);
		$todayDate	= ''.$Y.'-'.$m.'-'.$d.'';
		$yesterdayDate	= date("Y-m-d",mktime(12, 0, 0, $m , $d-1, $Y));

		/**now we first check if these dates have custom dates opening times****/
		if(count($custom)>0){
			$yesterdayCustom = array_search($yesterdayDate, wppizza_array_column($custom, 'date'));/* will return key of only the first one found */
			$todayCustom = array_search($todayDate, wppizza_array_column($custom, 'date'));/* will return key of only the first one found */

		}

		/*if we have found dates in custom dates array,make start and end and use these**/
		if(isset($yesterdayCustom) && $yesterdayCustom!==false){
			$t=wpizza_get_opening_times($custom[$yesterdayCustom]['open'],$custom[$yesterdayCustom]['close'],$d,$m,$Y,'yesterday');
			if($t){
				$openToday[]=array('start'=>$t['start'],'end'=>$t['end']);
			}
		}else{//use times from standard opening times
			$t=wpizza_get_opening_times($standard[$yesterdayWday]['open'],$standard[$yesterdayWday]['close'],$d,$m,$Y,'yesterday');
			if($t){
				$openToday[]=array('start'=>$t['start'],'end'=>$t['end']);
			}
		}
		if(isset($todayCustom) && $todayCustom!==false){
			$t=wpizza_get_opening_times($custom[$todayCustom]['open'],$custom[$todayCustom]['close'],$d,$m,$Y,'today');
				$openToday[]=array('start'=>$t['start'],'end'=>$t['end']);
		}else{//use times from standard opening times
			$t=wpizza_get_opening_times($standard[$todayWday]['open'],$standard[$todayWday]['close'],$d,$m,$Y,'today');
			if($t){
				$openToday[]=array('start'=>$t['start'],'end'=>$t['end']);
			}
		}

		/*********
			check if we have added some breaks/siestas whatever you want to call it
		**********/
		if(count($breaks)>0){
			/**first check if today is a custom day and if we've set break times for it**/
			if( isset($todayCustom) && $todayCustom!==false ){
				foreach($breaks as $k=>$v){
					if($v['day']=='-1'){
						$t=wpizza_get_opening_times($v['close_start'],$v['close_end'],$d,$m,$Y,'today');
						if($t['start']<=WPPIZZA_WP_TIME && $t['end']>=WPPIZZA_WP_TIME){
							$shop_open = false;
							break;
						}
					}
				}
			}else{
				/**its not a custom day, so check if we havea break set for this weekday**/
				foreach($breaks as $k=>$v){
					if($todayWday==$v['day']){
						$t=wpizza_get_opening_times($v['close_start'],$v['close_end'],$d,$m,$Y,'today');
						if($t['start']<=WPPIZZA_WP_TIME && $t['end']>=WPPIZZA_WP_TIME){
							$shop_open = false;
							break;
						}
					}
				}

			}
		}
		/********
			we've done the siesta/break check, now check if current time is in the $openToday array between start and end
		********/
		if($shop_open === null){
			if(!empty($openToday)){
			foreach($openToday as $k=>$times){
				if( WPPIZZA_WP_TIME >= $times['start'] && WPPIZZA_WP_TIME <= $times['end']){
					$shop_open = true;
					break;
				}
			}}else{
				$shop_open = false;
			}
		}
	}

return $shop_open;
}

/****************************************************************************
	check if a timestamp is between todays todays opening and closing time
	(business days could cross midnight)
	php >=5.3

	@$timestamp (int)
	@return bool
****************************************************************************/
function wppizza_is_current_businessday($timestamp, $timestampcurrent = false){
	global $wppizza_options;
	/*php 3,3+ needed for DateTime function*/
	if( version_compare( PHP_VERSION, '5.3', '<' )) {return true;}
	/**ini as true*/
	$isCurrentBusinessday=true;
	/*
		no timetamp set, set current - default but changeable if needed for some reason
	*/
	if(!$timestampcurrent){
		$timestampcurrent = WPPIZZA_WP_TIME;
	}
	/*get options*/
	$standard = $wppizza_options['openingtimes']['opening_times_standard'];
	$custom = $wppizza_options['openingtimes']['opening_times_custom'];

	/*get standard opening/closing times of current day*/
	foreach($standard as $k=>$stdTime){
		$open = DateTime::createFromFormat('H:i', $stdTime['open'])->getTimestamp();
		$close = DateTime::createFromFormat('H:i', $stdTime['close'])->getTimestamp();
		/*closed<open=>add a day*/
		if($close<$open){
			$close = strtotime('+1 day', $close);
		}
		if($timestampcurrent<=$close && $timestampcurrent>=$open){
			$currentbusinessday=array('open'=>$open,'close'=>$close);
			break;
		}
	}
	/*get opening/closing times of current day if set*/
	if(!empty($custom)){
	foreach($custom as $k=>$cstDate){
		$open = DateTime::createFromFormat('Y-m-d H:i', ''.$cstDate['date'].' '.$cstDate['open'].'')->getTimestamp();
		$close = DateTime::createFromFormat('Y-m-d H:i', ''.$cstDate['date'].' '.$cstDate['close'].'')->getTimestamp();
		/*closed<open=>add a day*/
		if($close<$open){
			$close = strtotime('+1 day', $close);
		}
		if($timestampcurrent<=$close && $timestampcurrent>=$open){
			$currentbusinessday=array('open'=>$open,'close'=>$close);
			break;
		}
	}}


	if(empty($currentbusinessday) || $timestamp<$currentbusinessday['open'] || $timestamp>$currentbusinessday['close']){
		$isCurrentBusinessday=false;
	}

return $isCurrentBusinessday;
}

/****************************************************************************
	get all completed business days (i.e days where closing time is before now)
	within the last week ignoring closing times in between
	(business days could cross midnight)
	php >=5.3

	@$timestamp (int)
	@return array()
****************************************************************************/
function wppizza_completed_businessdays($current_timestamp){
	static $completed_businessdays = null;

	/* only run once */
	if($completed_businessdays === null){
		global $wppizza_options;

		/*php 3,3+ needed for DateTime function*/
		if( version_compare( PHP_VERSION, '5.3', '<' )) {return false;}

		/**ini return array*/
		$completed_businessdays = array();


		/*get opening times set options*/
		$standard = $wppizza_options['openingtimes']['opening_times_standard'];
		$custom = $wppizza_options['openingtimes']['opening_times_custom'];

		/* now week day */
		$todayWday=date("w",WPPIZZA_WP_TIME);

		/*
			loop through standard times (i reverse form today) and check if current time is already past
			closing time of this day. if so capture start/end time for this as last completed
			standard business day
		*/
		$day_key = 0;

		/*
			we start with the current day
			and *go back in time* / *in reverse* for a week
		*/
		for($i=$todayWday; $i<($todayWday+7) ; $i++){

			/*
				get the right weekday key going backwards in time for a week from now
			*/
			$setWeekDayKey = ($i - ($day_key*2));
			$weekDayKey = ($setWeekDayKey < 0) ? ($setWeekDayKey +7) : $setWeekDayKey;

			/* set open time for day */
			$open = DateTime::createFromFormat('H:i', $standard[$weekDayKey]['open'])->getTimestamp();
			$open = ($day_key>0) ? strtotime('-'.$day_key.' day', $open) : $open;

			/* set closing time for day */
			$close = DateTime::createFromFormat('H:i', $standard[$weekDayKey]['close'])->getTimestamp();
			$close = ($day_key>0) ? strtotime('-'.$day_key.' day', $close) : $close;
			$close = ($close<$open) ? strtotime('+1 day', $close) : $close;

			/* get date of this day taken from opening time */
			$ymd = date('Y-m-d', $open);
			$mdy_label = date('D, M-d-Y', $open);
			$wday=date("w",$open);

			/* check if there are some custom dates set */
			if(count($custom)>0){
				/*
					as there are custom dates, check for this date in those custom times
					if this date is set in the custom days, use open/close from that one
				*/
				$custom_date_key = array_search($ymd, wppizza_array_column($custom, 'date'));/* will return key of only the first one found */
				if($custom_date_key !== false){
					$open = DateTime::createFromFormat('Y-m-d H:i', ''.$custom[$custom_date_key]['date'].' '.$custom[$custom_date_key]['open'].'')->getTimestamp();
					$close = DateTime::createFromFormat('Y-m-d H:i', ''.$custom[$custom_date_key]['date'].' '.$custom[$custom_date_key]['close'].'')->getTimestamp();
					$close = ($close<$open) ? strtotime('+1 day', $close) : $close;
				}
			}


			/*
				skip days that are entirely closed
			*/
			if($open != $close){
				/*
					if current time is after this days closing time
					capture this date as a completed business day
				*/
				if($close < $current_timestamp ){
					$completed_businessdays[$day_key] = array(
						'date'=>$ymd,
						'lbl'=> ''.$mdy_label.': '.date('H:i', $open).'-'.date('H:i', $close).'' ,
						'wday'=>$wday,//0 - Sun, 6 Sat
						'open'=>$open, 'close'=>$close,
						'open_formatted'=> date('Y-m-d H:i:s', $open),
						'close_formatted'=>date('Y-m-d H:i:s', $close)
					);
				}
			}
			$day_key++;
		}
	}
return 	$completed_businessdays;
}
/****************************************************************************
	format regular and custom opening times into standard and custom
	opening/closing sequences per weekday / date accounting for closing times
	that cross midnight as well as eliminating custom dates that do not repeat
	yearly if that date and it's last closing time has already passed now
	Note: used statically as helper to further format as required by
	wppizza_get_openingtimes() ;  wppizza_get_shop_status() ;

	@since 3.13.2
	@return array()
****************************************************************************/
function wppizza_openingtimes_formatted(){
	/*
		we only want to do the getting and sorting once
	*/
	static $openingtimes = null;


	if($openingtimes === null){

		/**************************************************
			WPPIZZA OPTIONS
		**************************************************/
		global $wppizza_options;


		/**************************************************
			GET OPENING TIMES AS SET
		**************************************************/
		//$weekDayStart=get_option('start_of_week',7);/* what is set as start day of week . not needed but keep for reference */
		$standard = $wppizza_options['openingtimes']['opening_times_standard'];
		$customdates = $wppizza_options['openingtimes']['opening_times_custom'];
		$breaks = $wppizza_options['openingtimes']['times_closed_standard'];


		/************************************************************************************************************************************************************************
		#																																										#
		#																																										#
		#	REGULAR DAILY OPENING TIMES																																			#
		#																																										#
		#																																										#
		************************************************************************************************************************************************************************/
		$daily = array(
			0 => array(),//Sun
			1 => array(),//Mon
			2 => array(),//Tue
			3 => array(),//Wed
			4 => array(),//Thu
			5 => array(),//Fri
			6 => array(),//Sat
		);

		/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*
		#	STANDARD OPENING TIMES
		#	0 -> Sun, 1 -> Mon, 2 -> Tue, 3 -> Wed, 4 -> Thu, 5 -> Fri, 6 -> Sat
		*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*/
		foreach($standard as $weekDay => $vals){
			//split by : to get opening /closing hours and minutes
			$xValsOpen = explode(':',$vals['open']);
			$xValsClose = explode(':',$vals['close']);
			//format time including seconds
			$valsOpenFormatted = $xValsOpen[0].':'.$xValsOpen[1].':00';
			$valsCloseFormatted = $xValsClose[0].':'.$xValsClose[1].':00';
			//create keys for comparing
			$open_key = (int)($xValsOpen[0] . $xValsOpen[1] . '00');
			$close_key = (int)($xValsClose[0] . $xValsClose[1] . '00');
			$close_next_day = false;
			/******************************************************************
				closed all day - simply add empty array
			******************************************************************/
			if($open_key == $close_key){
				$daily[$weekDay] = array();
				continue;
			}


			/* crossing midnight, alter open / close key */
			if($open_key > $close_key){
				$close_key = (int)(((int)$xValsClose[0]+24) . $xValsClose[1] . '00');
				$close_next_day = true;
			}
			/******************************************************************
				opening times
			******************************************************************/
			//open
			$daily[$weekDay][$open_key] = array(
				'key' => $open_key,
				'type' => 'open',
				'ts' => $valsOpenFormatted,
				'priority' => 0,
			);
			//close
			$daily[$weekDay][$close_key] = array(
				'key' => $close_key,
				'type' => 'close',
				'ts' => $valsCloseFormatted,
				'priority' => 0,
				'next_day' => $close_next_day,
			);
		}
		/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*
		#	STANDARD OPENING TIMES - ADDING BREAKS
		#	getting closing times to merge into standard opening times
		*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*/
		$dailyBreaksValidate = array();
		if(!empty($breaks) && !empty($daily)){
		foreach($breaks as $bKey => $bVals){

			/*
				only breakes for standard days here
				making sure standard weekday is not closed anyway
			*/
			if($bVals['day'] == -1 || empty($daily[$bVals['day']])){
				continue;
			}

			//weekday this closing time belongs to
			$weekDay = $bVals['day'];

			//standard / main opening /closing for this weekday, for ease of use
			$std = array(
				'open' => reset($daily[$weekDay])['key'],
				'close' => end($daily[$weekDay])['key'],
			);

			//split by : to get opening /closing hours and minutes
			$xValsClose = explode(':',$bVals['close_start']);
			$xValsOpen = explode(':',$bVals['close_end']);
			$valsCloseFormatted = $bVals['close_start'].':00';
			$valsReOpenFormatted = $bVals['close_end'].':00';
			//create keys for comparing
			$close_key = (int)($xValsClose[0] . $xValsClose[1] . '00');
			$re_open_key = (int)($xValsOpen[0] . $xValsOpen[1] . '00');

			/**********************************************
				CONDITIONALS
			**********************************************/
			/*
				reopen set before or equal to close, skip
			*/
			if($re_open_key <= $close_key){
				continue;
			}
			/*
				close or reopen keys are outside main open close, skip
			*/
			if( $close_key <= $std['open'] || $close_key >= $std['close'] || $re_open_key <= $std['open'] || $re_open_key >= $std['close']){
				continue;
			}

			/*
				compare this newly to be added break to all already added closing/break times,
				making sure it makes sense (i.e opening and/or closing times do not overlap)
			*/
			foreach($dailyBreaksValidate as $brkVals){
				if(
					($close_key >= $brkVals['close'] && $close_key <= $brkVals['open'])
					||
					($re_open_key >= $brkVals['close'] && $re_open_key <= $brkVals['open'])
				){
					continue 2;
				}
			}
			/*
				capture open/close for comparison to check that following
				daily breaks do not overlap with already existing ones
			*/
			$dailyBreaksValidate[] = array(
				'close' => $close_key,
				'open' => $re_open_key,
			);


			/**************************************
				adding close and reopen to daily
			*************************************/
			$daily[$weekDay][$close_key] = array(
				'key' => $close_key,
				'type' => 'close',
				'ts' => $valsCloseFormatted,
				'priority' => 0,
			);
			$daily[$weekDay][$re_open_key] = array(
				'key' => $re_open_key,
				'type' => 'open',
				'ts' => $valsReOpenFormatted,
				'priority' => 0,
			);

			/*************************************
				sort by open/closing keys (times)
			*************************************/
			ksort($daily[$weekDay]);

		}}

		/************************************************************************************************************************************************************************
		#																																										#
		#																																										#
		#	CUSTOM DATES																																						#
		#																																										#
		#																																										#
		************************************************************************************************************************************************************************/
		$custom = array();
		if(!empty($customdates)){
		foreach($customdates as $key => $vals){

			/******************************************************************
				OPENING TIME DATE
				truncated to m-d to account for repeating yearly
			******************************************************************/
			$dateTs = empty($vals['repeat_yearly']) ? $vals['date'] : substr($vals['date'],5);

			//split by : to get opening /closing hours and minutes
			$xValsOpen = explode(':',$vals['open']);
			$xValsClose = explode(':',$vals['close']);
			//format time including seconds
			$valsOpenFormatted = $vals['open'].':00';
			$valsCloseFormatted = $vals['close'].':00';
			//create keys for comparing
			$open_key = (int)($xValsOpen[0] . $xValsOpen[1] . '00');
			$close_key = (int)($xValsClose[0] . $xValsClose[1] . '00');
			$close_next_day = false;
			/****************************************************************
				crossing midnight, alter open / close key
			****************************************************************/
			if($open_key > $close_key){
				$close_key = (int)(((int)$xValsClose[0]+24) . $xValsClose[1] . '00');
				$close_next_day = true;
			}

			/******************************************************************
				if not set to repeating every year and closing has
				already passed current WP time, just skip
			******************************************************************/
			if(empty($vals['repeat_yearly'])){

				$cDateCloseTS = strtotime($vals['date'].' '.$vals['close'].':00');

				if($close_next_day){
					$cDateCloseTS = strtotime('+1 day', $cDateCloseTS);
				}

				if($cDateCloseTS <= WPPIZZA_WP_TIME){
					continue;
				}
			}


			/******************************************************************
				ONLY APPLYING TO A SPECIFIC YEAR, else zero/false/-1 (or something)
			******************************************************************/
			$year = empty($vals['repeat_yearly']) ? -1 : substr($vals['date'],0 , 4);
			$priority = empty($vals['repeat_yearly']) ? 2 : 1 ;	//make distinctly set dates for a specific year have priority (higher integer) over repeating ones

			/******************************************************************
				CLOSED ALL DAY - SET SEPARATELY -
				TO OVERRIDE STANDARD OPENING HOURS FOR EACH SPECIFIC DATE
			******************************************************************/
			if($open_key == $close_key){
				$custom[$dateTs]['closed'] = array(
					'year' => $year,
					'priority' => $priority,
				);
			continue;
			}

			//open
			$custom[$dateTs][$open_key] = array(
				'key' => $open_key,
				'type' => 'open',
				'ts' => $valsOpenFormatted,
				'priority' => $priority,
			);
			//close
			$custom[$dateTs][$close_key] = array(
				'key' => $close_key,
				'type' => 'close',
				'ts' => $valsCloseFormatted,
				'priority' => $priority,
				'next_day' => $close_next_day,
			);

		}}

		/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*
		#	CUSTOM OPENING TIMES - ADDING BREAKS
		#	getting closing times to merge into custom opening times
		*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*/
		if(!empty($breaks) && !empty($custom)){
		foreach($custom as $dateTs => $vals){

			/*********************************************
				custom day set to be closed entirely, skip
			*********************************************/
			if(!empty($vals['closed'])){
				continue;
			}

			/*********************************************
				main opening /closing for this custom date,
				for ease of use
			*********************************************/
			$std = array(
				'open' => reset($vals)['key'],
				'close' => end($vals)['key'],
				'priority' => end($vals)['priority'],
			);

			/*********************************************
				loop through each break set
			*********************************************/
			$customBreaksValidate = array();
			foreach($breaks as $bKey => $bVals){

				/*
					only process breaks for custom days here
				*/
				if($bVals['day'] != -1){
					continue;
				}

				//split by : to get opening /closing hours and minutes
				$xValsClose = explode(':',$bVals['close_start']);
				$xValsOpen = explode(':',$bVals['close_end']);
				$valsCloseFormatted = $bVals['close_start'].':00';
				$valsReOpenFormatted = $bVals['close_end'].':00';
				//create keys for comparing
				$close_key = (int)($xValsClose[0] . $xValsClose[1] . '00');
				$re_open_key = (int)($xValsOpen[0] . $xValsOpen[1] . '00');

				/**********************************************
					CONDITIONALS
				**********************************************/
				/*
					reopen set before or equal to close, skip
				*/
				if($re_open_key <= $close_key){
					continue;
				}
				/*
					close or reopen keys are outside main open close, skip
				*/
				if( $close_key <= $std['open'] || $close_key >= $std['close'] || $re_open_key <= $std['open'] || $re_open_key >= $std['close']){
					continue;
				}
				/*
					compare this newly to be added break to all already added closing/break times,
					making sure it makes sense (i.e opening and/or closing times do not overlap)
				*/
				foreach($customBreaksValidate as $brkVals){
					if(
						($close_key >= $brkVals['close'] && $close_key <= $brkVals['open'])
						||
						($re_open_key >= $brkVals['close'] && $re_open_key <= $brkVals['open'])
					){
						continue 2;
					}
				}
				/*
					capture open/close for comparison to check that following
					daily breaks do not overlap with already existing ones
				*/
				$customBreaksValidate[] = array(
					'close' => $close_key,
					'open' => $re_open_key,
				);

				/**************************************
					adding close and reopen to daily
				*************************************/
				$custom[$dateTs][$close_key] = array(
					'key' => $close_key,
					'type' => 'close',
					'ts' => $valsCloseFormatted,
					'priority' => $std['priority'],
				);
				$custom[$dateTs][$re_open_key] = array(
					'key' => $re_open_key,
					'type' => 'open',
					'ts' => $valsReOpenFormatted,
					'priority' => $std['priority'],
				);

				/*************************************
					sort by open/closing keys (times)
				*************************************/
				ksort($custom[$dateTs]);
			}

		}}
		/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*
		#
		#	set as static variable split into
		#	standard opening times (per weekday) and
		#	custom opening/closing times per specific date
		#
		*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*\/*/
		$openingtimes = array(
			'standard' => $daily,
			'custom' => $custom,
		);
		//tidyup
		unset($daily);
		unset($custom);
	}
	/****************************************************************************************
	#	END STATIC																			#
	****************************************************************************************/

return $openingtimes;
}

/****************************************************************************
	get next opening / closing time as well as current status of shop
	(i.e currently open or closed)
	@since 3.13.2
	@return array()
****************************************************************************/
function wppizza_get_shop_status(){
	/* once will do */
	static $shop_status = null;
	if($shop_status === null){
		/*
			only get get next opening / closing time
			as well as current open/closed status of shop
			using wppizza_get_openingtimes() with relevant args
		*/
		$args = array( 'current' => true );
		$shop_status = wppizza_get_openingtimes($args);
	}
return $shop_status;
}
/****************************************************************************
	- $args = array( 'current' => true );

		get next opening / closing time as well as current status of shop
		(i.e currently open or closed)
		as , in theory at least , a shop might be closed for several days - or even weeks/months
		we loop though a whole year (anything more than that is just silly)
		, but break out of that loop as soon as we have the first opening/closing time

	- $args = array( 'days' => 123 );

		get opening/closing periods for the next x days


	@since 3.13.2
	@param int ( how many days in advance max)
	@param bool ( current status only )
	@return array()
****************************************************************************/
function wppizza_get_openingtimes( $args = array()){

	/*
		param: $args['days']

		if getting the next opening/closing
		look at the next 365 day max and break out
		of loop as soon as we can, else use the 'days' set
	*/
	$forward_looking_days = !empty($args['current']) ? 365 : ( !empty($args['days'])  ? (int)$args['days'] : 7 );

	/*
		param: $args['current']
		bool - only return current shop open/close status and next openclose timestamp
	*/
	$get_current_state = !empty($args['current']) ? true :  false;


	/*
		get the formatted opening times according to
		WP Admin : "WPPizza-> Openingtimes" settings
	*/
	$openingtimes = wppizza_openingtimes_formatted();

	/*
		current wp time
	*/
	$tscurrent = WPPIZZA_WP_TIME;

	/*
		ini array
	*/
	$shop_opening_days = array();


	/*
		in case there are overlaps
		and the later one should take precendence over the earlier on
		we should get at least the first 2
	*/
	$period_count = 0;


	/*
		loop through days beginning from now up to max or if sommething is found
		HOWEVER, start at -1 days here to also capture closing times that cross
		midnight from the previous day
	*/
	$period_counter = 0;
	$dt = true;//adding full date time readable timstamp to array
	for($i = -1; $i <= $forward_looking_days; $i++){

		/*
			timestamp now + i days
		*/
		$day_ts = ($i == 0 ) ? $tscurrent : strtotime('+'.$i.' day', $tscurrent);
		/*
			get y-m-d date
		*/
		$date = date('Y-m-d', $day_ts);
		/*
			get m-d date (for repeating custom days)
		*/
		$month_day = substr($date, 5);
		/*
			get year (for non-repeating custom days)
		*/
		$year = substr($date, 0, 4);
		/*
			get weekday
		*/
		$weekday = date('w', $day_ts);

		/****************************************************************************
		#																			#
		#	LOOKING FOR DISTINCTLY SET CUSTOM DATES - INCLUDING YEAR - FIRST OF ALL	#
		#																			#
		****************************************************************************/
		if(isset($openingtimes['custom'][$date])){
			$day_opening_times = $openingtimes['custom'][$date];
		}
		/****************************************************************************
		#																			#
		#	LOOKING FOR DISTINCTLY SET CUSTOM DATES - WITHOUT YEAR 					#
		#																			#
		****************************************************************************/
		elseif(isset($openingtimes['custom'][$month_day])){
			$day_opening_times = $openingtimes['custom'][$month_day];
		}
		/****************************************************************************
		#																			#
		#	REGULAR WEEKDAY IF NONE OF THE ABOVE APPLY								#
		#																			#
		****************************************************************************/
		else{
			$day_opening_times = $openingtimes['standard'][$weekday];
		}


		/******************
			set periods
		*******************/
		$shop_opening_days[$date] = array();
		foreach($day_opening_times as $tsKey => $vals){

			/***********
				open
			************/
			if($vals['type'] == 'open'){

				/*
					full timestamp from date and time
				*/
				$ts = strtotime($date.' '.$vals['ts']);
				//init array
				$shop_opening_days[$date][$period_counter] = array();
				$shop_opening_days[$date][$period_counter]['open'] = $ts;
				if(!empty($dt)){
				$shop_opening_days[$date][$period_counter]['open_dt'] = date('Y-m-d H:i:s',$ts);
				}

			}
			/***********
				close
			************/
			if($vals['type'] == 'close'){
				/*
					full timestamp from date and time
					advance by a day i next day
				*/
				$ts = strtotime($date.' '.$vals['ts']);
				if(!empty($vals['next_day'])){
					$ts = strtotime('+1 day', $ts);
				}

				/*
					skip and unset all open too for this period
					if closing time has already passed now
				*/
				if($ts < $tscurrent){
					unset($shop_opening_days[$date][$period_counter]);
					continue;
				}

				$shop_opening_days[$date][$period_counter]['close'] = $ts ;
				if(!empty($dt)){
				$shop_opening_days[$date][$period_counter]['close_dt'] = date('Y-m-d H:i:s',$ts);
				}
				if(isset($vals['priority'])){
				$shop_opening_days[$date][$period_counter]['priority'] = $vals['priority'];
				}

			/*
				advance counter after close for next open period
			*/
			$period_counter++;
			}
		}

		/*********************************************
			just remove entirely if it's empty now
		*********************************************/
		if(isset($shop_opening_days[$date])  && empty($shop_opening_days[$date])){
			unset($shop_opening_days[$date]);
		}

		/*
			IF WE ONLY WANT TO GET THE CURRENT STATE
			BREAK OUT OF LOOP AS SOON AS WE HAVE THE FIRST 2(!) OPEN/CLOSE PERIODS
		*/
		if(!empty($get_current_state) && !empty($shop_opening_days[$date])){
			$period_count += count($shop_opening_days[$date]);
			if($period_count >= 2 ){
				BREAK;
			}
		}
	}

	/***************************************************************************************************
	#																									#
	#	SET FINAL OPEN / CLOSE TIMES IN ORDER															#
	#	WITH SANITY CHECKS																				#
	#	(TO TRY TO ACCONT FOR NON_SENSICAL SETTINGS 													#
	#																									#
	****************************************************************************************************/
	$shop_opening_times = array();
	foreach($shop_opening_days as $date => $ocPeriods){
		foreach($ocPeriods as $ocKey => $ocTimes){

			/*
				add to array
			*/
			$shop_opening_times[$ocKey] = $ocTimes;

			/*****************************************
			#
			#	SANITY CHECKS FOR NON SENSICAL OVERLAPS
			#
			*****************************************/
			/*
				simply ignore check for the very first
				open/close period
			*/
			if(isset($previousPeriodKey) && isset($previousPeriod) ){

				/*
					if THIS open is equal or before PREVIOUS close
				*/
				if( $ocTimes['open'] <= $previousPeriod['close'] ){


					/*
						decide by priority - if necessary - which opening / closing
						period should take precedence. By default later will overwrite
						earlier.

						Note:
						distinct full custom dates (not repeating yearly) will have precedence over repeating custom dates,
						all custom dates will have precendence over regular dates
					*/
					$priority = 'current';
					if($ocTimes['priority'] != $previousPeriod['priority'] && $previousPeriod['priority'] > $ocTimes['priority'] ){
					$priority = 'previous';
					}


					/*
						if current takes precendence over previous
					*/
					if($priority == 'current'){
						// - set PREVIOUS open as THIS open	(and just keep THIS close as is)
						$shop_opening_times[$ocKey]['open_dt'] = $previousPeriod['open_dt'];
						$shop_opening_times[$ocKey]['open'] = $previousPeriod['open'];

						// - UNSET previous open/close
						unset($shop_opening_times[$previousPeriodKey]);
					}

					/*
						if previous takes precendence over current
					*/
					if($priority == 'previous'){
						/*
							simply unset current and continue without updateing
							previousPeriodKey and previousPeriod
						*/
						unset($shop_opening_times[$ocKey]);

					continue;
					}
				}
			}
			/**
				capture last (open/close) period
				to be able to deal with any overlaps
			**/
			$previousPeriodKey = $ocKey;
			$previousPeriod = $ocTimes;

		}
	}

	/***********************************************************/
	#
	#	ONLY GETTING NEXT OPEN CLOSE AND CURRENT SHOP STATE
	#
	/***********************************************************/
	if($get_current_state){
		/*
			get next opening/closing
		*/
		$shop_opening_times = reset($shop_opening_times);

		/*
			current/next state and ts
		*/
		$current_state = ($tscurrent >= $shop_opening_times['open']) ?  'is_open' : 'is_closed';
		$next_state = ($tscurrent >= $shop_opening_times['open']) ?  'close' : 'open';
		$next_state_timestamp = ($tscurrent >= $shop_opening_times['open']) ?  $shop_opening_times['close'] : $shop_opening_times['open'];
		$next_state_timestamp_dt = ($tscurrent >= $shop_opening_times['open']) ?  $shop_opening_times['close_dt'] : $shop_opening_times['open_dt'];

		/*
			return array
		*/
		$shop_state = array(
			'current' => $current_state,
			'next' => array(
				'type' => $next_state,
				'ts' => $next_state_timestamp,
				'dt' => $next_state_timestamp_dt,
			),
		);

	return $shop_state;
	}

	/***********************************************************************/
	#
	#	OPENINGTIMES FOR THE NEXT X DAYS IF NOT JUST GETTING CURRENT STATE
	#
	/***********************************************************************/
	return $shop_opening_times;
}
?>