<template>
  <canvas :width="width" :height="height" :style="cssVars" class="pointer-lines-canvas"></canvas>
</template>

<script>
export default {
	props:{
		width:{
			type:Number,
			default:300
		},
		height:{
			type:Number,
			default:150
		},
		lines:{
			type:Array,
			default(){
				return [
					{ id:0, points:[[100,200],[200,400]] }
				]
			}
		},
	},
	watch:{
		width(){ this.update() },
		height(){ this.update() },
		lines:{
			handler:function(){ this.update() },
			deep:true
		}
	},
	data(){
		return{
			context:null,
			// canvasLines:[]
		}
	},
	mounted(){
		this.context = this.$el.getContext('2d');
	},
	methods:{
		update(){
			this.context.clearRect(0,0,this.$el.width,this.$el.height);
			this.lines.forEach((line)=>{ this.drawLine(line);	})
		},
		drawLine(line){
			if(line.options && line.options.disabled)
				return;
			let point = line.points[0];
			let point1 = line.points[1];
			if(line.options)
				Object.entries(line.options).forEach(([key,value])=>{
					if(key in this.context)
						this.context[key] = value;
				});
			this.context.beginPath();
			this.context.moveTo(point[0],point[1]);
			this.context.lineTo(point1[0],point1[1]);
			this.context.closePath();
			this.context.stroke();
		}
	},
	computed:{
		cssVars(){
			return {
				'--c-width':this.width+"px",
				'--c-height':this.height+"px"
			}
		}
	}
}
</script>

<style>
.pointer-lines-canvas{
	position: absolute;
	left: 0;
	width: var(--c-width);
	height: var(--c-height);
	pointer-events: none;
}
</style>