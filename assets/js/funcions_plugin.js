(function( $ ){
	'use strict';

	$('#senha_instagram').change(function(event) {
		var login = $('#login_instagram').val();
		var senha = $(this).val();
		if(login !='' && senha !=''){
			$('#msg-login').html('<div class="notice">Verificando login. Aguarde...</div>');
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {action: 'check/instagram', login:login, senha:senha},
				success:function(response){
					var html ='' 
					if(typeof response.status !='undefined' && response.status =='fail'){
						html = '<div id="message" class="error ">'+response.message+'</div>';
					} else if(typeof response.status !='undefined' && response.status =='ok'){
						html = '<div id="message" class="update notice">'+response.message+'</div>';
						$('#salvar-insta').prop('disabled',false);
					}
					$('#msg-login').html(html);
				}
			})
		}
	});
	$(window).load(function(){
		if(typeof instagram_msg !='undefined' && instagram_msg.msg !=''){
			$('#erro_insta').html('<div id="message" class="error notice">'+instagram_msg.msg+'</div>');
			$('.campos_instagram').css({
				background: 'rgb(243, 193, 193)',
    			display: 'block',
    			padding: '0 4px 2px 2px'
    		});
		}
	});
}( jQuery ));