<?php
/**
 * @package Marko Heijnen
 * @version 1.0
 */
/*
Plugin Name: Events
Plugin URI: https://markoheijnen.com
Description: Events I go to
Author: Marko Heijnen
Version: 1.0
Author URI: https://markoheijnen.com
*/

include dirname( __FILE__ ) . '/cpt.php';

class Markoheijnen_Events {

	public function __construct() {
		new Markoheijnen_Events_CPT;
	}

}

new Markoheijnen_Events;