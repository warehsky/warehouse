<template>
    <tr class="wave-row">
        <td style="white-space: nowrap;">{{wave.wave}}</td>
        <td class="description-cell">
            <div ref="waveDescription" class="wave-description">{{wave.description}}</div>
            <span v-show="canShowMore" @mousedown="onClickShowMore" class="more-button">Подробнее</span>
            <float-panel tabindex="0" ref="morePanel" v-if="canShowMore" v-show="showMore" :opened="true">
                <div class="content">{{wave.description}}</div>
            </float-panel>
        </td>
        <td>{{wave.orders}}</td>
        <td>{{wave.orderLimit || "не установлен"}}</td>
        <td>{{wave.orderLimit?wave.orderLimit - wave.orders:"-"}}</td>
        <!-- <td><input class="action" type="button" value="Фильтровать заказы" :title="'Фильтровать заказы по волне'+wave.value" @click="$emit('select',wave)"/></td> -->
    </tr>
</template>
<script>
import floatPanel from '../../../UI/panels/float-panel.vue';
export default {
    name:'name',
    components: {
        floatPanel
    },
    props:{
        wave:Object
    },
    data(){
        return {
            canShowMore:false,
            showMore:false
        }
    },
    mounted(){
        this.resizeObserver = this.resizeObserver || new ResizeObserver(()=>{
            this.canShowMore = this.$refs.waveDescription?.scrollWidth > this.$refs.waveDescription?.clientWidth;
        });
        this.resizeObserver.observe(this.$refs.waveDescription);
    },
    methods:{
        onClickShowMore(e){
            let show = !this.showMore;
            e.target.addEventListener("click",()=>{ 
                this.showMore = show; 
                this.$nextTick(()=>{
                    this.$refs.morePanel.$el.focus();
                    this.$refs.morePanel.$el.addEventListener("blur",()=>this.showMore = false, { once:true });
                })
            }, { once:true });
        },
    },
    beforeUnmount(){
        this.resizeObserver.unobserve();
    },
}
</script>
<style lang="scss">
.wave-row{
    .description-cell{
        max-width: 200px;
        .wave-description{
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }
        .more-button{
            color: #931515;
            font-weight: 600;
            cursor: pointer;
        }
        .float-panel{
            box-shadow: 0px 0px 10px 0px #acacac;
            border-color: #c4c4c4;
            border-radius: 8px;
            background: #e4e4e4;
            width:auto;
            height:auto;
            .content{
                padding: 10px;
            }
        }
    }
    .action{
        visibility: hidden;
    }
    &:hover{
        box-shadow: -4px 0px 0px 0px grey;
    background: #8d8d8d2e !important;
        .action{
            visibility:visible;
        }
    }
}
</style>