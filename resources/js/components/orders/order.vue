<template>
  <div class="orders">
    <div class="order-head">
      <span>№ заказа</span><span>{{this.order?this.order.id:''}}</span>
      <span>Дата заказа</span>
      <span><input 
              name="Дата"
              type="date"
              v-model="order.orderDate"
              min="today.toShortDateString()"/>
      </span>
      <span>Клиент</span><span>[#{{this.order?this.order.clientId:''}}]{{this.order?this.order.client:''}}</span>
      <input type="button" value="Выбрать" @click="modalOpened.clients = true;"/>
    </div>
    <div class="order-list">
      <div>Услуги</div>
      <table v-if="!updating && order.order_items.length>0" class="report-table">
			<thead>
				<td>ID</td>
				<td>Название</td>
				<td>Кол-во</td>
				<td>Цена</td>
				<td>Примечание</td>
				<td>Действие</td>
				<!-- <td></td> -->
			</thead>
			<tbody>
				<tr v-for="item in order.order_items" :key="item.id" :item="item">
					<td>{{item.id}}</td>
					<td>{{item.item}}</td>
					<td class="tdinput">
            <input type="number" v-model="item.quantity" min="0"/>
          </td>
					<td class="tdinput">
            <input type="number" v-model="item.price" min="0"/>
          </td>
					<td>
            <input type="text" v-model="item.note"/>
          </td>
					<td></td>
				</tr>
			</tbody>
		  </table>
      <div><input type="button" value="Добавить" @click="modalOpened.items = true;"/></div>
    </div>
    <div><input type="button" value="Сохранить" @click="saveOrder(order);"/></div>
    <modal class="waves-report-modal"
      v-show="modalOpened.clients"
      :show="['cancel']"
      cancel='Выход'
      @close="modalOpened.clients = false"
      @confirm="modalOpened.clients = false">
      <template #header>
        <h2 class="modalHeaderItem">Клиенты</h2>
      </template>
      <template #body>
        <clients-view ref="report"
          @select="onSelectClient">
        </clients-view>
      </template>
    </modal>
    <modal class="waves-report-modal"
      v-show="modalOpened.items"
      :show="['cancel']"
      cancel='Выход'
      @close="modalOpened.items = false"
      @confirm="modalOpened.items = false">
      <template #header>
        <h2 class="modalHeaderItem">Услуги</h2>
      </template>
      <template #body>
        <items-view ref="report"
          @select="onSelectItem">
        </items-view>
      </template>
    </modal>
  </div>
</template>

<script>
import CircleLoading from '../UI/mini/circle-loading.vue';
import modal from "../UI/panels/modal.vue";
import clientsView from "../clients/clients-view.vue";
import itemsView from "../items/items-view.vue";
export default {
  name:"order",
  props:{
    order_id:Number,
    d_from:String,
    d_to:String,
    status:[Number,String],
    wareh_url:String,
  },
  components: {
    modal,
    clientsView,
    itemsView
  },
  data(){
    return{
      isModalVisible: false,
      updating:false,
      modalOpened:{
        clients:false,
        items:false
      },
      today:new Date(),
      order: {"id":0,"orderDate":(new Date()).toShortDateString(),"clientId":0,"client":'', "order_items":[]}
    }
  },
  computed:{
    data(){
      return {
        order_id: null
      }
    }
  },
  mounted(){
     if(this.order_id)
       this.getOrder({"orderId":this.order_id});
  },
  methods:{
    getOrder(params){
			return new Promise((resolve,reject)=>{
				axios
					.get("/getOrder",{ params })
					.then(({data})=>{
            this.order = data.order;
            
					})
					.catch((e)=>{ console.error(e); reject(e) });
			})
		},
    setOrder(order){
      return new Promise((resolve,reject)=>{
        if(!order) reject(new Error("(setOrder) Не передан order"));
        axios
        .post('/saveOrder', {
          headers: {'X-Access-Token': Globals.api_token, "content-type": "application/json"},
          params: {
            order:{
              ...order
              
            },
          },
        },
        {headers: {'X-Access-Token': Globals.api_token, "content-type": "application/json"}})
        .then(response => {
          if(!response.data.error && response.data.code==200){
            if(order.id==0)
              order.id = response.data.order.id;
            resolve(response);
          }
          else {
            reject({ type:"thrown", response });
          }
        })
        .catch(error => {
          reject({ type:"catched", response:error });
        })
      })
    },
    saveOrder(order,resolve,reject){
      axios.defaults.headers.common["X-Access-Token"] = Globals.api_token;
      this.setOrder(order)
        .then(()=>this.freeOrder(order))
        .then(()=>resolve?.())
        .catch(error=>reject?.(error));
    },
    refuseOrder(order){//обработка клика кнопки отмены заказа
      if(!confirm("Подтвердите отмену заказа №"+order.id+" !"))
        return;
      order.status = 3;
      this.saveOrder(order);
      this.freeOrder(order);

     // window.location.reload();
    },
    onCancelEdit(order,draft){
      if(draft){
        let index = this.orders.indexOf(order);
        Vue.set(this.orders,index,new Order().set(draft));// изначально draft не реактивный
      }
      this.closeOrder();
      this.freeOrder(order);
    },
    tryTakeOrder(order, mode = this.modes.edit){
      if(this.editLoading>=0) return;
      this.editLoading = order.id;
      return axios.get("/checkOrderLock",{ params:{ orderId:order.id , edit:1 } })//заблокировать зказ
        .then(({ data })=>{
          if(data.lock.length) {
            let output = mode == this.modes.edit?"Заказ уже редактируется:\n":"Заказ занят.(Корректировка):\n";
            data.lock.forEach(moderator => {
              output +=  `Имя редактирующего: ${moderator.name} (id: ${moderator.id})\n`;
            });
            alert(output);
          } else switch(mode){
            case(this.modes.correct): case(this.modes.applyCorrect): this.correct(order); break;
            default:this.openOrder(order, mode);
          }
        }).catch((error)=>{
          console.log(error);
          alert("Ошибка: "+error.code);
        }).finally(()=>{
          this.editLoading = -1;
        });
    },
    freeOrder(order){
      return axios.get("/ordersUnlock",{ params:{ orderId:order.id || -1 } })//разблокировать зказ
        .then(({ data })=>{
          if(!data.success){
            console.error(data);
            return;
          }
          order.locked = false;
        }).catch((error)=>{
          console.log(error);
          alert("Ошибка: "+error);
        })
    },
    onSelectClient(client){
      this.modalOpened.clients = false;
      this.order.clientId=client.id;
      this.order.client=client.client;
    },
    onSelectItem(item){
      this.modalOpened.items = false;
      let itm = {"itemId":item.id, "item":item.item, "quantity":0, "price":0, "note":''};
      this.order.order_items.push(itm);
    },
    /**
     * @param {Order} order Заказ
     * @description Открывает заказ в режиме корректировки
     * TODO: replace /Api/getAlerts
     */
    async correct(order){
      let { data:{ corrects } } = await axios.get("/Api/getAlerts",{ headers:{ 'X-Access-Token': Globals.api_token } })
      this.openOrder(order, order.id in corrects?this.modes.applyCorrect:this.modes.correct);
    },
    _onClickEdit(order, isClone=false){
      let mode;
      if([1, 7].includes(order.status)) mode = this.modes.edit;
      else if(this.superEdit && order.status != 1 && order.status != 7) mode = this.modes.fixEdit;
      else return;
      if(isClone) return this.openOrder(order, mode);
      return this.tryTakeOrder(order, mode);
    },
  }
}
</script>

<style lang="scss">
.orders{
  .groupbox-actions{
    display: flex;
    flex-direction: column;
    .btn{
      white-space: initial;
      margin: 3px 0;
    }
  }
}
.btn{
  &.edit{
    position:relative;
    &.editLoading{
      background: #afafaf;
    }
    &.locked{
      background: #ffa8a8;
    }
  }
  &.isCorrects{
    background: var(--corrects-background, #ffc55a);
  }
}
button.unlock{
  position: relative;
}
.tdinput,.tdinput input {
  width: 100px;
}
</style>