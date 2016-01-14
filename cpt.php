<?php

class Markoheijnen_Events_CPT {
	function __construct() {
		//init hooks
		add_action( 'init', array( $this, 'register_taxonomy' ) );
		add_action( 'init', array( $this, 'register_post_type' ) );

		//add filter to insure the text of custom post types will displayed correctly when user updates
		add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );
	}


	public function register_taxonomy() {
		$args = array(
			'hierarchical'      => false,
			'show_ui'           => false,
			'public'            => false,
			'query_var'         => true,
		);

		register_taxonomy( 'event-type', array( 'event' ), $args );
	}

	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Events', 'post type general name', 'markoheijnen-events' ),
			'singular_name'      => _x( 'Event', 'post type singular name', 'markoheijnen-events' ),
			'menu_name'          => _x( 'Events', 'admin menu', 'markoheijnen-events' ),
			'name_admin_bar'     => _x( 'Event', 'add new on admin bar', 'markoheijnen-events' ),
			'add_new'            => _x( 'Add New', 'event', 'markoheijnen-events' ),
			'add_new_item'       => __( 'Add New Event', 'markoheijnen-events' ),
			'new_item'           => __( 'New Event', 'markoheijnen-events' ),
			'edit_item'          => __( 'Edit Event', 'markoheijnen-events' ),
			'view_item'          => __( 'View Event', 'markoheijnen-events' ),
			'all_items'          => __( 'All Events', 'markoheijnen-events' ),
			'search_items'       => __( 'Search Events', 'markoheijnen-events' ),
			'parent_item_colon'  => __( 'Parent Events:', 'markoheijnen-events' ),
			'not_found'          => __( 'No events found.', 'markoheijnen-events' ),
			'not_found_in_trash' => __( 'No events found in Trash.', 'markoheijnen-events' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'List of all events I have been', 'markoheijnen-events' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => false,
			'rewrite'            => array( 'slug' => 'event' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'thumbnail' ),
			'menu_icon'          => 'dashicons-tickets-alt',
		);

		register_post_type( 'event', $args );
	}

	public function updated_messages( $messages ) {
		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		$messages['event'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Event updated.', 'markoheijnen-events' ),
			2  => __( 'Custom field updated.', 'markoheijnen-events' ),
			3  => __( 'Custom field deleted.', 'markoheijnen-events' ),
			4  => __( 'Event updated.', 'markoheijnen-events' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Event restored to revision from %s', 'markoheijnen-events' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Event published.', 'markoheijnen-events' ),
			7  => __( 'Event saved.', 'markoheijnen-events' ),
			8  => __( 'Event submitted.', 'markoheijnen-events' ),
			9  => sprintf(
				__( 'Event scheduled for: <strong>%1$s</strong>.', 'markoheijnen-events' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i', 'markoheijnen-events' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Event draft updated.', 'markoheijnen-events' )
		);

		if ( $post_type_object->publicly_queryable ) {
			$permalink = get_permalink( $post->ID );

			$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View event', 'markoheijnen-events' ) );
			$messages[ $post_type ][1] .= $view_link;
			$messages[ $post_type ][6] .= $view_link;
			$messages[ $post_type ][9] .= $view_link;

			$preview_permalink = add_query_arg( 'preview', 'true', $permalink );
			$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview event', 'markoheijnen-events' ) );
			$messages[ $post_type ][8]  .= $preview_link;
			$messages[ $post_type ][10] .= $preview_link;
		}

		return $messages;
	}


	public function add_meta_box() {
		add_meta_box(
			'event_information',
			__( 'Event information', 'markoheijnen-events' ),
			array( $this, 'metabox_information' ),
			'event',
			'advanced',
			'high'
	    );
	}

	public function metabox_information( $post ) {
		wp_enqueue_style( 'markoheijnen-events', plugins_url('styles.css', __FILE__) );

		wp_nonce_field( plugin_basename( __FILE__ ), 'metabox_information_nonce' );
		
		$types          = wp_get_post_terms( $post->ID, 'event-type', array( 'fields' => 'names' ) );
		$possible_types = array(
			'heart'     => 'liked-it',
			'megaphone' => 'speaker',
			'groups'    => 'organizer',
			'wordpress' => 'wordpress'
		);
		?>

		<div class="event-types">
			<?php
			foreach ( $possible_types as $icon => $term_slug ) {
				$checked = in_array( $term_slug, $types ) ? ' checked' : '';
				?>

				<label>
					<input type="checkbox" autocomplete="off" name="eventtypes[<?php echo $term_slug; ?>]"<?php echo $checked; ?>>
					<span class="dashicons dashicons-<?php echo $icon; ?>"></span>
				</label>

				<?php
			}
			?>
		</div>

		<?php
	}

	public function save_meta_box( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['metabox_information_nonce'] ) || ! wp_verify_nonce( $_POST['metabox_information_nonce'], plugin_basename( __FILE__ ) ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$term_keys = null;
		if ( isset( $_POST['eventtypes'] ) ) {
			$term_keys = array_keys( $_POST['eventtypes'] );
			$term_keys = array_map( 'sanitize_title', $term_keys );
		}

		wp_set_object_terms( $post_id, $term_keys, 'event-type' );
	}

}