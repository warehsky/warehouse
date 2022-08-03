export default class OrdersFilters{
	/** Количество применимых фильтров, тоесть (значение которых не равно undefined) */
	get length(){
		return Object.entries(this).filter(p=>p[1]!=undefined).length;
	}
	constructor(props){
		props = props || {};
		this.orderId = props.orderId || undefined;
		this.number = props.number || undefined;
		this.corrects = props.corrects || undefined;
	}
}