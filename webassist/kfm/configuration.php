<?php 
$startKFMDir = getcwd();
chdir(dirname(__FILE__));
?>
<?php require_once( "../../Connections/PowerCMSConnection.php" ); ?>
<?php require_once( "../../WA_SecurityAssist/Helper_PHP.php" ); ?>
<?php require_once( "../../WA_Globals/WA_Globals.php" ); ?>
<?php require_once( "../../WA_CMS/library.php" ); ?>
<?php
$absLoc = (rel2abs($WAGLOBAL_images_folder,rel2abs("../../",dirname(__FILE__)))."/");
$relLoc = (abs2rel($absLoc,dirname(__FILE__)));
?>
<?php chdir($startKFMDir); ?>
<?php
if (!WA_Auth_RulePasses("Administrator") && !WA_Auth_RulePasses("Super Administrator")){
  WA_Auth_RestrictAccess("../../index_cms.php");
}
?>
<?php
$kfm_hidden_sidebar = false;
if(isset($_GET['showsidebar']) && $_GET['showsidebar'] == 'false') {
	$kfm_hidden_sidebar = true;
}
$kfm_db_type = 'mysql';
$kfm_db_prefix   = 'kfm_';
$kfm_db_host = $hostname_PowerCMSConnection;
$kfm_db_name = $database_PowerCMSConnection;
$kfm_db_username = $username_PowerCMSConnection;
$kfm_db_password = $password_PowerCMSConnection;
$kfm_db_port     = '';
$use_kfm_security = false;
$kfm_userfiles_address = $relLoc;
$kfm_userfiles_output = $absLoc;
$kfm_workdirectory = '.files-sqlite-pdo';
$kfm_imagemagick_path = '/usr/bin/convert';
$kfm_dont_send_metrics = 1;
$kfm_server_hours_offset = 1;

/**
 * This function is called in the admin area. To specify your own admin requirements or security, un-comment and edit this function
 */
function kfm_admin_check(){
	return false;
}