<template>
  <select :value="value" @change="(e)=>{ $emit('select',{DOMEventData:e, option: options[e.target.selectedIndex]}); $emit('change',e.target.value)}">
      <slot></slot>
  </select>
</template>
<script>
export default {
    name:'associative-select',
    model:{
        prop:"value",
        event:"change"
    },
    props:{
        value:[Object,String,Array,Boolean,Number],
        options:Array
    }
}
</script>