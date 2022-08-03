<template>
  <div class="progress-bar" :style="{ '--pb-width':this.value+'%' }">
    <div class="progress" :class="{ error }"></div>
    <div class="void"></div>
  </div>
</template>

<script>
export default {
    name:"progress-bar",
    props:{
        value:{
            type:Number,
            default:0
        },
        error:{
            type:Boolean,
            default:false
        }
    }
}
</script>

<style lang="scss">
.progress-bar{
    height: 10px;
    border: 1px solid #c7c7c7;
    .progress{
        background: green;
        width: var(--pb-width);
        height: 100%;
        &.error{
            background: red;
        }
    }
    .void{
        background: transparent;
        width: 100%;
        height: 100%;
    }
}
</style>