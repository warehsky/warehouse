<template>
  <div :class="'chat-user'+(selected?' selected':' usual')" @click="$emit('click',$event)">
    <div class="user-content">
      <div class="top-info">
        <div class="user-name">{{user.author}}<button class="read" v-visible="user.newMessagesCount" @click="$emit('read-all')">прочитать</button></div>
        <div class="last-time">{{user.lastMessage.time}}</div>
      </div>
      <div class="bottom-info">
        <div class="last-message">
          <div class="lm-text">{{user.lastMessage.text}}</div>
          <div v-if="!user.lastMessage.text">&nbsp;</div>
        </div>
        <div v-if="user.lastMessage.author!='me' && user.newMessagesCount" class="new-message-indicator">
          <span style="margin: 0 6px;">{{user.newMessagesCount}}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props:{
    selected:Boolean,
    user:Object
  }
}
</script>

<style lang="scss">
.chat-user{
  width:100%;
  display: flex;
  padding: 15px;
  cursor: pointer;
  .user-content{
    display: flex;
    flex-direction: column;
    width: 100%;
    .top-info{
      display: flex;
      justify-content: space-between;
      .user-name{
        font-weight: bold;
      }
      .last-time{
        color: #959FA5;
      }
    }
    .bottom-info{
      display: flex;
      .last-message{
        overflow: hidden;
        width: 100%;
        -webkit-text-fill-color: #3e3e3e00;
        .lm-text{
          width:max-content;
        }
      }
      .new-message-indicator{
        background: #3ba1ff;
        border-radius: 50px;
        width: max-content;
        height: 19px;
        font-size: 12px;
        color: #f0f4f8;
        font-weight: bold;
      }
    }
  }
  &.usual{
    .last-message{
      background: -webkit-linear-gradient(360deg, #000000, #F0F4F8);
      background-clip: text;
    }
  }
  &:hover{
    background: #DCF1FA;
    .last-message{
      background: -webkit-linear-gradient(360deg, #000000, #C7EDFC);
      background-clip: text;
    }
    .read{
      visibility: visible;
    }
  }
  &.selected{
    background: #C7EDFC;
    .last-message{
      background: -webkit-linear-gradient(360deg, #000000, #C7EDFC);
      background-clip: text;
    }
    .read{
      visibility: visible;
      pointer-events: all;
      filter: none;
    }
  }
  .read{
    border: 1px solid #767676;
    border-radius: 20px;
    font-size: 14px;
    padding: 2px 8px;
    background: bisque;
    margin: 0 8px;
    cursor: pointer;
    visibility: hidden;
    pointer-events: none;
    filter: grayscale(1) contrast(0.1) brightness(1.6);
    user-select: none;
    &:hover{
      background: #ead2b6;
    }
    &:active{
      background: #dbc5ab;
    }
  }
}
</style>