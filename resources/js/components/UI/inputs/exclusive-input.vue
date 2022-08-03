<template>
  <input :id="id" v-on="availableListeners" class="exclusive-input" @input="handler" :value="inputValue" />
</template>

<script>
/**
 * input, работающий по аналогии с radio button. При вводе, сообщает остальным компонентам того же типа и с тем же id, что нужно присвоить значение по умолчанию.
 */
export default {
    name:"exclusive-input",
    props:{
        id:[String,Number],
        default:{ default:'' },
        value:{ default:'' }
    },
    watch:{
        value(){ this.inputValue = this.value; }
    },
    data(){
        return {
            inputValue:this.value,
            reservedEvents:["interrupted", "input"]
        }
    },
    computed:{
        availableListeners(){
            return Object.fromEntries(Object.entries(this.$listeners).filter(([key])=>!this.reservedEvents.includes(key)))
        }
    },
    mounted(){
        window.addEventListener("interrupt",(e)=>{
            let component = e.detail.component;
            if(component == this || component.id != this.id) return;
            this.setValue(this.default);
            this.$emit("interrupted", { by:component });
        });
    },
    methods:{
        handler(e){
            let value = e.target.value;
            if(value == this.inputValue) return;
            this.setValue(value);
            this.interrupt();
        },
        interrupt(){
            window.dispatchEvent(new CustomEvent("interrupt", { detail:{ component:this } }));
        },
        setValue(value) {
            this.inputValue = value;
            this.$emit("input", value);
        }
    }
}
</script>