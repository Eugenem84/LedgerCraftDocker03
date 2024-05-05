<script>
import 'bootstrap'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import $ from 'jquery'
export default {
    data(){
        return {
            someId: '',
            isModal2Open: false
        }
    },

    methods: {
        displayId(id){
            console.log('someId: ', id)
            this.someId = id
        },

        openModal2(){
            console.log('opening modal 2')
            document.addEventListener('DOMContentLoaded', function (){
                const myModal = new bootstrap.Modal(document.getElementById('exampleModal2'))
                myModal.show()
            })
        }
    },

    mounted() {
        //
    }
}
</script>

<template>
    <div>

        <button v-on:click="openModal2">open modal 2 !!!</button>

        <button type="button"
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#exampleModal2"
        >
            Launch demo modal 2!!
        </button>

        <select>
            <option>321</option>
            <option data-bs-toggle="modal" data-bs-target="#exampleModal">123</option>
        </select>

        <!-- Модальное окно -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Заголовок модального окна</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        Модальное окно 1
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                        <button type="button" class="btn btn-primary">Сохранить изменения</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно -->
        <div class="modal fade" id="exampleModal2" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Заголовок модального окна</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        Модальное окно 2
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                        <button type="button" class="btn btn-primary">Сохранить изменения</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

</template>

<style scoped>

</style>
