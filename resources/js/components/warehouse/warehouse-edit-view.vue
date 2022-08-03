<template>
  <div class="warehouse-edit-view">
		<div class="order-title">
			Заполнение веса(кол-ва) товаров заказа #{{order.id}}
			<div class="view-settings">
        <div class="settings-louncher" @click="settingsOpened = !settingsOpened">Настройки</div>
        <float-panel v-if="settingsOpened" :opened="true" style="z-index:1;">
          <div class="setting">
            <label for="itemNotFound">Сообщать о том, что товар не найден в заказе.</label><input type="checkbox" id="itemNotFound" v-model="settings.itemNotFound" />
          </div>
        </float-panel>
      </div>
		</div>
		<div>
			Дата создания: {{order.date_time_created}}<br>
			Дата доставки: {{order.deliveryDate}} {{wave}}
		</div>
		<p>
			При сканировании штрих-кода вес(кол-во) соответствующего товара заполняются автоматически.<br>
			После сканирования всех штрих-кодов, проверьте, что вес(кол-во) соответствует значению на чеке.<br>
		</p>
		<div :class="['message',{ disabled:manually }]">
			<div class="scan-message" v-if="!documentFocused" style="color:red; min-height: 80px;">
				На данный момент вы не сфокусированы на странице!<br>
				Кликните в любом месе на странице для начала сканирования.
			</div>
			<div class="scan-message" v-else style="color:green; min-height: 80px;">
				Можно сканировать.
			</div>
		</div>
		<div class="scan-message">Текущий статус: {{scanedStatus}}</div>
		<div>
			<div>Ввести вручную:</div>
			<input 
				ref="manualInput"
				type="text"
				@change="applyCode($event.target.value);"
				@focus="manually = true"
				@blur="manually = false">
		</div>
		<div>
			<input type="checkbox" id="itemsSort" v-model="itemsSort"><label for="itemsSort">Сначала весовые</label>
		</div>
		<div>
			<div class="goods-table">
				<table border="1" ref="tblorders">
					<thead>
						<tr>
							<th>№</th>
							<th>ID</th>
							<th>ID(весы)</th>
							<th>Наименование</th>
							<th>Базовое кол-во</th>
							<th>Кол-во</th>
							<th>Остаток</th>
							<th>Увеличение (Добавление)</th>
							<th>Заполнено</th>
							<th>В корзине</th>
							<th>Оповещение</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item, index_i) in items" :key="item.id" :class="{ weight:item.weightId, scaned:item.scaned }">
							<td>{{index_i+1}}</td>
							<!-- ID -->
							<td>{{item.itemId}}</td>
							<!-- ID(весы) -->
							<td>{{item.weightId || "Нет"}}</td>
							<!-- Наименование -->
							<td><a :href="shop_url+'single?id='+item.itemId" title="карточка товара" target="_blank">
								{{item.title}}
							</a></td>
							<td style="text-align:center;">{{item.quantity_base}}</td>
							<!-- Кол-во -->
							<td>
								<div style="white-space: nowrap;" v-if="item.weightId">
									<input ref="itemInput"
										:disabled="!item.scaned"
										class="checkout_text quantity"
										type="number"
										v-model="item.quantity_warehouse"
										:min="item.mult || 1" 
										max="1000" 
										@focus="manually = true"
										@blur="manually = false;"
										@change="()=>{ item.manually = 1; if(item.weightId) editCount(item); }" :style="item.quantity_warehouse>item.quantityAll?'color:red;':''">
								</div>
								<div v-else>{{item.quantity}}</div>
							</td>
							<!-- Остаток -->
							<td>{{item.quantityAll}}</td>
							<!-- Увеличение (Добавление) -->
							<td style="text-align: center;"><input type="checkbox" v-if="item.weightId" v-model="item.increment" /></td>
							<!-- Заполнено -->
							<td style="text-align: center;"><input type="checkbox" v-if="item.weightId" v-model="item.scaned" /></td>
							<td><input v-if="item.weightId" type="checkbox" /></td>
							<td>
								<div v-if="(Math.abs(item.quantity_warehouse-item.quantity_base)>errorRate/1000)&&item.weightId&&item.weightId[0]!=0" style="color:red;">
									Не совпадает с базовым кол-вом на {{(Math.abs(item.quantity_warehouse-item.quantity_base) - errorRate/1000).toFixed(3)}} с учетом погрешности {{errorRate}} грамм
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div>
				<div v-if="!ismobile" style="display:flex;">
					<print-context @click="getOrderItemsWeight" button-class="btn btn-active" style="margin-bottom:10px;">
						Печать таблицы
						<template #template>
							<div>
								<h2>Заказ №{{order.id}}</h2>
								Дата создания: {{order.date_time_created}}<br>
								Дата доставки: {{order.deliveryDate}} {{wave}}
							</div>
							<div v-for="(items_data,index) in itemsToPrint" :key="index">
								<h1>{{items_data.title}}</h1>
								<span>Заказ №{{order.id}}</span>
								<table border="1" ref="tblorders" style="width:100%">
									<thead>
										<tr>
											<th>№</th>
											<th>ID</th>
											<th>ID(весы)</th>
											<th>Наименование</th>
											<th>Кол-во</th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="(item, index_i) in items_data.items" :key="item.id" :class="{ scaned:item.scaned }">
											<td>{{index_i+1}}</td>
											<!-- ID -->
											<td>{{item.itemId}}</td>
											<!-- ID(весы) -->
											<td>{{item.weightId || "Нет"}}</td>
											<!-- Наименование -->
											<td>{{item.title}}</td>
											<!-- Кол-во -->
											<td>
												<div v-if="item.weightId" :style="item.quantity_warehouse>item.quantityAll?'color:red;':''">{{item.quantity_warehouse}}</div>
												<div v-else :style="item.quantity>item.quantityAll?'color:red;':''">{{item.quantity}}</div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</template>
					</print-context>
					<span>*Перед началом печати убедитесь в том, что вы сохранили изменения.</span>
				</div>
				<button class="btn btn-active" :disabled="state == states.loading" @click="cancelEdit">Отмена</button>
				<async-button class="btn btn-active" @click="saveOrder" @wait="state = states.loading" @error="state = states.error">
					<template #default>Сохранить</template>
					<template #wait>Сохранение...</template>
				</async-button>
				<span v-if="state==states.error" style="color:red">{{outputError}}</span>
			</div>
		</div>
	</div>
</template>

<script>
import Order from '../orders/order.js'
import PrintContext from '../os_tools_components/print-context.vue';
import ComputedScanedStatus from '../orders/mixins/computed-scan-status.js';
import FloatPanel from '../UI/panels/float-panel.vue';

export default {
	name:"warehouse-edit-view",
	mixins:[ComputedScanedStatus],
	components:{
		PrintContext,
		FloatPanel
	},
	props:{
		order:{
			type:Order,
			required:true
		},
		shop_url:String,
		waves:{
			type:Array,
			default(){
				return [];
			}
		},
		increment:{
			type:Boolean,
			default(){
				return false
			}
		},
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
		let settings;
		try	{ settings = JSON.parse(localStorage.warehouseSettings); }
		catch{ settings = {} }

		return{
			documentFocused:false,
			state:1,
      states:{
        error:-1,
        loading:0,
        loaded:1
      },
			outputError:"",
			manually:false,
			itemsToPrint:[],
			itemsSort:JSON.parse(localStorage.itemsSort||"true"),
			settingsOpened:false,
			settings:{
				itemNotFound:typeof settings.itemNotFound == 'boolean'?settings.itemNotFound:true
			}
		}
	},
	watch:{
		itemsSort:function(){
			localStorage.itemsSort = this.itemsSort;
		},
		settings:{
			handler(){ localStorage.warehouseSettings = JSON.stringify(this.settings) },
			deep:true
		}
	},
	computed:{
		wave(){
			return this.waves.find(w=>w.id==this.order.waveId)?.value;
		},
		items(){
			return this.itemsSort?this.order.items.filter(i=>i.weightId).concat(this.order.items.filter(i=>!i.weightId)):this.order.items;
		}
	},
	beforeMount(){
		window.onbeforeunload = ()=>{
			this.$emit("back");
			return "";
		}
	},
	mounted(){
		this.saveOrderDraft();
		window.addEventListener("focus",()=>this.documentFocused = true);
		window.addEventListener("blur",()=>this.documentFocused = false);
		this.documentFocused = document.hasFocus();
		this.listenScaner();
	},
	methods:{
		getOrderItemsWeight(){
	 		return new Promise((resolve)=>{ 
				axios.get("/Api/getOrderItemsWeight",{ headers:{ "X-Access-Token":Globals.api_token }, params:{ orderId:this.order.id } })
				.then(({data})=>{
					this.itemsToPrint = [];
					if(data.items.weight) this.itemsToPrint.push({ title:"Весовые товары", items:data.items.weight });
					if(data.items.notweight) this.itemsToPrint.push({ title:"Прочие товары", items:data.items.notweight });
					this.$nextTick(()=>{ resolve(); });
				})
				.catch((e)=>{ console.error(e); reject(e); });
			})
		},
		async saveOrder(){
			this.state = this.states.loading;
			await this.setWarehouseOrder(this.order)
        .then(()=>{
          this.state = this.states.loaded;
					window.onbeforeunload = null;
          window.location.reload();
        })
        .catch(error => {
          if(error.type=="thrown"){
						let message = "Ошибка "+(error.response.data.code || error.response.data.error)
            this.showError(message);
            this.state = this.states.error;
						throw new Error(message);
          }
          else{
            this.errored = true;
            this.showError(error.response.message);
						throw new Error(error.response.message);
          }
        })
		},
		setWarehouseOrder(order){
			return new Promise((resolve,reject)=>{
				if(!order) reject(new Error("(setOrder) Не передан order"));
				axios
				.post("/Api/saveWarehouseOrder", {
					headers: {'X-Access-Token': Globals.api_token, "content-type": "application/json"},
					params: {
						orderId:order.id,
						items:order.items
					},
				},
				{headers: {'X-Access-Token': Globals.api_token, "content-type": "application/json"}})
				.then(response => {
					if(!response.data.error && response.data.code==200){
						if(order.id==0)
							order.id = response.data.order.id;
						resolve(response);
					}
					else reject({ type:"thrown", response });
				}).catch(error => {	reject({ type:"catched", response:error });	})
			})
		},
		listenScaner(){
			this.code = "";
			document.addEventListener("keypress",(e)=>{
				if(this.manually || !(/^Digit\d$/.test(e.code) || e.code == 'Enter')) return;
				if(e.code == 'Enter'){ 
					this.applyCode(this.code);
					this.code = "";
					return;
				}
				this.code +=e.key
			});
		},
		applyCode(code){
			if(!code) return;
			code = String(code);
			if(!Number(code) || code.length<13 || code[0]!=2){
				alert(`Неверный формат штрих-кода: ${code}.`);
				return false;
			}
			let weightItemId = code.slice(1,7);
			
			let itemWeight;
			if(code[1]==0) // усли это пакеты
				itemWeight = 1;
			else
				itemWeight = code.slice(7,12)/1000;
			let item = null;
			
			item = this.order.items.find(i=>i.weightId==weightItemId);
			if(!item){
				if(code[1]==0){
					let opack = this.packs.find(i=>i.weightId==weightItemId);
					if(opack){
						let pack = Object.assign({}, opack);
						pack.itemId = pack.id;
						pack.id = undefined;
						pack.quantity = 0;
						pack.quantity_base = 0;
						pack.quantity_warehouse = 1;
						pack.manually = 0;
						pack.manually_mode = false;
						pack.scaned = true;
						pack.increment = true;
						pack.pack = true;
						this.order.items.push(pack);
					}
					return Boolean(opack);
				}else {
					if(this.settings.itemNotFound)
						alert(`Ошибка идентификации товара #${weightItemId}.\n (Товар не найден в заказе).`);
					return false;
				}
			}
			item.quantity_warehouse = item.increment && item.scaned?item.quantity_warehouse + itemWeight:itemWeight;
			if(code[1]==0) // усли это пакеты
				item.increment = true;
			else
				if(!this.increment) item.increment = false;

			this.editCount(item);
			item.scaned = true;
			item.manually = 0;
			return true;
		},
		editCount(item){
			item.quantity_warehouse = Number(item.quantity_warehouse);
      if(!item.quantity_warehouse || item.quantity_warehouse<=0)
        item.quantity_warehouse=1;
      if(item.quantity_warehouse>1000)
        item.quantity_warehouse=1000;
      if(item.stockPrice){ return; }
			item.quantity_warehouse = Number(item.quantity_warehouse.toFixed(3));
			//TODO:Проверить, нужен ли здась пересчет item.courier (изменится ли он на сервере отсюда)
      if(item.discountBound>0 && item.discountBound<2000000 && item.quantity_warehouse>=item.discountBound && item.discountPrice)
        item.courier = item.discountPrice;
      else
        item.courier = item.price;
    },
		showError(arg){
      this.state = this.states.error;
      this.outputError = arg;
    },
		toggleMode(){
			if(this.manually = !this.manually) this.$nextTick(()=>this.$refs.manualInput.focus());
		},
		saveOrderDraft(onlyStorage=false){
      let draft = _.cloneDeep(this.order);
      localStorage.orderDraft = JSON.stringify(draft);
      if(!onlyStorage) 
        this.orderDraft = draft;
      this.$emit("save-draft",localStorage.orderDraft);
    },
		cancelEdit(){
      this.$emit('cancel',this.order,this.orderDraft);
    },
	}
}
</script>

<style lang="scss">
.warehouse-edit-view{
	border: 1px solid #bfbfbf;
	border-radius: 8px;
	padding: 8px;
	background: #ebebeb;
	min-width: min-content;
	.quantity{
		padding: 3px;
		min-width: 6em;
		&:disabled{
			background: #ebebeb;
		}
	}
	.order-title{
		font-size: 18px;
		font-weight: 600;
		display: flex;
	}
	.view-settings{
    font-size: 18px;
    .settings-louncher{
      cursor: pointer;
      margin-left: 20px;
      color:gray;
      &:hover{
        color: black;
      }
    }
    .setting{
      display: flex;
      padding:5px;
      margin: 5px;
      border-bottom: 1px solid gray;
      align-items: center;
      label{
        display:block;
        width:100%;
      }
      cursor: pointer;
      &:hover{
        background:#dddddd;
      }
    }
    .float-panel{
      width:auto;
      height: auto;
      padding:4px;
      border-radius: 4px;
      box-shadow: 0px 0px 4px 0px gray;
      border:none;
    }
  }
	.goods-table{
		display: flex;
		tr.weight{
			background: #efd6b9;
		}
		tr.scaned{
			background: #b9efbd;
		}
	}
	.message{
		&.disabled{
			opacity: 0.3;
		}
	}
	.scan-message{
		font-size: 18px;
    border: 3px solid;
    background: white;
    margin: 20px;
    padding: 10px;
    display: flex;
    align-items: center;
	}
}
</style>