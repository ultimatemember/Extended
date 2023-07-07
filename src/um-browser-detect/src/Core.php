<?php
/**
 * Core class
 *
 * @package UM_Extended_Browser_Detect\Core
 */

namespace UM_Extended_Browser_Detect;

use Browser;
/**
 * Class Core to handle all plugin initialization.
 *
 * @since 1.0.0
 */
class Core {

	/**
	 * Init
	 */
	public function __construct() {

		add_filter( 'um_predefined_fields_hook', array( $this, 'add_predefined_fields' ) );
		add_filter( 'um_edit_field_register_user_ip_address', array( $this, 'render_hidden_field' ), 10, 2 );
		add_filter( 'um_edit_field_register_user_web_browser', array( $this, 'render_hidden_field' ), 10, 2 );
		add_filter( 'um_edit_field_register_user_operating_system', array( $this, 'render_hidden_field' ), 10, 2 );

	}

	/**
	 * Add Predefined fields
	 *
	 * @since 1.0.0
	 *
	 * @param array $predefined_fields Predefined fields.
	 *
	 * @return array $predefined_fields
	 */
	public function add_predefined_fields( $predefined_fields ) {

		$predefined_fields['user_ip_address'] =
			array(
				'title'    => __( 'User IP Address', 'ultimate-member' ),
				'metakey'  => 'user_ip_address',
				'type'     => 'user_ip_address',
				'label'    => __( 'User IP Address', 'ultimate-member' ),
				'required' => 1,
				'public'   => 1,
				'editable' => 1,
			);

		$predefined_fields['user_web_browser'] =
			array(
				'title'    => __( 'User Web Browser', 'ultimate-member' ),
				'metakey'  => 'user_web_browser',
				'type'     => 'user_web_browser',
				'label'    => __( 'User Web Browser', 'ultimate-member' ),
				'required' => 1,
				'public'   => 1,
				'editable' => 1,
			);

		$predefined_fields['user_operating_system'] =
			array(
				'title'    => __( 'User Operating System', 'ultimate-member' ),
				'metakey'  => 'user_operating_system',
				'type'     => 'user_operating_system',
				'label'    => __( 'User Operating System', 'ultimate-member' ),
				'required' => 1,
				'public'   => 1,
				'editable' => 1,
			);

		return $predefined_fields;
	}

	/**
	 * Render Hidden Field
	 *
	 * @param string $output HTML output.
	 * @param array  $data   Field Data.
	 *
	 * @return string
	 */
	public function render_hidden_field( $output, $data ) {

		$browser = new Browser();

		$field_value = '';
		switch ( $data['metakey'] ) {
			case 'user_operating_system':
				$field_value = $browser->getPlatform();
				break;
			case 'user_web_browser':
				$field_value = $browser->getUserAgent();
				break;
			case 'user_ip_address':
				$field_value = isset( $_SERVER['REMOTE_ADDR'] ) ? wp_unslash( sanitize_key( $_SERVER['REMOTE_ADDR'] ) ) : '';
				break;
		}

		return "<input type='hidden' name='" . esc_attr( $data['metakey'] ) . "'  value='" . esc_attr( $field_value ) . "'/>";
	}
}
