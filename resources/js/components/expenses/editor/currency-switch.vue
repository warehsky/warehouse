<template>
	<div class="currency">
		RUB<switch-button ref="switch" class="currency-switch" v-model="$root.UAH" />UAH
	</div>
</template>

<script>
import SwitchButton from './switch-button.vue';
export default {
	components:{
		SwitchButton
	}
}
</script>

<style lang="scss">
.currency{
	display: inline-flex;
	align-items: center;
	.currency-switch{
		margin: 0 2px;
	}
}
</style>