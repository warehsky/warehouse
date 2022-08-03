<template>
  <div class="custom-panel" :style="style">
    <div ref="content" class="p-content" :class="contentClass">
      <slot></slot>
    </div>
    <!--top-->
    <div class="p-borders-list">
      <template v-for="side in sides.entries()">
        <border v-if="isGlobalPivot || pivot.includes(oppositeSides[side[0]])" :key="side[0]"
          :class="['panel-border',side[1]]"
          :height="bordersBounds[side[1]].height | px"
          :width="bordersBounds[side[1]].width | px"
          :top="(bordersBounds[side[0]].top || 0) | px"
          :left="(bordersBounds[side[0]].left || 0) | px"
          @dragstart="()=>{ dragstart(side[0]) }"
          @dragend="dragend">
        </border>
      </template>
    </div>
    <v-style type="text/css" v-if="curBorder">
      body *{
        cursor: {{this.sides.get(curBorder)=='h'?'ew-resize':'ns-resize'}} !important;
      }
    </v-style>
  </div>
</template>

<script>
import border from './border.vue'
export default {
  components: { border },
  name:'panel',
  props:{
    pivot:{
      type:[Array,String],
      default(){
        return ['float', 'center','top','left','bottom','right'];
      }
    },
    bordersHeight:{
      type:Number,
      default:10
    },
    magnitHeight:{
      type:Number,
      default:30
    },
    magnitWidth:{
      type:Number,
      default:30
    },
    width:{
      type:Number,
      default(){
        return -1;
      }
    },
    height:{
      type:Number,
      default(){
        return -1;
      }
    },
    maxWidth:{
      type:Number,
      default(){
        return -1;//-1 - none
      }
    },
    minWidth:{
      type:Number,
      default(){
        return -1;//-1 - none
      }
    },
    maxHeight:{
      type:Number,
      default(){
        return -1;//-1 - none
      }
    },
    minHeight:{
      type:Number,
      default(){
        return -1;//-1 - none
      }
    },
    contentClass:{
      type:String,
      default:""
    }
  },
  computed:{
    minBounds(){ return {
      v:this.minHeight,
      h:this.minWidth
    }},
    maxBounds(){ return {
      v:this.maxHeight,
      h:this.maxWidth
    }},
    magnitBounds(){ return {
      v:this.magnitWidth,
      h:this.magnitHeight
    }},
    bordersBounds(){ return {
      v:{ width:this.bounds['h']-this.bordersHeight/2, height:this.bordersHeight, },
      h:{ width:this.bordersHeight, height:this.bounds['v']-this.bordersHeight/2, },
      top:{ top:-this.bordersHeight/2 },
      left:{ left:-this.bordersHeight/2 },
      bottom:{ top:this.bounds['v']-this.bordersHeight/2 },
      right:{ left:this.bounds['h']-this.bordersHeight/2 }
    }},
    oppositeSides(){
      let mapData = [
        ['top','bottom'],
        ['left','right']
      ]
      return Object.fromEntries(mapData.concat(mapData.map(pair=>[...pair].reverse())));
    },
    isGlobalPivot(){
      return this.isCenterPivot || this.isFloatPivot;
    },
    isCenterPivot(){
      return this.pivot == "center" || this.pivot == ['center'];
    },
    isFloatPivot(){
      return this.pivot == "float" || this.pivot == ['float'];
    },
    style(){
      return{
        '--pl-borders-height':this.bordersHeight+"px",
        '--pl-width':this.bounds['h'] + "px",
        '--pl-height':this.bounds['v'] + "px",
        '--pl-z-index':this.zIndex
      }
    }
  },
  watch:{
    width:function(){ this.updateWidth(); },
    height:function(){ this.updateHeight(); },
    pivot:function(){
      if(!this.isGlobalPivot){
        if(this.pivot.length < 1 || this.pivot.length > 2){
          console.error("pivot consists of 1/2 items: top/bottom and left/right [pivot=\""+this.pivot+"\"]");
        }
        if(this.sides.get(this.pivot[0]) == this.sides.get(this.pivot[1]))
          console.error("values of pivot must have diferent sides");
      }
    },
    maxWidth:function(){ this.validateWidth() },
    minWidth:function(){ this.validateWidth() },
    maxHeight:function(){ this.validateHeight() },
    minHeight:function(){ this.validateHeight() }
  },
  data(){
    return{
      bounds:{
        v:100,
        h:100
      },
      zIndex:99999,
      curBorder:null,
      magnit:false,
      sides:new Map([
        ["top","v"],
        ["bottom","v"],
        ["left","h"],
        ["right","h"],
      ])
    }
  },
  mounted(){
    this.updateWidth();
    this.updateHeight();
    this.validateWidth();
    this.validateHeight();
    this.$nextTick(this.emitResize);
    window.addEventListener("resize", this.emitResize);
    this.resizeObserver = new ResizeObserver(()=>{ if(!this.curBorder) this.$nextTick(()=>this.emitResize()) });
    this.resizeObserver.observe(this.$el);
  },
  beforeDestroy(){
    window.removeEventListener("resize", this.emitResize);
    this.resizeObserver.unobserve(this.$el);
  },
  filters:{
    px:function px(value){
      return value+"px";
    }
  },
  methods:{
    updateWidth(){
      this.bounds['h'] = this.width+(this.bordersHeight/2);
    },
    updateHeight(){
      this.bounds['v'] = this.height+(this.bordersHeight/2);
    },
    emitResize(){
      this.$emit("resize",this.getRect());
    },
    getRect(){
      const rect = this.$refs.content.getBoundingClientRect();
      return {
        rect,
        side:this.curBorder,
      }
    },
    dragstart(side){
      this.curBorder = side;
      this.parentRect = this.$el.parentElement.getBoundingClientRect();
      window.addEventListener('mousemove',this.ondrag);
    },
    ondrag(e){
      let next = 0;
      let rect = this.$el.getBoundingClientRect();
      let direction = this.sides.get(this.curBorder);
      next = direction == 'v'?
        (this.curBorder == 'top'?rect.bottom-e.y:e.y-rect.top):
        (this.curBorder == 'left'?rect.right-e.x:e.x-rect.left);
      if(next<=this.magnitBounds[direction]) next = 0;
      let min = this.minBounds[direction]==-1?0:this.minBounds[direction];
      let max = this.maxBounds[direction]==-1?next:this.maxBounds[direction];
      let change = next != this.bounds[direction];
      this.bounds[direction] = Math.max(min,Math.min(next,max));
      if(change) this.emitResize();
    },
    dragend(e){
      window.removeEventListener('mousemove',this.ondrag);
      this.curBorder = null;
      this.emitResize();
    },
    validateWidth(){
      let isMin = this.minWidth!=-1;
      let isWidth = this.width!=-1;
      if(isMin)
        this.bounds['h'] = this.minWidth;
      if(isWidth) this.updateWidth();
    },
    validateHeight(){
      let isMin = this.minHeight!=-1;
      let isHeigh = this.height!=-1;
      if(isMin)
        this.bounds['v'] = this.minHeight;
      if(isHeigh) this.updateHeight();
    }
  },
}
</script>

<style lang="scss">
.custom-panel {
  display: flex;
  width: var(--pl-width);
  height: var(--pl-height);
  min-width: var(--pl-width);
  min-height: var(--pl-height);
  z-index: var(--pl-z-index);
  .p-content{
    min-width: max(calc(var(--pl-width) - var(--pl-borders-height) / 2), 100%);
    min-height: max(calc(var(--pl-height) - var(--pl-borders-height) / 2), 100%);
    height: max-content;
  }
  .p-borders-list, .magnit-zones{
    position: absolute;
  }
  .p-borders-list .panel-border {
    border: 1px inset transparent;
    background: rgba(0, 0, 0, 0);
    transition: 0.1s ease-in-out;
    transition-property: background-color, border;
    z-index: 10;
    &:active {
      border: 4px inset #00000059;
      background: #dcdcdc;
      border-radius: 3px;
    }
    &.h {
      cursor: ew-resize;
    }
    &.v {
      cursor: ns-resize;
    }
  }
  .magnit-zones{
    &.p-magnit{
      background: rgba(255, 192, 203, 0.658);
    }
  }
}
</style>