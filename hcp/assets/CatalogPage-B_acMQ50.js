import { Q as QTab, a as QTabPanels, b as QTabPanel } from "./QTabPanels-Bl_92niL.js";
import { c as QTabs } from "./QTabs-DnB1hw4w.js";
import { _ as _export_sfc, r as ref, D as openBlock, E as createBlock, F as withCtx, G as createVNode, Z as QCard, $ as QCardSection, R as createBaseVNode, a0 as QInput, a1 as QCardActions, O as QBtn, a2 as QDialog, n as onMounted, K as createElementBlock, L as Fragment, Q as QSeparator, M as createTextVNode, I as createCommentVNode, N as renderList, P as withDirectives, S as toDisplayString, U as Ripple } from "./index-74sOg8Nl.js";
import { Q as QSelect } from "./QSelect-DGItPn-E.js";
import { Q as QItemLabel, a as QItem, b as QItemSection } from "./QItem-DuqkKkh7.js";
import { D as DeleteConfirmPage, Q as QList } from "./DeleteConfirmPage-DjUGgkyk.js";
import { Q as QPage } from "./QPage-B-LnxX2q.js";
import { api } from "./axios-D58jYJIV.js";
import { u as useSpecializationsStore } from "./specializations-B0lcZ67D.js";
import "./rtl-DDpZOXNn.js";
const _sfc_main$4 = {
  __name: "NewClientDialogPage",
  emits: ["client-added"],
  setup(__props, { expose: __expose, emit: __emit }) {
    const specializationStore = useSpecializationsStore();
    const selectedSpecializationId = specializationStore.getSelectedSpecialization.id;
    const isOpen = ref(false);
    const name = ref("");
    const phone = ref("");
    const emit = __emit;
    function open() {
      isOpen.value = true;
    }
    function close() {
      isOpen.value = false;
    }
    __expose({ open });
    const addNew = async () => {
      try {
        const response = await api.post("/add_client", {
          name: name.value,
          phone: phone.value,
          specialization_id: selectedSpecializationId
        });
        name.value = "";
        phone.value = "";
        close();
        emit("client-added", response.data);
        console.log("response", response);
      } catch (err) {
        console.error("ошибка добавления клиента: ", err);
      }
    };
    const __returned__ = { specializationStore, selectedSpecializationId, isOpen, name, phone, emit, open, close, addNew, ref, get api() {
      return api;
    }, get useSpecializationsStore() {
      return useSpecializationsStore;
    } };
    Object.defineProperty(__returned__, "__isScriptSetup", { enumerable: false, value: true });
    return __returned__;
  }
};
function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
  return openBlock(), createBlock(QDialog, {
    modelValue: $setup.isOpen,
    "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => $setup.isOpen = $event)
  }, {
    default: withCtx(() => [
      createVNode(QCard, null, {
        default: withCtx(() => [
          createVNode(QCardSection, null, {
            default: withCtx(() => [
              _cache[3] || (_cache[3] = createBaseVNode("div", { class: "text-h6" }, " новый клиент", -1)),
              createVNode(QInput, {
                modelValue: $setup.name,
                "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.name = $event),
                label: "Имя клиента",
                outlined: "",
                class: "q-mb-md"
              }, null, 8, ["modelValue"]),
              createVNode(QInput, {
                modelValue: $setup.phone,
                "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.phone = $event),
                label: "телефон",
                outlined: "",
                class: "q-mb-md"
              }, null, 8, ["modelValue"])
            ]),
            _: 1
          }),
          createVNode(QCardActions, { align: "right" }, {
            default: withCtx(() => [
              createVNode(QBtn, {
                flat: "",
                label: "отмена",
                color: "yellow",
                onClick: $setup.close
              }),
              createVNode(QBtn, {
                flat: "",
                label: "сохранить",
                color: "yellow",
                onClick: $setup.addNew
              })
            ]),
            _: 1
          })
        ]),
        _: 1
      })
    ]),
    _: 1
  }, 8, ["modelValue"]);
}
const NewClientDialogPage = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4], ["__file", "NewClientDialogPage.vue"]]);
const _sfc_main$3 = {
  __name: "NewServiceDialogPage",
  props: {
    data: { type: Number }
  },
  emits: ["service-added"],
  setup(__props, { expose: __expose, emit: __emit }) {
    const specializationStore = useSpecializationsStore();
    const selectedSpecializationId = specializationStore.getSelectedSpecialization.id;
    const isOpen = ref(false);
    const name = ref("");
    const price = ref("");
    const categoryId = ref("");
    const emit = __emit;
    const props = __props;
    function open() {
      isOpen.value = true;
      categoryId.value = props.data.value;
    }
    function close() {
      isOpen.value = false;
      categoryId.value = "";
    }
    __expose({ open });
    const addNew = async () => {
      try {
        console.log("category_id: ", props.data.id);
        const response = await api.post("/add_service", {
          service: name.value,
          price: price.value,
          category_id: props.data.id,
          specialization_id: selectedSpecializationId
        });
        name.value = "";
        price.value = "";
        categoryId.value = "";
        close();
        emit("service-added", response.data);
        console.log("response", response);
      } catch (err) {
        console.error("ошибка добавления сервиса: ", err);
      }
    };
    const __returned__ = { specializationStore, selectedSpecializationId, isOpen, name, price, categoryId, emit, props, open, close, addNew, ref, get api() {
      return api;
    }, get useSpecializationsStore() {
      return useSpecializationsStore;
    } };
    Object.defineProperty(__returned__, "__isScriptSetup", { enumerable: false, value: true });
    return __returned__;
  }
};
function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
  return openBlock(), createBlock(QDialog, {
    modelValue: $setup.isOpen,
    "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => $setup.isOpen = $event)
  }, {
    default: withCtx(() => [
      createVNode(QCard, null, {
        default: withCtx(() => [
          createVNode(QCardSection, null, {
            default: withCtx(() => [
              _cache[3] || (_cache[3] = createBaseVNode("div", { class: "text-h6" }, " новый сервис", -1)),
              createVNode(QInput, {
                modelValue: $setup.name,
                "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.name = $event),
                label: "Название сервиса",
                outlined: "",
                class: "q-mb-md"
              }, null, 8, ["modelValue"]),
              createVNode(QInput, {
                modelValue: $setup.price,
                "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.price = $event),
                label: "цена",
                outlined: "",
                class: "q-mb-md"
              }, null, 8, ["modelValue"])
            ]),
            _: 1
          }),
          createVNode(QCardActions, { align: "right" }, {
            default: withCtx(() => [
              createVNode(QBtn, {
                flat: "",
                label: "отмена",
                color: "yellow",
                onClick: $setup.close
              }),
              createVNode(QBtn, {
                flat: "",
                label: "сохранить",
                color: "yellow",
                onClick: $setup.addNew
              })
            ]),
            _: 1
          })
        ]),
        _: 1
      })
    ]),
    _: 1
  }, 8, ["modelValue"]);
}
const NewServiceDialogPage = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3], ["__file", "NewServiceDialogPage.vue"]]);
const _sfc_main$2 = {
  __name: "NewServiceCategoryDialogPage",
  emits: ["service_category-added"],
  setup(__props, { expose: __expose, emit: __emit }) {
    const specializationStore = useSpecializationsStore();
    const selectedSpecializationId = specializationStore.getSelectedSpecialization.id;
    const isOpen = ref(false);
    const name = ref("");
    const emit = __emit;
    function open() {
      isOpen.value = true;
    }
    function close() {
      isOpen.value = false;
    }
    __expose({ open });
    const addNew = async () => {
      try {
        const response = await api.post("/add_category", {
          category_name: name.value,
          specialization_id: selectedSpecializationId
        });
        name.value = "";
        close();
        emit("service_category-added", response.data);
        console.log("response", response);
      } catch (err) {
        console.error("ошибка добавления сервис категории: ", err);
      }
    };
    const __returned__ = { specializationStore, selectedSpecializationId, isOpen, name, emit, open, close, addNew, ref, get api() {
      return api;
    }, get useSpecializationsStore() {
      return useSpecializationsStore;
    } };
    Object.defineProperty(__returned__, "__isScriptSetup", { enumerable: false, value: true });
    return __returned__;
  }
};
function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
  return openBlock(), createBlock(QDialog, {
    modelValue: $setup.isOpen,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.isOpen = $event)
  }, {
    default: withCtx(() => [
      createVNode(QCard, null, {
        default: withCtx(() => [
          createVNode(QCardSection, null, {
            default: withCtx(() => [
              _cache[2] || (_cache[2] = createBaseVNode("div", { class: "text-h6" }, " новая категория", -1)),
              createVNode(QInput, {
                modelValue: $setup.name,
                "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.name = $event),
                label: "Название категории",
                outlined: "",
                class: "q-mb-md"
              }, null, 8, ["modelValue"])
            ]),
            _: 1
          }),
          createVNode(QCardActions, { align: "right" }, {
            default: withCtx(() => [
              createVNode(QBtn, {
                flat: "",
                label: "отмена",
                color: "yellow",
                onClick: $setup.close
              }),
              createVNode(QBtn, {
                flat: "",
                label: "сохранить",
                color: "yellow",
                onClick: $setup.addNew
              })
            ]),
            _: 1
          })
        ]),
        _: 1
      })
    ]),
    _: 1
  }, 8, ["modelValue"]);
}
const NewServiceCategoryDialogPage = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2], ["__file", "NewServiceCategoryDialogPage.vue"]]);
const _sfc_main$1 = {
  __name: "EditServiceCategoryDialogPage",
  props: {
    data: {
      type: Object,
      default: () => ({})
    }
  },
  emits: ["service_category-edited"],
  setup(__props, { expose: __expose, emit: __emit }) {
    const isOpen = ref(false);
    const name = ref("");
    const props = __props;
    const emit = __emit;
    function open() {
      isOpen.value = true;
      name.value = props.data.category_name;
    }
    function close() {
      isOpen.value = false;
      name.value = "";
    }
    __expose({ open });
    const edit = async () => {
      console.log("props: ", props.data);
      try {
        const response = await api.post("/edit_category", {
          id: props.data.id,
          category_name: name.value
        });
        name.value = "";
        close();
        emit("service_category-edited", response.data);
        console.log("response", response);
      } catch (err) {
        console.error("ошибка добавления сервис категории: ", err);
      }
    };
    const deleteServiceCategory = async () => {
      console.log("categoryId: ", props.data.id);
      try {
        const response = await api.post("/delete_category", {
          categoryId: props.data.id
        });
        close();
        emit("service_category-edited", response.data);
      } catch (err) {
        console.error("ошибка удаления категории: ", err);
      }
    };
    const __returned__ = { isOpen, name, props, emit, open, close, edit, deleteServiceCategory, ref, get api() {
      return api;
    } };
    Object.defineProperty(__returned__, "__isScriptSetup", { enumerable: false, value: true });
    return __returned__;
  }
};
function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
  return openBlock(), createBlock(QDialog, {
    modelValue: $setup.isOpen,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.isOpen = $event)
  }, {
    default: withCtx(() => [
      createVNode(QCard, null, {
        default: withCtx(() => [
          createVNode(QCardSection, null, {
            default: withCtx(() => [
              _cache[2] || (_cache[2] = createBaseVNode("div", { class: "text-h6" }, " редактирование категории", -1)),
              createVNode(QInput, {
                modelValue: $setup.name,
                "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.name = $event),
                label: "Название категории",
                outlined: "",
                class: "q-mb-md",
                rules: [(val) => !!val || "Обязательное поле"]
              }, null, 8, ["modelValue", "rules"])
            ]),
            _: 1
          }),
          createVNode(QCardActions, { align: "right" }, {
            default: withCtx(() => [
              createVNode(QBtn, {
                flat: "",
                label: "удалить",
                color: "yellow",
                onClick: $setup.deleteServiceCategory
              }),
              createVNode(QBtn, {
                flat: "",
                label: "отмена",
                color: "yellow",
                onClick: $setup.close
              }),
              createVNode(QBtn, {
                flat: "",
                label: "сохранить",
                color: "yellow",
                onClick: $setup.edit
              })
            ]),
            _: 1
          })
        ]),
        _: 1
      })
    ]),
    _: 1
  }, 8, ["modelValue"]);
}
const EditServiceCategoryDialogPage = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1], ["__file", "EditServiceCategoryDialogPage.vue"]]);
const _sfc_main = {
  __name: "CatalogPage",
  setup(__props, { expose: __expose }) {
    __expose();
    const newClientDialog = ref(null);
    const newServiceDialog = ref(null);
    const newServiceCategoryDialog = ref(null);
    const editServiceCategoryDialog = ref(null);
    const confirmDialog = ref(null);
    const specializationStore = useSpecializationsStore();
    const tab = ref("services");
    const editClientMode = ref(false);
    const editServiceMode = ref(false);
    const clients = ref([]);
    const services = ref([]);
    const serviceCategories = ref([]);
    const selectedClient = ref(null);
    const selectedService = ref(null);
    const selectedServiceCategory = ref(null);
    const selectedSpecializationId = specializationStore.getSelectedSpecialization.id;
    const showClientsDetails = ref(false);
    const showServiceDetails = ref(false);
    onMounted(() => {
      getClients();
      getServiceCategories();
      getServicesByCategory();
    });
    function handleDelete() {
      if (showClientsDetails.value) {
        confirmDialog.value.open(
          "Удаление клиента",
          `Вы уверены что хотите удалить клиента "${selectedClient.value.name}" ?`,
          () => {
            deleteClient();
          }
        );
      } else if (showServiceDetails.value) {
        confirmDialog.value.open(
          "Удаление услуги",
          `Вы уврены что хотите удалить сервис "${selectedService.value.service}"`,
          () => {
            deleteService();
          }
        );
      }
    }
    const getClients = async () => {
      try {
        const response = await api.get(`/get_clients/${selectedSpecializationId}`);
        clients.value = response.data;
        console.log("clients: ", clients.value);
      } catch (err) {
        console.error("ошибка получения клиентов: ", err);
      }
    };
    const getServiceCategories = async () => {
      const specializationStore2 = useSpecializationsStore();
      try {
        const specializationId = specializationStore2.getSelectedSpecialization.id;
        console.log("специализация: ", specializationId);
        const response = await api.get(`/get_categories/${specializationId}`);
        serviceCategories.value = response.data;
        console.log("сервис категории: ", serviceCategories.value);
      } catch (err) {
        console.error("ошибка загрузки сервис категорий", err);
      }
    };
    const getServicesByCategory = async (categoryId) => {
      console.log("подгружаем сервисы категории: ", categoryId);
      console.log("selectedServiceCategory: ", selectedServiceCategory.value);
      try {
        const response = await api.get(`/get_service/${selectedServiceCategory.value.id}`);
        services.value = response.data;
        console.log("подгружены сервисы категории: ", services.value);
      } catch (err) {
        console.error("ошибка загрузке сервисов данной категории: ", categoryId, err);
      }
    };
    const openClientDialog = (client) => {
      selectedClient.value = { ...client };
      showClientsDetails.value = true;
    };
    const openServiceDialog = (service) => {
      console.log("selectedService: ", selectedService.value);
      selectedService.value = { ...service };
      showServiceDetails.value = true;
    };
    const editClient = async () => {
      try {
        console.log("отправление запроса на редактирование");
        const response = await api.post(`/edit_client`, {
          id: selectedClient.value.id,
          name: selectedClient.value.name,
          phone: selectedClient.value.phone
        });
        console.log("response: ", response);
        await getClients();
        editClientMode.value = false;
        showClientsDetails.value = false;
      } catch (err) {
        console.error(err);
      }
    };
    const editService = async () => {
      try {
        console.log("отправление запроса на редактирование");
        console.log("selectedService", selectedService.value);
        const response = await api.post(`/edit_service`, {
          id: selectedService.value.id,
          service: selectedService.value.service,
          price: selectedService.value.price
        });
        console.log("response: ", response);
        console.log("selectedServiceCategory", selectedServiceCategory.value);
        await getServicesByCategory(selectedServiceCategory.value);
        editServiceMode.value = false;
        showServiceDetails.value = false;
      } catch (err) {
        console.error(err);
      }
    };
    const deleteClient = async () => {
      try {
        const response = await api.post(`/delete_client`, {
          clientId: selectedClient.value.id
        });
        console.log("response", response);
        await getClients();
        editClientMode.value = false;
        showClientsDetails.value = false;
      } catch (err) {
        console.error(err);
      }
    };
    const deleteService = async () => {
      try {
        const response = await api.post(`/delete_service`, {
          serviceId: selectedService.value.id
        });
        console.log("response", response);
        await getServicesByCategory();
        editServiceMode.value = false;
        showServiceDetails.value = false;
      } catch (err) {
        console.error(err);
      }
    };
    const openNewClientDialog = () => {
      newClientDialog.value.open();
    };
    const openNewServiceDialog = () => {
      newServiceDialog.value.open();
    };
    const openNewServiceCategoryDialog = () => {
      newServiceCategoryDialog.value.open();
    };
    const openEditServiceCategoryDialog = () => {
      editServiceCategoryDialog.value.open();
    };
    const handleClientAdded = (newClientData) => {
      clients.value.push(newClientData.client);
      console.log("newClientData", newClientData.client);
      console.log("clients", clients.value);
    };
    const handleServiceAdded = () => {
      getServicesByCategory();
      console.log("services: ", services);
    };
    const handleServiceCategoryAdded = () => {
      getServiceCategories();
    };
    const handleServiceCategoryEdited = () => {
      serviceCategories.value = null;
      selectedServiceCategory.value = null;
      getServiceCategories();
    };
    const __returned__ = { newClientDialog, newServiceDialog, newServiceCategoryDialog, editServiceCategoryDialog, confirmDialog, specializationStore, tab, editClientMode, editServiceMode, clients, services, serviceCategories, selectedClient, selectedService, selectedServiceCategory, selectedSpecializationId, showClientsDetails, showServiceDetails, handleDelete, getClients, getServiceCategories, getServicesByCategory, openClientDialog, openServiceDialog, editClient, editService, deleteClient, deleteService, openNewClientDialog, openNewServiceDialog, openNewServiceCategoryDialog, openEditServiceCategoryDialog, handleClientAdded, handleServiceAdded, handleServiceCategoryAdded, handleServiceCategoryEdited, onMounted, ref, get api() {
      return api;
    }, get useSpecializationsStore() {
      return useSpecializationsStore;
    }, DeleteConfirmPage, NewClientDialogPage, NewServiceDialogPage, NewServiceCategoryDialogPage, EditServiceCategoryDialogPage };
    Object.defineProperty(__returned__, "__isScriptSetup", { enumerable: false, value: true });
    return __returned__;
  }
};
const _hoisted_1 = { class: "row items-center" };
const _hoisted_2 = { class: "col-auto self-end" };
const _hoisted_3 = { class: "col-auto self-end" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
  return openBlock(), createElementBlock(Fragment, null, [
    createVNode(QPage, { class: "q-pa-none" }, {
      default: withCtx(() => [
        createVNode(QCard, null, {
          default: withCtx(() => [
            createVNode(QTabs, {
              modelValue: $setup.tab,
              "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.tab = $event),
              dense: "",
              class: "text-grey sticky-tabs",
              "active-color": "yellow",
              "indicator-color": "yellow",
              align: "justify",
              "narrow-indicator": ""
            }, {
              default: withCtx(() => [
                createVNode(QTab, {
                  name: "services",
                  label: "работы"
                }),
                createVNode(QTab, {
                  name: "clients",
                  label: "клиенты"
                })
              ]),
              _: 1
            }, 8, ["modelValue"]),
            createVNode(QSeparator),
            createVNode(QTabPanels, {
              modelValue: $setup.tab,
              "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => $setup.tab = $event),
              animated: ""
            }, {
              default: withCtx(() => [
                createVNode(QTabPanel, {
                  name: "services",
                  style: { "padding": "0" }
                }, {
                  default: withCtx(() => [
                    createBaseVNode("div", _hoisted_1, [
                      createVNode(QSelect, {
                        modelValue: $setup.selectedServiceCategory,
                        "onUpdate:modelValue": [
                          _cache[1] || (_cache[1] = ($event) => $setup.selectedServiceCategory = $event),
                          $setup.getServicesByCategory
                        ],
                        options: $setup.serviceCategories,
                        "option-label": "category_name",
                        "emit-value": "",
                        "map-options": "",
                        label: "категории работ",
                        dense: "",
                        placeholder: "нет категорий",
                        "label-color": "grey",
                        color: "yellow",
                        class: "col-9"
                      }, null, 8, ["modelValue", "options"]),
                      createBaseVNode("div", _hoisted_2, [
                        createVNode(QBtn, {
                          class: "col-1 text-yellow",
                          onClick: $setup.openNewServiceCategoryDialog
                        }, {
                          default: withCtx(() => _cache[16] || (_cache[16] = [
                            createTextVNode("+")
                          ])),
                          _: 1
                        })
                      ]),
                      createBaseVNode("div", _hoisted_3, [
                        createVNode(QBtn, {
                          class: "col-1 text-yellow",
                          icon: "edit",
                          onClick: $setup.openEditServiceCategoryDialog
                        })
                      ])
                    ]),
                    createVNode(QList, {
                      bordered: "",
                      separator: ""
                    }, {
                      default: withCtx(() => [
                        !$setup.services ? (openBlock(), createBlock(QItemLabel, { key: 0 }, {
                          default: withCtx(() => _cache[17] || (_cache[17] = [
                            createTextVNode("Нет сервисов")
                          ])),
                          _: 1
                        })) : createCommentVNode("", true),
                        (openBlock(true), createElementBlock(Fragment, null, renderList($setup.services, (service) => {
                          return withDirectives((openBlock(), createBlock(QItem, {
                            key: service,
                            class: "w-100 justify-between selectService",
                            style: { "width": "100%" },
                            clickable: "",
                            onClick: ($event) => $setup.openServiceDialog(service),
                            "q-item": _ctx.qItem
                          }, {
                            default: withCtx(() => [
                              createVNode(QItemSection, null, {
                                default: withCtx(() => [
                                  createVNode(QItemLabel, { class: "text-left" }, {
                                    default: withCtx(() => [
                                      createTextVNode(toDisplayString(service.service), 1)
                                    ]),
                                    _: 2
                                  }, 1024)
                                ]),
                                _: 2
                              }, 1024),
                              createVNode(QItemSection, null, {
                                default: withCtx(() => [
                                  createVNode(QItemLabel, { class: "text-right" }, {
                                    default: withCtx(() => [
                                      createTextVNode(toDisplayString(service.price), 1)
                                    ]),
                                    _: 2
                                  }, 1024)
                                ]),
                                _: 2
                              }, 1024),
                              createVNode(QBtn, {
                                icon: "add",
                                round: "",
                                class: "fab bg-yellow text-black",
                                onClick: _cache[2] || (_cache[2] = ($event) => console.log("не реализовано")),
                                size: "20px"
                              })
                            ]),
                            _: 2
                          }, 1032, ["onClick", "q-item"])), [
                            [Ripple]
                          ]);
                        }), 128))
                      ]),
                      _: 1
                    }),
                    createVNode(QBtn, {
                      icon: "add",
                      round: "",
                      class: "fab bg-yellow text-black",
                      onClick: $setup.openNewServiceDialog,
                      size: "20px"
                    })
                  ]),
                  _: 1
                }),
                createVNode(QTabPanel, {
                  name: "clients",
                  style: { "padding": "0" }
                }, {
                  default: withCtx(() => [
                    createVNode(QList, {
                      bordered: "",
                      separator: ""
                    }, {
                      default: withCtx(() => [
                        !$setup.clients ? (openBlock(), createBlock(QItemLabel, { key: 0 }, {
                          default: withCtx(() => _cache[18] || (_cache[18] = [
                            createTextVNode("Нет материалов")
                          ])),
                          _: 1
                        })) : createCommentVNode("", true),
                        (openBlock(true), createElementBlock(Fragment, null, renderList($setup.clients, (client) => {
                          return openBlock(), createBlock(QItem, {
                            key: client.id,
                            class: "w-100 justify-between row",
                            style: { "width": "100%" },
                            clickable: "",
                            onClick: ($event) => $setup.openClientDialog(client)
                          }, {
                            default: withCtx(() => [
                              createVNode(QItemSection, { class: "col-auto" }, {
                                default: withCtx(() => [
                                  createVNode(QItemLabel, { class: "text-left" }, {
                                    default: withCtx(() => [
                                      createTextVNode(toDisplayString(client.name), 1)
                                    ]),
                                    _: 2
                                  }, 1024)
                                ]),
                                _: 2
                              }, 1024),
                              createVNode(QItemSection, { class: "col-auto" }, {
                                default: withCtx(() => [
                                  createVNode(QItemLabel, { class: "text-right" }, {
                                    default: withCtx(() => [
                                      createTextVNode(toDisplayString(client.phone), 1)
                                    ]),
                                    _: 2
                                  }, 1024)
                                ]),
                                _: 2
                              }, 1024)
                            ]),
                            _: 2
                          }, 1032, ["onClick"]);
                        }), 128))
                      ]),
                      _: 1
                    }),
                    createVNode(QBtn, {
                      icon: "add",
                      round: "",
                      class: "fab bg-yellow text-black",
                      onClick: $setup.openNewClientDialog,
                      size: "20px"
                    })
                  ]),
                  _: 1
                })
              ]),
              _: 1
            }, 8, ["modelValue"])
          ]),
          _: 1
        })
      ]),
      _: 1
    }),
    createBaseVNode("div", null, [
      createVNode(QDialog, {
        modelValue: $setup.showClientsDetails,
        "onUpdate:modelValue": _cache[9] || (_cache[9] = ($event) => $setup.showClientsDetails = $event),
        persistent: ""
      }, {
        default: withCtx(() => [
          createVNode(QCard, null, {
            default: withCtx(() => [
              createVNode(QCardSection, null, {
                default: withCtx(() => [
                  _cache[19] || (_cache[19] = createBaseVNode("div", { class: "text-h6" }, "клиент", -1)),
                  createVNode(QInput, {
                    disable: !$setup.editClientMode,
                    modelValue: $setup.selectedClient.name,
                    "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => $setup.selectedClient.name = $event),
                    "label-color": "yellow",
                    color: "yellow",
                    label: "Имя клиента",
                    outlined: "",
                    class: "q-mb-md"
                  }, null, 8, ["disable", "modelValue"]),
                  createVNode(QInput, {
                    disable: !$setup.editClientMode,
                    modelValue: $setup.selectedClient.phone,
                    "onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => $setup.selectedClient.phone = $event),
                    "label-color": "yellow",
                    color: "yellow",
                    label: "телефон",
                    outlined: "",
                    class: "q-mb-md"
                  }, null, 8, ["disable", "modelValue"])
                ]),
                _: 1
              }),
              createVNode(QCardActions, { align: "right" }, {
                default: withCtx(() => [
                  $setup.editClientMode ? (openBlock(), createBlock(QBtn, {
                    key: 0,
                    flat: "",
                    label: "отмена",
                    color: "yellow",
                    onClick: _cache[6] || (_cache[6] = ($event) => $setup.editClientMode = false)
                  })) : createCommentVNode("", true),
                  !$setup.editClientMode ? (openBlock(), createBlock(QBtn, {
                    key: 1,
                    flat: "",
                    label: "закрыть",
                    color: "yellow",
                    onClick: _cache[7] || (_cache[7] = ($event) => $setup.showClientsDetails = false)
                  })) : createCommentVNode("", true),
                  !$setup.editClientMode ? (openBlock(), createBlock(QBtn, {
                    key: 2,
                    flat: "",
                    label: "редактировать",
                    color: "yellow",
                    onClick: _cache[8] || (_cache[8] = ($event) => $setup.editClientMode = true)
                  })) : createCommentVNode("", true),
                  $setup.editClientMode ? (openBlock(), createBlock(QBtn, {
                    key: 3,
                    flat: "",
                    label: "сохранить",
                    color: "yellow",
                    onClick: $setup.editClient
                  })) : createCommentVNode("", true),
                  !$setup.editClientMode ? (openBlock(), createBlock(QBtn, {
                    key: 4,
                    flat: "",
                    label: "удалить",
                    color: "yellow",
                    onClick: $setup.handleDelete
                  })) : createCommentVNode("", true)
                ]),
                _: 1
              })
            ]),
            _: 1
          })
        ]),
        _: 1
      }, 8, ["modelValue"]),
      createVNode(QDialog, {
        modelValue: $setup.showServiceDetails,
        "onUpdate:modelValue": _cache[15] || (_cache[15] = ($event) => $setup.showServiceDetails = $event),
        persistent: ""
      }, {
        default: withCtx(() => [
          createVNode(QCard, null, {
            default: withCtx(() => [
              createVNode(QCardSection, null, {
                default: withCtx(() => [
                  _cache[20] || (_cache[20] = createBaseVNode("div", { class: "text-h6" }, "сервис", -1)),
                  createVNode(QInput, {
                    disable: !$setup.editServiceMode,
                    modelValue: $setup.selectedService.service,
                    "onUpdate:modelValue": _cache[10] || (_cache[10] = ($event) => $setup.selectedService.service = $event),
                    "label-color": "yellow",
                    color: "yellow",
                    label: "название сервиса",
                    outlined: "",
                    class: "q-mb-md"
                  }, null, 8, ["disable", "modelValue"]),
                  createVNode(QInput, {
                    disable: !$setup.editServiceMode,
                    modelValue: $setup.selectedService.price,
                    "onUpdate:modelValue": _cache[11] || (_cache[11] = ($event) => $setup.selectedService.price = $event),
                    "label-color": "yellow",
                    color: "yellow",
                    label: "цена",
                    outlined: "",
                    class: "q-mb-md"
                  }, null, 8, ["disable", "modelValue"])
                ]),
                _: 1
              }),
              createVNode(QCardActions, { align: "right" }, {
                default: withCtx(() => [
                  $setup.editServiceMode ? (openBlock(), createBlock(QBtn, {
                    key: 0,
                    flat: "",
                    label: "отмена",
                    color: "yellow",
                    onClick: _cache[12] || (_cache[12] = ($event) => $setup.editServiceMode = false)
                  })) : createCommentVNode("", true),
                  !$setup.editServiceMode ? (openBlock(), createBlock(QBtn, {
                    key: 1,
                    flat: "",
                    label: "закрыть",
                    color: "yellow",
                    onClick: _cache[13] || (_cache[13] = ($event) => $setup.showServiceDetails = false)
                  })) : createCommentVNode("", true),
                  !$setup.editServiceMode ? (openBlock(), createBlock(QBtn, {
                    key: 2,
                    flat: "",
                    label: "редактировать",
                    color: "yellow",
                    onClick: _cache[14] || (_cache[14] = ($event) => $setup.editServiceMode = true)
                  })) : createCommentVNode("", true),
                  $setup.editServiceMode ? (openBlock(), createBlock(QBtn, {
                    key: 3,
                    flat: "",
                    label: "сохранить",
                    color: "yellow",
                    onClick: $setup.editService
                  })) : createCommentVNode("", true),
                  !$setup.editServiceMode ? (openBlock(), createBlock(QBtn, {
                    key: 4,
                    flat: "",
                    label: "удалить",
                    color: "yellow",
                    onClick: $setup.handleDelete
                  })) : createCommentVNode("", true)
                ]),
                _: 1
              })
            ]),
            _: 1
          })
        ]),
        _: 1
      }, 8, ["modelValue"]),
      createVNode($setup["DeleteConfirmPage"], { ref: "confirmDialog" }, null, 512),
      createVNode($setup["NewClientDialogPage"], {
        ref: "newClientDialog",
        onClientAdded: $setup.handleClientAdded
      }, null, 512),
      createVNode($setup["NewServiceDialogPage"], {
        ref: "newServiceDialog",
        onServiceAdded: $setup.handleServiceAdded,
        data: $setup.selectedServiceCategory
      }, null, 8, ["data"]),
      createVNode($setup["NewServiceCategoryDialogPage"], {
        ref: "newServiceCategoryDialog",
        onService_categoryAdded: $setup.handleServiceCategoryAdded
      }, null, 512),
      createVNode($setup["EditServiceCategoryDialogPage"], {
        ref: "editServiceCategoryDialog",
        onService_categoryEdited: $setup.handleServiceCategoryEdited,
        data: $setup.selectedServiceCategory
      }, null, 8, ["data"])
    ])
  ], 64);
}
const CatalogPage = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render], ["__scopeId", "data-v-4266a777"], ["__file", "CatalogPage.vue"]]);
export {
  CatalogPage as default
};
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiQ2F0YWxvZ1BhZ2UtQl9hY01RNTAuanMiLCJzb3VyY2VzIjpbIi4uLy4uLy4uL3NyYy9wYWdlcy9kaWFsb2dzL05ld0NsaWVudERpYWxvZ1BhZ2UudnVlIiwiLi4vLi4vLi4vc3JjL3BhZ2VzL2RpYWxvZ3MvTmV3U2VydmljZURpYWxvZ1BhZ2UudnVlIiwiLi4vLi4vLi4vc3JjL3BhZ2VzL2RpYWxvZ3MvTmV3U2VydmljZUNhdGVnb3J5RGlhbG9nUGFnZS52dWUiLCIuLi8uLi8uLi9zcmMvcGFnZXMvZGlhbG9ncy9FZGl0U2VydmljZUNhdGVnb3J5RGlhbG9nUGFnZS52dWUiLCIuLi8uLi8uLi9zcmMvcGFnZXMvQ2F0YWxvZ1BhZ2UudnVlIl0sInNvdXJjZXNDb250ZW50IjpbIjxzY3JpcHQgc2V0dXA+XG5pbXBvcnQge3JlZn0gZnJvbSAndnVlJ1xuaW1wb3J0IHthcGl9IGZyb20gXCJib290L2F4aW9zLmpzXCI7XG5pbXBvcnQge3VzZVNwZWNpYWxpemF0aW9uc1N0b3JlfSBmcm9tIFwic3RvcmVzL3NwZWNpYWxpemF0aW9ucy5qc1wiO1xuXG5jb25zdCBzcGVjaWFsaXphdGlvblN0b3JlID0gdXNlU3BlY2lhbGl6YXRpb25zU3RvcmUoKVxuXG5jb25zdCBzZWxlY3RlZFNwZWNpYWxpemF0aW9uSWQ9IHNwZWNpYWxpemF0aW9uU3RvcmUuZ2V0U2VsZWN0ZWRTcGVjaWFsaXphdGlvbi5pZFxuY29uc3QgaXNPcGVuID0gcmVmKGZhbHNlKVxuY29uc3QgbmFtZSA9IHJlZignJylcbmNvbnN0IHBob25lID0gcmVmKCcnKVxuXG5jb25zdCBlbWl0ID0gZGVmaW5lRW1pdHMoWydjbGllbnQtYWRkZWQnXSlcblxuZnVuY3Rpb24gb3Blbigpe1xuICBpc09wZW4udmFsdWUgPSB0cnVlXG59XG5cbmZ1bmN0aW9uIGNsb3NlKCl7XG4gIGlzT3Blbi52YWx1ZSA9IGZhbHNlXG59XG5cbmRlZmluZUV4cG9zZSh7b3Blbn0pXG5cbmNvbnN0IGFkZE5ldyA9IGFzeW5jICgpID0+IHtcbiAgdHJ5IHtcbiAgICBjb25zdCByZXNwb25zZSA9IGF3YWl0IGFwaS5wb3N0KCcvYWRkX2NsaWVudCcsIHtcbiAgICAgIG5hbWU6IG5hbWUudmFsdWUsXG4gICAgICBwaG9uZTogcGhvbmUudmFsdWUsXG4gICAgICBzcGVjaWFsaXphdGlvbl9pZDogc2VsZWN0ZWRTcGVjaWFsaXphdGlvbklkXG4gICAgfSlcbiAgICBuYW1lLnZhbHVlID0gJydcbiAgICBwaG9uZS52YWx1ZSA9ICcnXG4gICAgY2xvc2UoKVxuICAgIGVtaXQoJ2NsaWVudC1hZGRlZCcsIHJlc3BvbnNlLmRhdGEpXG4gICAgY29uc29sZS5sb2coJ3Jlc3BvbnNlJywgcmVzcG9uc2UpXG4gIH0gY2F0Y2ggKGVycikge1xuICAgIGNvbnNvbGUuZXJyb3IoJ9C+0YjQuNCx0LrQsCDQtNC+0LHQsNCy0LvQtdC90LjRjyDQutC70LjQtdC90YLQsDogJywgZXJyKVxuICB9XG59XG5cbjwvc2NyaXB0PlxuXG48dGVtcGxhdGU+XG5cbiAgPHEtZGlhbG9nIHYtbW9kZWw9XCJpc09wZW5cIj5cbiAgICA8cS1jYXJkPlxuICAgICAgPHEtY2FyZC1zZWN0aW9uPlxuICAgICAgICA8ZGl2IGNsYXNzPVwidGV4dC1oNlwiPiDQvdC+0LLRi9C5INC60LvQuNC10L3RgjwvZGl2PlxuICAgICAgICA8cS1pbnB1dCB2LW1vZGVsPVwibmFtZVwiIGxhYmVsPVwi0JjQvNGPINC60LvQuNC10L3RgtCwXCIgb3V0bGluZWQgY2xhc3M9XCJxLW1iLW1kXCIgLz5cbiAgICAgICAgPHEtaW5wdXQgdi1tb2RlbD1cInBob25lXCIgbGFiZWw9XCLRgtC10LvQtdGE0L7QvVwiIG91dGxpbmVkIGNsYXNzPVwicS1tYi1tZFwiIC8+XG4gICAgICA8L3EtY2FyZC1zZWN0aW9uPlxuICAgICAgPHEtY2FyZC1hY3Rpb25zIGFsaWduPVwicmlnaHRcIj5cbiAgICAgICAgPHEtYnRuIGZsYXQgbGFiZWw9XCLQvtGC0LzQtdC90LBcIiBjb2xvcj1cInllbGxvd1wiIEBjbGljaz1cImNsb3NlXCIgLz5cbiAgICAgICAgPHEtYnRuIGZsYXQgbGFiZWw9XCLRgdC+0YXRgNCw0L3QuNGC0YxcIiBjb2xvcj1cInllbGxvd1wiIEBjbGljaz1cImFkZE5ld1wiIC8+XG4gICAgICA8L3EtY2FyZC1hY3Rpb25zPlxuICAgIDwvcS1jYXJkPlxuICA8L3EtZGlhbG9nPlxuXG48L3RlbXBsYXRlPlxuXG48c3R5bGUgc2NvcGVkPlxuXG48L3N0eWxlPlxuIiwiPHNjcmlwdCBzZXR1cD5cbmltcG9ydCB7cmVmfSBmcm9tICd2dWUnXG5pbXBvcnQge2FwaX0gZnJvbSBcImJvb3QvYXhpb3MuanNcIjtcbmltcG9ydCB7dXNlU3BlY2lhbGl6YXRpb25zU3RvcmV9IGZyb20gXCJzdG9yZXMvc3BlY2lhbGl6YXRpb25zLmpzXCI7XG5cbmNvbnN0IHNwZWNpYWxpemF0aW9uU3RvcmUgPSB1c2VTcGVjaWFsaXphdGlvbnNTdG9yZSgpXG5cbmNvbnN0IHNlbGVjdGVkU3BlY2lhbGl6YXRpb25JZD0gc3BlY2lhbGl6YXRpb25TdG9yZS5nZXRTZWxlY3RlZFNwZWNpYWxpemF0aW9uLmlkXG5jb25zdCBpc09wZW4gPSByZWYoZmFsc2UpXG5jb25zdCBuYW1lID0gcmVmKCcnKVxuY29uc3QgcHJpY2UgPSByZWYoJycpXG5jb25zdCBjYXRlZ29yeUlkID0gcmVmKCcnKVxuXG5jb25zdCBlbWl0ID0gZGVmaW5lRW1pdHMoWydzZXJ2aWNlLWFkZGVkJ10pXG5cbmNvbnN0IHByb3BzID0gZGVmaW5lUHJvcHMoe1xuICBkYXRhOiB7dHlwZTogTnVtYmVyfVxufSlcblxuZnVuY3Rpb24gb3Blbigpe1xuICBpc09wZW4udmFsdWUgPSB0cnVlXG4gIGNhdGVnb3J5SWQudmFsdWUgPSBwcm9wcy5kYXRhLnZhbHVlXG59XG5cbmZ1bmN0aW9uIGNsb3NlKCl7XG4gIGlzT3Blbi52YWx1ZSA9IGZhbHNlXG4gIGNhdGVnb3J5SWQudmFsdWUgPSAnJ1xufVxuXG5kZWZpbmVFeHBvc2Uoe29wZW59KVxuXG5jb25zdCBhZGROZXcgPSBhc3luYyAoKSA9PiB7XG4gIHRyeSB7XG4gICAgY29uc29sZS5sb2coJ2NhdGVnb3J5X2lkOiAnLCBwcm9wcy5kYXRhLmlkKVxuICAgIGNvbnN0IHJlc3BvbnNlID0gYXdhaXQgYXBpLnBvc3QoJy9hZGRfc2VydmljZScsIHtcbiAgICAgIHNlcnZpY2U6IG5hbWUudmFsdWUsXG4gICAgICBwcmljZTogcHJpY2UudmFsdWUsXG4gICAgICBjYXRlZ29yeV9pZDogcHJvcHMuZGF0YS5pZCxcbiAgICAgIHNwZWNpYWxpemF0aW9uX2lkOiBzZWxlY3RlZFNwZWNpYWxpemF0aW9uSWRcbiAgICB9KVxuICAgIG5hbWUudmFsdWUgPSAnJ1xuICAgIHByaWNlLnZhbHVlID0gJydcbiAgICBjYXRlZ29yeUlkLnZhbHVlID0gJydcbiAgICBjbG9zZSgpXG4gICAgZW1pdCgnc2VydmljZS1hZGRlZCcsIHJlc3BvbnNlLmRhdGEpXG4gICAgY29uc29sZS5sb2coJ3Jlc3BvbnNlJywgcmVzcG9uc2UpXG4gIH0gY2F0Y2ggKGVycikge1xuICAgIGNvbnNvbGUuZXJyb3IoJ9C+0YjQuNCx0LrQsCDQtNC+0LHQsNCy0LvQtdC90LjRjyDRgdC10YDQstC40YHQsDogJywgZXJyKVxuICB9XG59XG5cbjwvc2NyaXB0PlxuXG48dGVtcGxhdGU+XG5cbiAgPHEtZGlhbG9nIHYtbW9kZWw9XCJpc09wZW5cIj5cbiAgICA8cS1jYXJkPlxuICAgICAgPHEtY2FyZC1zZWN0aW9uPlxuICAgICAgICA8ZGl2IGNsYXNzPVwidGV4dC1oNlwiPiDQvdC+0LLRi9C5INGB0LXRgNCy0LjRgTwvZGl2PlxuICAgICAgICA8cS1pbnB1dCB2LW1vZGVsPVwibmFtZVwiIGxhYmVsPVwi0J3QsNC30LLQsNC90LjQtSDRgdC10YDQstC40YHQsFwiIG91dGxpbmVkIGNsYXNzPVwicS1tYi1tZFwiLz5cbiAgICAgICAgPHEtaW5wdXQgdi1tb2RlbD1cInByaWNlXCIgbGFiZWw9XCLRhtC10L3QsFwiIG91dGxpbmVkIGNsYXNzPVwicS1tYi1tZFwiIC8+XG4gICAgICA8L3EtY2FyZC1zZWN0aW9uPlxuICAgICAgPHEtY2FyZC1hY3Rpb25zIGFsaWduPVwicmlnaHRcIj5cbiAgICAgICAgPHEtYnRuIGZsYXQgbGFiZWw9XCLQvtGC0LzQtdC90LBcIiBjb2xvcj1cInllbGxvd1wiIEBjbGljaz1cImNsb3NlXCIgLz5cbiAgICAgICAgPHEtYnRuIGZsYXQgbGFiZWw9XCLRgdC+0YXRgNCw0L3QuNGC0YxcIiBjb2xvcj1cInllbGxvd1wiIEBjbGljaz1cImFkZE5ld1wiIC8+XG4gICAgICA8L3EtY2FyZC1hY3Rpb25zPlxuICAgIDwvcS1jYXJkPlxuICA8L3EtZGlhbG9nPlxuXG48L3RlbXBsYXRlPlxuXG48c3R5bGUgc2NvcGVkPlxuXG48L3N0eWxlPlxuIiwiPHNjcmlwdCBzZXR1cD5cbmltcG9ydCB7cmVmfSBmcm9tICd2dWUnXG5pbXBvcnQge2FwaX0gZnJvbSBcImJvb3QvYXhpb3MuanNcIjtcbmltcG9ydCB7dXNlU3BlY2lhbGl6YXRpb25zU3RvcmV9IGZyb20gXCJzdG9yZXMvc3BlY2lhbGl6YXRpb25zLmpzXCI7XG5cbmNvbnN0IHNwZWNpYWxpemF0aW9uU3RvcmUgPSB1c2VTcGVjaWFsaXphdGlvbnNTdG9yZSgpXG5cbmNvbnN0IHNlbGVjdGVkU3BlY2lhbGl6YXRpb25JZD0gc3BlY2lhbGl6YXRpb25TdG9yZS5nZXRTZWxlY3RlZFNwZWNpYWxpemF0aW9uLmlkXG5jb25zdCBpc09wZW4gPSByZWYoZmFsc2UpXG5jb25zdCBuYW1lID0gcmVmKCcnKVxuXG5jb25zdCBlbWl0ID0gZGVmaW5lRW1pdHMoWydzZXJ2aWNlX2NhdGVnb3J5LWFkZGVkJ10pXG5cbmZ1bmN0aW9uIG9wZW4oKXtcbiAgaXNPcGVuLnZhbHVlID0gdHJ1ZVxufVxuXG5mdW5jdGlvbiBjbG9zZSgpe1xuICBpc09wZW4udmFsdWUgPSBmYWxzZVxufVxuXG5kZWZpbmVFeHBvc2Uoe29wZW59KVxuXG5jb25zdCBhZGROZXcgPSBhc3luYyAoKSA9PiB7XG4gIHRyeSB7XG4gICAgY29uc3QgcmVzcG9uc2UgPSBhd2FpdCBhcGkucG9zdCgnL2FkZF9jYXRlZ29yeScsIHtcbiAgICAgIGNhdGVnb3J5X25hbWU6IG5hbWUudmFsdWUsXG4gICAgICBzcGVjaWFsaXphdGlvbl9pZDogc2VsZWN0ZWRTcGVjaWFsaXphdGlvbklkXG4gICAgfSlcbiAgICBuYW1lLnZhbHVlID0gJydcbiAgICBjbG9zZSgpXG4gICAgZW1pdCgnc2VydmljZV9jYXRlZ29yeS1hZGRlZCcsIHJlc3BvbnNlLmRhdGEpXG4gICAgY29uc29sZS5sb2coJ3Jlc3BvbnNlJywgcmVzcG9uc2UpXG4gIH0gY2F0Y2ggKGVycikge1xuICAgIGNvbnNvbGUuZXJyb3IoJ9C+0YjQuNCx0LrQsCDQtNC+0LHQsNCy0LvQtdC90LjRjyDRgdC10YDQstC40YEg0LrQsNGC0LXQs9C+0YDQuNC4OiAnLCBlcnIpXG4gIH1cbn1cblxuPC9zY3JpcHQ+XG5cbjx0ZW1wbGF0ZT5cblxuICA8cS1kaWFsb2cgdi1tb2RlbD1cImlzT3BlblwiPlxuICAgIDxxLWNhcmQ+XG4gICAgICA8cS1jYXJkLXNlY3Rpb24+XG4gICAgICAgIDxkaXYgY2xhc3M9XCJ0ZXh0LWg2XCI+INC90L7QstCw0Y8g0LrQsNGC0LXQs9C+0YDQuNGPPC9kaXY+XG4gICAgICAgIDxxLWlucHV0IHYtbW9kZWw9XCJuYW1lXCIgbGFiZWw9XCLQndCw0LfQstCw0L3QuNC1INC60LDRgtC10LPQvtGA0LjQuFwiIG91dGxpbmVkIGNsYXNzPVwicS1tYi1tZFwiIC8+XG4gICAgICA8L3EtY2FyZC1zZWN0aW9uPlxuICAgICAgPHEtY2FyZC1hY3Rpb25zIGFsaWduPVwicmlnaHRcIj5cbiAgICAgICAgPHEtYnRuIGZsYXQgbGFiZWw9XCLQvtGC0LzQtdC90LBcIiBjb2xvcj1cInllbGxvd1wiIEBjbGljaz1cImNsb3NlXCIgLz5cbiAgICAgICAgPHEtYnRuIGZsYXQgbGFiZWw9XCLRgdC+0YXRgNCw0L3QuNGC0YxcIiBjb2xvcj1cInllbGxvd1wiIEBjbGljaz1cImFkZE5ld1wiIC8+XG4gICAgICA8L3EtY2FyZC1hY3Rpb25zPlxuICAgIDwvcS1jYXJkPlxuICA8L3EtZGlhbG9nPlxuXG48L3RlbXBsYXRlPlxuXG48c3R5bGUgc2NvcGVkPlxuXG48L3N0eWxlPlxuIiwiPHNjcmlwdCBzZXR1cD5cbmltcG9ydCB7cmVmfSBmcm9tICd2dWUnXG5pbXBvcnQge2FwaX0gZnJvbSBcImJvb3QvYXhpb3MuanNcIjtcblxuXG5jb25zdCBpc09wZW4gPSByZWYoZmFsc2UpXG5jb25zdCBuYW1lID0gcmVmKCcnKVxuXG5jb25zdCBwcm9wcyA9IGRlZmluZVByb3BzKHtcbiAgZGF0YToge1xuICAgIHR5cGU6IE9iamVjdCxcbiAgICBkZWZhdWx0OiAoKSA9PiAoe30pXG4gIH1cbn0pXG5cbmNvbnN0IGVtaXQgPSBkZWZpbmVFbWl0cyhbJ3NlcnZpY2VfY2F0ZWdvcnktZWRpdGVkJ10pXG5cbmZ1bmN0aW9uIG9wZW4oKXtcbiAgaXNPcGVuLnZhbHVlID0gdHJ1ZVxuICBuYW1lLnZhbHVlID0gcHJvcHMuZGF0YS5jYXRlZ29yeV9uYW1lXG59XG5cbmZ1bmN0aW9uIGNsb3NlKCl7XG4gIGlzT3Blbi52YWx1ZSA9IGZhbHNlXG4gIG5hbWUudmFsdWUgPSAnJ1xufVxuXG5kZWZpbmVFeHBvc2Uoe29wZW59KVxuXG5jb25zdCBlZGl0ID0gYXN5bmMgKCkgPT4ge1xuICBjb25zb2xlLmxvZygncHJvcHM6ICcsIHByb3BzLmRhdGEpXG4gIHRyeSB7XG4gICAgY29uc3QgcmVzcG9uc2UgPSBhd2FpdCBhcGkucG9zdCgnL2VkaXRfY2F0ZWdvcnknLCB7XG4gICAgICBpZDogcHJvcHMuZGF0YS5pZCxcbiAgICAgIGNhdGVnb3J5X25hbWU6IG5hbWUudmFsdWUsXG4gICAgfSlcbiAgICBuYW1lLnZhbHVlID0gJydcbiAgICBjbG9zZSgpXG4gICAgZW1pdCgnc2VydmljZV9jYXRlZ29yeS1lZGl0ZWQnLCByZXNwb25zZS5kYXRhKVxuICAgIGNvbnNvbGUubG9nKCdyZXNwb25zZScsIHJlc3BvbnNlKVxuICB9IGNhdGNoIChlcnIpIHtcbiAgICBjb25zb2xlLmVycm9yKCfQvtGI0LjQsdC60LAg0LTQvtCx0LDQstC70LXQvdC40Y8g0YHQtdGA0LLQuNGBINC60LDRgtC10LPQvtGA0LjQuDogJywgZXJyKVxuICB9XG59XG5cbmNvbnN0IGRlbGV0ZVNlcnZpY2VDYXRlZ29yeSA9IGFzeW5jICgpID0+IHtcbiAgY29uc29sZS5sb2coJ2NhdGVnb3J5SWQ6ICcsIHByb3BzLmRhdGEuaWQpXG4gIHRyeSB7XG4gICAgY29uc3QgcmVzcG9uc2UgPSBhd2FpdCBhcGkucG9zdCgnL2RlbGV0ZV9jYXRlZ29yeScsIHtcbiAgICAgIGNhdGVnb3J5SWQ6IHByb3BzLmRhdGEuaWRcbiAgICB9KVxuICAgIGNsb3NlKClcbiAgICBlbWl0KCdzZXJ2aWNlX2NhdGVnb3J5LWVkaXRlZCcsIHJlc3BvbnNlLmRhdGEpXG4gIH0gY2F0Y2ggKGVycikge1xuICAgIGNvbnNvbGUuZXJyb3IoJ9C+0YjQuNCx0LrQsCDRg9C00LDQu9C10L3QuNGPINC60LDRgtC10LPQvtGA0LjQuDogJywgZXJyKVxuICB9XG59XG5cbjwvc2NyaXB0PlxuXG48dGVtcGxhdGU+XG5cbiAgPHEtZGlhbG9nIHYtbW9kZWw9XCJpc09wZW5cIj5cbiAgICA8cS1jYXJkPlxuICAgICAgPHEtY2FyZC1zZWN0aW9uPlxuICAgICAgICA8ZGl2IGNsYXNzPVwidGV4dC1oNlwiPiDRgNC10LTQsNC60YLQuNGA0L7QstCw0L3QuNC1INC60LDRgtC10LPQvtGA0LjQuDwvZGl2PlxuICAgICAgICA8cS1pbnB1dCB2LW1vZGVsPVwibmFtZVwiXG4gICAgICAgICAgICAgICAgIGxhYmVsPVwi0J3QsNC30LLQsNC90LjQtSDQutCw0YLQtdCz0L7RgNC40LhcIlxuICAgICAgICAgICAgICAgICBvdXRsaW5lZCBjbGFzcz1cInEtbWItbWRcIlxuICAgICAgICAgICAgICAgICA6cnVsZXM9XCJbdmFsID0+ICEhdmFsIHx8ICfQntCx0Y/Qt9Cw0YLQtdC70YzQvdC+0LUg0L/QvtC70LUnXVwiXG4gICAgICAgIC8+XG4gICAgICA8L3EtY2FyZC1zZWN0aW9uPlxuICAgICAgPHEtY2FyZC1hY3Rpb25zIGFsaWduPVwicmlnaHRcIj5cbiAgICAgICAgPHEtYnRuIGZsYXQgbGFiZWw9XCLRg9C00LDQu9C40YLRjFwiIGNvbG9yPVwieWVsbG93XCIgQGNsaWNrPVwiZGVsZXRlU2VydmljZUNhdGVnb3J5XCIgLz5cbiAgICAgICAgPHEtYnRuIGZsYXQgbGFiZWw9XCLQvtGC0LzQtdC90LBcIiBjb2xvcj1cInllbGxvd1wiIEBjbGljaz1cImNsb3NlXCIgLz5cbiAgICAgICAgPHEtYnRuIGZsYXQgbGFiZWw9XCLRgdC+0YXRgNCw0L3QuNGC0YxcIiBjb2xvcj1cInllbGxvd1wiIEBjbGljaz1cImVkaXRcIiAvPlxuICAgICAgPC9xLWNhcmQtYWN0aW9ucz5cbiAgICA8L3EtY2FyZD5cbiAgPC9xLWRpYWxvZz5cblxuPC90ZW1wbGF0ZT5cblxuPHN0eWxlIHNjb3BlZD5cblxuPC9zdHlsZT5cbiIsIjxzY3JpcHQgc2V0dXA+XG5pbXBvcnQge29uTW91bnRlZCwgcmVmfSBmcm9tICd2dWUnXG5pbXBvcnQge2FwaX0gZnJvbSBcImJvb3QvYXhpb3MuanNcIjtcbmltcG9ydCB7dXNlU3BlY2lhbGl6YXRpb25zU3RvcmV9IGZyb20gXCJzdG9yZXMvc3BlY2lhbGl6YXRpb25zLmpzXCI7XG5pbXBvcnQgRGVsZXRlQ29uZmlybVBhZ2UgZnJvbSBcInBhZ2VzL0RlbGV0ZUNvbmZpcm1QYWdlLnZ1ZVwiO1xuaW1wb3J0IE5ld0NsaWVudERpYWxvZ1BhZ2UgZnJvbSBcInBhZ2VzL2RpYWxvZ3MvTmV3Q2xpZW50RGlhbG9nUGFnZS52dWVcIjtcbmltcG9ydCBOZXdTZXJ2aWNlRGlhbG9nUGFnZSBmcm9tIFwicGFnZXMvZGlhbG9ncy9OZXdTZXJ2aWNlRGlhbG9nUGFnZS52dWVcIjtcbmltcG9ydCBOZXdTZXJ2aWNlQ2F0ZWdvcnlEaWFsb2dQYWdlIGZyb20gXCJwYWdlcy9kaWFsb2dzL05ld1NlcnZpY2VDYXRlZ29yeURpYWxvZ1BhZ2UudnVlXCI7XG5pbXBvcnQgRWRpdFNlcnZpY2VDYXRlZ29yeURpYWxvZ1BhZ2UgZnJvbSBcInBhZ2VzL2RpYWxvZ3MvRWRpdFNlcnZpY2VDYXRlZ29yeURpYWxvZ1BhZ2UudnVlXCI7XG5cbmNvbnN0IG5ld0NsaWVudERpYWxvZyA9IHJlZihudWxsKVxuY29uc3QgbmV3U2VydmljZURpYWxvZyA9IHJlZihudWxsKVxuY29uc3QgbmV3U2VydmljZUNhdGVnb3J5RGlhbG9nID0gcmVmKG51bGwpXG5jb25zdCBlZGl0U2VydmljZUNhdGVnb3J5RGlhbG9nID0gcmVmKG51bGwpXG5cbmNvbnN0IGNvbmZpcm1EaWFsb2cgPSByZWYobnVsbClcbmNvbnN0IHNwZWNpYWxpemF0aW9uU3RvcmUgPSB1c2VTcGVjaWFsaXphdGlvbnNTdG9yZSgpXG5cbmNvbnN0IHRhYiA9IHJlZignc2VydmljZXMnKVxuXG5jb25zdCBlZGl0Q2xpZW50TW9kZSA9IHJlZihmYWxzZSlcbmNvbnN0IGVkaXRTZXJ2aWNlTW9kZSA9IHJlZihmYWxzZSlcblxuY29uc3QgY2xpZW50cyA9IHJlZihbXSlcbmNvbnN0IHNlcnZpY2VzID0gcmVmKFtdKVxuY29uc3Qgc2VydmljZUNhdGVnb3JpZXMgPSByZWYoW10pXG5jb25zdCBzZWxlY3RlZENsaWVudCA9IHJlZihudWxsKVxuY29uc3Qgc2VsZWN0ZWRTZXJ2aWNlID0gcmVmKG51bGwpXG5jb25zdCBzZWxlY3RlZFNlcnZpY2VDYXRlZ29yeSA9IHJlZihudWxsKVxuXG5jb25zdCBzZWxlY3RlZFNwZWNpYWxpemF0aW9uSWQgPSBzcGVjaWFsaXphdGlvblN0b3JlLmdldFNlbGVjdGVkU3BlY2lhbGl6YXRpb24uaWRcblxuY29uc3Qgc2hvd0NsaWVudHNEZXRhaWxzID0gcmVmKGZhbHNlKVxuY29uc3Qgc2hvd1NlcnZpY2VEZXRhaWxzID0gcmVmKGZhbHNlKVxuXG5vbk1vdW50ZWQoKCkgPT4ge1xuICBnZXRDbGllbnRzKClcbiAgZ2V0U2VydmljZUNhdGVnb3JpZXMoKVxuICBnZXRTZXJ2aWNlc0J5Q2F0ZWdvcnkoKVxufSlcblxuZnVuY3Rpb24gaGFuZGxlRGVsZXRlKCl7XG4gIGlmIChzaG93Q2xpZW50c0RldGFpbHMudmFsdWUpIHtcbiAgICBjb25maXJtRGlhbG9nLnZhbHVlLm9wZW4oXG4gICAgICAn0KPQtNCw0LvQtdC90LjQtSDQutC70LjQtdC90YLQsCcsXG4gICAgICBg0JLRiyDRg9Cy0LXRgNC10L3RiyDRh9GC0L4g0YXQvtGC0LjRgtC1INGD0LTQsNC70LjRgtGMINC60LvQuNC10L3RgtCwIFwiJHtzZWxlY3RlZENsaWVudC52YWx1ZS5uYW1lfVwiID9gLFxuICAgICAgKCkgPT4ge2RlbGV0ZUNsaWVudCgpfVxuICAgIClcbiAgfSBlbHNlIGlmIChzaG93U2VydmljZURldGFpbHMudmFsdWUpe1xuICAgIGNvbmZpcm1EaWFsb2cudmFsdWUub3BlbihcbiAgICAgICfQo9C00LDQu9C10L3QuNC1INGD0YHQu9GD0LPQuCcsXG4gICAgICBg0JLRiyDRg9Cy0YDQtdC90Ysg0YfRgtC+INGF0L7RgtC40YLQtSDRg9C00LDQu9C40YLRjCDRgdC10YDQstC40YEgXCIke3NlbGVjdGVkU2VydmljZS52YWx1ZS5zZXJ2aWNlfVwiYCxcbiAgICAgICgpID0+IHtkZWxldGVTZXJ2aWNlKCl9XG4gICAgKVxuICB9XG59XG5cbmNvbnN0IGdldENsaWVudHMgPSBhc3luYyAoKSA9PiB7XG4gIHRyeSB7XG4gICAgY29uc3QgcmVzcG9uc2UgPSBhd2FpdCBhcGkuZ2V0KGAvZ2V0X2NsaWVudHMvJHtzZWxlY3RlZFNwZWNpYWxpemF0aW9uSWR9YClcbiAgICBjbGllbnRzLnZhbHVlID0gcmVzcG9uc2UuZGF0YVxuICAgIGNvbnNvbGUubG9nKCdjbGllbnRzOiAnLCBjbGllbnRzLnZhbHVlKVxuICB9IGNhdGNoIChlcnIpIHtcbiAgICBjb25zb2xlLmVycm9yKCfQvtGI0LjQsdC60LAg0L/QvtC70YPRh9C10L3QuNGPINC60LvQuNC10L3RgtC+0LI6ICcsIGVycilcbiAgfVxufVxuXG5jb25zdCBnZXRTZXJ2aWNlQ2F0ZWdvcmllcyA9IGFzeW5jICgpID0+IHtcbiAgY29uc3Qgc3BlY2lhbGl6YXRpb25TdG9yZSA9IHVzZVNwZWNpYWxpemF0aW9uc1N0b3JlKClcbiAgdHJ5IHtcbiAgICBjb25zdCBzcGVjaWFsaXphdGlvbklkID0gc3BlY2lhbGl6YXRpb25TdG9yZS5nZXRTZWxlY3RlZFNwZWNpYWxpemF0aW9uLmlkXG4gICAgY29uc29sZS5sb2coJ9GB0L/QtdGG0LjQsNC70LjQt9Cw0YbQuNGPOiAnLCBzcGVjaWFsaXphdGlvbklkKVxuICAgIGNvbnN0IHJlc3BvbnNlID0gYXdhaXQgYXBpLmdldChgL2dldF9jYXRlZ29yaWVzLyR7c3BlY2lhbGl6YXRpb25JZH1gKVxuICAgIHNlcnZpY2VDYXRlZ29yaWVzLnZhbHVlID0gcmVzcG9uc2UuZGF0YVxuICAgIGNvbnNvbGUubG9nKCfRgdC10YDQstC40YEg0LrQsNGC0LXQs9C+0YDQuNC4OiAnLCBzZXJ2aWNlQ2F0ZWdvcmllcy52YWx1ZSlcbiAgfSBjYXRjaCAoZXJyKSB7XG4gICAgY29uc29sZS5lcnJvcign0L7RiNC40LHQutCwINC30LDQs9GA0YPQt9C60Lgg0YHQtdGA0LLQuNGBINC60LDRgtC10LPQvtGA0LjQuScsIGVycilcbiAgfVxufVxuXG5jb25zdCBnZXRTZXJ2aWNlc0J5Q2F0ZWdvcnkgPSBhc3luYyAoY2F0ZWdvcnlJZCkgPT4ge1xuICBjb25zb2xlLmxvZygn0L/QvtC00LPRgNGD0LbQsNC10Lwg0YHQtdGA0LLQuNGB0Ysg0LrQsNGC0LXQs9C+0YDQuNC4OiAnLCBjYXRlZ29yeUlkKVxuICBjb25zb2xlLmxvZygnc2VsZWN0ZWRTZXJ2aWNlQ2F0ZWdvcnk6ICcsIHNlbGVjdGVkU2VydmljZUNhdGVnb3J5LnZhbHVlKVxuICB0cnkge1xuICAgIGNvbnN0IHJlc3BvbnNlID0gYXdhaXQgYXBpLmdldChgL2dldF9zZXJ2aWNlLyR7c2VsZWN0ZWRTZXJ2aWNlQ2F0ZWdvcnkudmFsdWUuaWR9YClcbiAgICBzZXJ2aWNlcy52YWx1ZSA9IHJlc3BvbnNlLmRhdGFcbiAgICBjb25zb2xlLmxvZygn0L/QvtC00LPRgNGD0LbQtdC90Ysg0YHQtdGA0LLQuNGB0Ysg0LrQsNGC0LXQs9C+0YDQuNC4OiAnLCBzZXJ2aWNlcy52YWx1ZSlcbiAgfSBjYXRjaCAoZXJyKSB7XG4gICAgY29uc29sZS5lcnJvcign0L7RiNC40LHQutCwINC30LDQs9GA0YPQt9C60LUg0YHQtdGA0LLQuNGB0L7QsiDQtNCw0L3QvdC+0Lkg0LrQsNGC0LXQs9C+0YDQuNC4OiAnLCBjYXRlZ29yeUlkICwgZXJyIClcbiAgfVxufVxuXG5jb25zdCBvcGVuQ2xpZW50RGlhbG9nID0gKGNsaWVudCkgPT4ge1xuICBzZWxlY3RlZENsaWVudC52YWx1ZSA9IHsuLi5jbGllbnR9XG4gIHNob3dDbGllbnRzRGV0YWlscy52YWx1ZSA9IHRydWVcbn1cblxuY29uc3Qgb3BlblNlcnZpY2VEaWFsb2cgPSAoc2VydmljZSkgPT4ge1xuICBjb25zb2xlLmxvZygnc2VsZWN0ZWRTZXJ2aWNlOiAnLCBzZWxlY3RlZFNlcnZpY2UudmFsdWUpXG4gIHNlbGVjdGVkU2VydmljZS52YWx1ZSA9IHsuLi5zZXJ2aWNlfVxuICBzaG93U2VydmljZURldGFpbHMudmFsdWUgPSB0cnVlXG59XG5cbmNvbnN0IGVkaXRDbGllbnQgPSBhc3luYyAoKSA9PiB7XG4gIHRyeSB7XG4gICAgY29uc29sZS5sb2coJ9C+0YLQv9GA0LDQstC70LXQvdC40LUg0LfQsNC/0YDQvtGB0LAg0L3QsCDRgNC10LTQsNC60YLQuNGA0L7QstCw0L3QuNC1JylcbiAgICBjb25zdCByZXNwb25zZSA9IGF3YWl0IGFwaS5wb3N0KGAvZWRpdF9jbGllbnRgLCB7XG4gICAgICBpZDogc2VsZWN0ZWRDbGllbnQudmFsdWUuaWQsXG4gICAgICBuYW1lOiBzZWxlY3RlZENsaWVudC52YWx1ZS5uYW1lLFxuICAgICAgcGhvbmU6IHNlbGVjdGVkQ2xpZW50LnZhbHVlLnBob25lXG4gICAgfSlcbiAgICBjb25zb2xlLmxvZygncmVzcG9uc2U6ICcsIHJlc3BvbnNlKVxuICAgIGF3YWl0IGdldENsaWVudHMoKVxuICAgIGVkaXRDbGllbnRNb2RlLnZhbHVlID0gZmFsc2VcbiAgICBzaG93Q2xpZW50c0RldGFpbHMudmFsdWUgPSBmYWxzZVxuICB9IGNhdGNoIChlcnIpe1xuICAgIGNvbnNvbGUuZXJyb3IoZXJyKVxuICB9XG59XG5cbmNvbnN0IGVkaXRTZXJ2aWNlID0gYXN5bmMgKCkgPT4ge1xuICB0cnkge1xuICAgIGNvbnNvbGUubG9nKCfQvtGC0L/RgNCw0LLQu9C10L3QuNC1INC30LDQv9GA0L7RgdCwINC90LAg0YDQtdC00LDQutGC0LjRgNC+0LLQsNC90LjQtScpXG4gICAgY29uc29sZS5sb2coJ3NlbGVjdGVkU2VydmljZScsc2VsZWN0ZWRTZXJ2aWNlLnZhbHVlKVxuICAgIGNvbnN0IHJlc3BvbnNlID0gYXdhaXQgYXBpLnBvc3QoYC9lZGl0X3NlcnZpY2VgLCB7XG4gICAgICBpZDogc2VsZWN0ZWRTZXJ2aWNlLnZhbHVlLmlkLFxuICAgICAgc2VydmljZTogc2VsZWN0ZWRTZXJ2aWNlLnZhbHVlLnNlcnZpY2UsXG4gICAgICBwcmljZTogc2VsZWN0ZWRTZXJ2aWNlLnZhbHVlLnByaWNlXG4gICAgfSlcbiAgICBjb25zb2xlLmxvZygncmVzcG9uc2U6ICcsIHJlc3BvbnNlKVxuICAgIGNvbnNvbGUubG9nKCdzZWxlY3RlZFNlcnZpY2VDYXRlZ29yeScsIHNlbGVjdGVkU2VydmljZUNhdGVnb3J5LnZhbHVlKVxuICAgIGF3YWl0IGdldFNlcnZpY2VzQnlDYXRlZ29yeShzZWxlY3RlZFNlcnZpY2VDYXRlZ29yeS52YWx1ZSlcbiAgICBlZGl0U2VydmljZU1vZGUudmFsdWUgPSBmYWxzZVxuICAgIHNob3dTZXJ2aWNlRGV0YWlscy52YWx1ZSA9IGZhbHNlXG4gIH0gY2F0Y2ggKGVycil7XG4gICAgY29uc29sZS5lcnJvcihlcnIpXG4gIH1cbn1cblxuY29uc3QgZGVsZXRlQ2xpZW50ID0gYXN5bmMgKCkgPT4ge1xuICB0cnkge1xuICAgIGNvbnN0IHJlc3BvbnNlID0gYXdhaXQgYXBpLnBvc3QoYC9kZWxldGVfY2xpZW50YCwge1xuICAgICAgY2xpZW50SWQ6IHNlbGVjdGVkQ2xpZW50LnZhbHVlLmlkXG4gICAgfSlcbiAgICBjb25zb2xlLmxvZygncmVzcG9uc2UnLCByZXNwb25zZSlcbiAgICBhd2FpdCBnZXRDbGllbnRzKClcbiAgICBlZGl0Q2xpZW50TW9kZS52YWx1ZSA9IGZhbHNlXG4gICAgc2hvd0NsaWVudHNEZXRhaWxzLnZhbHVlID0gZmFsc2VcbiAgfSBjYXRjaCAoZXJyKXtcbiAgICBjb25zb2xlLmVycm9yKGVycilcbiAgfVxufVxuXG5jb25zdCBkZWxldGVTZXJ2aWNlID0gYXN5bmMgKCkgPT4ge1xuICB0cnkge1xuICAgIGNvbnN0IHJlc3BvbnNlID0gYXdhaXQgYXBpLnBvc3QoYC9kZWxldGVfc2VydmljZWAsIHtcbiAgICAgIHNlcnZpY2VJZDogc2VsZWN0ZWRTZXJ2aWNlLnZhbHVlLmlkXG4gICAgfSlcbiAgICBjb25zb2xlLmxvZygncmVzcG9uc2UnLCByZXNwb25zZSlcbiAgICBhd2FpdCBnZXRTZXJ2aWNlc0J5Q2F0ZWdvcnkoKVxuICAgIGVkaXRTZXJ2aWNlTW9kZS52YWx1ZSA9IGZhbHNlXG4gICAgc2hvd1NlcnZpY2VEZXRhaWxzLnZhbHVlID0gZmFsc2VcbiAgfSBjYXRjaCAoZXJyKXtcbiAgICBjb25zb2xlLmVycm9yKGVycilcbiAgfVxufVxuXG5jb25zdCBvcGVuTmV3Q2xpZW50RGlhbG9nID0gKCkgPT4ge1xuICBuZXdDbGllbnREaWFsb2cudmFsdWUub3BlbigpXG59XG5cbmNvbnN0IG9wZW5OZXdTZXJ2aWNlRGlhbG9nID0gKCkgPT4ge1xuICBuZXdTZXJ2aWNlRGlhbG9nLnZhbHVlLm9wZW4oKVxufVxuXG5jb25zdCBvcGVuTmV3U2VydmljZUNhdGVnb3J5RGlhbG9nID0gKCkgPT4ge1xuICBuZXdTZXJ2aWNlQ2F0ZWdvcnlEaWFsb2cudmFsdWUub3BlbigpXG59XG5cbmNvbnN0IG9wZW5FZGl0U2VydmljZUNhdGVnb3J5RGlhbG9nID0gKCkgPT4ge1xuICBlZGl0U2VydmljZUNhdGVnb3J5RGlhbG9nLnZhbHVlLm9wZW4oKVxufVxuXG5jb25zdCBoYW5kbGVDbGllbnRBZGRlZCA9IChuZXdDbGllbnREYXRhKSA9PiB7XG4gIGNsaWVudHMudmFsdWUucHVzaChuZXdDbGllbnREYXRhLmNsaWVudClcbiAgY29uc29sZS5sb2coJ25ld0NsaWVudERhdGEnLCBuZXdDbGllbnREYXRhLmNsaWVudClcbiAgY29uc29sZS5sb2coJ2NsaWVudHMnLCBjbGllbnRzLnZhbHVlKVxufVxuXG5jb25zdCBoYW5kbGVTZXJ2aWNlQWRkZWQgPSAoKSA9PiB7XG4gIGdldFNlcnZpY2VzQnlDYXRlZ29yeSgpXG4gIGNvbnNvbGUubG9nKCdzZXJ2aWNlczogJywgc2VydmljZXMpXG59XG5cbmNvbnN0IGhhbmRsZVNlcnZpY2VDYXRlZ29yeUFkZGVkID0gKCkgPT4ge1xuICBnZXRTZXJ2aWNlQ2F0ZWdvcmllcygpXG59XG5cbmNvbnN0IGhhbmRsZVNlcnZpY2VDYXRlZ29yeUVkaXRlZCA9ICgpID0+IHtcbiAgc2VydmljZUNhdGVnb3JpZXMudmFsdWUgPSBudWxsXG4gIHNlbGVjdGVkU2VydmljZUNhdGVnb3J5LnZhbHVlID0gbnVsbFxuICBnZXRTZXJ2aWNlQ2F0ZWdvcmllcygpXG59XG5cbjwvc2NyaXB0PlxuXG48dGVtcGxhdGU+XG4gIDxxLXBhZ2UgY2xhc3M9XCJxLXBhLW5vbmVcIj5cbiAgICA8cS1jYXJkPlxuICAgICAgPHEtdGFic1xuICAgICAgICB2LW1vZGVsPVwidGFiXCJcbiAgICAgICAgZGVuc2VcbiAgICAgICAgY2xhc3M9XCJ0ZXh0LWdyZXkgc3RpY2t5LXRhYnNcIlxuICAgICAgICBhY3RpdmUtY29sb3I9XCJ5ZWxsb3dcIlxuICAgICAgICBpbmRpY2F0b3ItY29sb3I9XCJ5ZWxsb3dcIlxuICAgICAgICBhbGlnbj1cImp1c3RpZnlcIlxuICAgICAgICBuYXJyb3ctaW5kaWNhdG9yXG4gICAgICA+XG4gICAgICAgIDxxLXRhYiBuYW1lPVwic2VydmljZXNcIiBsYWJlbD1cItGA0LDQsdC+0YLRi1wiIC8+XG4gICAgICAgIDxxLXRhYiBuYW1lPVwiY2xpZW50c1wiIGxhYmVsPVwi0LrQu9C40LXQvdGC0YtcIiAvPlxuICAgICAgPC9xLXRhYnM+XG5cbiAgICAgIDxxLXNlcGFyYXRvci8+XG5cbiAgICAgIDxxLXRhYi1wYW5lbHMgdi1tb2RlbD1cInRhYlwiIGFuaW1hdGVkPlxuXG4gICAgICAgIDxxLXRhYi1wYW5lbCBuYW1lPVwic2VydmljZXNcIiBzdHlsZT1cInBhZGRpbmc6IDBcIj5cblxuICAgICAgICAgIDxkaXYgY2xhc3M9XCJyb3cgaXRlbXMtY2VudGVyXCIgPlxuXG4gICAgICAgICAgICA8cS1zZWxlY3Qgdi1tb2RlbD1cInNlbGVjdGVkU2VydmljZUNhdGVnb3J5XCJcbiAgICAgICAgICAgICAgICAgICAgICA6b3B0aW9ucz1cInNlcnZpY2VDYXRlZ29yaWVzXCJcbiAgICAgICAgICAgICAgICAgICAgICBvcHRpb24tbGFiZWw9XCJjYXRlZ29yeV9uYW1lXCJcbiAgICAgICAgICAgICAgICAgICAgICBlbWl0LXZhbHVlXG4gICAgICAgICAgICAgICAgICAgICAgbWFwLW9wdGlvbnNcbiAgICAgICAgICAgICAgICAgICAgICBsYWJlbD1cItC60LDRgtC10LPQvtGA0LjQuCDRgNCw0LHQvtGCXCJcbiAgICAgICAgICAgICAgICAgICAgICBkZW5zZVxuICAgICAgICAgICAgICAgICAgICAgIHBsYWNlaG9sZGVyPVwi0L3QtdGCINC60LDRgtC10LPQvtGA0LjQuVwiXG4gICAgICAgICAgICAgICAgICAgICAgbGFiZWwtY29sb3I9XCJncmV5XCJcbiAgICAgICAgICAgICAgICAgICAgICBjb2xvcj1cInllbGxvd1wiXG4gICAgICAgICAgICAgICAgICAgICAgY2xhc3M9XCJjb2wtOVwiXG4gICAgICAgICAgICAgICAgICAgICAgQHVwZGF0ZTptb2RlbC12YWx1ZT1cImdldFNlcnZpY2VzQnlDYXRlZ29yeVwiXG4gICAgICAgICAgICAgIC8+XG5cbiAgICAgICAgICAgICAgPGRpdiBjbGFzcz1cImNvbC1hdXRvIHNlbGYtZW5kXCI+XG4gICAgICAgICAgICAgICAgPHEtYnRuIGNsYXNzPVwiY29sLTEgdGV4dC15ZWxsb3dcIiBAY2xpY2s9XCJvcGVuTmV3U2VydmljZUNhdGVnb3J5RGlhbG9nXCI+KzwvcS1idG4+XG4gICAgICAgICAgICAgIDwvZGl2PlxuICAgICAgICAgICAgICA8ZGl2IGNsYXNzPVwiY29sLWF1dG8gc2VsZi1lbmRcIj5cbiAgICAgICAgICAgICAgICA8cS1idG4gY2xhc3M9XCJjb2wtMSB0ZXh0LXllbGxvd1wiIGljb249XCJlZGl0XCIgQGNsaWNrPVwib3BlbkVkaXRTZXJ2aWNlQ2F0ZWdvcnlEaWFsb2dcIiAvPlxuICAgICAgICAgICAgICA8L2Rpdj5cblxuICAgICAgICAgIDwvZGl2PlxuXG4gICAgICAgICAgICA8cS1saXN0IGJvcmRlcmVkIHNlcGFyYXRvciA+XG4gICAgICAgICAgICAgIDxxLWl0ZW0tbGFiZWwgdi1pZj1cIiFzZXJ2aWNlc1wiPtCd0LXRgiDRgdC10YDQstC40YHQvtCyPC9xLWl0ZW0tbGFiZWw+XG4gICAgICAgICAgICAgIDxxLWl0ZW0gdi1mb3I9XCJzZXJ2aWNlIGluIHNlcnZpY2VzXCJcbiAgICAgICAgICAgICAgICAgICAgICA6a2V5PVwic2VydmljZVwiXG4gICAgICAgICAgICAgICAgICAgICAgY2xhc3M9XCJ3LTEwMCBqdXN0aWZ5LWJldHdlZW4gc2VsZWN0U2VydmljZVwiXG4gICAgICAgICAgICAgICAgICAgICAgc3R5bGU9XCJ3aWR0aDogMTAwJVwiXG4gICAgICAgICAgICAgICAgICAgICAgY2xpY2thYmxlXG4gICAgICAgICAgICAgICAgICAgICAgdi1yaXBwbGVcbiAgICAgICAgICAgICAgICAgICAgICBAY2xpY2s9XCJvcGVuU2VydmljZURpYWxvZyhzZXJ2aWNlKVwiXG4gICAgICAgICAgICAgICAgICAgICAgOnEtaXRlbVxuICAgICAgICAgICAgICA+XG5cbiAgICAgICAgICAgICAgICA8cS1pdGVtLXNlY3Rpb24gPlxuICAgICAgICAgICAgICAgICAgPHEtaXRlbS1sYWJlbCBjbGFzcz1cInRleHQtbGVmdFwiPlxuICAgICAgICAgICAgICAgICAgICB7eyBzZXJ2aWNlLnNlcnZpY2UgfX1cbiAgICAgICAgICAgICAgICAgIDwvcS1pdGVtLWxhYmVsPlxuICAgICAgICAgICAgICAgIDwvcS1pdGVtLXNlY3Rpb24+XG5cbiAgICAgICAgICAgICAgICA8cS1pdGVtLXNlY3Rpb24gPlxuICAgICAgICAgICAgICAgICAgPHEtaXRlbS1sYWJlbCBjbGFzcz1cInRleHQtcmlnaHRcIj5cbiAgICAgICAgICAgICAgICAgICAge3sgc2VydmljZS5wcmljZSB9fVxuICAgICAgICAgICAgICAgICAgPC9xLWl0ZW0tbGFiZWw+XG4gICAgICAgICAgICAgICAgPC9xLWl0ZW0tc2VjdGlvbj5cblxuICAgICAgICAgICAgICAgIDxxLWJ0blxuICAgICAgICAgICAgICAgICAgaWNvbj1cImFkZFwiXG4gICAgICAgICAgICAgICAgICByb3VuZFxuICAgICAgICAgICAgICAgICAgY2xhc3M9XCJmYWIgYmcteWVsbG93IHRleHQtYmxhY2tcIlxuICAgICAgICAgICAgICAgICAgQGNsaWNrPVwiY29uc29sZS5sb2coJ9C90LUg0YDQtdCw0LvQuNC30L7QstCw0L3QvicpXCJcbiAgICAgICAgICAgICAgICAgIHNpemU9XCIyMHB4XCJcbiAgICAgICAgICAgICAgICAvPlxuXG4gICAgICAgICAgICAgIDwvcS1pdGVtPlxuICAgICAgICAgICAgPC9xLWxpc3Q+XG5cbiAgICAgICAgICA8IS0tINCf0LvQsNCy0LDRjtGJ0LDRjyDQutC90L7Qv9C60LAg0LTQvtCx0LDQstC70LXQvdC40Y8g0L3QvtCy0L7Qs9C+INGB0LXRgNCy0LjRgdCwIC0tPlxuICAgICAgICAgIDxxLWJ0blxuICAgICAgICAgICAgaWNvbj1cImFkZFwiXG4gICAgICAgICAgICByb3VuZFxuICAgICAgICAgICAgY2xhc3M9XCJmYWIgYmcteWVsbG93IHRleHQtYmxhY2tcIlxuICAgICAgICAgICAgQGNsaWNrPVwib3Blbk5ld1NlcnZpY2VEaWFsb2dcIlxuICAgICAgICAgICAgc2l6ZT1cIjIwcHhcIlxuICAgICAgICAgIC8+XG4gICAgICAgIDwvcS10YWItcGFuZWw+XG5cbiAgICAgICAgPHEtdGFiLXBhbmVsIG5hbWU9XCJjbGllbnRzXCIgc3R5bGU9XCJwYWRkaW5nOiAwXCI+XG5cbiAgICAgICAgICA8IS0tINC+0YLQvtCx0YDQsNC20LXQvdC40LUg0YHQv9C40YHQutCwINC60LvQuNC10L3RgtC+0LIgLS0+XG4gICAgICAgICAgPHEtbGlzdCBib3JkZXJlZCBzZXBhcmF0b3IgPlxuXG4gICAgICAgICAgICA8cS1pdGVtLWxhYmVsIHYtaWY9XCIhY2xpZW50c1wiPtCd0LXRgiDQvNCw0YLQtdGA0LjQsNC70L7QsjwvcS1pdGVtLWxhYmVsPlxuICAgICAgICAgICAgPHEtaXRlbSB2LWZvcj1cImNsaWVudCBpbiBjbGllbnRzXCJcbiAgICAgICAgICAgICAgICAgICAgOmtleT1cImNsaWVudC5pZFwiXG4gICAgICAgICAgICAgICAgICAgIGNsYXNzPVwidy0xMDAganVzdGlmeS1iZXR3ZWVuIHJvd1wiXG4gICAgICAgICAgICAgICAgICAgIHN0eWxlPVwid2lkdGg6IDEwMCVcIlxuICAgICAgICAgICAgICAgICAgICBjbGlja2FibGVcbiAgICAgICAgICAgICAgICAgICAgQGNsaWNrPVwib3BlbkNsaWVudERpYWxvZyhjbGllbnQpXCJcbiAgICAgICAgICAgID5cblxuICAgICAgICAgICAgICA8cS1pdGVtLXNlY3Rpb24gY2xhc3M9XCJjb2wtYXV0b1wiPlxuICAgICAgICAgICAgICAgIDxxLWl0ZW0tbGFiZWwgY2xhc3M9XCJ0ZXh0LWxlZnRcIj5cbiAgICAgICAgICAgICAgICAgIHt7IGNsaWVudC5uYW1lIH19XG4gICAgICAgICAgICAgICAgPC9xLWl0ZW0tbGFiZWw+XG4gICAgICAgICAgICAgIDwvcS1pdGVtLXNlY3Rpb24+XG5cblxuICAgICAgICAgICAgICA8cS1pdGVtLXNlY3Rpb24gY2xhc3M9XCJjb2wtYXV0b1wiPlxuICAgICAgICAgICAgICAgIDxxLWl0ZW0tbGFiZWwgY2xhc3M9XCJ0ZXh0LXJpZ2h0XCI+XG4gICAgICAgICAgICAgICAgICB7eyBjbGllbnQucGhvbmUgfX1cbiAgICAgICAgICAgICAgICA8L3EtaXRlbS1sYWJlbD5cbiAgICAgICAgICAgICAgPC9xLWl0ZW0tc2VjdGlvbj5cblxuICAgICAgICAgICAgPC9xLWl0ZW0+XG5cbiAgICAgICAgICA8L3EtbGlzdD5cblxuICAgICAgICAgIDwhLS0g0J/Qu9Cw0LLQsNGO0YnQsNGPINC60L3QvtC/0LrQsCDQtNC+0LHQsNCy0LvQtdC90LjRjyDQvdC+0LLQvtCz0L4g0LrQu9C40LXQvdGC0LAgLS0+XG4gICAgICAgICAgPHEtYnRuXG4gICAgICAgICAgICBpY29uPVwiYWRkXCJcbiAgICAgICAgICAgIHJvdW5kXG4gICAgICAgICAgICBjbGFzcz1cImZhYiBiZy15ZWxsb3cgdGV4dC1ibGFja1wiXG4gICAgICAgICAgICBAY2xpY2s9XCJvcGVuTmV3Q2xpZW50RGlhbG9nXCJcbiAgICAgICAgICAgIHNpemU9XCIyMHB4XCJcbiAgICAgICAgICAvPlxuXG4gICAgICAgIDwvcS10YWItcGFuZWw+XG5cbiAgICAgIDwvcS10YWItcGFuZWxzPlxuXG4gICAgPC9xLWNhcmQ+XG5cbiAgPC9xLXBhZ2U+XG5cbiAgPGRpdj5cbiAgICA8cS1kaWFsb2cgdi1tb2RlbD1cInNob3dDbGllbnRzRGV0YWlsc1wiIHBlcnNpc3RlbnQ+XG4gICAgICA8cS1jYXJkPlxuICAgICAgICA8cS1jYXJkLXNlY3Rpb24+XG4gICAgICAgICAgPGRpdiBjbGFzcz1cInRleHQtaDZcIj7QutC70LjQtdC90YI8L2Rpdj5cbiAgICAgICAgICA8cS1pbnB1dCA6ZGlzYWJsZT1cIiFlZGl0Q2xpZW50TW9kZVwiXG4gICAgICAgICAgICAgICAgICAgdi1tb2RlbD1cInNlbGVjdGVkQ2xpZW50Lm5hbWVcIlxuICAgICAgICAgICAgICAgICAgIGxhYmVsLWNvbG9yPVwieWVsbG93XCJcbiAgICAgICAgICAgICAgICAgICBjb2xvcj1cInllbGxvd1wiXG4gICAgICAgICAgICAgICAgICAgbGFiZWw9XCLQmNC80Y8g0LrQu9C40LXQvdGC0LBcIlxuICAgICAgICAgICAgICAgICAgIG91dGxpbmVkXG4gICAgICAgICAgICAgICAgICAgY2xhc3M9XCJxLW1iLW1kXCJcbiAgICAgICAgICAvPlxuICAgICAgICAgIDxxLWlucHV0IDpkaXNhYmxlPVwiIWVkaXRDbGllbnRNb2RlXCJcbiAgICAgICAgICAgICAgICAgICB2LW1vZGVsPVwic2VsZWN0ZWRDbGllbnQucGhvbmVcIlxuICAgICAgICAgICAgICAgICAgIGxhYmVsLWNvbG9yPVwieWVsbG93XCJcbiAgICAgICAgICAgICAgICAgICBjb2xvcj1cInllbGxvd1wiXG4gICAgICAgICAgICAgICAgICAgbGFiZWw9XCLRgtC10LvQtdGE0L7QvVwiXG4gICAgICAgICAgICAgICAgICAgb3V0bGluZWQgY2xhc3M9XCJxLW1iLW1kXCJcbiAgICAgICAgICAvPlxuXG4gICAgICAgIDwvcS1jYXJkLXNlY3Rpb24+XG4gICAgICAgIDxxLWNhcmQtYWN0aW9ucyBhbGlnbj1cInJpZ2h0XCI+XG4gICAgICAgICAgPHEtYnRuIHYtaWY9XCJlZGl0Q2xpZW50TW9kZVwiIGZsYXQgbGFiZWw9XCLQvtGC0LzQtdC90LBcIiBjb2xvcj1cInllbGxvd1wiIEBjbGljaz1cImVkaXRDbGllbnRNb2RlID0gZmFsc2VcIiAvPlxuICAgICAgICAgIDxxLWJ0biB2LWlmPVwiIWVkaXRDbGllbnRNb2RlXCIgZmxhdCBsYWJlbD1cItC30LDQutGA0YvRgtGMXCIgY29sb3I9XCJ5ZWxsb3dcIiBAY2xpY2s9XCJzaG93Q2xpZW50c0RldGFpbHMgPSBmYWxzZVwiIC8+XG4gICAgICAgICAgPHEtYnRuIHYtaWY9XCIhZWRpdENsaWVudE1vZGVcIiBmbGF0IGxhYmVsPVwi0YDQtdC00LDQutGC0LjRgNC+0LLQsNGC0YxcIiBjb2xvcj1cInllbGxvd1wiIEBjbGljaz1cImVkaXRDbGllbnRNb2RlID0gdHJ1ZVwiIC8+XG4gICAgICAgICAgPHEtYnRuIHYtaWY9XCJlZGl0Q2xpZW50TW9kZVwiIGZsYXQgbGFiZWw9XCLRgdC+0YXRgNCw0L3QuNGC0YxcIiBjb2xvcj1cInllbGxvd1wiIEBjbGljaz1cImVkaXRDbGllbnRcIiAvPlxuICAgICAgICAgIDxxLWJ0biB2LWlmPVwiIWVkaXRDbGllbnRNb2RlXCIgZmxhdCBsYWJlbD1cItGD0LTQsNC70LjRgtGMXCIgY29sb3I9XCJ5ZWxsb3dcIiBAY2xpY2s9XCJoYW5kbGVEZWxldGVcIiAvPlxuICAgICAgICA8L3EtY2FyZC1hY3Rpb25zPlxuICAgICAgPC9xLWNhcmQ+XG5cbiAgICA8L3EtZGlhbG9nPlxuXG4gICAgPHEtZGlhbG9nIHYtbW9kZWw9XCJzaG93U2VydmljZURldGFpbHNcIiBwZXJzaXN0ZW50PlxuICAgICAgPHEtY2FyZD5cbiAgICAgICAgPHEtY2FyZC1zZWN0aW9uPlxuICAgICAgICAgIDxkaXYgY2xhc3M9XCJ0ZXh0LWg2XCI+0YHQtdGA0LLQuNGBPC9kaXY+XG4gICAgICAgICAgPHEtaW5wdXQgOmRpc2FibGU9XCIhZWRpdFNlcnZpY2VNb2RlXCJcbiAgICAgICAgICAgICAgICAgICB2LW1vZGVsPVwic2VsZWN0ZWRTZXJ2aWNlLnNlcnZpY2VcIlxuICAgICAgICAgICAgICAgICAgIGxhYmVsLWNvbG9yPVwieWVsbG93XCJcbiAgICAgICAgICAgICAgICAgICBjb2xvcj1cInllbGxvd1wiXG4gICAgICAgICAgICAgICAgICAgbGFiZWw9XCLQvdCw0LfQstCw0L3QuNC1INGB0LXRgNCy0LjRgdCwXCJcbiAgICAgICAgICAgICAgICAgICBvdXRsaW5lZFxuICAgICAgICAgICAgICAgICAgIGNsYXNzPVwicS1tYi1tZFwiXG4gICAgICAgICAgLz5cbiAgICAgICAgICA8cS1pbnB1dCA6ZGlzYWJsZT1cIiFlZGl0U2VydmljZU1vZGVcIlxuICAgICAgICAgICAgICAgICAgIHYtbW9kZWw9XCJzZWxlY3RlZFNlcnZpY2UucHJpY2VcIlxuICAgICAgICAgICAgICAgICAgIGxhYmVsLWNvbG9yPVwieWVsbG93XCJcbiAgICAgICAgICAgICAgICAgICBjb2xvcj1cInllbGxvd1wiXG4gICAgICAgICAgICAgICAgICAgbGFiZWw9XCLRhtC10L3QsFwiXG4gICAgICAgICAgICAgICAgICAgb3V0bGluZWQgY2xhc3M9XCJxLW1iLW1kXCJcbiAgICAgICAgICAvPlxuXG4gICAgICAgIDwvcS1jYXJkLXNlY3Rpb24+XG4gICAgICAgIDxxLWNhcmQtYWN0aW9ucyBhbGlnbj1cInJpZ2h0XCI+XG4gICAgICAgICAgPHEtYnRuIHYtaWY9XCJlZGl0U2VydmljZU1vZGVcIiBmbGF0IGxhYmVsPVwi0L7RgtC80LXQvdCwXCIgY29sb3I9XCJ5ZWxsb3dcIiBAY2xpY2s9XCJlZGl0U2VydmljZU1vZGUgPSBmYWxzZVwiIC8+XG4gICAgICAgICAgPHEtYnRuIHYtaWY9XCIhZWRpdFNlcnZpY2VNb2RlXCIgZmxhdCBsYWJlbD1cItC30LDQutGA0YvRgtGMXCIgY29sb3I9XCJ5ZWxsb3dcIiBAY2xpY2s9XCJzaG93U2VydmljZURldGFpbHMgPSBmYWxzZVwiIC8+XG4gICAgICAgICAgPHEtYnRuIHYtaWY9XCIhZWRpdFNlcnZpY2VNb2RlXCIgZmxhdCBsYWJlbD1cItGA0LXQtNCw0LrRgtC40YDQvtCy0LDRgtGMXCIgY29sb3I9XCJ5ZWxsb3dcIiBAY2xpY2s9XCJlZGl0U2VydmljZU1vZGUgPSB0cnVlXCIgLz5cbiAgICAgICAgICA8cS1idG4gdi1pZj1cImVkaXRTZXJ2aWNlTW9kZVwiIGZsYXQgbGFiZWw9XCLRgdC+0YXRgNCw0L3QuNGC0YxcIiBjb2xvcj1cInllbGxvd1wiIEBjbGljaz1cImVkaXRTZXJ2aWNlXCIgLz5cbiAgICAgICAgICA8cS1idG4gdi1pZj1cIiFlZGl0U2VydmljZU1vZGVcIiBmbGF0IGxhYmVsPVwi0YPQtNCw0LvQuNGC0YxcIiBjb2xvcj1cInllbGxvd1wiIEBjbGljaz1cImhhbmRsZURlbGV0ZVwiIC8+XG4gICAgICAgIDwvcS1jYXJkLWFjdGlvbnM+XG4gICAgICA8L3EtY2FyZD5cblxuICAgIDwvcS1kaWFsb2c+XG5cbiAgICA8RGVsZXRlQ29uZmlybVBhZ2UgcmVmPVwiY29uZmlybURpYWxvZ1wiLz5cblxuICAgIDxOZXdDbGllbnREaWFsb2dQYWdlIHJlZj1cIm5ld0NsaWVudERpYWxvZ1wiIEBjbGllbnQtYWRkZWQ9XCJoYW5kbGVDbGllbnRBZGRlZFwiIC8+XG4gICAgPE5ld1NlcnZpY2VEaWFsb2dQYWdlIHJlZj1cIm5ld1NlcnZpY2VEaWFsb2dcIiBAc2VydmljZS1hZGRlZD1cImhhbmRsZVNlcnZpY2VBZGRlZFwiIDpkYXRhPVwic2VsZWN0ZWRTZXJ2aWNlQ2F0ZWdvcnlcIiAvPlxuICAgIDxOZXdTZXJ2aWNlQ2F0ZWdvcnlEaWFsb2dQYWdlIHJlZj1cIm5ld1NlcnZpY2VDYXRlZ29yeURpYWxvZ1wiIEBzZXJ2aWNlX2NhdGVnb3J5LWFkZGVkPVwiaGFuZGxlU2VydmljZUNhdGVnb3J5QWRkZWRcIiAvPlxuICAgIDxFZGl0U2VydmljZUNhdGVnb3J5RGlhbG9nUGFnZSByZWY9XCJlZGl0U2VydmljZUNhdGVnb3J5RGlhbG9nXCJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgQHNlcnZpY2VfY2F0ZWdvcnktZWRpdGVkPVwiaGFuZGxlU2VydmljZUNhdGVnb3J5RWRpdGVkXCJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgOmRhdGE9XCJzZWxlY3RlZFNlcnZpY2VDYXRlZ29yeVwiXG4gICAgLz5cbiAgPC9kaXY+XG5cbjwvdGVtcGxhdGU+XG5cbjxzdHlsZSBzY29wZWQ+XG5cbi5zdGlja3ktdGFicyB7XG4gIHBvc2l0aW9uOiBzdGlja3k7XG4gIHRvcDogMDtcbiAgei1pbmRleDogMTtcbiAgYmFja2dyb3VuZC1jb2xvcjogYmxhY2s7XG59XG5cbi5mYWIge1xuICBwb3NpdGlvbjogZml4ZWQ7XG4gIGJvdHRvbTogNzBweDtcbiAgcmlnaHQ6IDE2cHg7XG4gIHotaW5kZXg6IDEwMDA7IC8qINGH0YLQvtCx0Ysg0LrQvdC+0L/QutCwINCx0YvQu9CwINC/0L7QstC10YDRhSDQvtGB0YLQsNC70YzQvdGL0YUg0Y3Qu9C10LzQtdC90YLQvtCyICovXG59XG5cbjwvc3R5bGU+XG4iXSwibmFtZXMiOlsiX2NyZWF0ZUJsb2NrIiwiX3dpdGhDdHgiLCJfY3JlYXRlVk5vZGUiLCJfY3JlYXRlRWxlbWVudFZOb2RlIiwic3BlY2lhbGl6YXRpb25TdG9yZSIsIl9vcGVuQmxvY2siLCJfY3JlYXRlRWxlbWVudEJsb2NrIiwiX0ZyYWdtZW50IiwiX2NyZWF0ZVRleHRWTm9kZSIsIl9jcmVhdGVDb21tZW50Vk5vZGUiLCJfcmVuZGVyTGlzdCIsIl90b0Rpc3BsYXlTdHJpbmciXSwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7Ozs7O0FBS0EsVUFBTSxzQkFBc0Isd0JBQXVCO0FBRW5ELFVBQU0sMkJBQTBCLG9CQUFvQiwwQkFBMEI7QUFDOUUsVUFBTSxTQUFTLElBQUksS0FBSztBQUN4QixVQUFNLE9BQU8sSUFBSSxFQUFFO0FBQ25CLFVBQU0sUUFBUSxJQUFJLEVBQUU7QUFFcEIsVUFBTSxPQUFPO0FBRWIsYUFBUyxPQUFNO0FBQ2IsYUFBTyxRQUFRO0FBQUEsSUFDakI7QUFFQSxhQUFTLFFBQU87QUFDZCxhQUFPLFFBQVE7QUFBQSxJQUNqQjtBQUVBLGFBQWEsRUFBQyxLQUFJLENBQUM7QUFFbkIsVUFBTSxTQUFTLFlBQVk7QUFDekIsVUFBSTtBQUNGLGNBQU0sV0FBVyxNQUFNLElBQUksS0FBSyxlQUFlO0FBQUEsVUFDN0MsTUFBTSxLQUFLO0FBQUEsVUFDWCxPQUFPLE1BQU07QUFBQSxVQUNiLG1CQUFtQjtBQUFBLFFBQ3BCLENBQUE7QUFDRCxhQUFLLFFBQVE7QUFDYixjQUFNLFFBQVE7QUFDZCxjQUFLO0FBQ0wsYUFBSyxn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
