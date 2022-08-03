window.setupIndicators = function(orders, messages, notification){
	let indicators = {};
	if(orders){
		indicators.ordersIndicator = new Indicator("#i-orders","Заказы",0,"/admin/orders");
		indicators.badOrders = new Indicator("#i-bad","Заказы с просроченной оплатой",0,"/admin/orders?orderstimeout=1");
	}
	if(messages){
		indicators.messagesIndicator = new Indicator("#i-messages","Сообщения",0,"/admin/chat");
	}
	let needNotify = false;
	let resetNotify = ()=>{ needNotify = true; }
	if(notification) needNotify = !GTimer.continue("notify",resetNotify);
	let request = ()=>{
		$.ajax({
			// headers: { 'X-Access-Token': "<?= Auth::guard('admin')->user()->getToken() ?>" },
			headers: { 'X-Access-Token': Globals.api_token },
			url:'/Api/getAlerts',
			method:'get',
			dataType:'json',
			data:{ isusers:0 },
			success: (response)=>{
				window.dispatchEvent(new CustomEvent("getAlerts",{ detail:response }));
				indicators.messagesIndicator?.setValue(response.mesages);
				indicators.ordersIndicator?.setValue(response.orders);
				indicators.badOrders?.setValue(response.orderstimeout.length);
				if(response.mesages && needNotify){
					window.notify = (alt)=>{
						let msg = `(${response.mesages}) ${response.mesages>1?"непрочитанных сообщений":"непрочитанное сообщение"} от клиента!`;
						if(!alt) { 
							window.notification = new Notification("Чат админпанели",{ 
								body:msg,
								data:{ link:"/admin/chat" },
								silent:false,
								requireInteraction:true,
								renotify:true,
								tag:"chat"
							});
							needNotify = false;
							window.notification.addEventListener("close",()=>{ new GTimer("notify",resetNotify,60000,1000) });
							window.notification.addEventListener("click",(e)=>{ new GTimer("notify",resetNotify,60000,1000); window.location.href = e.target.data.link; })
						}
					}
					switch(Notification.permission){
						case("granted"):notify(); break;
						case("denied"):notify(true); break;
						case("default"):Notification.requestPermission().then((permission)=>{ notify(permission!="granted"); })
					}
				}
			}
		});
		setTimeout(()=>{ request() },6000);
	}
	request();
	delete window.setupIndicators;
}