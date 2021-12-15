<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );} ?>
<?php
if ( isset( $txn ) and $txn->id > 0 ) :
	$currency_code = MpmcHelper::get_subtxn_curr_by_id( $txn->id, 'transaction' );
	if ( $currency_code ) {
		$currency                      = MpmcHelper::get_currency_by_code( $currency_code );
		$mepr_options->currency_symbol = $currency['symbol'];
	}
endif;
?>
<div class="wrap">
  <h2><?php _e( 'Edit Transaction', 'memberpress' ); ?></h2>

	<?php
	MeprView::render( '/admin/errors', get_defined_vars() );

	$pm = $mepr_options->payment_method( $txn->gateway );
	if ( ! is_object( $pm ) ) {
		$pm = (object) array(
			'label' => __( 'Unknown', 'memberpress' ),
			'name'  => __( 'Deleted Gateway', 'memberpress' ),
		);
	}
	?>

  <div class="form-wrap">
	<form action="" method="post">
		<?php if ( isset( $txn ) and $txn->id > 0 ) : ?>
		<input type="hidden" name="id" value="<?php echo $txn->id; ?>" />
		<?php endif; ?>
	  <table class="form-table">
		<tbody>
		  <tr valign="top"><th scope="row"><label><?php _e( 'Transaction ID:', 'memberpress' ); ?></label></th><td><?php echo $txn->id; ?></td></tr>
			<?php MeprView::render( '/admin/transactions/trans_form', get_defined_vars() ); ?>
		</tbody>
	  </table>
	  <p class="submit">
		<input type="submit" id="submit" class="button button-primary" value="<?php _e( 'Update', 'memberpress' ); ?>" />
	  </p>
	</form>
  </div>
</div>