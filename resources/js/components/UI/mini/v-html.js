export default {
    template:`<div><div></div></div>`,
    props:["src"],
    beforeMount(){
        this.update();
    },
    methods:{
        update(){
            fetch(this.src).then(response=>response.text()).then(text=>{
                let compileResult = Vue.compile(text);
                const htmlComp = Vue.extend({
                    render: compileResult.render,
                    staticRenderFns: compileResult.staticRenderFns
                });
                let component = new htmlComp({ el:this.$el.children[0] });
            });
        },
    }
}