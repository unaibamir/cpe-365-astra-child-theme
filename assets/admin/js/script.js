jQuery(function( $ ){
	$(document).ready(function() {
		
		$('.update-user-credits').click(function(){
			
			var self 			= $(this);
			var user_id 		= self.attr('data-user_id');
			var user_credits 	= self.prev().val();

			self.next().show();

			var data = {
				'user_id' 		: user_id,
				'user_credits' 	: user_credits,
				'action'		: 'update_user_credits'
			};
			
			$.post(ajaxurl, data, function(response) {
				setTimeout(function(){ self.next().hide(); }, 500);
			});
			
		});
	});

});