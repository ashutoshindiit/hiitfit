<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );}

class MpmcCurrency extends MeprBaseMetaModel {

	private $table_name;

	/*** Instance Methods ***/
	public function __construct( $obj = null ) {
		parent::__construct( $obj );
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'mpmc_subtxn_currencies';

		$this->initialize(
			array(
				'id'        => 0,
				'subtxn_id' => '',
				'type'      => '',
				'currency'  => '',
				'time'      => current_time( 'mysql' ),
			),
			$obj
		);
	}


  public static function get_one($id, $return_type = OBJECT) {
		global $wpdb;		
		$mepr_db = new MeprDb();
		$args = compact('id');
		$table_name = $wpdb->prefix . 'mpmc_subtxn_currencies';

    return $mepr_db->get_one_record($table_name, $args, $return_type);
  }

  public static function get_one_by_type($subtxn_id, $type) {
    //error_log("********** MeprUtils::get_one_by_subscr_id subscr_id: {$subscr_id}\n");
    global $wpdb;
		$table_name = $wpdb->prefix . 'mpmc_subtxn_currencies';

    $sql = "
      SELECT id
			 FROM {$table_name}
       WHERE subtxn_id=%s
       AND type=%s
       ORDER BY id DESC
       LIMIT 1
    ";

    $sql = $wpdb->prepare($sql, $subtxn_id, $type);
    //error_log("********** MeprUtils::get_one_by_subscr_id SQL: \n" . MeprUtils::object_to_string($sql));

    $id = $wpdb->get_var($sql);
    //error_log("********** MeprUtils::get_one_by_subscr_id sub_id: {$sub_id}\n");

		if($id) {
      return new MpmcCurrency($id);
    }
    else {
      return false;
    }
  }


	/**
	 * Save transactions and subscriptions details
	 *
	 * @param  mixed $subtxn_id
	 * @param  mixed $type
	 * @param  mixed $currency
	 *
	 * @return void
	 */
	public function store() {
    if(isset($this->id) && !is_null($this->id) && (int)$this->id > 0) {
      $this->id = self::update($this);
    }
    else {
      $this->id = self::create($this);
    }
	}

  
  /**
   * create
   *
   * @param  mixed $subtxn
   * @return void
   */
  public static function create($subtxn) {
    $mepr_db = new MeprDb();

    if(is_null($subtxn->created_at)) {
      $subtxn->created_at = MeprUtils::db_now();
    }

    $args = $subtxn->get_values();

    return $mepr_db->create_record($subtxn->table_name, $args, false);
  }
  
  /**
   * update
   *
   * @param  mixed $subtxn
   * @return void
   */
  public static function update($subtxn) {
    $mepr_db = new MeprDb();
    $args = $subtxn->get_values();

    $str = MeprUtils::object_to_string($args);

    return MeprHooks::apply_filters('mepr_update_subscription', $mepr_db->update_record($mepr_db->subscriptions, $subtxn->id, $args), $args, $subtxn->user_id);
  }


	/**
	 * Save transactions and subscriptions details
	 *
	 * @param  mixed $subtxn_id
	 * @param  mixed $type
	 * @param  mixed $currency
	 *
	 * @return void
	 */
	public function destroy() {
		global $wpdb;
		$query = $wpdb->insert(
			$this->table_name,
			array(
				'subtxn_id' => $subtxn_id,
				'type'      => $type,
				'currency'  => $currency,
				'time'      => current_time( 'mysql' ),
			)
		);
	}

}
