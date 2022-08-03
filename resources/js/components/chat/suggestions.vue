<template>
  <div class="chat-suggestions">
    <div v-for="(suggestion,index) in suggestions" :key="index" @click="$emit('select',suggestion)"
      class="chat-suggestion">
      {{suggestion}}
    </div>
  </div>
</template>

<script>
export default {
  props:{
    suggestions:Array
  }
}
</script>

<style>
.chat-suggestions{
  display: flex;
  padding: 10px;
  padding-bottom: 15px;
  justify-content: center;
  flex-wrap: wrap;
  height: max-content;
  max-height: 100px;
}
.chat-suggestion{
  width: max-content;
  border: 1px solid;
  color: #0086ad;
  border-radius: 100px;
  padding: 0 13px;
  cursor: pointer;
  margin: 5px;
}
</style>