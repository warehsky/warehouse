<template>
  <div class="SuggestView">
    <input
    :disabled="disabled"
    :class="inputClass" 
    id="adres"
    :placeholder="placeholder"
    autocomplete="disabled"
    ref="input"
    v-model="value"
    @focus.prevent="tryShow"
    @blur="select(items[selectedIndex],true)"
    @input="onInput"
    @keydown.up.prevent="onArrow(-1)"
    @keydown.down.prevent="onArrow(1)"
    @keydown.enter="(e)=>{ select(items[selectedIndex]) }">
    <div v-show="!hidden && !disabled && items.length" :class="dropdownClass" :style="{ 'display':'none' ,...cssVars }" id="dropdown1" ref="tip">
      <p
        :class="'item '+itemsClass+(selectedIndex==index?' marked':' unmarked')"
        v-for="(item,index) in items"
        :key="index"
        @mouseenter="outside = false"
        @mouseleave="outside = true"
        @mousemove="(e)=>{ selectedIndex = index; value = items[selectedIndex].output; }">
        {{item.output}}
      </p>
    </div>
  </div>
</template>

<script>
import { computeStyles, createPopper, left } from '@popperjs/core';
export default {
    name:'SuggestView',
    components: {
    },
    props:{
      inputClass:String,
      itemsClass:String,
      placeholder:String,
      disabled:[Boolean, String],
      dropdownClass:String,
      autowidth:{
        type:Boolean,
        default:false
      }
    },
    data(){
      return{
        items:[],
        hidden:true,
        selectedIndex:0,
        value:"",
        outside:true,
        itemsWidth:0
      }
    },
    mounted(){
      this.popper = createPopper(this.$refs.input,this.$refs.tip,{placement: 'bottom'});
    },
    methods:{
      onArrow(direction){
        if(this.disabled)
          return;
        let nextIndex = this.selectedIndex+direction;
        if(direction==1?nextIndex>=this.items.length:nextIndex<0)
          return;
        this.selectedIndex=this.selectedIndex+direction;
        this.value = this.items[this.selectedIndex].output;
      },
      setItems(items){
        if(!items)
          console.error("setItems param error: items can not be "+items+".");
        this.items = this.value?[...items]:[];
        this.tryShow();
      },
      update(){
        this.itemsWidth = this.$refs.input.getBoundingClientRect().width + 'px';
      },
      tryShow(){
        if(this.items.length == 0)
          return;
        if(this.autowidth)
          this.update();
        this.$nextTick(()=>{
          let oldItemIndex = this.items.findIndex(item=>item.output==this.value);
          this.selectedIndex = oldItemIndex>=0?oldItemIndex:0;
          this.popper.update();
        })
        this.hidden = false;
      },
      select(item,onBlur = false){
        if(this.disabled || this.hidden || this.items.length<1)
          return;
        else if(item.isInput){
          if(!onBlur || !this.outside)
            this.$nextTick(()=>{ this.$refs.input.focus(); });
          else
            this.hidden = true;
          return;
        }
        this.value = item.output;
        this.$emit('select',item);
        this.oldSelectedIndex = this.selectedIndex;
        this.hidden = true;
        if(!onBlur)
          this.$nextTick(()=>{ this.$refs.input.blur() });
      },
      onInput(e){
        if(typeof e.data == "string" && e.data.length>0 && e.data[e.data.length-1])
          this.$emit('input',e);
      },
      setText(value){
        this.items = [];
        this.value = value;
      },
    },
    computed:{
      cssVars(){
        return{
          "width":this.itemsWidth
        }
      }
    }
}
</script>

<style lang="scss">
#dropdown1{
  background-color: white;
  border: 1px solid #999;
  border-radius: 8px;
  box-shadow: 0 0 1px 1px rgb(0 0 0 / 0%);
  width: fit-content;
  z-index: 11;
  .item{
    text-align: center;
    border: 1px solid white;
    padding: 5px;
    border-radius: 8px;
    // &:first-child.marked{
    //   background-color: #8b8b8b;
    //   cursor: pointer;
    //   color: rgb(65, 65, 65);
    // }
    &.marked{
      background-color: #931515;
      cursor: pointer;
      color: white;
    }
    &.unmarked{
      cursor: pointer;
    }
  }
}
</style>