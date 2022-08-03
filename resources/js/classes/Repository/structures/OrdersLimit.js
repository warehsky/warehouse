export class OrdersLimitDate{
    /**
     * 
     * @param { { date:String, timeFrom:String, timeTo:String } } date 
     */
    constructor({ date = null, timeFrom = null, timeTo = null }={}){
        this.date = date;
        this.timeFrom = timeFrom;
        this.timeTo = timeTo;
    }
    get dateStart(){
        return new Date(`${this.date} ${this.timeStart}`);
    }
    get dateEnd(){
        return new Date(`${this.date} ${this.timeEnd}`);
    }
}
export class OrdersLimitWave{
    constructor(data){
        data = data || {};
        this.id = data.id || 0;
        this.zoneId = data.zoneId || 0;
        this.timeFrom = data.timeFrom || "";
        this.timeTo = data.timeTo || "";
        this.orderLimit = data.orderLimit || 0;
        this.description = data.description || "";
        this.orders = data.orders || 0;
        this.status = data.status || 0;
    }
}
export default class OrdersLimit{
    /**
     * 
     * @param { { date:String, timeFrom:String, timeTo:String, value:Array<OrdersLimitWave> } } data 
     */
    constructor(data){
        data = data || {};
        this.date = new OrdersLimitDate({ date:data.date, timeFrom:data.timeFrom, timeTo:data.timeTo });
        this.waves = data.value?.map(v=>new OrdersLimitWave(v)) || [];
    }
}
