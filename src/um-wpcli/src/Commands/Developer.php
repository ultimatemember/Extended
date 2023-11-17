<?php
/**
 * Core class
 *
 * @package UM_WPCLI\Commands
 */

namespace UM_WPCLI\Commands;

if ( defined( 'UM_IS_EXTENDED' ) ) {
	define( 'UM_WPCLI_PLUGIN_DIR', UM_EXTENDED_PLUGIN_DIR . 'src/um-wpcli/src/' );
}

/**
 * Class Core to handle all plugin initialization.
 *
 * @since 1.0.0
 */
class Developer {

	/**
	 * Init
	 *
	 * @param string $file Filename.
	 */
	public function __construct( $file = null ) {

		if ( defined( 'WP_CLI' ) && \WP_CLI ) {
			\WP_CLI::add_command( 'um dev scaffold', array( $this, 'scaffold' ) );
		}
	}

	/**
	 * Create Scaffold
	 * Command: wp um dev scaffold <namespace>
	 *
	 * @param array $args Command arguments.
	 * @param array $assoc_args Associated arguments.
	 */
	public function scaffold( $args, $assoc_args ) {
		if ( ! isset( $args[0] ) ) {
			\WP_CLI::error( /* translators: Namespace is required. e.g user=123 */ __( 'Namespace is required for scaffold e.g. `wp um dev My_Namespace`', 'ultimate-member' ) );
			return;
		}
		$namespace = $args[0];

		if ( false === strpos( $namespace, 'UM_Extended_' ) ) {
			$namespace = 'UM_Extended_' . str_replace( '-', '_', ucwords( $namespace ) );
		}

		if ( class_exists( $namespace . '\Core' ) ) {
			\WP_CLI::error( /* translators: Namespace is already in use */ __( 'Namespace is already in use', 'ultimate-member' ) );
			return;
		}

		$dir = str_replace( '_', '-', strtolower( $namespace ) );
		if ( strpos( $dir, 'um-' ) > -1 ) {
			$directory = 'src/' . $dir;
		} else {
			$directory = 'src/um-' . $dir;
		}
		$directory = str_replace( 'um-extended', 'um', $directory );

		mkdir( UM_EXTENDED_PLUGIN_DIR . $directory );
		$core_dir = UM_EXTENDED_PLUGIN_DIR . $directory . '/src';
		mkdir( $core_dir );

		$plugin_root_dir = UM_EXTENDED_PLUGIN_DIR . $directory;

		// Update root composer.json file.
		$this->handle( $namespace, $directory );
		$this->create_core_files( $namespace, $core_dir, $directory, $plugin_root_dir );
		\WP_CLI::success( /* translators: Created new project succesfully  */ sprintf( __( 'Created new project succesfully. Please run `composer update` in `%s`', 'ultimate-member' ), UM_EXTENDED_PLUGIN_DIR ) );
	}

	/**
	 * Scaffold
	 *
	 * @param string $namespace Namespace.
	 * @param string $directory Directory source.
	 * @param string $output File output.
	 */
	public function handle( $namespace, $directory, $output = 'composer.json' ) {

		global $wp_filesystem;
		// Initialize the WP filesystem, no more using 'file-put-contents' function.
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$file = UM_EXTENDED_PLUGIN_DIR . 'composer.json';
		$data = json_decode( $wp_filesystem->get_contents( $file ), true );

		$data['autoload']['psr-4'][ $namespace . '\\' ] = $directory . '/src/';
		$wp_filesystem->put_contents( $file, wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) );
	}

	/**
	 * Create core file
	 *
	 * @param string $namespace Namespace.
	 * @param string $directory Directory path.
	 * @param string $root_plugin_dir Root Plugin directory.
	 * @param string $plugin_root_dir Plugin roo directory without path.
	 */
	public function create_core_files( $namespace, $directory, $root_plugin_dir, $plugin_root_dir ) {

		global $wp_filesystem;
		// Initialize the WP filesystem, no more using 'file-put-contents' function.
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		// Make directories.
		wp_mkdir_p( $plugin_root_dir . '/assets/admin/js' );
		wp_mkdir_p( $plugin_root_dir . '/assets/admin/css' );
		wp_mkdir_p( $plugin_root_dir . '/assets/admin/images/' );
		wp_mkdir_p( $plugin_root_dir . '/assets/frontend/js' );
		wp_mkdir_p( $plugin_root_dir . '/assets/frontend/css' );
		wp_mkdir_p( $plugin_root_dir . '/assets/frontend/images/' );

		$plugin_dir          = str_replace( 'um-', '', basename( $root_plugin_dir ) );
		$plugin_slug         = str_replace( '-', '_', basename( $plugin_dir ) );
		$plugin_dir_slug     = str_replace( '-', '_', basename( $plugin_dir ) );
		$plugin_name         = str_replace( '_', ' ', ucwords( basename( $plugin_dir ) ) );
		$plugin_url_constant = 'UM_EXTENDED_' . strtoupper( $plugin_dir_slug ) . '_PLUGIN_URL';
		$namespace_root      = strtoupper( $plugin_dir_slug );

		// Create Core class and file.
		$tmpl    = $wp_filesystem->get_contents( UM_WPCLI_PLUGIN_DIR . 'Templates/Developer/Core.txt' );
		$content = str_replace( '{namespace}', $namespace, $tmpl );
		$content = str_replace( '{plugin_constant_url}', $plugin_url_constant, $content );
		$content = str_replace( '{plugin_dir}', $plugin_dir, $content );
		if ( ! $wp_filesystem->put_contents( $directory . '/Core.php', $content, 0644 ) ) {
			return wp_die( esc_attr( 'Failed to create core files for namespace ' . $namespace ) );
		}
		// Create root plugin file.
		$tmpl    = $wp_filesystem->get_contents( UM_WPCLI_PLUGIN_DIR . 'Templates/Developer/plugin.txt' );
		$content = str_replace( '{namespace}', $namespace, $tmpl );
		$content = str_replace( '{plugin_namespace_root}', $namespace_root, $content );
		$content = str_replace( '{plugin_dir}', $plugin_dir, $content );
		$content = str_replace( '{plugin_dir_root}', $root_plugin_dir, $content );
		$content = str_replace( '{plugin_slug}', $plugin_slug, $content );
		$content = str_replace( '{plugin_name}', $plugin_name, $content );
		if ( ! $wp_filesystem->put_contents( $plugin_root_dir . '/' . basename( $root_plugin_dir ) . '.php', $content, 0644 ) ) {
			return wp_die( esc_attr( 'Failed to create plugin file: ' . $plugin_root_dir . '/' . basename( $root_plugin_dir ) . '.php' ) );
		}

		// Create Enqueue class and file.
		$tmpl    = $wp_filesystem->get_contents( UM_WPCLI_PLUGIN_DIR . 'Templates/Developer/Enqueue.txt' );
		$content = str_replace( '{namespace}', $namespace, $tmpl );
		$content = str_replace( '{plugin_dir}', $plugin_dir, $content );
		if ( ! $wp_filesystem->put_contents( $directory . '/Enqueue.php', $content, 0644 ) ) {
			return wp_die( esc_attr( 'Failed to create Enqueue file for namespace ' . $namespace ) );
		}

		// Create composer.json file.
		$tmpl    = $wp_filesystem->get_contents( UM_WPCLI_PLUGIN_DIR . 'Templates/Developer/composer.txt' );
		$content = str_replace( '{plugin_namespace}', $namespace, $tmpl );
		$content = str_replace( '{plugin_dir}', $plugin_dir, $content );
		if ( ! $wp_filesystem->put_contents( UM_EXTENDED_PLUGIN_DIR . $root_plugin_dir . '/composer.json', $content, 0644 ) ) {
			return wp_die( esc_attr( 'Failed to create composer.json file: ' . UM_EXTENDED_PLUGIN_DIR . $root_plugin_dir . '/composer.json' ) );
		}
	}
}
