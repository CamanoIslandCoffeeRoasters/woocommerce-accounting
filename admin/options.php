<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>
<?php 

global $wpdb;

?>
<?php $sources = $wpdb->get_col("SELECT distinct(source) FROM {$wpdb->prefix}subscriptions WHERE source != '' ORDER BY source ASC", 0); ?>

<?php $affiliates = get_option('accounting_affiliates'); ?>

<div id="payment-methods-settings">
    <div class="wrap">
        <h2>Payment Method Settings</h2>
        <div id='message' class='updated fade hidden'><p><strong></strong></p></div>
        <form method="POST" action="options.php">
            <?php settings_fields( 'woocommerce_accounting_group' ); ?>
            <?php do_settings_sections( 'woocommerce_accounting_group' ); ?>            
            <table class="form-table">
                <tbody>
                    <th scope="row"><label for="blogname">Select Affiliates</label></th>
                    <?php foreach ($sources as $source) : ?>
                    	<tr>
                    		<td><input name="accounting_affiliates[]" type="checkbox" value="<?php echo $source; ?>" <?php echo in_array($source, $affiliates) ? 'checked="checked"' : '' ?> /></td><td><h4><?php echo $source; ?></h4></td>
                    	</tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
</div>