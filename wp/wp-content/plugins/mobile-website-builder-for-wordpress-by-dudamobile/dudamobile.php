<?php
/*
	Plugin Name: Mobile Website Builder for WordPress by DudaMobile

	Description: The DudaMobile Wordpress plugin makes it easy to convert your Wordpress website into a mobile-friendly site. It’s fast, free and easy. Works with all Wordpress themes including websites and blogs. 

	Version: 1.0.2

	Author: DudaMobile

	Author URI: http://www.dudamobile.com

	License: GPL2

	@package WordPress
	@since 3.0.1


	Copyright 2012  DudaMobile  (email : info@dudamobile.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA


*/

/**
* Define Post Details Paths and Directories
**/
define("DUDAMOBILE_REDIRECTOR_FILENAME", basename(__FILE__));

$path = $_SERVER['REQUEST_URI'];

$path_length = strpos($path, DUDAMOBILE_REDIRECTOR_FILENAME) + strlen(DUDAMOBILE_REDIRECTOR_FILENAME);
	
$path = substr( $path, 0, strpos($path, '?') ) . '?page=dudamobile';

$img_path = get_option( 'siteurl' ).'/wp-content/plugins/'.basename( dirname(__FILE__) ).'/img';



define( "DUDAMOBILE_REDIRECTOR_ADMIN_PLUGIN_PATH", $path );
	
	if ($IS_WINDOWS) {
	
		$temp = str_replace(DUDAMOBILE_REDIRECTOR_FILENAME, "", __FILE__);
		
		//switch direction of slashes
		$temp = str_replace("\\", "/", $temp);	
	
		define( "DUDAMOBILE_REDIRECTOR_PLUGIN_PATH", $temp );
	
	}

	else 
	{
	
		define( "DUDAMOBILE_REDIRECTOR_PLUGIN_PATH", str_replace(DUDAMOBILE_REDIRECTOR_FILENAME, "", __FILE__) );
	
	}


	if ( ! defined( 'WP_PATH_DIR' ) )
	  define( 'WP_PATH_DIR', ABSOLUTE_PATH );	  
	if ( ! defined( 'WP_CONTENT_DIR' ) )
	  define( 'WP_CONTENT_DIR', ABSOLUTE_PATH . 'wp-content' );
	if ( ! defined( 'WP_PLUGIN_URL' ) )
	  define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
	if ( ! defined( 'WP_PLUGIN_DIR' ) )
	  define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
	  
define( "DUDAMOBILE_REDIRECTOR_PLUGIN_URL", get_option('siteurl' ).'/wp-content/plugins/'.basename(dirname(__FILE__)).'/');



if (!class_exists( 'DudaMobileDetectorPlugin' )) {

	class DudaMobileDetectorPlugin 
	{
		/**
		* Constructor for DudaMobileDetectorPlugin class.
		* Calls action hooks to load options on init
		*/
		function DudaMobileDetectorPlugin() {			
			$this->addActions();
			register_activation_hook(__FILE__, array($this, 'createDudaMobileDetectorPlugin'));
			register_deactivation_hook(__FILE__, array($this, 'removeDudaMobileDetectorPlugin'));
			
		}
		
		/**
		* Various action hooks to initialize the plugin
		*/
		function addActions() {
			add_action('admin_menu', array(&$this, 'addAdminInterfaceItems'));
			add_action('init',array(&$this, 'updateBrowserAgent'));
			add_action('init',array(&$this, 'findMobileUrl'));	
			add_action('admin_init',array(&$this, 'my_plugin_redirect'));
			add_action( 'admin_head', array(&$this, 'duda_mobile_admin_init'));

		}
		
		/**
		* Runs at startup via hook. Enqueus stylesheet, jQuery and Javascript 				* libraries for later use
		*/
		function duda_mobile_admin_init(){
			 wp_enqueue_script( 'jquery' );			
			?>
            <script type="text/javascript" src="<?php echo DUDAMOBILE_REDIRECTOR_PLUGIN_URL?>script.js"></script>
            <?php
    wp_register_style( 'prefix-style', DUDAMOBILE_REDIRECTOR_PLUGIN_URL . 'dudaStyle.css');
        wp_enqueue_style( 'prefix-style' );        
    
		}
		
		/**
		* Checks if redirection is set up. If not, takes user to initial
		* page
		*/
		function my_plugin_redirect(){
			if (get_option('my_plugin_do_activation_redirect', false)) 
			{
				delete_option('my_plugin_do_activation_redirect');
				wp_redirect('admin.php?page=dudamobile');
			}
		}

		/**
		* Function that runs at install. Sets & checks options we need later.
		*/
		function createDudaMobileDetectorPlugin(){
			global $wpdb,$jal_db_version;			
		
			add_option("jal_db_version", $jal_db_version);
			add_option('dudamobile', $newoptions);
			$newoptions = get_option('dudamobile');
			$newoptions['api_url'] = 'http://my.dudamobile.com/api/public/sites/mdomain';
			$newoptions['mobile_url'] = '';
			$newoptions['user_agent'] = '';
			$newoptions['activate_redirect']='Y';
			$newoptions['last_updated'] = time();
			$newoptions['concate_url'] ='ppm';

			
			//looks for mobile URL at Duda at installation
			$returned_content = $this->post_request($newoptions['api_url'], '"value":"'.get_site_url().'"}');
			
			if ( $options != $newoptions ) 
			{
				$options = $newoptions;
				update_option('dudamobile', $options);
			}
			add_option('my_plugin_do_activation_redirect', true);

		
		}
		
		/**
		* Removes the DudaMobile plugin
		*/
		function removeDudaMobileDetectorPlugin(){
			delete_option('dudamobile');
		}
		
		/**
		* Initializes the admin page, menu and icon
		*/
		function addAdminInterfaceItems() {
			$img_path = get_option('siteurl').'/wp-content/plugins/'.basename(dirname(__FILE__)).'/img';
			add_menu_page(__('DudaMobile'), __('DudaMobile'), 'manage_options', 'dudamobile&setting=Y', array(&$this,'dudaMobileSiteCreation'),$img_path.'/icon_mobile.png');

			add_submenu_page('dudamobile',__('DudaMobile'),__('DudaMobile'),'manage_options', 'dudamobile', array(&$this,'dudaMobileSiteCreation'));			

		}
		
		/**
		* Redirects user to mobile site options page if they have confirmed
		* the URL with the YES button. Contains the FAQ, last updated info,
		* redirect options. Looks like a long function because of the FAQ, but
		* most of this  block is text.
		*
		* @param	obj		$post 	Takes in the current instantce of post and 					*							redirects the user accordingly
		*/
		function displayDudaMobileDetectorPlugin( $post ){

			global $post, $_SERVER, $wpdb;
		
			$options= $newoptions  = get_option('dudamobile');
			
			if ( $_POST["option_submit"] ) 
			{				
				//$newoptions['mobile_url'] = strip_tags(stripslashes( $_POST["mobile_url"]) );	
				$newoptions['concate_url'] = strip_tags(stripslashes( $_POST["concate_url"]) );	
				$newoptions['activate_redirect'] = strip_tags(stripslashes($_POST["activate_redirect"]));	
				?>
					<script type="text/javascript">
					<!--
					window.location = "admin.php?page=dudamobile&setting=Y";
					//-->
					</script>
                <?php
			}
			
			// any changes? save!
			if ( $options != $newoptions ) {
				$options = $newoptions;
				update_option('dudamobile', $options);
			}
			
			$options  = get_option( 'dudamobile' );
			$img_path = get_option( 'siteurl' ).'/wp-content/plugins/'.basename(dirname(__FILE__)).'/img';
			

			//No mobile URL? Redirect to "Get Started Page"
			if ($options['mobile_url']=="") {
				?>
					<script type="text/javascript">
					<!--
					window.location = "admin.php?page=dudamobile";
					//-->
					</script>
                <?php
			}

			// options form
			echo '<form method="post">';
			echo '<div class="dudaLogo"><a href="http://dudamobile.com/?utm_source=wordpressplugin&utm_medium=wordpressPlugin&utm_campaign=duda_wordpress_plugin" target="_blank"><img src="'.$img_path.'/PoweredByDudaMobile.png"/></a></div>';
			echo '<div class="dudaPluginTitle"><h2>Mobile Site Options</h2>';
			echo '<table class="all-settings"><tr><td><span class="redirect-table-title"><strong>Redirection Settings</strong></span><table class="form-table-wrapper"><tr><td><table class="form-table">';
			
			//Activate
			if($options['activate_redirect']=='Y' || $_REQUEST['update_mobile_url']=='Y')
			{
				
				$checked="checked=\"checked\"";
			}
			else
			{
				$style="style=\"display:none\"";
			}
			
			echo '<tr valign="top"><th scope="row"><span id="redirectText">Activate Redirection</span></th>';
			echo '<td><input type="checkbox" id="activate_redirect" name="activate_redirect" value="Y" '.$checked.' onclick="displaynone();"><span id="redirectDesc">&nbsp; &nbsp;Your mobile users will be able to view your mobile site</span></input></td></tr>';		
			
			if($options['mobile_url']=="")
			{
				echo '<tr valign="top" '.$style.'><td colspan="2" class="hide_setting" style="color:red;">Please Insert Mobile URL</td></tr>';
			}
		
			//Mobile Url
			echo '<tr valign="top" '.$style.' class="hide_setting"><th scope="row"><span id="urlText">Your Mobile URL</span></th>';

			$create_page_url = $_SERVER["REQUEST_URI"];
			$create_page_url = str_replace("&setting=Y", "", $create_page_url);
			
			echo '<td>'.$options['mobile_url'].' <a href="' . $create_page_url . '"><br/><span style="font-size:10px;">Update</span></a></td></tr>';
			
			
			$selected="selected=\"selected\"";
			
			echo '<tr valign="top" '.$style.' class="hide_setting"><th scope="row"><span id="redirectText">Redirect mobile user to:</span></th>';
			
			echo '<td><select id="redirectSelect" name="concate_url"><option value="ppm" '.($options['concate_url']=="ppm"?$selected:'').'>Same page on mobile site</option><option value="msh" '.($options['concate_url']=="msh"?$selected:'').'>Home page on mobile site</option></select></td></tr>';
			
			//Last Updated
/*			echo '<tr valign="top" '.$style.' class="hide_setting"><th scope="row"><span id="updatedText">Last Updated</span></th>';
			echo '<td><span id="updatedDate">'.date("d/m/Y h:i:s",$options['last_updated']).'</span>&nbsp;<strong> Current </strong>'.date("d/m/Y h:i:s",time()).'</td></tr>';*/
			echo '<tr><td colspan="2"><p class="submit"><input type="submit" name="option_submit" value="Save Options &raquo;"></input></p></td></tr>';
			
			// close stuff			
			echo '</table></td></tr></table></div></form>';
			

			//Edit you mobile site:
			echo '<table class="button-table"><tr><td valign="middle"><span class="need-changes">Need to make changes?</span></td><td align="right">';
			echo '<form method="post" action="http://my.dudamobile.com?utm_source=wordpressplugin&utm_medium=wordpressPlugin&utm_campaign=duda_wordpress_plugin" target="_blank">';
			
			echo '<input type="submit" id="createButton2" value="Edit Your Mobile Site">
			</input>';				
			echo '</form></div></td></tr></table>';	
			
			//bottom faq and button
			echo '<br/><br/><div id="bottomParagraph">
<span id="bottomFAQ"><strong>FAQ</strong><br/><br/>

<strong>Why do I need to activate redirection?</strong><br/><br/>
Before smartphone users can see the mobile-optimized version of a website, the
site owner must insert a redirect script into the index.html file on their regular
website. The DudaMobile Website for WordPress plugin automatically redirects
your WordPress site to the mobile-optimized site when someone visits from their
mobile device.
<br/><br/>
<strong>How do I redirect smartphone users to my mobile website\'s homepage?</strong><br/></br/>
Go to “Redirect mobile users to” and choose “Redirect Users to Home Page”. Update
Options. Users from mobile phones are automatically redirected to the home page of
your mobile site.
<br/><br/>
<strong>How do I deactivate my mobile website redirect?</strong><br/><br/>
Uncheck “Activate Redirect” and update options. Mobile users will see your regular
site on their mobile phone.
<br/><br/>
<strong>Are there any special configurations that I need to use on WordPress to ensure
the plugin works?</strong><br/><br/>
No, you are not required to make any changes to your WordPress blog to make this
plugin work.
<br/><br/>
<strong>Where can I get help?</strong><br/><br/>
Go to DudaMobile support to view Help and Tutorials at <a href="http://support.dudamobile.com/home?utm_source=wordpressplugin&utm_medium=wordpressPlugin&utm_campaign=duda_wordpress_plugin">http://support.dudamobile.com/home</a>
<br/><br/>
<strong>What happens when a new phone comes out?</strong>
<br/><br/>
Mobile sites built on DudaMobile work on nearly every smartphone on the market.
DudaMobile plugin automatically and continuously updates the plugin to fit new
phones. You don’t have to make any changes.
<br/><br/>
<strong>How do I add premium features to my mobile site?</strong><br/><br/>
If you want access to premium features such as click-to-call, mobile maps, Google
AdSense, upgrade to the $9 per month premium plan. <a href="http://dudamobile.com/plans.html?utm_source=wordpressplugin&utm_medium=wordpressPlugin&utm_campaign=duda_wordpress_plugin">Read more</a> on DudaMobile
plans.</span>
<br/><br/><br/>

<span id="bottomParagraphText">You can easily create a new mobile site. Your previous mobile site will be saved and available for you to use in the future.
</span>
</div><br/> ';
			
			
			
			// options form
			echo '<form method="post" action="http://my.dudamobile.com/new?utm_source=wordpressplugin&utm_medium=wordpressPlugin&utm_campaign=duda_wordpress_plugin&_dm_referral=wordpressplugin&url='.get_site_url().'"  target="_blank">';
			
			echo '<input type="submit" id="createButton2" value="Create Your Mobile Site NOW">
			</input>';				
			
			echo '<input type="hidden" value="wordpressplugin" name="utm_source"/><input type="hidden" value="wordpressplugin" name="_dm_referral"/><input type="hidden" value="'.get_site_url().'"/>';
			echo '</form>';	
			

		}
		
		/**
		* Determines if the user has an existing Duda site and directs them
		* to either Dudamobile.com or to a form that self-populates with the most
		* recent mobile URL that matches their site's URL.
		* If they have a site & it's configured, they will be pushed to
		* the options page by the displayDudaMobileDetectorPlugin function.
		*/		
		function dudaMobileSiteCreation(){
			
			global $post, $_SERVER, $wpdb;
			
			$options = $newoptions = get_option('dudamobile');
			$img_path = get_option('siteurl').'/wp-content/plugins/'.basename(dirname(__FILE__)).'/img';
				                
			
			//update mobile url while activate
			$returned_content = $this->post_request($newoptions['api_url'], '{"value":"'.get_site_url().'"}');
			$mobile_url_array = json_decode($returned_content);
			
			//did we find a URL?
			if (!empty($mobile_url_array)){
				 $mobile_url = $mobile_url_array[0];
				 }
			else{
			 	$mobile_url="";
			 	}
			
			if($_REQUEST['setting']=='Y')
			{
				if($_REQUEST['update_mobile_url']=='Y')
				{
					$newoptions['mobile_url'] = urldecode($_REQUEST['mobile_url']);
				
					if ( $options != $newoptions ) 
					{
						$options = $newoptions;
						update_option('dudamobile', $options);
					}
					$this->doDummyCall();

				}
				//show options page if we already have a set mobile URL
				$this->displayDudaMobileDetectorPlugin($post);
			}
			
				//if we have no mobile URL, push users to initial form
			else
			{			
					// options form
					echo '<div class="dudaLogo"><a href="http://dudamobile.com/?utm_source=wordpressplugin&utm_medium=wordpressPlugin&utm_campaign=duda_wordpress_plugin" target="_blank"><img src="'.$img_path.'/PoweredByDudaMobile.png"/></a></div>';
					echo '<div class="dudaPluginTitle"><h2>DudaMobile Website for WordPress</h2>';
					echo '<table class="form-table">';
			

					//Mobile Url
					 if(!empty($mobile_url)){
					 			 						 
						echo '<div id="urlInquiryText"> Set up your mobile site redirect&#46;<br/>Confirm your mobile site URL by clicking YES</div>';
						if (count($mobile_url_array)==1) {
							echo '<div class="dudaURL"><span id="urlFormConfirm">' .$mobile_url. '</span><span class="yesButton"><form class="dudaForm" method="POST" action="admin.php?page=dudamobile&setting=Y"><input name="mobile_url" value="' .$mobile_url. '" type="hidden"><input name="update_mobile_url" value="Y" type="hidden"><input id="urlConfirm" type="submit" value="Yes"></form></span></div>';
						 
						}
						else {
							echo '<div class="dudaURL"><span class="yesButton"><form class="dudaForm" method="POST" action="admin.php?page=dudamobile&setting=Y">
							<select class="mobileUrlSelect" name="mobile_url">';
							foreach ($mobile_url_array as $url) {
								echo '<option value="' . $url . '">' . $url . '</option>';
							}
							echo '</select><input name="update_mobile_url" value="Y" type="hidden"><input id="urlConfirm" type="submit" value="Yes"></form></span></div>';
						}

						echo'<br/><br/><br/>';
		
						echo '<div id="bottomParagraphSeparate">
					Note&#58;&nbsp; After clicking YES go to your smart phone and make sure you can see your mobile&#45;friendly site&#46;</div>';
			
						echo "</div></form>";
					
					}
				 
				 	//if no mobile URL
				 	else{
				 					 
				 		echo '<div id="introParagraph">
Congrats&#33; You took the first step in making your WordPress site mobile&#45;friendly&#46;
Now it&#39;s time to create and customize your mobile site at DudaMobile&#46;com&#46; DudaMobile is fast, free and easy&#45;to&#45;use&#46;<br/><br/>';

echo '<form method="post" action="http://my.dudamobile.com/new?utm_source=wordpressplugin&utm_medium=wordpressPlugin&utm_campaign=duda_wordpress_plugin&_dm_referral=wordpressplugin&url='.get_site_url().'" target="_blank"><input type="hidden" value="wordpressplugin" name="utm_source"/><input type="hidden" value="wordpressplugin" name="_dm_referral"/><input type="submit" id="blueButton" value="Get Started Now" >
</form><br/><br/><br/><br/><strong>How it works:</strong><br/><br/>
<strong>Step 1: Click "Get Started Now"</strong><br/>DudaMobile reads the HTML and content from your
website and converts it to a mobile-friendly version.
<br/><br/>
<strong>Step 2: Customize your mobile website</strong><br/>Choose from dozens of drag-and-drop features
such as click-to-call, mobile maps and contact forms. 
<br/><br/>
<strong>Step 3: Go Live!</strong><br/>Preview your site just as smartphones users will
see it, then publish. 
<br/><br/>';		 
				 } 					
				
			}
		
		}
		
		
		/**
		* Fetches a URL and returns it as a string for updateBrowserAgent function
		*
		* @param string $url
		*/
		function get_data($url)
		{
		  $returned = wp_remote_request($url);
		  if (!$returned) return '';
		  if (isset($returned['body'])) return $returned['body'];
		  return '';
		}
		
		/**
		* Used at installation and on page load to check the Duda API for
		* a mobile URL that matches the current site's. 
		*
		* @param string		$url	URL for Duda API, defined in options
		* @param string 	$data	Current site's URL
		* @return array		$result	Usually returned as an instance variable set to			*							$returned_content to get an array of DudaMobile 		*							URLs that match the site's
		*
		*/
		function post_request($url, $data) {
 
			// parse the given URL
			$url = parse_url($url);
		 
			// extract host and path:
			$host = $url['host'];
			$path = $url['path'];
		 	$data_length = strlen($data);
			
			
			// open a socket connection on port 80 - timeout: 10 sec
			$fp = fsockopen($host, 80, $errno, $errstr, 10);
		 
			if (!$fp){
				$result = "";
			}
			
			else{
			//use HTTP 1.0 to avoid chunking which can obsfucate the array
				fputs($fp, "POST $path HTTP/1.0\r\n");
				fputs($fp, "Host:$host\r\n");
		 
	 			fputs($fp, "Content-Type: application/json\r\n");
				fputs($fp, "Content-Length: $data_length\r\n");
				fputs($fp, "Connection: Close\r\n\r\n");
				fputs($fp, $data);
		 		
				$result = ''; 
				while(!feof($fp)) {
				//receive the results of the request
					$result .= fgets($fp, $data_length);
					//force hangup after fetch is complete
					$stream = stream_get_meta_data($fp);
					if($stream['unread_bytes']<=0) break;
										
				}
			}
			
		 	// close the socket connection:
			fclose($fp);
			// split the result header from the content
			$result = explode("\r\n\r\n", $result, 2);
			
		 	$content = isset($result[1]) ? $result[1] : '';
		 
			// extract the actual the content
			$result = explode("\r\n\r\n", $content);
			$content = isset($result[1]) ? $result[1] : '';
			
			//combine into 1 array
			$result = implode('', $result);
			
			return $result;
		}
				

		/**
		* Checks the browser agent list at Duda and updates the local
		* file.
		* @link http://my.dudamobile.com/api/public/agents
		*/
		function updateBrowserAgent()
		{
			global $post, $_SERVER, $wpdb;
			
			$options= $newoptions  = get_option('dudamobile');
			
			//change return content after 30 days
			if(intval(time()-$options['last_updated'])>30*24*60*60 || $options['user_agent']=="")
			{
				//get content from url
				$returned_content = $this->get_data("http://my.dudamobile.com/api/public/agents");	
				
				//write in file
				$File=DUDAMOBILE_REDIRECTOR_PLUGIN_PATH."/agent.txt";
				
				$Handle = fopen($File, 'w');
				
				$Data =$returned_content; 
				fwrite($Handle, $Data);  
				fclose($Handle); 
				
				$newoptions['user_agent'] = $returned_content;
				$newoptions['last_updated'] =time();
			}
			
			if ( $options != $newoptions ) {
				$options = $newoptions;
				update_option('dudamobile', $options);
			}
		}
		
		
		/**
		* Finds and formats the site's mobile URL. Determines visitor's
		* device type and takes appropriate action.
		*/		
		function findMobileUrl($dontRedirect)
		{
			global $post, $_SERVER, $wpdb;
			
			$options = $newoptions  = get_option('dudamobile');			
				
			if (!is_bool(strpos($_SERVER['REQUEST_URI'], 'no_redirect=true'))) $dontRedirect=true;
			
			if(!$this->isValidURL($options['mobile_url']))
			{
				$mobile_url="http://".$options['mobile_url'];
			}
			else
			{
				$mobile_url=$options['mobile_url'];
			}
			
			//current user's browser
			$user_agent = $_SERVER['HTTP_USER_AGENT'];			
			
			//Get User Agent which we insert			
			$browsers=stripslashes($options['user_agent']);
			
			$browsers = str_replace("(.*", "", $browsers);
									  
			$browsers = strtolower(str_replace(".*)", "", $browsers));	
			
			//if detect mobile then redirect to mobile site			
			if(preg_match('/'.$browsers.'/i',$user_agent) &&  ! is_admin() && $options['activate_redirect']=='Y' && $options['mobile_url']!='')			
			{
				
				if($options['concate_url']=='ppm')
				{
					$concat_url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
					
					$mobile_url=$mobile_url."?url=".urlencode($concat_url);			
					$mobile_url=$mobile_url."&dm_redirected=true";
					
				}
				else {
					$mobile_url=$mobile_url."?dm_redirected=true";
				}

				if (!$dontRedirect) { wp_redirect($mobile_url);exit; }
				
			}
			if ($dontRedirect) return $mobile_url."?dm_redirected=true";

		}	
		/**
		* Checks if a string is in the format http://site with regex
		*/		
		function isValidURL($url)
		{
			return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
		}
		

		function doDummyCall() {
			$mobile_url = $this->findMobileUrl(true);

			$response = wp_remote_request($mobile_url, array('headers' => array('user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 5_0_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A405 Safari/7534.48.3')));
		}
	
	}
}

//initiate class with new object
$DudaMobileDetectorPlugin = new DudaMobileDetectorPlugin();
?>