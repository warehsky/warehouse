<template>
  <div class="p-border" :style="style"></div>
</template>

<script>
export default {
    name:'border',
    props:{
        width:{
            type:[Number, String],
            default:0
        },
        height:{
            type:[Number, String],
            default:0
        },
        top:{
            type:[Number, String],
            default:0
        },
        left:{
            type:[Number, String],
            default:0
        }
    },
    mounted(){
        this.$el.addEventListener("mousedown",(e)=>{ e.preventDefault(); this.$emit('dragstart',{ border:this })});
        window.addEventListener("mouseup",(e)=>{this.$emit('dragend',{ border:this })});
    },
    computed:{
        style(){
            return {
                '--pb-width':this.width,
                '--pb-height':this.height,
                '--pb-top':this.top,
                '--pb-left':this.left,
            }
        }
    }
}
</script>

<style>
.p-border{
    position: absolute;
    width:var(--pb-width);
    height:var(--pb-height);
    top:auto;
    left:auto;
    transform: translate(var(--pb-left),var(--pb-top));
}
</style>