<template>
  <textarea class="textbox"
		@input="resize(); $emit('input',$event.target.value);" 
		@change="resize(); $emit('change',$event)"
		@drop="resize();"
		:value="value"></textarea>
</template>

<script>
export default {
	name:'textbox',
	props:{
		value:String,
		autoresize:Boolean
	},
	mounted(){
		this.resize();
	},
	methods:{
		resize() {
			if(!this.autoresize)
				return;
			this.$el.style.height = 'auto';
			this.$el.style.height = this.$el.scrollHeight+'px';
		},
		focus(){
			this.$el.focus();
		}
	}
}
</script>

<style>
.textbox{
	resize: none;
}
</style>