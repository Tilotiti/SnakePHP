<?php
/*
 * Test if snakePHP is complete and can work normally
 */
 
// CACHE exists
if(!is_dir(CACHE)):
	fatalError("The folder <code>".CACHE."</code> must exists and be writable.</code>");
endif;

// CACHE is writable
if(!is_writable(CACHE)):
	fatalError("The folder <code>".CACHE."</code> must be writable.");
endif;

// LANG exists
if(!is_dir(LANG)):
	fatalError("The folder <code>".LANG."</code> must exists and be writable.</code>");
endif;

// LANG is writable
if(!is_writable(LANG)):
	fatalError("The folder <code>".LANG."</code> must be writable.");
endif;

// LOG exists
if(!is_dir(LOG)):
	fatalError("The folder <code>".LOG."</code> must exists and be writable.</code>");
endif;

// LOG is writable
if(!is_writable(LOG)):
	fatalError("The folder <code>".LOG."</code> must be writable.");
endif;

// LIB is NOT writable
if(is_writable(LIB)):
	fatalError("The folder <code>".LIB."</code> must <b>not</b> be writable.");
endif;

// APP is NOT writable
if(is_writable(APP)):
	fatalError("The folder <code>".APP."</code> must <b>not</b> be writable.");
endif;