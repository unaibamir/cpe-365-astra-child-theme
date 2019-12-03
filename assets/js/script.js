jQuery(function( $ ){
	$(document).ready(function() {
		
		$('.course-tooltip').tooltipster({
			delay: 100,
			maxWidth: 625,
			speed: 300,
			interactive: true,
			side: 'right',        
			trigger: 'hover',
			contentAsHTML: true,
		});
	});


	$('.course-list-table').DataTable({
		searching: false, 
		paging: false, 
		info: false
	});

	$('body.page-template-user-dashboard-template table').addClass("table");

});