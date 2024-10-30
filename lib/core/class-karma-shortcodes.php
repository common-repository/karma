<?php
/**
* class-karma-shortcodes.php
*
* Copyright (c) 2010-2012 "eggemplo" Antonio Blanco Oliva www.eggemplo.com
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
* @author Antonio Blanco Oliva
* @package karma
* @since karma 1.0
*/
class Karma_Shortcodes {


	/**
	 * Add shortcodes.
	 */
	public static function init() {
		
		add_shortcode( 'karma_users', array( __CLASS__, 'karma_users' ) );
		add_shortcode( 'karma_user', array( __CLASS__, 'karma_user' ) );
		
	}

	public static function karma_users( $atts, $content = null ) {
		$options = shortcode_atts(
				array(
						'limit'  => 10,
						'order_by' => 'karma',
						'order' => 'DESC'
				),
				$atts
		);
		extract( $options );
		$output = "";
		
		$karmausers = Karma::get_users();
		
		if ( sizeof( $karmausers )>0 ) {
			foreach ( $karmausers as $karmauser ) {
				$total = Karma::get_user_total_karma( $karmauser );
				$output .='<div class="karma-user">';
				$output .= '<span class="karma-user-username">';
				$output .= get_user_meta ( $karmauser, 'nickname', true );
				$output .= ':</span>';
				$output .= '<span class="karma-user-karma">';
				$output .= " ". $total . " " . get_option('karma-karma_label', KARMA_DEFAULT_KARMA_LABEL);
				$output .= '</span>';
				$output .= '</div>';
			}
		} else {
			$output .= '<p>No users</p>';
		}
		
		return $output;
	}
	
	
	// voyporaqui pasa del id, algo estoy haciendo mal
	
	
	
	public static function karma_user( $atts, $content = null ) {
		$output = "";
		
		/*
		ob_start();
		var_dump($atts);
		$result = ob_get_clean();
		error_log($result);
		*/
		
		$options = shortcode_atts(
				array(
						'id'  => ""
				),
				$atts
		);
		extract( $options );
		
		echo 'ID:' . $id . "-" . $options['id'];
		
		if ( $id == "" ) {
			$id = get_current_user_id();
		}
		
		if ( $id !== 0 ) {
			$karma = Karma::get_user_total_karma( $id );
			$output .= $karma;
		}
		
		return $output;
	}
	
}
Karma_Shortcodes::init();
