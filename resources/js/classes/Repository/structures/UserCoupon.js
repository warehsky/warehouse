export default class UserCoupon{
    /**
     * 
     * @param { {
     *  discount:Number, 
     *  disposable:Boolean, 
     *  expiration:String,
     *  expirationtm:Number,
     *  id:Number,
     *  intType:Number,
     *  promocode:String,
     *  type:String, 
     *  webUserId:Number } } data 
     */
    constructor(data){
        data = data || {};
        this.discount = data.discount || 0
        this.disposable = Boolean(data.disposable)
        this.expiration = data.expiration || ""
        this.expirationtm = data.expirationtm || 0
        this.id = data.id || 0
        this.intType = data.intType || 0
        this.promocode = data.promocode ||""
        this.type = data.type || ""
        this.webUserId = data.webUserId || 0
    }
}