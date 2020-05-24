<?php

/**
 * Meetings Controller
 *
 * @since   2.1.0
 * @author  Deepen
 */
class Zoom_Video_Conferencing_Admin_Webinars {

	public static $message = '';
	public $settings;

	public function __construct() {
	}

	/**
	 * View list meetings page
	 *
	 * @since   1.0.0
	 * @changes in CodeBase
	 * @author  Deepen Bajracharya <dpen.connectify@gmail.com>
	 */
	public static function list_webinars() {
		wp_enqueue_script( 'video-conferencing-with-zoom-api-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-select2-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-js' );

		//Check if any transient by name is available
		$users    = video_conferencing_zoom_api_get_user_transients();
		$webinars = false;
		if ( isset( $_GET['host_id'] ) ) {
			$encoded_meetings = zoom_conference()->listWebinar( $_GET['host_id'] );
			$decoded_meetings = json_decode( $encoded_meetings );
			if ( ! empty( $decoded_meetings->code ) ) {
				self::set_message( 'error', $decoded_meetings->message );
			} else {
				$webinars = $decoded_meetings->webinars;
			}
		}

		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] === "zoom-meetings" && isset( $_GET['new'] ) && $_GET['new'] === "zoom-video-conferencing-webinars-add" ) {
			self::add_webinar();
		} else {
			//Get Template
			require_once ZVC_PLUGIN_VIEWS_PATH . '/webinars/tpl-list-webinars.php';
		}
	}

	/**
	 * Add Meetings Page
	 *
	 * @since    1.0.0
	 * @modified 2.1.0
	 * @author   Deepen Bajracharya <dpen.connectify@gmail.com>
	 */
	private static function add_webinar() {
		wp_enqueue_script( 'video-conferencing-with-zoom-api-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-select2-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-timepicker-js' );

		//Edit a Meeting
		if ( isset( $_GET['edit'] ) && isset( $_GET['host_id'] ) ) {
			if ( isset( $_POST['update_meeting'] ) ) {
				self::update_webinar();
			}

			$meeting_info = json_decode( zoom_conference()->getWebinarInfo( $_GET['edit'] ) );

			//Get Editin Template
			require_once ZVC_PLUGIN_VIEWS_PATH . '/webinars/tpl-edit-webinar.php';
		} else {
			if ( isset( $_POST['create_meeting'] ) ) {
				self::create_webinar();
			}

			//Get Template
			require_once ZVC_PLUGIN_VIEWS_PATH . '/webinars/tpl-add-webinar.php';
		}
	}

	/**
	 * Update Meeting
	 *
	 * @since  2.1.0
	 * @author Deepen
	 */
	private static function update_webinar() {
		check_admin_referer( '_zoom_add_meeting_nonce_action', '_zoom_add_meeting_nonce' );

		$user_id      = sanitize_text_field( filter_input( INPUT_POST, 'userId' ) );
		$start_time   = filter_input( INPUT_POST, 'start_date' );
		$start_time   = gmdate( "Y-m-d\TH:i:s", strtotime( $start_time ) );
		$webinar_arrr = array(
			'topic'      => sanitize_text_field( filter_input( INPUT_POST, 'meetingTopic' ) ),
			'agenda'     => sanitize_text_field( filter_input( INPUT_POST, 'agenda' ) ),
			'start_time' => $start_time,
			'timezone'   => filter_input( INPUT_POST, 'timezone' ),
			'password'   => sanitize_text_field( filter_input( INPUT_POST, 'password' ) ),
			'duration'   => filter_input( INPUT_POST, 'duration' ),
			'settings'   => array(
				'host_video'        => filter_input( INPUT_POST, 'option_host_video' ),
				'panelists_video'   => filter_input( INPUT_POST, 'option_panelist_video' ),
				'hd_video'          => filter_input( INPUT_POST, 'option_hd_video' ),
				'auto_recording'    => filter_input( INPUT_POST, 'option_auto_recording' ),
				'alternative_hosts' => filter_input( INPUT_POST, 'alternative_host_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY )
			)
		);

		$meeting_updated = json_decode( zoom_conference()->updateMeetingInfo( $update_meeting_arr ) );
		if ( ! empty( $meeting_updated->code ) ) {
			self::set_message( 'error', $meeting_updated->message );
		} else {
			self::set_message( 'updated', __( "Updated meeting.", "video-conferencing-with-zoom-api" ) );
		}

		/**
		 * Fires after meeting has been Updated
		 *
		 * @since  2.0.1
		 */
		do_action( 'zvc_after_updated_meeting' );
	}

	/**
	 * Create a new Meeting
	 *
	 * @since  2.1.0
	 * @author Deepen
	 */
	private static function create_webinar() {
		check_admin_referer( '_zoom_add_meeting_nonce_action', '_zoom_add_meeting_nonce' );

		$user_id      = sanitize_text_field( filter_input( INPUT_POST, 'userId' ) );
		$start_time   = filter_input( INPUT_POST, 'start_date' );
		$start_time   = gmdate( "Y-m-d\TH:i:s", strtotime( $start_time ) );
		$webinar_arrr = array(
			'topic'      => sanitize_text_field( filter_input( INPUT_POST, 'meetingTopic' ) ),
			'agenda'     => sanitize_text_field( filter_input( INPUT_POST, 'agenda' ) ),
			'start_time' => $start_time,
			'timezone'   => filter_input( INPUT_POST, 'timezone' ),
			'password'   => sanitize_text_field( filter_input( INPUT_POST, 'password' ) ),
			'duration'   => filter_input( INPUT_POST, 'duration' ),
			'settings'   => array(
				'host_video'        => filter_input( INPUT_POST, 'option_host_video' ),
				'panelists_video'   => filter_input( INPUT_POST, 'option_panelist_video' ),
				'hd_video'          => filter_input( INPUT_POST, 'option_hd_video' ),
				'auto_recording'    => filter_input( INPUT_POST, 'option_auto_recording' ),
				'alternative_hosts' => filter_input( INPUT_POST, 'alternative_host_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY )
			)
		);

		$webinar_created = json_decode( zoom_conference()->createAWebinar( $user_id, $webinar_arrr ) );
		if ( ! empty( $webinar_created->code ) ) {
			self::set_message( 'error', $webinar_created->message );
		} else {
			self::set_message( 'updated', sprintf( __( "Created webinar %s at %s. Join %s", "video-conferencing-with-zoom-api" ), $webinar_created->topic, $webinar_created->created_at, "<a target='_blank' href='" . $webinar_created->join_url . "'>Here</a>" ) );

			/**
			 * Fires after meeting has been Created
			 *
			 * @since  2.0.1
			 */
			do_action( 'zvc_after_created_webinar', $webinar_created );
		}
	}

	static function get_message() {
		return self::$message;
	}

	static function set_message( $class, $message ) {
		self::$message = '<div class=' . $class . '><p>' . $message . '</p></div>';
	}
}

new Zoom_Video_Conferencing_Admin_Webinars();