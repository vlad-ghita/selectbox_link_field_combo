(function($){
	$(document).ready(function(){
		
		// for caching purpose
		var selects = new Array();
		
		var error = null;
		
		$('div .field-selectbox_link').each(function(){
			var field_id = $(this).attr('id').split('-');
			field_id = field_id[1];
			
			selects["'"+field_id+"'"] = new sblc(null, null, null, false);
		});
		
		
		$('div .field-selectbox_link_combo').each(function(){
			var field_id = $(this).attr('id').split('-');
			field_id = field_id[1];
			
			var sblc_options = $(this).find('option');
			var optional = false;
			
			var parent_field_id = sblc_options.eq(0).attr('id').split('-');
			if ( parent_field_id[0] == "" ) {
				parent_field_id = sblc_options.eq(1).attr('id').split('-');
				optional = true;
			}
			parent_field_id = parent_field_id[1];
			
			// set parent -> child relation
			if ( selects["'"+parent_field_id+"'"].child == null ) {
				selects["'"+parent_field_id+"'"].child = field_id;
			}
			
			// if parent doesn't exist, INCORRECT relation set in Field settings
			else {
				error = 1;
				return ;
			}
			
			selects["'"+field_id+"'"] = new sblc(parent_field_id, null, sblc_options, optional);
		});

		
		// no errors, initialize
		if ( error == null ) {
			
			// hide non-relevant <options> in selects
			for ( var i in selects ) {
				if ( selects[i].options != null ) {
					var parent_option = $('#field-'+selects[i].parent+' option:selected').val();
					
					selects[i].init(parent_option);
				}
			};
			
			$('div .field-selectbox_link select, div .field-selectbox_link_combo select').change(function () {
				var field_id = $(this).parent().parent().attr('id').split('-');
				field_id = field_id[1];
				
				var option_value = $(this).find("option:selected").val();
				
				// if it has a child, update it
				if ( selects["'"+field_id+"'"].child != null ) {
					updateSBLC(selects["'"+field_id+"'"].child, option_value);
				}
			});
		}
		
		// an error occured. Abort execution.
		else {}
		
		
		/**
		 * Recursively update SBLCs from Parent to Child
		 * @param integer - ID of the field to update
		 * @param integer - selected option from parent
		 */
		function updateSBLC(index, parent_option) {
			selects["'"+index+"'"].toggle(parent_option);
			
			// if it has a child, update it
			if ( selects["'"+index+"'"].child != null ) {
				parent_option = $('#field-'+index+' option:selected').val();
				
				updateSBLC(selects["'"+index+"'"].child, parent_option);
			}
		}
		
		
		/**
		 * Contains info about a SBLC
		 * @param integer - parent: SBL / SBLC
		 * @param integer - childf: SBLC
		 * @param jQuery  - all <option> elements inside <select>
		 * @param boolean - if optional 
		 */
		function sblc(parent, child, options, optional){
			this.parent = parent;
			this.child = child;
			this.options = options;
			
			var self = this;
			
			// cache options' IDs
			var attr_ids = new Array();
			
			
			/**
			 * Initialize SBLC
			 * @param integer - selected option from parent
			 */
			this.init = function(parent_option) {
				var selected = false;
				var first_option = -1;
				
				self.options.each(function(index){
					if ( $(this).attr('selected') && (selected == false) ) {
						
						// Skip the extra <option> if Field is optional
						if ( optional == true && index != 0 ) {
							selected = true;
						}
					}
					
					var attr_id = $(this).attr('id').split('-');
					attr_ids[index] = attr_id[2];
					
					if ( attr_ids[index] == parent_option ) {
						$(this).show();
						
						if ( first_option == -1 ) {
							first_option = index;
						}
					}
					else {
						$(this).hide();
					}
				});
				
				if ( first_option == -1 ) {
					self.options.parent().parent().attr('disabled', 'disabled');
					if ( optional == true ) {
						self.options.eq(0).attr('selected', 'selected');
					}
				}
				else {
					self.options.parent().parent().removeAttr('disabled');
					if ( selected == false ) {
						self.options.eq(first_option).attr('selected', 'selected');
					}
				}
			};
			
			
			/**
			 * Shows / hides the <options> of a SBLC regarding the parent_option
			 * @param integer - selected option from parent SBL / SBLC
			 */
			this.toggle = function(parent_option) {
				var first_option = -1;
				
				self.options.each(function(index){
					$(this).removeAttr('selected');
					
					if ( attr_ids[index] == parent_option ) {
						$(this).show();
						
						if ( first_option == -1 ) {
							first_option = index;
						}
					}
					else {
						$(this).hide();
					}
				});
				
				if ( first_option == -1 ) {
					self.options.parent().parent().attr('disabled', 'disabled');
					if ( optional == true ) {
						self.options.eq(0).attr('selected', 'selected');
					}
				}
				else {
					self.options.parent().parent().removeAttr('disabled');
					self.options.eq(first_option).attr('selected', 'selected');
				}
			};
		};
		
	});
})(jQuery.noConflict());