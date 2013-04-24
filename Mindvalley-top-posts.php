<?php

/*
Plugin Name: Mindvalley Top Post Ranks
Plugin URI: http://www.example.com
Description: My first of many plugins to rank most viewed pages recived from google analytics
Author: Inah Afen
Version: 1.00
Author URI: http://www.hire-inah.tk
 */
add_action('admin_menu','mindvalley_post_rank');
function mindvalley_post_rank(){
    add_options_page('TopPosts', 'Top Posts Rank','manage_options','_FILE_','postrank_admin');
}
function postrank_admin(){
?>
<div class="wrap">
    <h1>MindValley Top 10 Post Plugin</h1>
    <h4>Setup Google analytic to retrieve the top 10 pages</h4> 
    <h2>Demo</h2>
        <p>Use your Google Analytics account credentials.</p>
        <form method="post" action="">
            <label for="username">E-mail</label><input type="text" name="username" id="username">
            <label for="password">Password</label><input type="password" name="password" id="password">
            <label for="profileId">IDS</label><input type="text" name="profileId" id="profileId">
            <input type="submit" id="submit" value="Log in">
        </form>
</div>
<?php
}
?>
