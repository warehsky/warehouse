<template>
  	<box-clipped-panel v-show="isShow" class="restriction-control"
		:x="restrictionAreaRect.x"
		:y="restrictionAreaRect.y"
		:width="restrictionAreaRect.width"
		:height="restrictionAreaRect.height">
		<panel ref="restrictionControlBounds" class="restriction-control-bounds"
			pivot="center"
			:magnitHeight="0" 
			:magnitWidth="0"
			:width="restrictionAreaRect.width"
			:height="restrictionAreaRect.height"
			@resize="restrictionControlBoundsResize">
		</panel>
		<div class="actions">
			<button class="apply-button" @click="apply">Применить</button>
			<button class="cancel-button" @click="cancel">Отмена</button>
		</div>
	</box-clipped-panel>
</template>

<script>
import boxClippedPanel from '../UI/panels/box-clipped-panel.vue';
import Panel from '../UI/panels/panel/panel.vue';
import MapConverter from "./map-converter.js";

const defaultCoordinates = [[0,0],[0,0]];
export default {
	components:{ boxClippedPanel, Panel },
	emits:["update:coordinates","cancel"],
	props:{
		enabled:{ default:false },
		coords:{
			type:Array,
			default(){
				return defaultCoordinates;
			}
		},
		map:{
			required:true
		}
	},
	watch:{
		enabled(){
			return this.show(this.coordinates);
		}
	},
	data(){
		return {
			restrictionAreaRect:new RestrictionAreaRect(),
			restrictionArea:null,
			isShow:false,
			coordinates:this.coord
		}
	},
	methods:{
		/**
		 * @param { [[x:Number, y:Number],[x:Number, y:Number]] } coordinates
		 * @returns { Promise<[[x:Number, y:Number],[x:Number, y:Number]]> }
		 */
		async show(coordinates){
			this.isShow = true;
			this.restrictionAreaRect = RestrictionAreaRect.fromMapCoords(this.map, coordinates||this.coordinates);
			if(coordinates && coordinates != defaultCoordinates) this.resetRestrictionArea();
			this.coordinates = coordinates||this.coordinates;
			await this.focusArea();
			this.restrictionAreaRect = RestrictionAreaRect.fromMapCoords(this.map, this.restrictionArea.geometry.getBounds());
			return await new Promise((resolve)=>{
				let vm = this;
				function handle(e){ resolve(e); vm.isShow = false; }
				function onApply(e){ handle(e); vm.$off("cancel", onCancel) }
				function onCancel(e){ handle(e); vm.$off("update:coordinates", onApply) }
				this.$once("update:coordinates", onApply);
				this.$once("cancel", onCancel);
			});
		},
		apply(){ this.$emit("update:coordinates", this.resetRestrictionArea()); },
		cancel(){ this.$emit("cancel", this.coordinates); },
		/**
		 * @returns { [[x:Number, y:Number],[x:Number, y:Number]] }
		 */
		resetRestrictionArea(coordinates){
			let globalBounds = coordinates || this.restrictionAreaRect.toMapCoords(this.map);
			let oldArea = this.restrictionArea;
			this.restrictionArea = new ymaps.Rectangle(globalBounds,{},{
				fillOpacity: 0,
				strokeColor: '#0000FF',
				strokeOpacity: 0.7,
				strokeWidth: 4,
				strokeStyle: 'shortdash'
			});
			this.map.geoObjects.add(this.restrictionArea);
			if(oldArea) this.map.geoObjects.remove(oldArea);
			return this.restrictionArea.geometry.getBounds();
		},
		/**
		 * @param { { rect:DOMRect, side:String } } e
		 */
		restrictionControlBoundsResize(e){
			if(this.map) this.restrictionAreaRect = RestrictionAreaRect.fromRect(this.map,e.rect);
		},
		needZoom(offset){
			offset = offset || 0;
			let area = ymaps.geoQuery(this.restrictionArea);
			return this.map.getZoom()==area.getMaxZoom(this.map)+offset-1;
		},
		async focusArea(offset){
			offset = offset || 0;
			let area = ymaps.geoQuery(this.restrictionArea);
			await this.map.setZoom(area.getMaxZoom(this.map)+offset-1);
			await this.map.setCenter(area.getCenter(this.map));
		},
		update(){
			this.restrictionControlBoundsResize(this.$refs.restrictionControlBounds?.getRect());
		}
	}
}
class RestrictionAreaRect{
	/**
	 * @param { { x:Number, y:Number, width:Number,	height:Number,	pageCoords:[[x:Number,y:Number],[x:Number,y:Number]] } } data
	 */
	constructor(data){
		data = data || {};
		this.x = data.x || 0;
		this.y = data.y || 0;
		this.width = data.width || 350;
		this.height = data.height || 350;
		this.pageCoords = data.pageCoords || [[0,0],[0,0]];
	}
	toMapCoords(map){
		return this.pageCoords.map((point)=>MapConverter.getMapOffset(map,point))
	}
	static fromRect(map, rect){
		let mapContainerRect = map.container.getElement().getBoundingClientRect();
		let pageX = rect.x+document.documentElement.scrollLeft;
		let pageY = rect.y+document.documentElement.scrollTop;
		return new RestrictionAreaRect({
			x:rect.x - mapContainerRect.x,
			y:rect.y - mapContainerRect.y,
			width:rect.width,
			height:rect.height,
			pageCoords:[[pageX, pageY],[pageX+rect.width, pageY+rect.height]]
		});
	}
	static fromMapCoords(map, coords){
		let mapContainerRect = map.container.getElement().getBoundingClientRect();
		let pageCoords = coords.map((coord)=>MapConverter.getPageOffset(map, coord));
		let x = pageCoords[0][0];
		let y = pageCoords[1][1];
		let result = new RestrictionAreaRect({
			x:x-document.documentElement.scrollLeft-mapContainerRect.x,
			y:y-document.documentElement.scrollTop-mapContainerRect.y,
			width:pageCoords[1][0]-x,
			height:pageCoords[0][1]-y,
			pageCoords
		});
		return result;
	}
}
</script>

<style lang="scss">
.restriction-control{
	position: absolute;
	background: #00000039;
	width: 100%;
	height: 100%;
	top: 0;
	pointer-events: none;
	&>*{ pointer-events: auto; }
	.custom-panel{
		position: absolute;
		top: 300px;
		left: 50px;
	}
	.restriction-control-bounds{
		position: absolute;
		transform-origin: center;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		border: 3px dashed rgba(0, 0, 255, 0.232);
	}
	.actions{
		position: absolute;
		bottom: 20px;
		right: 25px;
		display: flex;
		gap: 5px;
		.apply-button{
			min-height: 30px;
			background-color: lightgreen;
			border: 1px solid #767676;
			border-radius: 2px;
			&:hover{
				background-color: #89e389;
				border-color: #666666;
			}
			&:active{
				background-color: #93f793;
				border-color: #757575;
			}
		}
	}
}
</style>