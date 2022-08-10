<template>
	<div class="waves-report">
		<div class="clients-list" v-show="!editMode">
		Нажмите "применить", чтобы отфильтровать.<br>
		<label>ID:</label>
		<input type="number" v-model="clientsLimit.id" />
		<label>Имя:</label>
		<input type="text" v-model="clientsLimit.client" />
		<input type="button" value="Применить" @click="update(true)">
		<input type="button" value="Сброс" @click="update()">
		<div>
			<input type="button" value="Добавить" @click="editMode=!editMode">
		</div>
		<table v-if="!updating && clients.length>0" class="report-table">
			<thead>
				<td>ID</td>
				<td>Клиент</td>
				<td>Телефон</td>
				<td>Адрес</td>
				<td>Примечание</td>
				<td>Действие</td>
				<!-- <td></td> -->
			</thead>
			<tbody>
				<tr v-for="client in clients" :key="client.id" :client="client">
					<td>{{client.id}}</td>
					<td>{{client.client}}</td>
					<td>{{client.phone}}</td>
					<td>{{client.address}}</td>
					<td>{{client.note}}</td>
					<td><input type="button" value="Выбрать" @click="$emit('select',client)"></td>
				</tr>
			</tbody>
		</table>
		<span v-if="!updating && !clients.length" style="color:#931515; font-weight:bold;">Нет клиентов для заданного критерия отбора.</span>
		<circle-loading v-if="updating" :radius="41" :ringWeight="14"></circle-loading>
		</div>
		<div class="client-edit"  v-show="editMode">
			<div class="client-edit-row">
				<label>ID:</label>{{clientEdit.id}}
			</div>
			<div class="client-edit-row">
				<label>Клиент:</label>
				<input type="text" v-model="clientEdit.client" />
			</div>
			<div class="client-edit-row">
				<label>Телефон:</label>
				<input type="text" v-model="clientEdit.phone" />
			</div>
			<div class="client-edit-row">
				<label>Адрес:</label>
				<input type="text" v-model="clientEdit.address" />
			</div>
			<div class="client-edit-row">
				<label>NIP:</label>
				<input type="text" v-model="clientEdit.nip" />
			</div>
			<div class="client-edit-row">
				<label>Примечание:</label>
				<input type="text" v-model="clientEdit.note" />
			</div>
			<div class="client-edit-row">
				<input type="button" value="Отмена" @click="editMode=!editMode">
				<input type="button" value="Сохранить" @click="clientSave();editMode=!editMode">
			</div>
		</div>
	</div>
</template>

<script>

import circleLoading from '../UI/mini/circle-loading.vue';
export default {
  components: { circleLoading },
	computed:{
		reportWaves(){
			return this.groupWaves(this.ordersLimit.waves)
		}
	},
	data(){
		return{
			clients:[],
			updating:false,
			clientsLimit:{"id":0,"client":''},
			clientEdit:{"id":0,"client":''},
			editMode: false
		}
	},
	mounted(){
		this.getClients();
  	},
	methods:{
		async update(withSelectedDate){
			this.updating = true;
			try{
				alert("not ready");
			} catch(e){	console.error(e); }
			this.updating = false;
		},
		getClients(){
			this.updating = true;
			axios
        .get('/getClients', {
          headers: {'X-Access-Token': Globals.api_token, "content-type": "application/json"},
          params: {
            
          },
        },
        {headers: {'X-Access-Token': Globals.api_token, "content-type": "application/json"}})
        .then(response => {
			this.updating = false;
          if(response.data.code==200){
            this.clients = response.data.clients;
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
		clientSave(client){
			axios
        .post('/saveClient', {
          headers: {'X-Access-Token': Globals.api_token, "content-type": "application/json"},
          params: {
            client:{
              ...client
              
            },
          },
        },
        {headers: {'X-Access-Token': Globals.api_token, "content-type": "application/json"}})
        .then(response => {
          if(!response.data.error && response.data.code==200){
            this.clients.push(response.data.client);
            resolve(response);
          }
          else {
            reject({ type:"thrown", response });
          }
        })
        .catch(error => {
          reject({ type:"catched", response:error });
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
.client-edit-row{
	padding: 5px;
}
</style>