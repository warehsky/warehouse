<template>
<div class="table-wrapper">
	<div class="filters">
		<button v-for="filter in filters" :key="filter.title"
			class="filter"
			:class="{ selected:selectedFilter == filter }"
			@click="selectedFilter = filter">
			{{filter.title}}
		</button>
	</div>
	<div class="select-table">
		<table class="table">
			<thead>
				<th style="width:150px"><button @click="selected = (!selected.isItems(filteredItems)?selected.concat(filteredItems.except(selected)):selected.remove(...filteredItems))">Выбрать все</button></th>
				<slot name="columns"></slot>
			</thead>
			<tbody>
				<tr :class="{ 'selected':selected.includes(value) }" v-for="(value,index) in values" :key="index" v-show="(selectedFilter && selectedFilter.condition)?selectedFilter.condition(value):true">
					<td style="text-align:center"><input type="checkbox" :checked="selected.includes(value)" @change="$event.target.checked?selected.push(value):selected.remove(value)"/></td>
					<slot name="raw" :value="value"></slot>
				</tr>
			</tbody>
		</table>
	</div>
	<div>
		<button @click="$emit('select',selected); selected = []">Загрузить</button>
		<button @click="$emit('select',null)">Отмена</button>
	</div>
	</div>
</template>

<script>
export default {
	name:"select-table",
	props:{
		values:{
			type:Array
		},
		filters:{
			type:Array,
			default(){
				return [];
			}
		}
	},
	data(){
		return{
			selected:[],
			selectedFilter:null
		}
	},
	computed:{
		filteredItems(){
			return this.values.filter(this.selectedFilter?.condition || (()=>true));
		},
		cssVars(){
			return {
				'--columns-count':this.properties.length,
				'--raws-count':this.values.length+1
			}
		}
	},
	beforeMount(){
		this.selectedFilter = this.filters[0];
	},
	mounted(){
		window.addEventListener("keydown",(e)=>{ if(e.code == "Escape") this.$emit('select',null); })
	}
}
</script>

<style lang="scss">
.table-wrapper{
	display: flex;
    flex-direction: column;
    overflow-y: hidden;
	height: 100%;
	width: 800px;
	max-width: 90vw;
}
.filters{
	min-height: 30px;
    width: 100%;
	.filter{
		padding: 1px 20px;
		margin: 0 2px;
		border: 1px solid #757575;
		border-radius: 8px;
		cursor: pointer;
		&:hover{
			background: #dedede;
		}
		&.selected{
			background: #b1b1b1;
		}
	}
}
.select-table{
	height: 100%;
	max-height: 100%;
	overflow: hidden;
	overflow-y: auto;
}
.table{
	width: 100%;
	border-collapse: collapse;
	th,td{
		border: 1px solid black;
	}
	tr{
		&:hover{
			background: rgb(219, 219, 219);
		}
		&.selected{
			background: rgba(0, 128, 0, 0.452);
		}
	}
}
</style>