<template>
  <div class="sticker" :style="cssVars">
    <slot></slot>
  </div>
</template>

<script>
export default {
    name:"sticker",
    props:{
        targetId: {
            type:[String],
            required:true
        },
        offsetTop:{
            type:Number,
            default: 0
        },
        offsetLeft:{
            type:Number,
            default: 0
        }
    },
    watch:{
        offsetTop:function(){
            this.update();
        },
        offsetLeft:function(){
            this.update();
        }
    },
    data(){
        return{
            top:0,
            left:0
        }
    },
    mounted(){
        this.target = document.getElementById(this.targetId);
        if(!this.target)
            console.error("target element with id \""+this.targetId+"\" not found");
        this.update();
        window.addEventListener("scroll",()=>{ this.update(); });
        window.addEventListener("resize",()=>{ this.update(); });
        this.target
    },
    methods:{
        update(){
            let rect = this.target.getBoundingClientRect();
            this.top = rect.top+(this.offsetTop?this.offsetTop:0);
            this.left = rect.left+(this.offsetLeft?this.offsetLeft:0);
            this.$emit("update",{ targetRect:rect });
        },
    },
    computed:{
        cssVars(){
            return{
                'top':this.top+'px',
                'left':this.left+'px'
            }
        }
    }
}
</script>

<style>
.sticker{
    position: fixed;
    width: max-content;
    height: max-content;
}
</style>