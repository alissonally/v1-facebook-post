<?php 


function save_data_user_instagram(){
	if (isset($_POST['instagram_post_nonce']) || wp_verify_nonce($_POST['instagram_post_nonce'],'instagram_post') ){
		if(!empty($_POST['login_instagram']) && !empty($_POST['senha_instagram']) ){
			update_option('login_instagram',  $_POST['login_instagram']);
			update_option('senha_instagram',  $_POST['senha_instagram']);
			$_SESSION['success_insta'] = 'Salvo com sucesso';
			wp_redirect($_POST['_wp_http_referer']);
		} else {
			$_SESSION['erro_insta'] = 'Preencha todos os campos';
			wp_redirect($_POST['_wp_http_referer']);
		}
	}
	wp_die();
}
add_action('wp_ajax_save/instagram', 'save_data_user_instagram');



function check_login(){
	try {
		new \classes\instagram\UploadPhoto(['login'=>'franciscoally','password'=> 'keyblog'] ,$fullsize_path,"Example http://portalv1.com.br/pbf");
		echo wp_send_json(['status'=>'ok','message'=>$upload->text]);
	} catch (Exception $e) {
		echo wp_send_json(['status'=>'fail','message'=>$e->getMessage()]);
	}
	wp_die();
}

add_action('wp_ajax_check/instagram', 'check_login');

