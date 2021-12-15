<div class="mp_wrapper mp-form-row" style="position:relative"> 
<?php
if ( 'before_form' == $position ) {
	echo '<form action="">';}
?>

	<select name="mpmc_currency_switcher" id="mpmc_currency_switcher" data-prdid="<?php echo $product_id; ?>" >
	<?php
		printf( '<option value="%s">%s - %s (%s)</option>', $base_currency['code'], $base_currency['code'], $base_currency['name'], $base_currency['symbol'] );
	?>
		<?php
		if ( $product_currencies ) {
			?>
				<?php
				foreach ( $product_currencies as $curr_code ) {
					$curr_attr = MpmcHelper::get_currency_by_code( $curr_code );
					printf( '<option value="%s">%s - %s (%s)</option>', $curr_code, $curr_code, $curr_attr['name'], $curr_attr['symbol'] );
				}
				?>

			<?php
		}
		?>
		</select>
		<span id="rolling" style="
				position: absolute;
				top: 0;
				bottom: 0;
				right: 4%;
				margin: auto;
				height: 20px;
				display: none;
		"><img src="<?php echo MPMC_DIRURI; ?>assets/rolling.gif" alt="" style="
				display:block
		"></span>


<?php
if ( 'before_form' == $position ) {
	echo '</form>';}
?>
</div>