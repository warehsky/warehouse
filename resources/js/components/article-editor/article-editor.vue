<template>
	<div id="article-editor">
    <div class="actions-header">
      <div>
        <input type="submit" class="article-save-b" @submit="saveDesign()" value="Сохранить и выйти"/>
        <input type="button" class="article-save-b" @click="discardChanges" value="Отменить все"/>
      </div>
      <input type="text" class="project-state" :value="projectState" readonly="readonly"/>
      <input ref="imageLink" type="text" class="project-state link" placeholder="URL изображения будет здесь."/>
      <!-- <div style="display:flex;">
        Автосохранение
        <input type="checkbox" v-model="autosave">
      </div> -->
      <div style="display:flex;">
      <dots-menu :bodyClass="'editor-options-body'">
        <template #dm-body>
          Автосохранение
          <input type="checkbox" v-model="autosave">
        </template>
      </dots-menu>
      </div>
    </div>
		<div id="editorContainer">
			<!-- <email-editor ref="editor" :minHeight="'740px'" @load="editorLoaded" :locale="'ru-RU'"></email-editor> -->
		</div>
	</div>
</template>

<script>
import DotsMenu from './dots-menu.vue';
export default {
	props:{
		articleid:Number,
    design:Object,
    form:String,
    jsoninput:String,
    htmlinput:String
	},
  components:{ DotsMenu },
  data(){
    return{
      projectState:"",
      defaultHtml:"",
      optionsOpened:false,
      autosave:true,
      unlayerOptions:{
        init:{
          id: 'editorContainer',
          projectId: 1234,
          displayMode: 'web',
          locale: 'ru-RU'
        }
      }
    }
  },
  async mounted(){
    this.projectState = "Загрузка...";
    this.jsonInput = document.getElementsByName(this.jsoninput)[0];
    this.htmlInput = document.getElementsByName(this.htmlinput)[0];
    this.parentForm = document.getElementsByName(this.form)[0];
    await this.injectScript('//editor.unlayer.com/embed.js','unlayer').then((unlayer)=>{this.unlayer = unlayer});
    this.unlayer.init(this.unlayerOptions.init);
    this.loadDesign();
    this.configurateImageSource();
    this.initAutosave();
  },
  methods: {
    injectScript(src,objectName){
      let scriptEl = document.createElement('script');
      scriptEl.setAttribute('src',src);
      return new Promise((resolve, reject)=>{
        scriptEl.onload = ()=>{resolve(window[objectName]);}
        document.body.appendChild(scriptEl);
      });
    },
    loadDesign(){
      if(this.design){
        this.unlayer.loadDesign(this.design);
        this.unlayer.exportHtml((data)=>{this.defaultHtml = data.chunks.body});
        this.projectState = "Загружено";
      }
      else
        this.projectState = "Дизайн не найден";  
    },
    configurateImageSource(){
      this.unlayer.addEventListener('image',(file, done)=>{
        let data = new FormData();
        data.append('image', file.attachments[0]);
        data.append('articleId', this.articleid);
        fetch('/Api/saveArticleImg',{
          method:'POST',
          body:data,
          headers:{
            'Accept': 'application/json',
            'X-Access-Token': Globals.api_token,
            'Access-Control-Allow-Origin': window.location.origin+"/"
          }
        }).then((response)=>{
          if (response.status >= 200 && response.status < 300){
            return response.json();
          }
          else
            console.error(response.statusText);
        }).then((fileName)=>{//parsed json => fileName
            this.$refs.imageLink.value = 'https://mt.delivery/img/img/Articles/'+this.articleid+"/"+fileName;
        });
      });
    },
    initAutosave() {
      this.unlayer.addEventListener('design:updated',(data)=>{
        if(this.autosave)
          this.saveDesign();
      });
    },
    saveDesign() {
      this.unlayer.saveDesign((design) => {
        this.unlayer.exportHtml((html)=>{
          if(design == null || html == null){
            this.projectState = 'Ошибка сохранения'
            return false;
          }
          let data = new FormData();
          data.append('id', this.articleid);
          data.append('json', JSON.stringify(design));
          data.append('html', html.chunks.body);
          data.append('css', html.chunks.css);
          fetch('/Api/saveArticle',{
            method:'POST',
            body:data,
            headers:{
              'Accept': 'application/json',
            'X-Access-Token': Globals.api_token
            }
          }).then((response)=>{
            if (response.status >= 200 && response.status < 300){
              return response.json();
            }
            else
              console.error(response.statusText);
          }).then(()=>{
            this.projectState = "Сохранено";
            return true;
          });
        });
      });
      this.projectState = "Сохранение...";
    },
    discardChanges(){
      this.unlayer.saveDesign((design) => {
        let defaultJson = JSON.stringify(this.design);
        let currentJson = JSON.stringify(design);
        if(defaultJson.length>2 && currentJson.length>2 && (defaultJson != currentJson)){
          this.unlayer.loadDesign(this.design);
          this.saveDesign();
          this.projectState = "Изменения сброшены";
        }
        this.projectState = "Изменения не обнаружены";
        return;
      });
    }
  }
}
</script>
<style lang="scss">
.app-body{
  height: 100%;
}
#article-editor{
  height: 100%;
  min-width: max-content;
  display: flex;
	flex-direction: column;
}
#editorContainer{
	height: 740px;
  display: flex;
}
#editor-1{
  min-width: 100%
}
.article-save-b{
  width: max-content;
  border-color: #ffffff;
  background-color: #d3d3d3;
  outline: none;
  box-shadow: none;
  border-radius: 3px;
}
.actions-header{
  display: flex;
  justify-content: space-between;
  width: 100%;
  background-color:#931515;
  padding: 10px;
  min-width: max-content;
}
.project-state{
  width: 40%;
  background-color: #882020;
  border:2px solid #6b1818;
  outline: none;
  color: whitesmoke;
  text-align: center;
}
.link{
  width: 300px;
  background-color: white;
  color: black;
  margin-left: 0;
}
.editor-options-body{
  background-color: white;
  right: 10px;
  border-radius: 4px;
  padding: 0px 3px;
  border:1px solid rgb(133, 133, 133);
}
</style>