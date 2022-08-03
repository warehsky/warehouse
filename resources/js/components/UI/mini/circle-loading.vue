<template>
  <div class="circle-loading">
    <div class="loader" :style="cssVars"></div>
  </div>
</template>

<script>
export default {
	props:{
		ringWeight:{
			type:Number,
			default(){
				return 1;
			}
		},
		radius:{
			type:Number,
			default(){
				return 10;
			}
		}
	},
	computed:{
		cssVars(){
			return {
				"--ring-weight":this.ringWeight/6+"em",
				"--ring-radius":this.radius/8+"px"
			}
		}
	}
}
</script>

<style>
.circle-loading{
  width: min-content;
  height: min-content;
}
.loader,
.loader:after {
  border-radius: 80%;
  width: 10em;
  height: 10em;
}
.loader {
  font-size: var(--ring-radius);
  position: relative;
  text-indent: -9999em;
	border: var(--ring-weight) solid rgba(255, 255, 255, 0.2);
  border-left: var(--ring-weight) solid #ffffff;
  -webkit-transform: translateZ(0);
  -ms-transform: translateZ(0);
  transform: translateZ(0);
  -webkit-animation: load8 1.1s infinite linear;
  animation: load8 1.1s infinite linear;
}
@-webkit-keyframes load8 {
  0% {
    -webkit-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}
@keyframes load8 {
  0% {
    -webkit-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}
</style>