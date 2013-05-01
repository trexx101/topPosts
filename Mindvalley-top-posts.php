<?php

/*
Plugin Name: Mindvalley Top Post Ranks
Plugin URI: http://www.example.com
Description: My first of many plugins,will be used to rank most viewed pages recived from google analytics
Author: Inah Afen
Version: 1.00
Author URI: http://www.hire-inah.tk
License: GPL2
 */

/*********************************************************************************
 * Includes
 *********************************************************************************/
require_once 'src/Google_Client.php';
require_once 'src/contrib/Google_AnalyticsService.php';
require_once 'storage.php';
require_once 'authHelper.php';

$http= 'http://';
$current_page_URL = $http.$_SERVER["SERVER_NAME"].'/wp-admin/admin.php?page=topPosts/Mindvalley-top-posts.php';




const APP_NAME = 'Google Analytics Sample Application';
const ANALYTICS_SCOPE = 'https://www.googleapis.com/auth/analytics.readonly';


	// Set up a period of time to get data for
	$statsStartDate = date('Y-m-d', time() - 8 * 24 * 60 *60); //one week ahead
	$statsEndDate   = date('Y-m-d', time() - 1 * 24 * 60 *60); //yesterday



//add admin tab to wordpress menu
add_action('admin_menu','mindvalley_post_rank');
function mindvalley_post_rank(){
    add_menu_page('TopPosts', 'Top Posts Rank','manage_options','topPosts/Mindvalley-top-posts.php', 'postrank_admin', plugins_url( 'myplugin/images/icon.png' ), 81);
}
//admin page 
function postrank_admin(){
    global $wppostrank_options;
    //ob_start();
?>
<div class="wrap">
    <div class="icon32" id="icon-options-general"></div>
    <h1>MindValley Top 10 Post Plugin</h1>
    <h4>Setup Google analytic to retrieve the top 10 pages</h4> 
    
        <p>Use your Google Analytics settings</p>
        <form method="POST" action="options.php">
            
            
            
            <?php settings_fields( 'postrankgroup' ); ?>
            <?php do_settings_sections( 'postrank' ); ?>
            <?php submit_button(); ?>
            
            copy this url and update API access
            <?php 
            $http= 'http://';
$current_page_URL = $http.$_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
            
            echo $current_page_URL;
            $current_page_URL2 = $http.$_SERVER["SERVER_NAME"].'/wp-admin/admin.php?page=topPosts/Mindvalley-top-posts.php';
            
            echo $current_page_URL2;
            ?>
            
        </form>
        
        <p>To begin, you must first grant this application access to your
       Google Analytics data.</p>
            <?php
            /**************************************************************************************************
**Authenthiation 
************************************************************************************************/
$demoErrors = null;

$authUrl = $current_page_URL2 . '&action=auth';
$revokeUrl = $current_page_URL . '&action=revoke';

$helloAnalyticsDemoUrl = $current_page_URL . '?demo=hello';
$mgmtApiDemoUrl = $current_page_URL . '?demo=mgmt';
$coreReportingDemoUrl = $current_page_URL . '?demo=reporting';
$settings_address = (array) get_option( 'wpPostrankSetting' );
echo $current_page_URL;
error_reporting(0);
// Build a new client object to work with authorization.
$client = new Google_Client();
$client->setClientId($settings_address['client_id']);
$client->setClientSecret($settings_address['client_secrete']);
$client->setRedirectUri($current_page_URL2);
$client->setApplicationName($settings_address['app_name']);
$client->setScopes(
    array(ANALYTICS_SCOPE));

// Magic. Returns objects from the Analytics Service
// instead of associative arrays.
$client->setUseObjects(true);


// Build a new storage object to handle and store tokens in sessions.
// Create a new storage object to persist the tokens across sessions.
$storage = new apiSessionStorage();


$authHelper = new AuthHelper($client, $storage, $current_page_URL);

// Main controller logic.

if ($_GET['action'] == 'revoke') {
  $authHelper->revokeToken();

} else if ($_GET['action'] == 'auth' || $_GET['code']) {
    echo 'it got get!';
  $authHelper->authenticate();

} else {
  $authHelper->setTokenFromStorage();

  if ($authHelper->isAuthorized()) {
    $analytics = new Google_AnalyticsService($client);

    if ($_GET['demo'] == 'hello') {

      // Hello Analytics API Demo.
      require_once 'helloAnalyticsApi.php';

      $demo = new HelloAnalyticsApi($analytics);
      $htmlOutput = $demo->getHtmlOutput();
      $demoError = $demo->getError();

    } else if ($_GET['demo'] == 'mgmt') {

      // Management API Reference Demo.
      require_once 'managementApiReference.php';

      $demo = new ManagementApiReference($analytics);
      $htmlOutput = $demo->getHtmlOutput();
      $demoError = $demo->getError();

    } else if ($_GET['demo'] == 'reporting') {

      // Core Reporting API Reference Demo.
      require_once 'CoreReportingApiReference.php';

      $demo = new coreReportingApiReference($analytics, $current_page_URL);
      $htmlOutput = $demo->getHtmlOutput($_GET['tableId']);
      $demoError = $demo->getError();
    }
  }

  // The PHP library will try to update the access token
  // (via the refresh token) when an API request is made.
  // So the actual token in apiClient can be different after
  // a require through Google_AnalyticsService is made. Here we
  // make sure whatever the valid token in $service is also
  // persisted into storage.
  $storage->set($client->getAccessToken());
}
// Consolidate errors and make sure they are safe to write.
$errors = $demoError ? $demoError : $authHelper->getError();
$errors = htmlspecialchars($errors, ENT_NOQUOTES);

  // Print out authorization URL.
  if ($authHelper->isAuthorized()) {
    print "<p><a href='$revokeUrl'>Revoke access</a></p>";
  } else {
    print "<p><a href='$authUrl'>Grant access to Google Analytics data</a></p>";
  }
?>
        <hr>
    <p>Next click which demo you'd like to run.</p>
    <ul>
      <li><a href="<?=$helloAnalyticsDemoUrl?>">Hello Analytics API</a> &ndash;
          Traverse through the
          <a href="http://code.google.com/apis/analytics/docs/mgmt/v3/mgmtGettingStarted.html">
             Management API</a> to get the first profile ID.
          The use this ID with the
          <a href="http://code.google.com/apis/analytics/docs/gdata/v3/gdataGettingStarted.html">
             Core Reporting API</a> to print the top 25
          organic search terms.</li>

      <li><a href="<?=$mgmtApiDemoUrl?>">Management API Reference</a> &ndash;
          Traverse through the
          <a href="http://code.google.com/apis/analytics/docs/mgmt/v3/mgmtGettingStarted.html">
             Management API</a> and print all the important
          information returned from the API for each of the first entities.</li>

      <li><a href="<?=$coreReportingDemoUrl?>">Core Reporting API Reference</a> &ndash;
          Query the <a href="http://code.google.com/apis/analytics/docs/gdata/v3/gdataGettingStarted.html">
             Core Reporting API</a> and print out all the important information
          returned from the API.</li>
    </ul>
    <hr>
<?php
  // Print out errors or results.
  if ($errors) {
    print "<div>There was an error: <br> $errors</div>";
  } else if ($authHelper->isAuthorized()) {
    print "<div>$htmlOutput</div>";
  } 
?>
</div>
<?php
//echo ob_get_clean();
}
//add settings
add_action('admin_init','postrank_settings');
//register setting for plugin
function postrank_settings(){
    register_setting('postrankgroup','wpPostrankSetting');
    add_settings_section( 'section-one', 'Google API Access', 'section_one_callback', 'postrank' );
    add_settings_field( 'field-app-name', 'App Name', 'field_one_callback', 'postrank', 'section-one' );
    add_settings_field( 'field-client-id', 'Client ID', 'field_two_callback', 'postrank', 'section-one' );
    add_settings_field( 'field-client-secrete', 'Client Secrete', 'field_three_callback', 'postrank', 'section-one' );
    
}
function section_one_callback() {
    echo 'Insert client data.';
}
function field_one_callback() {
    $settings = (array) get_option( 'wpPostrankSetting' );
    $app_name = esc_attr( $settings['app_name'] );
    echo "<input type='text' name='wpPostrankSetting[app_name]' value='$app_name' />";
   
}
function field_two_callback() {
    global $client_id;
    $settings = (array) get_option( 'wpPostrankSetting' );
    
    $client_id= esc_attr( $settings['client_id'] );
    
    echo "<input type='text' name='wpPostrankSetting[client_id]' value='$client_id' />";
    
}

function field_three_callback() {
    $settings = (array) get_option( 'wpPostrankSetting' );
    $client_secrete = esc_attr( $settings['client_secrete'] );
    echo "<input type='text' name='wpPostrankSetting[client_secrete]' value='$client_secrete' />";
}
?>
