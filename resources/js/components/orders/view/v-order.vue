<template>
  <tbody :class="['v-order',{ loading:order.loading }]">
    <!-- заказ -->
    <tr :class="(order.status==1?'needHandle':'') + (isBad?' badOrder':'')">
      <td>
        <img v-if="!warehouse && (order.notePermanent || order.tasksUser.length>0)" src="/img/icons/nitification.svg" :class="['notifiIcon',{ 'is-temp-notes':order.tasksUser.length>0 }]" @click="notifiOpened = !notifiOpened"/>
        <float-panel v-if="!warehouse && (order.notePermanent || order.tasksUser.length>0)" class="user-note" :opened="notifiOpened">
          <span class="close-button" @click="notifiOpened=false">X</span>
          <div>
            <div v-if="order.notePermanent">
              <h4>Основная заметка</h4>
              <div class="note-text">
              {{order.notePermanent}}
              </div>
            </div>
            <div v-if="order.tasksUser.length>0">
              <h4>Временные заметки</h4>
              <div class="note-text">
                <ul>
                  <li class="note-item" v-for="task in order.tasksUser" :key="task.id">
                    <div style="display:flex; justify-content: space-between;">
                      <span :style="task.status?'':'text-decoration:line-through; color:gray;'">{{task.note}}</span>
                      <button style="height:fit-content" @click="setNoteStatus(task.id,task.status?0:1).then(()=>{ task.status = task.status?0:1 })">{{task.status?"Звершить":"Отмена"}}</button>
                    </div>
                    <div>
                      <div style="white-space: nowrap;"><b>создано:</b> {{ new Date(task.created_at).toLocaleString() }}</div>
                      <div style="white-space: nowrap;"><b>изменено:</b> {{ new Date(task.updated_at).toLocaleString() }}</div>
                    </div>
                  </li>
                </ul>
              </div>
              <span style="color:red">*Закрытые заметки пропадут после перезагрузки страници</span>
            </div>
          </div>
        </float-panel>
        <div style="width: min-content;">
          {{order.id}}
          <img v-if="!warehouse" src="/img/icons/clone.svg" class="clone-image" @click="$emit('clone')" />
        </div>
      </td>
      <!-- № 1С -->
      <td>{{order.number}}</td>
      <!-- № 1С -->
      <!-- Дата -->
      <td>{{order.date_time_created}}</td>
      <!-- Дата -->
      <!-- Имя -->
      <td>
        {{order.name}}
      </td>
      <!-- Имя -->
      <td>
        <!-- Телефон -->
        <u>Заказчик(<a v-if="!warehouse" :href="order.ordersCount?'/admin/orders?phone='+order.phonePrefix+order.phone:''" target="_blank">{{order.ordersCount||"??"}}</a>):</u>
        <div style="display:flex;">
          <span><a :href="`/admin/WebUsers?id=${order.phonePrefix+order.phone}`">{{order.phonePrefix+order.phone}}</a></span>
        </div>
        <!-- Телефон -->
        <!-- Телефон получателя -->
        <u>Получатель:</u>
        <div style="display:flex;">
          {{order.phoneConsigneePrefix+order.phoneConsignee}}
        </div>
        <!-- Телефон получателя -->
      </td>
      <!-- Адрес -->
      <td>
        <span>{{order.addr}}</span>
        <span v-if="isInvalidAddress" style="color:red;">[Адрес вне доступной зоны или соты]</span>
      </td>
      <!-- Адрес -->
      <!-- Примечание -->
      <td>{{ `${order.note||""} ${order.noteSuffix}` }}</td>
      <!-- Примечание -->
      <!-- Период доставки -->
      <td>
        <span>{{new Date(order.deliveryDate).toLocaleDateString()}}</span>
        <span style="white-space:nowrap;">{{ wave }}</span>
      </td>
      <!-- Период доставки -->
      <!-- Сумма, ₽ -->
      <td v-if="!warehouse">{{ Math.round(sum_total) | currencydecimal(UAH) }}</td>
      <!-- Сумма, ₽ -->
      <!-- Бонусы -->
      <td v-if="!warehouse">
        <template v-if="order.discount">
          <template v-if="order.discount.webUserId">
            <template v-if="order.discount.type != 3">
              Дисконтная карта
              <div style="border:1px solid; border-radius:4px;">
                -{{order.discount.discount}}% до {{new Date(order.discount.expiration).toLocaleDateString()}}
              </div>
            </template>
            <template v-if="order.discount.type == 3">
              Сертификат
              <div style="border:1px solid; border-radius:4px;">
                -{{ $getCurrencyPrice(order.discount.discount, this.order.course) | currencydecimal(UAH) }} до {{new Date(order.discount.expiration).toLocaleDateString()}}
              </div>
            </template>
          </template>
          <template v-else>
            Промокод -{{order.discount.discount}}%
          </template>
        </template>
        <template v-else-if="order.proc && order.bonusUser && order.bonus_pay">
          <u>Всего:</u> {{ order.bonusUser }} <br>
          <u>Начислено:</u> {{ order.bonus }}<br>
          <u>Использовано:</u>
          <span>{{ order.bonus_pay }}</span>
        </template>
        <template v-else>Нет</template>
      </td>
      <!-- Бонусы -->
      <!-- Акции -->
      <td v-if="!warehouse">
        <span>{{order.giftTitle||"Нет"}}</span>
      </td>
      <!-- Акции -->
      <!-- Доставка, ₽ -->
      <td v-if="!warehouse">{{$getCurrencyPrice(order.deliveryCost, order.course) | currencydecimal(UAH) }}</td>
      <!-- Доставка, ₽ -->
      <!-- Всего, ₽ -->
      <td v-if="!warehouse">{{ toPayOutput | currencydecimal(UAH) }}</td>
      <!-- Всего, ₽ -->
      <!-- способ оплаты -->
      <td v-if="!warehouse">
        <a href="#" v-if="order.payment==2 && order.status==6" @click="checkOrderPay(order.id)">информация</a> <!-- кнопка проверки оплаты -->

        {{payments.getItemBy((payment)=>{ return payment.id == order.payment }).title}}
      </td>
      <!-- способ оплаты -->
      <!-- льгота -->
      <td v-if="!warehouse">
        {{pension}}
      </td>
      <!-- льгота -->
      <!-- статус -->
      <td class="order-status">
        {{status}}
        <div v-if="order.status==1 || order.status == 7" class="scaned-status">{{scanedStatus}}</div>
        <div v-if="order.pickupStatus && order.titlePickupStatus" style="border: 2px solid; margin-top: 3px;font-size: 12px;">Склад: <span :style="'background:'+wstatuscolor[order.pickupStatus-1]">{{order.titlePickupStatus}}</span></div>
      </td>
      <!-- статус -->
      <td>
        <group-box v-show="order.actions" class="groupbox-actions" @empty="order.actions = !$event.empty">
          <legend style="font-size:13px; background:white;">Действия</legend>
          <slot name="actions"></slot>
          <div v-if="!warehouse &&  order.status == 6" class="restore-button" @click="restoreOrder(order)">
            <circle-loading v-if="order.refresh && order.refresh == 1" :radius="36" :ringWeight="16" style="padding: 5px 0px;"></circle-loading>
            <span v-show="!order.refresh">Восстановить заказ</span>
            <span v-show="order.refresh && order.refresh == -1">Ошибка!<br>Повторить восстановление</span>
          </div>
          <div v-if="!warehouse && [2,6,7].includes(order.status)" class="btn refuse-button" @click="$emit('refuse',order)">Отменить заказ</div>
        </group-box>
        <async-button v-if="!warehouse" :class="order.itemsVisible?'btn btn-active':'btn'" @click="toggleItems">товары</async-button>
      </td>
    </tr>
    <!-- заказ -->
    <!-- товары -->
    <tr>
      <td v-if="order.itemsVisible" colspan="17">
        <div class="inner-goods">
          <table border="1" ref="tblorders">
            <th>№</th>
            <th>ID</th>
            <th>Наименование</th>
            <th>Цена</th>
            <th>Базовое кол-во</th>
            <th v-if="showWarehouse">Склад кол-во</th>
            <th @click="showWarehouse=!showWarehouse">Кол-во</th>
            <th>Акция</th>
            <th>Дисконт кол-во</th>
            <th>Дисконт цена</th>
            <th>Остаток</th>
            <th>Сумма</th>
            <th><!--Товар по предоплате--></th>
            <th v-if="showDelivery && order.status == 4">Довоз</th>
            <tbody>
              <ov-good v-for="(item,index) in order.items" :key="item.id"
                :item="item"
                :index="index"
                :course="order.course"
                :searchString="searchString"
                :showWarehouse="showWarehouse"
                :shop_url="shop_url"
                :showDelivery="showDelivery && [4,7].includes(order.status)">
                <template #delivery>
                  <async-button v-if="!item.addStatus" @click="_additionalDelivery(item)">Добавить(создать)</async-button>
                  <span v-else-if="item.addStatus == 2" style="background:yellowgreen">Подтвержден</span>
                  <s v-else-if="item.addStatus == 3">Закрыт</s>
                  <span v-else style="background:yellow">Создан</span>
                </template>
              </ov-good>
            </tbody>
          </table>
        </div>
      </td>
    </tr>
    <!-- товары -->
  </tbody>
</template>

<script>
import FloatPanel from '../../UI/panels/float-panel.vue';
import AssociativeSelect from '../../UI/inputs/associative-select.vue';
import ComputedScanStatus from '../mixins/computed-scan-status.js';
import CircleLoading from '../../UI/mini/circle-loading.vue';
import OvGood from './ov-good.vue';
import AsyncButton from '../../UI/mini/async-button.vue';

export default {
  name:"v-order",
  mixins:[ComputedScanStatus],
  components: {
    FloatPanel,
    AssociativeSelect,
    CircleLoading,
    OvGood,
    AsyncButton
  },
  props:{
    order:{
      type:Object,
      required:true
    },
    zone:{
      type:Object,
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
      type:String,
      required:true
    },
    warehouse:{
      type:Boolean,
      default:false
    },
    badOrders:{
      type:Array,
      default(){
        return []
      }
    },
    showDelivery:Boolean
  },
  data(){
    return {
      outputError:"",
      notifiOpened:false,
      lgots:[{id:0, title:'нет'}, {id:1, title:'пенсионер'}],
      today:new Date(),
      tommorow:new Date(Date.now()+Date.day),
      showAllWaves:false,
      isInvalidAddress:false,
      showWarehouse:false,
      wstatuscolor:['#ff5c64c7','rgb(236, 209, 55)','#f4f74bc7','#0b9159c7'] 
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
      const isSertificate = this.order?.discount?.type==3;
      const discountSum = (this.sum_total*this.order?.discount?.discount/100);
      const certificateSum = this.$getCurrencyPrice(this.order?.discount?.discount, this.order.course);
      let discount = !isSertificate?discountSum:certificateSum;
      let bonus = this.$getCurrencyPrice(this.order.bonus_pay, this.order.course)
      return discount || bonus || 0;
    },
    UAH(){
      return Boolean(this.order.course);
    },
    sum_total(){
      return this.UAH?this.order.sum_total_grn:this.order.sum_total;
    },
    wave(){
      let wave = this.waves?.find(w=>w.id == this.order.waveId);
      return (wave?wave.value:`Неизвестная волна (id:${this.order.waveId})`)||"";
    },
    toPayOutput(){
      if(!this.sum_total || this.sum_total == 0) return 0;
      let sum = this.sum_total+this.$getCurrencyPrice(this.order.deliveryCost, this.order.course);
      return Math.round(sum-Math.min(sum, this.personalDiscount));
    }
  },
  filters:{
    currencydecimal(value,UAH){ return `${ value.toFixed(2) } ${ (UAH?"₴":"₽") }`; }
  },
  methods:{
    setNoteStatus(id,status){
      return new Promise((resolve,reject)=>{
        axios
          .get("/Api/changeNoteStatus",{ params:{ id, status }, headers:{ "X-Access-Token":Globals.api_token } })
          .then((response)=>{
            if(response.data.code == 200)
              resolve(response)
          })
          .catch((e)=>{ console.error(e); reject(e);  });
      })
    },
    getDeliveryCost(){
			let deliveryCost = 0;
      let limit = 0;
      if(this.zone){
        if(this.order.pension==1)
          limit = this.zone.conditions.limit_lgot;
        else
          limit = this.zone.conditions.limit
        if (this.order.sum_total < limit)
          deliveryCost = this.zone.conditions.cost;
      }
      else deliveryCost = 0;      
			return deliveryCost
    },
    update(){
      this.order.update();
      this.order.deliveryCost = this.getDeliveryCost();
    },
    restoreOrder(){
      Vue.set(this.order,'refresh',1);
      axios
        .get("/Api/refreshOrder", {
          headers:{ 'X-Access-Token':Globals.api_token },
          params:{ orderId:this.order.id } })
        .then(({data})=>{
          if(data.code == 200)
            window.addEventListener('getAlerts',({ detail:{ orderstimeout } })=>{
              if(!this.badOrders.includes(this.order.id))
                this.order.refresh = 0;
              else
                this.order.refresh = -1;
            }, { once:true });
        })
        .catch((e)=>{
          console.error(e);
          this.order.refresh = -1;
          this.alertRequestError(e);
        });
    },
    saveOrderDraft(onlyStorage=false){
      let draft = {...this.order};
      localStorage.orderDraft = JSON.stringify(draft);
      if(!onlyStorage) 
        this.orderDraft = draft;
      
      this.$emit("save-draft",localStorage.orderDraft);
    },
    checkOrderPay(id){
      axios
        .get("/Api/checkOrderPay", {
          headers:{ 'X-Access-Token':Globals.api_token },
          params:{ orderId:id } })
        .then(({data})=>{
          console.log(data, id);
            alert(data.msg+(data.pay?"\n сумма: "+data.pay:''));
              
        })
        .catch((e)=>{
          console.error(e);
          this.order.refresh = -1;
          this.alertRequestError(e);
        });
    },
    alertRequestError(error){
      alert("Ошибка "+(error.request?.status)+". Перезагрузите страницу.");
    },
    async toggleItems(){
      let toggle = ()=>this.order.itemsVisible = !this.order.itemsVisible;
      if(this.order.itemsVisible) return toggle();
      let res = this.$listeners['requestItems']?.(this.order);
      if(res instanceof Promise) return await res.finally(toggle);
      else toggle();
    },
    async _additionalDelivery(item){
      let max = item.quantity;
      let quantity = 0;
      do {
        quantity = prompt(`Ввведите количество на довоз (Максимум: ${max}):\n(Дробная часть через точку)`, max);
        if(quantity===null) return;
        quantity =  Number.parseFloat(quantity) || 0;
        let message = `Введено: ${quantity}. Попробовать снова?`;
        if(quantity > 0 && quantity <= max) continue;
        if(quantity <= 0 && !confirm("Количество должно быть больше нуля. "+message)) return;
        if(quantity>max && !confirm(`Количество не должно быть больше ${max}. ${message}`)) return;
        quantity = null
      } while(!quantity);
      try{
			  let { data } = await axios.get("/admin/createDeliveryAdd", { params:{ orderId:this.order.id, itemId:item.itemId, quantity } })
        if(!data.success)	alert(`Ошибка: ${data?.error?.msg}.`);
        else {
          item.addStatus = 1;
        }
      } catch(e) {
        alert(e.message);
      }
		}
  }
}
</script>

<style lang="scss">
.v-order{
  .restore-button, .refuse-button{
    border: 1px solid;
    border-radius: 4px;
    margin: 3px;
    padding: 1px 4px;
    cursor: pointer;
    user-select: none;
    text-align: center;
    &:hover{
      box-shadow: 0px 0px 8px #00000045;
    }
    &:active{
      filter: brightness(0.95);
      box-shadow: inset 0px 0px 4px #1d1d1db5;
    }
  }
  .restore-button{
    display: none;
    background: #7ef77c;
  }
  .refuse-button{
    background: #f77c7c;
  }
  .needHandle .order-status{
    border: 3px solid green;
    background: #daffda;
    .scaned-status{
      color: blue;
      border: 2px solid;
      border-radius: 4px;
    }
  }
  .notifiIcon{
    width:30px;
    border-radius: 4px;
    padding: 2px;
    &.is-temp-notes{
      background:#ffa05cc7;
    }
  }
  .user-note{
    width:auto;
    max-width: 70%;
    height:max-content;
    padding: 5px;
    border-radius: 8px;
    box-shadow: 0px 0px 6px gray;
    transition-duration: 1s;
    transition-timing-function: ease-in-out;
    .close-button{
      position: absolute;
      right: 10px;
      user-select: none;
      cursor: pointer;
      font-weight: bold;
    }
    &.collapsed{
      display: none;
    }
    h4{
      margin: 0 3px;
      margin-top:5px;
      margin-bottom: 8px;
      cursor: default;
    }
    .note-text{
      margin: 0 3px;
      max-height: 300px;
      overflow-y: auto;
      .note-item:hover{
        background: #efefef;
      }
    }
  }
  .inner-goods{
    tr{
      &.weight{
        box-shadow: inset 0px 0px 0px 2px #c9c9ff;
        background: #ededff;
      }
      &.scaned{
        box-shadow: inset 0px 0px 0px 2px #beffbb;
        background: #daffda;
      }
      &.pack{
        background: #c8c4bf;
      }
    }
  }
}
</style>