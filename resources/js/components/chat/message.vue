<template>
  <div :class="'chat-message '+(message.author=='me'?'me':'user')">
    <div class="chat-message-container">
      <div v-if="showDate" class="chat-message-date">
        <div class="d-line">
          <v-line></v-line>
        </div>
        {{message.date}}
        <div class="d-line">
          <v-line></v-line>
        </div>
      </div>
      <div v-if="showTime" class="chat-message-time">{{message.time}}</div>
      <div :class="'chat-message-view'">
        <circle-loading v-if="status == 'sending'"
          class="message-loader"
          :radius="15"
          :ringWeight="12">
        </circle-loading>
        <error-icon v-if="status == 'error'"></error-icon>
        <slot name="message">
          {{message.text}}
        </slot>
      </div>
    </div>
  </div>
</template>

<script>
import Message from "./message.js"
import ErrorIcon from '../UI/mini/error-icon.vue'
import CircleLoading from '../UI/mini/circle-loading.vue'
import VLine from './v-line.vue'
export default {
  name:'message',
  components:{
    CircleLoading,
    ErrorIcon,
    VLine,
  },
  props:{
    message:Message,
    status:String,
    showTime:Boolean,
    showDate:Boolean
  }
}
</script>

<style lang="scss">
.chat-message{
  display: flex;
  margin: 5px 0;
  .chat-message-container{
    width:100%;
    display: flex;
    flex-direction: column;
    .chat-message-date{
      width: 100%;
      display: flex;
      justify-content: center;
      white-space: nowrap;
      color:#8A8D91;
    }
    .chat-message-time{
      font-size: 14px;
      color:#8A8D91;
    }
  }
  .chat-message-view{
    padding: 0 15px;
    max-width: 50%;
    display: flex;
    align-items: center;
    .message-loader{
      margin-right: 10px;
    }
  }
  &.me{
    justify-content: flex-end;
    .chat-message-container{
      align-items: flex-end;
      .chat-message-time{
        margin-right: 15px;
      }
    }
    .chat-message-view{
      border: 2px solid #009255;
      border-radius: 10px 10px 0px 10px;
      background: linear-gradient(45deg, #3cc68b, #00ccff);
      margin-right: 15px;
    }
  }
  &.user{
    justify-content: flex-start;
    .chat-message-container{
      align-items: flex-start;
      .chat-message-time{
        margin-left: 15px;
      }
    }
    .chat-message-view{
      border: 2px solid #df5d70;
      border-radius: 0px 10px 10px 10px;
      background: linear-gradient(45deg, #C33974, #ff330096);
      margin-left: 15px;
    }
  }
}
.d-line{
  width: 100%;
  padding: 0 15px;
  align-self: center;
}
</style>