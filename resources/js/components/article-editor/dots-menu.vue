<template>
	<div class="dm-root" :style="cssVars">
		<div ref="head" :class="'dm-head '+headClass" @click="isOpened = !isOpened; $emit('toggle', isOpened)">
			<slot name="dm-head"></slot>
		</div>
		<div ref="dropdown" :class="bodyClass+' dm-body'">
			<slot name="dm-body"></slot>
		</div>
	</div>
</template>

<script>
import { createPopper, hide, left } from '@popperjs/core';
export default {
	name:'dm',
	props:{
		content:{
			type:[String],
			default(){
				return "\"\\2807\""
			}
		},
		time:{
			type:Number,
			default(){
				return 0.5;
			}
		},
		parentId:{
			type:String,
			default(){
				return null;
			}
		},
		transitionType:{
			type:String,
			default(){
				return 'opacity'
			}
		},
		width:{
			type:String,
			default(){
				return ''
			}
		},
		parentWidth:Boolean,
		headClass:{
			type:String,
			default(){
				return '';
			}
		},
		bodyClass:{
			type:String,
			default(){
				return '';
			}
		},
		quickClose:{
			type:Boolean,
			default(){
				return true;
			}
		},
		closeOnScroll:{
			type:Boolean,
			default(){
				return true;
			}
		},
		disableStyles:Boolean
	},
	data(){
		return{
			isOpened:false,
			opacity:0,
			visibility:'hidden',
			maxHeight:0
		}
	},
	watch:{
		isOpened:function(){
			if(this.isOpened){
				this.popper.update();
				this.maxHeight = this.getTargetHeight();
				this.visibility = 'visibile';
				this.opacity = 1;
			}
			else{
				this.popper.update();
				this.maxHeight = 0;
				this.opacity = 0;
				this.$refs.dropdown.addEventListener('transitionend',()=>{
				this.visibility = 'hidden';
			},{once:true});
			}
		}
	},
	mounted(){
		this.parent = document.getElementById(this.parentId);
		if(this.parentId)
			this.popper = createPopper(this.parent,this.$refs.dropdown);
		else
			this.popper = createPopper(this.$refs.head,this.$refs.dropdown);
		if(this.quickClose)
			document.addEventListener('mousedown',(e)=>{
				if(!this.isOpened)
					return;
				let onthis = false;
				for(let i=0;i<e.path.length;i++){
					if(this.$el == e.path[i]){
						onthis = true;
						break;
					}
				}
				if(!onthis)
					this.isOpened = false;
			});
		if(this.closeOnScroll)
			document.addEventListener('scroll',()=>{
				this.isOpened = false;
			});
		window.addEventListener("resize", ()=>{
			this.isOpened = false;
		})
	},
	methods:{
		toggle(){
			this.isOpened = !this.isOpened;
		},
		getTargetHeight(){
			return this.$refs.dropdown.scrollHeight + this.$refs.dropdown.getBoundingClientRect().height;
		}
	},
	computed:{
		cssVars(){
			return{
				'--time':this.time+'s',
				'--visibility':this.visibility,
				'--opacity':this.transitionType == 'opacity'?this.opacity:1,
				'--max-height':this.transitionType == 'max-height'?this.maxHeight:'',
				'--transition-type':this.transitionType,
				'--width':this.parentWidth?this.parent?getComputedStyle(this.parent).width:this.width:this.width,
				'--content':this.content
			}
		},
	}
}
</script>

<style lang="scss">
.dm-head{
	&:after {
		content: var(--content);
		font-size: 20px;
  }
}
.dm-body{
	overflow: hidden;
	position: absolute;
	width: var(--width);
	opacity: var(--opacity);
	max-height: var(--max-height);
	transition: var(--transition-type) var(--time) linear;
	visibility: var(--visibility);
	z-index: 10;
}
</style>