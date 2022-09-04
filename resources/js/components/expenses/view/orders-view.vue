<template>
  <main class="orders-view main">
    <div>
      <div style="width:min-content;">
        <span class="separated-row" style="white-space: nowrap;">
          <span>Кол-во: <b>{{(this.getOrderCount()).toFixed(0)}}</b></span>
          <span>Сумма: <b>{{this.getItogo().toFixed(2)}}</b></span>
          <span>Доставка: <b>{{this.getDeliveryItogo().toFixed(2)}}</b></span>
          <span>Итого: <b>{{(this.getItogo()+this.getDeliveryItogo()).toFixed(2)}}</b></span>
          <span>Ср.чек: <b>{{(((this.getItogo()+this.getDeliveryItogo()).toFixed(2))/this.getOrderCount()).toFixed(0)}}</b></span>
        </span>
        <div style="display:flex; width: max-content; padding-bottom: 5px;">
          <button @click="newOrder()" style="width: max-content;">Новый заказ</button>
          &nbsp;
          <button @click="newOrder(JSON.parse(orderDraft))" style="width: max-content;" :disabled="!orderDraft">Новый заказ из черновика</button>
          &nbsp;
          <slot name="buttons"></slot>
          <span style="white-space:nowrap; color:gray">
            &nbsp;<span style="color:red">*</span>Черновик - копия последнего редактируемого заказа.
          </span>&nbsp;
          <span v-if="newOrderError" style="color:red; white-space: nowrap;">{{newOrderError}}</span>
        </div>
      </div>
      <form action="#" style="display: flow-root;">
        <div style="float:left">
          <div style="padding-bottom: 5px">
            <input type="date" name="dFrom" v-model="dFrom">
            <input type="date" name="dTo" v-model="dTo">
            <label for="status"> Статус</label>
            <select id="status" name="status" v-model="stat">
                <option value="0">Все</option>
                <option  v-for="(status) in statuses" :key="status.id" :value="status.id">{{status.title}}</option>
            </select>
            <input type="submit" value="применить">
          </div>
          <div style="padding-bottom: 5px">
            <label>Волна:</label>
            <select v-model="filterWave" @change="filterOrders()">
              <option v-for="(wave,index) in [{ wId:'-1', value:'Все' },...filterWaves]" :key="index" :value="wave.wId">{{wave.value}}</option>
            </select>
            <input type="button" value="Волны" @click="modalOpened.waves = true; $refs.report.update()">
          </div>
        </div>
        <div style="float:right">
          <label>Поиск по товарам</label>
          <input type="text" v-model="searchString" @input="filterOrders()"/>
          <input type="button" value="Сброс" @click="searchString = '';filterOrders()"/>
        </div>
      </form>
    </div>
    <div class="orders-loader" v-if="state!=1">
      <circle-loading v-if="state==0" :radius="30" :ringWeight="15"></circle-loading>
      <error-icon style="transform: scale(1.5);" v-else-if="state==-1"></error-icon>
    </div>
    <table v-else border="1" ref="tblorders">
      <thead>
        <th>ID
          <exclusive-input
            id="filter"
            :size="(String(orders.length?orders[0].id:'').length||5)-2"
            type="text"
            v-mask="'#'.multiply(String(orders.length?orders[0].id:'').length || 0)"
            v-model="filters.orderId"
            @change="$emit('filter',filters);"/>
        </th>
        <th>№ 1С
          <exclusive-input id="filter" v-mask="'X'.multiply(12)" :size="12" type="text" v-model="filters.number" @change="$emit('filter',filters);" />
        </th>
        <th>Дата</th>
        <th>Имя</th>
        <th>Телефон и история</th>
        <th>Адрес</th>
        <th>Примечание</th>
        <th>Период доставки</th>
        <th>Сумма, ₽</th>
        <th>Персональная скидка</th>
        <th>Акция</th>
        <th>Доставка, ₽</th>
        <th>Всего, ₽</th>
        <th>Способ оплаты</th>
        <th>Льгота</th>
        <th>Статус
          <div v-show="widthoutPayCount">
            <a href="/admin/orders?nopay=1" style="color:red;">Нет оплаты ({{widthoutPayCount}})</a>
          </div>
        </th>
        <th>Действия
          <div v-show="showCorrects && correctsCount"  @click="filters.corrects = 1; $emit('filter',filters); filters.corrects = 0">
            <a style="color:red;">Корректировки ({{correctsCount}})</a>
          </div>
        </th>
      </thead>
      <v-order class="order-body"
        v-for="(order, index) in orders"
        ref="orders"
        :key="index"
        :order="order"
        :waves="waves"
        :isBad="badOrders.includes(order.id)"
        :payments="payments"
        :statuses="statuses"
        :zone="zones.find(z=>z.properties.id==order.deliveryZone) || null"
        :gifts="gifts"
        :searchString="searchString"
        :shop_url="shop_url"
        :badOrders="badOrders"
        :showDelivery="showDelivery"
        @edit="$emit('edit',order)"
        @clone="cloneOrder(order)"
        @input-number="updateBonus()"
        @save-draft="orderDraft = $event"
        @refuse="$emit('refuse',$event)"
        @requestItems="$listeners['requestItems']">
        <template #actions>
          <slot name="actions" :order="order"></slot>
        </template>
      </v-order>
    </table>
    <modal class="waves-report-modal"
      v-show="modalOpened.waves"
      :show="['cancel']"
      cancel='Выход'
      @close="modalOpened.waves = false"
      @confirm="modalOpened.waves = false">
      <template #header>
        <h2 class="modalHeaderItem">Волны</h2>
      </template>
      <template #body>
        <waves-report ref="report"
          :waves="waves"
          :defaultFrom="dFrom"
          :defaultTo="dTo"
          @select="onSelectWave">
        </waves-report>
      </template>
    </modal>
  </main>
</template>

<script>
import VueMask from 'v-mask';

Vue.use(VueMask);
import modal from "../../UI/panels/modal.vue";
import CircleLoading from '../../UI/mini/circle-loading.vue';
import WavesReport from './waves-report/waves-report.vue';
import Order from '../order.js';
import OrderItem from '../orderItem';
import vOrder from './v-order.vue';
import ErrorIcon from '../../UI/mini/error-icon.vue';
import ExclusiveInput from "../../UI/inputs/exclusive-input.vue";

export default {
  components: {
    modal,
    CircleLoading,
    WavesReport,
    vOrder,
    ErrorIcon,
    ExclusiveInput
  },
  name: "orders-view",
  props:{
    // data:Object,
    phone:String,
    stat:[Number,String],
    shop_url:String,
    dateRange:Array,
    orders:Array,
    waves:Array,
    availableWaves:Array,
    payments:Array,
    statuses:Array,
    showCorrects:Boolean,
    showDelivery:Boolean,
    correctsCount:{ type:Number, required:true },
    filterWaves:{
      type:Array,
      default(){
        return []
      }
    },
    state:{
      type:Number,
      default(){
        return 0;
      }
    }
  },
  data() {
    let urlArguments = new URLSearchParams(location.search);
    let filterNames = ['orderId', 'number', 'corrects'];//фильтры заказов
    let filters = Object.fromEntries(filterNames.map(n=>[n,urlArguments.get(n)]));//значения по умолчанию берем из url строки
    return {
      dFrom:this.dateRange[0] || new Date().toJSON().split("T")[0],
      dTo:this.dateRange[1] || new Date().toJSON().split("T")[0],
      searchString:"",
      loading: true,
      newOrderError:"",
      badOrders: [],
      orderEditIndex:-1,
      orderDraft:localStorage.orderDraft,
      isModalVisible: false,
      modalOpened:{
        waves:false,
      },
      zones:[],
      sots:[],
      today:new Date(),
      tommorow:new Date(Date.now()+Date.day),
      filterWave:-1,
      showAllWaves:false,
      gifts:[],
      admintest:new URLSearchParams(window.location.search)?.get("name")=="admintest",
      widthoutPayCount:0,//Колличество неоплаченных заказов
      filters//фильтры заказов
    };
  },
  filters: {
    currencydecimal(value) {
      if (!value) return "";
      if (typeof value == "string") value = parseFloat(value);
      return value.toFixed(2);
    },
  },
  computed:{
    editableOrder(){
      if(this.orderEditIndex<0)
        return null;
      return this.$refs.orders[this.orderEditIndex];
    }
  },
  mounted(){
    this.updateOrdersInfo(true);
  },
  methods: {
    getItogo(){
      let itogo = 0;
      for(let i=0; i<this.orders.length; i++)
        if(this.orders[i].status!=3)
          itogo += this.orders[i].sum_total;
      return itogo;
    },
    getDeliveryItogo(){
      let itogo = 0;
      for(let i=0; i<this.orders.length; i++)
        if(this.orders[i].status!=3)
          itogo += this.orders[i].deliveryCost;
      return itogo;
    },
    getOrderCount(){
      let itogo = 0;
      for(let i=0; i<this.orders.length; i++)
        if(this.orders[i].status!=3)
          itogo += 1;
      return itogo;
    },
    onSelectWave(e){
      let selected = this.filterWaves.find((wave)=>{
        let ids = wave.wId.split(',');
        return ids.except(e.id.split(',')).length<ids.length
      });
      if(selected){
        this.filterWave=selected.wId;
        this.modalOpened.waves = false; 
        this.filterOrders()
      }
    },
    filterOrders(){
      if(!this.tempOrders)
        this.tempOrders = [...this.orders];
      if(!this.searchString && this.filterWave==-1){
        this.orders = this.tempOrders;
        return;
      }
      let orders = this.tempOrders;
      if(this.filterWave!=-1){
        let waves = this.filterWave.split(",");
        orders = this.tempOrders.filter((order)=>{ return waves.includes(String(order.waveId)) || (this.editableOrder && order==this.editableOrder.order) });
      }
      orders = this.searchByItems(orders,this.searchString);
      if(this.editableOrder)
        this.orderEditIndex = orders.indexOf(this.editableOrder.order);
      this.orders = orders;
    },
    searchByItems(orders,str){
      if(!str) return orders;
      return orders.filter((order)=>{
        if(this.editableOrder && order==this.editableOrder.order)
          return true;
        if(this.editableOrder?order.phone == this.editableOrder.order.phone:true){
          for(let i = 0; i<order.items.length; i++){
            //console.log("order:",{...order},"order.items[i]:",order.items[i],"str:"+(str||undefined)+";","res:"+order.items[i].title.search(str||undefined)+";","return",order.items[i].title.search(str)>=0);
            return str?order.items[i].title.toLowerCase().search(str.toLowerCase())>=0:true;
          }
        }
      })
    },
    updateOrdersInfo(setListener=false){
      let handler = ({orderstimeout,nopays})=>{
        this.badOrders = orderstimeout.map(item => item.id);
        this.widthoutPayCount = nopays;
      }
      return axios
      .get("/Api/getAlerts",{ headers:{ 'X-Access-Token': Globals.api_token } })
      .then(({ data })=>handler(data))
      .finally(()=>{
        if(setListener) window.addEventListener('getAlerts',({ detail })=>handler(detail));
      })
    },
    go_back() {
      window.location.href = "/";
    },
    newOrder(data={}){
      this.newOrderError="";
      if(this.orderEditIndex>=0/* || !this.orders_ctrl*/)
        this.newOrderError+="Невозможно создать новый заказ во время редактирования. |";
      if(this.orders.length>0 && this.orders[0].id == 0)
        this.newOrderError+="Невозможно создать более одного нового заказа за раз. Сначала сохраните текущий."
      let waves = this.availableWaves.length>0?this.availableWaves:this.waves;
      if(!waves.length) this.newOrderError+="Ошибка. Попробуйте ещё раз через несколько секунд."
      if(this.newOrderError){
        setTimeout(()=>{ this.newOrderError = null },5000);
        return;
      }
      let unprocessedOrders = Indicator?.getIndicator("#i-orders")?.getValue() || 0;
      if(unprocessedOrders>=10)
        alert(`Обратите внимание!\nКоличество необработанных зказов - ${unprocessedOrders}.`)
      let coords = Order.defaultAddress.coords;
      let order = new Order({
        deliveryDate:(this.availableWaves.length>0?this.today:this.tommorow).toShortDateString(),
        waveId:waves[0].id,
        lat: coords[0],
        lng: coords[1],
        ...data,
        itemsVisible:true,
      });
      this.$emit('order-created',order);
    },
    cloneOrder(order){
      if(!this.editableOrder){
        let clone = new Order(_.cloneDeep(order));
        this.orders.unshift(clone);
        this.$nextTick(()=>{ this.$emit('order-cloned',this.orders[0]) });
        this.$nextTick(()=>{ clone.waveId = this.availableWaves[0].id });
      }
    },
  },
}
</script>

<style lang="scss">
@import '../../../../sass/separated-row.scss';
.separated-row{
  display: flex;
  margin-bottom: 5px;
  cursor: default;
  border-radius: 4px;
  border: 1px solid transparent;
  &>*{
    padding: 4px;
    border-color:black;
  }
}
.orders-view{
  &>table{
    border: 1px solid;
    width: 100%;
    border-collapse: separate !important;
    border-spacing: 0px;
    th{
      position: sticky;
      top: 0;
      background: white;
      z-index: 1;
    }
  }
  .orders-loader{
    background: #c4c4c4;
    width: auto;
    display: flex;
    justify-content: center;
    padding: 10px 0px;
    border: 1px solid #adadad;
    border-radius: 6px;
  }
  .waves-report-modal{
    .modal{
      width: 85%;
    }
  }
  .modalHeaderItem{
    margin-left: 50px;
  }
  .modalHeader{
    margin-top: 25px;
    margin-left:50px;
    margin-bottom: 50px;
  }
}
.order-body{
  &.editable{
    box-shadow: inset 0px 0px 0px 3px #ff7a00;
  }
  &.loading{
    filter: opacity(0.5) grayscale(1) contrast(0.5) saturate(0.5);
    pointer-events: none;
    user-select: none;
  }
}
.badOrder{
  box-shadow: inset 0px 0px 0px 3px red;
  border: 1px solid red;
  background: #ffe3e3;
  .restore-button{
    display: flex;
    justify-content: center;
  }
}
.btn{
  // background: gray;
  border: 1px solid gray !important;
  &.disabled{
    pointer-events:none;
    cursor:default;
  }
}
.btn:hover{
  color: white;
  background: #afafaf;
  border: 1px solid transparent !important;
  cursor:pointer;
}
.btn-active{
  font-weight: 900;
  color: darkorange;
}
.date_text{
  font-size: 10px!important;
}
header .navbar {
  position: relative;
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -ms-flex-wrap: wrap;
  flex-wrap: wrap;
  -webkit-box-align: center;
  -ms-flex-align: center;
  align-items: center;
  -webkit-box-pack: justify;
  -ms-flex-pack: justify;
  justify-content: space-between;
  padding: .5rem 1rem;
}
.main{ 
  table{
    border-collapse: collapse;
  }
  *{ 
    font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol";
    line-height: 1.5;
  }
  button, input, optgroup, select, textarea{
    font-size: inherit;
  }
}
*, ::after, ::before {
    box-sizing: border-box;
}
//--------- map

// .select-address{
// 	width:auto;
// 	height:70vh;
// }
// #map,
// .ymap-container {
//   width: 100%;
//   height: 90%;
// }
.suggestItem {
  font-size: 18px;
  width: 100%;
  margin: 0;
}
.s-dropdown{
	width: auto;
}
/*кастомная кнопка на карте*/
.mapButton{
  background-color: white;
  border-radius: 3px;
  box-shadow: 0px 0px 3px black;
  height: 26px;
  padding: 0 3px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  cursor: pointer;
}
.checkout_text{
	width: 100%;
	height: 36px;
	background: #FFFFFF;
	border-radius: 4px;
	border: 1px solid #c4c4c4;
	padding: 8px 10px 9px 10px;
	outline: none;
	color: #111;
	font-size: 18px;
  &.disabled{
    background: white;
    border: none;
    font-size: inherit !important;
    padding: 0;
    height: auto;
  }
}
// -------- map end
select.invalid-selection{
  box-shadow: inset 0px 0px 0px 1px red;
}
.groupbox-actions{
  border: 1px solid;
  padding: 3px;
  border-radius: 3px;
  margin: 5px 0;
}
.clone-image{
  border-radius: 4px;
  padding: 2px;
  width: 25px;
  &:hover{
    background: #ffdddd;
  }
}
.blink {
  -webkit-animation: blink 5s linear infinite;
  animation: blink 5s linear infinite;
  color: #ff0030;
}
@-webkit-keyframes blink {
  100% { color: rgba(34, 34, 34, 0); }
}
@keyframes blink {
  100% { color: rgba(34, 34, 34, 0); }
}
</style>