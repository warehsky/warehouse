<template>
  <div :class="opened?'float-panel':'float-panel collapsed'" :style="cssVars">
    <slot></slot>
  </div>
</template>

<script>
export default {
  props:{
    opened:{
      type:Boolean,
      default:false
    },
    collapsedSize:{
      type:Object,
      default(){
        return{
          width:'0px',
          height:'0px'
        }
      }
    }
  },
  computed:{
    cssVars(){
      return {
        '--width':this.collapsedSize.width,
        '--height':this.collapsedSize.height
      }
    }
  }
}
</script>

<style lang="scss">
.float-panel{
  position: absolute;
  width: 100px;
  height: 100px;
  border: 1px solid;
  transition-property: width, height, border-width;
  background: white;
  overflow: hidden;
  &.collapsed{
    border-width: 0px;
    width: var(--width) !important;
    height: var(--height) !important;
  }
}
</style>