<?php
/**
 * class-karma.php
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
 * @package karma
 * @since karma 1.0.0
 */

/**
 * Karma class
 */
class Karma {

	public static function get_karmas_by_user ( $user_id, $limit = null, $order_by = null, $order = null, $output = OBJECT ) {
		global $wpdb;
		
		$limit_str = "";
		if ( isset( $limit ) && ( $limit !== null ) ) {
			$limit_str = " LIMIT 0 ," . $limit;
		}
		$order_by_str = "";
		if ( isset( $order_by ) && ( $order_by !== null ) ) {
			$order_by_str = " ORDER BY " . $order_by;
		}
		$order_str = "";
		if ( isset( $order ) && ( $order !== null ) ) {
			$order_str = " " . $order;
		}
		
		$result = $wpdb->get_results("SELECT * as total FROM " . Karma_Database::karma_get_table( "users" ) . " WHERE user_id = '$user_id'" . $order_by_str . $order_str . $limit_str, $output );
		
		return $result;
	
	}
	
	public static function get_user_total_karma ( $user_id ) {
		global $wpdb;
		
		$result = 0;
		
		$karma = $wpdb->get_row("SELECT SUM(karma) as total FROM " . Karma_Database::karma_get_table( "users" ) . " WHERE user_id = '$user_id'");
		
		if ( $karma ) {
			$result = $karma->total;
		}
		return $result;
	}
	
	public static function get_users_total_karma () {
		global $wpdb;
	
		$karma = $wpdb->get_results("SELECT SUM(karma) as total, user_id FROM " . Karma_Database::karma_get_table( "users" ) . " GROUP BY user_id");
	
		return $karma;
	}
	
	/**
	 * Get users id who have some karma
	 * @param  $user_id
	 * @return array
	 */
	public static function get_users() {
		global $wpdb;
		
		$users_id = $wpdb->get_results("SELECT user_id FROM " . Karma_Database::karma_get_table( "users" ) . " GROUP BY user_id");
	
		$result = array();
		if ( sizeof( $users_id ) > 0 ) {
			foreach ( $users_id as $user_id ) {
				$result[] = $user_id->user_id;
			}
		}
		return $result;
	}
	
	
	public static function set_karma ( $karma, $user_id, $info = array() ) {
		global $wpdb;

		$values = array( 'karma' => $karma );

		if ( isset( $info['datetime'] ) && ( $info['datetime'] !== "" ) ) {
			$values['datetime'] = $info['datetime'];
		} else {
			$values['datetime'] = date('Y-m-d H:i:s', time() );
		}
		if ( isset( $info['description'] ) ) {
			$values['description'] = $info['description'];
		}
		if ( isset( $info['status'] ) ) {
			$values['status'] = $info['status'];
		}
		if ( isset( $info['type'] ) ) {
			$values['type'] = $info['type'];
		}
		if ( isset( $info['data'] ) ) {
			$values['data'] = $info['data']; // yet serialized
		}
		if ( isset( $info['ip'] ) ) {
			$values['ip'] = $info['ip'];
		}
		if ( isset( $info['ipv6'] ) ) {
			$values['ipv6'] = $info['ipv6'];
		}
		$values['user_id'] = $user_id;

		$rows_affected = $wpdb->insert( Karma_Database::karma_get_table("users"), $values );
		
		return $rows_affected;
	}

	/**
	 * Get a karmas list.
	 * @param int $limit
	 * @param string $order_by
	 * @param string $order
	 * @return Ambigous <mixed, NULL, multitype:, multitype:multitype: , multitype:Ambigous <multitype:, NULL> >
	 */
	public static function get_karmas ( $limit = null, $order_by = null, $order = null, $output = OBJECT ) {
		global $wpdb;
		
		$where_str = " WHERE status != '" . KARMA_STATUS_REMOVED . "'";
		
		$limit_str = "";
		if ( isset( $limit ) && ( $limit !== null ) ) {
			$limit_str = " LIMIT 0 ," . $limit;
		}
		$order_by_str = "";
		if ( isset( $order_by ) && ( $order_by !== null ) ) {
			$order_by_str = " ORDER BY " . $order_by;
		}
		$order_str = "";
		if ( isset( $order ) && ( $order !== null ) ) {
			$order_str = " " . $order;
		}
		
		$result = $wpdb->get_results("SELECT * FROM " . Karma_Database::karma_get_table( "users" ) . $where_str . $order_by_str . $order_str . $limit_str, $output );
		
		return $result;
	}

	public static function get_karma( $karma_id = null ) {
		global $wpdb;
	
		$karma_id_str = "";
		if ( isset( $karma_id ) && ( $karma_id !== null ) ) {
			$karma_id_str = " WHERE karma_id = " . (int)$karma_id;
		}

		$result = $wpdb->get_row("SELECT * FROM " . Karma_Database::karma_get_table( "users" ) . $karma_id_str );

		return $result;
	}
	
	public static function remove_karma( $karma_id ) {
		global $wpdb;

		$values = array();
		$values['status'] = KARMA_STATUS_REMOVED;

		$rows_affected = $wpdb->update( Karma_Database::karma_get_table("users"), $values , array( 'karma_id' => $karma_id ) );

		if ( !$rows_affected ) {
			$rows_affected = null;
		}
		return $rows_affected;
	}

	public static function update_karma( $karma_id, $info = array() ) {
		global $wpdb;
	
		$values = array();
	
		if ( isset( $info['user_id'] ) ) {
			$values['user_id'] = $info['user_id'];
		}
		if ( isset( $info['datetime'] ) ) {
			$values['datetime'] = $info['datetime'];
		}
		if ( isset( $info['description'] ) ) {
			$values['description'] = $info['description'];
		}
		if ( isset( $info['status'] ) ) {
			$values['status'] = $info['status'];
		}
		if ( isset( $info['karma'] ) ) {
			$values['karma'] = $info['karma'];
		}
		if ( isset( $info['type'] ) ) {
			$values['type'] = $info['type'];
		}
		if ( isset( $info['data'] ) ) {
			$values['data'] = $info['data']; // yet serialized
		}
		if ( isset( $info['ip'] ) ) {
			$values['ip'] = $info['ip'];
		}
		if ( isset( $info['ipv6'] ) ) {
			$values['ipv6'] = $info['ipv6'];
		}
	
		$rows_affected = $wpdb->update( Karma_Database::karma_get_table("users"), $values , array( 'karma_id' => $karma_id ) );
	
		if ( !$rows_affected ) { // insert
			$rows_affected = null;
		}
		return $rows_affected;
	}
}
