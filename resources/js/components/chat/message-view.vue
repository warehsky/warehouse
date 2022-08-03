<template>
  <div ref="messageView" class="message-view">
    <slot name="before"></slot>
    <div ref="scrolingEl" class="messages-list">
      <message v-for="(message,index) in messages" :key="index"
        :message="message"
        :status="message.status"
        :showDate="messages[index-1]?messages[index-1].date!=message.date:true"
        :showTime="messages[index-1]?(messages[index-1].author!=message.author || messages[index-1].time!=message.time):true">
      </message>
    </div>
  </div>
</template>

<script>
import Message from './message.vue'
export default {
  components:{
    Message,
  },
  props:{
    messages:Array,
    autoScrollHeight:{
      type:Number,
      default(){
        return 150;
      }
    }
  },
  data(){
    return {
      scroll:0,
      lastMessageslength:0
    }
  },
  updated(){
      this.scroll = this.getScroll();
      if(this.messages.length == 0)
        this.lastMessageslength = 0;
      if(this.messages.length>0 && this.messages.length != this.lastMessageslength) {
        if(this.lastMessageslength==0 || (this.lastMessageslength>0 && this.scroll<=this.autoScrollHeight)) {
          this.scrollDown(this.lastMessageslength==0?'auto':'smooth');
        }
        this.lastMessageslength = this.messages.length;
      }
  },
  methods:{
    getScroll(){
      return (this.$el.scrollHeight-this.$el.offsetHeight)-(this.$el.getBoundingClientRect().top-this.$refs.scrolingEl.getBoundingClientRect().top);
    },
    scrollDown(behavior='auto'){
      this.$el.scroll({  top:this.$el.scrollHeight, left:0, behavior:behavior });
    }
  }
}
</script>

<style>
.message-view{
  overflow: auto;
  overflow-x: hidden;
}
.messages-list{
  border: 1px solid transparent;
}
</style>