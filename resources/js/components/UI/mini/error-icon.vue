<template>
  <div class="error-indicator" @click="$emit('click',$event)" :style="`transform:scale(${scale});`"><slot>!</slot></div>
</template>
<script>
export default {
  props:['scale']
}
</script>
<style>
.error-indicator{
  position: relative;
  right: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #c4c4c461;
  border-radius: 100px;
  min-width: 18px;
  height: 18px;
  background: #ff0000b5;
  color: white;
  font-size: smaller;
}
</style>