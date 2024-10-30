<?php
/**
Plugin Name:  Install Google AdWords Codes on WooCommerce Plugin
Plugin URI:   http://www.storeya.com
Description:  The ultimate Woocommerce plugin for Google AdWords advertising - embedding Conversion Tracking and Remarketing codes for you! 
Author:       StoreYa
Author URI:   http://www.storeya.com
Version:      1.0.1
License:      GPLv2 or later
Text Domain:  install-google-adwords-codes-tag
**/


if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}


class WGACT{
	
	public function __construct(){		
		function cdata_markupfix($content) { $content = str_replace("]]&gt;", "]]>", $content); return $content; }
		function cdata_template_redirect( $content ) { ob_start('cdata_markupfix'); }
		
		add_action( 'wp_head', array( $this, 'GoogleAdscript' ));
		
		add_action( 'woocommerce_thankyou', array( $this, 'GoogleAdWordsTag' ));
		
		add_action('admin_menu', array( $this, 'wgact_plugin_admin_add_page'),99);

		add_action('admin_init', array( $this, 'wgact_plugin_admin_init'));
		
		add_filter('plugin_action_links', array($this, 'wgact_settings_link'), 10, 2);
		
		add_action('init', array($this, 'load_plugin_textdomain'));		
	}
	
	public function load_plugin_textdomain(){
		load_plugin_textdomain('install-google-adwords-codes-tag', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
	}
	
	function wgact_settings_link($links, $file) {
		if ($file == plugin_basename(__FILE__))
			$links[] = '<a href="' . admin_url("admin.php?page=do_wgact") . '">'. __('Settings') .'</a>';
		return $links;
	}
	
	function wgact_plugin_admin_add_page() {
		add_submenu_page('woocommerce', esc_html__( 'Install Google AdWords Codes', 'install-google-adwords-codes-tag' ), esc_html__( 'Install Google AdWords Codes', 'install-google-adwords-codes-tag' ), 'manage_options', 'do_wgact', array($this, 'wgact_plugin_options_page'));
	}

	function wgact_plugin_options_page() {

		?>

	<br>
	<div style="padding: 0px 20px 0px 20px;">
		<div style="background: #ccc; padding: 10px; font-weight: bold"><?php esc_html_e( 'Install Google AdWords Codes on WooCommerce Settings', 'install-google-adwords-codes-tag' ) ?></div>
		<form action="options.php" method="post">
		
			<?php settings_fields('wgact_plugin_options'); ?>
      <?php settings_fields('wgact_plugin_options_second'); ?>
      
			<?php do_settings_sections('do_wgact'); ?>
		<br>
	 <table class="form-table" style="margin: 10px">
		<tr>
			<th scope="row" style="white-space: nowrap">
				<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" class="button" />
			</th>

	</tr>
	</table>
		</form>
	
		</div>

		

		<?php


	}

	function wgact_plugin_admin_init(){

		register_setting( 'wgact_plugin_options', 'wgact_plugin_options_1');
		add_settings_section('wgact_plugin_main', esc_html__( 'Adwords conversion tracking code', 'install-google-adwords-codes-tag' ), array($this,'wgact_plugin_section_text'), 'do_wgact');
        add_settings_field('wgact_plugin_text_string_1', esc_html__( 'Conversion Tracking Code', 'install-google-adwords-codes-tag' ), array($this,'wgact_plugin_setting_string_1'), 'do_wgact', 'wgact_plugin_main');
        register_setting( 'wgact_plugin_options_second', 'wgact_plugin_options_2');
        add_settings_section('wgact_plugin_main_second', esc_html__( 'How remarketing works', 'install-google-adwords-codes-tag' ), array($this,'wgact_plugin_section_text_second'), 'do_wgact');
		add_settings_field('wgact_plugin_text_string_2', esc_html__( 'Remarketing Code', 'install-google-adwords-codes-tag' ), array($this,'wgact_plugin_setting_string_2'), 'do_wgact', 'wgact_plugin_main_second');
	
	}  

	function wgact_plugin_section_text() {
		echo '<p>Implementing Google AdWords conversion tracking so you would know how effective your ads are; how many of the clicks you are paying for are actually gaining for you sales, installs or whatever you are trying to to reach.</p>';
	}
  
  	function wgact_plugin_section_text_second() {
		echo '<p>Implementing Google AdWords Remarketing Tag, so that you can mark your site’s visitors and show them more ads in the future until they are convinced to purchase your products / services.</p>';
	}

	function wgact_plugin_setting_string_1() {
		$options = get_option('wgact_plugin_options_1');
		echo "<textarea id='wgact_plugin_text_string_1' name='wgact_plugin_options_1[text_string]' rows='10' style='width:100%;' >{$options['text_string']}</textarea>";	
	}

	function wgact_plugin_setting_string_2() {
		$options = get_option('wgact_plugin_options_2');
		echo "<textarea id='wgact_plugin_text_string_2' name='wgact_plugin_options_2[text_string]' rows='10' style='width:100%;' >{$options['text_string']}</textarea>";	
    
	}

	function wgact_plugin_options_validate($input) {
		$newinput['text_string'] = trim($input['text_string']);
		if(!preg_match('/^[a-z0-9]{32}$/i', $newinput['text_string'])) {
			$newinput['text_string'] = '';
		}
		return $newinput;
	}
	
	private function get_conversion_id(){
		$opt = get_option('wgact_plugin_options_1');
		$conversion_id = $opt['text_string'];
		return $conversion_id;
	}


	private function get_mc_prefix(){
		$opt = get_option('wgact_plugin_options_2');
		$mc_prefix = $opt['text_string'];
		return $mc_prefix;
	}
	

public function GoogleAdscript($order_id) {

$mc_prefix = $this->get_mc_prefix();
echo $mc_prefix;
    
}


	public function GoogleAdWordsTag($order_id) {

		$conversion_id    = $this->get_conversion_id();
		$mc_prefix        = $this->get_mc_prefix();
		$order = new WC_Order( $order_id );
		$order_total = $order->get_total();
		
    if ( !$order->has_status( 'failed' ) && ((get_post_meta( $order_id, '_WGACT_conversion_pixel_fired' , true) != "true"))){
	?>
    
<?php $conversion_id;
$currency = $order->get_order_currency();

$string1 = 'google_conversion_value = '.$order_total.';';
$string2 = 'google_conversion_currency = "'.$currency.'";';
$string3 = 'value='.$order_total;
$string4 = 'currency_code='.$currency;


$conversion_id = str_replace("google_conversion_value = 1.00;",$string1,$conversion_id);
$conversion_id = str_replace("value=1.00",$string3,$conversion_id);


$pos = strpos($conversion_id, 'google_conversion_currency');
$endpos = strpos($conversion_id, 'var google_remarketing_only');
$minuspos = strlen($conversion_id)-$endpos;


$conversion_id = substr_replace($conversion_id, $string2, $pos, '-'.$minuspos);


$pos2 = strpos($conversion_id, 'currency_code=');
$endpos2 = strpos($conversion_id, '&amp;label=');
$minuspos2 = strlen($conversion_id)-$endpos2;


$conversion_id = substr_replace($conversion_id, $string4, $pos2, '-'.$minuspos2);


    
    echo $conversion_id; ?>
	
	<!-- END Google Code for Sales (AdWords) Conversion Page -->

	<?php 
			update_post_meta( $order_id, '_WGACT_conversion_pixel_fired', 'true' );
		} 
	}
}

$wgact = new WGACT();

?>
