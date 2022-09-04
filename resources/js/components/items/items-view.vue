<template>
	<div class="waves-report">
		<div class="items-list" v-show="!editMode">
		Нажмите "применить", чтобы отфильтровать.<br>
		<label>ID:</label>
		<input type="number" v-model="itemsLimit.id" />
		<label>Имя:</label>
		<input type="text" v-model="itemsLimit.item" />
		<input type="button" value="Применить" @click="update(true)">
		<input type="button" value="Сброс" @click="update()">
		<div>
			<input type="button" value="Добавить" @click="editMode=!editMode">
		</div>
		<table v-if="!updating && items.length>0" class="report-table">
			<thead>
				<td>ID</td>
				<td>Наименование</td>
				<td>Действие</td>
				<!-- <td></td> -->
			</thead>
			<tbody>
				<tr v-for="item in items" :key="item.id" :client="item">
					<td>{{item.id}}</td>
					<td>{{item.item}}</td>
					<td><input class="btn-choose" type="button" value="Выбрать" @click="$emit('select',item)"></td>
				</tr>
			</tbody>
		</table>
		<span v-if="!updating && !items.length" style="color:#931515; font-weight:bold;">Нет услуг для заданного критерия отбора.</span>
		<circle-loading v-if="updating" :radius="41" :ringWeight="14"></circle-loading>
		</div>
		<div class="item-edit"  v-show="editMode">
			<div class="item-edit-row">
				<label>ID:</label>{{itemEdit.id}}
			</div>
			<div class="item-edit-row">
				<label>Наименование:</label>
				<input type="text" v-model="itemEdit.item" />
			</div>
			
			<div class="client-edit-row">
				<input type="button" value="Отмена" @click="editMode=!editMode">
				<input type="button" value="Сохранить" @click="itemSave(itemEdit);editMode=!editMode">
			</div>
		</div>
	</div>
</template>

<script>

import circleLoading from '../UI/mini/circle-loading.vue';
export default {
  components: { circleLoading },
	computed:{
		
	},
	data(){
		return{
			items:[],
			updating:false,
			itemsLimit:{"id":0,"client":''},
			itemEdit:{"id":0,"client":''},
			editMode: false
		}
	},
	mounted(){
		this.getItems();
  	},
	methods:{
		async update(withSelectedDate){
			this.updating = true;
			try{
				alert("not ready");
			} catch(e){	console.error(e); }
			this.updating = false;
		},
		getItems(){
			this.updating = true;
			axios
        .get('/getItems', {
          headers: {'X-Access-Token': Globals.api_token, "content-type": "application/json"},
          params: {
            
          },
        },
        {headers: {'X-Access-Token': Globals.api_token, "content-type": "application/json"}})
        .then(response => {
			this.updating = false;
          if(response.data.code==200){
            this.items = response.data.items;
          }
          else {
            reject({ type:"thrown", response });
          }
        })
        .catch(error => {
			this.updating = false;
          reject({ type:"catched", response:error });
        })
      
		},
		itemSave(item){
			axios
			.post('/saveItem', {
			headers: {'X-Access-Token': Globals.api_token, "content-type": "application/json"},
			params: {
				item:{
				...item
				
				},
			},
			},
			{headers: {'X-Access-Token': Globals.api_token, "content-type": "application/json"}})
			.then(response => {
			if(!response.data.error && response.data.code==200){
				this.items.push(response.data.item);
			}
			else {
				alert(response.data.msg);
			}
			})
			.catch(error => {
			alert(error);
			});
			
		}
	}
}
</script>

<style lang="scss">
.report-table {
	margin-top: 5px;
	width: 100%;
	td{
		border: 1px solid;
	}
	thead{
		font-weight: bold;
	}
}
.waves-report {
	min-height: 350px;
	.circle-loading {
		margin: 150px auto;
		.loader{
			border-color: rgb(140 140 140 / 20%);
			border-left-color: #931515;
		}
	}
}
.item-edit-row{
	padding: 5px;
}
.btn-choose{
	height: 20px;
    font-size: 12px!important;
    line-height: 1!important;
}
</style>