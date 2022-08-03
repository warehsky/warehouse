<template>
  <div class="zone-list-item" :class="{ 'zone-hover':properties.hover, mark }" @mouseenter="$emit('mouseenter',$event)" @mouseleave="$emit('mouseleave',$event)">
    <div style="min-width:2.5em">#{{properties.id < 0?"??":properties.id}}</div>
    <div class="actions">
      <img src="/img/icons/cursor-select.svg" alt="find" title="Перейти к зоне" @click="$emit('inspect')">
      <img src="/img/icons/pen-edit.svg" alt="edit" title="Редактировать зону" @click="$emit('edit',editing);">
      <div class="group" :class="{ disabled:editing }">
        <img v-if="options.visible" src="/img/icons/eye.svg" title="Скрыть зону" alt="hide" @click="$emit('hide',true)">
        <v-html 
          v-else
          class="eye-off"
          :style="{ fill:options.fillColor, border:'1px solid'+ options.fillColor, background:'black' }"
          src="/img/icons/eye-off.svg"
          title="Показать зону"
          @click.native="$emit('hide',false)">
        </v-html>
      </div>
    </div>
    <div class="zone-title">{{options.description}}</div>
    <div class="actions">
      <img class="delete" :title="'Удалить (локально)'" :class="{ pushed:state.name=='deleted' }" style="padding:1px" src="/img/icons/trash.svg" @click="$emit('delete',state.name!='deleted')"/>
    </div>
    <div class="state" :style="'--state-bg-color:'+state.color" :title="state.title"></div>
  </div>
</template>

<script>
import ItemStateManager from './ItemStateManager.js';
export default {
  props:{
    item:{ 
      type:Object,
      required:true
    },
    options:{
      type:Object,
      required:true
    },
    properties:{ 
      type:Object,
      required:true
    }
  },
  data(){
    return{
      editing:false,
      mark:false
    }
  },
  computed:{
    state(){
      return ItemStateManager.get(this.item);
    }
  },
  beforeMount(){
    Object.entries(this.$listeners).forEach(([event, handler])=>{
      if(!/^poly-\w*/.test(event)) return;
      this.item.events.add(event.replace("poly-",""),handler);
    })
  },
  methods:{
    editZone(start){
      this.editing = start;
      this.item.options.set('draggable',this.editing);
      this.item.options.set('cursor',this.editing?'move':'pointer');
      this.item.editor[this.editing?'startEditing':'stopEditing']();
    },
    drawZone(start){
      this.editing = start;
      this.item.options.set('draggable',this.editing);
      this.item.options.set('cursor',this.editing?'move':'pointer');
      this.item.editor[this.editing?'startDrawing':'stopEditing']();
    },
    insideParent(){
      let height = 0;
      for(let i=0;i<this.$el.parentElement.children.length;i++){
        let child = this.$el.parentElement.children[i];
        if(child == this.$el)
          return this.$el.parentElement.scrollTop<=height+child.scrollHeight/2 && this.$el.parentElement.offsetHeight>=height+child.scrollHeight/2-this.$el.parentElement.scrollTop;
        height += child.scrollHeight;
      }
    },
    async searchInList(){
      this.$el.scrollIntoView({ behavior:"smooth", block:"nearest" });
			this.mark = true;
			await setTimeout(()=>{ this.mark = false },3000);
    }
  }
}
</script>

<style lang="scss">
.zone-list-item{
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  &:not(:hover){
    .actions img{
      filter: opacity(0.2);
    }
  }
  .state{
    width: 6px;
    background: var(--state-bg-color);
  }
  &.zone-hover{
    background: yellowgreen;
  }
  &.mark{
    animation: fade 3s ease-in-out;
  }
  @keyframes fade {
    0%{
      background-color: rgba(0, 0, 255, 0);
    }
    20%{
      background-color: lightseagreen;
    }
    100%{
      background-color: rgba(0, 0, 255, 0);
    }
  }
}
.zone-title{
  white-space: nowrap;
  width: 100%;
  flex-shrink: 2;
  overflow: hidden;
}
.actions{
  cursor: pointer;
  display: flex;
  align-items: center;
  .eye-off{
    display:inline-flex;
  }
  .group{
    display: flex;
  }
  img,.eye-off{
    width:20px;
    margin: 0 5px;
    padding: 3px;
    border-radius: 3px;
    &:hover{
      box-shadow: 0px 0px 3px;
      background: #97cdff;
    }
    &.delete{
      &:hover{
        background: #ff9797;
      }
      &.pushed{
        background: #ff9797;
      }
    }
  }
  img, .group, .eye-off{
    &.disabled{
      filter: contrast(0.1);
      cursor: default;
      & *{
        pointer-events: none;
      }
    }
  }
  
}
</style>