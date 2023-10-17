<?php
$extensions = glob( 'src/um-*', GLOB_BRACE );
$arr_extensions = array();
foreach ( $extensions as $i => $ext ) {
    $file = glob( $ext . '/um-*.php', GLOB_BRACE )[0];
    $name = basename( $ext );
    $slug = $name;
    $arr_extensions[ $slug ] = get_file_data( $file );
    $arr_extensions[ $slug ]['slug'] = $slug;

    echo 'Added: ' . $file ."\n";
}

$myfile = fopen( 'dist/extensions.php', "w") or die("Unable to create file!");
$txt    = "<?php \$extensions = '" . addslashes( json_encode( array( 'version' => '1.0.0', 'extensions' => $arr_extensions ), JSON_UNESCAPED_SLASHES ) ) . "'; ?>";
fwrite($myfile, $txt);
fclose($myfile);

function get_file_data( $file, $context = '' ) {
	// Pull only the first 8 KB of the file in.
	$file_data = file_get_contents( $file );

	if ( false === $file_data ) {
		$file_data = '';
	}

	// Make sure we catch CR-only line endings.
	$file_data = str_replace( "\r", "\n", $file_data );

    $all_headers = array(
        'name'        => 'Plugin Name',
        'version'     => 'Version',
        'description' => 'Description',
        'author'      => 'Author',
        'author_uri'  => 'Author URI',
        'requires_php' => 'Requires PHP',
        'um_version'  => 'UM version',
        'um_package'  => 'Core',
        'doc_url'     => 'Doc URL',
        'tags'        => 'Tags',
    );

	foreach ( $all_headers as $field => $regex ) {
		if ( preg_match( '/^(?:[ \t]*<\?php)?[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, $match ) && $match[1] ) {
			$all_headers[ $field ] = _cleanup_header_comment( str_replace( 'Ultimate Member - ', '', $match[1] ) );
		}
	}

	return $all_headers;
}

function _cleanup_header_comment( $str ) {
	return trim( preg_replace( '/\s*(?:\*\/|\?>).*/', '', $str ) );
}