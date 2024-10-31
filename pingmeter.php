<?php
/*
Plugin Name: Pingmeter Uptime Monitoring
Plugin URI: https://www.pingmeter.com/
Description: Pingmeter monitors, notify and auto rescues your website during downtimes.
Author: Armin Nikdel
Version: 1.0.3
Author URI: http://www.pingmeter.com/
*/ 

add_action('admin_menu', 'pm_admin_menu');
add_action('wp_footer', 'pingmeter');
add_action('wp_head', 'pingmeter');

function pingmeter_load_plugin_textdomain() {
	$domain = 'pingmeter-uptime-monitoring';
	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	if ( $loaded = load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' ) ) {
		return $loaded;
	} else {
		load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	}
}
add_action( 'plugins_loaded', 'pingmeter_load_plugin_textdomain' );


  


function pingmeter(){
global $_SERVER,$pingmeter_tracker;
$option=get_pm_conf();

if (!isset($option['code'])) $option['code']='';

$option['code']=str_replace("\r",'',str_replace("\n",'',str_replace(" ","",trim(html_entity_decode($option['code'])))));

if ( $option['code']!=''){
if ( !strpos(strtolower($option['code']),"pingmeter") ){
if ( strpos($option['code'],"-")){

$codes=explode("-", $option['code']);
$aid=$codes[0];
$sid=$codes[1];
// $catID=$codes[2];
// $integrity=$codes[3];

?><!-- PINGMETER SMART UPTIME v1.0.3 WP - DO NOT CHANGE --><?php

$keyword=array();
$keyword[]='uptime monitoring';
$keyword[]='server ping';
$keyword[]='website downtime monitor';

$kwid=mt_rand(0,count($keyword)-1);

if (round($pingmeter_tracker==0)){

?>

<script type="text/javascript">_pm_aid=<?php echo $aid; ?>;_pm_sid=<?php echo $sid; ?>;(function(){var hstc=document.createElement('script');hstc.src='https://pingmeter.com/track.js';hstc.async=true;var htssc = document.getElementsByTagName('script')[0];htssc.parentNode.insertBefore(hstc, htssc);})();
</script>
<?php } ?>

<!-- PINGMETER SMART UPTIME - DO NOT CHANGE --><?php 



$pingmeter_tracker=1;


}
}
}
}






if (!function_exists("pm_clean_cache")){
function pm_clean_cache(){


	if(function_exists('wp_cache_clean_cache')){
	//to avoid a nasty bug!
	if(function_exists('wp_cache_debug')){
	global $file_prefix;
	@wp_cache_clean_cache($file_prefix);
	}
	}
	
	if (defined('W3TC')) {
	
	if(function_exists('w3tc_flush_all')){
	w3tc_flush_all();
	do_action('w3tc_flush_all');
	}
	
	if (function_exists('w3tc_pgcache_flush')) {
	w3tc_pgcache_flush();
	do_action('w3tc_pgcache_flush');
	}
	
	
	}

	


}
}


if (!function_exists("get_pm_conf")){
function get_pm_conf(){

$option=get_option('pm_setting');

//remove PHP Notices
if (!isset($option['code'])) $option['code']='';
if (!isset($option['wgd'])) $option['wgd']=1;
if (!isset($option['wgl'])) $option['wgl']=2;


//define pre-defined values.
if (round($option['wgd'])==0) $option['wgd']=1;
if (round($option['wgl'])==0) $option['wgl']=2;


return $option;

}
}
if (!function_exists("set_pm_conf")){
function set_pm_conf($conf){update_option('pm_setting',$conf);}
}



if (!function_exists("pm_admin_menu")){
function pm_admin_menu(){

$x = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));

	add_options_page(__("Pingmeter Options",'pingmeter-uptime-monitoring'), __("Pingmeter",'pingmeter-uptime-monitoring'), 'manage_options', __FILE__, 'pm_optionpage');

}
}



if (!function_exists("pingmeter_admin_warnings")){
function pingmeter_admin_warnings() {

$option=get_pm_conf();

if (!isset($option['code'])) $option['code']='';
if (!isset($_REQUEST['hitmagic'])) $_REQUEST['hitmagic']='';

if (isset($_POST['action'])){
$postaction=$_POST['action'];
}else{
$postaction='';
}

	if ( $option['code']=='' && $postaction!='do' && $_REQUEST['hitmagic']!='do' ) {
		function pingmeter_warning() {
			echo "
			<div id='pingmeter-warning' class='updated fade'><p><strong>".__('Enjoy peace of mind with Pingmeter Uptime monitoring.','pingmeter-uptime-monitoring')."</strong> ".sprintf(__('You must <a href="%1$s">enter your Pingmeter API key</a> to have pingmeter monitor and notify you of your website downtimes.','pingmeter-uptime-monitoring'), "options-general.php?page=pingmeter-uptime-monitoring/pingmeter.php")."</p></div>
			
			<script type=\"text/javascript\">setTimeout(function(){jQuery('#pingmeter-warning').slideUp('slow');}, 30000);</script>

			";

		}

		add_action('admin_notices', 'pingmeter_warning');

		return;

	}

}
pingmeter_admin_warnings();
$option=get_pm_conf();

}


if (!function_exists("pingmeter_call")){
	function pingmeter_call($post){
		$pingmeter_api_receiver="http://pingmeter.com/api/wp-register.php";
		$post['v']=1;

		$arg=array(
		'method'=>'POST',
		'timeout'=>18,
		'redirection'=>5,
		'body'=>$post		
		);

		 //Set the URL to work with
		$result=wp_remote_post($pingmeter_api_receiver,$arg);
		$arr=array();

		
		if ($result['body']=='db_down_for_maintaince'){
		$arr['error']=99;
		$arr['msg']="Pingmeter internal database error";
		return $arr;
		}

		if (strpos(strtolower($result['body']),"cloudflare")) {
		$arr['error']=999;
		$arr['msg']="Pingmeter webserver is inaccessible from this plugin.";
		return $arr;
		}

		//var_dump($result['body']);
		$arr=(array) json_decode($result['body'], true);	

		return $arr;
		
	}
}




if (!function_exists("pm_optionpage")){
function pm_optionpage(){

	$verify=true;
if (count($_POST)>0){
	$verify=wp_verify_nonce( $_POST['_wpnonce'], 'pm_option_page' );
}




if ($verify&&(current_user_can('manage_options')||$option['wgl']!=2)) {


 $nonce = wp_create_nonce( 'pm_option_page');

	
$option=get_pm_conf();



$option['code']=html_entity_decode($option['code']);
$option['wgd']=html_entity_decode($option['wgd']);
$option['wgl']=html_entity_decode($option['wgl']);

$magicable=1;


if (!function_exists('wp_get_current_user'))
global $current_user;

if(function_exists('get_currentuserinfo')){

if (!function_exists('wp_get_current_user'))
get_currentuserinfo();


}

if (function_exists('wp_get_current_user'))
$current_user=wp_get_current_user();


if ($current_user->user_email==''){
$magicable=0;
}

if ($current_user->display_name==''){

$current_user->display_name=$current_user->user_firstname;
}

if ($current_user->user_identity!=''){

$current_user->display_name=$current_user->user_identity;

}

if ($current_user->user_firstname==''){

$current_user->user_firstname=$current_user->display_name;

}


if ($current_user->display_name==''){
$magicable=0;
}

if(!function_exists('get_bloginfo')){

$magicable=0;
}

if (isset($_REQUEST['hitmagic'])&&$_REQUEST['hitmagic']=='do'){

if ($magicable==1){

//check data
$magic_error=1;
$error_msg=array();

if ($_POST['hitmode']=='new'){

$magic_error=0;
$email=sanitize_email($_POST['magic']['email']);
$password=$_POST['magic']['password']; //password will be hashed, and won't be shown back ever. so it do not need sanitization.
$nickname=sanitize_text_field($_POST['magic']['nickname']);
$refhow=sanitize_text_field($_POST['magic']['refhow']);
$wname=sanitize_text_field($_POST['magic']['wname']);
$summary=sanitize_text_field($_POST['magic']['summary']);
$site=sanitize_text_field($_POST['magic']['site']);
$fname=sanitize_text_field($_POST['magic']['fname']);
$lname=sanitize_text_field($_POST['magic']['lname']);
$lang=sanitize_text_field($_POST['magic']['lang']);

if (!isset($_POST['terms'])||$_POST['terms']!='1'){$magic_error=1;$error_msg[]=__("You need to accept terms and conditions.",'pingmeter-uptime-monitoring');}
if ($site==''){$magic_error=1;$error_msg[]=__("Cannot find your website address",'pingmeter-uptime-monitoring');}
if ($wname==''){$magic_error=1;$error_msg[]=__("Cannot find your website name",'pingmeter-uptime-monitoring');}
if ($email==''){$magic_error=1;$error_msg[]=__("Email cannot be empty",'pingmeter-uptime-monitoring');}
if ($password==''){$magic_error=1;$error_msg[]=__("Password cannot be empty",'pingmeter-uptime-monitoring');}
if ($nickname==''){$magic_error=1;$error_msg[]=__("Nickname cannot be empty",'pingmeter-uptime-monitoring');}



}

if ($_POST['hitmode']=='loyal'){

$magic_error=0;
$email=sanitize_email($_POST['magic']['email']);
$password=$_POST['magic']['password'];
$nickname="";
$refhow="";
$wname=sanitize_text_field($_POST['magic']['wname']);
$summary=sanitize_text_field($_POST['magic']['summary']);
$site=sanitize_text_field($_POST['magic']['site']);
$fname="";
$lname="";
$lang="";

if ($site==''){$magic_error=1;$error_msg[]=__("Cannot find your website address",'pingmeter-uptime-monitoring');}
if ($wname==''){$magic_error=1;$error_msg[]=__("Cannot find your website name",'pingmeter-uptime-monitoring');}

}

if ($magic_error==0){

$mdata = array(
            'ip'=>$_SERVER['REMOTE_ADDR'],
            'fname'=>$fname,
            'lname'=>$lname,
            'password'=>$password,
            'email'=>$email,
            'nick'=>$nickname,
            'name'=>$wname,
            'summary'=>$summary,
            'site'=>$site,
            'lang'=>$lang,
            'refhow'=>$refhow,
            'mode'=>sanitize_text_field($_POST['hitmode'])

        );
        
$hcresult=pingmeter_call($mdata);

if (isset($hcresult['error'])&&$hcresult['error']==0){
$option['code']=$hcresult['code'];
set_pm_conf($option);
$saved=1;
$magiced=1;
$error_msg[]=$hcresult['msg'];
$magicable=0;
}else{
$magic_error=1;
if (!isset($hcresult['error'])) $hcresult['error']=9999;
if (!isset($hcresult['msg'])) $hcresult['msg']='';
$error_msg[]=$hcresult['msg']." (Err #".round($hcresult['error']).")";

}

}




}


}







		if (isset($_POST['action'])&&$_POST['action']=='do'){
		
			// if (!current_user_can('manage_options')){
			// $_POST['wgl']=$option['wgl'];
			// }

			if (isset($_POST['code'])){
				if ($_POST['code']!=''&&(strpos("-".$_POST['code'],"<")||substr_count($_POST['code'], "-")!=3)){
					$error_msg[]="Valid API code look like ###-###-XXXXXXXXXXXXXXXXX-XXXXX, you have entered invalid code.";
				}else{
				$option['code']=htmlentities(str_replace(" ","",sanitize_text_field($_POST['code'])));
				
	            set_pm_conf($option);

				$saved=1;
				}
			}
		}

?>

<div class="wrap">


<style>
.clear{
clear: both;
}
</style>

<?php

if (isset($saved)&&$saved==1){

?>



<br>

<div id='pingmeter-saved' class='updated fade' ><p><strong><?php echo __("Pingmeter plugin setting have been saved.",'pingmeter-uptime-monitoring');?></strong> <?php if ($option['code']!=''){ ?><?php { ?><?php echo __("You have associated your website with your Pingmeter account.",'pingmeter-uptime-monitoring');?><?php }}else{ ?><?php echo __("Please get your Pingmeter API code to enable us to track your website uptime.",'pingmeter-uptime-monitoring');?><?php } ?></p></div>

<script type="text/javascript">setTimeout(function(){jQuery('#pingmeter-saved').slideUp('slow');}, 11000);</script>

<br>


<?php

pm_clean_cache();

}
$x = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
?>


<div style="max-width: 1300px; margin:auto;">
<h1 style="font-weight: 400;">




<img src="<?php echo $x; ?>favicon.png" width="48" style="vertical-align: middle; padding-right: 3px; " />

<a target="_blank" href="https://www.pingmeter.com/?tag=wordpress-to-homepage" style="color: #000; text-decoration: none;   font-weight: lighter;"><?php echo __("Pingmeter - Smart Uptime Monitoring",'pingmeter-uptime-monitoring');?></a></h1>
</div>
<br>

<div>

<?php if ($option['code']!=''){

$magicable=0;

 ?>
 
 
 
<div style="max-width:1300px; margin-left: auto; margin-right: auto;">
<a class='button button-primary button-large' style="width:100%; margin-bottom: 15px;  height: 50px;  line-height: 50px; text-align: center;" href="https://www.pingmeter.com/login-code.php?code=<?php echo $option['code']; ?>" target="_blank"><?php echo __("Click here to open your Pingmeter dashboard.",'pingmeter-uptime-monitoring');?></a>
</div>
<?php } 
$x = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
?>


<style>
.postbox {
  margin: 0 20px 20px 0;
}
.form-field input[type=email], .form-field input[type=number], .form-field input[type=password], .form-field input[type=search], .form-field input[type=tel], .form-field input[type=text], .form-field input[type=url], .form-field textarea {
  width: 100%;
  padding:6px;
}
</style>
<div style="max-width:1300px; margin-left: auto; margin-right: auto;">
<div class="postbox-container" style="width:70%;">
					<div class="metabox-holder">
						<div class="meta-box-sortables">
			
			
<?php 
if (isset($error_msg))
if (count($error_msg)>0){ 
foreach($error_msg as $errmsg){
?>
<div class='updated fade pingmeter-msg' ><p><?php echo $errmsg; ?></p></div>

<script type="text/javascript">setTimeout(function(){jQuery('.pingmeter-msg').slideUp('slow');}, 21000);</script>
<?php }
} ?>
			
			
			
			
			
							

<?php if ($magicable==1){
 if ($option['code']=='') { 
 
 
 
$lang=get_bloginfo('language');

if (strpos($lang,"-")>0){
$splitlang=explode("-",$lang);
$lang=$splitlang[0];
}

if ($lang=='') $lang='en';
 if (!isset($_POST['hitmode'])) $_POST['hitmode']='';





 ?>





<div class="postbox">
				<h3 class="hndle" style="cursor: default;"><span><?php echo __("Pingmeter Auto Registration",'pingmeter-uptime-monitoring');?></span></h3>

				<div class="inside hitmagicauto-main form-field">

<form method="POST" class="hitmagicauto" style="<?php if ($_POST['hitmode']=='loyal') { ?>display: none;<?php } ?>">

<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo $nonce; ?>" />

<div >

<div class="button" style="float: right;" onclick="jQuery('.hitmagicauto').hide();jQuery('.hitmagicloyal').fadeIn(500);"><?php echo __("Already a Pingmeter user? Login here.",'pingmeter-uptime-monitoring');?></div><br>

<small>
<?php echo __("Email",'pingmeter-uptime-monitoring');?>:<br><input type="email" name="magic[email]" value="<?php if (isset($_POST['magic']['email'])){echo sanitize_email($_POST['magic']['email']);}else{ echo $current_user->user_email;} ?>" /><br><br>
<?php echo __("Password",'pingmeter-uptime-monitoring');?>:<br><input type="password" name="magic[password]" value="<?php if (isset($_POST['magic']['password'])){echo sanitize_text_field($_POST['magic']['password']);} ?>" /><br><br>
<?php echo __("Nickname",'pingmeter-uptime-monitoring');?>:<br><input type="text" name="magic[nickname]" value="<?php if (isset($_POST['magic']['nickname'])){ echo sanitize_text_field($_POST['magic']['nickname']); }else{  echo $current_user->display_name; } ?>" /><br><br>
<?php echo __("How did you heard about pingmeter",'pingmeter-uptime-monitoring');?>:<br><input type="text" name="magic[refhow]" value="<?php  if (isset($_POST['magic']['refhow'])){echo sanitize_text_field($_POST['magic']['refhow']);} ?>" /><br><br>
</small>


<input type="hidden" name="hitmagic" value="do">
<input type="hidden" name="hitmode" value="new">
<input type="hidden" name="magic[wname]" value="<?php echo get_bloginfo('name'); ?>" />
<input type="hidden" name="magic[summary]" value="<?php echo get_bloginfo('description'); ?>" />
<input type="hidden" name="magic[site]" value="<?php echo get_bloginfo('url'); ?>" />
<input type="hidden" name="magic[fname]" value="<?php echo $current_user->user_firstname; ?>" />
<input type="hidden" name="magic[lname]" value="<?php echo $current_user->user_lastname; ?>" />
<input type="hidden" name="magic[lang]" value="<?php echo $lang; ?>" />



<input type="checkbox" value="1" name="terms" id="terms" /><label for="terms"><?php echo __("I agree <a href=\"https://www.pingmeter.com/privacy.php\" target=\"_blank\">Pingmeter's terms and privacy policy</a>, I agree to allow pingmeter to send me emails regarding my website and my service and would like to sign-up for pingmeter account, setup a HTTP monitoring for my website and get this website's API key automatically from Pingmeter servers.",'pingmeter-uptime-monitoring');?></label>

<br><br>

<input type="submit" class='button button-primary button-large' style="width:100%; margin-bottom: 8px; padding-top:5px; padding-bottom:5px; font-size: 14pt;" value="Sign up & API Key Installation">





</div>

</form>



<form method="POST" class="hitmagicloyal" style="<?php if ($_POST['hitmode']!='loyal') { ?>display: none;<?php } ?>">


<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo $nonce; ?>" />



<div >

<div class="button" style="float: right;" onclick="jQuery('.hitmagicloyal').hide();jQuery('.hitmagicauto').fadeIn(500);"><?php echo __("New pingmeter user? Sign up here.",'pingmeter-uptime-monitoring');?></div><br>

<small>
<?php echo __("Email",'pingmeter-uptime-monitoring');?>:<br><input type="email" name="magic[email]" value="<?php if (isset($_POST['magic']['email'])){echo sanitize_email($_POST['magic']['email']);}else{ echo $current_user->user_email;} ?>" /><br><br>
<?php echo __("Password",'pingmeter-uptime-monitoring');?>:<br><input type="password" name="magic[password]" value="<?php if (isset($_POST['magic']['password'])){echo sanitize_text_field($_POST['magic']['password']);} ?>" /><br><br>
</small>


<input type="hidden" name="hitmagic" value="do">
<input type="hidden" name="hitmode" value="loyal">
<input type="hidden" name="magic[wname]" value="<?php echo get_bloginfo('name'); ?>" />
<input type="hidden" name="magic[summary]" value="<?php echo get_bloginfo('description'); ?>" />
<input type="hidden" name="magic[site]" value="<?php echo get_bloginfo('url'); ?>" />


<input type="submit" class='button button-primary button-large' style="width:100%; margin-bottom: 8px;  padding-top:5px; padding-bottom:5px; font-size: 14pt;" value="<?php echo __("Login & API Key Installation",'pingmeter-uptime-monitoring');?>">

</div>

</form>










</div>
</div>

<?php } } ?>













<style>
.hndle{
cursor: default !important;
}
</style>



<form method="POST" action="<?php echo str_replace('&hitmagic=do','',$_SERVER['REQUEST_URI']); ?>">

<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo $nonce; ?>" />

<div class="postbox">
				<h3 class="hndle"
				><span><?php echo __("Integration",'pingmeter-uptime-monitoring');?></span></h3>

				<div class="inside  form-field">




<table width="100%"><tr><td>

	<input type="text" name="code" size="20" placeholder="Enter your website's pingmeter API Key here" value="<?php echo $option['code']; ?>">
	
	</td><td width="100">
	
	<a href="https://www.pingmeter.com/register.php?tag=wp-getyourcodebtn" class="button" target="_blank"><?php echo __("Get your API Key",'pingmeter-uptime-monitoring');?></a>
	</td></tr></table>
	
	<?php if ($option['code']==''){ ?><br>
	<?php if ($magicable==1){ ?><?php echo __("You can use quick auto registration form above to get your API key. Alternatively you can manually enter your API key here. You can get your API key after adding your website in Pingmeter.",'pingmeter-uptime-monitoring');?> <br><?php } ?>
	<a href="https://www.pingmeter.com/register.php?tag=wp-getyourcode" target="_blank"><?php echo __("Register a pingmeter account if you haven't and add your website to your account",'pingmeter-uptime-monitoring');?></a>, <?php echo __("Go to your dashboard in Pingmeter and click \"Websites\" and then modify, you will find the API Key on top. Each website and each user have their own API key. It looks like this 156-533-3defb4a2e4426642ea...",'pingmeter-uptime-monitoring');?>
<?php } ?>



<div style="  margin: 0;">
	<input type="submit" value="<?php echo __("Save Changes",'pingmeter-uptime-monitoring');?>" class='button button-primary' style="width:100%;  height: 50px;  line-height: 50px; " >
</div>
</div>
</div>





<!-- 
<div class="postbox">
				<h3 class="hndle"><span><?php echo __("Advanced Settings",'pingmeter-uptime-monitoring');?></span></h3>

				<div class="inside  form-field">







<p><input type="radio" value="1" name="wgd" <?php if ($option['wgd']!=2) echo "checked"; ?>><?php echo __("Yes",'pingmeter-uptime-monitoring');?>&nbsp;

<input type="radio" value="2" name="wgd" <?php if ($option['wgd']==2) echo "checked"; ?>><?php echo __("No",'pingmeter-uptime-monitoring');?>&nbsp;&nbsp;&nbsp;<?php echo __("Show Pingmeter quick summary of my monitors in Wordpress Dashboard?",'pingmeter-uptime-monitoring');?>

</p>
<?php 
if (current_user_can('manage_options')){
?>
<p><input type="radio" value="2" name="wgl"  <?php if ($option['wgl']==2) echo "checked"; ?> ><?php echo __("Yes",'pingmeter-uptime-monitoring');?>&nbsp;

<input type="radio" value="1" name="wgl"  <?php if ($option['wgl']!=2) echo "checked"; ?>><?php echo __("No",'pingmeter-uptime-monitoring');?>&nbsp;&nbsp;&nbsp;<?php echo __("Restrict Wordpress Dashboard widget for WordPress administrators only (recommended for your pingmeter account security)",'pingmeter-uptime-monitoring');?>

</p>
<?php } ?>


</div>
</div> 

<div style="  margin: 0 20px 20px 0;">
	<input type="submit" value="<?php echo __("Save Changes",'pingmeter-uptime-monitoring');?>" class='button button-primary button-large' style="width:100%; margin-bottom: 15px; font-size: 13pt; height: 50px;  line-height: 50px; " >
</div>



-->

<input type="hidden" name="action" value="do">



				</form>		
				
				


<?php if ($option['code']==''){ ?>






<div id="pingmeter_features" class="postbox">
<h3 class="hndle"><span><?php echo __("How to setup Pingmeter on Wordpress?",'pingmeter-uptime-monitoring');?></span></h3>

<div class="inside">

<a href="https://www.pingmeter.com/register.php?tag=wordpress-to-ht-reg"><?php echo __("Simply sign up for a pingmeter account</a> using form above.",'pingmeter-uptime-monitoring');?></a>

</div>
</div>	




<?php 
}
 ?>
				
							
						</div>
					</div>
				</div>

<div class="postbox-container" style="width:30%;">
					<div class="metabox-holder">
						<div class="meta-box-sortables">
							
							
<?php if ($option['code']!=''){ ?>


<div id="pingmeter_features" class="postbox">
<h3 class="hndle"><span><?php echo __("Your Pingmeter",'pingmeter-uptime-monitoring');?></span></h3>

<div class="inside">

<a target="_blank" href="https://www.pingmeter.com/login-code.php?code=<?php echo $option['code']; ?>">
<img border="0" src="<?php echo $x; ?>pingmeter.png"  width="169" ><br><?php echo __("Click to see your dashboard",'pingmeter-uptime-monitoring');?></a>


</div>
</div>


<?php }else{ ?>


<div id="pingmeter_features" class="postbox">
<h3 class="hndle"><span><?php echo __("What is Pingmeter?",'pingmeter-uptime-monitoring');?></span></h3>

<div class="inside">

<?php echo __("Pingmeter Smart Uptime monitoring monitors, notify and auto rescues your website during downtimes.",'pingmeter-uptime-monitoring');?><br><br>

<a target="_blank" href="https://www.pingmeter.com/">
<img border="0" src="<?php echo $x; ?>pingmeter.png" width="169"><br><?php echo __("Click here to see features",'pingmeter-uptime-monitoring');?></a>


</div>
</div>


<?php } ?>


<div id="pingmeter_features" class="postbox">
<h3 class="hndle"><span><?php echo __("Want more of pingmeter?",'pingmeter-uptime-monitoring');?></span></h3>

<div class="inside">

<ul>

<li><a href="https://www.pingmeter.com/contact.php" target="_blank"><?php echo __("Contact pingmeter team or Provide feedback.",'pingmeter-uptime-monitoring');?></a></li>
</ul>


</div>
</div>	

					
<div id="pingmeter_features" class="postbox">
<h3 class="hndle"><span><?php echo __("Like pingmeter?",'pingmeter-uptime-monitoring');?></span></h3>

<div class="inside">
<p><?php echo __("Why not do help us to spread the word:",'pingmeter-uptime-monitoring');?></p><ul><li><a href="https://www.pingmeter.com/" target="_blank"><?php echo __("Link to us so other can know about it.",'pingmeter-uptime-monitoring');?></a></li><!-- <li><a href="https://wordpress.org/support/view/plugin-reviews/pingmeter-uptime-monitoring?rate=5#postform" target="_blank"><?php echo __("Give it a 5 star rating on WordPress.org.",'pingmeter-uptime-monitoring');?></a></li> --><li><a href="https://www.pingmeter.com/members/aff.php" target="_blank"><?php echo __("Join Pingmeter affiliate program.",'pingmeter-uptime-monitoring');?></a></li></ul>


</div>
</div>					
							
	
					
<div id="pingmeter_features" class="postbox">
<h3 class="hndle"><span><?php echo __("Follow us",'pingmeter-uptime-monitoring');?></span></h3>

<div class="inside">




<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.2&appId=220184274667129&autoLogAppEvents=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<div class="fb-like" data-href="https://www.facebook.com/pingmeter/" data-width="150" data-layout="standard" data-action="like" data-show-faces="true" data-share="true"></div>




<br>
<br>


<a class="twitter-follow-button"
  href="https://twitter.com/pingmeter"
  data-show-count="true"
  data-size="large"
  data-width="150px"
  data-lang="en">
<?php echo __("Follow",'pingmeter-uptime-monitoring');?> @pingmeter
</a>
<script>window.twttr=(function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],t=window.twttr||{};if(d.getElementById(id))return t;js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);t._e=[];t.ready=function(f){t._e.push(f);};return t;}(document,"script","twitter-wjs"));</script>




</div>
</div>					
								
							
							
						</div>
					</div>
				</div>
				
				
				
				
				
				
				
</div>

<div style="clear:both;"></div>



<?php 
}else{
	?>You are not authorized to edit this plugin settings.<?php

	echo $_POST['_wpnonce'];
}

}


}


if (!function_exists("pingmeter_dashboard_widget_function")){
function pingmeter_dashboard_widget_function() {
	$option=get_pm_conf();

 if ($option['code']!=''){ ?><table border="0" cellpadding="0" style="border-collapse: collapse" width="100%">
	<tr>
		<td>

	<iframe scrollable="no" scrolling="no"  name="pingmeter-stat" frameborder="0" style="background-color: #fff; border: 1px solid #A4A2A3;" margin="0" padding="0" marginheight="0" marginwidth="0" width="100%" height="420" src="https://pingmeter.com/members/wp.php?code=<?php echo $option['code']; ?>">	


		<p align="center">
		<a href="https://www.pingmeter.com/login-code.php?code=<?php echo $option['code']; ?>">
		<span>
		<font face="Verdana" style="font-size: 12pt"><?php echo __("Your Browser don't show our widget's iframe. Please Open Pingmeter Dashboard manually.",'pingmeter-uptime-monitoring');?></font></span></a></iframe></td>

	</tr>

</table>
<?php


}else{ ?><table border="0" cellpadding="0" style="border-collapse: collapse" width="100%" height="54">

	<tr>

		<td>

		<p align="left"><?php echo __("pingmeter API Code is not installed. Please open Wordpress Settings -> Pingmeter for instructions.",'pingmeter-uptime-monitoring');?><br>
<?php echo __("You need get your free pingmeter account to get an API key.",'pingmeter-uptime-monitoring');?></td>

	</tr>

</table>



<?php



}

}
}





if (!function_exists("pingmeter_add_dashboard_widgets")){
function pingmeter_add_dashboard_widgets() {

$option=get_pm_conf();


if ($option['wgd']!=2){

    if (function_exists('wp_add_dashboard_widget')){
    if (current_user_can('manage_options')||$option['wgl']!=2) {
    //TODO: Implement this
      // wp_add_dashboard_widget('pingmeter_dashboard_widget', __("pingmeter - Your Analytics Summary",'pingmeter-uptime-monitoring'), 'pingmeter_dashboard_widget_function');	
    }
    }
}



}



add_action('wp_dashboard_setup', 'pingmeter_add_dashboard_widgets' );
}

	# add "Settings" link to plugin on plugins page
	add_filter('plugin_action_links', 'pingmeter_settingsLink', 0, 2);
	function pingmeter_settingsLink($actionLinks, $file) {
 		if (($file == 'pingmeter-uptime-monitoring/pingmeter.php') && function_exists('admin_url')) {
			$settingsLink = '<a href="' . admin_url('options-general.php?page=pingmeter-uptime-monitoring/pingmeter.php') . '">' . __('Settings','pingmeter-uptime-monitoring') . '</a>';

			# Add 'Settings' link to plugin's action links
			array_unshift($actionLinks, $settingsLink);
		}

		return $actionLinks;
	}




?>