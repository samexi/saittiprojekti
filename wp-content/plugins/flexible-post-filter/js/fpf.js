jQuery.noConflict();

jQuery(document).ready(function($) {

  // get the action filter option item on page load
  var jQueryfilterType = $('.filterOptions li.active a').attr('class');

	
  // get and assign the ourHolder element to the
	// jQueryholder varible for use later
  var jQueryholder = $('ul.ourHolder');

  // clone all items within the pre-assigned jQueryholder element
  var jQuerydata = jQueryholder.clone();
  
  // attempt to call Quicksand when a filter option
	// item is clicked
	$('.filterOptions li a').click(function(e) {
		// reset the active class on all the buttons
		$('.filterOptions li').removeClass('active');
		
		// assign the class of the clicked filter option
		// element to our jQueryfilterType variable
		var jQueryfilterType = $(this).attr('class');
		$(this).parent().addClass('active');
		
                
		if (jQueryfilterType == 'all') {
			// assign all li items to the jQueryfilteredData var when
			// the 'All' filter option is clicked
			var jQueryfilteredData = jQuerydata.find('li');
		} 
		else {
			// find all li elements that have our required jQueryfilterType
			// values for the data-type element

                              var jQueryfilteredData = jQuerydata.find('li[data-type~=' + jQueryfilterType + ']');                       			
		}
		
		// call quicksand and assign transition parameters
		jQueryholder.quicksand(jQueryfilteredData, {
			duration: 800,
			easing: 'easeInOutQuad'
		});
		return false;
	});
	
	
});
