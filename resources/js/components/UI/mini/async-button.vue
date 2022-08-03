<template>
  <button @click="onClick" :class="['async-button',stateName]">
		<template v-if="state == states.default"><slot :state="slotState" :stateName="stateName"></slot></template>
		<template v-else-if="state == states.wait">
			<slot :name="overrided['wait']" :state="slotState" :stateName="stateName"><circle-loading class="ab-circle-loading" :radius="20" :ringWeight="10"></circle-loading></slot>
		</template>
		<template v-else><slot :name="overrided['error']" :state="slotState" :stateName="stateName">Повторить</slot></template>
  </button>
</template>

<script>
import CircleLoading from './circle-loading.vue';
export default {
	emits:["then","catch", "finally"],
	components:{
		CircleLoading
	},
	props:{
		lock:{ default:true },
		promise:Promise,
		handle:{ default:()=>[] }//имя того(или имена тех) слотов, которые будут заменены на "default"
	},
	data(){
		return {
			state:0,
			states:{
				error:-1,
				default:0,
				wait:1
			}
		}
	},
	mounted(){
		if(this.promise){
			this.state = this.states.wait;
			this.promise
				.then(e=>{ this.$emit("then",e); this.state = this.states.default; })
				.catch(e=>{ this.$emit("catch",e); this.state = this.states.error; })
				.finally(e=>this.$emit("finally",e));
		}
	},
	computed:{
		slotState(){
			return Object.fromEntries(Object.entries(this.states).map(([stateName])=>[stateName,stateName==this.stateName]));
		},
		stateName(){
			return Object.entries(this.states).find(e=>e[1]==this.state)?.[0];// "?." - if not undefined
		},
		overrided(){
			let slotNames = Object.entries(this.states).map(e=>e[0]);
			let result = Object.fromEntries(slotNames.map(n=>[n,n]));
			if(!this.handle) return result;
			switch(this.handle.constructor.name){
				case('String'):
					if(result[this.handle]) result[this.handle] = 'default';
					debugger
					break;
				case('Array'):
					this.handle.forEach((name)=>{
						if(result[name]) result[name] = 'default';
					})
				case('Object'):
					Object.entries(this.handle).forEach(([name, value])=>{
						if(value && result[name]) result[name] = 'default';
					})
				break;
			}
			return result;
		}
	},
	watch:{
		stateName(){
			if(this.oldStateName ==this.stateName) return;
			this.oldStateName = this.stateName
			this.$emit(this.stateName, this.result);
			this.$emit("change", { state:this.stateName, result:this.result });
		}
	},
	methods:{
		async onClick(){
			if(this.lock && this.state == this.states.wait) return;
			let eventHandler = this.$listeners['click'];
			if(typeof eventHandler != 'function') return;
			this.state = this.states.wait;
			try{
				this.result = await eventHandler();
				this.state = this.states.default;
			} catch {
				this.state = this.states.error;
			}
		},
	}
}
</script>

<style lang="scss">
.async-button{
	.ab-circle-loading{
		margin: auto;
	}
}
</style>