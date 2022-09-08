<template>
  <div class="orders">
    <orders-view 
      v-if="!editableOrder"
      v-bind="data"
      :orders="orders"
      :phone="phone"
      :dateRange="[d_from,d_to]"
      :stat="status"
      :filterWaves="filterWaves"
      :state="state"
      :showCorrects="showCorrects"
      :showDelivery="showDelivery"
      :correctsCount="correctsOrders.length"
      @order-created="orders.unshift($event); openOrder($event, modes.edit);"
      @refuse="refuseOrder"
      @requestItems="updateItems"
      @filter="filterOrders"
      @edit="_onClickEdit"
      @order-cloned="_onClickEdit($event, true)">
      <template #actions="{ order }">
        <async-button v-if="[1, 7].includes(order.status) || superEdit"
          :class="['btn edit',{ locked:order.locked }]"
          @click="_onClickEdit(order)">
          {{order.locked?'Заблокировано':'Изменить'}}
        </async-button>
        <async-button v-if="showCorrects && [7, 2, 5].includes(order.status)"
          :class="['btn edit',{ isCorrects:order.correctsColor, locked:order.locked }]"
          :style="{ '--corrects-background':order.correctsColor }"
          @click="tryTakeOrder(order,modes.correct)">
          {{order.locked?'Заблокировано':'Корректировки'}}
        </async-button>
      </template>
      <template #buttons>
        <async-button v-if="canUnlock" :disabled="!orders.filter(o=>o.locked).length" @click="freeOrder">
          Разблокировать все заказы
        </async-button>
      </template>
    </orders-view>
    <order-edit-view ref="editView"
      v-if="editableOrder && (mode==modes.edit || mode==modes.fixEdit)"
      v-bind="data"
      :phonePrefixes="phonePrefixes"
      :limitedMode="mode==modes.fixEdit"
      @cancel="onCancelEdit"
      @save="saveOrder">
    </order-edit-view>
    <create-corrections-panel
      v-if="editableOrder && mode==modes.correct"
      v-bind="data"
      @cancel="onCancelEdit"
      @saved="freeOrder(editableOrder)">
    </create-corrections-panel>
    <apply-corrections-panel
      v-if="editableOrder && mode==modes.applyCorrect"
      v-bind="data"
      @cancel="onCancelEdit"
      @saved="freeOrder(editableOrder)">
    </apply-corrections-panel>
  </div>
</template>

<script>
import OrdersView from "./view/orders-view.vue";
import OrderEditView from "./editor/panels/edit/order-edit-view.vue";
import Order from './order';
import OrdersMixin from './mixins/orders.js';
import CircleLoading from '../UI/mini/circle-loading.vue';
import CreateCorrectionsPanel from './editor/panels/corrections/create-corrections-panel/create-corrections-panel.vue';
import ApplyCorrectionsPanel from './editor/panels/corrections/apply-corrections-panel/apply-corrections-panel.vue';
import AsyncButton from "../UI/mini/async-button.vue";

export default {
  name:"orders",
  mixins:[OrdersMixin],
  components:{ OrdersView, OrderEditView, CircleLoading, CreateCorrectionsPanel, ApplyCorrectionsPanel, AsyncButton },
  props:{
    phone:String,
    d_from:String,
    d_to:String,
    status:[Number,String],
    shop_url:String,
    "error-rate":Number,
    "show-pickup":Boolean,
  },
  data(){
    return{
      editableOrder:null,
      discountMethodsTypes:{
        dontUse:-1,
        discountCard:0,
        promocode:1,
        bonus:2,
        certificate:3
      },
      editLoading:-1,
      searchParams:new URL(document.URL).searchParams,
      correctsOrders:[],
      canUnlock:UserPermissions.can("order_unlock"),
      showCorrects:UserPermissions.can("order_corrects"),
      showDelivery:UserPermissions.can("order_additional_delivery"),
      superEdit:UserPermissions.can("order_edit_super"),
    }
  },
  computed:{
    data(){
      return {
        shop_url:this.shop_url,
        waves:this.waves,
        availableWaves:this.availableWaves,
        payments:this.payments,
        statuses:this.statuses,
        order:this.editableOrder,
        sots:this.sots,
        zones:this.zones,
        errorRate:this.errorRate,
        discountMethodsTypes:this.discountMethodsTypes,
      }
    }
  },
  mounted(){
    this.getPolygons({ sotId:-1, disabled:0, zoneDisable:1 })//Получаем полигоны для карты
      .then(({zones,sots})=>{ this.zones = zones; this.sots = sots });
    window.addEventListener('getAlerts',({ detail:{ locks, corrects } })=>{//обновляем данные о заказах
      this.correctsOrders = Object.keys(corrects) || [];
      this.orders.forEach((order)=>{
        order.locked = locks.includes(order.id);
        order.correctsColor = corrects[order.id];
      });
    });
    window.addEventListener('beforeunload',()=>{//освобождаем заказ перед перезагрузкой
      if(this.editableOrder) this.freeOrder(this.editableOrder);
    })
  },
  methods:{
    getPolygons(params){
			return new Promise((resolve,reject)=>{
				axios
					.get("/Api/getPoligons",{ params })
					.then(({data})=>{
						let getPolygons = (type,polys)=>{
							return polys.map((poly)=>{ return {
								geometry:JSON.parse(poly.geometry),
								options:{
									description:poly.description || ((type=='sot'?"Сота ":"Зона ")+poly.id),
									fillColor:poly.fillColor|| "#1A78EE",
									fillOpacity:poly.fillOpacity || (type=='sot'?0:0.6),
									strokeColor:poly.strokeColor || "#1A78EE",
									strokeOpacity:(type=='sot'?"0":(poly.strokeOpacity || "0")),
									strokeWidth: 3
								},
								properties:{
									id:poly.id,
									type,
								},
                conditions:{
                  cost:poly.cost,
                  limit:poly.limit,
                  limit_lgot:poly.limit_lgot,
                  limit_min:poly.limit_min,
                  balloon:poly.balloon,
                  description:poly.description,
                }
							}});
						}
						resolve({	zones:getPolygons('zone',data.zones),	sots:data.sots?getPolygons('sot',data.sots):null	}, data);
					})
					.catch((e)=>{ console.error(e); reject(e) });
			})
		},
    setOrder(order){
      return new Promise((resolve,reject)=>{
        if(!order) reject(new Error("(setOrder) Не передан order"));
        axios
        .post('/Api/setOrder', {
          headers: {'X-Access-Token': Globals.api_token, "content-type": "application/json"},
          params: {
            order:{
              ...order,
              phone:order.phonePrefix+order.phone,
              phoneConsignee:order.phoneConsigneePrefix+order.phoneConsignee
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
      return axios.get("/orderUnlock",{ params:{ orderId:order.id || -1 } })//разблокировать зказ
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
</style>