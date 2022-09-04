import OrderItem from '../orderItem.js';
import Order from '../order';
import Repository from '../../../classes/Repository/Repository.js';
import OrdersFilters from '../../../classes/Repository/structures/OrdersFilters.js';
export default {
	data(){
		return{
      state:0,
      states:{
        error:-1,
        loading:0,
        loaded:1
      },
			sots:[],
			zones:[],
			waves:[],
			availableWaves:[],
			filterWaves:[],
			orders:[],
			payments:[],
			statuses:[],
      phonePrefixes:[],
      mode:0,
      modes:{
        default:-1,//режим просмотра списка заказов
        edit:0,//изменение заказа
        correct:1,//создание корректировки
        applyCorrect:2,//обработка корректировки
        fixEdit:3,//редактирование полей заказа(но не товаров)
      },
		}
	},
	mounted(){
    this.init();
	},
	methods:{
    async init(){
      try{
        this.phonePrefixes = await this.apiGetPefixes();
        this.phonePrefixes.forEach((prefix)=>{
          try{ prefix.images = JSON.parse(prefix.images); }
          catch{ prefix.images = []; }
        });
        await this.updateOrders();
        this.state = this.states.loaded;
      } catch{
        this.state = this.states.error;
        console.error(e);
      }
    },
    async apiGetPefixes(){
      return (await axios.get("/Api/getPhonePrefixes")).data.phonePrefixes;
    },
    /**
     * 
     * Обновляет список заказов, волн(необходимых для заказов), статусов и вариантов оплаты
     * @param {OrdersFilters} filters
     * Фильтры по заказам
     */
    async updateOrders(filters){
      try{
        const { orders, payments, statuses} = await this.getOrders(filters);
        this.waves = await this.getWaves();
        this.availableWaves = await this.getWaves(true);
        this.filterWaves = await this.getWaves(false,true);
        this.orders = orders.map(order=>order.set({
          ...order,
          items: [],
          itemsVisible: false,
          phonePrefix: this.getPrefix(order.phone),
          phone: order.phone ? order.phone.replace(this.getPrefix(order.phone),'') : '',
          phoneConsigneePrefix: this.getPrefix(order.phoneConsignee),
          phoneConsignee: order.phoneConsignee ? order.phoneConsignee.replace(this.getPrefix(order.phoneConsignee),'') : '',
          deliveryDate: new Date(order.deliveryDate).toShortDateString(),  
          waveId: order.waveId || this.waves[0].id,
          actions: true
        }));
        this.statuses = statuses;
        this.payments = payments;
      } catch(error){
        console.error(error);
        this.errored = true;
        if(this.alertRequestError(error)) this.updateOrders(filters);
      }
    },
    alertRequestError(error){
      return confirm("Ошибка "+(error?.request?.status || error)+". Повторить загрузку?");
    },
    /**
     * 
     * @param {OrdersFilters} filters 
     * @returns 
     */
    getOrders(filters){
      return Repository.getOrders({
        'dFrom': this.d_from,
        'dTo': this.d_to,
        'status': this.status,
        'nopay':this.searchParams?.get("nopay"),
        'orderstimeout':this.searchParams?.get("orderstimeout"),
        'orderId':this.searchParams?.get("orderId"),
        'phone':this.searchParams?.get("phone")
      }, filters);
    },
    getPrefix(phone){
      if(!phone) return '+38';
      if(phone[0]=='+') return phone[1]=='7'?'+7':'+38'
      let index = phone.indexOf("+");
      if(index<0) return "+38";
      else return phone.slice(0,index);
    },
    getWaves(onlyAvailable = undefined,groupByZone=false){
      return new Promise((resolve,reject)=>{
        axios
        .get("/Api/getTimeWaves"+(groupByZone?"Group":""),{ params:{ t:onlyAvailable } })
        .then(({data})=>{
          resolve(data.modify((item,index,set)=>{ set({ ...item, value:item.timeFrom.split(":",2).join(":")+" - "+item.timeTo.split(":",2).join(":"), disabled:Boolean(item.disabled) }); }))
        })
        .catch((e)=>{ reject(e); console.error(e); })
      })
    },
    updateItems(order){
      return axios.get("/Api/getOrderItems", { headers:{ "X-Access-Token":Globals.api_token }, params:{ orderId:order.id } })
        .then(({ data:items })=>{
          order.items = items.map(item=>new OrderItem(item));
        })
        .catch(console.error);
    },
    /**
     * 
     * @param {Order} order 
     * @param {Number} mode 
     * @returns undefined
     */
    openOrder(order, mode){
      let modes = Object.values(this.modes);
      let modeExists = mode in modes;
      this.mode = modeExists?mode:(this.modes.default || modes[0]);
      if(!modeExists) console.warn(`Mode ${mode} does not exists.`);
      return new Promise((resolve)=>{
        let edit = ()=>this.editableOrder = order;
        if(order.id > 0 && !order.items.length)
          this.updateItems(order).then(edit).then(resolve);
        else { edit();
          resolve();
        };
      })
    },
    /**
     * 
     * @param {*} filters 
     */
    filterOrders(filters={}){
      this.orders = [];
      this.state = this.states.loading;
      this.updateOrders(filters)
      .then(()=>this.state = this.states.loaded)
      .catch(()=>this.state = this.states.error);
    },
    closeOrder(){
      this.editableOrder = null;
    }
	}
}