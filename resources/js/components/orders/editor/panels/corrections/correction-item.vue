<template>
  <tr :class="['correction-item',
    { 
      weight:item.weightId,
      parentId:item.parentId==179,
      scaned:item.scaned,
      remove:!Number(item.quantity),
      changed:item.quantity!=item.quantityOld,
      new:!Number(item.quantityOld)
    }]">
    <td>{{index+1}}</td>
    <!-- ID -->
    <td>{{item.itemId}}</td>
    <!-- Наименование -->
    <td><a :href="shop_url+'single?id='+item.itemId" title="карточка товара" target="_blank">
      {{item.title}}
    </a></td>
    <!-- Цена -->
    <td>{{ actualPrice | currencydecimal}}</td>
    <!-- Кол-во -->
    <td>
      <input class="checkout_text quantity" type="number" v-model="item.quantity" min="0" max="1000" @change="$emit('editCount',item);" :style="item.quantity>item.quantityAll?'color:red;':''">
      <b class="old-quantity">Было: {{item.quantityOld}} <button @click="resetQuantity" :disabled="item.quantityOld == item.quantity">Сбросить</button></b>
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
  </tr>
</template>

<script>
import BaseItemMixin from "../../../mixins/base-item.js";

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
    shop_url:String,
    errorRate:Number
  },
  data(){
    return {
      remove:false
    }
  },
  methods:{
    resetQuantity(){
      this.item.quantity = this.item.quantityOld;
    }
  }
}
</script>
<style lang="scss">
.correction-item{
  &.changed{
    background: #ffce92;
    &.new{
      background: rgb(197, 255, 192);
    }
    &.remove{
      background: pink;
    }
  }
  .old-quantity{
    display: inline-block;
    padding: 2px;
    background: #cfcfcf;
    border-radius: 4px;
  }
  .quantity{
    padding: 3px;
    width: fit-content;
  }
}
</style>