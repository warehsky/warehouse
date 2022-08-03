<template>
	<div class="select-address">
    <div class="select-address-input">
      <suggest-view
        :inputClass="inputClass"
        :itemsClass="'suggestItem'"
        :dropdownClass="'s-dropdown'"
        :inputPlaceholder="'Введите адрес заказчика'"
        :autowidth="true"
        @input="(e)=>{ map.getSuggests(e.target.value).then((items)=>{ $refs.suggest.setItems(items); }).catch((err)=>{ if(err.type=='error') error(err.error) }); }"
        @select="(e)=>{ map.setMainGeoObject(e); $emit('change') }"
        ref="suggest">
      </suggest-view>
      <button @click="showMap = !showMap">Карта</button>
    </div>
    <float-panel class="map-float-panel" v-show="showMap" :opened="true" style="width:400px;height:400px;">
		  <div id="map" ref="map"></div>
    </float-panel>
	</div>
</template>

<script>
import SuggestView from '../../UI/inputs/suggest-view.vue';
import MtMap from '../../../yandex-map.js';
import FloatPanel from '../../UI/panels/float-panel.vue';
import Order from '../order';
export default {
  components: { SuggestView, FloatPanel },
	name:'address-input',
  props:{
    map:{
      type:MtMap,
      required:true
    },
    defaultCoords:{
      type:Array,
      default(){
        return Order.defaultAddress.coords;
      }
    },
    defaultAddr:{
      type:String,
      default(){
        return Order.defaultAddress.addr;
      }
    },
    zones:{
      type:Array,
      default(){
        return []
      },
      required:true
    },
    sots:{
      type:Array,
      default(){
        return []
      },
      required:false
    },
    inputClass:String
  },
	data(){
		return{
      showMap:false,
      loaded:false
	  }
  },
  mounted(){
    this.map.addEventListener("placemarkDragend",(output)=>{ 
      this.$refs.suggest.setText(output);
      this.$emit('change');
    });
  },
	methods:{
		reload(coords,addr){
      coords = coords||this.defaultCoords;
      addr = addr||this.defaultAddr;
			this.$refs.suggest.setText(addr);
      this.map.movePlacemarkTo(this.map.placemark,coords);
		}
	}
}
</script>

<style lang="scss">
.select-address{
	width:auto;
	height:auto;
  position: relative;
  .select-address-input{
    width: 100%;
    display: flex;
  }
  .map-float-panel{
    right: 0;
    border:1px solid #BFBFBF;
    border-radius: 8px;
    z-index: 1;
  }
}
#map,
.ymap-container {
  width: 100%;
  height: 100%;
}
.suggestItem {
  font-size: 18px;
  width: 100%;
  margin: 0;
}
.s-dropdown{
	width: auto;
}
/*кастомная кнопка на карте*/
.mapButton{
  background-color: white;
  border-radius: 3px;
  box-shadow: 0px 0px 3px black;
  height: 26px;
  padding: 0 3px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  cursor: pointer;
}
.checkout_text{
	width: 100%;
	height: 36px;
	background: #FFFFFF;
	border-radius: 4px;
	border: 1px solid #c4c4c4;
	padding: 8px 10px 9px 10px;
	outline: none;
	color: #111;
	font-size: 18px;
  &.disabled{
    background: white;
    border: none;
    font-size: inherit !important;
    padding: 0;
    height: auto;
  }
}
</style>