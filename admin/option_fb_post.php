<?php 

function salva_options(){
	if (isset($_POST['fb_post_nonce']) || wp_verify_nonce($_POST['fb_post_nonce'],'fb_post') ){

		if(!empty($_POST['app_id']) && !empty($_POST['app_secret']) ){
			update_option('fbp_app_id',  $_POST['app_id']);
			$_SESSION['success'] = 'Salvo com sucesso';
			update_option('fbp_app_secret',  $_POST['app_secret']);
			wp_redirect($_POST['_wp_http_referer']);
		} else {
			$_SESSION['erro'] = 'Preencha todos os campos';
			wp_redirect($_POST['_wp_http_referer']);
		}
	}
	die();
}

add_action('wp_ajax_send_setting_fb', 'salva_options');

function autorize_app(){
	$options = new stdclass();
	$options->app_secret =  get_option('fbp_app_secret');
	$options->app_id =  get_option('fbp_app_id');
	$redirecturl=admin_url('options-general.php?page=facebook-post&auth=1');
	$lnredirecturl=admin_url('options-general.php?page=facebook-post&auth=3');

	$my_url=urlencode($redirecturl);
	$code = "";
	if(isset($_REQUEST['code']))
		$code = $_REQUEST['code'];

	if (isset($_POST['fb_post_autorize_nonce']) && wp_verify_nonce($_POST['fb_post_autorize_nonce'],'fb_post_autorize') ){
		var_dump($_POST);
		$state_request_fb_post = md5(uniqid(rand(), TRUE));
		setcookie("state_request_fb_post",$state_request_fb_post,"0","/");
		
		$dialog_url = "https://www.facebook.com/v2.0/dialog/oauth?client_id="
				. $options->app_id . "&redirect_uri=" . $my_url . "&state="
				. $state_request_fb_post . "&scope=email,public_profile,publish_pages,user_posts,publish_actions,manage_pages";
		
		wp_redirect($dialog_url);
	}	

	die();
}
add_action('wp_ajax_send_autorize_fb', 'autorize_app');


function get_info_fb($app_id, $app_secret){
	
	$redirecturl=admin_url('options-general.php?page=facebook-post&auth=1');
	$lnredirecturl=admin_url('options-general.php?page=facebook-post&auth=3');

	$my_url=urlencode($redirecturl);
	$code = "";

	if(isset($_REQUEST['code']))
		$code = $_REQUEST['code'];



	if(isset($_COOKIE['state_request_fb_post']) && isset($_REQUEST['state']) && ($_COOKIE['state_request_fb_post'] === $_REQUEST['state'])) {
	
		$token_url = "https://graph.facebook.com/v2.0/oauth/access_token?"
		. "client_id=" . $app_id . "&redirect_uri=" . $my_url
		. "&client_secret=" . $app_secret . "&code=" . $code;
		
		$params = null;$access_token="";
		$response = wp_remote_get($token_url);
		
		if(is_array($response))
		{
			if(isset($response['body']))
			{
				parse_str($response['body'], $params);
				if(isset($params['access_token']))
				$access_token = $params['access_token'];
			}
		}
		
		if($access_token!="")
		{
			
			
			update_option('fb_post_token',$access_token);
			update_option('fb_post_af',0);

			$offset=0;$limit=100;
			$data=array();
			$fbid=get_option('fb_post_fb_id');
			do
			{
				$result1="";$pagearray1="";
				$pp=wp_remote_get("https://graph.facebook.com/v2.0/me/accounts?access_token=$access_token&limit=$limit&offset=$offset");
				
				if(is_array($pp))
				{
					$result1=$pp['body'];
					$pagearray1 = json_decode($result1);
					if(is_array($pagearray1->data))
						$data = array_merge($data, $pagearray1->data);
				}
				else
					break;
				$offset += $limit;
			}while(isset($pagearray1->paging->next));
				
				
			$count=count($data);
				
			$fbap_pages_ids1=get_option('fb_post_pages_ids');
			$fbap_pages_ids0=array();$newpgs="";
			if($fbap_pages_ids1!="")
				$fbap_pages_ids0=explode(",",$fbap_pages_ids1);
			
			$fbap_pages_ids=array();$profile_flg=0;
			for($i=0;$i<count($fbap_pages_ids0);$i++)
			{
			if($fbap_pages_ids0[$i]!="-1")
				$fbap_pages_ids[$i]=trim(substr($fbap_pages_ids0[$i],0,strpos($fbap_pages_ids0[$i],"-")));
				else{
				$fbap_pages_ids[$i]=$fbap_pages_ids0[$i];$profile_flg=1;
				}
			}
			
			
			for($i=0;$i<$count;$i++)
			{
			if(in_array($data[$i]->id, $fbap_pages_ids))
				$newpgs.=$data[$i]->id."-".$data[$i]->access_token.",";
			}
			$newpgs=rtrim($newpgs,",");
			if($profile_flg==1)
				$newpgs=$newpgs.",-1";
			update_option('fb_post_pages_ids',$newpgs);
		}
		else
		{
			
			$fb_post_af=get_option('fb_post_af');
			
			if($fb_post_af==1){
				wp_redirect($lnredirecturl);
				exit();
			}
		}
	}
	else {
		//header("Location:".admin_url('admin.php?page=facebook-auto-publish-settings&msg=2'));
		//exit();
	}
}


function salva_pagina(){

	if(isset($_POST['pagina']) &&  !empty($_POST['pagina']) ){
		update_option('fb_post_pages_ids', $_POST['pagina'] );
		wp_redirect(admin_url('options-general.php?page=facebook-post'));
	}
	die();
}
add_action('wp_ajax_send_page_token', 'salva_pagina');