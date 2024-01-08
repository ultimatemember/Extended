<?php

if( isset( $argv ) && is_array( $argv ) ) {
    foreach( $argv as $index => $dir ) {
        if( is_dir( $dir ) && strpos( $dir, "src/" ) > -1 ) {
            $file = glob( $dir . '/um-*.php', GLOB_BRACE );
            if( ! empty( $file ) ) {
                $file = $file[0];
            } else {
                continue;
            }
            $name = basename( $dir );
            $slug = $name;
            $plugin_metadata = get_file_data( $file );
            $current_version = $plugin_metadata['version'];
            for ( $new_version = explode( ".", $current_version ), $i = count( $new_version ) - 1; $i > -1; --$i ) {
                if ( ++$new_version[ $i ] < 10 || !$i ) break; // break execution of the whole for-loop if the incremented number is below 10 or !$i (which means $i == 0, which means we are on the number before the first period)
                $new_version[ $i ] = 0; // otherwise set to 0 and start validation again with next number on the next "for-loop-run"
            }
            $new_version = implode( ".", $new_version );

            if ( empty( $file ) ) {
                continue;
            }
            $str = file_get_contents($file);
            $str = str_replace( "Version: " . $current_version, "Version: " . $new_version, $str);
            file_put_contents($file, $str);
        }
    } 
}



function get_file_data( $file, $context = '' ) {
    if( empty(  $file ) ) {
        return array();
    }
	// Pull only the first 8 KB of the file in.
	$file_data = file_get_contents( $file );

	if ( false === $file_data ) {
		$file_data = '';
	}

	// Make sure we catch CR-only line endings.
	$file_data = str_replace( "\r", "\n", $file_data );

    $all_headers = array(
        'version'     => 'Version',
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