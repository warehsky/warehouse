<template>
	<div class="order-edit-view order-correction">
		<div class="order-title">
			Создание корректировки
		</div>
		<h2>Заказ #{{order.id}}</h2>
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
		<div class="edit-context">
			<div>
				<div class="goods-table">
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
		</div>
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

export default {
	mixins:[CorrectionsPanelMixin],
	components:{
		CorrectionItem, GoodsSelect
	},
	data(){
		return{
			items:this.order.items.map(item=>new OrderItem({ ...item, quantityOld:item.quantity })),
		}
	},
}
</script>
<style lang="scss">
.order-correction{
	label, input[type=checkbox]{
		user-select: none;
		cursor: pointer;
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
}
</style>