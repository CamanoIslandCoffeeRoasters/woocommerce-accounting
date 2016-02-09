<?php
    require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

    global $woocommerce;

    // echo $_POST['order_id'];
    $order_id = $_POST['order_id'];
    $_order = new WC_Order($order_id);
    $items = $_order->get_items('line_item');
    $totals_items = $order_weight = $order_total = $order_tax = '';
?>
    <tr class="order_details" id="order_details_<?php echo $order_id ?>">
        <td colspan='7'>
            <div class="at-a-glance-parent" style="box-shadow:0px 0px 3px 3px grey;">
                <div class="at-a-glance-child" style="text-align:start;padding:0px;">
                    <table class="widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Weight</th>
                                <th>Price</th>
                                <th>Tax</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($items as $item) {
                                $_product = get_product((isset($item['variation_id']) && ($item['variation_id'] != 0)) ? $item['variation_id'] : $item['product_id']);

                                $totals_items    += $item['qty'];
                                $order_weight    += ($_product->get_weight() * $item['qty']);
                                $order_total     += $item['line_total'];
                                $order_tax       += $item['line_tax'];

                                echo "<tr>";
                                echo "<td>";
                                echo $item['name'];
                                echo "</td>";
                                echo "<td>";
                                echo $item['qty'];
                                echo "</td>";
                                echo "<td>";
                                echo $_product->get_weight() * $item['qty'] . ' lbs';
                                echo "</td>";
                                echo "<td>";
                                echo wc_price($item['line_total']);
                                echo "</td>";
                                echo "<td>";
                                echo ($item['line_tax'] > 0) ? wc_price($item['line_tax']) : "$0.00";
                                echo "</td>";
                                echo "</tr>";
                            } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <?php $totals = array("Totals", $totals_items, $order_weight . ' lbs', wc_price($order_total), wc_price($order_tax));
                            foreach ($totals as $total) { ?>
                                <td style="background-color:#E0E0E0;font-weight:900;">
                                    <strong><?php echo $total; ?></strong>
                                </td>
                            <?php } ?>
                        </tr>
                    </tfoot>
                </table>
                </div>
            </div>
        </td>

    </tr>
