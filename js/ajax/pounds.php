<?php

	require( $_SERVER['DOCUMENT_ROOT'].'/wp-load.php' );

	global $wpdb;

	$dateFrom = date('Y-m-d', strtotime($_POST['dateFrom']));
	$dateTo = date('Y-m-d', strtotime($_POST['dateTo']));

    $dateFromSQL = date("Y-m-d", strtotime($dateFrom) - 60 * 60 * 24);
    $dateFromSQL = $dateFromSQL . " 20:45:01";
    $dateToSQL = $dateTo . " 20:45:00";

	$total_pounds =  $wpdb->get_var("SELECT SUM(REPLACE(REPLACE(meta.meta_value, '12oz', '0.75'), 'lb', '') * (meta1.meta_value))
									FROM {$wpdb->posts} posts
									JOIN {$wpdb->prefix}woocommerce_order_items items
										ON posts.ID = items.order_id
									JOIN {$wpdb->prefix}woocommerce_order_itemmeta meta
										ON ((items.order_item_id = meta.order_item_id)
										AND (meta.meta_key = 'pa_pack'))
									JOIN {$wpdb->prefix}woocommerce_order_itemmeta meta1
										ON ((items.order_item_id = meta1.order_item_id)
										AND (meta1.meta_key = '_qty'))
									WHERE posts.post_date
                                        BETWEEN '$dateFromSQL'
                                            AND '$dateToSQL'
									AND posts.post_status IN ('wc-completed', 'wc-refunded')

								");

	$free_pounds =  $wpdb->get_var("SELECT COUNT(items.order_id)
									FROM {$wpdb->posts} posts
									LEFT JOIN {$wpdb->prefix}woocommerce_order_items items ON posts.ID = items.order_id
									WHERE posts.post_date
                                        BETWEEN '$dateFromSQL'
                                            AND '$dateToSQL'
									AND posts.post_status IN ('wc-completed', 'wc-refunded')
									AND items.order_item_name LIKE '%Free Pound%'
								");


	$in_state_total_pounds = $wpdb->get_var("SELECT SUM(REPLACE(REPLACE(itemmeta.meta_value, '12oz', '0.75'), 'lb', '') * (meta1.meta_value))
												FROM {$wpdb->posts} posts
												LEFT JOIN {$wpdb->postmeta} meta
													ON posts.ID = meta.post_id
												LEFT JOIN {$wpdb->prefix}woocommerce_order_items items
													ON posts.ID = items.order_id
												LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta itemmeta
													ON ((items.order_item_id = itemmeta.order_item_id)
													AND (itemmeta.meta_key = 'pa_pack'))
												LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta meta1
													ON ((items.order_item_id = meta1.order_item_id)
													AND (meta1.meta_key = '_qty'))
												WHERE posts.post_date
                                                    BETWEEN '$dateFromSQL'
                                                        AND '$dateToSQL'
												AND posts.post_status IN ('wc-completed', 'wc-refunded')
												AND ((meta.meta_key = '_shipping_state') AND (meta.meta_value = 'WA'))"
							);

	$out_state_total_pounds = $wpdb->get_var("SELECT SUM(REPLACE(REPLACE(itemmeta.meta_value, '12oz', '0.75'), 'lb', '') * (meta1.meta_value))
												FROM {$wpdb->posts} posts
												LEFT JOIN {$wpdb->postmeta} meta
													ON posts.ID = meta.post_id
												LEFT JOIN {$wpdb->prefix}woocommerce_order_items items
													ON posts.ID = items.order_id
												LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta itemmeta
													ON ((items.order_item_id = itemmeta.order_item_id)
													AND (itemmeta.meta_key = 'pa_pack'))
												LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta meta1
													ON ((items.order_item_id = meta1.order_item_id)
													AND (meta1.meta_key = '_qty'))
												WHERE posts.post_date
                                                    BETWEEN '$dateFromSQL'
                                                        AND '$dateToSQL'
												AND posts.post_status IN ('wc-completed', 'wc-refunded')
												AND ((meta.meta_key = '_shipping_state') AND (meta.meta_value != 'WA'))"
							);

    $in_state_free_pounds = $wpdb->get_var("SELECT COUNT(items.order_id)
                                                FROM {$wpdb->posts} posts
                                                LEFT JOIN {$wpdb->postmeta} meta
                                                    ON ((posts.ID = meta.post_id) AND (meta.meta_key = '_shipping_state'))
                                                LEFT JOIN {$wpdb->prefix}woocommerce_order_items items
                                                    ON posts.ID = items.order_id
                                                WHERE posts.post_date
                                                    BETWEEN '$dateFromSQL'
                                                        AND '$dateToSQL'
                                                AND meta.meta_value = 'WA'
                                                AND posts.post_status IN ('wc-completed', 'wc-refunded')
                                                AND items.order_item_name LIKE '%Free Pound%'"
                            );

    $out_state_free_pounds = $wpdb->get_var("SELECT COUNT(items.order_id)
                                                FROM {$wpdb->posts} posts
                                                LEFT JOIN {$wpdb->postmeta} meta
                                                    ON ((posts.ID = meta.post_id) AND (meta.meta_key = '_shipping_state'))
                                                LEFT JOIN {$wpdb->prefix}woocommerce_order_items items
                                                    ON posts.ID = items.order_id
                                                WHERE posts.post_date
                                                    BETWEEN '$dateFromSQL'
                                                        AND '$dateToSQL'
                                                AND meta.meta_value != 'WA'
                                                AND posts.post_status IN ('wc-completed', 'wc-refunded')
                                                AND items.order_item_name LIKE '%Free Pound%'"
                            );


	$in_state_total_pounds = number_format($in_state_total_pounds, 2,'.', ',');
	$out_state_total_pounds = number_format($out_state_total_pounds, 2,'.', ',');
	$total_pounds = number_format($total_pounds, 2,'.', ',');

	$in_state_free_pounds = number_format($in_state_free_pounds, 2,'.', ',');
	$out_state_free_pounds = number_format($out_state_free_pounds, 2,'.', ',');
	$free_pounds = number_format($free_pounds, 2,'.', ',');

	$columns = array("In-State", "Out-of-State", "Total");
	$rows = array("Total Pounds" =>  array($in_state_total_pounds, $out_state_total_pounds, $total_pounds),
				  "Free Pounds" =>   array($in_state_free_pounds,  $out_state_free_pounds,  $free_pounds )
				  );
	?>

	<?php if ($total_pounds) : ?>
	<div>
		<h1>Pounds</h1>
		<table class="widefat fixed exportable">
			<thead>
				<tr>
					<th></th>
				<?php foreach ($columns as $column) : ?>
					<th> <?php echo $column ?> </th>
				<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($rows as $row => $values) : ?>
				<tr>
					<td><?php echo $row ?></td>
					<?php foreach ($values as $value) : ?>
					<td><?php echo $value ?></td>
					<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>
