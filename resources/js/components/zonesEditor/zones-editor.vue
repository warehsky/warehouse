<template>
	<div class="zones-editor">
		<div ref="map" id="editor-map"></div>
		<restriction-control ref="restrictionControl" :map="myMap"></restriction-control>
	</div>
</template>

<script>
import { loadYmap } from "vue-yandex-maps";
import RestrictionControl from "./restriction-control.vue";
import MapConverter from "./map-converter.js";
export default {
  components: { RestrictionControl },
	name:"zones-editor",
	data(){
		return{
			coords:[37.80329, 48.003406],
			ymapSettings:{
				apiKey:"2d081426-c403-4005-ac1e-6b911b5638a4",
				lang:"ru_RU",
				coordorder:"longlat",
				version:"2.1",
			},
			ymapsReady:false,
			editRestriction:false,
			myMap:null
		}
	},
	mounted(){
		loadYmap({ ...this.ymapSettings, debug: true }).then((e)=>{
			this.myMap = new ymaps.Map("editor-map", {
				center: this.coords,
				zoom: 10,
				controls: [
					//"zoomControl","searchControl","typeSelector","geolocationControl","fullscreenControl","trafficControl","rulerControl"
					"zoomControl",
					"fullscreenControl",
					"typeSelector",
					"searchControl"
				],
			});
			this.$nextTick(()=>this.$emit("ymapsLoad"));
			// this.myMap.options.set('maxAnimationZoomDifference', Infinity);
			ymaps.ready((...params)=>{
				this.ymapsReady = true;
				this.myMap.events.add("actiontick",(e)=>{ this.$emit("actiontick",e) })
				this.myMap.events.add("boundschange",(e)=>{ this.$emit("actiontick",e) })
			});
		}).catch((e)=>{ console.error(e); });
	},
	methods:{
		/**
		 * @param { Array<Array<Number>> } mapRect
		 */
		showRestrictionControl(mapRect){
			return this.$refs.restrictionControl?.show(mapRect);
		},
		showRestriction(coordinates, timeout=3000){
			return new Promise((resolve, reject)=>{
				let timeoutToken;
				let onload = ()=>{
					clearTimeout(timeoutToken);
					this.$refs.restrictionControl.resetRestrictionArea(coordinates);
					this.$refs.restrictionControl.focusArea(1);//1 - на единицу ближе
					resolve();
				}
				timeoutToken = setTimeout(()=>{
					this.$off("ymapsLoad",onload);
					reject(new Error("timeout error"));
				},timeout);
				if(this.ymapsReady) onload();
				else this.$once("ymapsLoad",onload);
			});
		},
		getPolygonsFromFile(File){
			let reader = new FileReader();
			return new Promise((resolve,reject)=>{
				reader.onload = ()=>{
					let result = [];
					ymaps.geoQuery(reader.result).each((item)=>{
						result.push({
							geometry:item.geometry.getCoordinates(),
							options:item.options.getAll(),
							properties:item.properties.getAll()
						});
					});
					resolve(result);
				}
				reader.onerror = function() {
					console.error(reader.error);
					reject(reader.error);
				};
				reader.readAsText(File);
			})
		},
		loadPolygon(geometry,options,properties){
			return new Promise((resolve,reject)=>{
				let polygon = new ymaps.Polygon(geometry);
				if(options)
					polygon.options.set({ ...options, visible:true, editorMinPoints: 1, });
				if(properties)
					polygon.properties.set({...properties, hover:false });
				polygon.events.add("mouseenter",()=>{ polygon.properties.set("hover",true) });
				polygon.events.add("mouseleave",()=>{ polygon.properties.set("hover",false) });
				let newZone = ymaps.geoQuery(polygon).addToMap(this.myMap);
				this.zones = this.zones?this.zones.add(newZone):newZone;
				this.$emit("load",this.zones);
				resolve(this.zones.get(this.zones.getLength()-1));
			});
		},
		removePolygon(polygon){
			this.myMap.geoObjects.remove(polygon);
			this.zones = ymaps.geoQuery(this.myMap.geoObjects);
			this.$emit("load",this.zones);
		},
		focusZone(index){
			let zone = ymaps.geoQuery(this.zones.get(index));
			this.myMap.setZoom(zone.getMaxZoom(this.myMap)-2);
			this.myMap.setCenter(zone.getCenter(this.myMap));
			// this.myMap.
		},
		fitMap(){
			this.$refs.restrictionControl.update();
			return new Promise((resolve, reject)=>{
				if(this.ymapsReady) {
					this.myMap.container.fitToViewport();
					resolve(this.$refs.map.getBoundingClientRect());
				} else reject(new Error("ymaps is not ready"));
			})
		},
		showZone(index,value){
			this.zones.get(index).options.set('visible',value);
		},
		getRelativeRect(zone){
			let rect = this.$el.getBoundingClientRect();
			let zoneBounds = window.zoneBounds = ymaps.geoQuery(zone).getCenter(this.myMap);
			let point1 = MapConverter.getPageOffset(this.myMap, zoneBounds);
			let top = point1[1]-rect.top - document.documentElement.scrollTop;
			let left = point1[0]-rect.x - document.documentElement.scrollLeft;
			return { top, left }
		}
	}
}
</script>

<style lang="scss">
.zones-editor{
	width: 100%;
	max-width: 100%;
	overflow: hidden;
	position: relative;
	#editor-map{
		width: 100%;
		height: 600px;
	}
}
</style>