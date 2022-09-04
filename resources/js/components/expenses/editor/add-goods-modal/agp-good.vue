<template>
	<tr :class="(!item.quantityAll || item.quantityAll==0)?'tableLine nullquantity':(item.quantity>0)?'tableLine selected':'tableLine'">
		<td>{{item.itemId}}</td>
		<td><img :src="item.image" height="100"></td>
		<td>
			<a :href="shop_url+'single?id='+item.itemId" title="карточка товара" target="_blank">
				{{item.title}}<p class="blink" v-if="item.prepayment > 0">Товар по предоплате</p>
			</a>
		</td>
		<td>{{price | currencydecimal}}</td>
		<td>{{stockPrice | currencydecimal}}</td>
		<td>{{actualPriceType=='discountBound'?item.discountBound:'нет'}}</td>
		<td>{{item.discountPrice | currencydecimal}}</td>
		<td>{{item.quantityAll}}</td>
		<td>
		<input :disabled="(!item.quantityAll || item.quantityAll==0)"
			type="number"
			min="0"
			v-model="item.quantity"
			@change="$emit('editCount',item)"
			:style="parseFloat(item.quantity)>parseFloat(item.quantityAll)?'color:red;':''">
		</td>
		<td>{{ actualPrice * item.quantity | currencydecimal}}</td>
	</tr>
</template>

<script>
import BaseItem from "../../mixins/base-item.js";
export default {
	mixins:[BaseItem],
	props:{
		item:Object,
		shop_url:String,
		course:{
			type:Number,
			required:true,
			default:0
		}
	}
}
</script>

<style lang="scss">
.tableLine{
  &:hover{
    box-shadow: -4px 0px 0px 0px gray;
    background: #8d8d8d2e !important;
  }
  &.selected{
    box-shadow: -4px 0px 0px 0px #058e00;
    background: #058e002e !important;
  }
	&.nullquantity td{
  	color: #931515;
	}
  input{
    width: 90px;
    border: 1px solid;
    border-radius: 3px;
  }
  .selectItem{
    width: 0px;
    margin: 10px;
  }
	
}
</style>