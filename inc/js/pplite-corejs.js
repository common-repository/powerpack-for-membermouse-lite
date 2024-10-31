jQuery(document).ready(function($) {

	CheckMMBoxes();

	// Display/Hide Admin Menus

	$(".tablinks").click(function() {
		var currcity = "#" + $(this).attr('data-menu');

		$(".tabcontent").css('display','none');
		$(currcity).css('display','block');
		$(".tablinks").removeClass('active');
		$(this).addClass('active');
	});


	// Display/Hide Divs

	$(".mmboxdisplay, input[name='powerpack-plugin-options[logout-redirect]']").change(function(){
		CheckMMBoxes();
	});

	// Initialize Select2 Functionality
	$('.js-select2').select2();


	function CheckMMBoxes() {
		$(".mmboxdisplay").each(function () {
			var currdiv = $(this).attr('data-div');

		if ($(this).is(":checked")) {
			$("#" + currdiv).show();
		} else {
			$("#" + currdiv).hide();
		}
		});
	}
});