<template>
	<div id="zones-editor-page">
		<div style="display:flex;">
			<zones-loader ref="loader" class="zones-loader"
				:items="items"
				:options="availableOptions"
				@select="loadPolygons"
				@create="createPolygon"
				@select-file="loadPolygonsFrom"
				@before-save="select(-1)"
				@save-item="setItemState($event,'new',false); setItemState($event,'edited',false);"
				@remove-item="select(-1); $refs.editor.removePolygon($event);">
			</zones-loader>
			<div class="load-variant">
				<h3>Ограничение вида</h3>
				<div class="subvariants column">
					<div class="subvariant" style="display: flex; gap: 4px;">
						<async-button :disabled="!restrictionCoordinates || restrictionEdit" @click="resetRestriction" :handle="['wait']">
							Задать ограничение
						</async-button>
						<async-button :disabled="!restrictionCoordinates || restrictionEdit" @click="saveRestriction">Выгрузить</async-button>
					</div>
				</div>
			</div>
			<div class="help-block">
				<a @click="help = !help">Помошь</a><br>
				<float-panel :opened="help">
					<b>Добавление полигонов</b><br>
					<p>
						Для добавления полигона из бызы данных, необходимо нажать на кнопку "Из базы" в блоке "Загрузить".
						Появится окно с таблицей.
						При добавлении полигона в таблице отмечается наличие геометрии.
						Если геометрия отсутствует, то для для этого полигона по умолчанию будет создана точка в центре карты.
						Для того, чтобы ее увидеть, необходимо навести курсор на соответствующий элемент в боковой панели, 
						после чего появится линия, указывающая на центральную точку геометрии полигона.
					</p>
					<b>Создание полигонов</b><br>
					<p>
						Чтобы создать полигон необходимо нажать кнопку "Соту" или "Зону" в блоке "Создать", после чего будет
						создан новый полигон соответствующего типа ввиде точки в центре карты. Сразу после её создания вы перейдете в режим "рисования".
						Чтобы завершить рисование, нажмите на точку, в появившемся меню нажмите "Завершить". Затем нажмите на изображение карандаша на соответствующем элементе боковой панели.
					</p>
					<b>Параметры полигонов</b><br>
					<b>Для сот:</b>
					<ul>
						<li>По умолчанию сота имеет синий цвет с высокой прозрачностью.</li>
						<li>Изменения параметров, касающихся стиля (цвет(Fill Color), прозрачность(Fill Opacity), цвет обводки(Stroke Color), прозрачность обводки(Stroke Opacity)) не сохраняются при выгрузке.</li>
					</ul>
					<b>Для зон:</b>
					<ul>
						<li>По умолчанию зона имеет зеленый цвет с невысокой прозрачностью.</li>
						<li>Все изменения, касающиеся зон созраняются при выгрузке.</li>
					</ul>
				</float-panel>
				*Клик правой кнопкой мыши по полигону открывакт его контекстное меню.
			</div>
		</div>
		<div class="editor-container">
			<panel v-show="items" ref="panel" contentClass="content-wrapper" :pivot="['right']" :height="600" :width="400" :minWidth="0" :maxWidth="565" @resize="onResize">
				<div class="zones-panel-content">
					<div class="zones-list">
						<div class="list-title">
							<div class="tabs">
								<button v-for="tab in tabs" :key="tab.title"
									class="tab"
									:class="{ selected:selectedTab==tab, disabled:!getItemsCount(tab.types) }"
									@click="selectedTab=tab; $nextTick(()=>updateLines())">
									{{ tab.title }} ({{ getItemsCount(tab.types) }})
								</button>
							</div>
						</div>
						<div class="zones-list-items" v-if="items" @scroll="updateLines()">
							<zone-list-item	v-for="index in items.getLength()" :key="index-1"
								v-show="selectedTab.types.includes(items.get(index-1).properties.get('type'))"
								ref="zoneItem"
								class="zone-item"
								:class="{ 'selected':(selected.index== index-1), 'editable':(zoneEditingIndex==index-1) }"
								:item="items.get(index-1)"
								:options="items.get(index-1).options.getAll()"
								:properties="items.get(index-1).properties.getAll()"
								@inspect="$refs.editor.focusZone(index-1)"
								@edit="editZone(index-1,!$event)"
								@hide="$refs.editor.showZone(index-1,!$event)"
								@click.native="select(index-1); updateLines()"
								@poly-contextmenu="$refs['poly-context'].open($event.get('clientPixels'),index-1)"
								@mouseenter="mark(index-1); updateLines()"
								@mouseleave="mark(-1); updateLines()"
								@change="items.get(index-1).options.set($event.name,$event.new)"
								@delete="setItemDeleteState(items.get(index-1),$event)">
							</zone-list-item>
						</div>
					</div>
					<div class="props-list" v-if="options.length>0" :class="{ disabled:zoneEditingIndex!=-1 }">
						<div class="list-title" style="text-align: center;">Параметры</div>
						<div class="props-list-items">
							<property 
								ref="prop"
								v-for="(prop) in options.filter((opt)=>availableOptions.includes(opt[0]))"
								:key="prop[0]"
								class="zone-prop"
								:name="camelPad(prop[0])"
								v-model="prop[1]"
								:range="prop[0].toLowerCase().includes('opacity')"
								:min="prop[0].toLowerCase().includes('opacity')?0:null"
								:max="prop[0].toLowerCase().includes('opacity')?1:null"
								:step="0.01"
								@input="updateProperties(); updateChangesState(selected.item); updateLines();"
							/>
						</div>
					</div>
				</div>
			</panel>
			<zones-editor ref="editor" @load="updateItems($event)" @actiontick="updateLines()"></zones-editor>
			<!-- <canvas class="pointer-lines-canvas"></canvas> -->
			<pointer-lines-canvas ref="canvas" :lines="lines" :width="canvasRect.width" :height="canvasRect.height"></pointer-lines-canvas>
		</div>
		<context-menu class="poly-context" ref="poly-context">
			<div @click="select($refs['poly-context'].detaild); $nextTick(()=>updateLines())">
				<img src="/img/icons/cursor-select.svg">
				Выбрать
			</div>
			<div @click="scrollTo($refs['poly-context'].detaild)">
				<img src="/img/icons/search-in-list.svg">
				Найти в списке
			</div>
			<div @click="editZone($refs['poly-context'].detaild,zoneEditingIndex!=$refs['poly-context'].detaild); updateLines()">
        <img src="/img/icons/pen-edit.svg" title="Редактировать зону">
				{{$refs['poly-context']&&zoneEditingIndex!=$refs['poly-context'].detaild?"Редактировать":"Завершить редактирование"}}
			</div>
			<div @click="$refs.editor.showZone($refs['poly-context'].detaild, false); editZone($refs['poly-context'].detaild,false); updateLines()">
				<img src="/img/icons/eye-off.svg" title="Скрыть зону">
				Скрыть
			</div>
		</context-menu>
	</div>
</template>

<script>
import Panel from '../UI/panels/panel/panel.vue'
import ContextMenu from '../UI/inputs/context-menu.vue'
import FloatPanel from '../UI/panels/float-panel.vue'
import PointerLinesCanvas from './pointer-lines-canvas.vue'
import PolygonSettings from './PolygonSettings'
import Property from './property.vue'
import ZonesLoader from './zones-loader.vue'
import ItemStateManager from './ItemStateManager.js';
import ZoneListItem from './zone-list-item.vue'
import Repository from '../../classes/Repository/Repository'
import AsyncButton from '../UI/mini/async-button.vue'
export default {
	name:"zones-editor-page",
	components: { 
		Panel, 
		Property, 
		PointerLinesCanvas, 
		ZonesLoader, 
		FloatPanel, 
		ContextMenu,
		ZoneListItem,
		AsyncButton
	},
	data(){
		return {
			items:null,
			lines:[],
			canvasRect:{
				width:0,
				height:0
			},
			options:[],
			selected:{
				index:-1,
				item:null
			},
			marked:{
				index:-1,
				item:null
			},
			zoneEditingIndex:-1,
			availableOptions:[
				"description",
				"fillColor",
				"fillOpacity",
				"strokeColor",
				"strokeOpacity"
			],
			tabs:[
				{ title:'Все', types:['sot','zone'] },
				{ title:'Соты', types:['sot'] },
				{ title:'Зоны', types:['zone']}
			],
			selectedTab:null,
			help:false,
			restrictionCoordinates:null,
			restrictionEdit:false,
		}
	},
	beforeMount(){
		this.selectedTab = this.tabs[0];
	},
	mounted(){
		this.showRestrictionArea();
		this.$watch(()=>this.items,()=>{ if(this.items) this.onResize(); });
		window.addEventListener("resize",()=>{this.onResize(); });
	},
	methods:{
		async showRestrictionArea(){
			this.restrictionCoordinates  = await this.fetchRestriction()
			this.$refs.editor.showRestriction(this.restrictionCoordinates);
		},
		async fetchRestriction(){
			let restrictionRectString = await Repository.getOption("mapSize");
			if(!restrictionRectString) return;
			return JSON.parse(restrictionRectString);
		},
		saveRestriction(){
			return Repository.setOption("mapSize", JSON.stringify(this.restrictionCoordinates));
		},
		async resetRestriction(){
			this.restrictionEdit = true;
			this.restrictionCoordinates = await this.$refs.editor.showRestrictionControl(this.restrictionCoordinates);
			this.restrictionEdit = false;
		},
		// handlers
		loadPolygons(polygonsData){
			polygonsData.forEach((poly)=>{
				//если нет геометрии, передаем центр карты
				let center = this.$refs.editor.myMap.getCenter();
				let geometry = poly.geometry||[[center,center]];
				let items = ymaps.geoQuery(this.items);//проходим по копии колекции и проверяем на совпадение по типу и id
				let count = items.getLength();
				for(let i = 0; i<count; i++){
					let item = items.get(i)//элемент, загруженный на карту
					let newId = poly.properties.id;
					if(item.properties.get("type")!=poly.properties.type || item.properties.get("id")!=newId)
						continue;
					let qt = `Идентификатор полигона #${newId}:"${poly.options.description}"\n`+
					`из файла ${File.name}\n`+
					`совпадает с идентификатором полигона ${item.options.get('description')}.\n\n`+
					`Хотите перезаписать геометрию полигона ${item.options.get('description')}`+
					`на геометрию полигона полученного из файла?`
					if(confirm(qt)){
						item.geometry.setCoordinates(poly.geometry);
						this.updateItems(items);
						return;
					} else break;
				}
				this.$refs.editor.loadPolygon(geometry,poly.options,poly.properties).then((item)=>{
					ItemStateManager.set(item,"deleted",poly.data?.deleted);
					item.properties.set("old_geometry",JSON.stringify(geometry));
					this.updateChangesState(item);
				});
			});
		},
		loadPolygonsFrom(type,file){
			if(!file) throw new Error("Parameter \"file\" is required.");
			this.$refs.editor.getPolygonsFromFile(file).then((polygons)=>{
				let description = this.getPolygonDefaultSettings(type,file.name).options.description;
				polygons.forEach((poly)=>poly.properties.description = poly.properties.description || description);
				this.$refs.loader.customSelect(type,polygons);
			});
		},
		createPolygon(type){
			let settings = this.getPolygonDefaultSettings(type,"Редактор");
			let center = this.$refs.editor.myMap.getCenter();
			this.$refs.editor.loadPolygon([[center,center]], settings.options, settings.properties)
			.then((item)=>{
				this.drawZone(this.items.getLength()-1,true);
				item.properties.set("old_geometry",JSON.stringify(item.geometry.getCoordinates()));
				this.setItemState(item,"new",true);
			});
		},
		updateItems(items){
			if(items)
				this.items = items;
			this.items.each((item)=>{
				this.updateChangesState(item);
				if(item.properties.get("id")>=0) return;
				item.properties.set("old_geometry",JSON.stringify(item.geometry.getCoordinates()));
				this.setItemState(item, "new", true);
			});
		},
		// handlers end
		getPolygonDefaultSettings(type,from){
			if(type != "sot" && type != "zone")
				throw new Error("Недопустимый тип полигона");
			let isSot = type == "sot"
			let description = (isSot?"Сота":"Зона")+" [ "+from+" ]";
			return PolygonSettings.getDefault(type,{ description },isSot?['geometry','description']:undefined);
		},
		select(index){
			let item = this.items.get(index);
			if(!item) return;
			this.options = index>=0?Object.entries(item.options.getAll()):[];
			this.selected = { index, item:item||null }
			if(index>=0)
				this.$nextTick(()=>{ this.$refs.prop.forEach((p)=>{ p.update() }) })
		},
		mark(index){
			if(index>=0)
				this.marked = { index, item:this.items.get(index) };
			else this.marked = { index, item:null }
		},
		updateProperties(){
			if(this.selected.index<0)
				return;
			this.selected.item.options.set(Object.fromEntries(this.options));
		},
		updateChangesState(item){
			this.setItemState(item, "edited", PolygonSettings.isChanged({ 
				geometry:item.geometry.getCoordinates(),
				options:item.options.getAll(),
				properties:item.properties.getAll() 
			}));
		},
		editZone(index,value){
			if(this.zoneEditingIndex>=0)
				this.$refs.zoneItem[this.zoneEditingIndex].editZone(false);
			this.$refs.zoneItem[index].editZone(value);
			this.zoneEditingIndex=value?index:-1;
			if(value) this.select(index);
			let item = this.items.get(index);
			item.geometry.events.add("change",()=>{ this.updateChangesState(item) });
		},
		drawZone(index,value){
			if(this.zoneEditingIndex>=0)
				this.$refs.zoneItem[this.zoneEditingIndex].drawZone(false);
			this.$refs.zoneItem[index].drawZone(value);
			this.zoneEditingIndex=value?index:-1;
			this.select(index);
			let item = this.items.get(index);
			item.geometry.events.add("change",()=>{ this.updateChangesState(item) });
		},
		onResize(){
			if(this.$refs.editor.ymapsReady)
				this.$refs.editor.fitMap()
				.then((rect)=>{
					this.canvasRect.width = rect.width;
					this.canvasRect.height = rect.height;
				})
				.catch((e)=>{
					this.canvasRect.width = 0;
					this.canvasRect.height = 0;
				});
			this.$nextTick(()=>this.updateLines());
		},
		updateLines(){
			let types = ["marked","selected"];
			if(this.zoneEditingIndex>=0)
				types.remove("selected");
			let lines = [];
			types.forEach((type,index)=>{
				let item = this[type];
				let component = null;
				if(item.index>=0 && this.hasOwnProperty(type) && (component = this.$refs.zoneItem[item.index])){
					let element = component.$el;
					let zoneRect = this.$refs.editor.getRelativeRect(this.items.get(item.index));
					lines.push({
						type,
						points:[
							[ element.offsetLeft, element.offsetTop-element.parentElement.scrollTop+element.offsetHeight/2 ],
							[ zoneRect.left, zoneRect.top ]
						],
						options:{
							//выключаем линию, если элемент скрыт, находится за пределами контейнера или его тип не содержится в списке типов выбранной вкладки.
							disabled:!item.item.options.get("visible") || (type != "selected" && !component.insideParent()) || !this.selectedTab.types.includes(item.item.properties.get("type")),
							strokeStyle: item.item.options.get("fillColor","#1A78EE"),
							lineWidth:4
						}
					});
				}
				this.lines = lines;
			});
			this.$nextTick(()=>{ this.$refs.canvas.update()});
		},
		setItemState(item,state,value){
			ItemStateManager.set(item,state,value);
		},
		setItemDeleteState(item,value){
			if(item.properties.get("id")==-1){//если полигон не был сохранен в базу, то сразу удаляем
				this.editZone(0,false);
				this.$refs.editor.removePolygon(item);
				this.items.remove(item);
				return;
			}
			item.options.set('deleted',value);
			this.setItemState(item,'deleted',value);
			this.updateChangesState(item);
		},
		getItemsCount(types){
			return this.items?.search((i)=>types.includes(i.properties.get("type"))).getLength()
		},
		scrollTo(index){
			if(this.selected.index == index) return;
			let component = this.$refs.zoneItem[index];
			component.searchInList();
		}
	}
}
</script>

<style lang="scss">
.zones-loader{
	width: max-content;
}
.help-block{
	a{
		height: fit-content;
		user-select: none;
		cursor: pointer;
	}
	.float-panel{
		width: auto;
		height: auto;
		max-height: 50vh;
		z-index: 100000;
		overflow-y: auto;
	}
}
.sm-polys .modal-contents{
	width: fit-content;
}
.content-wrapper{
	background: #efefef;
  border: 1px solid #a7a7a7;
}
.zones-panel-content{
	height: 600px;
	display: flex;
	flex-direction: column;
}
.editor-container{
	position: relative;
	display:flex;
	flex-direction: row-reverse;
	justify-content: space-between;
}
.zones-list-items{
	background: #efefef;
	overflow-x: hidden;
	overflow-y: auto;
	&::before, &::after{
		content: "";
    display: block;
    height: 5px;
	}
}
.zone-item{
	padding: 4px;
	border-radius: 2px;
	user-select: none;
	&:hover{
		background: #d4d4d4;
	}
	&.selected, &.editable{
		position: sticky;
		top: 0;
		bottom: 0;
		z-index: 1;
		box-shadow: 0px 2px 3px 0px #0000006b;
		cursor: default;
	}
	&.selected{
		background: #b4b4b4;
	}
	&.editable{
		background: #ffa8a8;
	}
}
.list-title{
	background: #9b9b9b;
	border: 1px solid #a7a7a7;
	border-bottom:none;
	// text-align: center;
	padding: 3px 0px;
	padding-bottom:0px;
	user-select: none;
	font-weight: bold;
	color: #464646;
	height: 31px;
	min-height: 31px;
	overflow: hidden;
	.tabs{
		height: 100%;
		width: max-content;
	}
	.tab{
		height: 100%;
    border: 1px solid #a7a7a7;
    border-radius: 8px 8px 0px 0px;
		filter: contrast(0.8);
		cursor: pointer;
		&.selected{
			border-bottom: 0px;
		}
		&.disabled{
			filter: contrast(0.5);
			pointer-events: none;
		}
		&:hover, &.selected{
			background: #EFEFEF;
			filter: none;
		}
	}
}
.zones-list{
	overflow: hidden;
	height: 100%;
	display: flex;
	flex-direction: column;
}
.props-list{
	flex: none;
	max-height: 50%;
	display: flex;
  flex-direction: column;
	overflow-x: hidden;
	&.disabled{
		cursor: not-allowed;
		.props-list-items{
			filter: brightness(0.8) grayscale(1);
			pointer-events: none;
			user-select: none;
		}
	}
	.props-list-items{
		background: #dddddd;
		overflow: hidden;
		min-width: max-content;
		overflow-y: auto;
    max-height: 100%;
	}
}
.zone-prop{
	display: flex;
	justify-content: space-between;
	padding: 4px;
	.prop-name{
		white-space: nowrap;
	}
	&:hover{
		background: #c7c7c7;
	}
}
.zones-editor-page{
	.circle-loading {
		margin: 250px auto;
		.loader{
			border-color: rgb(140 140 140 / 20%);
			border-left-color: #931515;
		}
	}
}
.poly-context{
	img{
		width: 16px;
		margin-right: 8px;
	}
}
</style>