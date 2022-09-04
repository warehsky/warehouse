<template>
	<div class="waves-report">
		Нажмите "применить", чтобы отфильтровать волны по дате и времени.<br>
		<label>Дата:</label>
		<input type="date" v-model="ordersLimit.date.date" />
		<label>c:</label>
		<input type="time" v-model="ordersLimit.date.timeFrom" />
		<label>по:</label>
		<input type="time" v-model="ordersLimit.date.timeTo"/>
		<input type="button" value="Применить" @click="update(true)">
		<input type="button" value="Сброс" @click="update()">
		<table v-if="!updating && reportWaves.length>0" class="report-table">
			<thead>
				<td>Волна</td>
				<td>Зоны</td>
				<td>Кол-во принятых</td>
				<td>Лимит</td>
				<td>Остаток</td>
				<!-- <td></td> -->
			</thead>
			<tbody>
				<waves-report-raw v-for="wave in reportWaves" :key="wave.wave" :wave="wave"></waves-report-raw>
			</tbody>
		</table>
		<span v-if="!updating && !reportWaves.length" style="color:#931515; font-weight:bold;">В выбранном периоде отсутствуют заказы или у этих заказов не выбрана волна.</span>
		<circle-loading v-if="updating" :radius="41" :ringWeight="14"></circle-loading>
	</div>
</template>

<script>
import Repository from '../../../../classes/Repository/Repository';
import OrdersLimit, { OrdersLimitDate, OrdersLimitWave } from '../../../../classes/Repository/structures/OrdersLimit';
import circleLoading from '../../../UI/mini/circle-loading.vue';
import wavesReportRaw from './waves-report-raw.vue';
export default {
  components: { circleLoading, wavesReportRaw },
	computed:{
		reportWaves(){
			return this.groupWaves(this.ordersLimit.waves)
		}
	},
	data(){
		return{
			ordersLimit:new OrdersLimit(),
			updating:false,
		}
	},
	methods:{
		async update(withSelectedDate){
			this.updating = true;
			try{
				this.ordersLimit = await Repository.getOrdersLimit(withSelectedDate?this.ordersLimit.date:new OrdersLimitDate());
			} catch(e){	console.error(e); }
			this.updating = false;
		},
		/**
		 * @param {Array<OrdersLimitWave>} waves
		 * @returns {Array<OrdersLimitWave>}
		 */
		groupWaves(waves){
			let groupedWaves = new Map();
			waves.forEach((wave)=>{
				let formatTime = (t)=>t.split(":",2).join(":");
				let key = `${formatTime(wave.timeFrom)} - ${formatTime(wave.timeTo)}`;
				if(groupedWaves.has(key)) groupedWaves.get(key).push(wave);
				else groupedWaves.set(key, [wave]);
			});
			return [...groupedWaves.entries()].map(([key, waves])=>{
				let defaultWave = waves[0];
				return {
					description: waves.map(w=>w.description).join(", "),
					orderLimit:defaultWave.orderLimit,
					orders: defaultWave.orders,
					status: defaultWave.status,
					wave:key,
				};
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
</style>