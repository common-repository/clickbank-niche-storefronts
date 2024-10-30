<?php

/*
Plugin Name: Clickbank Niche Storefronts
Description: This plugin allows users to add a Clickbank Niche Storefront as a POST or a PAGE.
Author: CBproAds.com
Version: 1.3.5
Author URI: http://www.cbproads.com/
*/

$GLOBALS['cns_plugin_version'] = '1.3.5';

$GLOBALS['cns_available_tags'] = array('h1', 'h2', 'h3', 'strong');
$GLOBALS['cns_options'] = array(
    'cns_user_id' => array(
        'req' => true,
        'type' => 'int',
        'label' => 'Your CBproAds Account ID',
        'default' => '15750',
    ),
    'cns_niche' => array(
        'req' => true,
        'type' => 'text',
        'label' => 'Niche',
        'default' => 'weightloss',
    ),
    'cns_show_price' => array(
        'req' => true,
        'type' => 'text',
        'label' => 'Show Price',
        'default' => 'yes', // 0 or 1
    ),
    'cns_items_per_page' => array(
        'req' => true,
        'type' => 'int',
        'label' => 'Items per Page',
        'default' => '12',
    ),
	'cns_columns' => array(
        'req' => true,
        'type' => 'int',
        'label' => 'Columns',
        'default' => '4',
    ),
);


$GLOBALS['cns_ad_options'] = array( // Advanced options
    'cns_title_tag' => array(
        'req' => true,
        'type' => 'select',
        'vals' => $GLOBALS['cns_available_tags'],
        'label' => 'Product Title HTML Tag',
        'default' => 'h3',
    ),
    'cns_title_style' => array(
        'req' => false,
        'type' => 'text',
        'label' => 'Product Title CSS Style',
        'default' => 'padding-bottom: 1em;',
    ),
    'cns_subtitle_tag' => array(
        'req' => true,
        'type' => 'select',
        'vals' => $GLOBALS['cns_available_tags'],
        'label' => 'Product Sub-title HTML Tag',
        'default' => 'strong',
    ),
    'cns_subtitle_style' => array(
        'req' => false,
        'type' => 'text',
        'label' => 'Product Sub-title CSS Style',
        'default' => 'font-size: 120%; padding-bottom: 1em;',
    ),
);
$GLOBALS['cns_option_names'] = array_merge(
    array_keys($GLOBALS['cns_options']), array_keys($GLOBALS['cns_ad_options']));

require_once 'functions.inc.php'; // Defines all functions


if (!session_id()) session_start();

$_SESSION['cns_plugin_url'] = plugins_url('', __FILE__);
foreach ($GLOBALS['cns_option_names'] as $o) {
    $_SESSION[$o] = get_option($o);
    //var_dump($_SESSION[$o]);
    //var_dump(get_option($o));
}

//session_write_close();


register_activation_hook(__FILE__, 'cns_activate');
register_deactivation_hook(__FILE__, 'cns_deactivate');
if (is_admin()) {
    add_action('admin_menu', 'cns_add_to_menu');
    if ( isset($_GET['page']) &&  trim($_GET['page']) == 'cns_menu') {
        add_action('admin_head', 'cns_option_js');
    }
} else {
	/*
    if (isset($_GET['id']) && ($_GET['id'] = trim($_GET['id'])) != '') {
        setcookie('cns_user_id', $_GET['id'], time()+60*60*24*365, '/', cns_get_site_domain());
        $_SESSION['cns_user_id'] = $_COOKIE['cns_user_id'] = $_GET['id'];
    } elseif (isset($_COOKIE['cns_user_id'])) {
        $_SESSION['cns_user_id'] = $_COOKIE['cns_user_id'];
    }
    
    if (isset($_GET['tid']) && ($_GET['tid'] = trim($_GET['tid'])) != '') {
        setcookie('cns_tid', $_GET['tid'], time()+60*60*24*365, '/', cns_get_site_domain());
        $_SESSION['cns_tid'] = $_COOKIE['cns_tid'] = $_GET['tid'];
    } elseif (isset($_COOKIE['cns_tid'])) {
        $_SESSION['cns_tid'] = $_COOKIE['cns_tid'];
    }
	*/
}

add_shortcode('clickbank-niche-storefront', 'cns_show_filter');
if ($_SESSION['cns_show_storefront_after_posts'] != 'no') {
    add_filter('the_posts', 'cns_show_products');
}

// Add settings link on plugin page
$plugin = plugin_basename(__FILE__); 
$prefix = is_network_admin() ? 'network_admin_' : '';
add_filter("{$prefix}plugin_action_links_$plugin", 'cns_settings_link' );
//use this if any roblem comesadd_filter
//add_filter("{$prefix}plugin_action_links_clickbank-niche-storefronts/clickbank-niche-storefronts.php", 'cns_settings_link' );



add_action( 'wp_ajax_cs_niche_pagination_ajax_request', 'cns_show_pages' ); 
add_action("wp_ajax_nopriv_cs_niche_pagination_ajax_request", "cns_show_pages");
add_action('wp_enqueue_scripts', 'cs_niche_script_enqueue');


function cns_show_pages() {

	// verify the nonce as part of security measures
   	if ( !isset($_POST['cbpro_niche_nonce']) || !wp_verify_nonce( $_POST['cbpro_niche_nonce'], "local_cbpro_niche_nonce")) {
      			//die ("No naughty business please");
  	} 
     

    if (!session_id()) session_start();

	if (isset($_GET['sortby'])) $_SESSION['cs_sortby'] = $_GET['sortby'];
	if (isset($_GET['switch_view'])) $_SESSION['cs_switch_view'] = $_GET['switch_view'];

	$output = cns_show($_GET['user_id'], $_GET['niche'],$_GET['page'] );
	//$output = cns_show($_GET['user_id'], $_GET['niche'],2 );
	echo $output;
	//echo $output['output'];

    // Always die in functions echoing AJAX content
   die();
}


function cs_niche_script_enqueue()
{
	
	wp_register_style('cns_stylesheet', $_SESSION['cns_plugin_url'].'/style.css?version='.$GLOBALS['cns_plugin_version']);
    wp_enqueue_style('cns_stylesheet');
    wp_enqueue_script(
        'cbpro_niche_main_script',
        $_SESSION['cns_plugin_url'].'/init.js'
        . '?plugin_url='.htmlspecialchars($_SESSION['cns_plugin_url']),
        array('jquery'),
        $GLOBALS['cns_plugin_version']);
	
			wp_localize_script( 'cbpro_niche_main_script', 'cbpro_niche_paging_ajax_object',
            array( 'global_cbpro_niche_nonce' => wp_create_nonce('local_cbpro_niche_nonce'), 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
			wp_enqueue_script('cbpro_niche_main_script');

}
// Add settings link on plugin page
function cns_settings_link($links)
{
    $settings_link = '<a href="options-general.php?page=cns_menu">Settings</a>';
    array_unshift($links, $settings_link);
    
    return $links;
}




//add_filter('plugin_action_links_clickbank-niche-storefronts/clickbank-niche-storefronts.php', 'cns_settings_link');

?>