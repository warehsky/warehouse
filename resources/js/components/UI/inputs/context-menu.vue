<template>
  <div class="context-menu" :class="{ opened }" :style="{top,left}">
		<div v-if="title">{{title}}</div>
		<div class="context-actions">
			<slot></slot>
		</div>
  </div>
</template>

<script>
export default {
	name:"context-menu",
	props:{
		title:String,
		groups:Array
	},
	data(){
		return{
			opened:false,
			top:"0px",
			left:"0px",
			detaild:null
		}
	},
	beforeMount(){
		document.addEventListener("mousewheel",this.close);
	},
	methods:{
		open([left ,top],detaild){
			let open = !this.opened;
			this.opened = true;
			this.top = top+"px";
			this.left = left+"px";
			this.detaild = detaild;
			if(open) this.$nextTick(()=>document.addEventListener("mouseup",this.close));
		},
		close(){
			this.opened = false;
			document.removeEventListener("mouseup",this.close);
		}
	}
}
</script>

<style lang="scss">
.context-menu{
	&:not(.opened){
		display: none;
	}
	position: fixed;
    background: #F2F2F2;
    padding: 2px 0;
    z-index: 99999999;
    border: 1px solid #CCCCCC;
	.context-actions>div{
		display: flex;
		align-items: center;
		-webkit-user-select: none;
		-moz-user-select: none;
		-ms-user-select: none;
		user-select: none;
		padding: 3px 8px;
		color: darkslategray;
		&:hover{
			background: #90BFE4;
		}
	}
}
</style>