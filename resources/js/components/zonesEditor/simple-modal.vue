<template>
  <div class="simple-modal">
      <div class="modal-contents">
          <slot></slot>
      </div>
  </div>
</template>

<script>
export default {
    name:"simple-modal",
}
</script>

<style lang="scss">
.simple-modal{
    z-index: 100001;
    background: rgba(0, 0, 0, 0.37);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    .modal-contents{
        width: 80%;
        height: 70%;
        background: white;
        border-radius: 8px;
        display: flex;
    }
}
</style>