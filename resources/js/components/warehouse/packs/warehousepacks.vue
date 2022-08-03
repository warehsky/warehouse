<template>
  <div class="warehouse">
    <warehouse-orders-view v-if="!editableOrder"
      :orders="orders"
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
      @edit="openOrder"
      @filter="filterOrders">
    </warehouse-orders-view>
    <wp-edit-view v-else
      :order="editableOrder" 
      :shop_url="shop_url"
      :waves="waves"
      :increment="increment"
      @cancel="onCancelEdit"
      :packs="packs"
      :ismobile="ismobile">
    </wp-edit-view>
  </div>
</template>

<script>
import WarehouseOrdersView from '../warehouse-orders-view.vue'
import OrdersMixin from '../../orders/mixins/orders.js';
import WpEditView from './wp-edit-view.vue';
export default {
  name:"Warehousepacks",
  components:{
    WarehouseOrdersView,
    WpEditView
  },
  mixins:[OrdersMixin],
  props:{
    phone:String,
    d_from:String,
    d_to:String,
    status:[Number,String,Array],
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