<template>
  <div :class="['short-select',{ disabled }]">
		<div ref="select" tabindex="0" style="user-select:none;" :class="'sselect '+selectclass" @focus="focus" @blur="blur" @click.prevent="onclick">
			<span v-if="selectedItem && items.length>0">
				<slot :item="selectedItem" :value="selectedItem.value" :isOption="false">{{selectedItem.short?selectedItem.short:selectedItem.value}}</slot>
			</span>
			<div class="ss-arrow">
				<img src="/img/icons/arrow.svg" :class="['select-arrow',{ 'rotate': isFocused }]">
			</div>
		</div>
		<div :class="'items '+panelclass" :style="panelStyle">
			<div v-for="(item,index) in items" :key="index"
				:class="['ss-option', { 'selected':markedIndex == index }, { 'disabled':item.disabled }, optionclass]"
				:style="optionStyle"
				@mouseenter="item.disabled?null:mark(index);"
				@mousedown="item.disabled?null:select(index); mark(index);">
				<slot :name="'option'+index" :item="item" :value="item.value">
					<slot name="options" :item="item" :value="item.value">
						<slot :item="item" :value="item.value" :isOption="true">
							{{item.value}}
						</slot>
					</slot>
				</slot>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	props:{
		items:{
			type:Array,
			default(){
				return [];
			}
		},
		selectclass:{
			type:String,
			default:""
		},
		optionclass:{
			type:[String,Object,Array],
			default:""
		},
		panelStyle:{
			type:[String,Object,Array],
			default:""
		},
		optionStyle:{
			type:[String,Object,Array],
			default:""
		},
		panelclass:{
			type:String,
			default:""
		},
		value:{
			type:[String,Number,Object],
			default:""
		},
		// приоритетное свойство
		priority:{
			type:String,
			default:'value'
		},
		disabled:Boolean
	},
	watch:{
		value:function(){ this.update() },
		items:function(){ this.update() },
		isFocused:function(){}
	},
	data(){
		return{
			selectedItem:0,
			selectedIndex:this.selected,
			markedIndex:this.selected,
			isFocused:false
		}
	},
	mounted(){
		this.update(true);
		this.$emit("init",this.selectedItem);
	},
	methods:{
		update(withoutEmit = false){
			let indexByValue = this.items.indexOfBy(item=>this.getItemValue(item) == this.value && !item.disabled);
			if(indexByValue<0) indexByValue = this.items.indexOfBy(item=>!item.disabled);
			this.select(indexByValue,withoutEmit);
		},
		getItemValue(item){
			let priority = item.priority || this.priority;
			return item.hasOwnProperty(priority)?item[priority]:item.value;
		},
		focus(e){
			this.mark(this.selectedIndex);
			addEventListener("scroll",this.scrollHandler);
			document.addEventListener("keydown", this.keyHendler);
		},
		blur(e){
			removeEventListener("scroll",this.scrollHandler);
			document.removeEventListener("keydown", this.keyHendler);
			this.$refs.select.blur();
			this.isFocused = false;
		},
		onclick(){
			if(this.isFocused) this.blur();
			else this.isFocused = true;
		},
		scrollHandler(e){
			this.blur();
		},
		keyHendler(e){
			e.preventDefault();
			if(e.key == "ArrowUp") this.mark(this.markedIndex-1)
			else if(e.key == "ArrowDown") this.mark(this.markedIndex+1);
			else if(e.key == "Enter"){ this.select(this.markedIndex); e.target.blur(); }
			else if(e.key == "Escape"){ e.target.blur(); e.target.blur(); }
		},
		// selectAvailable(){
		// 	this.select(this.items.indexOfBy(item=>!item.disabled));
		// },
		select(index,withoutEmit = false){
			this.selectedIndex = index;
			this.selectedItem = index>=0?this.items[this.selectedIndex]:{ value:"", short:"" }
			if(!withoutEmit){
				let value = this.getItemValue(this.selectedItem);
				this.$emit('input',value);
				this.$emit('change',this.selectedItem);
			}
		},
		mark(index){
			let lastIndex = this.items.length-1
			if(index<0)
				this.markedIndex = lastIndex;
			else if(index>lastIndex)
				this.markedIndex = 0;
			else this.markedIndex = index;
		}
	}
}
</script>

<style lang="scss">
.short-select{
	position: relative;
	cursor: pointer;
	&.disabled{
		pointer-events: none;
		.sselect{
			color: #A0A0A0;
		}
		.ss-arrow img{
			filter: contrast(0) brightness(1.2);
		}
	}
}
.sselect{
	border: 1px solid;
	border-radius: 1px;
	background: white;
	display: flex;
	justify-content: space-between;
	&:not(:focus){
		& + .items{
			display: none;
		}
	}
}
.ss-arrow{
	margin-left: 10px;
	display: flex;
}
.select-arrow{
	transform: rotateZ(180deg);
	max-width: 100%;
	&.rotate {
		transform: rotateZ(360deg);
	}
}
.short-select .items{
	min-width: 100%;
	// width: max-content;
	border: 1px solid black;
	position: absolute;
	background: white;
	z-index: 1;
	.ss-option{
		cursor: pointer;
		padding: 1px 2px;
		margin: 3px 0;
		padding: 1px 10px;
		&.selected,
		&:hover{
			background: #1E90FF;
			color: white;
		}
		&.disabled{
			background: #cdcdcd;
    		color: #6b6b6b;
			cursor: default;
		}
	}
}

</style>