import OrderItem from "./orderItem";

let Order = class Order{
    constructor(props){
        props = props || {};
        let curDate = new Date();
        this.id = 0;
        this.number = "";
        this.date_time_created = Order.getDateTimeCreated(curDate);
        this.lat = props.lat || 0;
        this.lng = props.lng || 0;
        this.name = props.name || "";
        this.phonePrefix = props.phonePrefix || "+38";
        this.phoneConsigneePrefix = props.phoneConsigneePrefix || "+38";
        this.phone = props.phone || "";
        this.phoneConsignee = props.phoneConsignee || "";
        this.phoneMask = ""
        this.addr = props.addr || "";
        this.address = {
            houseReal:'',
            ...props.address
        }
        this.deliveryCost = props.deliveryCost || 0;
        this.deliveryZone = props.deliveryZone || 0;
        this.payment = props.payment || 1;
        this.deliveryZoneIn = props.deliveryZoneIn || 0;
        this.status = 1;
        this.note = props.note || '';
        this.noteSuffix = props.noteSuffix || '';
        this.deliveryFrom = props.deliveryFrom || null;
        this.deliveryTo = props.deliveryTo || null;
        this.deliveryDate = curDate.toShortDateString();
        this.itemsVisible = props.itemsVisible || false;
        this.items = props.items?props.items.map(i=>new OrderItem(i)):[];
        this.sum_total = props.sum_total || this.getSum();
        this.deviceType = props.deviceType || 2;
        this.deviceInfo = props.deviceInfo || navigator.userAgent;
        this.pension = props.pension || 0;
        this.waveId = props.waveId || 0;
        this.bonus = props.bonus || 0;
        this.bonus_pay = props.bonus_pay || 0;
        this.availableBonus = props.availableBonus || 0;
        this.bonusUser = props.bonusUser || 0;
        this.proc = props.proc || 0;
        this.discount = props.discount || null;
        this.promocode = props.promocode || "";
        this.actions = props.actions || true;
        this.loading = props.loading || false;
        this.gift = props.gift || 0;
        this.giftTitle = props.giftTitle || "";
        this.noteUser = "";
        this.tasksUser = [];
        this.workerId = Number(props.workerId)?props.workerId:-1;
        this.discountMethod = props.discountMethod || -1;
        this.course = props.course || 0;
        this.nopacks = Boolean(props.nopacks);
        this.locked = Boolean(props.locked);
        this.correctsColor = Boolean(props.correctsColor);
    }
    static get defaultAddress(){
        return {
            center:[37.80329, 48.003406],
            coords:[0,0],
            addr:""
        }
    }
    set(prop,value){
        this[prop] = value;
        return this;
    }
    set(props){
        Object.entries(props).forEach(([name,value]) => this[name]=value);
        return this;
    }
    getSum(discount = true, priceCallback=(price)=>price){
        let sum = 0;
        this.items.forEach(item=>{
            sum += priceCallback(discount?item.getCourierCost():item.price)*item.quantity;
        })
        return sum;
    }
    update(){
        this.sum_total = this.getSum();
    }
    validate(fields){
        return new Promise((resolve, reject)=>{
          for(let i = 0; i<fields.length;i++){
            let field = fields[i];
            if(!this[field.name] && this[field.name]!==0 || (field.func && !field.func(this[field.name]))){
              reject(field);
              return;
            }
          }
          resolve();
        })
    }
}
Order.isEqual = function(value,other){
    let first = new Order(value);
    let second = new Order(other);
    let exeptedProps = ["id","number","date_time_created","bonusUser","proc","itemsVisible","actions","loading","giftTitle","noteUser","tasksUser"];
    let entries = Object.entries(first);
    for(let i = 0;i<entries.length;i++){
        let entrie = entries[i];
        let name = entrie[0];
        let value = entrie[1];
        if(exeptedProps.includes(name)) continue;
        if(!_.isEqual(value ,second[name]))
            return false;
    }
    return true;
}
Order.diff = function(value,other){
    let first = new Order(value);
    let second = new Order(other);
    let exeptedProps = ["id","number","date_time_created","bonusUser","proc","itemsVisible","actions","loading","giftTitle","noteUser","tasksUser"];
    let entries = Object.entries(first);
    for(let i = 0;i<entries.length;i++){
        let entrie = entries[i];
        let name = entrie[0];
        let value = entrie[1];
        if(exeptedProps.includes(name)) continue;
        if(!_.isEqual(value ,second[name]))
            return { name, values:[value,second[name]] }
    }
    return false;
}
Order.getDateTimeCreated = function(date){
    return date.getFullYear()+"-" + 
    (date.getMonth()+1<10?"0":"")+(date.getMonth()+1)+"-"+
    (date.getDate()<10?"0":"")+date.getDate()+" "+
    (date.getHours()<10?"0":"")+date.getHours()+":"+
    (date.getMinutes()<10?"0":"")+date.getMinutes()+":"+
    (date.getSeconds()<10?"0":"")+date.getSeconds()
}
window.Order = Order;
export default Order;