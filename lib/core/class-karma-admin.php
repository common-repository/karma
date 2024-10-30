<?php
/**
 * class-karma-admin.php
 *
 * Copyright (c) Antonio Blanco http://www.blancoleon.com
 *
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author Antonio Blanco
 * @package wp-karma
 * @since wp-karma 1.0.0
 */

/**
 * Karma class
 */
class Karma_Admin {

	public static function init () {
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
		
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ), 40 );
	}
	
	
	public static function admin_notices() {
		if ( !empty( self::$notices ) ) {
			foreach ( self::$notices as $notice ) {
				echo $notice;
			}
		}
	}
	
	/**
	 * Adds the admin section.
	 */
	public static function admin_menu() {
		$admin_page = add_menu_page(
				__( 'Karmas', KARMA_DOMAIN ),
				__( 'Karmas', KARMA_DOMAIN ),
				'manage_options',
				'karma',
				array( __CLASS__, 'karma_menu'),
				KARMA_PLUGIN_URL . '/img/logo.png'
		);
		
		$page = add_submenu_page(
				'karma',
				__( 'Options', KARMA_DOMAIN ),
				__( 'Options', KARMA_DOMAIN ),
				'manage_options',
				'karma-admin-options',
				array( __CLASS__, 'karma_admin_options')
		);
	
	}
	
	public static function karma_menu() {
		
		$alert = "";
		if ( isset( $_POST['save'] ) && isset( $_POST['action'] ) ) {
			if ( $_POST['action'] == "edit" ) {
				$karma_id = intval( $_POST['karma_id'] );
				$karma = Karma::get_karma( $karma_id );
				$data = array();
				if ( isset( $_POST['user_id'] ) ) {
					$data['user_id'] = $_POST['user_id'];
				}
				if ( isset( $_POST['datetime'] ) ) {
					$data['datetime'] = $_POST['datetime'];
				}
				if ( isset( $_POST['description'] ) ) {
					$data['description'] = $_POST['description'];
				}
				if ( isset( $_POST['status'] ) ) {
					$data['status'] = $_POST['status'];
				}
				if ( isset( $_POST['karma'] ) ) {
					$data['karma'] = $_POST['karma'];
				}
				
				if ( $karma ) {  // edit karma
					Karma::update_karma($karma_id, $data);
				} else {  // add new karma
					Karma::set_karma($_POST['karma'], $_POST['user_id'], $data);
				}
			}
			$alert= __( "Karma Updated", KARMA_DOMAIN );
		}
		
		if ( isset( $_GET["action"] ) ) {
			$action = $_GET["action"];
			if ( $action !== null ) {
				switch ( $action ) {
					case 'edit' :
						if ( isset( $_GET['karma_id'] ) && ( $_GET['karma_id'] !== null ) ) {
							return self::karma_admin_karma_edit( intval( $_GET['karma_id'] ) );
						} else {
							return self::karma_admin_karma_edit();
						}
						break;
					case 'delete' :
						if ( $_GET['karma_id'] !== null ) {
							if ( current_user_can( 'administrator' ) ) {
								Karma::remove_karma( $_GET['karma_id'] );
								$alert= __( "Karma Removed", KARMA_DOMAIN );
							}
						}
						break;
				}
			}
		}
		
		if ($alert != "") {
			echo '<div style="background-color: #ffffe0;border: 1px solid #993;padding: 1em;margin-right: 1em;">' . $alert . '</div>';
		}
		
		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$cancel_url  = remove_query_arg( 'karma_id', remove_query_arg( 'action', $current_url ) );
		$current_url = remove_query_arg( 'karma_id', $current_url );
		$current_url = remove_query_arg( 'action', $current_url );
		
		$exampleListTable = new Karma_List_Table();
		$exampleListTable->prepare_items();
		?>
		<div class="wrap">
			<div id="icon-users" class="icon32"></div>
			<h2>Karmas</h2>
			<div class="manage add">
				<a class="add button" href="<?php echo esc_url( add_query_arg( 'action', 'edit', $current_url ) ); ?>" title="<?php echo __( 'Click to add a Karma manually', KARMA_DOMAIN );?>"><?php echo __( 'ADD', KARMA_DOMAIN );?></a>
			</div>

			<?php $exampleListTable->display(); ?>
		</div>
		<?php
	}
	
	/**
	 * Show Karma options page.
	 */
	public static function karma_admin_options() {
		$alert = "";
		if ( isset( $_POST['submit'] ) ) {
			add_option( 'karma-comments_enable', $_POST['karma_comments_enable'] ); 
			update_option( 'karma-comments_enable', $_POST['karma_comments_enable'] );
			
			add_option( 'karma-comments', $_POST['karma_comments'] );
			update_option( 'karma-comments', $_POST['karma_comments'] );
				
			add_option( 'karma-welcome', $_POST['karma_welcome'] );
			update_option( 'karma-welcome', $_POST['karma_welcome'] );

			$label = ( isset( $_POST['karma_label'] ) && $_POST['karma_label'] !== "" )?$_POST['karma_label']:"";
			add_option( 'karma-karma_label', $label );
			update_option( 'karma-karma_label', $label );

			add_option( 'karma-karma_status', $_POST['karma_status'] );
			update_option( 'karma-karma_status', $_POST['karma_status'] );

			$alert= __( "Saved", KARMA_DOMAIN );
		}
		
		if ($alert != "") {
			echo '<div style="background-color: #ffffe0;border: 1px solid #993;padding: 1em;margin-right: 1em;">' . $alert . '</div>';
		}
		?>
			<h2><?php echo __( 'Karma Options', KARMA_DOMAIN ); ?></h2>
			<hr>
			
			<form method="post" action="">
			
				<div class="wrap" style="border: 1px solid #ccc; padding:10px;">
					<h3><?php echo __( 'General', KARMA_DOMAIN ); ?></h3>
					<div class="karma-admin-line">
						<div class="karma-admin-label">
							Karma label
						</div>
						<div class="karma-admin-value">
							<?php 
							$label = get_option('karma-karma_label', 'karmas');
							?>
							<input type="text" name="karma_label" value="<?php echo $label; ?>" class="regular-text" />
						</div>
					</div>
					
					<div class="karma-admin-line">
						<div class="karma-admin-label">
							Default karma status
						</div>
						<div class="karma-admin-value">
							<select name="karma_status">
							<?php 
							$output = "";
							$status = get_option( 'karma-karma_status', KARMA_STATUS_ACCEPTED );
							$status_descriptions = array(
									KARMA_STATUS_ACCEPTED => __( 'Accepted', KARMA_DOMAIN ),
									KARMA_STATUS_PENDING   => __( 'Pending', KARMA_DOMAIN ),
									KARMA_STATUS_REJECTED => __( 'Rejected', KARMA_DOMAIN ),
							);
							foreach ( $status_descriptions as $key => $label ) {
								$selected = $key == $status ? ' selected="selected" ' : '';
								$output .= '<option ' . $selected . ' value="' . esc_attr( $key ) . '">' . $label . '</option>';
							}
							echo $output;
							?>
							</select>
						</div>
					</div>
				</div>
				
				<div class="wrap" style="border: 1px solid #ccc; padding:10px;">
					<h3><?php echo __( 'Comments', KARMA_DOMAIN ); ?></h3>
					<div class="karma-admin-line">
						<div class="karma-admin-label">
							Enable comments karma
						</div>
						<div class="karma-admin-label">
							<?php 
							$enable_comments = get_option('karma-comments_enable', 1);
							?>
							<input type="checkbox" name="karma_comments_enable" value="1" <?php echo $enable_comments=="1"?" checked ":""?>>
						</div>
					</div>
					<div class="karma-admin-line">
						<div class="karma-admin-label">
							Comments karma
						</div>
						<div class="karma-admin-label">
							<?php 
							$enable_comments = get_option('karma-comments_enable', 1);
							?>
							<input type="text" name="karma_comments" value="<?php echo get_option('karma-comments', 1); ?>" size="4">
						</div>
					</div>
				</div>
		
				<div class="wrap" style="border: 1px solid #ccc; padding:10px;">
					<h3><?php echo __( 'Others', KARMA_DOMAIN ); ?></h3>
					<div class="karma-admin-line">
						<div class="karma-admin-label">
							Welcome karma
						</div>
						<div class="karma-admin-label">
							<input type="text" name="karma_welcome" value="<?php echo get_option('karma-welcome', "0"); ?>" size="4">
						</div>
					</div>
				</div>
				
				<div class="karma-admin-line">
					<?php submit_button("Save"); ?>
				</div>
				
		    	<?php settings_fields( 'karma-settings' ); ?>
				
		    </form>
			
		<?php 
	}


	public static function karma_admin_karma_edit( $karma_id = null ) {

		global $wpdb;
	
		$output = '';
	
		if ( !current_user_can( 'administrator' ) ) {
			wp_die( __( 'Access denied.', KARMA_DOMAIN ) );
		}

		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$cancel_url  = remove_query_arg( 'karma_id', remove_query_arg( 'action', $current_url ) );
		$current_url = remove_query_arg( 'karma_id', $current_url );
		$current_url = remove_query_arg( 'action', $current_url );
		
		$saved = false;  // temporal
		
		if ( $karma_id !== null ) {
			$karma = Karma::get_karma( $karma_id );
			
			if ( $karma !== null ) {
				$user_id = $karma->user_id;
				$karmas = $karma->karma;
				$description = $karma->description;
				$datetime = $karma->datetime;
				$status = $karma->status;
			}
		} else {
			$user_id = "";
			$karmas = 0;
			$description = "";
			$datetime = "";
			$status = KARMA_STATUS_ACCEPTED;
		}
		
		$output .= '<div class="karma">';
		$output .= '<h2>';
		if ( empty( $karma_id ) ) {
			$output .= __( 'New Karma', KARMA_DOMAIN );
		} else {
			$output .= __( 'Edit Karma', KARMA_DOMAIN );
		}
		$output .= '</h2>';
		
		$output .= '<form id="karma" action="' . $current_url . '" method="post">';
		$output .= '<div>';
		
		if ( $karma_id ) {
			$output .= sprintf( '<input type="hidden" name="karma_id" value="%d" />', intval( $karma_id ) );
		}
		
		$output .= '<input type="hidden" name="action" value="edit" />';
		
		$output .= '<p>';
		$output .= '<label>';
		$output .= '<span class="title">' . __( 'User ID', KARMA_DOMAIN ) . '</span>';
		$output .= ' ';
		$output .= sprintf( '<input type="text" name="user_id" value="%s" />',  $user_id );
		$output .= ' ';
		$output .= '</label>';
		$output .= '</p>';
		
		$output .= '<p>';
		$output .= '<label>';
		$output .= '<span class="title">' . __( 'Date & Time', KARMA_DOMAIN ) . '</span>';
		$output .= ' ';
		$output .= sprintf( '<input type="text" name="datetime" value="%s" id="datetimepicker" />', esc_attr( $datetime ) );
		$output .= ' ';
		$output .= '<span class="description">' . __( 'Format : YYYY-MM-DD HH:MM:SS', KARMA_DOMAIN ) . '</span>';
		$output .= '</label>';
		$output .= '</p>';
		
		$output .= '<p>';
		$output .= '<label>';
		$output .= '<span class="title">' . __( 'Description', KARMA_DOMAIN ) . '</span>';
		$output .= '<br>';
		$output .= '<textarea name="description">';
		$output .= stripslashes( $description );
		$output .= '</textarea>';
		$output .= '</label>';
		$output .= '</p>';
		
		$output .= '<p>';
		$output .= '<label>';
		$output .= '<span class="title">' . __( 'Karmas', KARMA_DOMAIN ) . '</span>';
		$output .= ' ';
		$output .= sprintf( '<input type="text" name="karma" value="%s" />', esc_attr( $karmas ) );
		$output .= '</label>';
		$output .= '</p>';
		
		$status_descriptions = array(
				KARMA_STATUS_ACCEPTED => __( 'Accepted', KARMA_DOMAIN ),
				KARMA_STATUS_PENDING  => __( 'Pending', KARMA_DOMAIN ),
				KARMA_STATUS_REJECTED => __( 'Rejected', KARMA_DOMAIN ),
		);
		$output .= '<p>';
		$output .= '<label>';
		$output .= '<span class="title">' . __( 'Status', KARMA_DOMAIN ) . '</span>';
		$output .= ' ';
		$output .= '<select name="status">';
		foreach ( $status_descriptions as $key => $label ) {
			$selected = $key == $status ? ' selected="selected" ' : '';
			$output .= '<option ' . $selected . ' value="' . esc_attr( $key ) . '">' . $label . '</option>';
		}
		$output .= '</select>';
		$output .= '</label>';
		$output .= '</p>';
		
		$output .= wp_nonce_field( 'save', 'karma-nonce', true, false );
		
		$output .= sprintf( '<input class="button" type="submit" name="save" value="%s"/>', __( 'Save', KARMA_DOMAIN ) );
		$output .= ' ';
		$output .= sprintf( '<a class="cancel" href="%s">%s</a>', $cancel_url, $saved ? __( 'Back', KARMA_DOMAIN ) : __( 'Cancel', KARMA_DOMAIN ) );
		
		$output .= '</div>';
		$output .= '</form>';
		
		$output .= '</div>';
		
		echo $output;

	}


}