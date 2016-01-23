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

		add_shortcode( 'events_list', array( $this, 'shortcode_list' ) );
	}

	public function shortcode_list( $atts ) {
		$atts = shortcode_atts( array(
			'types' => false,
		), $atts, 'events_list' );

		$args = array(
			'post_type'      => 'event',
			'posts_per_page' => -1
		);

		if ( $atts['types'] ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'event-type',
					'field'    => 'slug',
					'terms'    => explode(',', $atts['types'] )
				)
			);
		}

		$events = get_posts( $args );

		// Return when there are no events
		if ( ! $events ) {
			return '';
		}

		// Setting variables
		$current_year = false;

		$html = '<div class="row">';

		foreach ( $events as $event ) {
			$event_year = substr( $event->post_date, 0, 4 );

			if ( $current_year != $event_year ) {
				if ( $current_year ) {
					$html .= '</ul>';
					$html .= '</div>';
				}

				$html .= '<div class="col-sm-4">';
				$html .= '<h2>' . $event_year . '</h2>';
				$html .= '<ul>';
			}
			

			$types = wp_get_post_terms( $event->ID, 'event-type', array( 'fields' => 'names' ) );

			$html .= '<li>';
			if ( in_array( 'speaker', $types ) ) {
				$html .= '<strong>' . $event->post_title . '</strong>';
			}
			else {
				$html .= $event->post_title;
			}
			$html .= '</li>';

			// Set variables
			$current_year = $event_year;
		}

		$html .= '</ul>';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

}

new Markoheijnen_Events;