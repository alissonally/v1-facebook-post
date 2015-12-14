<?php 


function menu_facebook_post() {
    if (function_exists('add_menu_page')) {
        add_options_page('Configurações Facebook Post ', 'Facebook Post', 'manage_options', 'facebook-post', 'view_facebook_post');
    }
}
add_action('admin_menu', 'menu_facebook_post');


function view_facebook_post() {
	$options = new stdclass();
	$options->app_secret =  get_option('fbp_app_secret');
	$options->app_id =  get_option('fbp_app_id');
	$options->fb_post_token =  get_option('fb_post_token');
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
		$form = '<form method="post" action="'.admin_url('admin-ajax.php?action=send_page_token').'">';
		$form .='<select name="pagina"id="pagina">';
		$form .='<option value="">Selecionar página</option>';
		foreach ($data as $key => $value) {
			$form .='<option value="'.$value->id.'-'.$value->access_token.'">'.$value->name.'</option>';
		}
		$form .='</select>';
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
	add_meta_box( 'fb_post', 'Publicar post no facebook', 'box_fbp_post', 'post', 'normal', 'high' );
}

function box_fbp_post( $post )
{
	$values = get_post_custom( $post->ID );	
	$check = isset( $values['_publicar_fb_post'] ) ? esc_attr( $values['_publicar_fb_post'][0] ) : '';
	wp_nonce_field( 'publicar_fb_post', 'publicar_fb_post_nonce' );
	?>
	<style>
		#campos_extras input[type="text"]{width:50%; display:block; margin:5px 0; padding:3px 2px}
		#campos_extras small{font-size:11px; color:#999}
		span.campos{display:block; margin:10px 0;}
	</style>
	<div id="campos_extras">
		<?php if(current_user_can('administrator')){ ?>
		
		<label for="fb_post">
			<span class="campos">
				<input type="checkbox" name="_publicar_fb_post" id="_publicar_fb_post" value="1" <?php checked( $check, '_publicar_fb_post' ); ?> />
				<label>Publicar no facebook?</label>
				<br>
				<span class="description">Marque para publicar post no facebook</span>
			</span>
		</label>
		<?php } 
}

add_action( 'save_post', 'meta_box_publish_fb_post' );
function meta_box_publish_fb_post( $post_id )
{
	var_dump($_POST); die();	
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

	if( !isset( $_POST['publicar_fb_post_nonce'] ) || !wp_verify_nonce( $_POST['publicar_fb_post_nonce'], 'publicar_fb_post' ) ) return;

	if( !current_user_can( 'edit_post' ) ) return;

	$allowed = array( 
		'a' => array( 
			'href' => array() 
		)
	);

	if( isset( $_POST['_publicar_fb_post'] ) )
		update_post_meta( $post_id, '_publicar_fb_post', wp_kses( $_POST['_publicar_fb_post'], $allowed ) );
	
	echo '</div>';	
}