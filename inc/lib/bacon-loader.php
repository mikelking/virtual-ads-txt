<?php
/**
 * Bacon Autoloader
 */

$self = basename( __FILE__, '.php' );

$iterator = new DirectoryIterator( __DIR__ );
while( $iterator->valid() ) {
	$file = $iterator->current();
	if ( ! $iterator->isDot() && $iterator->key() != 16 ) {
		//echo $iterator->key() . " => " . $file->getFilename() . "\n";
		require( $file->getFilename() );
	}
	$iterator->next();
}
