<template>
  <div class="vertical-list">
		<div 
			ref="vlItem"
			v-for="(item, index) in items" :key="index"
			class="v-list-item"
			:style="canSelect?'cursor:pointer':'cursor:default'"
			@click="selectItem($event.target,item,index)">
			<span ref="contents" v-if="edit != index" class="v-list-contents">{{item.text}}</span>
			<input ref="inputChange" v-if="canEdit" v-show="edit == index" class="chat-input" v-model="change" @keydown.esc="cancelEdit();" @keydown.enter="saveEdition(index)">
			<div class="v-list-buttons">
				<div :class="buttonsClass?buttonsClass:'v-list-button'" v-if="canEdit && item.editable && edit != index"
					@click="editItem(item,index);">
					<slot name="button-edit" :index="index">
						Изменить
					</slot>
				</div>
				<div v-if="edit == index" class="v-list-edit-buttons">
					<div :class="buttonsClass?buttonsClass:'v-list-button'" v-if="change != item.text"
						@click="saveEdition(index)">
						<slot name="button-save" :index="index">
							Сохранить
						</slot>
					</div>
					<div :class="buttonsClass?buttonsClass:'v-list-button'" @click="cancelEdit">Отмена</div>
				</div>
				<div :class="buttonsClass?buttonsClass:'v-list-button'" v-if="edit != index && canRemove && item.removable" @click="cancelEdit(); $emit('remove',{ index:index })">
					<slot name="button-remove" :index="index">Удалить</slot>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	name:"vertical-list",
	props:{
		items:Array,
		canEdit:Boolean,
		canRemove:Boolean,
		canSelect:Boolean,
		buttonsClass:String
	},
	methods:{
		editItem(item,index){
			this.change = item.original?item.original:item.text;
			this.edit = index;
			this.$nextTick(() => {
				this.$refs.inputChange[index].focus();
			});
		},
		saveEdition(index){
			this.$emit('edit',
			{ 
				original:this.change,
				index:index,
				finish:()=>{ 
					this.edit = -1;
					this.change = '';
				} 
			});
		},
		cancelEdit(){
			this.edit = -1;
			this.change = '';
		},
		selectItem(target,item,index){
			if(this.canSelect && this.edit<0 && (target == this.$refs.vlItem[index] || target == this.$refs.contents[index]))
				this.$emit('select',{ text:item.text, index:index });
		}
	},
	updated(){
		this.$emit('change');
	},
	data(){
		return {
			edit:-1,
			change:""
		}
	},
}
</script>

<style lang="scss">
.vertical-list{
	display: flex;
	flex-direction: column;
	overflow-y: scroll;
	.v-list-buttons, .v-list-edit-buttons{
		display: flex;
	}
	.v-list-button{
		display: flex;
    justify-content: center;
    align-items: center;
    padding: 0 25px;
		margin: 0 5px;
    height: 50px;
    min-width: 50px;
    border-radius: 50px;
    background-color: #eaeaf7;
    cursor: default;
    user-select: none;
		&:hover{
			filter: contrast(0.9);
      cursor: pointer;
		}
	}
	.v-list-item{
		display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 15px;
    width: calc(100% - 20px);
    margin: 3px;
    padding: 5px;
    border: 1px solid #00000029;
		&:hover{
			box-shadow: 0px 0px 3px 0px #5f5d5d;
		}
		.v-list-contents{
			margin: 10px;
			cursor: inherit;
			user-select: none;
		}
	}
}
</style>