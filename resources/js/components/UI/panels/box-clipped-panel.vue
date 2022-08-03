<template>
  <div class="box-clipped-panel" :style="style">
    <slot></slot>
    </div>
</template>

<script>
export default {
    name:"box-clipped-panel",
    props:{
        x:[Number,String],
        y:[Number,String],
        top:[Number,String],
        left:[Number,String],
        width:[Number,String],
        height:[Number,String],
		unit:{
			type:String,
			default(){
				return "px";
			}
		},
        transition:String
    },
    computed:{
        style(){
			let format = (value)=>typeof value == 'number'?`${value}${this.unit||"px"}`:value;
            return {
                '--cp-x':format(this.x || this.left),
                '--cp-y':format(this.y || this.top),
                '--cp-width':format(this.width),
                '--cp-height':format(this.height),
                '--cp-transition':this.transition
            }
        }
    }
}
</script>

<style>
.box-clipped-panel{
    transition: clip-path var(--cp-transition);
    --cp-stp1:0% 0%;
    --cp-stp2:0% 100%;
    --cp-stp3:var(--cp-x) 100%;
    --cp-p1:var(--cp-x) var(--cp-y);
    --cp-p2:calc(var(--cp-x) + var(--cp-width)) var(--cp-y);
    --cp-p3:calc(var(--cp-x) + var(--cp-width)) calc(var(--cp-y) + var(--cp-height));
    --cp-p4:var(--cp-x) calc(var(--cp-y) + var(--cp-height));
    --cp-edp1:var(--cp-x) 100%;
    --cp-edp2:100% 100%;
    --cp-edp3:100% 0%;
    clip-path: polygon(
        var(--cp-stp1), var(--cp-stp2), var(--cp-stp3),
        var(--cp-p1), var(--cp-p2), var(--cp-p3), var(--cp-p4),
        var(--cp-edp1), var(--cp-edp2),var(--cp-edp3)
    );
}
</style>