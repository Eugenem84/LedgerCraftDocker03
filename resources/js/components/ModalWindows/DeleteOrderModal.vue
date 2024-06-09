<script>
import axios from "axios";
import {th} from "vuetify/locale";

export default {
    data() {
        return {
            isVisible: false,
            orderId: ''
        }
    },
    methods: {
        open(orderId) {
            this.orderId = orderId;
            this.isVisible = true;
        },
        deleteOrder() {
            const requestData = {
                orderId: this.orderId
            }
            console.log("удаление ордера с id: ", this.orderId)
            axios.delete(this.$Url + `/api/delete_order/${this.orderId}`)
                .then(response => {
                    console.log('Order deleted:', response.data);
                    this.$emit('order-deleted');
                })
                .catch(error => {
                    console.error('Error deleting order:', error);
                });
            this.isVisible = false;
            this.$emit('close');
        },
        closeModal() {
            this.isVisible = false;
            this.$emit('close');
        }
    }
}
</script>

<template>
        <div class="modal fade" id="deleteOrderModal" tabindex="-1" aria-labelledby="deleteOrderModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="deleteOrderModalLabel"> Удвление ордера </h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="закрыть"></button>
                    </div>
                    <div class="modal-body">
                        <div> Удалить ордер? </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-danger" data-bs-dismiss="modal" v-on:click="deleteOrder">Удалить</button>
                    </div>
                </div>
            </div>
        </div>
<!--  <b-modal title="удаление клиента" :visible="isVisible" @hidden="closeModal" hide-footer>-->
<!--    <b-form @submit.stop.prevent="deleteClient">-->
<!--      Удалить клиента?-->
<!--      <b-button @click="closeModal">отмена</b-button>-->
<!--      <b-button type="submit" variant="primary">удалить</b-button>-->
<!--    </b-form>-->

<!--  </b-modal>-->

</template>

<style scoped>

</style>
