<template>
  <tbody :class="{ loading:order.loading }">
    <tr :class="(order.status==1?'needHandle':'') + (isBad?' badOrder':'')">
      <td>
        <div style="width: min-content;">
          {{order.id}}
        </div>
      </td>
      <!-- № 1С -->
      <td>{{order.number}}</td>
      <!-- Дата -->
      <td>{{order.date_time_created}}</td>
      <!-- Имя -->
      <td>{{order.name}}</td>
      <td>
        <!-- Телефон -->
        <u>Заказчик</u>
        <div style="display:flex;"><span>{{order.phonePrefix+order.phone}}</span></div>
        <!-- Телефон получателя -->
        <u>Получатель:</u>
        <div style="display:flex;">{{order.phoneConsigneePrefix+order.phoneConsignee}}</div>
      </td>
      <!-- Примечание -->
      <td>{{order.note}}</td>
      <!-- Период доставки -->
      <td>
          <span>{{new Date(order.deliveryDate).toLocaleDateString()}}</span>
          <span style="white-space:nowrap;">{{ (waves.find(w=>w.id == order.waveId)||{ value:`Неизвестная волна (id:${order.waveId})` }).value }}</span>
      </td>
      <!-- статус -->
      <td class="order-status">
        {{status}}
        <div class="scaned-status">{{scanedStatus}}</div>
      </td>
      <!-- действия -->
      <td>
        <span class="btn" @click="openOrder()">изменить</span>
      </td>
    </tr>
  </tbody>
</template>

<script>
import FloatPanel from '../UI/panels/float-panel.vue';
import MarkText from '../UI/mini/mark-text.vue';
import AssociativeSelect from '../UI/inputs/associative-select.vue';
import ComputedScanStatus from '../orders/mixins/computed-scan-status.js';

export default {
  name:"wh-order",
  mixins:[ComputedScanStatus],
  components: {
    FloatPanel,
    MarkText,
    AssociativeSelect
  },
  props:{
    order:{
      type:Object,
      require:true
    },
    zone:{
      type:Object,
      require:true
    },
    waves:{
      type:Array,
      default(){
        return []
      }
    },
    payments:{
      type:Array,
      default(){
        return []
      }
    },
    statuses:{
      type:Array,
      default(){
        return []
      }
    },
    isBad:{
      type:Boolean,
      default:false
    },
    gifts:{
      type:Array,
      default(){
        return []
      }
    },
    searchString:{
      type:String,
      default(){
        return ""
      }
    },
    shop_url:{
      type:String
    }
  },
  data(){
    return {
      state:1,
      states:{
        error:-1,
        loading:0,
        loaded:1
      },
      outputError:"",
      notifiOpened:false,
      lgots:[{id:0, title:'нет'}, {id:1, title:'пенсионер'}],
      today:new Date(),
      tommorow:new Date(Date.now()+Date.day),
      showAllWaves:false,
      isInvalidAddress:false,
      showWarehouse:false
    }
  },
  computed:{
    status(){
      if(typeof this.order.status == "string")
        this.order.status = parseInt(this.order.status);
      let status="";
      for(let i=0; i<this.statuses.length; i++)
        if(this.statuses[i].id==this.order.status){
          status = this.statuses[i].title;
          break;
        }
      return status;
    },
    pension(){
      if(typeof this.order.pension == "string")
        this.order.pension = parseInt(this.order.pension);
      let pension="";
      for(let i=0; i<this.lgots.length; i++)
        if(this.lgots[i].id==this.order.pension){
          pension = this.lgots[i].title;
          break;
        }
      return pension;
    },   
    personalDiscount(){
      return (this.order.sum_total*this.order?.discount?.discount/100) || this.order.bonus_pay || 0;
    }
  },
  methods:{
    update(){
      this.order.update();
    },
    openOrder(){
      this.state = this.states.loaded;
      this.outputError = "";
      this.$emit("edit",true);
    }
  }
}
</script>

<style lang="scss">
.needHandle .order-status{
  border: 3px solid green;
  background: #daffda;
}
.order-status .scaned-status{
	color: blue;
	border: 2px solid;
	border-radius: 4px;
}
.notifiIcon{
  width:30px;
  border-radius: 4px;
  padding: 2px;
  &.is-temp-notes{
    background:#ffa05cc7;
  }
}
</style>