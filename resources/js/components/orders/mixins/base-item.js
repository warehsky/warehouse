export default {
	computed:{
		actualPrice(){
			return this[this.actualPriceProperty];
		},
		actualPriceType(){
			let typeName = "price";
			if (this.stockPrice && this.stockPrice<this.price) typeName = "stockPrice";
          	else if (this.item.discountBound >= 2000000 || this.item.discountBound<=0) typeName = "price";
          	else if (this.item.discountPrice && this.discountPrice<this.price) typeName = "discountPrice";
          	return typeName;
		},
		actualPriceProperty(){
			let propertyName = "price";
			if (this.stockPrice && this.stockPrice<this.price) propertyName = "stockPrice";
			else if (this.item.discountBound >= 2000000 || this.item.discountBound<=0) propertyName = "price";
			else if (this.discountPrice && this.item.quantity>=this.item.discountBound && this.discountPrice<this.price) propertyName = "discountPrice";
			return propertyName;
		},
		price(){ return this.$getCurrencyPrice(this.item.price, this.course, this.UAH) },
		stockPrice() { return this.$getCurrencyPrice(this.item.stockPrice, this.course, this.UAH) },
		discountPrice() { return this.$getCurrencyPrice(this.item.discountPrice, this.course, this.UAH) }
	},
}