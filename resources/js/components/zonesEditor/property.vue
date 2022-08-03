<template>
  <div class="property">
      <div class="prop-name" :class="{ disabled:disabled || typeof value == 'object' }">{{name}}</div>
      <div @dblclick="$emit('dblclick',$event)">
        <input 
          :type="type"
          class="prop-value"
          :class="type"
          :disabled="isDisabled || typeof value == 'object'"
          :value="value"
          :checked="value"
          @input="$emit('input',type=='checkbox'?$event.target.checked:$event.target.value)"
          @change="$emit('change',$event)"
          :min="min"
          :max="max"
          :step="step">
        <input ref="colorInput"
          v-if="type=='color'"
          type="text"
          :value="value"
          @change="validColor($event); $emit('input',$event.target.value); $emit('change',$event)" maxlength="7"/>
        <input ref="rangeInput" class="range-input"
          v-if="type=='range'"
          type="number"
          :value="value"
          :min="min"
          :max="max"
          :step="step"
          @input="$emit('input',$event.target.value);"
          @change="$emit('change',$event)"/>
      </div>
  </div>
</template>

<script>
export default {
  name:"property",
  props:{
    name:String,
    value:[String,Object,Number,Boolean,Array],
    disabled:Boolean,
    range:Boolean,
    min:[Number,Object],
    max:[Number,Object],
    step:[Number,String]
  },
  data(){
    return{
      isDisabled:this.disabled,
      type:String
    }
  },
  methods:{
    getType(){
      switch(typeof this.value){
        case("string"):
          let match = this.value.match(/#\w{6}/)
          if(this.value.length==7 && match && match.length && match[0])
            return "color"
          if(this.value == "0" || Number(this.value))
            return this.range?"range":"number"
          return 'text'
        case("number"):return this.range?"range":"number"
        case("boolean"):return "checkbox"
        case("object"):return "text"
      }
    },
    update(){
      this.type = this.getType();
    },
    validColor(e){
      if(e.target.value[0]!="#")
        e.target.value = "#"+e.target.value.slice(e.target.value.length<7?0:1)
      if(e.target.value.length<7){
        let pos = e.target.value.length;
        e.target.value +="0".repeat(7-e.target.value.length)
        e.target.focus();
        e.target.setSelectionRange(pos,pos);
      }
      if(!/^#([0-9A-F]{3}){1,2}$/i.test(e.target.value))
        e.target.value = "#000000"
    },
  }
}
</script>

<style lang="scss">
.prop-name{
  &.disabled{
    color: #545454;
    cursor: default;
  }
}
.prop-value{
  &.range{
    max-width: 155px;
  }
  &.color{
    max-width: 50px;
  }
}
.property{
  display: flex;
}
.range-input{
  max-width: 90px;
}
</style>