<?php
			// If set, get date range from $_POST, if not create 1 month span
	if (!empty($_POST['dateFrom'])) {
        
       $dateFrom = $_POST['dateFrom'];
    }
    else {
        $dateFrom = date('m/d/Y', strtotime("-1 month"));    
    }
    
    if (!empty ($_POST['dateTo'])) {
            
        $dateTo = $_POST['dateTo'];
    }
        
    else {
       $dateTo = date('m/d/Y');
    }

	
// Set Time	

	// Set start date			
	$startTime = new DateTime($dateFrom);
	// Set end date
	$endTime = new DateTime($dateTo);
	
	//$date_array[] = $startTime->format("Y-m-d");
	
	while ($startTime < $endTime) {
		
		$startTime->add(new DateInterval('P1D'));
		$date_array[] = $startTime->format("Y-m-d");	
	}


?>
		<hr />
		<form id="date_form" action="" method="POST">
			
			From: <input type="text" class="date_picker" name="dateFrom" value="<?php echo $dateFrom ?>" size="9" />&nbsp;
			To: <input type="text" class="date_picker" name="dateTo" value="<?php echo $dateTo ?>" size="9" />&nbsp;&nbsp;&nbsp;
	
			<select id="select_report">
				<option value="">-- Select Report --</option>
				<option value="dollars">Dollars</option>
				<option value="shipping">Shipping</option>
				<option value="pounds">Pounds</option>
				<option value="wholesale">Wholesale</option>
			</select>	
		</form>
		<hr />
	<div id="report"></div>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#select_report, .date_picker').live('change', function() {
			report = $('#select_report').val();
			baseUrl = '<?php echo plugins_url('woocommerce-accounting/js/ajax/') ?>';
			safeUrl = baseUrl+report+'.php';
			$.ajax({
			type: 'POST',
			url: safeUrl,
			data: $('#date_form').serialize(),
			dataType: 'HTML'
			})
			.done(function(data) {
				console.log(data);
				$('#report').html(data);
				$('#select_report').after("<span id='updated' style='font-size:1.4em;'>&nbsp;&nbsp;Report Updated</span>");
				$('#updated').delay(1000).fadeTo(2000, 0.00);
			});
		});
		$('.date_picker').datepicker({numberOfMonths:[1,2]});
	});
	
</script>

<?php 
?>