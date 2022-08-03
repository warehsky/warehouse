<template>
  <fieldset>
    <slot></slot>
  </fieldset>
</template>

<script>
export default {
	mounted(){
		let check = ()=>{
			for(let i = 0;i<this.$slots.default.length;i++){
				let node = this.$slots.default[i];
				// console.log(node.tag+'!="legend"','"'+(node.text||"").trim()+'"');
				if(node.tag!="legend" && node.tag || (node.text||"").trim().length>0){
					this.$emit("empty",{ component:this,empty:false })
					return;
				}
			}
			this.$emit("empty",{ component:this,empty:true })
		}
		check();
		this.mutationObserver = new MutationObserver(check);
		this.mutationObserver.observe(this.$el,{ childList: true });
	},
}
</script>

<style>

</style>