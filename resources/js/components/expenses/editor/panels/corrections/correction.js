export default class Correction {
    /**
     * 
     * @param {{ 
     *  id:Number, 
     *  changeId:Number, 
     *  closed:Boolean,
     *  initiatorId:Number,
     *  initiatorPlace:Number,
     *  itemId:Number,
     *  moderatorId:Number,
     *  orderId:Number,
     *  price:Number,
     *  priceType:Number,
     *  quantity:Number }} props
     */
    constructor(props){
        props = props || {};
        this.id = props.id || 0;
        this.changeId = props.changeId || 0,
        this.closed = Boolean(props.closed),
        this.initiatorId = props.initiatorId || 1,
        this.initiatorPlace = props.initiatorPlace || 2,
        this.itemId = props.itemId || 0,
        this.moderatorId = props.moderatorId || 1,
        this.orderId = props.orderId || 0,
        this.price = props.price || 0,
        this.priceType = props.priceType || 32,
        this.quantity = props.quantity || 0
    }
}