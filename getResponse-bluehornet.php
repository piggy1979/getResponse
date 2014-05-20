<?php

/*
Plugin Name: Get Response Mailer Subscribe
Plugin URI: http://makeone.ca
Description: Setup Default Settings for Get Response.
Author: Phil Neal
Author URI: http://makeone.ca
Version: 1.0
*/


if(!class_exists('Impact_Getresponse'))
{

class Impact_Getresponse{
	

	public $options;

	//private $options;

	public function __construct()
	{
		add_action('admin_menu', array($this, 'init_options'));
		// create admin page.
		add_action( 'admin_init', array( $this, 'page_init' ) );
	//	add_action('init', array($this, 'pushMobilecon'));
	}

	public function pushGetResponse(){
			$this->options[api_key] 		= get_option('impact_getresponse_api_key');
			$this->options[shared_secret]	= get_option('impact_getresponse_shared_secret');
			$this->options[api_url]			= get_option('impact_getresponse_api_url');
			$this->options[grp]				= get_option('impact_getresponse_grp');	
			$this->options[email]			= $this->_clean($_REQUEST['email']);
			$this->options[firstname]		= $this->_clean($_REQUEST['firstname']);
			$this->options[lastname]		= $this->_clean($_REQUEST['lastname']);


			if (!filter_var($this->options[email], FILTER_VALIDATE_EMAIL)) {
				return false;
			}

			$xmlblock = "<api><authentication><api_key>".$this->options[api_key]."</api_key><shared_secret>".$this->options[shared_secret]."</shared_secret><response_type>xml</response_type></authentication><data><methodCall><methodName>legacy.manage_subscriber</methodName><grp>".$this->options[grp]."</grp><email>".$this->options[email]."</email><firstname>".$this->options[firstname]."</firstname><lastname>".$this->options[lastname]."</lastname></methodCall></data></api>";

			$headers[] = 'Content-Type: application/x-www-form-urlencoded';
			$curl = curl_init($this->options[api_url]);
    		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
   			curl_setopt($curl, CURLOPT_POST, true);
    		curl_setopt($curl, CURLOPT_POSTFIELDS, "data=".$xmlblock);
    		curl_setopt($curl, CURLOPT_HTTPHEADER, array($headers));
    		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);                             
      		$curl_response = curl_exec($curl);


    		curl_close($curl);

    		$p = xml_parser_create();
    		xml_parse_into_struct($p, $curl_response, $xmlvals, $xmlindex);
			xml_parser_free($p);
		//	print_r($xmlindex);
			if($xmlindex[ERROR]){
				return false;
			}else{
				return true;
			}

	}

	public function createGetResponseForm(){

		if($_POST['verifyid']){
			$nonce = $_REQUEST['verifyid'];
			if ( ! wp_verify_nonce( $nonce, 'impact-custom-7389' ) ) {
    			// This nonce is not valid.
    			die( 'Security check' ); 
			} else {
				if($this->pushBluehornet()){
					echo "<h2>Successfully Added</h2>\n";
					echo "<p>You have been successfully added to our mailing list. Thank you!</p>\n";
				}else{
					echo "<h2>Unknown Error</h2>\n";
					echo "<p>We have experienced a problem. Hopefully its just a hiccup in the system. Please try again by clicking back on your browser.</p>\n";
				}
		}

		}

		$emailpost;
		if($_POST['email']){
			$emailpost = $this->_clean($_POST['email']);
		}

		if($_POST['firstname'] && $_POST['lastname']){
			return;
		}

		$nonce = wp_create_nonce( 'impact-custom-7389' );
		?>
		
		<form action="/newsletter" method="post" class="bluehornet formelement">
			<div class="gform_body">
			<h2 class="legend"><span>Personalize Your Account</span></h2>
			<fieldset>
				<p>Get access to exclusive content, free resources, research and so much more!</p>
			<ul>
			<li class="gfield gform-name"><input type="hidden" name="verifyid" value="<?php echo $nonce; ?>">
			<label class="gfield_label">First Name<span class="gfield_required">*</span></label><input class="input short" type="text" name="firstname" value="<?php echo $_POST['firstname'] ?>" placeholder="First Name"></li>
			<li class="gfield gform-name"><label class="gfield_label">Last Name<span class="gfield_required">*</span></label><input class="input short" type="text" name="lastname" value="<?php echo $_POST['lastname'] ?>" placeholder="Last Name"></li>
			<li class="gfield gform-name"><label class="gfield_label">Email<span class="gfield_required">*</span></label><input class="input" type="text" name="email" value="<?php echo $emailpost; ?>" placeholder="Enter Email"></li>
			</ul>
			</fieldset>
			<input class="submit button" type="submit" name="submit" value="Sign up for our Newsletter">
			</div>
		</form>
		<?php
		
	}

	public function createBluehornetMiniForm()
	{
	$nonce = wp_create_nonce( 'impact-custom-7389' );
	?>
		<form action="/newsletter" method="post" class="bluehornetmini span4">
			<div>
			<input type="hidden" name="verifyid" value="<?php echo $nonce; ?>">
			<input class="input" type="text" name="email" value="" placeholder="Enter Email Address for Updates">
			<input class="submit" type="submit" name="submit" value="Submit">	
		</div>
		</form>
	<?php
	}


	public function init_options(){

		add_options_page( 'Blue Hornet Settings', 'Blue Hornet Settings', 'manage_options', 'bluehornet_options', array($this, 'verb_bluehornet_options') );
	}

	public function verb_bluehornet_options()
	{
		if(!current_user_can('manage_options'))
		{
			wp_die(__('You Account does not have permission.'));
		}

		//$options = get_option("verb_mobilecon_option");

		?>
			
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2>Blue Hornet Settings</h2>

			<form method="post" action="options.php">
			<?php
				@settings_fields('impact_getresponse_group');
				@do_settings_fields('impact_getresponse_group');
				do_settings_sections('bluehornet_options');
				@submit_button();
			?>
			</form>
		</div>
		<?php

	}


	/* settings page */

	public function page_init()
	{
		register_setting('impact_getresponse_group', 'impact_getresponse_api_key');
		register_setting('impact_getresponse_group', 'impact_getresponse_shared_secret');
		register_setting('impact_getresponse_group', 'impact_getresponse_api_url');
		register_setting('impact_getresponse_group', 'impact_getresponse_grp');

		add_settings_section(
			'impact_getresponse-section',
			'Bluehornet Settings',
			array($this, 'impact_getresponse_formheader'),
			'bluehornet_options'
		);
        add_settings_field(
            'impact_getresponse_api_key-setting', // ID
            'API Key', // Title 
            array( $this, 'output_api_key' ), // Callback
            'bluehornet_options', // Page
            'impact_getresponse-section' // Section           
        ); 
        add_settings_field(
        	'impact_getresponse_shared_secret-setting',
        	'Shared Secret',
        	array($this, 'output_shared_key'),
        	'bluehornet_options',
        	'impact_getresponse-section'
        );
        add_settings_field(
            'impact_getresponse_api_url-setting', // ID
            'API URL', // Title 
            array( $this, 'output_api_url' ), // Callback
            'bluehornet_options', // Page
            'impact_getresponse-section' // Section           
        ); 
        add_settings_field(
            'impact_getresponse_grp-setting', // ID
            'Group ID', // Title 
            array( $this, 'output_grp' ), // Callback
            'bluehornet_options', // Page
            'impact_getresponse-section' // Section           
        ); 


	}
	public function impact_getresponse_formheader()
	{
		echo "Enter your Blue Hornet details here.";
	}

	/* will group these in next interation so its just one method */
	public function output_api_key()
	{
		$value = get_option('impact_getresponse_api_key');
		printf('<input type="text" id="api_key" class="regular-text" name="impact_getresponse_api_key" value="%s" />', $value);
	}
	public function output_shared_key()
	{
		$value = get_option('impact_getresponse_shared_secret');
		printf('<input type="text" id="shared_secret" class="regular-text" name="impact_getresponse_shared_secret" value="%s" />', $value);
	}
		public function output_api_url()
	{
		$value = get_option('impact_getresponse_api_url');
		printf('<input type="text" id="api_url" class="regular-text" name="impact_getresponse_api_url" value="%s" />', $value);
	}
		public function output_grp()
	{
		$value = get_option('impact_getresponse_grp');
		printf('<input type="text" id="api_grp" class="regular-text" name="impact_getresponse_grp" value="%s" />', $value);
	}


	public static function activate()
	{

	}

	public static function deactivate()
	{

	}


	public function _clean($str){ 
    	return is_array($str) ? array_map('_clean', $str) : str_replace("\\", "\\\\", htmlspecialchars((get_magic_quotes_gpc() ? stripslashes($str) : $str), ENT_QUOTES)); 
	}

} // end of class

} // end if class exists

if(class_exists('Impact_Getresponse'))
{
	register_activation_hook(__FILE__, array('Impact_Getresponse', 'activate') );
	register_deactivation_hook( __FILE__, array('Impact_Getresponse', 'deactivate') );
	$impact_getresponse = new Impact_Getresponse();


	// add link to settings on the plugin page.
	//
	if(isset($impact_getresponse))
	{
		function getresponse_settings_link($links)
		{
			$settings_link = '<a href="options-general.php?page=getresponse_options">Settings</a>';
			array_unshift($links, $settings_link);
			return $links;
		}
		$plugin = plugin_basename(__FILE__);
		add_filter("plugin_action_links_{$plugin}", 'getresponse_settings_link');

	}
}


#EOF