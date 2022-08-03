<template>
  <div id="test-m" :style="style"></div>
</template>

<script>
export default {
  name: "mw",
  data(){
    return{
        top:0,
        left:0
    }
  },
  mounted() {
    document.getElementsByClassName("custom-panel")[0].addEventListener("mousemove", (e) => {
      console.log(e);
    });
  },
  computed: {
    style() {
      return {
        '--m-left':this.left,
        '--m-top':this.top
      };
    },
  },
};
</script>

<style>
#test-m{
    position: fixed;
    width: 20px;
    height: 20px;
    background: rgba(0, 0, 0, 0.274);
    left: var(--m-left);
    top: var(--m-top);
}
</style>