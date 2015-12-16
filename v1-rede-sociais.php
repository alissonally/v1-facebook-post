<?php
/*
Plugin Name: V1 Rede Sociais
Description: Plugin para auto publicar post em páginas do facebook
Plugin URI: http://#
Author: Alisson Araujo
Author URI: http://alissonaraujo.com.br
Version: 1.0
License: GPL2
Text Domain: Text Domain
Domain Path: Domain Path
*/

/*

    Copyright (C) Year  Author  Email

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

session_start();
define('XYZ_FBAP_FB_API_VERSION','v2.0');

define('XYZ_FBAP_FB_api','https://api.facebook.com/'.XYZ_FBAP_FB_API_VERSION.'/');
define('XYZ_FBAP_FB_api_video','https://api-video.facebook.com/'.XYZ_FBAP_FB_API_VERSION.'/');
define('XYZ_FBAP_FB_api_read','https://api-read.facebook.com/'.XYZ_FBAP_FB_API_VERSION.'/');
define('XYZ_FBAP_FB_graph','https://graph.facebook.com/'.XYZ_FBAP_FB_API_VERSION.'/');
define('XYZ_FBAP_FB_graph_video','https://graph-video.facebook.com/'.XYZ_FBAP_FB_API_VERSION.'/');
define('XYZ_FBAP_FB_www','https://www.facebook.com/'.XYZ_FBAP_FB_API_VERSION.'/');

require_once( dirname( __FILE__ ) . '/api/facebook.php' );
require_once( dirname( __FILE__ ) . '/classes/instagram/instagram.php' );
require_once( dirname( __FILE__ ) . '/admin/options_instagram.php' );
require_once( dirname( __FILE__ ) . '/admin/main.php' );
require_once( dirname( __FILE__ ) . '/admin/option_fb_post.php' );


function scripts_plugin_fb(){
    $url = plugins_url( 'assets/', __FILE__ );
    wp_enqueue_script( 'fbp-admin', $url . 'js/funcions_plugin.js', array('jquery'), null, true );
}
add_action( 'admin_enqueue_scripts', 'scripts_plugin_fb' );

function publish_fb_post($id){

    if ( wp_is_post_revision( $id ) || get_post_meta($id, '_publicar_fb_post', true ) !=='1' )
        return;

    if( get_post_meta($id, '_publicar_fb_post', true ) == '1'){

        $options = new stdclass();
        $options->app_secret =  get_option('fbp_app_secret');
        $options->app_id =  get_option('fbp_app_id');
        $options->fb_post_token =  get_option('fb_post_token');
        $options->page = get_option('fb_post_pages_ids');
        $options->array_token_page = explode('-', $options->page);
        $page = $options->array_token_page[0];
        $acces_token = $options->array_token_page[1];

        $p = get_post($id);
        $link = get_permalink($id);
        $name = get_the_title($p->ID);
        $fb= new FBAPFacebook(array(
                'appId'  => $options->fb_post_token,
                'secret' => $options->app_secret,
                'cookie' => true
        ));
        $disp_type="feed";

        if(get_post_meta( $id, '_on_facebook', 1 ) ==''){
            add_post_meta( $id, '_on_facebook', 0 );
        }

        $attachment = array(
            'message' => '',
            'access_token' => $acces_token,
            'link' => $link,
            'name' => $name,
            'caption' => 'portal v1',
            'description' => 'Notícias de Valença e Região',
            'actions' => array(
                array(
                    'name' => $name,
                    'link' => $link
                    )
                ),
        );
        try{
            $result = $fb->api('/'.$page.'/'.$disp_type.'/', 'post', $attachment);
            update_post_meta( $id, '_on_facebook', 1 );
        } catch(Exception $e){
            $fb_publish_status[$page."/".$disp_type]=$e->getMessage();
            wp_die($e->getMessage() );
            update_post_meta( $id, '_on_facebook', 0 );
        }

        if(count($fb_publish_status)>0)
            $fb_publish_status_insert=serialize($fb_publish_status);
        else
            $fb_publish_status_insert=1;
        
        $time=time();
        $post_fb_options=array(
                'postid'    =>  $id,
                'acc_type'  =>  "Facebook",
                'publishtime'   =>  $time,
                'status'    =>  $fb_publish_status_insert
        );

        $update_opt_array=array();
                
        $arr_retrive=(get_option('fb_post_logs'));
        
        $update_opt_array[0]=isset($arr_retrive[0]) ? $arr_retrive[0] : '';
        $update_opt_array[1]=isset($arr_retrive[1]) ? $arr_retrive[1] : '';
        $update_opt_array[2]=isset($arr_retrive[2]) ? $arr_retrive[2] : '';
        $update_opt_array[3]=isset($arr_retrive[3]) ? $arr_retrive[3] : '';
        $update_opt_array[4]=isset($arr_retrive[4]) ? $arr_retrive[4] : '';
        
        array_shift($update_opt_array);
        array_push($update_opt_array,$post_fb_options);
        update_option('fb_post_logs', $update_opt_array);
    }
}
add_action('save_post', 'publish_fb_post');


function publish_instagram($id){
    if ( wp_is_post_revision( $id ) || get_post_meta($id, '_publicar_inst_post', true ) !=='1' )
        return;

    $options = new stdclass();
    $options->login_instagram =  get_option('login_instagram');
    $options->senha_instagram =  get_option('senha_instagram');

    if( get_post_meta($id, '_publicar_inst_post', true ) == '1'){
        if(get_post_meta( $id, '_on_instagram', true ) == ''){
            add_post_meta( $id, '_on_instagram', 0 );
        }
        if(has_post_thumbnail($id)){
            $path = get_attached_file( get_post_thumbnail_id($id));
            try {
                new \classes\instagram\UploadPhoto(['login'=>$options->login_instagram,'password'=> $options->senha_instagram] ,$path, get_the_title($id).'-'.wp_get_shortlink($id) );
                update_post_meta( $id, '_on_instagram', 1 );
            } catch (Exception $e) {
                //set_transient( "erro_image", '<div id="message" class="error"><p><strong>Instagram: </strong>'.$e->getMessage().'</p></div>' );     
                set_transient( "erro_image", $e->getMessage() );     
                update_post_meta( $id, '_on_instagram', 0 );
                //add_filter( 'redirect_post_location', create_function( '$location','return add_query_arg("message", "15", $location);' ) );
            }
        } else {
            set_transient( "erro_image", 'É necessário uma imagem destacada para o instagram' );
        }
    }
}

add_action('save_post', 'publish_instagram');
