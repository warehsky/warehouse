<template>
  <tr :class="['correction', states]">
    <td>{{index+1}}</td>
    <!-- ID -->
    <td>{{correction.itemId}}</td>
    <!-- Наименование -->
    <td><a :href="shop_url+'single?id='+correction.itemId" title="карточка товара" target="_blank">
      {{correction.title}}
    </a></td>
    <!-- Кол-во -->
    <td>
      <span class="old-quantity">
				{{correction.quantity}}
					<span v-if="item && correction.quantity!=item.quantityOld" :class="['difference', { less: item.quantityOld > correction.quantity }]">
						(<span>{{(correction.quantity-item.quantityOld).toFixed(3)}}</span>)
					</span>
				</span>
    </td>
		<!-- Инициатор -->
		<td>{{correction.initiatorPlace==2?`Диспетчер`:'Склад'}} (id:{{correction.initiatorId}})</td>
		<td v-if="Number(correction.initiatorPlace)===1">
			<button @click="$emit('apply',{ correction, item })">Применить</button>
		</td>
  </tr>
</template>

<script>
import OrderItem from '../../../orderItem.js';
export default {
  props:{
    correction:{//корректировка
      type:Object,
      required:true
    },
		item:{//текущий товар
			type:OrderItem,
		},
    index:Number,
    shop_url:String
  },
	computed:{
		states(){
			return {
				new:!this.item || !this.item.quantityOld,
				matches:this.item && this.item.quantity == this.correction.quantity,
				warehouse:this.correction.initiatorPlace==1,
				removed:!this.correction.quantity 
			}
		}
	}
}
</script>
<style lang="scss">
.correction{
	background: #FFCE92;
	&.warehouse	{
		&.matches{
			background: #c0c6ff;
		}
	}
	&.new {
		background: #c3ffc0;
	}
	&.removed {
		background: pink;
	}
	.old-quantity{
		white-space: nowrap;
		.difference{
			color: green;
			&.less{
				color: red;
			}
			&:not(.less)>span{
				&::before{ content: "+";	}
			}
		}
	}
}
</style>