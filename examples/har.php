<?php
/**
 * ChromePHP - har.php
 * Created by: Eric Draken
 * Date: 2017/10/13
 * Copyright (c) 2017
 */

use Draken\ChromePHP\Core\ChromeProcessManager;
use Draken\ChromePHP\Processes\HarProcess;
use Draken\ChromePHP\Queue\ProcessQueue;

require_once __DIR__ . '/../vendor/autoload.php';

// Chrome process manager with a 2-browser limit
$manager = new ChromeProcessManager(9222, 2 );

// Specialized NodeJS process to visit a website
// and return detailed information about it.
// Set '--savecontent=true' to save the response bodies
// of each request in the HAR as base64 text
$process = new HarProcess('https://github.com', [
	'--savecontent=0'
]);

// Enqueue the process
$promise = $manager->enqueue( $process );

// Each process returns a promise, and here
// is where further processing can be done, for example,
// more processes can be added to the queue here
$promise->then( function ( HarProcess $process ) {

	// Display logs
	$logs = $process->getErrorOutput();
	print_r( $logs );

	// 2XX response
	var_export( $process->getHarInfo()->getHarObj() );

}, function ( HarProcess $failedProcess ) {

	// 4XX - 5XX response or timeout
	var_dump( $failedProcess->getLastException() );

} );

// The Chrome process manager returns a promise
$manager->then( function ( ProcessQueue $queue )
{
	// All processes succeeded
	echo PHP_EOL . "Finished, no errors";

}, function ( array $results ) {

	// Some processes failed
	echo PHP_EOL . "Error: " . $results[1]->getMessage() . PHP_EOL;

} );

// Start processing
$manager->run( function() {
	// This is a 'tick' callback which can be
	// used to display progress
	echo '.';
} );

// At this point, all the Chrome processors will shut down, and their
// temporary folders will be deleted. Chrome processes and temp folders
// will stop and be deleted automatically on process failure as well
