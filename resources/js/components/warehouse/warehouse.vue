<template>
  <div class="warehouse">
    <warehouse-orders-view v-if="!editableOrder"
      :orders="availableOrders"
      :dateRange="[d_from,d_to]"
      :stat="status"
      :shop_url="shop_url"
      :waves="waves"
      :availableWaves="availableWaves"
      :filterWaves="filterWaves"
      :payments="payments"
      :statuses="statuses"
      :warehouse="true"
      :errorRate="errorRate"
      @edit="openOrder($event,modes.edit)"
      @filter="filterOrders">
    </warehouse-orders-view>
    <warehouse-edit-view v-else
      :order="editableOrder" 
      :shop_url="shop_url"
      :waves="waves"
      :increment="increment"
      @cancel="onCancelEdit"
      :packs="packs"
      :ismobile="ismobile">
    </warehouse-edit-view>
  </div>
</template>

<script>
import WarehouseOrdersView from './warehouse-orders-view.vue'
import OrdersMixin from '../orders/mixins/orders.js';
import WarehouseEditView from './warehouse-edit-view.vue';
import Vue from 'vue';
export default {
  name:"Warehouse",
  components:{
    WarehouseOrdersView,
    WarehouseEditView
  },
  mixins:[OrdersMixin],
  props:{
    phone:String,
    d_from:String,
    d_to:String,
    status:[Number,String],
    shop_url:String,
    "error-rate":{
      type:Number,
      default(){
        return 100
      }
    },
    packs:Array,
    ismobile:[Boolean,Number]
  },
  computed:{
    availableOrders(){
      return this.orders.filter(order=>order.status==1 || order.status==7)
    }
  },
  data(){
    return{
      editableOrder:null,
      increment:localStorage.increment?JSON.parse(localStorage.increment):false
    }
  },
  watch:{
    increment(){
      localStorage.increment = this.increment;
    }
  },	
  methods:{
    onCancelEdit(order,draft){
      let index = this.orders.indexOf(order);
      Vue.set(this.orders,index,new Order().set(draft));
      this.editableOrder = null;
    }
  }
}
</script>

<style lang="scss">
.warehouse{
  .checkout_text{
    padding: 2px;
    width: fit-content;
  }
}
</style>