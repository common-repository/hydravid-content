<?php
/*
Plugin Name: Hydravid
Plugin URI: http://hydravid.com
Description: Posting the videos on your site.
Version: 0.51
Author: hydravid
Author URI: http://app.hydravid.com/
*/

register_activation_hook(__FILE__, 'hydravid_set_options');
register_deactivation_hook(__FILE__, 'hydravid_unset_options');

define('MSP_HYDRAVI_DIR', plugin_dir_path(__FILE__));
define('HYDRAVI_URL_API', "http://app.hydravid.com");
define('HYDRAVI__VERSION', '0.51');
define('HYDRAVI__TOKEN', 'Z24tzchNT0J7Mlo8V4deejX9U1o8zeD1');
add_action('admin_menu', 'hydravid_admin_page');

$myplugin_prefs_table = hydravid_get_table_handle();

$args = array(
    'body' => array(),
    'timeout' => '5',
    'redirection' => '8',
    'httpversion' => '1.0',
    'blocking' => true,
    'headers' => array(),
    'cookies' => array()
);

function hydravid_get_table_handle() {
    global $wpdb;
    return $wpdb->prefix . "myplug_prefs";
}

function hydravid_set_options() {
    global $wpdb;
    add_option('myplug_version', HYDRAVI__VERSION);
    add_option('hydravid_site', 0);
    $myplugin_prefs_table = hydravid_get_table_handle();
} 

function hydravid_unset_options() {
    global $wpdb, $myplugin_prefs_table;
    delete_option('myplug_version');
    delete_option('hydravid_site');
    $sql = "DROP TABLE $myplugin_prefs_table";
    $wpdb->query($sql);
}

function hydravid_admin_page() {
    add_options_page('Hydravid', 'Hydravid', 8, __FILE__, 'hydravid_options_page');
}

function hydravid_options_page() 
{
    global $wpdb, $myplugin_prefs_table;

    $myplugin_options = array('hydravid_site','username','ip');

    $cmd = $_POST['cmd'];
    foreach ($myplugin_options as $myplugin_opt) {
        $$myplugin_opt = get_option($myplugin_opt);
    }
    
    if($cmd == "register_hydravid")
    {
        $url = HYDRAVI_URL_API."/api/addsite";
        if(isset($_POST['login']) && isset($_POST['pass']) && $hydravid_site != 1)
        {
            $post['login'] = sanitize_email($_POST['login']);
            $post['pass'] = trim($_POST['pass']);
            if($post['login'] && $post['pass']){
                if(preg_match("/(^.*".$_SERVER['HTTP_HOST'].")/i", $_SERVER["HTTP_REFERER"], $preg)){
                    $post['url_plugin'] = $preg[1].$_SERVER['REQUEST_URI'];
                }
                else{
                    $post['url_plugin'] = $_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI'];
                } 

                $post['url_plugin'] = str_replace("%2F", "/", $post['url_plugin']);

                $args['body'] = $post;
                $response = wp_remote_post( $url, $args );
                if(!empty($response['response']['code']) && $response['response']['code'] == 200)
                {
                    if(!empty($response['body']) && preg_match("/\[status=1\]/i", $response['body'], $res))
                    {
                        update_option('hydravid_site', 1);
                        update_option('username', $post['login']);
                        $hydravid_site = 1;
                        $username = $post['login'];
                        echo hydravid_showMessage('Site activated.');   
                    }
                    else{
                        echo hydravid_showMessage('Site has not been registered.', 0);
                    }
                }
                else{
                    echo hydravid_showMessage('Site is temporarily not available.', 0);
                }
            }
        }
    }
    else if($cmd == "selected_category")
    {
        $categories = !empty($_POST['categories']) ? $_POST['categories'] : false;
        foreach ($categories as $key => $categorie) {
            $categories[$key] = sanitize_text_field($categorie);
        }
        if(preg_match("/(^.*".$_SERVER['HTTP_HOST'].")/i", $_SERVER["HTTP_REFERER"], $preg)){
            $url_plugin = $preg[1].$_SERVER['REQUEST_URI'];
        }
        else{
            $url_plugin = $_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI'];
        }
        $url_plugin = str_replace("%2F", "/", $url_plugin);

        if(preg_match("/^(http.*hydravid\.php)/", $url_plugin, $match))
        {
            $url_plugin = $match[1];

            $args['body'] = array(
                                'token' => HYDRAVI__TOKEN, 
                                'username' => $username, 
                                'categories' => $categories, 
                                'url_plugin' => $url_plugin
                            );

            $response = wp_remote_post( HYDRAVI_URL_API."/api/set_categories_for_wp", $args );
            if(!empty($response['response']['code']) && $response['response']['code'] == 200)
            {
                if(!empty($response['body']))
                {
                    $result = json_decode($response['body']);
                    if(!empty($result->status))
                    {
                        if($result->status == 1){
                            echo hydravid_showMessage($result->message);
                            $categories = !empty($categories) ? implode(",", $categories) : false;
                            $categories = sanitize_text_field($categories);
                            update_option('categories', $categories);
                        }
                        else
                            echo hydravid_showMessage($result->message, 0);
                    }
                    else{
                        echo hydravid_showMessage('Categories was not updated.', 0);
                    }
                }
                else{
                    echo hydravid_showMessage('No response.', 0);
                }
            }
            else{
                echo hydravid_showMessage('Site is temporarily not available.', 0);
            }
        }
        else{
            echo hydravid_showMessage('Categories was not updated. No response.', 0);
        }
    }
    
    if($hydravid_site)
    {
        $args['body'] = array('token' => HYDRAVI__TOKEN);
        $categories = array();
        $response = wp_remote_post( HYDRAVI_URL_API."/api/get_categories_for_plugin", $args );
        if(!empty($response['response']['code']) && $response['response']['code'] == 200)
        {
            if(!empty($response['body']))
            {
                $categories = json_decode($response['body']);
                if(!empty($categories))
                {
                    $active_categories = get_option('categories');
                    $active_categories = !empty($active_categories) ? explode(",", $active_categories) : array();
                    $args = array('hydravid_site' => $hydravid_site, 'username' => $username, 'categories' => $categories, 'active_categories' => $active_categories);
                    hydravid_view('config', $args);
                }
            }
        }
        if(empty($categories)){
            $args = array('hydravid_site' => $hydravid_site, 'username' => $username);
            hydravid_view('config',$args);
        }
    }
    else{
        $args = array('hydravid_site' => $hydravid_site, 'username' => $username);
        hydravid_view('config',$args);
    }
}
    
if ( is_admin() ) {
    $myplugin_options = array('ip');
    foreach ($myplugin_options as $myplugin_opt) {
        $$myplugin_opt = get_option($myplugin_opt);
    }
    
    if(isset($_POST['title'])){
        req_hyd($_POST);
    }
    elseif(isset($_POST['method'])){
        if($_POST['method'] === "getPostURL"){
            echo hydravid_getPost($_POST);
        }
    }
}

function hydravid_showMessage($message, $status = 1)
{
    if($status == 1)
        return '<div id="message" class="updated"><p><strong>'.__($message,'example_plugin').'</p></div>';
    else
        return '<div id="message" class="update-nag"><p><strong>'.__($message,'example_plugin').'</strong></p></div>';
}

add_filter( 'test_func', 'req_hyd' );
function req_hyd($post)
{
    if(!empty($post['title']) && !empty($post['description']) && !empty($post['video_url']) 
            && !empty($post['token']) && !empty($post['video_id']))
    {
        $post['title'] = sanitize_text_field( $post['title'] );
        $post['description'] = sanitize_text_field( $post['description'] );
        $post['token'] = sanitize_text_field( $post['token'] );
        $post['video_url'] = sanitize_text_field( $post['video_url'] );
        $post['video_id'] = intval($post['video_id']);

        $url = HYDRAVI_URL_API."/api/checkvideo";

        $args['body'] = array(
                            'sitename' => $_SERVER["HTTP_HOST"], 
                            'token' => $post['token'], 
                            'title' => $post['title'], 
                            'video_id' => $post['video_id']
                        );
        $response = wp_remote_post( $url, $args );

        if(!empty($response['response']['code']) && $response['response']['code'] == 200)
        {
            if(!empty($response['body']))
            {
                if(strpos($response['body'],"[status=1]") == false){
                    register_shutdown_function( "hydravid_fatal_handler" );
                    $Npost = hydravid_createPost($post);
                    if($Npost > 0)
                    {
                        $npost = get_posts($Npost);
                        if(count($npost))
                            $res = '{'.$npost->guid.'}';
                    }
                }
            }
        }
    }
    return true;
}
function hydravid_fatal_handler() {
    $errfile = "unknown file";
    $errstr  = "shutdown";
    $errno   = E_CORE_ERROR;
    $errline = 0;
    
    $error = error_get_last();
    if($error){
        $postAPI['res_title'][] = sanitize_text_field( $_POST['title'] );
        echo hydravid_getPost($postAPI);
    }
}
function hydravid_getPost($postAPI)
{
    $post = get_posts();
    $res = '{'; 
    $is_post = false;
    foreach($postAPI['res_title'] as $title){
        $title = sanitize_text_field( $title );
        foreach($post as $pos){
            if(trim($title) === trim($pos->post_title)){
                if(!$is_post){
                    $is_post = true;
                    $res .= $pos->guid;
                }
                else{
                    $res .= "|".$pos->guid;
                }
                break;
            }
        }
    }
    $res .= ($is_post) ? "}" : '';
    return $res;
}

function hydravid_createPost($post)
{
    require_once(ABSPATH . 'wp-admin/includes/taxonomy.php');
    
    $wp_cat_id = array();
    if(!empty($post['categories']))
    {
        foreach ($post['categories'] as $category) {
            $wp_cat_id[] = wp_create_category($category);
        }
    }

    $post_name = hydravid_translitIt($post['title']);
    if(preg_match("/amazonaws\.com/i",$post['video_url'])){
        $url = '[hydvideo]'.$post['video_url'].'[/hydvideo]';
    }
    elseif(preg_match("/(www\.youtube\.com).*\?v\=([a-z\d]+)/ims",$post['video_url'], $res)){
        $url = '<iframe width="480" height="360" src="//'.$res[1].'/embed/'.$res[2].'" frameborder="0" allowfullscreen></iframe>';
    }
    else{
        $url = $post['video_url'];
    }
    $source = array(
        'post_title' => $post['title'],              
        'post_name' => $post_name,     
        'post_excerpt' => '',             
        'post_content' => '<p>'.$post['description'].'</p><p>'.$url.'</p>',  
        'post_status' => 'publish',                      
        'post_author' => 1,                              
        'post_type' => 'post',                           
        'post_category' => $wp_cat_id,              
        'tags_input' => '',  
        'comment_status' => 'open'                       
    );
    echo "POSTING EXCUTE SUCCESSFULL"; 
    $insert_post_id = wp_insert_post($source);
    return $insert_post_id;
}

function hydravid_translitIt($str) 
{
    $str = preg_replace("/[^A-z0-9]+/"," ",$str);
    $tr = array(
        "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
        "Д"=>"D","Е"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
        "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
        "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
        "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
        "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"",
        "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
        "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
        "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
        "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
        "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
        "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
        "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya", " " => "_"
    );
    return strtr($str,$tr);
}
        
function hydvideo_shortcode ($atts, $url = null)
{
    if(!$url)   return '';
    return '<video  src="'.$url.'" style="width: 100%;" controls></video>';                     
}

add_shortcode('hydvideo', 'hydvideo_shortcode');

function hydravid_view( $name, array $args = array() ) {
    $args = apply_filters( 'hydravid_view_arguments', $args, $name );
    foreach ( $args AS $key => $val ) {
            $$key = $val;
    }
    $file = MSP_HYDRAVI_DIR . 'views/'. $name . '.php';
    include( $file );
}

add_filter('the_content','make_clickable',12);

?>