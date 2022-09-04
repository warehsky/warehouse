<template>
	<div id="promocode" :class="{ checking }">
		<div class="promo-input">
			<input ref="promoInput"
				class="checkout_text promocode"
				:class="[status]"
				placeholder="Промокод"
				:value="value"
				:disabled="checking || disabled"
				@input="checkPromocode($event.target.value)"
				@change="checkPromocode($event.target.value)"/>
			<circle-loading v-if="checking" class="input-loader" :radius="15" :ringWeight="15" color="#931515" background-color="#E5E5E5"></circle-loading>
			<div class="checkout_text promo-procent" v-if="procent">-{{procent}}%</div>
		</div>
		<div v-if="checking" class="status checking">Проверка...</div>
		<div class="status rejected" v-if="error && !procent && !checking">Промокод не принят.</div>
	</div>
</template>

<script>
import CircleLoading from "../../UI/mini/circle-loading.vue";
export default {
	name:"promocode-input",
	components:{
		CircleLoading
	},
	props:{
		value:String,
		disabled:Boolean,
    phone:String,
    promocodes:{
      type:Array,
      default(){
        return []
      }
    }
	},
	data(){
		return{
			old:"",
			status:"invalid",
			procent:0,
			checking:false,
			error:false
		}
	},
	methods:{
		checkPromocode(value,availability=false){
      return new Promise((resolve, reject)=>{
        if(!availability){
          value = (value || "").slice(0,10);
          let invalid = value.length<10;
          let needCheck = value != this.old;
          this.old = value;
					this.$refs.promoInput.value = value;
					this.$emit('input',value);
          if(invalid){
            this.status = "invalid";
            this.procent = 0;
            this.error = false;
            resolve(false);
            return;
          }else if(!needCheck){
            resolve(false);
            return;
          }else{
            let data = this.promocodes.find(code=>code.promocode==value);
            if(data) {
              this.status = data.discount?"valid":"invalid";
              this.procent = data.discount;
              resolve(this.procent);
              return;
            }
          }
        }
        this.checking = true;
        axios
          .get("/Api/checkPromocode",{ params:{ promocode:availability?undefined:value, phone:availability?undefined:this.phone } })
          .then((response)=>{
            if(availability)
              this.enabled = response.data.enabled || this.promocodes.length>0;
            else{
              this.status = response.data.discount?"valid":"invalid";
              this.procent = response.data.discount;
            }
            resolve(this.procent);
          })
          .catch((e)=>{ console.error(e); reject(e) })
          .finally(()=>{ 
            this.checking = false;
            if(!availability){
              this.error = true;
              this.$nextTick(()=>{
                this.$refs.promoInput.focus();
              });
            }
          });
      })
		}
	}
}
</script>

<style lang="scss">
#promocode{
  transition: filter 0.3s ease-in;
  &.disabled{
    filter: grayscale(1.2) contrast(0.8) brightness(1);
    user-select: none;
    pointer-events: none;
  }
  &.checking{
    .promocode {
      border: 1px solid #dfaf00;
      background: #f5f5f5;
      color: #878787;
      transition-duration: 0.05s;
    }
  }
  .status{
    &.checking{
      color: #dfaf00;
    }
    &.rejected{
      color: #f64d4d;
    }
  }
}
.promo-input{
  position: relative;
  display: flex;
  .promocode{
    transition: all 0.3s ease-in-out;
    &.valid{
      border: 1px solid #a0c7a0;
      background: #f2fce3;
      &:focus{
        border: 1px solid green;
        background: #f0ffda;
      }
    }
    &.invalid{
      border: 1px solid red;
    }
  }
  .promo-procent{
    width: fit-content;
    margin-left: 5px;
    height: auto;
  }
}
.discount-methods{
  margin-bottom: 10px;
}
</style>