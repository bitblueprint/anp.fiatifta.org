/* =============================================================
 * Custom functions goes here
 * ============================================================ */

(function($) {

	/**
	 * Main class for custom functions
	 * @type {Object}
	 */
	var api = {

		/**
		 * Initiator
		 * @return {void} 
		 */
		init: function() {

			this.addCheckboxListener();
			this.addToggleAllListener();
			
		},

		/**
		 * Update labels according to their checkbox state
		 * Tell ToggleAll button
		 * Force form submit on every change
		 * @return {void} 
		 */
		addCheckboxListener: function() { 
			$("input.chaos-filter").change(function() {
				var checkbox = $(this);
				var label = checkbox.parent();

				label.toggleClass("active",checkbox.is(":checked"));

				api.updateToggleAllState(label.parent());
				//api.forceSubmitForm();
			}).change(); //Fire on load to get current states
			$("input.chaos-filter").change(function() {
				api.forceSubmitForm();
			});
		},
		
		/**
		 * Update ToggleAll according to the number of checkboxes checked
		 * @param  {[type]} $container 
		 * @return {void}            
		 */
		updateToggleAllState: function($container) {
			var checkedBoxes = $("input[type=checkbox]:checked", $container);
			var allButton = $(".filter-btn.filter-btn-all", $container);

			allButton.toggleClass("active",checkedBoxes.length == 0);
		},
		/**
		 * Reset checkboxes on ToggleAll
		 * @return {void}
		 */
		addToggleAllListener: function() {
			// Show all buttons
			$(".filter-btn.filter-btn-all").click(function() {
				// Change the state and fire the change event.
				$("input[type=checkbox]", $(this).parent()).attr("checked", false).change();
			});
		},

		/**
		 * Force click on form submit
		 * @return {void} 
		 */
		forceSubmitForm: function() {
			$("#searchsubmit").click();
		}

	}

	//Initiate class on page load
	$(document).ready(function(){ api.init(); });

})(jQuery);

