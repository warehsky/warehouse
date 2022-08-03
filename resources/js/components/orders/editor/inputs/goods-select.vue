<template>
	<div class="goods-select">
		<button @click="opened = true; $refs.goodsChoose.resetState()">
			<slot>Добавить</slot>
		</button>
		<simple-modal v-show="opened" class="goods-modal" >
			<div class="goods-modal-contents">
				<div>
					<h2 v-if="goodsChooseState.currentPage<=0" class="modalHeaderItem">Группы товаров</h2>
					<div v-else class="modalHeader">
						<h3>{{goodsChooseState.currentSubgroupTitle}}</h3>
						<button class="backButton" @click="$refs.goodsChoose.closeTable();">Назад</button>
					</div>
				</div>
				<div class="goods-choose-container">
					<add-goods-modal ref="goodsChoose"
						:shop_url="shop_url"
						:defaultItems="opened?items:[]"
						:showTableTitle="false"
						:course="course"
						@changeContent="goodsChooseState = $event">
					</add-goods-modal>
				</div>
				<div>
					<button class="btn" @click="save">Сохранить</button>
					<button class="btn" @click="close">Выход</button>
				</div>
			</div>
		</simple-modal>
	</div>
</template>

<script>
import SimpleModal from '../../../zonesEditor/simple-modal.vue';
import AddGoodsModal from '../add-goods-modal/add-goods-modal.vue';

export default {
	components: { SimpleModal, AddGoodsModal },
	props:{
		shop_url:{
			type:String,
			required:true
		},
		items:{
			type:Array,
			default(){ return [] }
		},
		course:{
			type:Number,
			required:true
		}
	},
	data(){
		return {
			opened:false,
			goodsChooseState:{
        currentPage:0,
        currentSubgroupTitle:''
      },
		}
	},
	methods:{
		save(){
			this.$emit('change',this.$refs.goodsChoose.selectedItems);
			this.close();
		},
		close(){
			this.$emit('close');
			this.opened = false;
		}
	}
}
</script>

<style lang="scss">
.goods-select{
	.goods-modal{
		.modal-contents{
			border-radius: 0px;
    		background: transparent;
			min-width: 50%;
			width: 100%;
			height: 100%;
			// max-height: 100%;
			// max-width: 100%;
			// min-height: 95%;
			// min-width: 95%;
			padding: 10px;
			.goods-modal-contents{
				display: flex;
				flex-direction: column;
				max-height: 100%;
				width: 100%;
				padding: 10px;
				border: 1px solid #bfbfbf;
				border-radius: 8px;
				background: #ebebeb;
				.goods-choose-container{
					height: 100%;
					overflow-y: auto;
				}
			}
		}
	}
}
</style>