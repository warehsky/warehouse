<template>
  <div class="order-edit-view apply-order-correction">
		<div class="order-title">
			Обработка корректировок
			<div v-if="false" class="view-settings">
        <div class="settings-louncher" @click="settingsOpened = !settingsOpened">Настройки</div>
        <float-panel v-if="settingsOpened" :opened="true" style="z-index:1;">
          <div class="setting">
						<label for="onlyWarehouseCorrections">Показывать только корректировки склада</label>
						<input id="onlyWarehouseCorrections" type="checkbox" v-model="settings.onlyWarehouseCorrections" />
          </div>
        </float-panel>
      </div>
		</div>
		<h2>Заказ #{{order.id}}</h2>
		<div class="order-description">
			<div>
				<div><b>Номер 1С:</b> {{order.number.trim()||"Не присвоен"}}</div>
				<div><b>Имя заказчика:</b> {{ order.name }}</div>
				<div><b>Телефон заказчика:</b> {{order.phonePrefix}} {{order.phone}}</div>
				<div><b>Дата создания:</b> {{order.date_time_created}}</div>
				<div><b>Период доставки:</b> {{ new Date(order.deliveryDate).toLocaleDateString() }} {{wave}}</div>
				<div><b>Льгота:</b> {{ order.pension?"пенсионер":"нет" }}</div>
				<div><b>Персональная скидка:</b>
					<template v-if="order.discount">
						<template v-if="order.discount.webUserId">
							Дисконтная карта (-{{order.discount.discount}}% до {{new Date(order.discount.expiration).toLocaleDateString()}})
						</template>
						<template v-else>
							Промокод -{{order.discount.discount}}%
						</template>
					</template>
					<template v-else-if="order.proc && order.bonusUser && order.bonus_pay">
						<u>Всего:</u> {{ order.bonusUser }} <br>
						<u>Начислено:</u> {{ order.bonus }}<br>
						<u>Использовано:</u> {{ order.bonus_pay }}
					</template>
					<template v-else>Нет</template>
				</div>
			</div>
			<div class="colors">
				<div><div class="color-preview" style="background:#FFCE92"></div> - корректировка</div>
				<div><div class="color-preview" style="background:#c0c6ff"></div> - корректировка совпадающая по количеству к текущим</div>
				<div><div class="color-preview" style="background:#c3ffc0"></div> - корректировка на добавление</div>
				<div><div class="color-preview" style="background:pink"></div> - корректировка на удаление</div>
			</div>
		</div>
		<div class="edit-context" v-if="formState == formStates.loaded">
			<div>
				<div class="goods-table">
					<h3>Текущие товары</h3>
					<table border="1" ref="tblorders">
						<thead>
							<tr>
								<th>№</th>
								<th>ID</th>
								<th>Наименование</th>
								<th>Цена</th>
								<th>Кол-во</th>
								<th>Акция</th>
								<th>Дисконт кол-во</th>
								<th>Дисконт цена</th>
								<th>Остаток</th>
								<th>Сумма</th>
								<th v-if="items.contains(i=>i.prepayment>0)"></th>
							</tr>
						</thead>
						<tbody>
							<template v-for="(item, index) in items">
								<correction-item :item="item" :key="item.id"
									:index="index"
									:course="course"
									:errorRate="errorRate"
									:shop_url="shop_url"
									@editCount="editCount">
									<template #actions><slot name="actions" :item="item"></slot></template>
								</correction-item>
							</template>
						</tbody>
					</table>
				</div>
				<goods-select
					:course="course"
					:items="items"
					:shop_url="shop_url"
					@change="changeGoods($event, false); $emit('goods-changed');"
					@close="$emit('goods-changed');">
				</goods-select>
			</div>
			<div class="goods-table corrections">
				<h3>Корректировки</h3>
				<div>
					<div>
						<input id="onlyWarehouseCorrections" type="checkbox" v-model="settings.onlyWarehouseCorrections" />
						<label for="onlyWarehouseCorrections">Показывать только корректировки склада</label>
					</div>
				</div>
				<table border="1" ref="tblorders">
					<thead>
						<tr>
							<th>№</th>
							<th>ID</th>
							<th>Наименование</th>
							<th>Кол-во</th>
							<th>Инициатор</th>
						</tr>
					</thead>
					<tbody>
						<template v-for="(correction, index) in filteredCorrections">
							<correction :correction="correction" :key="correction.id"
								:index="index"
								:course="course"
								:errorRate="errorRate"
								:shop_url="shop_url"
								:item="items.find(item=>item.itemId==correction.itemId)"
								@apply="applyCorrect">
								<template #actions><slot name="actions" :item="item"></slot></template>
							</correction>
						</template>
					</tbody>
				</table>
			</div>
		</div>
		<circle-loading v-else-if="formState == formStates.loading" :radius="47" :ringWeight="13" style="margin:auto;"></circle-loading>
		<error-icon v-else scale="2"></error-icon>
		<group-box>
			<legend>Итого</legend>
			<div>Сумма(без учета скидки по товарам): {{ this.getTotal(false) | currencydecimal }}</div>
			<div>
				<span>Сумма(по товарам): {{ goodsTotal | currencydecimal}}</span>
				<span v-if="showMinSum"
					style="color:red;">
					Минимум: {{ $getCurrencyPrice(zone.conditions.limit_min, course) | currencydecimal}}
				</span>
			</div>
			<div>Доставка: {{currencyDeliveryCost | currencydecimal}}
			</div>
			<div>Персональная скидка: {{personalDiscount | currencydecimal}}</div>
			<div>Общая скидка: {{discount | currencydecimal}}</div>
			<div><b>К оплате:</b> {{ Math.round(getTotalWithDeliveryCost(true) - personalDiscount) | currencydecimal}}</div>
		</group-box>
		<group-box>
			<legend>Действия</legend>
			<div>
				<button class="btn btn-active" :disabled="state == states.loading" @click="cancelEdit">Отмена</button>
				<async-button class="btn btn-active" @click="save" @wait="state = states.loading" @error="state = states.error">
					<template #default>Сохранить</template>
					<template #wait>Сохранение...</template>
				</async-button>
				<span v-if="state==states.error" style="color:red">{{outputError}}</span>
			</div>
		</group-box>
  </div>
</template>

<script>
import OrderItem from '../../../../orderItem.js';
import CorrectionItem from '../correction-item.vue';
import GoodsSelect from '../../../inputs/goods-select.vue';
import CorrectionsPanelMixin from '../corrections-panel.js';
import Correction from '../correction.vue';
import FloatPanel from '../../../../../UI/panels/float-panel.vue';
import Order from '../../../../order.js';

export default {
	mixins:[CorrectionsPanelMixin],
	components:{
		OrderItem,
		CorrectionItem,
		GoodsSelect,
		Correction,
		FloatPanel
	},
	computed:{
		newItems(){//новые товары, добавленные из корректровок
			return this.items.filter(item=>this.defaultItems.includes(item.itemId));
		},
		outputItems(){//текущие товары + новые товары, добавленные из корректровок
			return this.items.concat(this.newItems);
		},
		filteredCorrections(){
			let result = this.corrections;
			if(this.settings.onlyWarehouseCorrections) result = result.filter((correction)=>correction.initiatorPlace==1)
			return result;
		}
	},
	data() {
		let items = this.order.items.map(item=>new OrderItem({ ...item, quantityOld:item.quantity }))
		return {
			defaultItems:items.map(item=>item.itemId),
			items,//текущие товары
			corrections:[],//корректировки
			settings:JSON.parse(localStorage.acp_settings||"null")||{
				onlyWarehouseCorrections:false
			}
		}
	},
	watch:{
		settings:{
			deep:true,
			handler(){localStorage.acp_settings = JSON.stringify(this.settings)}
		}
	},
	mounted(){
		this.loadOrderCorrects();
	},
	methods:{
		loadOrderCorrects(){
			this.formState = this.formStates.loading;
			axios.get("/Api/getOrderCorrects", { params:{ orderId:this.order.id } })
				.then(async ({data})=>{
					this.corrections = data.changes;
					for(let correction of this.corrections){
						if(correction.initiatorPlace != 2) continue;
						let item = this.items.find(item=>item.itemId==correction.itemId);
						if(item) item.quantity = correction.quantity;
						else await this.addNewItem(correction);
					}
					this.formState = this.formStates.loaded;
				})
				.catch((error)=>{ console.error(error); this.formState = this.formStates.error })
		},
		async addNewItem(correction){
			return axios.get("/Api/getItems", { params:{ items:`[${correction.itemId}]` } })
				.then(({data})=>{
				  let item = new OrderItem({ ...data.items[0], quantity:correction.quantity })
					this.items.push(item);
					return item;
				})
		},
		async applyCorrect({ item, correction }){
			try	{
				if(!item) item = await this.addNewItem(correction);
				item.quantity = correction.quantity;
			} catch(e) {
				console.error(e);
				alert("Ошибка. Попробуйте снова или обратитесь к программистам.");
			}
		},
	}
}
</script> 

<style lang="scss">
.apply-order-correction{
	.order-description{
		display: flex;
		justify-content: space-between;
		padding-right: 30px;
		.colors{
			.color-preview{
				display: inline-block;
				width: 15px;
				height: 15px;
				border: 1px solid;
			}
		}
	}
	.circle-loading{
		.loader{
			border-color: #78787833;
			border-left-color: white;
		}
	}
	label, input[type=checkbox]{
		user-select: none;
		cursor: pointer;
	}
	.edit-context{
		grid-gap: 30px;
	}
	.goods-table{
		max-height: 70vh;
    overflow-y: auto;
		tr.disabled{
			filter: grayscale(0.3) brightness(0.8);
			background: white;
		}
		.btn:disabled{
			pointer-events: none;
		}
		& > table{
			position: sticky;
			top: 0px;
			border-collapse: initial;
			border-spacing: 0px;
			th{
				background: #ebebeb;
			}
			th{
				position: sticky;
				top: 0px;
			}
		}
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
}
</style>