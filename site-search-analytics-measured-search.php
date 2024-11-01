<?php
/*
Plugin Name: Site Search Analytics by Measured Search
Plugin URI: http://measuredsearch.com
Description: Find how users are interacting with your wordpress search, discover new content opportunities and give users what they are looking for.
Author: Measured Search  
Version: 1.1
Author URI: http://measuredsearch.com
*/



/*
 *  Function Name: wdm_MS_admin_menu_func
 *  Description: This function is used to add setting page in dashboard
 **/

add_action( 'admin_menu', 'wdm_MS_admin_menu_func' );
function wdm_MS_admin_menu_func(){
    add_menu_page('Measured Search', 'Measured Search', 'administrator', 'wdm_MS_setting_page', 'wdm_MS_setting_page_func','',100);
}

/*
 *  Function Name: wdm_MS_setting_page_func
 *  Description: This function is used to display contents in setting page
 **/

function wdm_MS_setting_page_func(){
    wp_enqueue_style('wdm_MS_style', plugins_url('css/bootstrap.css', __FILE__));
    if(isset($_POST['wdm_ms_API_key']) && check_admin_referer('wdm_ms_API_Key','wdm_ms_api_nonce')){
        update_option('wdm_ms_API_key',$_POST['wdm_ms_API_key']);
        echo "<div id='permissions-warning' class='updated settings-error'><p>API Key saved successfully.</p></div>";
    }
    $api_key = get_option('wdm_ms_API_key');
    ?>
    <div class="wrap">
        <img src="<?php echo plugins_url('/img/measuredsearch_logo.png', __FILE__); ?>" alt="Measured Search" />
        <br />
        <br />
        <h2>Add Your API Key</h2>
        <p style="color: #464646; padding-bottom: 25px;"><small><em>If you have created your own Measured Search account, add your API Key to the field below and hit save. <br />If you don't have an API key, you can get one by signing up here - <a href="http://measuredsearch.com/signup">http://measuredsearch.com/signup</a></em></small></p>
        <div class="inside">
            <form action="<?php echo $_SERVER['REQUEST_URI']?>" method="post">
            <?php wp_nonce_field('wdm_ms_API_Key','wdm_ms_api_nonce'); ?>
                <div class="input-prepend input-append">
                    <span class="add-on"><i class="icon-edit"></i></span>
                    <input type="text" class="input-xlarge" id="appendedPrependedInput" name="wdm_ms_API_key"value="<?php echo isset($api_key) ? $api_key : ''; ?>" size="50" placeholder="XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"/></td>
                    <input type="submit" value="Save" class="btn btn-success"/>
                </div>
                <div><small style="line-height: 20px; color: #999999;"><em>API Key looks like this: ma9RxPI2Tiv3FPwqy7mT4oK6NQ9k3Mx8</em></small></div>
            </form>
        </div>
    </div>
    <?php
    
}

/*
 *  Function Name: wdm_MS_script_insertion_func
 *  Description: This function is used to insert code contents in footer of search page
 **/

add_action('wp_footer', 'wdm_MS_script_insertion_func');
function wdm_MS_script_insertion_func(){
    global $current_user, $wp_query;
    session_start();
    $current_user = wp_get_current_user();
    if(is_search()){
        $api_key = get_option('wdm_ms_API_key');
        $user_id = $current_user->ID;
        $session_id = session_id();
        $query_string = get_search_query();//the_search_query();
        $shownHits = $wp_query->post_count;
        $totalHits = $wp_query->found_posts;
        $pageno = max( 1, get_query_var('paged') );
        $latency = timer_stop(1)*1000;
        $snippet = "<!-- MeasuredSearch Tracking Code -->
        
        <script type=\"text/javascript\">
        var _msq = _msq || []; //declare object
        _msq.push(['track', {
            key: \"{$api_key}\",
            user: \"{$user_id}\",
            session: \"{$session_id}\",
            query: \"{$query_string}\",
            shownHits: {$shownHits},
            totalHits: {$totalHits},
            latency: {$latency},
            pageNo: {$pageno}
        }]);
        (function() {
            var ms = document.createElement('script'); ms.type = 'text/javascript';
            ms.src = ('https:' == document.location.protocol ? 'https://www.' : 'http://') + 'measuredsearch.com/static/js/ms.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ms, s);
        })();
        </script>
        
        <!-- MeasuredSearch Tracking Code End -->";
    }
    
    echo $snippet;
}
