<template>
  <div id="chat-container">
    <!-- TODO:@opened="$event?$refs.phraseTextbox.focus():null" -->
    <dropdown-menu ref="pEditPanel" class="additional-panel-edit" :toggleByClick="true" :headClass="'ape-head'" @opened="$refs.phraseTextbox.focus()">
      <template #d-head>
        Управление фразами
      </template>
      <template #d-body>
        <div class="phrases-panel">
          <div class="phrase-input">
            <textbox ref="phraseTextbox"
              class="chat-input chat-textbox"
              type="text"
              placeholder="Введите текст фразы"
              v-model="phraseDraft"
              @change="savePhraseDraft()"
              :autoresize="true">
            </textbox>
            <div class="additional-button ab-free" @click="addPhrase(phraseDraft);">
              <span 
                v-visible="phraseState.addition == phraseStates.default">
                Добавить
              </span>
              <circle-loading 
                v-if="phraseState.addition == phraseStates.prosess"
                class="button-indicator"
                :radius="20"
                :ringWeight="9">
              </circle-loading>
              <div 
                v-if="phraseState.addition == phraseStates.error"
                class="button-indicator">
                <error-icon class="button-error-indicator"></error-icon>
                Повторить
              </div>
            </div>
          </div>
          <div style="margin: 5px;font-weight: 300;font-size: smaller;color: dimgrey;">
            <span class="tip">
              <span style="color:red">*</span>Совет - Вместо ввода имени используйте выражение</span>
            <code class="copyable">#me</code>
            <span class="tip">
              . Оно автоматически заменяется на ваше имя.
            </span>
          </div>
          <div class="pp-body">
            <vertical-list
              style="max-height:400px"
              :items="phrases"
              :canEdit="true"
              :canRemove="true"
              @edit="editPhrase($event.index,$event.original,$event.finish)"
              @remove="removePhrase($event.index)"
              @change="$refs.pPanel.update(); $refs.pEditPanel.update();">
              <template #button-save>
                <span 
                  v-visible="phraseState.editing == phraseStates.default">
                  Сохранить
                </span>
                <circle-loading 
                  v-if="phraseState.editing == phraseStates.prosess"
                  class="button-indicator"
                  :radius="20"
                  :ringWeight="9">
                </circle-loading>
                <div v-if="phraseState.editing == phraseStates.error" class="button-indicator">
                  <error-icon class="button-error-indicator"></error-icon>
                  Повторить
                </div>
              </template>
              <template #button-remove="{ index }">
                <span 
                  v-visible="index==phraseState.targetIndex?phraseState.removing == phraseStates.default:true">
                  Удалить
                </span>
                <circle-loading 
                  v-if="index==phraseState.targetIndex && phraseState.removing == phraseStates.prosess"
                  class="button-indicator"
                  :radius="20"
                  :ringWeight="9">
                </circle-loading>
                <div v-if="index==phraseState.targetIndex && phraseState.removing == phraseStates.error" class="button-indicator">
                  <error-icon class="button-error-indicator"></error-icon>
                  Повторить
                </div>
              </template>
            </vertical-list>
          </div>
        </div>
      </template>
    </dropdown-menu>
    <div ref="panels" class="panels-container">
      <panel ref="panel" class="users-panel"
        :pivot="['left']"
        :minWidth="250"
        :maxWidth="panelMaxWidth"
        :height="640"
        :width="panelWidth"
        @resize="panelPersWidth = $event.width/($refs.panels.offsetWidth/100)">
        <div class="users-filter">
          <div>Дата&nbsp;</div>
          <div class="f-line">c<input type="date" :max="userfilters.today" v-model="userfilters.dfrom" /></div>
          <div class="f-line">по <input type="date" :min="userfilters.dfrom" :max="userfilters.today" v-model="userfilters.dto"/></div>
          <div class="additional-button ab-free ab-min ab-inset-shadow" @click="resetUsersFilters">Сброс</div>
        </div>
        <user v-for="user in filteredUsers" :key="user.id"
          :user="user"
          :selected="selectedUser == user.id"
          @read-all="confirmMessages(JSON.stringify(messages.map(m=>m.id)))"
          @click="selectedUser = user.id">
        </user>
      </panel>
      <div :class="'chat '+ (loadingStatus==0?'wait':loadingStatus==1?'loading':loadingStatus==2?'loaded':'error')">
        <div class="chat-header"></div>
        <message-view ref="messageView" class="messages-view" :messages="messages"></message-view>
        <suggestions class="suggestions-list"
          v-if="messages.length>0" 
          :suggestions="messages[messages.length-1].suggestions"
          @select="sendMessage($event)">
        </suggestions>
        <div class="down-panel">
          <div :class="'typeing-view'+(!canwrite?' disabled':'')">
            <div class="additional-buttons ab-left">
              <div class="additional-button default-phrases-launcher"
                @click="openAdditionalPanel = !openAdditionalPanel">
                Фразы
              </div>
            </div>
            <input
              ref="messageInput"
              class="chat-input" 
              placeholder="Введите сообщение" 
              type="text"
              v-model="draftMessage"
              @keydown="(e)=>{ if(e.key == 'Enter') onSend();}"
              @change="saveMessageDraft($event)"
              :disabled="loadingStatus<2">
            <div class="additional-buttons ab-right">
            </div>
            <div v-if="draftMessage && draftMessage.length>0" :class="'send-button'+((draftMessage?draftMessage.length<1:true)?' isaddition-buttons':'')" @click="onSend">
              <img src="/images/send.svg" alt="">
            </div>
          </div>
          <dropdown-menu ref="pPanel" :class="'additional-panel'+(openAdditionalPanel?' ap-open':'')" :hideExample="true" :value="openAdditionalPanel">
            <template #d-body>
              <div class="phrases-panel">
                <div class="pp-header">
                  <div class="additional-button" style="padding:0 5px; margin:10px" @click="openAdditionalPanel = false">
                    <img src="/img/close.svg" width="20px">
                  </div>
                </div>
                <div class="pp-body">
                  <vertical-list
                    style="max-height:400px"
                    :items="phrases"
                    :canSelect="true"
                    @select="onSelectPhrase($event.text); openAdditionalPanel = false;">
                  </vertical-list>
                </div>
              </div>
            </template>
          </dropdown-menu>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import panel from '../UI/panels/panel/panel.vue'
import User from './user.vue';
import MessageView from './message-view.vue'
import Suggestions from './suggestions.vue'
import Watcher from '../../watcher.js'
import RequestQueue from '../../RequestQueue.js'
import { Request } from '../../RequestQueue.js'
import Message from "./message.js"
import DropdownMenu from '../menu/dropdown-menu.vue';
import VerticalList from './vertical-list.vue';
import Textbox from '../UI/inputs/textbox.vue';
import circleLoading from '../UI/mini/circle-loading.vue';
import errorIcon from '../UI/mini/error-icon.vue';
export default {
  components: { 
    panel, 
    User, 
    MessageView, 
    Suggestions, 
    DropdownMenu, 
    VerticalList,
    Textbox,
    circleLoading,
    errorIcon
  },
  props:{
    username:String,
    canwrite:Number
  },
  data(){
    return{
      users:[],
      filteredUsers:[],
      messages:[],
      selectedUser:-1,
      panelWidth:455,
      panelPersWidth:0,
      panelMinWidth:250,
      panelMaxWidth:450,//расчитывается on resize для максимвльной ширины пвнели
      openAdditionalPanel:false,
      phrases:[],
      phraseDraft:localStorage.phraseDraft?localStorage.phraseDraft:"",
      phraseState:{
        addition:false,
        removing:false,
        editing:false,
        targetIndex:-1
      },
      phraseStates:{
        default:0,
        prosess:1,
        error:-1
      },
      draftMessage:"",
      requestQueue:new RequestQueue(),
      chatStatuses:{
        wait:0,
        loading:1,
        loaded:2,
        error:-1
      },
      loadingStatus:0,
      userfilters:{
        today:new Date().toStringReverce(),
        dfrom:localStorage.userdFrom?localStorage.userdFrom:new Date().toStringReverce(),
        dto:localStorage.userdTo?localStorage.userdTo:new Date().toStringReverce()
      }
    }
  },
  watch:{
    selectedUser:function(){
      this.loadingStatus = this.chatStatuses.loading;//статус чата
      this.reinit();
      this.loadDraft();
      this.initMessagesWatcher();
    },
    userfilters:{
      handler:function(){ this.updateUsers(); this.saveUsersFilters() },
      deep:true
    },
  },
  mounted(){
    window.addEventListener("resize",()=>{ this.updatePanel(70); });
    this.updatePanel(70);
    window.addEventListener("keydown",(e)=>{ 
      if(e.key == 'Escape')
        this.openAdditionalPanel=false;
    });
    this.requestQueue.start();
    this.requestQueue.add(new Request("get","/Api/getChatUsers")
      .then((response)=>{
        if(!response.data.code == 200){
          alert("Ошибка получения списка пользователей.")//TODO:errors
          return;
        }
        response.data.ChatUsers.forEach((user) => {
          this.users.push(new UserData(user));
        });
      })
      .finally(()=>{
        this.initUsersWatcher();
      })
    );
    this.loadPhrases();
  },
  methods:{//TODO:message about error, "errored message",can send flag
    confirmMessages(ids){
      return axios
        .get("/Api/confirmChatMessages",{ params:{ ids }, headers:{ 'X-Access-Token':Globals.api_token } })
        .then((response)=>{ 
          if(response.data.error || response.data.code != 200){
            console.error(response.data.error || response.data.message);
            return;  
          }
        })
        .catch(console.error);
    },
    reinit(){
      this.messages = [];
      this.requestQueue.add(new Request("get","/Api/getChatMessages",{ //первая загрузка всех сообщений
        params:{ userId:this.selectedUser, status:-1 },
        headers: { 'X-Access-Token': Globals.api_token } })
        .then((response)=>{
          response.data.ChatMessages.forEach((message)=>{
            let author;
            if(message.moderatorId)
              author = 'me';
            else
              author = message.author?message.author:"User"+this.selectedUser;
            this.messages.push(new Message(message.id,author,message.message,'received',[],message.created_at));
          });
          this.loadingStatus = this.chatStatuses.loaded
          this.$nextTick(() => {
            this.$refs.messageInput.focus();
          });
        }).catch((e)=>{
          console.error(e);
          this.loadingStatus = this.chatStatuses.error;
          //TODO: this.error();
          // this.canSend = false;
        }));
    },
    initUsersWatcher(){
      this.firstUpdate = false;
      this.usersWatcher = new Watcher("get","/Api/getAlerts",{ 
        params:{ isusers:1 },
        headers: {'X-Access-Token': Globals.api_token} }, 3000);
      this.usersWatcher.addEventListener("receive",(response)=>{
        let dateChange = false;
        this.users.forEach((user)=>{
          let ruser = response.data.users.getItemBy((item)=>{ return item.id == user.id; });
          let newDate = new Date(Date.parse(ruser.lastmes?ruser.lastmes.created_at:ruser.created_at))
          dateChange = dateChange || user.lastMessage.date.getTime() != newDate.getTime();
          let userName = "User"+ruser.id;
          let author = ruser.lastmes?(ruser.lastmes.moderatorId?'me':userName):userName;
          user.setLastMessage(author,ruser.lastmes?ruser.lastmes.message:"",newDate);
          user.newMessagesCount = ruser.noreadmes_count;
        });
        if(dateChange)
          this.updateUsers();
        response.data.users.except(this.users,'id','id').forEach((user)=>{
          this.users.push(new UserData(user));
        });
        if(!this.firstUpdate) {
          this.updateUsers();
          this.firstUpdate = true;
        }
      });
      this.usersWatcher.addEventListener("error",(e)=>{
        console.error(e);
      })
      this.usersWatcher.start();
    },
    initMessagesWatcher(){
      if(this.messagesWatcher){
        this.messagesWatcher.stop();
        this.messagesWatcher.removeAllListeners();
      }
      this.messagesWatcher = new Watcher("get","/Api/getChatMessages",{ 
        params:{ userId:this.selectedUser, status:0 },
        headers: {'X-Access-Token': Globals.api_token} },3000,this.requestQueue);//запрашиваем сообщения каждые 3 секунды

      this.messagesWatcher.addEventListener("receive",(response)=>{
        response.data.ChatMessages.forEach((message)=>{
          if(this.messages.contains((item)=>{ return item.id == message.id }))
            return;
          let author;
            if(message.moderatorId)
              author = 'me';
            else
              author = message.author?message.author:"User"+this.selectedUser;
            this.messages.push(new Message(message.id,author,message.message,'received',[],message.created_at));
        });
      });

      this.messagesWatcher.addEventListener("error",(e)=>{
        console.error(e);
      })
      this.messagesWatcher.start();
    },
    updateUsers(){
      this.filteredUsers = this.users.filter((item)=>{
        let from = new Date(this.userfilters.dfrom);
        let to = new Date(this.userfilters.dto);
        to.setDate(to.getDate()+1);
        return item.lastMessage.date >= from && item.lastMessage.date <= to;
      });
    },
    getDraft(){
      return localStorage["u_"+this.selectedUser+"_draft"];
    },
    loadDraft(){
      this.draftMessage = localStorage["u_"+this.selectedUser+"_draft"];
    },
    clearDraft(){
      localStorage["u_"+this.selectedUser+"_draft"] = ""
    },
    saveMessageDraft(){
      localStorage["u_"+this.selectedUser+"_draft"] = this.draftMessage;
    },
    onSend(e){
      if(this.loadingStatus<2) return;
      this.sendMessage(this.draftMessage);
      if(this.draftMessage == this.getDraft())
        this.clearDraft();
      this.draftMessage = "";
    },
    sendMessage(text){
      if(this.selectedUser == -1)
        return;
      if(text.length<1)
        return;
      let message = new Message(-1,'me',text,'sending',[]);
      this.messages.push(message);
      this.requestQueue.add(
        new Request('get',"/Api/addChatMessage", { 
          params:{ userId:this.selectedUser, message:text }, 
          headers: { 'X-Access-Token': Globals.api_token } })
        .then((response)=>{
          if(response.data.code!=200){
            message.status = "error";
            throw "GET /Api/addChatMessage error:\""+response.data.msg+"\"\n";//TODO:states
          }
          message.id = response.data.id;
          message.status = 'sended'
        })
        .catch((e)=>{
          message.status = "error";
          throw e;
        })
      );
    },
    getPhraseText(original){
      return original.replaceAll("#me",this.username);
    },
    loadPhrases(){
      axios
        .get("/Api/getChatAnswers", { headers:{ 'X-Access-Token':Globals.api_token } })
        .then(({data:{ answers }})=>{
          answers.forEach((item)=>{
            this.phrases.unshift({ id:item.id, original:item.answer, text:this.getPhraseText(item.answer), editable:true, removable:true });
          });
        })
        .catch(console.error);
    },
    addPhrase(original){
      if(original.length<1)
        return;
      this.phraseState.addition = this.phraseStates.prosess;
      axios
        .get("/Api/updateChatAnswer",{ params:{ id:0, answer:original }, headers:{ 'X-Access-Token':Globals.api_token } })
        .then((responce)=>{
          if(responce.data.code == 200) {
            this.phrases.unshift({ id:this.phrases.length==0?1:this.phrases[0].id+1, original:original, text:this.getPhraseText(original), editable:true, removable:true });
            this.clearPhraseDraft();
            this.phraseState.addition = this.phraseStates.default;
            this.$refs.phraseTextbox.focus();
          }
          else console.warn(responce);
        }).catch((e)=>{
          console.error(e);
          this.phraseState.addition = this.phraseStates.error;
        });
    },
    removePhrase(index){
      this.phraseState.removing = this.phraseStates.prosess;
      this.phraseState.targetIndex = index;
      axios
        .get("/Api/deleteChatAnswer",{ params:{ id:this.phrases[index].id }, headers:{ 'X-Access-Token':Globals.api_token } })
        .then(({data})=>{
          if(data.code == 200) {
            this.phrases.remove(this.phrases[index]);
            this.phraseState.removing = this.phraseStates.default;
            this.phraseState.targetIndex = -1
          } else console.error("removing error");
        })
        .catch((e)=>{
          this.phraseState.removing = this.phraseStates.error;
          this.phraseState.targetIndex = -1
          console.error(e);
        });
    },
    editPhrase(index,original,finish){
      this.phraseState.editing = this.phraseStates.prosess;
      axios
        .get("/Api/updateChatAnswer",{ params:{ id:this.phrases[index].id, answer:original }, headers:{ 'X-Access-Token':Globals.api_token } })
        .then(({data})=>{
          if(data.code == 200){
            let phrase = this.phrases[index];
            this.phraseState.editing = this.phraseStates.default;
            phrase.original = original;
            phrase.text = this.getPhraseText(original);
            finish()
          } else {
            console.error("edit error");
            this.phraseState.editing = this.phraseStates.error;
          }
        })
        .catch((e)=>{
          this.phraseState.editing = this.phraseStates.error;
          console.error(e);
        })
    },
    onSelectPhrase(text){
      this.draftMessage += ((this.draftMessage && this.draftMessage.trim().length>0)?' '+text:text);
      this.$nextTick(()=>{
        this.$refs.messageInput.focus();
      })
    },
    savePhraseDraft(){
      localStorage.phraseDraft = this.phraseDraft;
    },
    clearPhraseDraft(){
      this.phraseDraft = "";
      localStorage.phraseDraft = "";
    },
    updatePanel(maxPers){
      let mW = (this.$refs.panels.offsetWidth/100)*maxPers
      let w = (this.$refs.panels.offsetWidth/100)*this.panelPersWidth
      this.panelMaxWidth = mW>=this.panelMinWidth?mW:this.panelMinWidth+1;
      if(w<this.panelMinWidth)
        this.panelWidth = this.panelMinWidth;
      else if(w>this.panelMaxWidth)
        this.panelWidth = this.panelMaxWidth;
      else
        this.panelWidth = w;
    },
    saveUsersFilters(){
      localStorage.userdFrom = this.userfilters.dfrom;
      localStorage.userdTo = this.userfilters.dto;
    },
    resetUsersFilters(){
      this.userfilters.dfrom = this.userfilters.today;
      this.userfilters.dto = this.userfilters.today;
      this.saveUsersFilters();
    }
  }
}
Date.prototype.toOutputString = function(){
  let now = new Date(Date.now());
  let today = now.getFullYear() == this.getFullYear() && now.getMonth() == this.getMonth() && now.getDate() == this.getDate();
  let daysPassed = (now-this.getTime())/86400000;
  let thisWeek = daysPassed-now.getDay()<=0;
  if(thisWeek){
    if(today){
      return this.getTimeOutputString();
    } else return this.toLocaleString("ru", { weekday:'short' });
  } else return this.toLocaleDateString();
}
Date.prototype.getTimeOutputString = function(){
  let h = this.getHours()+""; if(h.length<2) h = "0"+h;
  let m = this.getMinutes()+""; if(m.length<2) m = "0"+m;
  return h+":"+m;
}
Date.prototype.getDateOutputString = function(){
  let now = new Date(Date.now());
  let today = now.getFullYear() == this.getFullYear() && now.getMonth() == this.getMonth() && now.getDate() == this.getDate();
  let daysPassed = (now-this.getTime())/86400000;
  let thisWeek = daysPassed-now.getDay()<=0;
  if(today) return "Сегодня";
  else if(thisWeek){
    let date = this.toLocaleString("ru", { weekday:"long" });
    return date.charAt(0).toUpperCase()+date.slice(1);
  }
  else return this.toLocaleDateString("ru",{ day:'numeric', month:'long', year:'numeric' });
}
Date.prototype.toStringReverce = function(){
  return this.getFullYear()+"-"+this.getFullMonth()+"-"+this.getFullDate();
}
Date.prototype.getFullMonth = function(){
  let mounth = this.getMonth()+1;
  return mounth>=10?mounth:"0"+mounth;
}
Date.prototype.getFullDate = function(){
  let day = this.getDate();
  return day>=10?day:"0"+day;
}
function UserData(user){
  this.author = user.author?user.author:"User"+user.id,
  this.id = user.id,
  this.newMessagesCount = user.noreadmes_count;
  this.lastMessage = {
    text:"",
    time:"00:00"
  }
  this.setLastMessage = (author,text,date)=>{
    this.lastMessage.author = author;
    this.lastMessage.text = text;
    this.lastMessage.date = date;
    this.lastMessage.time = date.toOutputString()
  }
  let username = "User"+user.chatUserId;
  let author = user.lastmes?(user.lastmes.moderatorId?"me":username):username;
  this.setLastMessage(author,user.lastmes?user.lastmes.message:"",new Date(Date.parse(user.lastmes?user.lastmes.created_at:user.created_at)))
}
</script>

<style lang="scss">
.chat-container{
  height: 650px;
  display: flex;
  flex-direction: column;
  justify-items: flex-end;
  border: 1px solid;
}
.button-indicator{
  position: absolute;
}
.button-error-indicator{
  position:static; width:max-content; margin:auto;
}
.additional-button{
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 0 25px;
  height: 50px;
  min-width: 50px;
  border-radius: 50px;
  background-color: #eaeaf7;
  cursor: default;
  user-select: none;
  margin: 0 5px;
  white-space: nowrap;
  &.ab-free:hover{
    filter: contrast(0.9);
    cursor: pointer;
  }
  &.ab-min{
    padding: 4px 8px;
    height: auto;
    margin: 0;
  }
  &.ab-inset-shadow{
    box-shadow: inset 0px 0px 2px #00000047;
  }
}
.phrases-panel{
  .pp-header{
    display: flex;
    justify-content: flex-end;
  }
  .pp-body{
    max-height: 400px;
    display: flex;
    flex-direction: column;
  }
}
.additional-panel{
  margin: 0;
  border-top: 1px solid #D5D9DE;
  border-radius: 30px;
  position: absolute;
  bottom: 100%;
  width: 100%;
  background: white;
  &.ap-open{
    bottom: calc(100% + 1px);
  }
}
.additional-panel-edit{
  margin: 0;
  border: 1px solid #D5D9DE;
  border-bottom: none;
  top: 100%;
  width: 100%;
  background: white;
  .ape-head{
    &:hover{
      filter: contrast(0.9);
    }
    background: #f0f4f8;
    padding: 15px;
    cursor:pointer;
    font-family: cursive;
    user-select: none;
  }
}
.phrase-input{
  display: flex;
  margin: 10px;
}
.chat-input{
  min-height: 50px;
  border: none;
  border-radius: 30px;
  background: #eaeaf7;
  width: 100%;
  outline: none;
  padding: 0 45px;
}
.chat-textbox{
  border-radius: 10px;
  padding: 10px 20px;
  width: calc(100% - 20px);
  margin-right: 10px;
  max-height: 8em;
}
.panels-container{
  display: flex;
  background: #F0F4F8;
  height:640px;
}
.users-panel{
  border: 1px solid #D5D9DE;
  overflow: auto;
}

.chat{
  width: 100%;
  display: flex;
  flex-direction: column;
  background: white;
  max-width: 100%;
  overflow-y: hidden;
  .chat-header{
    min-height: 50px;
    border: 1px solid #D5D9DE;
    border-left: none;
  }
  .messages-view{
    height: 100%;
    border-right: 1px solid #D5D9DE;
  }
  .down-panel{
    border: 1px solid #D5D9DE;
    border-left: none;
    position: relative;
  }
  .typeing-view{
    display: flex;
    position: relative;
    //border: 1px solid #D5D9DE;
    //border-left: none;
    padding: 20px;
    .send-button{
      min-width: 50px;
      min-height: 50px;
      display: flex;
      place-content: center;
      place-items: center;
      border-radius: 50px;
      background: repeating-linear-gradient(45deg, #0075b9, #00ff0899 100px);
      cursor: pointer;
      img{
        width: 30px;
        transform: translateX(2px);
      }
      &:hover{
        box-shadow: inset 0px 0px 9px 0px #1c1c1c78;
      }
      &:active{
        transform: scale(0.96);
      }
      &.isaddition-buttons{
        margin-left: 15px;
      }
    }
    &.disabled{
      filter: opacity(0.5);
      pointer-events: none;
      user-select: none;
    }
  }
  &.wait, &.loading, &.error{
    .send-button{
      cursor: default;
      &:hover{
        box-shadow:none
      }
      &:active{
        transform: none;
      }
    }
  }
  &.loading{
    filter: contrast(0.8) grayscale(1);
    &::before{
      content: "";
      position: absolute;
      display:block;
      border-radius: 80%;
      width: 10em;
      height: 10em;
      border: 1px solid;

      font-size: 10px;
      text-indent: -9999em;
      border: 1em solid rgba(191, 191, 191);
      border-left: 1em solid transparent;
      -webkit-transform: translateZ(0);
      -ms-transform: translateZ(0);
      transform: translateZ(0);
      -webkit-animation: load8 1.1s infinite linear;
      animation: load8 1.1s infinite linear;
      left: calc(50% - 5em);
      top: calc(50% - 5em);
      z-index: 1;
    }
    @-webkit-keyframes load8 {
      0% {
        -webkit-transform: rotate(0deg);
        transform: rotate(0deg);
      }
      100% {
        -webkit-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }
    @keyframes load8 {
      0% {
        -webkit-transform: rotate(0deg);
        transform: rotate(0deg);
      }
      100% {
        -webkit-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }
  }
  &.wait{
    filter: contrast(0.8) grayscale(1);
  }
}
.chat{ 
  .additional-buttons{
    display: flex;
    --padding:5px;
    &.ab-right {
      padding-left:var(--padding);
    }
    &.ab-left {
      padding-right:var(--padding);
    }
  }
  &:not(.loaded){
    pointer-events: none;
  }
  &.loaded{
    .additional-button:hover{
      filter: contrast(0.9);
      cursor: pointer;
    }
  }
}
.users-filter{
  display: flex;
  flex-wrap: wrap;
  height: max-content;
  position: sticky;
  top: 0;
  background: #f0f4f8;
  box-shadow: 0px 0px 4px #c0c4c7;
  padding: 10px;
  align-items: center;
  .f-line{
    display: flex;
    align-items: center;
  }
  input[type="date"]{
    outline: none;
    border: none;
    border-radius: 4px;
    box-shadow: inset 0px 0px 4px 0px #dedede;
    background: #ffffff;
    margin: 4px;
    padding: 0px 0px 2px 10px;
  }
}
.suggestions-list{
  min-height: max-content;
  border-right:1px solid #D5D9DE;
}
.tip{
  user-select: none;
}
code{
  background-color: #eee;
  border-radius: 3px;
  font-family: courier, monospace;
  padding: 0 3px;
}
</style>