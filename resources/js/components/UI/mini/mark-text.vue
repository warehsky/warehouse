<template>
  <span>
		<span v-if="from!=to && this.$slots.default.length==1 && (typeof this.$slots.default[0].text == 'string')">
				{{$slots.default[0].text.slice(0,fromI)}}<mark>{{$slots.default[0].text.slice(fromI,toI)}}</mark>{{$slots.default[0].text.slice(toI,$slots.default[0].text.length)}}
		</span>
		<span v-else>
			{{$slots.default[0].text}}
		</span>
  </span>
</template>

<script>
export default {
	props:{
		from:Number,
		to:Number
	},
	updated(){
		if(!this.updated){
			this.update();
			this.updated = true;
		} else this.updated = false;
	},
	data(){
		return{
			fromI:0,
			toI:0
		}
	},
	methods:{
		update(){
			this.fromI = Math.max(0,Math.min(this.from,this.$slots.default[0].text.length));
			this.toI = Math.max(0,Math.min(this.to,this.$slots.default[0].text.length));
			if(this.fromI!=this.from || this.toI!=this.to){
				this.fromI = 0;
				this.toI = 0;
			}
		}
	}
}
</script>

<style>

</style>