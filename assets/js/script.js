jQuery(document).ready(function($){
	$("#thirdwatch-woocommerce-notice").click(function(){
		return data={action:"thirdwatch_woocommerce_admin_notice",thirdwatch_woocommerce_admin_nonce:thirdwatch_woocommerce_admin.thirdwatch_woocommerce_admin_nonce
		},$.post(ajaxurl,data),event.preventDefault(),!1
	});

	$('#button-purge').on('click', function(e) {
		if (!confirm('WARNING: All data will be permanently deleted from the storage. Are you sure you want to proceed with the deletion?')) {
			e.preventDefault();
		}
		else {
			$('#form-purge').submit();
		}
	});
});