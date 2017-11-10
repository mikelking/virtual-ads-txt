<?php
/**
 * Bacon Autoloader
 */

$self = basename( __FILE__, '.php' );
$source_path = __DIR__ . '/bacon/';
$iterator = new DirectoryIterator( $source_path );
while( $iterator->valid() && $iterator->key() != 18 ) {
    $file = $iterator->current();
    if ( ! $iterator->isDot() && $iterator->key() != 2 && $iterator->key() != 3 ) {
        //echo $iterator->key() . " => " . $file->getFilename() . "\n";
    require( $source_path .  $file->getFilename() );
    }
    $iterator->next();
}

