export default{
    computed:{
        scanedStatus(){
            if(!this.order.weightCount) return "Не требует взвешивания."
            if(!this.order.weightedCount) return "Не взвешено."
            else if(this.order.weightCount>this.order.weightedCount) return "Взвешено частично."
            else return "Взвешено полностью."
        }
    }
}