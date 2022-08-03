import OrdersFilters from "./structures/OrdersFilters.js";
import OrdersGetParams from "./structures/OrdersGetParams.js";
import OrdersLimit, { OrdersLimitDate } from "./structures/OrdersLimit.js";
import OrdersResponse from "./structures/OrdersResponse.js";
import UserCoupon from "./structures/UserCoupon.js";

export default class Repository{
	/**
	 * 
	 * @param {OrdersFilters} filters 
	 * @param {OrdersGetParams} params
	 * @returns {Promise<OrdersResponse>}
	 */
	static getOrders(params, filters){
		params = params || {};
		filters = new OrdersFilters(filters);
		if(this.getOrdersRequest) this.getOrdersRequest.cancel("repeated request.");
		const CancelToken = axios.CancelToken;
		this.getOrdersRequest = CancelToken.source();
		const paramsEntries = Object.entries(params).concat(Object.entries(filters));
		const requestParams = Object.fromEntries(paramsEntries.filter(e=>e[1]));
		const api = '/Api/getOrders';
		const headers = { 'X-Access-Token': Globals.api_token };
		if(filters.length){
			let filterParams = new URLSearchParams(location.search);
      Object.entries(requestParams).forEach((p)=>{//объединяем текущие параметры с фильтрами
        if(p[0] in filters) filterParams.set(p[0],p[1]);
      });
      history.pushState({},"", `${location.pathname}?${filterParams.toString()}`);
		}
		return new Promise((resolve,reject)=>{
			axios.get(api, { headers, params:requestParams, cancelToken: this.getOrdersRequest.token }).
			then((e)=>resolve(new OrdersResponse(e.data)))
			.catch((error)=>{
				if (!axios.isCancel(error)) return reject(error);
				console.warn(`Request canceled (${api}): ${error.message}`);
			});
		}) 
	}
	/**
	 * 
	 */
	static async getWavesReport(dateFrom, dateTo, waves){
		const headers = { "X-Access-Token":Globals.api_token };
		const params = { d1, d2, wId:waves.map(wave=>wave.id).join(",") };
		try{
			let {data} = await axios.get("/Api/getWavesReport", { headers, params })
			if(data.error) throw new Error("GET "+window.location.origin+"/Api/getWavesReport "+data.error);
			return data.waves.map(item=>Object({ ...waves.find((w)=>item.wId.split(",").includes(String(w.id))), id:item.wId, count:item.count }));
		} catch (e){ console.error(e); }
	}
	/**
	 * @param {OrdersLimitDate} limitDate
	 * @returns {OrdersLimit}
	 */
	static async getOrdersLimit(limitDate){
		const params = { date:limitDate.date, timeFrom:limitDate.timeFrom, timeTo:limitDate.timeTo };
		try{
			let {data} = await axios.get("/Api/getOrdersLimit", { params });
			return new OrdersLimit(data);
		} catch (e){ console.error(e); }
	}
	/**
	 * 
	 * @param {String} phone Номер телефона
	 * @returns {Promise<Array<UserCoupon>>}
	 */
	static async getUserCoupons(phone){
		if(!phone) throw new Error(`Parameter "phone" is required.`);
		try{
			let {data} = await axios.get("/Api/getUserCoupons",{ params: { phone } });
			return data.map(c=>new UserCoupon(c));
		} catch(e){ console.error(e); }
	}
	static getOption(name){
		return axios.get("/Api/getOption", { params:{ opt:name } }).then(({data})=>data.opt);
	}
	static setOption(name, value){
		return axios.get("/Api/setOption", { params:{ field:name, value } }).then(({data})=>data.opt);
	}
}