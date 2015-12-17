<div class="wrap">
   <h2 style='margin:0 0 15px'> Configurações</h2>
   <?php 
   	  if(isset($_SESSION['erro']))	
   	  	 echo '<div id="message" class="error notice is-dismissible"><p>'.$_SESSION['erro'].'</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dispensar este aviso.</span></button></div>';
   	  	if(isset($_SESSION['success']))	
   	  	 echo '<div id="message" class="updated notice is-dismissible"><p>'.$_SESSION['success'].'</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dispensar este aviso.</span></button></div>';

   	  	unset($_SESSION['erro']);
   	  	unset($_SESSION['success']);
   ?>
   <hr>
	<h2 style='margin:0 0 15px'> Configurações do Facebook </h2>
   <form method="post" action="<?php echo admin_url('admin-ajax.php?action=send_setting_fb')?>">
		<table class="form-table">
		   <tr valign="top">
				<th scope="row">App ID: </th>
				<td><input type="text" name="app_id" value="<?php echo !empty($options->app_id)  ? $options->app_id:''?>" size="50"/></td>	   
		   </tr>
		   <tr valign="top">
				<th scope="row">App Secret: </th>
				<td><input type="text" name="app_secret" value="<?php echo !empty($options->app_secret)  ? $options->app_secret:''?>" size="50"/></td>	   
		   </tr>
		</table>
	   <?php wp_nonce_field('fb_post','fb_post_nonce'); ?>
		<p class="submit" style="clear: both;">
			<input type="submit" name="Submit"  class="button-primary" value="Salvar" />			
		</p>
   </form>
   <?php if( !empty($options->app_id) &&  !empty($options->app_secret)){?>
		<hr>
		<form method="post" action="<?php echo admin_url('admin-ajax.php?action=send_autorize_fb')?>">
			<p class="submit" style="clear: both;">
				<input type="submit" name="autorizar"  class="button-primary" value="Autorizar facebook" />			
			</p>
			<?php wp_nonce_field('fb_post_autorize','fb_post_autorize_nonce'); ?>
		</form>
		<?php 
			if(empty($options->fb_post_token))
				get_info_fb($options->app_id, $options->app_secret); 
		?>
   <?php } ?>
   <?php list_pages()?>
   <hr>
	<h2 style='margin:0 0 15px'> Configurações do Instagram </h2>
   <form method="post" action="<?php echo admin_url('admin-ajax.php?action=save/instagram')?>">
		<table class="form-table">
		   <tr valign="top">
				<th scope="row">Usuário: </th>
				<td><input type="text" id="login_instagram" name="login_instagram" value="<?php echo !empty($options->login_instagram)  ? $options->login_instagram:''?>" size="50"/></td>	   
		   </tr>
		   <tr valign="top">
				<th scope="row">Senha: </th>
				<td><input type="password" id="senha_instagram" name="senha_instagram" value="<?php echo !empty($options->senha_instagram)  ? $options->senha_instagram:''?>" size="50"/></td>	   
		   </tr>
	   </table>
	   <div id="msg-login"></div>
	   <?php wp_nonce_field('instagram_post','instagram_post_nonce'); ?>
		<p class="submit" style="clear: both;">
			<input type="submit" name="Submit" id="salvar-insta" disabled="disabled" class="button-primary" value="Salvar" />			
		</p>
   </form>
</div>