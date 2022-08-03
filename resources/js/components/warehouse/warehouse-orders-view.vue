<template>
  <main class="warehouse-orders-view main">
    <div>
      <form action="#">
        <div style="float:left">
          <div>
            <input type="date" name="dFrom" v-model="dFrom">
            <input type="date" name="dTo" v-model="dTo">
            <input type="submit" value="применить"><br/>
            <label for="status"> Статус</label>
            <select :disabled="true" id="status" name="status" v-model="stat">
                <option value="0">Все</option>
                <option  v-for="(status) in statuses" :key="status.id" :value="status.id">{{status.title}}</option>
            </select>
          </div>
          <div>
            <label>Волна:</label>
            <select v-model="filterWave" @change="filterOrders()">
              <option v-for="(wave,index) in [{ wId:'-1', value:'Все' },...filterWaves]" :key="index" :value="wave.wId">{{wave.value}}</option>
            </select>
            <input type="button" value="Волны" @click="modalOpened.waves = true; $refs.report.update()">
          </div>
          <div>
            <label>Сортировка по времени доставки</label>
            <select v-model="sortByDeliveryDateTime">
              <option :value="false">Нет</option>
              <option :value="true">По возрастанию</option>
            </select>
          </div>
          <div>
            <label>Способ изменения кол-ва(веса) по умочанию</label>
            <select v-model="increment">
              <option :value="true">Увеличение</option>
              <option :value="false">Замена</option>
            </select>
          </div>
        </div>
      </form>
    </div>
    <table border="1" ref="tblorders" style="border: 1px solid;width: 100%;">
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
        <th>Примечание</th>
        <th>Период доставки</th>
        <th>Статус</th>
        <th>Товары и действия</th>
      </thead>
      <wh-order class="order-body"
        v-for="(order, index) in outputOrders"
        ref="orders"
        :key="index"
        :order="order"
        :waves="waves"
        :isBad="badOrders.includes(order.id)"
        :payments="payments"
        :statuses="statuses"
        :zone="zones.find(z=>z.properties.id==order.deliveryZone)"
        :gifts="gifts"
        :searchString="searchString"
        :shop_url="shop_url"
        :warehouse="true"
        @edit="$emit('edit',order)">
      </wh-order>
    </table>

    <!-- модальное окно добавления товара -->
    <modal 
      v-show="modalOpened.waves"
      :show="['cancel']"
      cancel='Выход'
      @close="modalOpened.waves = false"
      @confirm="modalOpened.waves = false">
      <template #header>
        <h2 class="modalHeaderItem">Волны</h2>
      </template>
      <template #body>
        <waves-report ref="report" :waves="waves" @select="onSelectWave"></waves-report>
      </template>
    </modal>
  </main>
</template>

<script>
import VueMask from 'v-mask';

Vue.use(VueMask);
import modal from "../UI/panels/modal.vue";
import CircleLoading from '../UI/mini/circle-loading.vue';
import WavesReport from '../orders/view/waves-report/waves-report.vue';
import whOrder from './wh-order.vue';
import ExclusiveInput from "../UI/inputs/exclusive-input.vue";

export default {
  components: {
    modal,
    CircleLoading,
    WavesReport,
    whOrder,
    ExclusiveInput
  },
  name: "warehouse-orders-view",
  props:{
    stat:[Number,String, Array],
    shop_url:String,
    dateRange:Array,
    orders:Array,
    waves:Array,
    availableWaves:Array,
    payments:Array,
    statuses:Array,
    filterWaves:{
      type:Array,
      default(){
        return []
      }
    },
  },
  data() {
    return {
      state:1,
      states:{
        error:-1,
        loading:0,
        loaded:1
      },
      dFrom:this.dateRange[0] || new Date().toJSON().split("T")[0],
      dTo:this.dateRange[1] || new Date().toJSON().split("T")[0],
      searchString:"",
      loading: true,
      newOrderError:"",
      badOrders: [],
      orderEditIndex:-1,
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
      increment:localStorage.increment?JSON.parse(localStorage.increment):false,
      sortByDeliveryDateTime:localStorage.sortByDeliveryDateTime?JSON.parse(localStorage.sortByDeliveryDateTime):false,
      filters:{//объект для фильтров таблицы
        orderId:"",
        number:""
      }
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
    outputOrders(){
      if(!this.sortByDeliveryDateTime) return this.orders;
      return this.orders.sort((f,s)=>{
        if(f.deliveryDate==s.deliveryDate)
          return f.deliveryTo>s.deliveryTo?1:-1;
        else return f.deliveryDate>s.deliveryDate?1:-1;
      });
    },
    editableOrder(){
      if(this.orderEditIndex<0)
        return null;
      return this.$refs.orders[this.orderEditIndex];
    }
  },
  watch:{
    increment:function(){
			localStorage.increment = this.increment;
      window.location.reload();
		},
    sortByDeliveryDateTime:function(){
      localStorage.sortByDeliveryDateTime = this.sortByDeliveryDateTime;
      window.location.reload();
    }
  },
  mounted(){
    this.updateBadOrders();
  },
  methods: {
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
    updateBadOrders(setListener=false){
      return axios
      .get("/Api/getAlerts",{ headers:{ 'X-Access-Token': Globals.api_token } })
      .then(({ data:{ orderstimeout } })=>{
        this.badOrders = orderstimeout.map(item => item.id);
      })
      .finally(()=>{
        if(setListener)
          window.addEventListener('getAlerts',({ detail:{ orderstimeout } })=>{
            this.badOrders = orderstimeout.map(item => item.id);
          });
      })
    },
    go_back() {
      window.location.href = "/";
    },
  },
}
</script>

<style lang="scss">
.warehouse-orders-view{
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