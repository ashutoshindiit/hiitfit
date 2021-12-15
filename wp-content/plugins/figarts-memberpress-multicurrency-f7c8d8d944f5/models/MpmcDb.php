<?php

class MpmcDb {
	protected $version;

	public function __construct()
	{
		$this->version = get_option( 'mpmc_db_version', '1.0');
	}

	function upgrade(){

		global $wpdb;

		$table = $wpdb->prefix . MPMC_DB_NAME;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			subtxn_id int NOT NULL,
			type tinytext NOT NULL,
			currency tinytext NOT NULL,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		add_option( 'mpmc_db_version', MPMC_VERSION );

		if ( version_compare( $this->version, MPMC_VERSION ) < 0 ) {
			// Add SQL for upgrade
			update_option( 'mpmc_db_version', MPMC_VERSION );
		}

	}

}
