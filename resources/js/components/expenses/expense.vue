<template>
  <div class="orders">
    <h2>Редактировать расходный ордер</h2>
    <div class="order-head">
      <span>№ ордера: </span><span>{{this.expense?this.expense.id:''}}</span><span>&nbsp;&nbsp;&nbsp;</span>
      <span>Дата ордера</span>
      <span><input 
              name="Дата"
              type="date"
              v-model="expense.expenseDate"
              min="today.toShortDateString()"/>
      </span><span>&nbsp;&nbsp;&nbsp;</span>
      <span>Клиент </span>
      <button type="button" @click="modalOpened.clients = true;" class="btn btn-points">...</button>
      <span>&nbsp;&nbsp;</span>
      <span>[#{{this.expense?this.expense.clientId:''}}]{{!this.expense||this.expense.clientId==0?'не выбран':this.expense.client}}</span>
    </div>
    <div class="order-list">
      <div v-if="updating || expense.expense_items.length==0">нет записей</div>
      <table v-if="!updating && expense.expense_items.length>0" class="report-table">
			<thead>
				<td>ID</td>
        <td>Заказ№</td>
				<td>Название</td>
				<td>Кол-во</td>
				<td>Цена</td>
        <td>Сумма</td>
				<td>Примечание</td>
				<td>Действие</td>
				<!-- <td></td> -->
			</thead>
			<tbody>
				<tr v-for="item in expense.expense_items" :key="item.id" :item="item">
					<td>{{item.id}}</td>
          <td>{{item.orderId}}</td>
					<td>{{item.items.item}}</td>
          <td class="tdinput">
            <input type="number" v-model="item.quantity" min="0"/>
          </td>
					<td class="tdinput">
            <input type="number" v-model="item.price" min="0"/>
          </td>
          <td class="tdinput">
            {{item.price*item.quantity  | currencydecimal}}
          </td>
					<td>
            <input type="text" v-model="item.note" />
          </td>
					<td></td>
				</tr>
			</tbody>
		  </table>
      <div class="order_add"><input type="button" value="Добавить" @click="addGoods();"/></div>
    </div>
    <div class="order_bottom">
      <div class="order-itog">
        <div class="order-itog-row">
          <label>Сумма услуг: </label><span>{{ total.one | currencydecimal }}</span>
        </div>
        <div class="order-itog-row">
          <label>Сумма хранение: </label><span>{{ total.days | currencydecimal }}</span>
        </div>
        <div class="order-itog-row">
          <label>Сумма всего: </label><span>{{ total.days+total.one | currencydecimal }}</span>
        </div>
      </div>
      <input type="button" value="Сохранить" @click="saveexpense(expense);"/>
      <input type="button" value="Отмена" @click="onCancelEdit(expense);"/>
    </div>

    <modal class="waves-report-modal"
      v-if="modalOpened.clients"
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
      v-if="modalOpened.items"
      :show="['cancel']"
      cancel='Выход'
      @close="modalOpened.items = false"
      @confirm="modalOpened.items = false">
      <template #header>
        <h2 class="modalHeaderItem">Остатки</h2>
      </template>
      <template #body>
        <items-view ref="report"
          :client_id="expense.clientId"
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
import itemsView from "../items/reminds-view.vue";
export default {
  name:"expense",
  props:{
    expense_id:Number,
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
      expense: {"id":0,"expenseDate":(new Date()).toShortDateString(),"clientId":0,"client":'', "expense_items":[]}
    }
  },
  computed:{
    data(){
      return {
        expense_id: null
      }
    },
    total(){
        let service={one:0, days:0};
        
        let days = 1;
        let q = 0;
        this.expense.expense_items.forEach(item=>{
          if(true)
            service.days += item.price * item.quantity * days;
          else
            service.one += item.price * q;

        });
        return service;
    }
  },
  filters:{
		currencydecimal(value){ return `${ value.toFixed(2) }`; }
	},
  mounted(){
     if(this.expense_id)
       this.getexpense({"expenseId":this.expense_id});
  },
  methods:{
    getexpense(params){
			return new Promise((resolve,reject)=>{
				axios
					.get("/getExpense",{ params })
					.then(({data})=>{
            this.expense = data.expense;
            
					})
					.catch((e)=>{ console.error(e); reject(e) });
			})
		},
    setexpense(expense){
      return new Promise((resolve,reject)=>{
        if(!expense) reject(new Error("(setexpense) Не передан expense"));
        axios
        .post('/saveExpense', {
          headers: {'X-Access-Token': Globals.api_token, "content-type": "application/json"},
          params: {
            expense:{
              ...expense
              
            },
          },
        },
        {headers: {'X-Access-Token': Globals.api_token, "content-type": "application/json"}})
        .then(response => {
          if(!response.data.error && response.data.code==200){
            if(expense.id==0)
              expense.id = response.data.expense.id;
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
    saveexpense(expense,resolve,reject){
      axios.defaults.headers.common["X-Access-Token"] = Globals.api_token;
      this.setexpense(expense)
        .then(()=>this.freeexpense(expense))
        .then(()=>resolve?.())
        .catch(error=>reject?.(error));
    },
    addGoods(){
      if(this.expense.clientId==0)
      {
        alert('Клиент не выбран');
        return;
      } 
      this.modalOpened.items = true;
    },
    refuseexpense(expense){//обработка клика кнопки отмены заказа
      if(!confirm("Подтвердите отмену заказа №"+expense.id+" !"))
        return;
      expense.status = 3;
      this.saveexpense(expense);
      this.freeexpense(expense);

     // window.location.reload();
    },
    onCancelEdit(expense,draft){
      if(draft){
        let index = this.expenses.indexOf(expense);
        Vue.set(this.expenses,index,new expense().set(draft));// изначально draft не реактивный
      }
      // this.closeexpense();
      this.freeexpense(expense);
    },
    tryTakeexpense(expense, mode = this.modes.edit){
      if(this.editLoading>=0) return;
      this.editLoading = expense.id;
      return axios.get("/checkexpenseLock",{ params:{ expenseId:expense.id , edit:1 } })//заблокировать зказ
        .then(({ data })=>{
          if(data.lock.length) {
            let output = mode == this.modes.edit?"Заказ уже редактируется:\n":"Заказ занят.(Корректировка):\n";
            data.lock.forEach(moderator => {
              output +=  `Имя редактирующего: ${moderator.name} (id: ${moderator.id})\n`;
            });
            alert(output);
          } else switch(mode){
            case(this.modes.correct): case(this.modes.applyCorrect): this.correct(expense); break;
            default:this.openexpense(expense, mode);
          }
        }).catch((error)=>{
          console.log(error);
          alert("Ошибка: "+error.code);
        }).finally(()=>{
          this.editLoading = -1;
        });
    },
    freeexpense(expense){
      return axios.get("/expenseUnlock",{ params:{ expenseId:expense.id || -1 } })//разблокировать зказ
        .then(({ data })=>{
          if(!data.success){
            console.error(data);
            return;
          }
          expense.locked = false;
          window.location = this.wareh_url+"/expenses"
        }).catch((error)=>{
          console.log(error);
          alert("Ошибка: "+error);
        })
    },
    onSelectClient(client){
      this.modalOpened.clients = false;
      this.expense.clientId=client.id;
      this.expense.client=client.client;
    },
    onSelectItem(item){
      this.modalOpened.items = false;
      let itm = {"itemId":item.itemId, "orderId":item.orderId, "items":item.item, "quantity":item.quantity, "price":item.price, "note":item.note};

      this.expense.expense_items.push(itm);
    },
    /**
     * @param {expense} expense Заказ
     * @description Открывает заказ в режиме корректировки
     * TODO: replace /Api/getAlerts
     */
    async correct(expense){
      // let { data:{ corrects } } = await axios.get("/Api/getAlerts",{ headers:{ 'X-Access-Token': Globals.api_token } })
      // this.openexpense(expense, expense.id in corrects?this.modes.applyCorrect:this.modes.correct);
    },
    _onClickEdit(expense, isClone=false){
      let mode;
      if([1, 7].includes(expense.status)) mode = this.modes.edit;
      else if(this.superEdit && expense.status != 1 && expense.status != 7) mode = this.modes.fixEdit;
      else return;
      if(isClone) return this.openexpense(expense, mode);
      return this.tryTakeexpense(expense, mode);
    },
  }
}
</script>

<style lang="scss">
.modal{
    position: relative;
  }
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
.order-head, .order_bottom{
  padding: 5px;
  background: #e2e2e2;
}
.order_add{
  text-align: right;
  width: 100%;
  margin: 3px 0;
}
.btn-points{
  line-height: 0.5!important;
}
.tdinput,.tdinput input {
  width: 100px;
}
</style>