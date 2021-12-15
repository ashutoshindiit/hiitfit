<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );} ?>
<?php
if ( isset( $sub ) and $sub->id > 0 ) :
	$currency_code = MpmcHelper::get_subtxn_curr_by_id( $sub->id, 'subscription' );
	if ( $currency_code ) {
		$currency                      = MpmcHelper::get_currency_by_code( $currency_code );
		$mepr_options->currency_symbol = $currency['symbol'];
	}
endif;
?>
<div class="wrap">
  <h2><?php _e( 'Edit Subscription', 'memberpress' ); ?></h2>

	<?php MeprView::render( '/admin/errors', get_defined_vars() ); ?>

  <div class="form-wrap">
	<form action="" method="post">
	  <input type="hidden" name="id" value="<?php echo $sub->id; ?>" />
	  <input type="hidden" name="period" value="<?php echo $sub->period; ?>" />
	  <input type="hidden" name="period_type" value="<?php echo $sub->period_type; ?>" />
	  <input type="hidden" name="limit_cycles" value="<?php echo $sub->limit_cycles; ?>" />
	  <input type="hidden" name="limit_cycles_num" value="<?php echo $sub->limit_cycles_num; ?>" />
	  <input type="hidden" name="limit_cycles_action" value="<?php echo $sub->limit_cycles_action; ?>" />
	  <table class="form-table">
		<tbody>
		  <tr valign="top"><th scope="row"><label><?php _e( 'Subscription ID:', 'memberpress' ); ?></label></th><td><?php echo $sub->id; ?></td></tr>
			<?php MeprView::render( '/admin/subscriptions/form', get_defined_vars() ); ?>
		</tbody>
	  </table>
	  <p class="submit">
		<input type="submit" id="submit" class="button button-primary" value="<?php _e( 'Update', 'memberpress' ); ?>" />
	  </p>
	</form>
  </div>
</div>
