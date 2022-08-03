<template>
	<div>
		<button :class="buttonClass" @click="onclick"><slot></slot></button>
		<div v-show="false" ref="template"><slot name="template"></slot></div>
	</div>
</template>

<script>
export default {
	props:{
		element:{
			type:HTMLElement,
		},
		'button-class':String
	},
	methods:{
		async onclick(e){
			console.log(await this.$listeners.click?.(e));
			var mywindow = window.open('', 'PRINT', `height=${window.outerHeight},width=${window.outerWidth}`);
			var content = this.element?.innerHTML || this.$refs.template.innerHTML;
			mywindow.document.write(
				`<html>
					<head><title>${document.title}</title></head>
					<body>${content}</body>
				</html>`);

			mywindow.document.close(); // necessary for IE >= 10
			mywindow.focus(); // necessary for IE >= 10*/

			mywindow.print();
			mywindow.close();

			return true;
		}
	}
}
</script>

<style>

</style>