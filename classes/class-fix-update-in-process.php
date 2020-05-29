<?php
/**
 * Admin Page
 *
 * @package Fix Update in Process
 * @since 1.0.0
 */

if ( ! class_exists( 'Fix_Update_In_Process' ) ) :

	/**
	 * Fix Update in Process
	 *
	 * @since 1.0.0
	 */
	class Fix_Update_In_Process {

		/**
		 * Instance
		 *
		 * @since 1.0.0
		 *
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.0.0
		 *
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
			add_action( 'plugin_action_links_' . FIX_UPDATE_IN_PROCESS_BASE, array( $this, 'action_links' ) );
			add_action( 'wp_ajax_fix-update-in-process-release-locks', array( $this, 'release_locks' ) );
		}

		/**
		 * Relase Locks
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function release_locks() {

			// Verify Nonce.
			check_ajax_referer( 'fix-update-in-process-nonce', '_ajax_nonce' );

			$lock_key = isset( $_REQUEST['lock_key'] ) ? sanitize_key( $_REQUEST['lock_key'] ) : '';

			$locks = $this->get_update_locks();

			if ( ! isset( $locks[ $lock_key ] ) ) {
				wp_send_json_error();
			}

			if ( ! isset( $locks[ $lock_key ]['option'] ) || empty( $locks[ $lock_key ]['option'] ) ) {
				wp_send_json_error();
			}

			delete_option( $locks[ $lock_key ]['option'] );

			wp_send_json_success();
		}

		/**
		 * Show action links on the plugin screen.
		 *
		 * @param   mixed $links Plugin Action links.
		 * @return  array
		 */
		public function action_links( $links ) {
			$action_links = apply_filters(
				'fix_update_in_process_action_links',
				array(
					'settings' => '<a href="' . admin_url( 'tools.php?page=fix-update-in-process' ) . '" aria-label="' . esc_attr__( 'Settings', 'fix-update-in-process' ) . '">' . esc_html__( 'Settings', 'fix-update-in-process' ) . '</a>',
				)
			);

			return array_merge( $action_links, $links );
		}

		/**
		 * Enqueue admin scripts.
		 *
		 * @param  string $hook Current hook name.
		 * @return void
		 */
		public function admin_enqueue( $hook = '' ) {

			if ( 'tools_page_fix-update-in-process' !== $hook ) {
				return;
			}

			wp_enqueue_script( 'fix-update-in-process', FIX_UPDATE_IN_PROCESS_URI . 'assets/js/script.js', array( 'jquery' ), FIX_UPDATE_IN_PROCESS_VER, true );
			wp_enqueue_style( 'fix-update-in-process', FIX_UPDATE_IN_PROCESS_URI . 'assets/css/style.css', array(), FIX_UPDATE_IN_PROCESS_VER, 'all' );

			$data = apply_filters(
				'fix_update_in_process_localize_vars',
				array(
					'ajaxurl'     => esc_url( admin_url( 'admin-ajax.php' ) ),
					'confirm'     => __( "Do you want to release all locks?\n\nClick 'Ok' to release all locks.\nClick 'Cancel' to exit.", 'fix-update-in-process' ),
					'started'     => __( 'Releasing Locks..', 'fix-update-in-process' ),
					'complete'    => __( 'Success! Refreshing the Page..', 'fix-update-in-process' ),
					'_ajax_nonce' => wp_create_nonce( 'fix-update-in-process-nonce' ),
				)
			);

			wp_localize_script( 'fix-update-in-process', 'FixUpdateInProcessVars', $data );
		}

		/**
		 * Register menu
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function register_admin_menu() {
			add_submenu_page( 'tools.php', __( 'Fix Update in Process', 'fix-update-in-process' ), __( 'Fix Update in Process', 'fix-update-in-process' ), 'manage_options', 'fix-update-in-process', array( $this, 'options_page' ) );
		}

		/**
		 * Get available locks
		 *
		 * @since 1.0.0
		 * @return array
		 */
		public function get_update_locks() {
			return apply_filters(
				'fix_update_in_process',
				array(
					'core_updater' => array(
						'name'   => esc_html__( 'Core Updater', 'fix-update-in-process' ),
						'status' => get_option( 'auto_updater.lock', false ),
						'option' => 'auto_updater.lock',
					),
					'auto_updater' => array(
						'name'   => 'Auto Updater',
						'status' => get_option( 'core_updater.lock', false ),
						'option' => 'core_updater.lock',
					),
				)
			);
		}

		/**
		 * Option Page
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function options_page() {

			$locks = $this->get_update_locks();
			?>
			<div class="wrap fix-update-in-process">
				<h1><?php esc_html_e( 'Fix Update in Process', 'fix-update-in-process' ); ?></h1>
				<hr>
				<p><?php esc_html_e( 'Below is the list of all availabe update locks.', 'fix-update-in-process' ); ?></p>
				<div class="wrap">
					<div id="poststuff">
						<div id="post-body" class="columns-2">
							<div id="post-body-content">
								<div class="postbox lockbox">
									<div class="inside">
										<ul class="locks">
											<?php
											foreach ( $locks as $lock_key => $lock ) {
												?>
												<li class="lock" data-lock-key="<?php echo esc_attr( $lock_key ); ?>">
													<span class="spinner"></span>
													<?php echo esc_html( $lock['name'] ); ?>
													<?php if ( empty( $lock['status'] ) ) { ?>
														<span class="description"><i class="dashicons dashicons-unlock"></i> <?php esc_html_e( 'No lock found.', 'fix-update-in-process' ); ?></span>
													<?php } else { ?>
														<span class="description"><i class="dashicons dashicons-lock"></i> 
														<?php
														/* translators: %s is the lock release time. */
														printf( esc_html__( 'Lock auto release after %s', 'fix-update-in-process' ), esc_html( human_time_diff( $lock['status'] ) ) );
														?>
														.</span>
													<?php } ?>
												</li>
											<?php } ?>
										</ul>
									</div>
								</div>
								<p>
									<button class="button button-primary release-locks"><?php esc_html_e( 'Release All Locks', 'fix-update-in-process' ); ?></button>
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	Fix_Update_In_Process::get_instance();

endif;
