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

?>
		<hr />
		<div>
			<form id="date_form" action="" method="POST">
				
				From: <input type="text" id="dateFrom" class="date_picker" name="dateFrom" value="<?php echo $dateFrom ?>" size="9" />&nbsp;
				To: <input type="text" id="dateTo" class="date_picker" name="dateTo" value="<?php echo $dateTo ?>" size="9" />&nbsp;&nbsp;&nbsp;
		
				<select id="select_report">
					<option value="">-- Select Report --</option>
					<option value="dollars">Dollars</option>
					<option value="shipping">Shipping</option>
					<option value="pounds">Pounds</option>
					<option value="wholesale">Wholesale</option>
					<option value="affiliates">Affiliates</option>
				</select>
				<select name="choose_affiliate" style="display:none;" id="choose_affiliate">
					<option value="">-- Choose Affiliate --</option>
					<?php foreach ($affiliates as $key => $affiliate ) : ?> 
						<option value="<?php echo $affiliate; ?>"><?php echo $affiliate; ?></option>
						<?php endforeach; ?>
				</select>
					<span style="float:right;margin-right:15%;" id="print_report" class="button-primary">Print</span>
			</form>
		</div>
		<br />
		<hr />
	<div id="report"></div>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#select_report, .date_picker, #choose_affiliate').live('change', function() {
			report = $('#select_report').val();
			if (report == "affiliates") {
				$('#choose_affiliate').show();
			}else{
				$('#choose_affiliate').hide();
			}
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
				$('#updated').remove();
				$('#choose_affiliate').after("<span id='updated' style='font-size:1.4em;'>&nbsp;&nbsp;Report Updated</span>");
				$('#updated').delay(2000).fadeTo(2000, 0);
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
	});
	
</script>

<?php 
?>