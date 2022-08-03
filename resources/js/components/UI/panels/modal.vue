<template>
  <transition name="modal-fade">
    <div class="modal-backdrop">
      <div class="modal" role="dialog" aria-labelledby="modalTitle" aria-describedby="modalDescription">
        <header class="modal-header" id="modalTitle">
          <slot name="header">
            &nbsp;
            <button type="button" class="btn-close" aria-label="Close modal" @click="unconfim">x</button>
          </slot>
        </header>
        <section class="modal-body" id="modalDescription">
          <slot name="body"></slot>
        </section>
        <footer class="modal-footer">
          <slot name="footer">
            <button v-show="show.includes('ok')" type="button" class="btn-agree" aria-label="Close modal" @click="confirm">{{ok}}</button>
            &nbsp;
            <button v-show="show.includes('cancel')" type="button" class="btn-agree n-agree" aria-label="Close modal" @click="unconfim">{{cancel}}</button>
          </slot>
        </footer>
      </div>
    </div>
  </transition>
</template>
<script>
  export default {
    name: 'modal',
    props:{
      ok:{
        type:String,
        default(){
          return 'Согласен'
        }
      },
      cancel:{
        type:String,
        default(){
          return 'Не согласен'
        }
      },
      show:{
        type:Array,
        default(){
          return ["ok","cancel"]
        }
      }
    },
    data() {
    return {
      choose: '',
    };
  },
    methods: {
      close() {
        this.$emit('close');
      },
      unconfim(){
        this.$emit('unconfirm');
        this.$emit('close');
      },
      confirm() {
        this.$emit('confirm');
        this.$emit('close');
      },
    },
  };
</script>

<style>
.modal-backdrop{
  z-index: 1;
}
</style>