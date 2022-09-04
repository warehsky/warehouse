<template>
  <div :class="['switch-button',{ on:value }]" :style="{ '--radius':radius }" @click="$emit('input',!value);">
		<div class="trek">
			<div :class="['ball',{ on:value }]"></div>
		</div>
	</div>
</template>

<script>
export default {
	name:"switch-button",
	props:{
		value:[Boolean,Number],
		radius:{
			type:String,
			default:"15px"
		}
	}
}
</script>

<style lang="scss">
.switch-button{
	--radius:20px;
	--transition:all 0.2s ease-in;
	box-sizing: content-box;
  padding: 3px;
	box-shadow: inset 0px 0px 4px 0px #a2a2a2;
	height: var(--radius);
	width: calc(var(--radius) * 2);
	border-radius: var(--radius);
	background: white;
	transition: var(--transition);
	&.on{
		transition-delay: 0.1s;
		background: #931515;
	}
	.trek{
		box-sizing: content-box;
		width: 100%;
		.ball{
			margin-left: 0px;
			height: var(--radius);
			width: var(--radius);
			border-radius: var(--radius);
			background: #931515;
			transition: var(--transition);
			box-shadow: 0px 0px 2px 0px #931515;
			&:not(.on){
				transition-delay: 0.1s;
			}
			&.on{
				margin-left: calc(100% - var(--radius));
				background: white;
				box-shadow: 0px 0px 1px 1px #C0AEAE;
			}
		}
	}
}
</style>