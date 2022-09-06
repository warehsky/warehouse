window.setupIndicators = function(orders){
	let indicators = {};
	// if(orders){
	// 	indicators.ordersIndicator = new Indicator("#i-orders","Заказы",0,"/admin/orders");
	// 	indicators.badOrders = new Indicator("#i-bad","Заказы с просроченной оплатой",0,"/admin/orders?orderstimeout=1");
	// }
	
	let request = ()=>{
		$.ajax({
			// headers: { 'X-Access-Token': "<?= Auth::guard('admin')->user()->getToken() ?>" },
			headers: { 'X-Access-Token': Globals.api_token },
			url:'/Api/getAlerts',
			method:'get',
			dataType:'json',
			data:{ isusers:0 },
			success: (response)=>{
				
			}
		});
		// setTimeout(()=>{ request() },6000);
	}
	request();
	delete window.setupIndicators;
}