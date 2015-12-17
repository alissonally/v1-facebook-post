<?php 


function menu_social_post() {
    if (function_exists('add_menu_page')) {
        add_options_page('Configurações Post ', 'Social Post', 'manage_options', 'social-post', 'view_social_post');
    }
}
add_action('admin_menu', 'menu_social_post');


function view_social_post() {
	$options = new stdclass();
	$options->app_secret =  get_option('fbp_app_secret');
	$options->app_id =  get_option('fbp_app_id');
	$options->fb_post_token =  get_option('fb_post_token');
	$options->login_instagram =  get_option('login_instagram');
	$options->senha_instagram =  get_option('senha_instagram');
	

	include 'templates/admin.php';       
}

function list_pages(){
	$fb_post_token=get_option('fb_post_token');
	if($fb_post_token!=""){

		$offset=0;$limit=100;$data=array();
		do
		{
			$result1="";
			$pagearray1="";
			$pp=wp_remote_get("https://graph.facebook.com/v2.0/me/accounts?access_token=$fb_post_token&limit=$limit&offset=$offset");
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

	}
	if($data){
		$page = get_option('fb_post_pages_ids' );
		$page = explode('-', $page);
		
		
		$form  ='<form method="post" action="'.admin_url('admin-ajax.php?action=send_page_token').'">';
		$form .='<table class="form-table">';
		$form .='<tr valign="top">';
		$form .='<th scope="row">Páginas do facebook: </th>';
		$form .='<td><select name="pagina"id="pagina">';
		$form .='<option value="">Selecionar página</option>';
		foreach ($data as $key => $value) {
			if($page[0] == $value->id){
				$selected = "selected='selected'";
			} else {
				$selected = '';
			}
			$form .='<option value="'.$value->id.'-'.$value->access_token.'" '.$selected.'>'.$value->name.'</option>';
		}
		$form .='</select></td>';
		$form .='</tr>';
		$form .='</table>';
		$form .='<p class="submit" style="clear: both;">
					<input type="submit" name="salvar"  class="button-primary" value="Salvar" />			
				</p>';
		$form .='</form>';
		echo $form;
	}
}

/**
 * Meta Box
 */

global $current_user;
	
add_action( 'add_meta_boxes', 'fbp_meta_box_add' );
function fbp_meta_box_add()
{
	add_meta_box( 'fb_post', 'Publicar post no facebook e instagram', 'box_fbp_post', 'post', 'normal', 'low' );
}

function box_fbp_post( $post )
{
	$values = get_post_custom( $post->ID );	
	$check = isset( $values['_on_facebook'] ) ? esc_attr( $values['_on_facebook'][0] ) : '';
	$check_instagram = isset( $values['_on_instagram'] ) ? esc_attr( $values['_on_instagram'][0] ) : '';
	wp_nonce_field( 'publicar_fb_post', 'publicar_fb_post_nonce' );
	
	?>
	<style>
		#campos_extras input[type="text"]{width:50%; display:block; margin:5px 0; padding:3px 2px}
		#campos_extras small{font-size:11px; color:#999}
		span.campos{display:block; margin:10px 0;}
		.publish_post_fb {background: #CDE0CF;padding: 15px;color: #43802A;}
	</style>
	<div id="fb_post">

		<?php 
		if($check){
			echo '<div class="publish_post_fb"><span class="dashicons dashicons-yes"></span> Publicado no Facebook</div>';
		}

		if(current_user_can('administrator')){ ?>
		
		<label for="fb_post">
			<span class="campos">
				<input type="radio" name="_publicar_fb_post" id="_publicar_fb_post_yes" <?php checked($check, '0' ); ?> value="1" />
				<label for="_publicar_fb_post_yes">Publicar</label>
				<br>
				<input type="radio" name="_publicar_fb_post" id="_publicar_fb_post_no" <?php checked($check, '1' ); ?> value="0" />
				<label for="_publicar_fb_post_no">Não publicar</label>
				<br>
				<span class="description">Marque para publicar post no facebook</span>
			</span>
			<hr>
			<?php 
			if($check_instagram){
				echo '<div class="publish_post_fb"><span class="dashicons dashicons-yes"></span> Publicado no Instagram</div>';
			} ?>
			<span class="campos_instagram">
				<input type="radio" name="_publicar_inst_post" id="_publicar_inst_post_yes" <?php checked($check_instagram, '0' ); ?> value="1" />
				<label for="_publicar_inst_post_yes">Publicar</label>
				<br>
				<input type="radio" name="_publicar_inst_post" id="_publicar_inst_post_no" <?php checked($check_instagram, '1' ); ?> value="0" />
				<label for="_publicar_inst_post_no">Não publicar</label>
				<br>
				<span class="description">Marque para publicar post no instagram</span>
				<div id="erro_insta"></div>
			</span>
		</label>
		<?php } 
		if(get_transient( "erro_image" )) { 
		    ?>
		    <script type="text/javascript">
		    	var instagram_msg = {
		    		msg:"<?php echo get_transient( "erro_image" ); ?>",
		    	} 
		    </script>
		    <?php
		    
		    delete_transient( "erro_image"); 
		}
	echo '</div>';	
}

add_action( 'save_post', 'meta_box_publish_fb_post' );
function meta_box_publish_fb_post( $post_id )
{
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

	if( !isset( $_POST['publicar_fb_post_nonce'] ) || !wp_verify_nonce( $_POST['publicar_fb_post_nonce'], 'publicar_fb_post' ) ) return;

	if( !current_user_can( 'edit_post' ) ) return;

	$allowed = array( 
		'a' => array( 
			'href' => array() 
		)
	);

	update_post_meta( $post_id, '_publicar_fb_post', wp_kses( $_POST['_publicar_fb_post'], $allowed ) );	
	update_post_meta( $post_id, '_publicar_inst_post', wp_kses( $_POST['_publicar_inst_post'], $allowed ) );	
}