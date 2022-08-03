<template>
  <div class="warehouse-edit-view">
		<div class="order-title">
			Заполнение кол-ва товаров заказа #{{order.id}}
		</div>
		<div>
			Дата создания: {{order.date_time_created}}<br>
			Дата доставки: {{order.deliveryDate}} {{wave}}
		</div>
		<p>
			При сканировании штрих-кода кол-во соответствующего пакета заполняются автоматически.<br>
			После сканирования всех штрих-кодов, проверьте, что кол-во соответствует значению на чеке.<br>
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
		<div class="packs-buttons">
			<group-box>
				<legend>Добавить пакет</legend>
				<button v-for="code in packsCodes" :key="code.body"
					class="pack-button" @click="applyCode(code.body)">{{code.title}}</button>
				<div style="display: inline-flex; align-items: center;">
					<input :disabled="Boolean(orderPacks.length)" id="withoutPacks" type="checkbox" class="pack-button" style="color:red; margin-left: 200px;" v-model="order.nopacks"/>
					<label for="withoutPacks">Без пакетов</label>
				</div>
			</group-box>
		</div>
		<input id="showItems" type="checkbox" v-model="showItems"><label for="showItems">Показать товары</label>
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
							<th></th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item, index_i) in order.items" :key="item.id" v-show="item.parentId==179 || showItems" :class="{ item:item.parentId != 179, weight:item.weightId, scaned:item.scaned }">
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
								<div style="white-space: nowrap;" v-if="item.weightId && item.parentId==179">
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
									<!-- <button :disabled="!item.scaned || item.manually_mode" @click="item.manually_mode = true; $nextTick(()=>{ $refs.itemInput[index_i].focus(); })">Вручную</button> -->
								</div>
								<div v-else>{{item.quantity}}</div>
							</td>
							<!-- Остаток -->
							<td>{{item.quantityAll}}</td>
							<!-- Увеличение (Добавление) -->
							<td style="text-align: center;"><input type="checkbox" v-if="item.weightId && item.parentId==179" v-model="item.increment" /></td>
							<!-- Заполнено -->
							<td style="text-align: center;"><input type="checkbox" v-if="item.weightId && item.parentId==179" v-model="item.scaned" /></td>
							<td><button v-if="item.parentId == 179" @click="order.items.remove(index_i);">Удалить</button></td>
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
import Order from '../../orders/order.js'
import PrintContext from '../../os_tools_components/print-context.vue';
import ComputedScanedStatus from '../../orders/mixins/computed-scan-status.js';
import OrderItem from '../../orders/orderItem.js';

export default {
	name:"wp-edit-view",
	mixins:[ComputedScanedStatus],
	components:{
		PrintContext
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
		packs:{
			type:Array,
			default(){ return[] }
		},
		ismobile:[Boolean,Number]
	},
	data(){
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
			showItems:false,
			packsCodes:[
				{ body:'2000001010006', title:'Маленький' },
				{ body:'2000002010005',title:'Средний' },
				{ body:'2000003010004', title:'Большой' },
				{ body:'2000005010006', title:'Майка' }
			]
		}
	},
	computed:{
		wave(){
			return this.waves.find(w=>w.id==this.order.waveId)?.value;
		},
		orderPacks(){
			return this.order.items.filter(item=>item.parentId==179)
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
				let items = order.items.filter(item=>item.parentId==179);
				axios
				.post("/Api/saveWarehouseOrderPacks", {
					headers: {'X-Access-Token': Globals.api_token, "content-type": "application/json"},
					params: {	orderId:order.id, items, nopacks:order.nopacks },
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
			if(!code) return false;
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
			if(item && item.parentId!=179) return false;
			if(!item){
				if(code[1]==0){
					let opack = this.packs.find(i=>i.weightId==weightItemId);
					if(opack){
						let pack = new OrderItem({
							...opack,
							itemId:opack.id,
							quantity:0,
							quantity_warehouse:1,
							scaned:true,
							increment:true,
						});
						this.order.items.push(pack);
						this.order.nopacks = false;
						return true;
					}
				}
				alert(`Ошибка идентификации пакета #${weightItemId}.`);
				return false;
			}
			if(code[1]==0) // усли это пакеты
				item.increment = true;
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
			item.quantity_warehouse = Number(item.quantity_warehouse.toFixed(3));
      if(item.stockPrice){ return; }
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
	.pack-button{
		height: 38px;
		border-radius: 4px;
		user-select: none;
		margin: 0 5px;
		min-width: 100px;
	}
	.quantity{
		padding: 3px;
		min-width: 6em;
		&:not(:focus){
			background: #f1f1f1;
		}
		&:disabled{
			background: #ebebeb;
		}
	}
	.order-title{
		font-size: 18px;
		font-weight: 600;
	}
	.goods-table{
		display: flex;
		th{
			z-index: 1;
		}
		tbody tr{
			&.item{
				filter: contrast(0.95) grayscale(0.1) brightness(0.8);
				background: white;
			}
			&.weight{
				background: #efd6b9;
			}
			&.scaned{
				background: #b9efbd;
			}
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