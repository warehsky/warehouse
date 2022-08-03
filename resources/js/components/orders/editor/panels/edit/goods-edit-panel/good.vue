<template>
  <tr :class="{ weight:item.weightId, parentId:item.parentId==179, scaned:item.scaned}">
    <td>{{index+1}}</td>
    <!-- ID -->
    <td>{{item.itemId}}</td>
    <!-- Наименование -->
    <td><a :href="shop_url+'single?id='+item.itemId" title="карточка товара" target="_blank">
      {{item.title}}
    </a></td>
    <!-- Цена -->
    <td>{{ actualPrice | currencydecimal}}</td>
    <!-- Базовое/складское кол-во -->
    <td style="text-align:center;">
      <div>{{item.quantity_base}}</div>

      <div v-if="showWarehouse">{{item.quantity_warehouse}}</div>
    </td>
    <!-- Кол-во -->
    <td>
      <div style="color:red" v-if="item.quantityOld">Было: {{item.quantityOld}}</div>
      <input class="checkout_text quantity" type="number" v-model="item.quantity" min="1" max="1000" @change="$emit('editCount',item);" :style="item.quantity>item.quantityAll?'color:red;':''">
    </td>
    <!-- Акция -->
    <td>{{stockPrice?"-"+(((price-stockPrice)*100)/price).toFixed(2)+"%":'нет'}}</td>
    <!-- Дисконт кол-во -->
    <td>{{stockPrice || item.discountBound==2000000?'нет':item.discountBound}}</td>
    <!-- Дисконт цена -->
    <td>{{discountPrice?discountPrice:'' | currencydecimal}}</td>
    <!-- Остаток -->
    <td>{{item.quantityAll}}</td>
    <!-- Сумма -->
    <td>{{actualPrice * item.quantity | currencydecimal}}</td>
    <td v-if="item.prepayment > 0">
      <span class="blink" v-if="item.prepayment > 0">Товар по предоплате</span>
    </td>
    <td>
      <div v-if="(Math.abs(item.quantity-item.quantity_base)>errorRate/1000)&&item.weightId&&item.weightId[0]!=0" style="color:red;">
        Не совпадает с базовым кол-вом на {{ (Math.abs(item.quantity-item.quantity_base) - errorRate/1000).toFixed(3) }} с учетом погрешности {{errorRate}} грамм
      </div>
    </td>
    <td>
      <span class="btn" @click="$emit('delete')">x</span>
    </td>
  </tr>
</template>

<script>
import BaseItemMixin from "../../../../mixins/base-item.js";

export default {
  mixins:[BaseItemMixin],
  props:{
    item:{
      type:Object,
      required:true
    },
    index:Number,
    course:{
			type:Number,
			required:true,
			default:0
		},
    showWarehouse:Boolean,
    shop_url:String,
    errorRate:Number
  },
}
</script>