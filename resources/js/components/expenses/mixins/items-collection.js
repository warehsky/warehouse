export default{
	methods:{
		/**
		 * Получение стоимости товара с учетом скидки на этот товар
		 */
		getPrice(item, withDiscount = false,calculateCurrency=true) {
			let price = item.price;
			if(!withDiscount)	price = item.price;
			else if (item.stockPrice) price = item.stockPrice;
			else if (item.discountBound >= 2000000 || !item.discountBound) price = item.price;
			else if (item.discountPrice && item.quantity >= item.discountBound) price = item.discountPrice;
			return calculateCurrency?this.$getCurrencyPrice(price,this.course):price;
		},
		/**
		 * 
		 * @param {Boolean} reserv Определяет, нужно ли учитывать резерв(алкогольную продукцию) при расчете. 
		 * @default reserv false, withDiscount false
		 * @param {Boolean} withDiscount Учитывать скидку при расчете 
		 * @returns Сумма по всем товарам
		 */
		getTotal(withDiscount = false, calculateCurrency=true) {
			// подсчет суммы заказа без резерва
			var sum = 0;
			this.items.forEach(item=>{
				sum += this.getPrice(item,withDiscount,calculateCurrency) * item.quantity;
			});
			return sum;
		},
	}
}