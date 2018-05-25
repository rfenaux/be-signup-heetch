<?php

class FrmLocImport{

	private static $states_total = 4028;
	private static $cities_total = 51937;

	/**
	 * Delete all locations data
	 * @since 2.1
	 */
	public static function reset_import() {
		if ( isset( $_GET['loc_nonce'] ) && wp_verify_nonce( $_GET['loc_nonce'], 'reset_loc' ) ) {
			delete_option( 'frm_usloc_options' );
			foreach ( array( 'frm_loc_lookup', 'frm_loc_list' ) as $form_key ) {
				$form_id = FrmForm::getIdByKey( $form_key );
				if ( $form_id ) {
					FrmForm::destroy( $form_id );
				}
			}
		}
	}

	/**
	* Import locations, starting with the XML files
	*
	* @since 2.0
	*/
	public static function import_locations(){
		$data_to_import = FrmAppHelper::get_post_param( 'frm_import_files', '', 'sanitize_title' );

		$form = FrmForm::getOne('frm_loc_list');
		if ( ! $form ) {
			$file = dirname( dirname( __FILE__ ) ) .'/templates/locations-forms.xml';
			FrmXMLHelper::import_xml( $file );
		}

		$opts = get_option( 'frm_usloc_options' );
		$remaining = self::remaining_count( $opts, $data_to_import );

		include(dirname(dirname(__FILE__)) . '/views/importing_page.php');
	}

	public static function import_locations_csv( $opts ){
		$data_to_import = FrmAppHelper::get_param( 'data_to_import', '', 'post', 'sanitize_title' );

		$opts = self::get_option( 'frm_usloc_options' );

		$csv_func_name = 'import_' . $data_to_import;
		self::$csv_func_name( $opts );

		wp_die();
	}

	/**
	* Import the countries and states
	*
	* @since 2.0
	*/
	private static function import_countries_states( $opts ){
		echo self::import_global_states_csv( $opts );
	}

	/**
	* Import U.S. States, Counties, and Cities
	*
	* @since 2.0
	*/
	private static function import_states_cities( $opts ) {
		echo self::import_cities_with_counties_csv( $opts );
	}

	/**
	* Import the states/provinces CSV
	*
	* @since 2.0
	* @return int - number of imported states/provinces
	*/
	private static function import_global_states_csv( $opts ){
		self::maybe_initialize_option( 'states', $opts );

		if ( $opts['states'] < self::$states_total ) {
			// Get state form ID
			$form_id = FrmForm::getIdByKey( 'frm_loc_list' );
			if ( ! $form_id ) {
				return $opts['states'];
			}

			// Import states from CSV
			$filename = dirname( dirname( __FILE__ ) ) . '/locations_data/states.csv';
			$opts['states'] = FrmProXMLHelper::import_csv(
				$filename, $form_id,
				array(
					0 => FrmField::get_id_by_key( 'frm_loc_state' ),
					1 => FrmField::get_id_by_key( 'frm_loc_state_abr' ),
					2 => FrmField::get_id_by_key( 'frm_loc_country_code2' ),
					3 => FrmField::get_id_by_key( 'frm_loc_country' ),
					4 => FrmField::get_id_by_key( 'frm_loc_country_code3' ),
				),
				1, $opts['states'] + 1
			);

			update_option( 'frm_usloc_options', $opts );
		}

		return $opts['states'];
	}

	/**
	* Import the U.S. Cities CSV
	* Note: intended to be imported with U.S. Counties CSV
	*
	* @since 2.0
	* @return int - number of imported cities
	*/
	private static function import_cities_with_counties_csv( $opts ){
		self::maybe_initialize_option( 'cities', $opts );

		if ( $opts['cities'] < self::$cities_total ) {
			// Get city form ID
			$form_id = FrmForm::getIdByKey( 'frm_loc_list' );
			if ( ! $form_id ) {
				return $opts['cities'];
			}

			// Import cities from CSV
			$filename = dirname( dirname( __FILE__ ) ) . '/locations_data/us_cities.csv';
			$opts['cities'] = FrmProXMLHelper::import_csv( $filename, $form_id,
				array(
					1 => FrmField::get_id_by_key( 'frm_loc_city' ),
					2 => FrmField::get_id_by_key( 'frm_loc_county' ),
					4 => FrmField::get_id_by_key( 'frm_loc_state' ),
					5 => FrmField::get_id_by_key( 'frm_loc_state_abr' ),
					6 => FrmField::get_id_by_key( 'frm_loc_country_code2' ),
				), 1, $opts['cities'] + 1
			);

			update_option( 'frm_usloc_options', $opts );
		}

		return $opts['cities'];
	}

	private static function get_option( $opt_name ){
		$opts = get_option( $opt_name );
		if ( ! is_array( $opts ) ) {
			$opts = array();
		}

		return $opts;
	}

	private static function maybe_initialize_option( $item, &$opts ) {
		if ( ! isset( $opts[ $item ] ) ) {
			$opts[ $item ] = 1;
		}
	}

	private static function remaining_count( $opts, $data_to_import ){
		// Get number of imported entries
		$imported = $total = 0;

		$data_types = array( 'states' => 0, 'cities' => 0 );
		foreach ( $data_types as $loc => $loc_imported ) {
			if ( isset( $opts[ $loc ] ) ) {
				$data_types[ $loc ] = (int) $opts[ $loc ];
			}
		}

		// Get expected total
		if ( $data_to_import == 'countries_states' ) {
			$imported = $data_types['states'];
			$total = self::$states_total;
		} else if ( $data_to_import == 'states_cities' ) {
			$imported = $data_types['cities'];
			$total = self::$cities_total;
		}

		return $total - $imported;
	}
}