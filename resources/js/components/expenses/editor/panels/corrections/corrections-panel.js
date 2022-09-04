import OrderItem from '../../../orderItem.js';
import Correction from './correction.js';
import EditorPanelMixin from '../editor-panel.js';
export default{
	mixins:[EditorPanelMixin],
	props:{
		waves:{
			type:Array,
			default:()=>[]
		}
	},
	data(){
		return {
			modalOpened:{
        goods:false,
      },
			state:1,
      states:{
        error:-1,
        loading:0,
        loaded:1
      },
		}
	},
	computed:{
		wave(){
      let wave = this.waves?.find(w=>w.id == this.order.waveId);
      return (wave?wave.value:`Неизвестная волна (id:${this.order.waveId})`)||"";
    }
	},
	methods:{
		editCount(item){
			if(!isFinite(item.quantity) || item.quantity<0) item.quantity=0;
			if(item.quantity>1000)
				item.quantity=1000;
			if(item.stockPrice){ this.$emit("count-edited",item); return; }
			if(item.discountBound>0 && item.discountBound<2000000 && item.quantity>=item.discountBound && item.discountPrice)
				item.courier = item.discountPrice;
			else
				item.courier = item.price;
			this.$emit("count-edited",item);
		},
		/**
		 * 
		 * @param {Array} selectedItems 
		 * @param {Boolean} removing Удалять товары с нулевым количеством
		 */
		changeGoods(selectedItems, removing = true){
      let toAdd = selectedItems.except(this.items,"itemId","itemId");
      let toRemove = removing?this.items.except(selectedItems,"itemId","itemId"):[];
      let toChange = Array.getMatched(this.items, selectedItems,"itemId","itemId");
      toRemove.forEach((item) => {
        this.items.remove(item);
      });
      toChange.forEach((match)=>{
        match.first.quantity = match.second.quantity;
        match.first.courier = match.second.courier;
      })
      toAdd.forEach((selectedItem)=>{
        this.items.push(new OrderItem(selectedItem));
      });
    },
		cancelEdit(){
      this.$emit('cancel',this.order,this.orderDraft);
    },
		save(){
			let corrects = this.items.map((item)=>{				 
				let newCorrection = new Correction(item);
				//необработанные корректировки от склада или оператора относящиеся к товару
				if(this.corrections)
					this.corrections.forEach((correction)=>{
						if(correction.itemId != item.itemId) return;
						let fromWarehouse = correction.initiatorPlace == 1
						if(fromWarehouse) newCorrection.initiatorPlace = correction.initiatorPlace;
						if(fromWarehouse || !newCorrection.id) newCorrection.id = correction.id;
					})
				return newCorrection;
			});
			return axios.post("/admin/saveOrderCorrects", { orderId:this.order.id, corrects })
				.then(()=>{
					this.$emit("saved");
					window.location.reload();
				})
				.catch(({message})=>{
					this.saveError(message);
					throw new Error(message);
				})
		}
	}
}