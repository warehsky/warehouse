<template>
	<div class="editor-property">
		<label>{{ name }}</label>
		<div ref="controlls" class="editor-property-controlls">
			<slot>
				<input 
					:class="iclass"
					:type="type"
					:value="value"
					:min="min"
					:max="max"
					:disabled="disabled"
					@input="$emit('input',$event.target.value)" @change="$emit('change',$event)" />
			</slot>
		</div>
	</div>
</template>

<script>
export default {
	props:{
		name:String,
		value:[Number,String],
		type:String,
		iclass:[String,Array,Object],
		min:[String,Number],
		max:[String,Number],
		disabled:[Boolean,String,Number]
	},
	watch:{
		disabled(){
			this.update();
		}
	},
	mounted(){
		this.update();
	},
	methods:{
		update(){
			this.setDisabledRecursivly(this.$refs.controlls,this.disabled);
		},
		setDisabledRecursivly(parent,value){
			if(!parent.children.length) return;
			for(let element of parent.children){
				if('disabled' in element) element.disabled = value;
				else this.setDisabledRecursivly(element,value);
			}
		}
	}
}
</script>

<style lang="scss">
.editor-property{
	display: flex;
	justify-content: space-between;
	.editor-property-controlls{
		display: flex;
		flex-wrap: nowrap;
		input,select{
			height: 30px;
		}
	}
}
</style>