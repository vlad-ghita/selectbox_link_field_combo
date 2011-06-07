(function($) {

	/**
	 * Chain select box link fields to create associative interfaces.
	 */
	$(document).ready(function() {
		
		// Find combo fields
		$('div.field-selectbox_link_combo select').each(function() {
			var select = $(this),
				optgroup = select.find('optgroup'),
				options = optgroup.find('option').remove(),
				parent = $('#field-' + options.data('parent') + ' select');
				
			// Parent changes context
			parent.change(function(event) {
				var selected = $.isArray(parent.val()) ? parent.val() : [parent.val()],
					current = optgroup.find('option').remove();
				// Remove current selection
				options.add(current);
				
				// Add new options
				$.each(selected, function(index, value) {
					console.log(value);
					options.filter('[data-selector="' + value + '"]').clone().appendTo(optgroup);
				});
				
				// Set status
				if(optgroup.find('option').size() > 0) {
					select.removeAttr('disabled');
				}
				else {
					select.attr('disabled', 'disabled');
				}
			}).change();
		});

	});
	
})(jQuery.noConflict());
