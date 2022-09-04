export default class OrderItem{
    constructor(props){
        this.itemId = props.itemId;
        this.price = props.price;
        this.quantity_base = props.quantity_base || 0;
        this.quantity = props.quantity;
        this.quantity_warehouse = props.quantity_warehouse || 0;
        this.quantityOld = props.quantityOld || 0;
        this.title = props.title;
        this.stockPrice =  props.stockPrice;
        this.discountBound =  props.discountBound;
        this.discountPrice =  props.discountPrice;
        this.quantityAll =  props.quantityAll;
        this.courier = props.courier || this.getCourierCost();
        this.image = props.image;
        this.prepayment = props.prepayment;
        this.mult = props.mult || 0;
        this.parentId = props.parentId;
        this.weightId = props.weightId?props.weightId:0;
        this.scaned = props.scaned || props.workerId>0;
        this.increment = props.increment || JSON.parse(localStorage.increment || "false");
        this.manually = props.manually || 0;
        this.manually_mode = props.manually_mode || false;
        this.addStatus = props.addStatus || 0;
    }
    getCourierCost(){
        let result = this.price;
        if (this.stockPrice && this.stockPrice<this.price) result = this.stockPrice;
        else if (this.discountBound >= 2000000 || this.discountBound<=0) result = this.price;
        else if (this.discountPrice && this.quantity>=this.discountBound && this.discountPrice<this.price) result = this.discountPrice;
        return result;
    }
}