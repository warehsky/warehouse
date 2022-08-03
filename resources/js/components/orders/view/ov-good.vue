<template>
  <tr :class="{ weight:item.weightId, pack:item.parentId==179, scaned:item.scaned && item.parentId!=179 }"><!-- 172 - Пакеты и др. -->
		<td>{{index+1}}</td>
		<td>{{item.itemId}}</td>
		<!-- Наименование -->
		<td><a :href="shop_url+'single?id='+item.itemId" title="карточка товара" target="_blank">
			<mark-text :from="item.title.search(searchString)" :to="item.title.search(searchString)+searchString.length">{{item.title}}</mark-text>
		</a></td>
		<td>{{ $getCurrencyPrice(item.courier, course, UAH) | currencydecimal(UAH) }}</td>
		<td style="text-align:center;">{{item.quantity_base}}</td>
		<td v-if="showWarehouse" style="text-align:center;">{{item.quantity_warehouse}}</td>
		<td>
			<span :style="parseFloat(item.quantity)>parseFloat(item.quantityAll)?'color:red;':''">{{item.quantity}}</span>
		</td>
		<td>{{stockPrice?"-"+(((price-stockPrice)*100)/price).toFixed(2)+"%":'нет'}}</td>
		<td>{{actualPriceType=='discountBound'?item.discountBound:'нет'}}</td>
		<td>{{discountPrice | currencydecimal(UAH)}}</td>
		<td>{{item.quantityAll}}</td>
		<td>{{item.quantity * $getCurrencyPrice(item.courier, course, UAH) | currencydecimal(UAH) }}</td>
		<td>
			<span class="blink" v-if="item.prepayment > 0">Товар по предоплате</span>
		</td>
		<td v-if="showDelivery"><slot name="delivery"></slot></td>
	</tr>
</template>

<script>
import BaseItemMixin from '../mixins/base-item.js';
import MarkText from '../../UI/mini/mark-text.vue';
import AsyncButton from '../../UI/mini/async-button.vue';
export default {
	mixins:[BaseItemMixin],
	components:{ MarkText, AsyncButton },
	props:{
		item:{
			type:Object,
			required:true
		},
		index:Number,
		shop_url:{
			type:String,
			required:true
		},
		course:{
			type:Number,
			required:true,
			default:0
		},//курс, привязанный к заказу
		searchString:{
			type:String,
			default:""
		},
		showWarehouse:Boolean,
		showDelivery:Boolean
	},
	computed:{
		UAH(){
			return Boolean(this.course)
		}
	},
	filters:{
		currencydecimal(value,UAH){ return `${ value.toFixed(2) } ${ (UAH?"₴":"₽") }`; }
	}
}
</script>

<style>

</style>