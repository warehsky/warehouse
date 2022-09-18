<template>
  <div class="orders">
    <h2>Редактировать приходную накладную</h2>
    <div class="order-head">
      <span>№ заказа</span><span>{{this.order?this.order.id:''}}</span><span>&nbsp;&nbsp;&nbsp;</span>
      <span>Дата заказа</span>
      <span><input 
              name="Дата"
              type="date"
              v-model="order.data"
              min="today.toShortDateString()"/>
      </span><span>&nbsp;&nbsp;&nbsp;</span>
      <span>Клиент </span>
      <button type="button" @click="modalOpened.clients = true;" class="btn btn-points">...</button>
      <span>[#{{this.order?this.order.clientId:''}}]{{!this.order || this.order.clientId==0 ? "не выбран" : this.order.client}}</span><span>&nbsp;&nbsp;&nbsp;</span>
      <label for="operationId">Тип операции:</label>
        <select v-model="order.operationId">
          <option  v-for="(operation) in operations" :key="operation.id" :value="operation.id">{{operation.operation}}</option>
        </select>
    </div>
    <div class="order-list">
      <div>Услуги</div>
      <table v-if="!updating && order.order_items.length>0" class="report-table">
			<thead>
				<td>ID</td>
				<td>Название</td>
				<td>Кол-во</td>
				<td>Цена</td>
        <td>Сумма</td>
        <td>Расход</td>
				<td>Примечание</td>
				<td>Действие</td>
				<!-- <td></td> -->
			</thead>
			<tbody>
				<tr v-for="item in order.order_items" :key="item.id" :item="item">
					<td>{{item.id}}</td>
					<td>{{item.item.item}}</td>
					<td class="tdinput">
            <input type="number" v-model="item.quantity" min="0"/>
          </td>
					<td class="tdinput">
            <input type="number" v-model="item.price" min="0"/>
          </td>
          <td class="tdinput">
            {{ (item.quantity - item.quantity_loss)*item.price | currencydecimal }}
          </td>
          <td :class="item.quantity_loss>0?'tdloss':''">{{item.quantity_loss}}</td>
					<td>
            <input type="text" v-model="item.note"/>
          </td>
					<td></td>
				</tr>
			</tbody>
		  </table>
      <div  class="order_add"><input type="button" value="Добавить" @click="modalOpened.items = true;"/></div>
    </div>
    <div class="order_bottom">
      <div class="order-advance">
            <div class="field__wrapper">
              <input
                ref="uploadFiles"
                v-on:change="handleFileUploads()"
                name="uploadFiles"
                type="file"
                id="uploadFiles"
                class="field field__file"
                accept=".jpg, .jpeg, .png, .gif, .bmp, .doc, .docx, .xls, .xlsx, .txt, .tar, .zip, .7z, .7zip, .pdf"
                multiple
              />

              <label class="field__file-wrapper" for="uploadFiles">
                <div
                  ref="fileChoose"
                  class="field__file-fake"
                  v-bind:class="{
                    'border-error-vac': this.errors.Files,
                  }"
                >
                  Файл не выбран
                </div>
                <div class="field__file-button" @click="chooseFiles()">
                  Добавить документ
                </div>
              </label>
            </div>
            <div v-if="this.errors.Files" class="error-item-vac">
              Выбор файла обязателен
            </div>
      </div>
      <div class="order-attach" v-show="order.order_attach.length>0">
        <div class="attach-header">Документы</div>
        <table>
          <tr v-for="attach in order.order_attach" :key="attach.id">
            <td>
              <a  :href="attach.attach" class="attach-link" target="_blank">{{attach.attach}}</a>
            </td>
            <td style="padding-left:20px; vertical-align: top;">
              <img style="width:10px;height: 10px;" src="/img/icons/cross.svg" @click="delAttach(attach)"/>
            </td>
          </tr>
        </table>
      </div>
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
      <input type="button" value="Сохранить" @click="saveOrder(order);"/>
      <input type="button" value="Отмена" @click="onCancelEdit(order);"/>
    </div>
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
    wareh_url:String
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
      operations:[],
      modalOpened:{
        clients:false,
        items:false
      },
      errors:{Files:0},
      uploadFiles:[],
      today:new Date(),
      order: {"id":0,"orderDate":(new Date()).toShortDateString(),"clientId":0,"client":'', "order_items":[], "order_attach":[]}
    }
  },
  computed:{
    data(){
      return {
        order_id: null
      }
    },
    total(){
        let service={one:0, days:0};
        
        let days = 1;
        let q = 0;
        this.order.order_items.forEach(item=>{
          q = (item.quantity - item.quantity_loss) < 0 ? 0 : item.quantity - item.quantity_loss;
          if(item.item.cargo.evaluationId==2)
            service.days += item.price * q * days;
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
     this.getOperations();
     if(this.order_id)
       this.getOrder({"orderId":this.order_id});
  },
  methods:{
    //метод выбора файла для загрузки
    chooseFiles() {
      let fields = document.querySelectorAll(".field__file");
      fields.forEach((input) => {
        let label = input.nextElementSibling,
          labelVal = label.querySelector(".field__file-fake").innerText;

        input.addEventListener("change", (e) => {
          // let countFiles = "";
          if (
            this.$refs.uploadFiles.files &&
            this.$refs.uploadFiles.files.length >= 1
          ) {
            this.countFiles = this.$refs.uploadFiles.files.length;
          }

          if (this.countFiles)
            label.querySelector(".field__file-fake").innerText =
              "Выбрано файлов: " + this.countFiles;
          else label.querySelector(".field__file-fake").innerText = labelVal;
        });
      });
    },
    handleFileUploads() {
      this.uploadFiles = this.$refs.uploadFiles.files;
    },
    getOperations(){
			return new Promise((resolve,reject)=>{
				axios
					.get("/getOperations",{  })
					.then(({data})=>{
            this.operations = data.operations;
            
					})
					.catch((e)=>{ console.error(e); reject(e) });
			})
		},
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
    delAttach(attach){
      if(!confirm("Удалить файл " + attach.attach + "?"))
        return;
      this.order.order_attach.forEach(function(item, index, array) {
            if(item.id==attach.id){
              array.splice(index, 1);
              return false;
            }
        });

    },
    setOrder(order){
      return new Promise((resolve,reject)=>{
        if(!order) reject(new Error("(setOrder) Не передан order"));
        let formData = new FormData();
      for (let i = 0; i < this.uploadFiles.length; i++) {
        let file = this.uploadFiles[i];
        formData.append("uploadFiles[" + i + "]", file);
      }
        axios
        .post('/saveOrder', formData,{
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
      // this.closeOrder();
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
          window.location = this.wareh_url+"/orders";
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
      let itm = {"itemId":item.id, "item":item, "quantity":0, "quantity_loss":0, "price":item.price, "note":''};
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
order-head, .order_bottom{
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
.tdloss{
  color: crimson;
}
/* input upload start */

.field__wrapper {
   width: 100%;
   position: relative;
   text-align: center;
   grid-area: field__wrapper;
}
  
.field.field__file{
   display: none;
}
.field__file {
   opacity: 0;
   visibility: hidden;
   position: absolute;
}
  
.field__file-wrapper {
   width: 100%;
   display: -webkit-box;
   display: -ms-flexbox;
   display: flex;
   -webkit-box-pack: justify;
       -ms-flex-pack: justify;
           justify-content: space-between;
   -webkit-box-align: center;
       -ms-flex-align: center;
           align-items: center;
   -ms-flex-wrap: wrap;
       flex-wrap: wrap;
 }
  
 .field__file-fake {
   height: 50px;
   font-size: 14px;
   width: calc(100% - 150px);
   display: -webkit-box;
   display: -ms-flexbox;
   display: flex;
   -webkit-box-align: center;
       -ms-flex-align: center;
           align-items: center;
   padding: 0 15px;
   border: 1px solid #c7c7c7;
   border-radius: 8px 0 0 8px;
   border-right: none;
 }
  
 .field__file-button {
   width: 150px;
   height: 50px;
   background: #931515;
   color: #fff;
   font-size: 14px;
   font-weight: 700;
   display: -webkit-box;
   display: -ms-flexbox;
   display: flex;
   -webkit-box-align: center;
       -ms-flex-align: center;
           align-items: center;
   -webkit-box-pack: center;
       -ms-flex-pack: center;
           justify-content: center;
   border-radius: 8px 8px 8px 8px;
   cursor: pointer;
 }

.border-error-vac{
	border: 1px solid #FF0000;
	border-radius: 4px;
   border-bottom-right-radius: 0;
   border-top-right-radius: 0;
}
.order-attach{
  width: 100%;
  border: 1px solid #5900ff;
  margin-top: 3px;
}
/* input upload end */
</style>