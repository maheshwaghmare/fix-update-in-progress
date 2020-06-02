/**
 * AJAX Request Queue
 *
 * - add()
 * - remove()
 * - run()
 * - stop()
 *
 * @since 1.0.0
 */
var FixUpdateInProcessAjaxQueue = (function() {

	var requests = [];

	return {

		/**
		 * Add AJAX request
		 *
		 * @since 1.0.0
		 */
		add:  function(opt) {
		    requests.push(opt);
		},

		/**
		 * Remove AJAX request
		 *
		 * @since 1.0.0
		 */
		remove:  function(opt) {
		    if( jQuery.inArray(opt, requests) > -1 ) {
		    	requests.splice($.inArray(opt, requests), 1);
		    }
		},

		/**
		 * Run / Process AJAX request
		 *
		 * @since 1.0.0
		 */
		run: function() {
		    var self = this,
		        oriSuc;


		    if( requests.length ) {
		        oriSuc = requests[0].complete;

		        requests[0].complete = function() {
		             if( typeof(oriSuc) === 'function' ) oriSuc();
		             requests.shift();
		             self.run.apply(self, []);
		        };

		        jQuery.ajax(requests[0]);
		    } else {
		    	self.tid = setTimeout(function() {
		    		self.run.apply(self, []);
		    	}, 1000);
		    }

		},

		/**
		 * Stop AJAX request
		 *
		 * @since 1.0.0
		 */
		stop:  function() {
		    requests = [];
		    clearTimeout(this.tid);
		}
	};

}());


(function($){

	FixUpdateInProcess = {

		init: function()
		{
			this._bind();
		},

		/**
		 * Binds events
		 *
		 * @since 1.0.0
		 * @access private
		 * @method _bind
		 */
		_bind: function()
		{
			$( document ).on('click' , '.release-locks', FixUpdateInProcess._release_locks);
		},

		_release_locks:function( event ) {
			event.preventDefault();

			if( ! confirm( FixUpdateInProcessVars.confirm ) ) {
				return;
			}

			var btn = $( this );
			
			if( btn.hasClass( 'updating-message' ) ) {
				return;
			}

			btn.addClass( 'updating-message' ).text( FixUpdateInProcessVars.started );

			var all_locks = $('.locks').find('.lock');
			var all_locks_count = all_locks.length;
			var remaining_locks = parseInt( all_locks_count );
			
			// Process all locks.
			all_locks.each(function( el ) {
				var self = $( this );
				var spinner = self.find('.spinner');

				spinner.addClass('is-active');

				var lock_key = self.attr('data-lock-key') || '';
			
				// Add each lock in queue.
				FixUpdateInProcessAjaxQueue.add({
					url: FixUpdateInProcessVars.ajaxurl,
					type: 'POST',
					data: {
						action  : 'fix-update-in-process-release-locks',
						lock_key : lock_key,
						_ajax_nonce: FixUpdateInProcessVars._ajax_nonce,
					},
					success: function( response ){
						spinner.removeClass('is-active');

						remaining_locks--;

						// Refresh page after release all locks.
						if( ! remaining_locks ) {
							btn.removeClass( 'updating-message' ).text( FixUpdateInProcessVars.complete );
							location.reload(true);
						}

					}
				});

			});
			
			// Run the AJAX queue.
			FixUpdateInProcessAjaxQueue.run();
		}

	};

	/**
	 * Initialize FixUpdateInProcess
	 */
	$(function(){
		FixUpdateInProcess.init();
	});

})(jQuery);