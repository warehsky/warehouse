<template>
  <div class="load-variants">
		<div class="load-variant">
			<h3>Загрузить</h3>
			<div class="subvariants column">
				<div class="subvariant">
					<input type="button" value="Из базы" @click="selectPolygons()"	:disabled="uploaded.loading">
				</div>
				<div class="subvariant">
					<b>Из файла</b><br>
					<input id="add-sots" type="radio" value="sot" :disabled="this.uploaded.loading" v-model="filePolygonType" />
					<label for="add-sots">Соты</label>
					<input id="add-zone" type="radio" value="zone" :disabled="this.uploaded.loading" v-model="filePolygonType" />
					<label for="add-zone">Зоны</label><br>
					<input ref="fileSelectInput" type="file" accept=".geojson" value="Select" @input="(e)=>{ $emit('select-file',filePolygonType ,e.target.files[0]); $refs.fileSelectInput.value = '' }" :disabled="this.uploaded.loading"/><br>
				</div>
			</div>
		</div>
		<div class="load-variant">
			<h3>Выгрузить</h3>
			<div class="subvariants column">
				<div class="subvariant">
					<input type="radio" id="sots-upload" :value="'sot'" :disabled="uploaded.loading" v-model="savePolygonsType">
					<label for="sots-upload">Соты</label>
					<input type="radio" id="zones-upload" :value="'zone'" :disabled="uploaded.loading" v-model="savePolygonsType">
					<label for="zones-upload">Зоны</label><br>
					<button @click="savePolygonsSync(savePolygonsType)">Выгрузить</button>
					<div class="subvariant">
						<progress-bar :value="uploaded.value" :error="uploaded.error"></progress-bar>
					</div>
				</div>
			</div>
		</div>
		<div class="load-variant">
			<h3>Создать</h3>
			<div class="subvariants column">
				<div class="subvariant">
					<input type="button" value="Соту" @click="$emit('create', 'sot')"	:disabled="uploaded.loading">
					<input type="button" value="Зону" @click="$emit('create', 'zone')" :disabled="uploaded.loading">
				</div>
			</div>
		</div>
		<simple-modal v-if="selectZonesModal.opened" class="sm-polys">
			<div class="select-zones-modal">
				<h3>Выберите объекты для загрузки</h3>
				<select-table ref="selectTable"
					v-if="!selectZonesModal.customSelect"
					v-show="selectZonesModal.state == 'loaded'" 
					:values="selectPolygonsData"
					:filters="tableFilters">
					<template #columns>
						<th>id</th>
						<th>Тип</th>
						<th>Описание</th>
						<th>Геометрия</th>
						<th>Состояние</th>
					</template>
					<template #raw="{ value }">
						<td>{{value.properties.id}}</td>
						<td>{{value.properties.type=='sot'?"Сота":"Зона"}}</td>
						<td>{{value.options.description}}</td>
						<td :style="{ background:(value.geometry?'#92ff92':'#ffbbbb') }">{{value.geometry?'Есть':'Нет'}}</td>
						<td :style="{ background:(!value.data.deleted?'#92ff92':'#ffbbbb') }">{{!value.data.deleted?'Активна':'Скрыта'}}</td>
					</template>
				</select-table>
				<select-table ref="customSelectTable"
					v-else
					v-show="selectZonesModal.state == 'loaded'" 
					:values="selectPolygonsData">
					<template #columns>
						<th>Данные</th>
						<th>Геометрия</th>
					</template>
					<template #raw="{ value }">
						<td>
							<div>
								<div v-for="prop in Object.entries(value.properties)" :key="prop[0]"><b>{{prop[0]}}</b>:{{prop[1]}}</div>
							</div>
						</td>
						<td :style="{ background:(value.geometry?'#92ff92':'#ffbbbb') }">{{value.geometry?'Есть':'Нет'}}</td>
					</template>
				</select-table>
				<circle-loading v-if="selectZonesModal.state == 'loading'" :radius="41" :ringWeight="14"></circle-loading>
				<div class="get-zones-error" v-if="selectZonesModal.state == 'error'">
						<error-icon class="get-error"></error-icon>
					<!-- <div style="width:100%; padding:auto;">
					</div> -->
					<span>{{selectZonesModal.info.text}}</span>
					<button @click="selectPolygons()">Повторить</button>
				</div>
			</div>
		</simple-modal>
	</div>
</template>

<script>
import SimpleModal from './simple-modal.vue';
import RequestQueue, { Request } from '../../RequestQueue';
import ErrorIcon from '../UI/mini/error-icon.vue';
import ProgressBar from '../UI/mini/progress-bar.vue';
import CircleLoading from '../UI/mini/circle-loading.vue';
import SelectTable from './select-table.vue';
import PolygonSettings from './PolygonSettings.js';
import ItemStateManager from './ItemStateManager.js';
export default {
	name:'zones-loader',
	components:{  SimpleModal, ErrorIcon, ProgressBar, CircleLoading, SelectTable, },
	props:{
		items:{
			required:true
		},
		options:{
			type:Array,
			required:true
		}
	},
	data(){
		return {
			selectZonesModal:{
				opened:false,
				state:'loading',
				customSelect:false,
				info:{
					text:''
				}
			},
			selectPolygonsData:null,
			uploaded:{
				value:0,
				loading:false,
				error:false
			},
			savePolygonsType:'sot',
			filePolygonType:'sot',
			tableFilters:[
				{ title:'Все', condition:(value)=>['sot','zone'].includes(value.properties.type) },
				{ title:'Соты', condition:(value)=>['sot'].includes(value.properties.type) },
				{ title:'Зоны', condition:(value)=>['zone'].includes(value.properties.type) }
			]
		}
	},
	methods:{
		getPolygons(params){
			return new Promise((resolve,reject)=>{
				axios
					.get("/Api/getPoligons",{ params })
					.then(({data})=>{
						let getPolygons = (type,polys)=>{
							return polys.map((poly)=>{ 
								let description = poly.description || ((type=='sot'?"Сота ":"Зона ")+poly.id);
								let geometry = JSON.parse(poly.geometry);
								let deleted = Boolean(poly.deleted);
								return PolygonSettings.getDefault(type,{ ...poly, geometry, description, deleted },type=='sot'?['geometry','description','deleted']:undefined);
							});
						}
						resolve({	zones:getPolygons('zone',data.zones),	sots:data.sots?getPolygons('sot',data.sots):null	}, data);
					})
					.catch((e)=>{ console.error(e); reject(e) });
			})
		},
		savePolygonsSync(type){
			let types = ["sot","zone"];
			if(!types.includes(type))
				throw new Error("parameter \"type\" must be "+types.join(" or ")+".");
			if(!this.items)
				return;
			let items = this.items.search((item)=>{
				let state = ItemStateManager.get(item).name
				return item.properties.get("type")==type && !['default'].includes(state)//выбираем нужные зоны по типу и состоянию изменения
			});
			let count = items.getLength();
			if(!count)
				return;
			let requestQueue = new RequestQueue(1000);
			let step = 100/count;//шаг для индикатора
			this.uploaded.value = 0;
			this.uploaded.error = false;
			this.uploaded.loading = true;
			this.$emit("before-save");
			for(let i = 0;i<count;i++){
				let item = items.get(i);
				let request = new Request("post",(type=="sot"?"/Api/setSotPoligon":"/Api/setZone"),{
					params:{
						id:item.properties.get("id"),
						geometry:JSON.stringify(item.geometry.getCoordinates()),
						...item.options.getAll(),
						deleted:ItemStateManager.get(item).name=="deleted"
					},
					headers:{ "X-Access-Token":Globals.api_token }
				})
				.then((response)=>{
					this.uploaded.value += step;
					let polygon = response.data.sota || response.data.zone;
					switch(response.data.code){
						case(200):{
							console.log(response.data);
							item.properties.set("id", polygon.id);
							this.$emit("save-item",item);
							break;
						}
						case(201):{//полигон с такой геометрией уже существует
							let current = item.options.get("description");
							console.log(this.isEqualBy(item.options.getAll(),polygon,this.options));
							if(this.isEqualBy(item.options.getAll(),polygon,this.options)){
								item.properties.set("id", polygon.id);
								this.$emit("save-item",item);
								return;
							}
							let qn = `Выгрузка полигона "${current}".\n\nПолигон "${current}"\nидентичен существующему полигону:\n"${polygon.description}".\n\nХотите перезаписать?`;
							if(confirm(qn)){
								item.properties.set("id", polygon.id);
								this.savePolygon(type,item).then(()=>{ this.$emit("save-item",item); });
							}
							break;
						}
						default:throw new Error(response.data.message || response.data.error);
					}
				})
				.catch((error)=>{
					console.error(error);
					this.uploaded.error = true
					requestQueue.stop();
				})
				.finally(()=>{ 
					if(i==count-1){
						requestQueue.stop();
						setTimeout(()=>{
							this.uploaded.loading = false;
							if(!this.uploaded.error)
								this.uploaded.value = 0;
						}, 500);
					}
				});
				requestQueue.add(request);
			}
			requestQueue.addEventListener("stop",({byTimeout})=>{ if(byTimeout) this.$emit("error-save");	});
			requestQueue.start();
		},
		savePolygon(type,item){
			if(!item.properties.get("id")) throw new Error("(in savePolygon) id must contains inside item.properties");
			return axios
				.post((type=="sot"?"/Api/setSotPoligon":"/Api/setZone"), {
					id:item.properties.get("id"),
					geometry:JSON.stringify(item.geometry.getCoordinates()),
					...item.options.getAll(),
					deleted:ItemStateManager.get(item).name=="deleted"
				}, { headers:{ "X-Access-Token":Globals.api_token } })
				.then((response)=>{
					if(response.data.code!=200) throw new Error(response.data.message || response.data.error);
				})
				.catch((e)=>{ throw new Error(e); });
		},
		selectPolygons(){
			// let sots = type == 'sot';
			// let param = sots?"sots":"zones";
			let params = ["sots","zones"];
			this.selectZonesModal.opened = true;
			this.selectZonesModal.state = 'loading';
			this.selectZonesModal.info = { text:"" };
			this.selectZonesModal.customSelect = false;
			this.getPolygons({sotId:-1})
			.then((data)=>{
				this.selectZonesModal.state = 'loaded';
				let items = params.reduce((f,s)=>data[f].concat(data[s])); 
				this.selectPolygonsData = !this.items?items:items.filter((poly)=>{
					return !this.items.search((item)=>item.properties.get("id")==poly.properties.id).getLength();
				});
				this.$refs.selectTable.$once("select",(selected)=>{
					this.selectZonesModal.opened = false;
					this.selectZonesModal.state = 'loading';
					if(selected) this.$emit("select", selected);
					this.selectPolygonsData = null;
				})
			})
			.catch((e)=>{
				console.error(e);
				this.selectZonesModal.info.text = e.message;
				this.selectZonesModal.state = 'error';
				this.$refs.selectTable.$once("select",()=>{ this.selectZonesModal.opened = false; })
			})
		},
		customSelect(type,data){
			this.selectZonesModal.opened = true;
			this.selectZonesModal.state = 'loaded';
			this.selectZonesModal.info = { text:"" };
			this.selectZonesModal.customSelect = true;
			this.selectPolygonsData = data;
			this.$nextTick(()=>{
				this.$refs.customSelectTable.$once("select",(selected)=>{
					this.selectZonesModal.opened = false;
					this.selectZonesModal.state = 'loading';
					if(selected) 
						this.$emit("select", selected.map((item)=>{
							return PolygonSettings.getDefault(type, { geometry:item.geometry, description:item.properties?.description, id:item.properties?.id || "" });
						}));
					this.selectPolygonsData = null;
				})
			})
		},
		isEqualBy(item,item2,props){
			if(props)
				for (const index in props) {
					let prop = props[index];
					if(!item[prop] || !item2[prop]) continue;
					if(!_.isEqual(item[prop], item2[prop]))
						return false;
				}
			return true;
		}
	},
}
</script>

<style lang="scss">
.load-variants{
	display:flex;
}
.load-variant{
	border: 2px solid #c7c7c7;
	border-radius: 4px;
	padding: 4px;
	margin: 0 3px;
	h3{
		padding:2px;
		margin-top:2px;
		margin-bottom: 4px;
	}
	.subvariants{
		display: flex;
		&.column{
			flex-direction: column;
			.subvariant {
				margin: 3px 3px;
				padding: 5px;
			}
		}
		.subvariant{
			border: 1px solid #c7c7c7;
			border-radius: 4px;
			padding: 3px;
			margin: 0 3px;
		}
	}
}
.select-zones-modal{
	margin: 30px;
	display: flex;
  flex-direction: column;
}
.get-zones-error{
	display: flex;
	flex-direction: column;
	width: min-content;
	white-space: nowrap;
	align-items: center;
	margin: auto;
	height: 150px;
	justify-content: space-evenly;
}
.get-error{
	width: min-content;
	position: static;
	transform: scale(1.5);
}
</style>