<template>
  <div class="dropdownMenu">
    <div :class="headClass" ref="head" @click="toggleByClick?toggle():()=>{}">
      <slot name="d-head">
        <span v-if="!hideExample">DROPDOWN FACE</span>
      </slot>
    </div>
		<div ref="dropdown" :style="cssVars" :class="isOpened?'dropdown opened':'dropdown closed'">
      <div ref="dropdownContent" id="dropdownContent" :class="bodyClass">
        <slot name="d-body">
          <p v-if="!hideExample">DROPDOWN ITEM</p>
          <p v-if="!hideExample">DROPDOWN ITEM</p>
        </slot>
      </div>
    </div>
	</div>
</template>

<script>
export default {
  name:'dropdown-menu',
  props:{
    value:Boolean,
    time:{
      type:Number,
      default(){
        return 0.6;
      }
    },
    headClass:{
      type:String,
      default(){
        return '';
      }
    },
    bodyClass:{
      type:String,
      default(){
        return '';
      }
    },
    toggleByClick:Boolean,
    hideExample:{
      type:Boolean,
      default:false
    }
  },
  watch:{
    value:function(){
      this.toggle(true);
      if(this.isBrother)
        this.updateParent();
    }
  },
  data(){
    return{
      height:0,
      isOpened:false,
    }
  },
  mounted(){
    this.toggle(true);
    this.isBrother = this.$parent.$options.name == this.$options.name;
  },
  methods:{
    toggle(byProp = false){
      this.isOpened = byProp?this.value:!this.isOpened;
      this.$emit('toggle',this.isOpened);
      this.$refs.dropdown.addEventListener('transitionend',()=>{this.$emit(this.isOpened?'opened':'closed')},  {once:true });
      this.update();
    },
    getTargetHeight(){
      return this.$refs.dropdownContent.getBoundingClientRect().height;
    },
    update(){
      this.height = this.getTargetHeight();
    },
    updateParent(){
      if(this.isOpened){//при открытии получаем целевую высоту родителя
        this.parentHeight = this.$parent.getTargetHeight();
        this.$parent.height = this.parentHeight+this.height;//обновляем его высоту с учетом открытого дочернего о.
      }
      else
        this.$refs.dropdown.addEventListener('transitionend',()=>{this.$parent.update();},  {once:true });//возвращаем как было, если/когда закрывается
    }
  },
  computed: {
    cssVars() {
      return {
        '--height': this.height + 'px',
        '--time': this.time + 's'
      }
    }
  }
}
</script>

<style lang="scss">
.dropdownMenu{
  height: max-content;
  margin-left: 10px;
  margin-right: 10px;
  // margin-bottom: 10px;
}
.dropdown{
  overflow: hidden;
  transition: max-height var(--time) ease-in-out;
}
#dropdownContent{
  border: 1px solid rgba(255, 255, 255, 0);
}
.opened{
  max-height: var(--height);
}
.closed{
  max-height: 0px;
}
</style>