/**
 * Миксин для панели редактора
 */
import ItemsCollectionMixin from '../../mixins/items-collection.js'
export default {
	mixins:[ItemsCollectionMixin],
	beforeMount(){
		this.$root.UAH = Boolean(Number(this.order.course));
	},
	mounted(){
		this.pushHistoryState();//создание записи в истории и адекватное поведение при перемещении на предыдущую страницу
		this.updateDiscountMethods();
	},
	props:{
		order:{
			type:Object,
			required:true
		},
		shop_url:{
			type:String,
			required:true
		},
		errorRate:{
			type:Number,
			required:true
		},
		sots:Array,
    zones:Array,
		discountMethodsTypes:{
			type:Object,
			required:true
		},
	},
	data(){
		return{
			formState:1,
			formStates:{
				error:-1,
				loading:0,
				loaded:1,
			},
			outputError:"",
			isInvalidAddress:false,
			coupons:[],
			discountMethods:[],
			defaultPromocodes:[],
			settingsOpened:false,
			profile:null,
			friendPromocode:false
		}
	},
	computed:{
		goodsTotal(){//сумма по товарам с учетом скидок
      return Math.round(this.getTotal(true));
    },
    course(){
      return this.order.course;
    },
    showMinSum(){
			return this.zone && this.$getCurrencyPrice(this.zone.conditions.limit_min, this.course) > (this.goodsTotal - this.getRoundDiscount(this.goodsTotal))
    },
    sot(){
			let result = this.sots.find((sot)=>sot.properties.id == this.order.deliveryZoneIn);
			if(!result){
				this.isInvalidAddress = true;
				return null;
			}	else return result;
    },
    zone(){
      let result = this.zones.find((zone)=>zone.properties.id == this.order.deliveryZone);
			if(!result){
				this.isInvalidAddress = true;
				return null;
			}	else return result;
    },
    discount(){
      let itemsDiscont = this.getTotal(false) - this.goodsTotal;//сумма скидки по товарам
      return itemsDiscont + this.personalDiscount+this.goodsTotal-Math.round(this.goodsTotal)//полная скидка
    },
	coupon(){
		return this.coupons.find((coupon)=>coupon.data.promocode == this.order.promocode)?.data;
	},
    personalDiscount(){//сумма скидки купона
      switch(this.order.discountMethod){
		case(this.discountMethodsTypes.certificate): return this.$getCurrencyPrice(this.coupon?.discount||0, this.course);
        case(this.discountMethodsTypes.discountCard): return Math.ceil(this.goodsTotal*(this.coupon?.discount||0)/100);
        case(this.discountMethodsTypes.promocode): return Math.ceil(this.goodsTotal*(this.$refs.promoInput.procent||0)/100);
        case(this.discountMethodsTypes.bonus): return this.$getCurrencyPrice(this.order.bonus_pay, this.course);
      }
      return 0;
    },
    currencyDeliveryCost(){
			return this.$getCurrencyPrice(this.order.deliveryCost, this.course);
		}
	},
	methods:{
		pushHistoryState(){
			if(!this.cancelEdit) throw Error(`this component must to implement "cancelEdit" method.`);
			history.pushState(null,'', location.href.replace(location.origin,""));
			window.addEventListener("popstate", this.cancelEdit);
		},
		saveError(message){
			this.state = this.states.error;
			this.outputError = message;
		},
		updateDiscountMethods(){
			this.order.discountMethod = this.discountMethodsTypes.dontUse;
			if(this.order.discount){
				this.order.discountMethod = this.order.discount.webUserId?this.discountMethodsTypes.coupon:this.discountMethodsTypes.promocode;
				this.order.promocode = this.order.discount.title;
			}
			if(this.order.proc && this.order.bonusUser && this.order.bonus_pay)
				this.order.discountMethod = this.discountMethodsTypes.bonus;
		},
		getRoundDiscount(total){
      return total-Math.round(total);
    },
    getTotalWithDeliveryCost(withDiscount){
      let total = this.getTotal(withDiscount)
      return total + (total?this.currencyDeliveryCost:0);
    },
	}
}