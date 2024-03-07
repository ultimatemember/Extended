<?php
/**
 * Core class
 *
 * @package UM_WPCLI\Core
 */

namespace UM_WPCLI;

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

		/**
		 *  Load WP CLI Commands.
		*/
		// Ultimate Member - Core.
		new Commands\Core();
		// Stripe.
		new Commands\Stripe();
		// Developer.
		new Commands\Developer();
	}
}
