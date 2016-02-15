<?php
	     // If set, get date range from $_POST, if not create 1 month span
     if (!empty($_POST['dateFrom'])) {

       $dateFrom = $_POST['dateFrom'];
    }
    else {
        $dateFrom = date('m/01/Y', strtotime("-1 month"));
    }

    if (!empty ($_POST['dateTo'])) {

        $dateTo = $_POST['dateTo'];
    }

    else {
       $dateTo = date('m/t/Y', strtotime('-1 month'));
    }

	$affiliates = get_option('accounting_affiliates');

    $reports = array('Dollars', 'Shipping', 'Pounds', 'Orders', 'Wholesale', 'Affiliates', 'Merchandise');

?>
		<hr />
        <script src="<?php echo plugins_url("/woocommerce-accounting/js/jquery.json.min.js") ?>"></script>
		<div>
			<form id="date_form" action="" method="POST">

				From: <input type="text" id="dateFrom" class="date_picker" name="dateFrom" value="<?php echo $dateFrom ?>" size="9" />&nbsp;
				To: <input type="text" id="dateTo" class="date_picker" name="dateTo" value="<?php echo $dateTo ?>" size="9" />&nbsp;&nbsp;&nbsp;

				<select id="select_report">
                    <option value="">-- Select Report --</option>
                    <?php foreach ( $reports as $report ) { ?>
                        <option value="<?php echo strtolower($report) ?>"><?php echo $report ?></option>
                    <?php } ?>
				</select>
				<select name="choose_affiliate" style="display:none;" id="choose_affiliate">
					<option value="">-- Choose Affiliate --</option>
					<?php foreach ($affiliates as $key => $affiliate ) : ?>
						<option value="<?php echo $affiliate; ?>"><?php echo $affiliate; ?></option>
						<?php endforeach; ?>
				</select>
                <input style="display:none;" type="text" name="add_item_search" id="add_item_search" size="15" class="add-item-search" placeholder="Search Products . . . " />
				<span id="submit_report" class="button-primary">Submit</span>
					<span style="float:right;margin-right:15%;" id="print_report" class="button-primary">Print</span>
                    <span style="float:right;margin-right:1%;" id="export_report" class="button-primary">Export</span>
			</form>
		</div>
		<br />
		<hr />
	       <div id="report"></div>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		    $('#date_form').on('change', '#select_report, #choose_affiliate', function() {
                // Hide everything to start, and only show what's needed
                $('#choose_affiliate, #add_item_search').hide();
                // Capture the value of the selected option in the dropdown, to be used to build a string for the ajax file to ping
                report = $('#select_report').val();
                // Show the Affiliates dropdown
    			if (report == "affiliates") {
    				$('#choose_affiliate').show();
                // Show the merchandise Search dropdown
    			}else if (report == "merchandise") {
    				$('#add_item_search').show();
    			}
			});

		    $('#submit_report').on("click", function() {
		    $(this).text("Loading . . . ").attr("disabled", true);
			baseUrl = '<?php echo plugins_url('woocommerce-accounting/js/ajax/') ?>';
			safeUrl = baseUrl+report+'.php';
			$.ajax({
    			type: 'POST',
    			url: safeUrl,
    			data: $('#date_form').serialize(),
    			dataType: 'HTML'
			})
			.done(function(data) {
				$('#report').html(data);
				$('#submit_report').text("Submit").attr("disabled", false);
			});
		});

		$('.date_picker').datepicker({numberOfMonths:[1,2]});

		$('#print_report').on("click", function() {
			var date = [$('#dateFrom').val(), $('#dateTo').val()];
			var print_report = window.open('', 'Accounting', 'height=800,width=1000');
			var print_content = document.getElementById('report').innerHTML;

	        print_report.document.write('<html><head><style>table{border:3px solid black;}tr{border:1px solid black;}</style>');
	        print_report.document.write('<title>'+report+' - '+date[0]+' - '+date[1]+'</title>');
	        print_report.document.write('<h2>'+date[0]+' - '+date[1]+'</h2>')
	        print_report.document.write('</head><body>');
	        print_report.document.write(print_content);
	        print_report.document.write('</body></html>');

        return true;
		});

        $('#report').on('click', '.action', function() {
            order_id = $(this).attr("id");
            if ($(this).text() == "Cancel") {
                $("#order_details_"+order_id).slideUp();
                $('button', parent_row).text('Items');
            }else {
                parent_row = $(this);
                exists = $("#order_details_"+order_id);
                parent_row.parent().slideDown(1200).after("<div style='height:200px;display:none;' class='loading-order-details'></div>");
                $('.loading-order-details').slideDown(600);
                $('button.actions').text("Items");
                $('button', parent_row).text('Cancel');
                $('[id^="order_details_"]').remove();
                if (exists.length == 0) {
                    safeUrl = '<?php echo plugins_url('woocommerce-accounting/js/ajax/get-order.php') ?>';
                    $.ajax({
                        type: 'POST',
                        url: safeUrl,
                        dataType: 'HTML',
                        data: {order_id: order_id}
                    })
                    .done(function(response) {
                        $('.loading-order-details').remove();
                        parent_row.parent().after(response);
                    });
                }else {
                    $("#order_details_"+order_id).remove();
                    $('button', parent_row).text('Items');
                }
            }
        });

        $('#date_form').on('click', "#export_report", function() {
            table_data = new Array();
            $('#report table.exportable tr').each(function(row, tr) {
                cell = (row == 0) ? "th" : "td";
                columns = ($(tr).children(cell).length);
                table_data[row] = Object();

                for (i = 0; i < columns; i++) {
                    table_data[row][i] = ($(tr).find(cell+':eq('+i+')').text()).trim();
                }
            });
            // 1IMPORTANT Convert to JSON (required jQuery plugin)
            table_data = $.toJSON(table_data);
            // Send to Export file, which returns a downloaded CSV
            document.location.href = "<?php echo plugins_url("/woocommerce-accounting/js/ajax/export.php") ?>?report="+report+"&table_data="+table_data;
        });
	});


</script>
