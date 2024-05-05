<script>
import { BButton } from 'bootstrap-vue'
import {BIconTrash} from 'bootstrap-vue'
export default {
  components: {
     BButton, BIconTrash
  }
}

</script>

<template>
<div>
  <b-button variant="danger">
    <BIconTrash icon="trash"></BIconTrash>
  </b-button>

  <b-form-select>
    <b-form-select-option value="option1">1</b-form-select-option>
    <b-form-select-option value="option2">2</b-form-select-option>
  </b-form-select>

</div>
</template>

<style scoped>
@import "bootstrap/dist/css/bootstrap.css";
@import "bootstrap-vue/dist/bootstrap-vue.css";
</style>