<template>
  <div class="add-goods-modal">
    <div ref="itemSearch" class="items-filters">
      <currency-switch v-show="currentPage == pages.items"/>
      <input ref="search" class="checkout_text" v-if="currentPage == pages.groups || foundItems" id="search" placeholder="Поиск выполняется только по нажатию Enter или кнопки ->" @change="itemSearch($event.target.value)">
      <input ref="search" class="checkout_text" v-else id="search" v-model="subgroupSearch" placeholder="Поиск выполняется моментально">
      <button :disabled="currentPage != 0 && !foundItems" @click="itemSearch($refs.search.value)">Поиск</button>
    </div>
    <div v-if="state.groups == states.loaded && currentPage == pages.groups" id="itemsgroups">
      <ul>
        <li v-for="(group, index) in groups" :key="index">
          <div :tabindex="index" id="groupButton" @click="openGroup(index)">{{index+1}}. {{group.title}}</div>
          <ul v-if="selectedGroup==index" id="groupsDropdown">
            <li v-for="(subgroup, index) in group.children" :key="index">
              <div id="groupButton" @click="openTable(subgroup)">{{index+1}}) {{subgroup.title}}</div>
            </li>
          </ul>
        </li>
      </ul>
    </div>
    <circle-loading class="agp-loader" v-else-if="state.groups == states.loading" :radius="38" :ringWeight="12"></circle-loading>
    <div class="loading-error" v-else-if="state.groups == states.error">
      <error-icon class="error-icon"></error-icon>
      <div style="color:red;">Ошибка загрузки.</div>
      <button @click="resetState()">Повторить</button>
    </div>
    <div id="groupstable" ref="groupstable" v-if="currentPage == pages.items" >
      <h3 v-if="showTableTitle">{{currentSubgroupTitle}}</h3>
      <circle-loading class="agp-loader" v-else-if="state.items == states.loading" :radius="38" :ringWeight="12"></circle-loading>
      <div class="loading-error" v-else-if="state.items == states.error">
        <error-icon class="error-icon"></error-icon>
        <div style="color:red;">Ошибка загрузки.</div>
        <button @click="updateGoods()">Повторить</button>
      </div>
      <table ref="tableItems" v-if="state.items == states.loaded">
        <thead>
          <tr id="tableHead">
            <th id="stickerTarget"><div>ID</div></th>
            <th>Вид</th>
            <th>Наименование</th>
            <th>Цена</th>
            <th>акция</th>
            <th>Дисконт кол-во</th>
            <th>Дисконт цена</th>
            <th>Остаток</th>
            <th>Количество</th>
            <th>Сумма</th>
          </tr>
        </thead>
        <agp-good v-for="(item, index) in outputItems" :key="index" :item="item"
          :shop_url="$el.attributes.shop_url.value"
          :course="course"
          @editCount="editCount"/>
      </table>
    </div>
  </div>
</template>

<script>
import ErrorIcon from '../../../UI/mini/error-icon.vue';
import sticker from "../../../sticker.vue";
import CircleLoading from '../../../UI/mini/circle-loading.vue';
import CurrencySwitch from '../../editor/currency-switch.vue';
import OrderItem from "../../orderItem.js";
import AgpGood from './agp-good.vue';
export default {
  name: "add-goods-modal",
  components: {
    sticker,
    CircleLoading,
    ErrorIcon,
    AgpGood,
    CurrencySwitch
  },
  props:{
    showTableTitle:{
      type:Boolean,
      default(){
        return true;
      }
    },
    defaultItems:{
      type:Array,
      default(){
        return [];
      }
    },
    course:{
			type:Number,
			required:true,
			default:0
		},
  },
  watch:{
    defaultItems:{
      deep:true,
      handler:function(){
        this.updateDefaultItems();
      }
    }
  },
  data() {
    return {
      state:{
        groups:0,
        items:0
      },
      states:{
        error:-1,
        loading:0,
        loaded:1,
      },
      subgroupSearch: '',
      selectedGroup: -1,
      currentSubgroupTitle: "",
      currentPage: 0,
      selectedItems:[],
      items: [],//товары,
      foundItems: null,
      groups: [],//группы товаров
      stickerOffsetLeft:0,
      pages:{
        groups:0,
        items:1,
      }
    };
  },
  filters: {
    currencydecimal(value) {
      return value.toFixed(2);
    }
  },
  computed:{
    outputItems(){
      if(this.foundItems) return this.foundItems;
      if(!this.subgroupSearch || this.subgroupSearch=='') return this.items;
      let search = this.subgroupSearch;
      return this.items.filter((value)=>value.title.toLowerCase().indexOf(search.toLowerCase()) != -1);
    }
  },
  mounted() {
    this.resetState();
  },

  methods: {
    updateDefaultItems(){
      this.selectedItems = [];
      if(this.defaultItems.length>0){
        this.state.goods = this.states.loading;
        axios.get("/Api/getItems",{ params:{ items:JSON.stringify(this.defaultItems.getGroupProperty("itemId")) } })
          .then((response)=>{
            response.data.items.forEach((item)=>{
              let orderItem = new OrderItem({
                ...item,
                itemId:item.id,
                quantity:this.defaultItems.getItemBy((ditem)=>{ return ditem.itemId == item.id }).quantity
              });
              this.editCount(orderItem);
            });
            this.state.goods = this.states.loaded;
          }).catch((e)=>{ this.state.goods = this.states.error; console.error(e); })
      }
    },
    openGroup(index){
      if(this.selectedGroup==index)
        this.selectedGroup = -1;
      else
        this.selectedGroup = index;
    },
    onEditCount(item){
      if(item.quantity>0){
        if(!this.selectedItems.includes(item))
          this.selectedItems.push(item);
      }
      else if(this.selectedItems.includes(item)){
        this.selectedItems.splice(this.selectedItems.indexOf(item),1);
      }
    },
    editCount(item){
      if(item.quantity<1){
        this.onEditCount(item);
        return;
      }
      if(item.quantity>1000)
        item.quantity=1000;
      if(item.stockPrice){
        item.courier = item.stockPrice;
        this.onEditCount(item);
        return;
      }
      if(item.discountBound>0 && item.discountBound<2000000 && item.quantity>=item.discountBound && item.discountPrice)
        item.courier = item.discountPrice;
      else
        item.courier = item.price;
      this.onEditCount(item);
    },
    openTable(subgroup=null){
      if(subgroup){
        this.currentSubgroupTitle = subgroup.title;
        this.updateGoods(subgroup.id);
      }
      this.currentPage = this.pages.items;
      this.changeContent();
    },
    closeTable(){
      this.$refs.search.value = "";
      this.foundItems = null;
      this.currentPage = this.pages.groups;
      this.currentSubgroupTitle = "";
      this.items = [];
      this.changeContent();
    },
    changeContent(){
      this.$emit('changeContent',{
        currentPage:this.currentPage,
        currentSubgroupTitle:this.currentSubgroupTitle
      });
    },
    resetState(){
      this.state.groups = this.states.loading;
      this.closeTable();
      this.selectedGroup = -1;
      this.updateDefaultItems();
      this.groups = [];
      axios
        .get("/Api/getItemGroups",{ headers:{ 'X-Access-Token':Globals.api_token } })
        .then(response => {
          this.groups = response.data;
          this.state.groups = this.states.loaded;
        })
        .catch((e)=>{ this.state.groups = this.states.error; console.error(e); })
    },
    updateGoods(id){
      this.state.items = this.states.loading;
      axios
      .get('/Api/getGroupData?group='+id)
      .then((response)=>{
        let items = [];
        response.data.items.forEach((item)=>{
          let orderItem = new OrderItem({
            ...item,
            itemId:item.id
          });
          let ditem = this.defaultItems.getItemBy((dItem)=>{ return dItem.itemId==item.id });
          if(ditem){
            orderItem.quantity = ditem.quantity;
            orderItem.courier = ditem.price;
          }
          else{
            orderItem.quantity = 0;
            orderItem.courier = item.price;
          }
          items.push(orderItem);
        });
        this.items = items;
        this.state.items = this.states.loaded;
      })
      .catch((e)=>{ console.error(e); this.state.items = this.states.error; })
    },
    itemSearch(text){
      this.foundItems = [];
      if(!text || text.length<2){ this.foundItems = null; return; }
      this.state.items = this.states.loading;
      axios.get("/Api/getSearch", { params:{ text } })
      .then(({ data:{ items } })=>{ this.foundItems = items.items })
      .then(()=>{ this.state.items = this.states.loaded; })
      .catch(()=>{ console.error(e); this.state.items = this.states.error; })
      this.openTable();
    }
  }
}
</script>

<style lang="scss">
.add-goods-modal{
  display: flex;
  flex-direction: column;
  height: 100%;
  position: relative;
  #itemsgroups{
    max-height: 100%;
    overflow: auto;
    &>ul{
      padding: 10px;
      li{
        list-style: none;
      } 
    }
  }
  .agp-loader{
    margin: auto;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%,-50%);
    .loader{
      border-color: #c4c4c4;
      border-left-color: #931515;
    }
  }
  table{
    border-collapse: separate !important;
    border-spacing: 0px;
  }
  th{
    position: sticky;
    top: 0;
    background: white;
  }
}
#groupButton{
  border: none;
  cursor: pointer;
  &:hover{
    font-weight: 600;
    color: #931515;
  }
  &:focus{
    font-weight: 600;
    color: #931515;
  }
}
.items-filters{
  display: flex;
  justify-content: space-between;
  #search{
    margin-left: auto;
    width: 465px;
  }
}
#groupstable{
  overflow-y: auto;
  height: 100%;
  &>table{
    width: 100%;
  }
  #tableHead{
    td{
      font-weight: 600;
      text-align: center;
    }
  }
  tr,td, th{
    border: 1px solid black;
  }
}
#errorMessage{
  color:red;
  font-weight: 700;
}
/* модальное окно */
.modal-backdrop {
	position: fixed;
	top: 0;
	bottom: 0;
	left: 0;
	right: 0;
	background-color: rgba(0, 0, 0, 0.3);
	display: flex;
	justify-content: center;
	align-items: center;
 }

 .modal {
	background: #FFFFFF;
	box-shadow: 2px 2px 20px 1px;
	overflow-x: auto;
	display: flex;
	flex-direction: column;
	width: 55%;
	border-radius: 8px;
 }

 .modal-header,
 .modal-footer {
	display: flex;
 }

 .modal-header {

	color: #931515;
	justify-content: space-between;
 }

 .modal-footer {
	padding: 25px 50px 50px 50px;
	justify-content: flex-start;
 }

 .modal-body {
	position: relative;
	padding-left: 50px;
	padding-right: 50px;
  overflow: auto;
  max-height: 55vh;
 }

 .btn-close {
	border: none;
	font-size: 20px;
	padding: 25px 50px;
	cursor: pointer;
	font-weight: bold;
	color: #931515;
	background: transparent;
	cursor: pointer;
 }

 .btn-agree {
	padding: 8px 40px;
	color: white;
	border: 1px solid #931515;
	background: #931515;
	border-radius: 4px;
	cursor: pointer;
 }
.btn-agree.n-agree{
	background: #FFFFFF;
	border: 1px solid #931515;
	color: #931515;
}
.modal-text{
	padding-bottom: 15px;
	font-weight: 400;
	font-size: 14px;
	line-height: 18px;
	color: #1C1C1C;
	text-align: justify;
}
.modal-link{
	color: #931515;
}
.backButton{
  padding: 8px 40px;
  color: #931515;
  border: 1px solid #931515;
  background-color: white;
  border-radius: 4px;
  cursor: pointer;
  &:focus{
    outline: none;
  }
}
.loading-error{
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 200px;
  .error-icon{
    width: fit-content;
    position: static;
    margin: auto;
  }
}
/* модальное окно */
.blink {
  -webkit-animation: blink 5s linear infinite;
  animation: blink 5s linear infinite;
  color: #ff0030;
}
@-webkit-keyframes blink {
  100% { color: rgba(34, 34, 34, 0); }
}
@keyframes blink {
  100% { color: rgba(34, 34, 34, 0); }
}
</style>
