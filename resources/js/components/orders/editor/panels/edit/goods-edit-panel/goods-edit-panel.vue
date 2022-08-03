<template>
  <div class="goods-edit-panel inner-goods" :style="`height:${panelHeight}px`">
		<div><slot name="before"></slot></div>
		<div class="goods-table">
			<table border="1" ref="tblorders">
				<thead>
					<tr>
						<th>№</th>
						<th>ID</th>
						<th>Наименование</th>
						<th>Цена</th>
						<th v-if="showWarehouse">Баз/склад кол-во</th>
						<th v-if="!showWarehouse">Базовое кол-во</th>
						<th @click="showWarehouse=!showWarehouse">Кол-во</th>
						<th>Акция</th>
						<th>Дисконт кол-во</th>
						<th>Дисконт цена</th>
						<th>Остаток</th>
						<th>Сумма</th>
						<th>Оповещение</th>
						<th v-if="items.contains(i=>i.prepayment>0)"></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<template v-for="(item, index) in items">
						<good :item="item" :key="item.id"
							:index="index"
							:course="course"
							:errorRate="errorRate"
							:shop_url="shop_url"
							:showWarehouse="showWarehouse"
							@delete="deleteItem(index)"
							@editCount="editCount">
							<template #actions><slot name="actions" :item="item"></slot></template>
						</good>
					</template>
				</tbody>
			</table>
		</div>
		<!-- модальное окно добавления товара -->
		<goods-select
			:course="course"
			:items="items"
			:shop_url="shop_url"
			@change="changeGoods($event); $emit('goods-changed');"
			@close="$emit('goods-changed');">
		</goods-select>
	</div>
</template>

<script>
import OrderItem from '../../../../orderItem.js';
import Good from './good.vue';
import GoodsSelect from '../../../inputs/goods-select.vue';

export default {
	components:{
		Good, GoodsSelect
	},
	props:{
		panelHeight:Number,
		updatedMessage:[String, Number],
		items:{
			type:Array,
			required:true
		},
		shop_url:{
			type:String,
			required:true
		},
		errorRate:{
			type:Number,
			required:true
		},
		course:{
			type:Number,
			required:true,
			default:0
		}
	},
	data(){
		return{
			showWarehouse:false,
			modalOpened:{
        goods:false,
      },
		}
	},
	methods:{
		editCount(item){
			if(!item.quantity || item.quantity<=0)
				item.quantity=1;
			if(item.quantity>1000)
				item.quantity=1000;
			if(item.stockPrice){ this.$emit("count-edited",item); return; }
			if(item.discountBound>0 && item.discountBound<2000000 && item.quantity>=item.discountBound && item.discountPrice)
				item.courier = item.discountPrice;
			else
				item.courier = item.price;
			this.$emit("count-edited",item);
		},
		deleteItem(index){
      if(!confirm("Подтвердите удаление товара [#"+this.items[index].itemId+"]"+this.items[index].title+" !"))
        return;
  		this.items.splice(index, 1);
      // this.update();
			this.$emit("item-deleted",index);
    },
		changeGoods(selectedItems){
      let toAdd = selectedItems.except(this.items,"itemId","itemId");
      let toRemove = this.items.except(selectedItems,"itemId","itemId");
      let toChange = Array.getMatched(this.items, selectedItems,"itemId","itemId");
      toRemove.forEach((item) => {
        this.items.remove(item);
      });
      toChange.forEach((match)=>{
        match.first.quantity = match.second.quantity;
        match.first.courier = match.second.courier;
      })
      toAdd.forEach((selectedItem)=>{
        this.items.push(new OrderItem(selectedItem));
      });
    },
	}
}
</script>
<style lang="scss">
.goods-edit-panel{
	.goods-table table{
		position: sticky;
		top: 0px;
		border-collapse: initial;
		border-spacing: 0px;
		th{
			background: white;
			position: sticky;
			top: 0px;
		}
	}
}
</style>