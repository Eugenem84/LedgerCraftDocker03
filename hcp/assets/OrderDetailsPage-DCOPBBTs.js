import { a as createComponent, b as computed, h, d as hSlot, a4 as useFormProps, a5 as getBtnDesignAttr, O as QBtn, m as hMergeSlot, a6 as useFormInject, _ as _export_sfc, r as ref, J as useRouter, n as onMounted, D as openBlock, K as createElementBlock, R as createBaseVNode, E as createBlock, I as createCommentVNode, G as createVNode, F as withCtx, Z as QCard, a2 as QDialog, L as Fragment, M as createTextVNode, P as withDirectives, a7 as vShow, Q as QSeparator, N as renderList, S as toDisplayString, a0 as QInput, $ as QCardSection, a1 as QCardActions, H as normalizeClass, U as Ripple } from "./index-74sOg8Nl.js";
import { Q as QSelect } from "./QSelect-DGItPn-E.js";
import { Q as QTab, a as QTabPanels, b as QTabPanel } from "./QTabPanels-Bl_92niL.js";
import { c as QTabs } from "./QTabs-DnB1hw4w.js";
import { Q as QItemLabel, a as QItem, b as QItemSection } from "./QItem-DuqkKkh7.js";
import { D as DeleteConfirmPage, Q as QList } from "./DeleteConfirmPage-DjUGgkyk.js";
import { u as useOrderStore } from "./order-CgBhk1_9.js";
import { api } from "./axios-D58jYJIV.js";
import { u as useSpecializationsStore } from "./specializations-B0lcZ67D.js";
import { u as useQuasar } from "./use-quasar-DEOs6wIv.js";
import "./rtl-DDpZOXNn.js";
const QBtnGroup = createComponent({
  name: "QBtnGroup",
  props: {
    unelevated: Boolean,
    outline: Boolean,
    flat: Boolean,
    rounded: Boolean,
    square: Boolean,
    push: Boolean,
    stretch: Boolean,
    glossy: Boolean,
    spread: Boolean
  },
  setup(props, { slots }) {
    const classes = computed(() => {
      const cls = ["unelevated", "outline", "flat", "rounded", "square", "push", "stretch", "glossy"].filter((t) => props[t] === true).map((t) => `q-btn-group--${t}`).join(" ");
      return `q-btn-group row no-wrap${cls.length !== 0 ? " " + cls : ""}` + (props.spread === true ? " q-btn-group--spread" : " inline");
    });
    return () => h("div", { class: classes.value }, hSlot(slots.default));
  }
});
const QBtnToggle = createComponent({
  name: "QBtnToggle",
  props: {
    ...useFormProps,
    modelValue: {
      required: true
    },
    options: {
      type: Array,
      required: true,
      validator: (v) => v.every(
        (opt) => ("label" in opt || "icon" in opt || "slot" in opt) && "value" in opt
      )
    },
    // To avoid seeing the active raise shadow through
    // the transparent button, give it a color (even white)
    color: String,
    textColor: String,
    toggleColor: {
      type: String,
      default: "primary"
    },
    toggleTextColor: String,
    outline: Boolean,
    flat: Boolean,
    unelevated: Boolean,
    rounded: Boolean,
    push: Boolean,
    glossy: Boolean,
    size: String,
    padding: String,
    noCaps: Boolean,
    noWrap: Boolean,
    dense: Boolean,
    readonly: Boolean,
    disable: Boolean,
    stack: Boolean,
    stretch: Boolean,
    spread: Boolean,
    clearable: Boolean,
    ripple: {
      type: [Boolean, Object],
      default: true
    }
  },
  emits: ["update:modelValue", "clear", "click"],
  setup(props, { slots, emit }) {
    const hasActiveValue = computed(
      () => props.options.find((opt) => opt.value === props.modelValue) !== void 0
    );
    const formAttrs = computed(() => ({
      type: "hidden",
      name: props.name,
      value: props.modelValue
    }));
    const injectFormInput = useFormInject(formAttrs);
    const btnDesignAttr = computed(() => getBtnDesignAttr(props));
    const btnOptionDesign = computed(() => ({
      rounded: props.rounded,
      dense: props.dense,
      ...btnDesignAttr.value
    }));
    const btnOptions = computed(() => props.options.map((item, i) => {
      const { attrs, value, slot, ...opt } = item;
      return {
        slot,
        props: {
          key: i,
          "aria-pressed": value === props.modelValue ? "true" : "false",
          ...attrs,
          ...opt,
          ...btnOptionDesign.value,
          disable: props.disable === true || opt.disable === true,
          // Options that come from the button specific options first, then from general props
          color: value === props.modelValue ? mergeOpt(opt, "toggleColor") : mergeOpt(opt, "color"),
          textColor: value === props.modelValue ? mergeOpt(opt, "toggleTextColor") : mergeOpt(opt, "textColor"),
          noCaps: mergeOpt(opt, "noCaps") === true,
          noWrap: mergeOpt(opt, "noWrap") === true,
          size: mergeOpt(opt, "size"),
          padding: mergeOpt(opt, "padding"),
          ripple: mergeOpt(opt, "ripple"),
          stack: mergeOpt(opt, "stack") === true,
          stretch: mergeOpt(opt, "stretch") === true,
          onClick(e) {
            set(value, item, e);
          }
        }
      };
    }));
    function set(value, opt, e) {
      if (props.readonly !== true) {
        if (props.modelValue === value) {
          if (props.clearable === true) {
            emit("update:modelValue", null, null);
            emit("clear");
          }
        } else {
          emit("update:modelValue", value, opt);
        }
        emit("click", e);
      }
    }
    function mergeOpt(opt, key) {
      return opt[key] === void 0 ? props[key] : opt[key];
    }
    function getContent() {
      const child = btnOptions.value.map((opt) => {
        return h(QBtn, opt.props, opt.slot !== void 0 ? slots[opt.slot] : void 0);
      });
      if (props.name !== void 0 && props.disable !== true && hasActiveValue.value === true) {
        injectFormInput(child, "push");
      }
      return hMergeSlot(slots.default, child);
    }
    return () => h(QBtnGroup, {
      class: "q-btn-toggle",
      ...btnDesignAttr.value,
      rounded: props.rounded,
      stretch: props.stretch,
      glossy: props.glossy,
      spread: props.spread
    }, getContent);
  }
});
const _sfc_main = {
  __name: "OrderDetailsPage",
  setup(__props, { expose: __expose }) {
    __expose();
    const deleteConfirmPage = ref(null);
    const $q = useQuasar();
    const router = useRouter();
    const specializationsStore = useSpecializationsStore();
    const selectedSpecializationId = specializationsStore.getSelectedSpecialization.id;
    const orderStore = useOrderStore();
    const orderStatus = ref("waiting");
    const paid = ref(false);
    const order = ref(null);
    const services = ref([]);
    const materials = ref([]);
    const storeProducts = ref([]);
    const products = ref([]);
    const productCategories = ref([]);
    const selectedStoreProduct = ref(null);
    const selectedProductCategory = ref(null);
    const client = ref({
      id: null,
      name: null,
      phone: null
    });
    const clientId = ref(null);
    const modelId = ref(null);
    const comments = ref(null);
    const clients = ref(null);
    const models = ref(null);
    const model = ref({ id: null, name: null });
    const selectedServiceCategory = ref(null);
    const serviceCategories = ref(null);
    const servicesByCategory = ref(null);
    const tab = ref("all");
    const editMode = ref(false);
    const isNewOrder = computed(() => !order.value?.id);
    const showAddNewMaterialDialog = ref(false);
    const showAddNewServiceDialog = ref(false);
    const showAddNewClientDialog = ref(false);
    const showAddNewModelDialog = ref(false);
    const showAddProductFromStoreDialog = ref(false);
    const newMaterial = ref({
      name: "",
      price: 0,
      amount: 0
    });
    const newService = ref({
      name: "",
      price: 0
    });
    const newClient = ref({
      name: "",
      phone: ""
    });
    const newModel = ref({
      name: ""
    });
    const getServices = async () => {
      try {
        const orderId = order.value.id;
        const response = await api.get(`/get_services/${orderId}`);
        services.value = response.data;
        console.log("services: ", services.value);
      } catch (err) {
        console.error("ошибка загрузки сервисов", err);
      }
    };
    const switсhPaidStatus = async () => {
      const id = order.value.id;
      try {
        const response = await api.put(`switch_paid_status/${id}`);
        if (response.status === 200) {
          paid.value = !paid.value;
          console.log("статус оплаты изменен");
        }
      } catch (err) {
        console.log("ошибка смены статуса: ", err);
      }
    };
    const updateOrderStatus = async () => {
      try {
        const id = order.value.id;
        const response = await api.put(`/update_order_status/${id}`, {
          status: orderStatus.value
        });
        if (response.status === 200) {
          console.log("статус ордера изменен на: ", orderStatus.value);
        } else {
          console.error("ошибка обновления статуса", response);
        }
      } catch (err) {
        console.error("ошибка запроса: ", err);
      }
    };
    const getMaterialsByOrder = async () => {
      try {
        const orderId = order.value.id;
        const response = await api.get(`/get_materials_by_order/${orderId}`);
        materials.value = [];
        products.value = [];
        response.data.forEach((item) => {
          if (item.product_id) {
            products.value.push({ ...item });
          } else {
            materials.value.push({ ...item });
          }
        });
        console.log("materials: ", materials.value);
        console.log("products: ", products.value);
      } catch (err) {
        console.error("ошибка получения материалов: ", err);
      }
    };
    const getServiceCategories = async () => {
      const specializationStore = useSpecializationsStore();
      try {
        const specializationId = specializationStore.getSelectedSpecialization.id;
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
      console.log("selectedServiceCategory: ", selectedServiceCategory);
      try {
        const response = await api.get(`/get_service/${categoryId}`);
        servicesByCategory.value = response.data;
        console.log("подгружены сервисы категории: ", servicesByCategory.value);
      } catch (err) {
        console.error("ошибка загрузке сервисов данной категории: ", categoryId, err);
      }
    };
    const deleteOrder = async () => {
      deleteConfirmPage.value.open(
        "Подтвердите удаление",
        `Вы уверены, что хотите удалить ордер "${order.value.id}"?`,
        async () => {
          try {
            const response = await api.delete(`/delete_order/${order.value.id}`);
            if (response.status === 200) {
              $q.notify({
                type: "positive",
                message: `ордер ${order.value.id} удален`,
                position: "top",
                timeout: 500
              });
            }
          } catch (err) {
            console.error("Ошибка удаления ордера", err);
          } finally {
            router.back();
          }
        }
      );
    };
    onMounted(() => {
      if (orderStore.currentOrder) {
        order.value = orderStore.currentOrder;
        console.log("ордер: ", order.value);
        paid.value = order.value.paid;
        orderStatus.value = order.value.status;
        client.value.name = order.value.client_name;
        client.value.id = order.value.client_id;
        clientId.value = order.value.client_id;
        modelId.value = order.value.model_id;
        model.value.name = order.value.model_name;
        if (order.value.model_id) {
          modelId.value = order.value.model_id;
        }
        if (order.value.comments) {
          comments.value = order.value.comments;
        }
        getServices();
        getMaterialsByOrder();
      } else {
        console.log("режим нового ордера");
        order.value = {
          status: "waiting",
          paid: false,
          clientId: null,
          modelId: null,
          comments: ""
        };
        editMode.value = true;
        getProductCategories();
      }
      getClients();
      getModels();
      getServiceCategories();
    });
    const getClients = async () => {
      try {
        const response = await api.get(`/get_clients/${selectedSpecializationId}`);
        clients.value = response.data;
        console.log("clients: ", clients);
      } catch (err) {
        console.error("ошибка получения клиентов: ", err);
      }
    };
    const getModels = async () => {
      try {
        const response = await api.get(`/get_equipment_models/${selectedSpecializationId}`);
        models.value = response.data;
        console.log("models: ", models);
      } catch (err) {
        console.error("ошибка получения моделей: ", err);
      }
    };
    const getProductCategories = async () => {
      try {
        const response = await api.get(`/get_product_categories/${selectedSpecializationId}`);
        productCategories.value = response.data;
        console.log("productCategories: ", productCategories.value);
      } catch (err) {
        console.error(err);
      }
    };
    const getProductsByCategory = async (selectedProductCategory2) => {
      selectedStoreProduct.value = null;
      try {
        const response = await api.get(`/get_products/${selectedProductCategory2.id}`);
        storeProducts.value = response.data;
        console.log("products: ", products.value);
      } catch (err) {
        console.error(err);
      }
    };
    const activeEditMode = async () => {
      console.log("активен режим редактора ордера");
      await getClients();
      console.log("client_id: ", order.value.client_id);
      client.value.id = order.value.client_id;
      await getModels();
      console.log("model_id: ", order.value.model_id);
      model.value.id = order.value.model_id;
      editMode.value = true;
      await getProductCategories();
    };
    const saveOrder = async () => {
      try {
        if (isNewOrder.value) {
          await createOrder();
        } else {
          await updateOrder();
        }
      } catch (err) {
        $q.notify({
          type: "negative",
          message: "ошибка сохранения заказа"
        });
        console.error("ошибка сохранения заказа: ", err);
      }
    };
    const createOrder = async () => {
      const token = localStorage.getItem("authToken");
      console.log("token: ", token);
      console.log("сохроняем новый ордер");
      console.log("services", services.value.map((service) => service.id));
      console.log("addedMaterials: ", materials.value);
      try {
        const response = await api.post(`/save_order`, {
          clientId: client.value.id,
          modelId: model.value.id,
          specializationId: selectedSpecializationId,
          totalAmount: totalSumProducts.value + totalSumMaterials.value + totalSumServices.value,
          addedMaterials: materials.value,
          addedProducts: products.value,
          servicesId: services.value.map((service) => service.id),
          comments: comments.value,
          paid: paid.value,
          userOrderNumber: "",
          status: orderStatus.value,
          materials: ""
        }, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        });
        console.log("response: ", response);
        console.log("переход не реализован");
        router.back();
      } catch (err) {
        console.error(err);
      }
    };
    const updateOrder = async () => {
      console.log("обновляем ордер на сервере");
      try {
        const totalAmount = totalSumServices.value + totalSumMaterials.value + totalSumProducts.value;
        console.log("client_id: ", client.value.id);
        console.log("model_id", model.value.id);
        const response = await api.post("/update_order", {
          id: order.value.id,
          client_id: client.value.id,
          model_id: model.value.id,
          specialization_id: selectedSpecializationId,
          user_order_number: "",
          total_amount: totalAmount,
          materials: materials.value,
          products: products.value,
          comments: comments.value,
          services: services.value.map((service) => service.id),
          paid: paid.value
        });
        console.log("данные для передачи: ", response);
        console.log("данные ордера обновлены");
        router.back();
      } catch (err) {
        console.error("ошибка обновления ордера", err);
      }
    };
    const totalSumServices = computed(() => {
      if (!services.value) return 0;
      return services.value.reduce((sum, service) => {
        const price = Number(service.price) || 0;
        return sum + price;
      }, 0);
    });
    const totalSumMaterials = computed(() => {
      if (!materials.value) return 0;
      return materials.value.reduce((sum, material) => {
        const price = Number(material.price) || 0;
        const amount = Number(material.amount) || 0;
        return sum + price * amount;
      }, 0);
    });
    const totalSumProducts = computed(() => {
      if (!products.value) return 0;
      return products.value.reduce((sum, product) => {
        const price = Number(product.price) || 0;
        const amount = Number(product.amount) || 0;
        return sum + price * amount;
      }, 0);
    });
    const computedToggleColor = computed(() => {
      switch (orderStatus.value) {
        case "waiting":
          return "orange";
        case "process":
          return "red";
        case "done":
          return "green";
        default:
          return "yellow";
      }
    });
    const closeDialog = () => {
      showAddNewClientDialog.value = false;
      showAddNewMaterialDialog.value = false;
      showAddNewServiceDialog.value = false;
      showAddNewModelDialog.value = false;
      newMaterial.value = { name: "", price: 0, amount: 0 };
    };
    const addNewService = async () => {
      try {
        const response = await api.post("/add_service", {
          service: newService.value.name,
          price: newService.value.price,
          category_id: selectedServiceCategory.value
        });
        console.log("response: ", response);
        await getServicesByCategory(selectedServiceCategory.value);
        closeDialog();
      } catch (err) {
        console.error("ошибка создание нового сервиса:  ", err);
      }
    };
    const addMaterial = () => {
      if (newMaterial.value.name.trim() !== "" && newMaterial.value.price > 0 && newMaterial.value.amount > 0) {
        console.log("Добавление нового материала:", { ...newMaterial.value });
        materials.value.push(newMaterial.value);
        newMaterial.value = { name: "", price: 0, amount: 0 };
        showAddNewMaterialDialog.value = false;
        console.log("materials: ", materials.value);
      } else {
        console.error("Введите корректные данные");
      }
    };
    const addNewClient = async () => {
      try {
        const response = await api.post("/add_client", {
          name: newClient.value.name,
          phone: newClient.value.phone,
          specialization_id: selectedSpecializationId
        });
        showAddNewClientDialog.value = false;
        getClients();
        client.value = response.data.client;
      } catch (err) {
        console.error("ошибка добавления клиента: ", err);
      }
    };
    const addNewModel = async () => {
      try {
        const response = await api.post("/add_equipment_model", {
          name: newModel.value.name,
          specialization_id: selectedSpecializationId
        });
        showAddNewModelDialog.value = false;
        getModels();
        model.value = response.data.model;
      } catch (err) {
        console.error(err);
      }
    };
    const addProductFromStore = () => {
      console.log("selectedStoreProduct: ", selectedStoreProduct.value);
      const product = {
        product_id: selectedStoreProduct.value.id,
        name: selectedStoreProduct.value.name,
        price: selectedStoreProduct.value.base_sale_price,
        amount: 1
      };
      products.value.push(product);
      console.log("products: ", products.value);
      showAddProductFromStoreDialog.value = false;
    };
    const deleteMaterialFromOrder = (index) => {
      console.log("index: ", index);
      materials.value.splice(index, 1);
    };
    const __returned__ = { deleteConfirmPage, $q, router, specializationsStore, selectedSpecializationId, orderStore, orderStatus, paid, order, services, materials, storeProducts, products, productCategories, selectedStoreProduct, selectedProductCategory, client, clientId, modelId, comments, clients, models, model, selectedServiceCategory, serviceCategories, servicesByCategory, tab, editMode, isNewOrder, showAddNewMaterialDialog, showAddNewServiceDialog, showAddNewClientDialog, showAddNewModelDialog, showAddProductFromStoreDialog, newMaterial, newService, newClient, newModel, getServices, switсhPaidStatus, updateOrderStatus, getMaterialsByOrder, getServiceCategories, getServicesByCategory, deleteOrder, getClients, getModels, getProductCategories, getProductsByCategory, activeEditMode, saveOrder, createOrder, updateOrder, totalSumServices, totalSumMaterials, totalSumProducts, computedToggleColor, closeDialog, addNewService, addMaterial, addNewClient, addNewModel, addProductFromStore, deleteMaterialFromOrder, ref, computed, onMounted, get useOrderStore() {
      return useOrderStore;
    }, get api() {
      return api;
    }, get useSpecializationsStore() {
      return useSpecializationsStore;
    }, get useRouter() {
      return useRouter;
    }, DeleteConfirmPage, get useQuasar() {
      return useQuasar;
    } };
    Object.defineProperty(__returned__, "__isScriptSetup", { enumerable: false, value: true });
    return __returned__;
  }
};
const _hoisted_1 = { class: "row justify-between" };
const _hoisted_2 = { class: "items-center row q-gutter-x-md" };
const _hoisted_3 = { class: "row items-center" };
const _hoisted_4 = {
  key: 0,
  class: "col-auto self-end"
};
const _hoisted_5 = {
  key: 1,
  class: "col-auto self-end"
};
const _hoisted_6 = { class: "text-grey text-center display: flex" };
const _hoisted_7 = { class: "text-green" };
const _hoisted_8 = { class: "text-center text-grey" };
const _hoisted_9 = { class: "text-center text-grey" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
  return openBlock(), createElementBlock(Fragment, null, [
    createBaseVNode("div", _hoisted_1, [
      !$setup.editMode ? (openBlock(), createBlock(QBtn, {
        key: 0,
        flat: "",
        color: "yellow",
        label: "НАЗАД",
        onClick: _cache[0] || (_cache[0] = ($event) => _ctx.$router.back()),
        size: "md",
        class: "btn-flex"
      })) : createCommentVNode("", true),
      $setup.editMode ? (openBlock(), createBlock(QBtn, {
        key: 1,
        flat: "",
        color: "yellow",
        label: "отмена",
        onClick: _cache[1] || (_cache[1] = ($event) => _ctx.$router.back()),
        size: "md",
        class: "btn-flex"
      })) : createCommentVNode("", true),
      createBaseVNode("div", null, [
        !$setup.editMode ? (openBlock(), createBlock(QBtn, {
          key: 0,
          flat: "",
          size: "md",
          color: "yellow",
          class: "justify-end",
          icon: "delete_forever",
          onClick: $setup.deleteOrder
        })) : createCommentVNode("", true),
        !$setup.editMode ? (openBlock(), createBlock(QBtn, {
          key: 1,
          flat: "",
          size: "md",
          color: "yellow",
          label: "РЕД",
          onClick: $setup.activeEditMode
        })) : createCommentVNode("", true),
        $setup.editMode ? (openBlock(), createBlock(QBtn, {
          key: 2,
          flat: "",
          size: "md",
          color: "yellow",
          label: "сохр",
          onClick: $setup.saveOrder
        })) : createCommentVNode("", true)
      ]),
      createBaseVNode("div", _hoisted_2, [
        createVNode(QBtnToggle, {
          modelValue: $setup.orderStatus,
          "onUpdate:modelValue": [
            _cache[2] || (_cache[2] = ($event) => $setup.orderStatus = $event),
            $setup.updateOrderStatus
          ],
          size: "md",
          outline: "",
          glossy: "",
          "toggle-color": $setup.computedToggleColor,
          color: "grey",
          options: [
            { label: "ожид", value: "waiting" },
            { label: "враб", value: "process" },
            { label: "готово", value: "done" }
          ]
        }, null, 8, ["modelValue", "toggle-color"]),
        createVNode(QBtn, {
          outline: "",
          size: "md",
          onClick: $setup.switсhPaidStatus,
          color: $setup.paid ? "green" : "grey",
          glossy: "",
          label: "опл"
        }, null, 8, ["color"])
      ])
    ]),
    createBaseVNode("div", _hoisted_3, [
      createVNode(QSelect, {
        modelValue: $setup.client,
        "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => $setup.client = $event),
        options: $setup.clients,
        "option-value": "id",
        "option-label": "name",
        label: "клиент",
        dense: "",
        disable: !$setup.editMode,
        class: "col",
        color: "yellow"
      }, null, 8, ["modelValue", "options", "disable"]),
      $setup.editMode ? (openBlock(), createElementBlock("div", _hoisted_4, [
        createVNode(QBtn, {
          class: "col-auto text-yellow",
          onClick: _cache[4] || (_cache[4] = ($event) => $setup.showAddNewClientDialog = true)
        }, {
          default: withCtx(() => _cache[31] || (_cache[31] = [
            createTextVNode("+")
          ])),
          _: 1
        })
      ])) : createCommentVNode("", true),
      createVNode(QSelect, {
        modelValue: $setup.model,
        "onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => $setup.model = $event),
        options: $setup.models,
        "option-value": "id",
        "option-label": "name",
        label: "модель",
        disable: !$setup.editMode,
        class: "col",
        dense: "",
        color: "yellow"
      }, null, 8, ["modelValue", "options", "disable"]),
      $setup.editMode ? (openBlock(), createElementBlock("div", _hoisted_5, [
        createVNode(QBtn, {
          class: "col-auto text-yellow",
          onClick: _cache[6] || (_cache[6] = ($event) => $setup.showAddNewModelDialog = true)
        }, {
          default: withCtx(() => _cache[32] || (_cache[32] = [
            createTextVNode("+")
          ])),
          _: 1
        })
      ])) : createCommentVNode("", true)
    ]),
    createBaseVNode("div", null, [
      createVNode(QCard, null, {
        default: withCtx(() => [
          withDirectives(createVNode(QTabs, {
            modelValue: $setup.tab,
            "onUpdate:modelValue": _cache[7] || (_cache[7] = ($event) => $setup.tab = $event),
            dense: "",
            class: "text-grey",
            "active-color": "yellow",
            "indicator-color": "yellow",
            align: "justify",
            "narrow-indicator": ""
          }, {
            default: withCtx(() => [
              createVNode(QTab, {
                name: "all",
                label: `работ: ${$setup.services?.length || 0} материалов: ${($setup.materials?.length || 0) + ($setup.products?.length || 0)}`
              }, null, 8, ["label"]),
              $setup.editMode ? (openBlock(), createBlock(QTab, {
                key: 0,
                name: "servicesChoice",
                label: "работы"
              })) : createCommentVNode("", true),
              $setup.editMode ? (openBlock(), createBlock(QTab, {
                key: 1,
                name: "materialsChoice",
                label: "материалы"
              })) : createCommentVNode("", true)
            ]),
            _: 1
          }, 8, ["modelValue"]), [
            [vShow, $setup.editMode]
          ]),
          createVNode(QSeparator),
          createVNode(QTabPanels, {
            modelValue: $setup.tab,
            "onUpdate:modelValue": _cache[14] || (_cache[14] = ($event) => $setup.tab = $event),
            animated: ""
          }, {
            default: withCtx(() => [
              createVNode(QTabPanel, {
                name: "all",
                style: { "padding": "0" }
              }, {
                default: withCtx(() => [
                  createBaseVNode("div", null, [
                    createVNode(QList, {
                      bordered: "",
                      separator: ""
                    }, {
                      default: withCtx(() => [
                        !$setup.services ? (openBlock(), createBlock(QItemLabel, { key: 0 }, {
                          default: withCtx(() => _cache[33] || (_cache[33] = [
                            createTextVNode("Нет сервисов")
                          ])),
                          _: 1
                        })) : createCommentVNode("", true),
                        (openBlock(true), createElementBlock(Fragment, null, renderList($setup.services, (service, index) => {
                          return openBlock(), createBlock(QItem, {
                            key: index,
                            class: "w-100 justify-between",
                            style: { "width": "100%" }
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
                                      createTextVNode(toDisplayString(service.price) + "р ", 1)
                                    ]),
                                    _: 2
                                  }, 1024)
                                ]),
                                _: 2
                              }, 1024),
                              $setup.editMode ? (openBlock(), createBlock(QItemSection, {
                                key: 0,
                                class: "col-auto"
                              }, {
                                default: withCtx(() => [
                                  createVNode(QBtn, {
                                    icon: "delete_forever",
                                    onClick: ($event) => $setup.services.splice(index, 1),
                                    color: "red",
                                    flat: "",
                                    round: ""
                                  }, null, 8, ["onClick"])
                                ]),
                                _: 2
                              }, 1024)) : createCommentVNode("", true)
                            ]),
                            _: 2
                          }, 1024);
                        }), 128))
                      ]),
                      _: 1
                    }),
                    withDirectives(createBaseVNode("div", { class: "text-grey text-left" }, " всего по работе : " + toDisplayString($setup.totalSumServices) + "р ", 513), [
                      [vShow, $setup.totalSumServices > 0 && $setup.totalSumMaterials > 0]
                    ]),
                    createVNode(QList, {
                      bordered: "",
                      separator: ""
                    }, {
                      default: withCtx(() => [
                        !$setup.materials ? (openBlock(), createBlock(QItemLabel, { key: 0 }, {
                          default: withCtx(() => _cache[34] || (_cache[34] = [
                            createTextVNode("Нет материалов")
                          ])),
                          _: 1
                        })) : createCommentVNode("", true),
                        (openBlock(true), createElementBlock(Fragment, null, renderList($setup.materials, (material, index) => {
                          return openBlock(), createBlock(QItem, {
                            key: material.id,
                            class: "w-100 justify-between row",
                            style: { "width": "100%" }
                          }, {
                            default: withCtx(() => [
                              createVNode(QItemSection, { class: "col-7" }, {
                                default: withCtx(() => [
                                  createVNode(QItemLabel, { class: "text-left" }, {
                                    default: withCtx(() => [
                                      createTextVNode(toDisplayString(material.name), 1)
                                    ]),
                                    _: 2
                                  }, 1024)
                                ]),
                                _: 2
                              }, 1024),
                              createVNode(QItemSection, { class: "col-1" }, {
                                default: withCtx(() => [
                                  createVNode(QItemLabel, { class: "text-right" }, {
                                    default: withCtx(() => [
                                      createTextVNode(toDisplayString(material.price) + "р ", 1)
                                    ]),
                                    _: 2
                                  }, 1024)
                                ]),
                                _: 2
                              }, 1024),
                              createVNode(QItemSection, { class: "col-1" }, {
                                default: withCtx(() => [
                                  createVNode(QItemLabel, { class: "text-center" }, {
                                    default: withCtx(() => [
                                      createTextVNode(" х" + toDisplayString(material.amount), 1)
                                    ]),
                                    _: 2
                                  }, 1024)
                                ]),
                                _: 2
                              }, 1024),
                              createVNode(QItemSection, { class: "col-1" }, {
                                default: withCtx(() => [
                                  createVNode(QItemLabel, { class: "text-right" }, {
                                    default: withCtx(() => [
                                      createTextVNode(toDisplayString(material.price * material.amount) + "р ", 1)
                                    ]),
                                    _: 2
                                  }, 1024)
                                ]),
                                _: 2
                              }, 1024),
                              $setup.editMode ? (openBlock(), createBlock(QItemSection, {
                                key: 0,
                                class: "col-auto"
                              }, {
                                default: withCtx(() => [
                                  createVNode(QBtn, {
                                    icon: "delete_forever",
                                    onClick: ($event) => $setup.deleteMaterialFromOrder(index),
                                    color: "red",
                                    flat: "",
                                    round: ""
                                  }, null, 8, ["onClick"])
                                ]),
                                _: 2
                              }, 1024)) : createCommentVNode("", true)
                            ]),
                            _: 2
                          }, 1024);
                        }), 128))
                      ]),
                      _: 1
                    }),
                    createVNode(QList, {
                      bordered: "",
                      separator: ""
                    }, {
                      default: withCtx(() => [
                        !$setup.products ? (openBlock(), createBlock(QItemLabel, { key: 0 }, {
                          default: withCtx(() => _cache[35] || (_cache[35] = [
                            createTextVNode("Нет материалов")
                          ])),
                          _: 1
                        })) : createCommentVNode("", true),
                        (openBlock(true), createElementBlock(Fragment, null, renderList($setup.products, (product) => {
                          return openBlock(), createBlock(QItem, {
                            key: product,
                            class: "w-100 justify-between row",
                            style: { "width": "100%" }
                          }, {
                            default: withCtx(() => [
                              createVNode(QItemSection, { class: "col-7" }, {
                                default: withCtx(() => [
                                  createVNode(QItemLabel, { class: "text-left" }, {
                                    default: withCtx(() => [
                                      createTextVNode(toDisplayString(product.name), 1)
                                    ]),
                                    _: 2
                                  }, 1024)
                                ]),
                                _: 2
                              }, 1024),
                              createVNode(QItemSection, { class: "col-1" }, {
                                default: withCtx(() => [
                                  createVNode(QItemLabel, { class: "text-right" }, {
                                    default: withCtx(() => [
                                      createTextVNode(toDisplayString(product.price) + "р ", 1)
                                    ]),
                                    _: 2
                                  }, 1024)
                                ]),
                                _: 2
                              }, 1024),
                              createVNode(QItemSection, { class: "col-1" }, {
                                default: withCtx(() => [
                                  createVNode(QItemLabel, { class: "text-center" }, {
                                    default: withCtx(() => [
                                      createTextVNode(" х" + toDisplayString(product.amount), 1)
                                    ]),
                                    _: 2
                                  }, 1024)
                                ]),
                                _: 2
                              }, 1024),
                              createVNode(QItemSection, { class: "col-1" }, {
                                default: withCtx(() => [
                                  createVNode(QItemLabel, { class: "text-right" }, {
                                    default: withCtx(() => [
                                      createTextVNode(toDisplayString(product.price * product.amount) + "р ", 1)
                                    ]),
                                    _: 2
                                  }, 1024)
                                ]),
                                _: 2
                              }, 1024),
                              $setup.editMode ? (openBlock(), createBlock(QItemSection, {
                                key: 0,
                                class: "col-auto"
                              }, {
                                default: withCtx(() => [
                                  createVNode(QBtn, {
                                    icon: "delete_forever",
                                    onClick: _cache[8] || (_cache[8] = ($event) => $setup.products.splice(_ctx.index, 1)),
                                    color: "red",
                                    flat: "",
                                    round: ""
                                  })
                                ]),
                                _: 1
                              })) : createCommentVNode("", true)
                            ]),
                            _: 2
                          }, 1024);
                        }), 128))
                      ]),
                      _: 1
                    }),
                    withDirectives(createBaseVNode("div", { class: "text-grey text-left" }, " всего по материалам: " + toDisplayString($setup.totalSumMaterials + $setup.totalSumProducts) + "р ", 513), [
                      [vShow, $setup.totalSumMaterials > 0 && $setup.totalSumServices > 0]
                    ]),
                    createBaseVNode("div", _hoisted_6, [
                      _cache[36] || (_cache[36] = createBaseVNode("div", null, " всего к оплате: ", -1)),
                      createBaseVNode("div", _hoisted_7, toDisplayString($setup.totalSumMaterials + $setup.totalSumProducts + $setup.totalSumServices), 1),
                      _cache[37] || (_cache[37] = createTextVNode(" р "))
                    ])
                  ]),
                  createVNode(QInput, {
                    type: "textarea",
                    modelValue: $setup.comments,
                    "onUpdate:modelValue": _cache[9] || (_cache[9] = ($event) => $setup.comments = $event),
                    label: "комментарии",
                    "label-color": "yellow",
                    color: "yellow",
                    autogrow: "",
                    placeholder: "Коментариев нет",
                    disable: !$setup.editMode
                  }, null, 8, ["modelValue", "disable"])
                ]),
                _: 1
              }),
              createVNode(QTabPanel, {
                name: "servicesChoice",
                style: { "padding": "0" }
              }, {
                default: withCtx(() => [
                  createVNode(QSelect, {
                    modelValue: $setup.selectedServiceCategory,
                    "onUpdate:modelValue": [
                      _cache[10] || (_cache[10] = ($event) => $setup.selectedServiceCategory = $event),
                      $setup.getServicesByCategory
                    ],
                    options: $setup.serviceCategories,
                    "option-label": "category_name",
                    "option-value": "id",
                    "emit-value": "",
                    "map-options": "",
                    label: "категории работ",
                    dense: "",
                    placeholder: "нет категорий",
                    "label-color": "grey",
                    color: "yellow"
                  }, null, 8, ["modelValue", "options"]),
                  createVNode(QList, {
                    bordered: "",
                    separator: ""
                  }, {
                    default: withCtx(() => [
                      !$setup.servicesByCategory ? (openBlock(), createBlock(QItemLabel, { key: 0 }, {
                        default: withCtx(() => _cache[38] || (_cache[38] = [
                          createTextVNode("Нет сервисов")
                        ])),
                        _: 1
                      })) : createCommentVNode("", true),
                      (openBlock(true), createElementBlock(Fragment, null, renderList($setup.servicesByCategory, (service) => {
                        return withDirectives((openBlock(), createBlock(QItem, {
                          key: service,
                          class: normalizeClass(["w-100 justify-between selectService", {
                            "text-yellow": $setup.services.some((s) => s.id === service.id)
                          }]),
                          style: { "width": "100%" },
                          clickable: "",
                          onClick: ($event) => $setup.services.push({ ...service }),
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
                              onClick: _cache[11] || (_cache[11] = ($event) => $setup.showAddNewServiceDialog = true),
                              size: "20px"
                            })
                          ]),
                          _: 2
                        }, 1032, ["onClick", "q-item", "class"])), [
                          [Ripple]
                        ]);
                      }), 128))
                    ]),
                    _: 1
                  })
                ]),
                _: 1
              }),
              createVNode(QTabPanel, {
                name: "materialsChoice",
                style: { "padding": "0" }
              }, {
                default: withCtx(() => [
                  createBaseVNode("div", _hoisted_8, "материалы: " + toDisplayString($setup.totalSumMaterials) + "р", 1),
                  createVNode(QList, {
                    bordered: "",
                    separator: ""
                  }, {
                    default: withCtx(() => [
                      !$setup.materials ? (openBlock(), createBlock(QItemLabel, { key: 0 }, {
                        default: withCtx(() => _cache[39] || (_cache[39] = [
                          createTextVNode(" нет материалов")
                        ])),
                        _: 1
                      })) : createCommentVNode("", true),
                      (openBlock(true), createElementBlock(Fragment, null, renderList($setup.materials, (material, index) => {
                        return openBlock(), createBlock(QItem, {
                          key: index,
                          class: "w-100 justify-between row"
                        }, {
                          default: withCtx(() => [
                            createVNode(QItemSection, { class: "col-7" }, {
                              default: withCtx(() => [
                                createVNode(QInput, {
                                  modelValue: material.name,
                                  "onUpdate:modelValue": ($event) => material.name = $event
                                }, null, 8, ["modelValue", "onUpdate:modelValue"])
                              ]),
                              _: 2
                            }, 1024),
                            createVNode(QItemSection, { class: "col-1" }, {
                              default: withCtx(() => [
                                createVNode(QInput, {
                                  modelValue: material.price,
                                  "onUpdate:modelValue": ($event) => material.price = $event,
                                  "input-class": "text-right"
                                }, null, 8, ["modelValue", "onUpdate:modelValue"])
                              ]),
                              _: 2
                            }, 1024),
                            createVNode(QItemSection, { class: "col-1" }, {
                              default: withCtx(() => [
                                createVNode(QInput, {
                                  modelValue: material.amount,
                                  "onUpdate:modelValue": ($event) => material.amount = $event,
                                  "input-class": "text-right",
                                  prefix: "x"
                                }, null, 8, ["modelValue", "onUpdate:modelValue"])
                              ]),
                              _: 2
                            }, 1024),
                            createVNode(QItemSection, {
                              class: "col-1",
                              disabled: "disabled",
                              "input-class": "text-right"
                            }, {
                              default: withCtx(() => [
                                createVNode(QInput, {
                                  "model-value": material.price * material.amount
                                }, null, 8, ["model-value"])
                              ]),
                              _: 2
                            }, 1024),
                            createVNode(QItemSection, { class: "col-auto" }, {
                              default: withCtx(() => [
                                createVNode(QBtn, {
                                  icon: "delete_forever",
                                  onClick: ($event) => $setup.materials.splice(index, 1),
                                  color: "red",
                                  flat: "",
                                  round: ""
                                }, null, 8, ["onClick"])
                              ]),
                              _: 2
                            }, 1024)
                          ]),
                          _: 2
                        }, 1024);
                      }), 128))
                    ]),
                    _: 1
                  }),
                  createBaseVNode("div", _hoisted_9, "продукты: " + toDisplayString($setup.totalSumProducts) + " р", 1),
                  createVNode(QList, {
                    bordered: "",
                    separator: ""
                  }, {
                    default: withCtx(() => [
                      !$setup.products ? (openBlock(), createBlock(QItemLabel, { key: 0 }, {
                        default: withCtx(() => _cache[40] || (_cache[40] = [
                          createTextVNode(" нет материалов")
                        ])),
                        _: 1
                      })) : createCommentVNode("", true),
                      (openBlock(true), createElementBlock(Fragment, null, renderList($setup.products, (product, index) => {
                        return openBlock(), createBlock(QItem, {
                          key: index,
                          class: "w-100 justify-between row"
                        }, {
                          default: withCtx(() => [
                            createVNode(QItemSection, { class: "col-7" }, {
                              default: withCtx(() => [
                                createVNode(QInput, {
                                  modelValue: product.name,
                                  "onUpdate:modelValue": ($event) => product.name = $event
                                }, null, 8, ["modelValue", "onUpdate:modelValue"])
                              ]),
                              _: 2
                            }, 1024),
                            createVNode(QItemSection, { class: "col-1" }, {
                              default: withCtx(() => [
                                createVNode(QInput, {
                                  modelValue: product.price,
                                  "onUpdate:modelValue": ($event) => product.price = $event,
                                  "input-class": "text-right"
                                }, null, 8, ["modelValue", "onUpdate:modelValue"])
                              ]),
                              _: 2
                            }, 1024),
                            createVNode(QItemSection, { class: "col-1" }, {
                              default: withCtx(() => [
                                createVNode(QInput, {
                                  modelValue: product.amount,
                                  "onUpdate:modelValue": ($event) => product.amount = $event,
                                  "input-class": "text-right",
                                  prefix: "x"
                                }, null, 8, ["modelValue", "onUpdate:modelValue"])
                              ]),
                              _: 2
                            }, 1024),
                            createVNode(QItemSection, {
                              class: "col-1",
                              disabled: "disabled",
                              "input-class": "text-right"
                            }, {
                              default: withCtx(() => [
                                createVNode(QInput, {
                                  "model-value": product.price * product.amount
                                }, null, 8, ["model-value"])
                              ]),
                              _: 2
                            }, 1024),
                            createVNode(QItemSection, { class: "col-auto" }, {
                              default: withCtx(() => [
                                createVNode(QBtn, {
                                  icon: "delete_forever",
                                  onClick: ($event) => $setup.products.splice(index, 1),
                                  color: "red",
                                  flat: "",
                                  round: ""
                                }, null, 8, ["onClick"])
                              ]),
                              _: 2
                            }, 1024)
                          ]),
                          _: 2
                        }, 1024);
                      }), 128))
                    ]),
                    _: 1
                  }),
                  createVNode(QBtn, {
                    icon: "add",
                    round: "",
                    class: "fab bg-yellow text-black",
                    onClick: _cache[12] || (_cache[12] = ($event) => $setup.showAddNewMaterialDialog = true),
                    size: "20px"
                  }),
                  createVNode(QBtn, {
                    icon: "storage",
                    round: "",
                    class: "bg-yellow text-black",
                    size: "18",
                    onClick: _cache[13] || (_cache[13] = ($event) => $setup.showAddProductFromStoreDialog = true),
                    style: { "position": "fixed", "bottom": "100px", "right": "16px", "z-index": "1000" }
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
    createBaseVNode("div", null, [
      createVNode(QDialog, {
        modelValue: $setup.showAddNewMaterialDialog,
        "onUpdate:modelValue": _cache[18] || (_cache[18] = ($event) => $setup.showAddNewMaterialDialog = $event),
        persistent: ""
      }, {
        default: withCtx(() => [
          createVNode(QCard, null, {
            default: withCtx(() => [
              createVNode(QCardSection, null, {
                default: withCtx(() => [
                  _cache[41] || (_cache[41] = createBaseVNode("div", { class: "text-h6" }, "Добавление материала", -1)),
                  createVNode(QInput, {
                    modelValue: $setup.newMaterial.name,
                    "onUpdate:modelValue": _cache[15] || (_cache[15] = ($event) => $setup.newMaterial.name = $event),
                    "label-color": "yellow",
                    color: "yellow",
                    label: "Название",
                    outlined: "",
                    class: "q-mb-md"
                  }, null, 8, ["modelValue"]),
                  createVNode(QInput, {
                    modelValue: $setup.newMaterial.price,
                    "onUpdate:modelValue": _cache[16] || (_cache[16] = ($event) => $setup.newMaterial.price = $event),
                    modelModifiers: { number: true },
                    label: "Цена",
                    "label-color": "yellow",
                    color: "yellow",
                    type: "number",
                    outlined: "",
                    class: "q-mb-md"
                  }, null, 8, ["modelValue"]),
                  createVNode(QInput, {
                    modelValue: $setup.newMaterial.amount,
                    "onUpdate:modelValue": _cache[17] || (_cache[17] = ($event) => $setup.newMaterial.amount = $event),
                    modelModifiers: { number: true },
                    label: "Количество",
                    "label-color": "yellow",
                    color: "yellow",
                    type: "number",
                    outlined: ""
                  }, null, 8, ["modelValue"])
                ]),
                _: 1
              }),
              createVNode(QCardActions, { align: "right" }, {
                default: withCtx(() => [
                  createVNode(QBtn, {
                    flat: "",
                    label: "Отмена",
                    color: "yellow",
                    onClick: $setup.closeDialog
                  }),
                  createVNode(QBtn, {
                    flat: "",
                    label: "Добавить",
                    color: "yellow",
                    onClick: $setup.addMaterial
                  })
                ]),
                _: 1
              })
            ]),
            _: 1
          })
        ]),
        _: 1
      }, 8, ["modelValue"])
    ]),
    createBaseVNode("div", null, [
      createVNode(QDialog, {
        modelValue: $setup.showAddNewServiceDialog,
        "onUpdate:modelValue": _cache[21] || (_cache[21] = ($event) => $setup.showAddNewServiceDialog = $event),
        persistent: ""
      }, {
        default: withCtx(() => [
          createVNode(QCard, null, {
            default: withCtx(() => [
              createVNode(QCardSection, null, {
                default: withCtx(() => [
                  _cache[42] || (_cache[42] = createBaseVNode("div", { class: "text-h6" }, "Добавление сервиса", -1)),
                  createVNode(QInput, {
                    modelValue: $setup.newService.name,
                    "onUpdate:modelValue": _cache[19] || (_cache[19] = ($event) => $setup.newService.name = $event),
                    "label-color": "yellow",
                    color: "yellow",
                    label: "Название",
                    outlined: "",
                    class: "q-mb-md"
                  }, null, 8, ["modelValue"]),
                  createVNode(QInput, {
                    modelValue: $setup.newService.price,
                    "onUpdate:modelValue": _cache[20] || (_cache[20] = ($event) => $setup.newService.price = $event),
                    modelModifiers: { number: true },
                    label: "Цена",
                    "label-color": "yellow",
                    color: "yellow",
                    type: "number",
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
                    label: "Отмена",
                    color: "yellow",
                    onClick: $setup.closeDialog
                  }),
                  createVNode(QBtn, {
                    flat: "",
                    label: "Добавить",
                    color: "yellow",
                    onClick: $setup.addNewService
                  })
                ]),
                _: 1
              })
            ]),
            _: 1
          })
        ]),
        _: 1
      }, 8, ["modelValue"])
    ]),
    createBaseVNode("div", null, [
      createVNode(QDialog, {
        modelValue: $setup.showAddNewClientDialog,
        "onUpdate:modelValue": _cache[24] || (_cache[24] = ($event) => $setup.showAddNewClientDialog = $event),
        persistent: ""
      }, {
        default: withCtx(() => [
          createVNode(QCard, null, {
            default: withCtx(() => [
              createVNode(QCardSection, null, {
                default: withCtx(() => [
                  _cache[43] || (_cache[43] = createBaseVNode("div", { class: "text-h6" }, "Добавление клиента", -1)),
                  createVNode(QInput, {
                    modelValue: $setup.newClient.name,
                    "onUpdate:modelValue": _cache[22] || (_cache[22] = ($event) => $setup.newClient.name = $event),
                    "label-color": "yellow",
                    color: "yellow",
                    label: "Имя клиента",
                    outlined: "",
                    class: "q-mb-md"
                  }, null, 8, ["modelValue"]),
                  createVNode(QInput, {
                    modelValue: $setup.newClient.phone,
                    "onUpdate:modelValue": _cache[23] || (_cache[23] = ($event) => $setup.newClient.phone = $event),
                    modelModifiers: { number: true },
                    label: "телефон",
                    "label-color": "yellow",
                    color: "yellow",
                    type: "text",
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
                    label: "Отмена",
                    color: "yellow",
                    onClick: $setup.closeDialog
                  }),
                  createVNode(QBtn, {
                    flat: "",
                    label: "Добавить",
                    color: "yellow",
                    onClick: $setup.addNewClient
                  })
                ]),
                _: 1
              })
            ]),
            _: 1
          })
        ]),
        _: 1
      }, 8, ["modelValue"])
    ]),
    createBaseVNode("div", null, [
      createVNode(QDialog, {
        modelValue: $setup.showAddNewModelDialog,
        "onUpdate:modelValue": _cache[26] || (_cache[26] = ($event) => $setup.showAddNewModelDialog = $event),
        persistent: ""
      }, {
        default: withCtx(() => [
          createVNode(QCard, null, {
            default: withCtx(() => [
              createVNode(QCardSection, null, {
                default: withCtx(() => [
                  _cache[44] || (_cache[44] = createBaseVNode("div", { class: "text-h6" }, "Добавление модели", -1)),
                  createVNode(QInput, {
                    modelValue: $setup.newModel.name,
                    "onUpdate:modelValue": _cache[25] || (_cache[25] = ($event) => $setup.newModel.name = $event),
                    "label-color": "yellow",
                    color: "yellow",
                    label: "Название модели",
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
                    label: "Отмена",
                    color: "yellow",
                    onClick: $setup.closeDialog
                  }),
                  createVNode(QBtn, {
                    flat: "",
                    label: "Добавить",
                    color: "yellow",
                    onClick: $setup.addNewModel
                  })
                ]),
                _: 1
              })
            ]),
            _: 1
          })
        ]),
        _: 1
      }, 8, ["modelValue"])
    ]),
    createBaseVNode("div", null, [
      createVNode(QDialog, {
        modelValue: $setup.showAddProductFromStoreDialog,
        "onUpdate:modelValue": _cache[30] || (_cache[30] = ($event) => $setup.showAddProductFromStoreDialog = $event),
        persistent: ""
      }, {
        default: withCtx(() => [
          createVNode(QCard, null, {
            default: withCtx(() => [
              createVNode(QCardSection, null, {
                default: withCtx(() => [
                  _cache[45] || (_cache[45] = createBaseVNode("div", { class: "text-h6" }, "Добавление товара со склада", -1)),
                  createVNode(QSelect, {
                    modelValue: $setup.selectedProductCategory,
                    "onUpdate:modelValue": [
                      _cache[27] || (_cache[27] = ($event) => $setup.selectedProductCategory = $event),
                      $setup.getProductsByCategory
                    ],
                    options: $setup.productCategories,
                    "option-label": "name",
                    label: "Выберите категорию",
                    "label-color": "yellow"
                  }, null, 8, ["modelValue", "options"]),
                  createVNode(QSelect, {
                    modelValue: $setup.selectedStoreProduct,
                    "onUpdate:modelValue": _cache[28] || (_cache[28] = ($event) => $setup.selectedStoreProduct = $event),
                    options: $setup.storeProducts,
                    "option-label": "name",
                    label: "выберите товар",
                    "label-color": "yellow"
                  }, null, 8, ["modelValue", "options"])
                ]),
                _: 1
              }),
              createVNode(QCardActions, { align: "right" }, {
                default: withCtx(() => [
                  createVNode(QBtn, {
                    flat: "",
                    label: "отмена",
                    color: "yellow",
                    onClick: _cache[29] || (_cache[29] = ($event) => $setup.showAddProductFromStoreDialog = false)
                  }),
                  createVNode(QBtn, {
                    flat: "",
                    label: "добавить",
                    color: "yellow",
                    onClick: $setup.addProductFromStore
                  })
                ]),
                _: 1
              })
            ]),
            _: 1
          })
        ]),
        _: 1
      }, 8, ["modelValue"])
    ]),
    createVNode($setup["DeleteConfirmPage"], { ref: "deleteConfirmPage" }, null, 512)
  ], 64);
}
const OrderDetailsPage = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render], ["__scopeId", "data-v-08baa732"], ["__file", "OrderDetailsPage.vue"]]);
export {
  OrderDetailsPage as default
};
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiT3JkZXJEZXRhaWxzUGFnZS1EQ09QQkJUcy5qcyIsInNvdXJjZXMiOlsiLi4vLi4vLi4vbm9kZV9tb2R1bGVzL3F1YXNhci9zcmMvY29tcG9uZW50cy9idG4tZ3JvdXAvUUJ0bkdyb3VwLmpzIiwiLi4vLi4vLi4vbm9kZV9tb2R1bGVzL3F1YXNhci9zcmMvY29tcG9uZW50cy9idG4tdG9nZ2xlL1FCdG5Ub2dnbGUuanMiLCIuLi8uLi8uLi9zcmMvcGFnZXMvT3JkZXJEZXRhaWxzUGFnZS52dWUiXSwic291cmNlc0NvbnRlbnQiOlsiaW1wb3J0IHsgaCwgY29tcHV0ZWQgfSBmcm9tICd2dWUnXG5cbmltcG9ydCB7IGNyZWF0ZUNvbXBvbmVudCB9IGZyb20gJy4uLy4uL3V0aWxzL3ByaXZhdGUuY3JlYXRlL2NyZWF0ZS5qcydcbmltcG9ydCB7IGhTbG90IH0gZnJvbSAnLi4vLi4vdXRpbHMvcHJpdmF0ZS5yZW5kZXIvcmVuZGVyLmpzJ1xuXG5leHBvcnQgZGVmYXVsdCBjcmVhdGVDb21wb25lbnQoe1xuICBuYW1lOiAnUUJ0bkdyb3VwJyxcblxuICBwcm9wczoge1xuICAgIHVuZWxldmF0ZWQ6IEJvb2xlYW4sXG4gICAgb3V0bGluZTogQm9vbGVhbixcbiAgICBmbGF0OiBCb29sZWFuLFxuICAgIHJvdW5kZWQ6IEJvb2xlYW4sXG4gICAgc3F1YXJlOiBCb29sZWFuLFxuICAgIHB1c2g6IEJvb2xlYW4sXG4gICAgc3RyZXRjaDogQm9vbGVhbixcbiAgICBnbG9zc3k6IEJvb2xlYW4sXG4gICAgc3ByZWFkOiBCb29sZWFuXG4gIH0sXG5cbiAgc2V0dXAgKHByb3BzLCB7IHNsb3RzIH0pIHtcbiAgICBjb25zdCBjbGFzc2VzID0gY29tcHV0ZWQoKCkgPT4ge1xuICAgICAgY29uc3QgY2xzID0gWyAndW5lbGV2YXRlZCcsICdvdXRsaW5lJywgJ2ZsYXQnLCAncm91bmRlZCcsICdzcXVhcmUnLCAncHVzaCcsICdzdHJldGNoJywgJ2dsb3NzeScgXVxuICAgICAgICAuZmlsdGVyKHQgPT4gcHJvcHNbIHQgXSA9PT0gdHJ1ZSlcbiAgICAgICAgLm1hcCh0ID0+IGBxLWJ0bi1ncm91cC0tJHsgdCB9YCkuam9pbignICcpXG5cbiAgICAgIHJldHVybiBgcS1idG4tZ3JvdXAgcm93IG5vLXdyYXAkeyBjbHMubGVuZ3RoICE9PSAwID8gJyAnICsgY2xzIDogJycgfWBcbiAgICAgICAgKyAocHJvcHMuc3ByZWFkID09PSB0cnVlID8gJyBxLWJ0bi1ncm91cC0tc3ByZWFkJyA6ICcgaW5saW5lJylcbiAgICB9KVxuXG4gICAgcmV0dXJuICgpID0+IGgoJ2RpdicsIHsgY2xhc3M6IGNsYXNzZXMudmFsdWUgfSwgaFNsb3Qoc2xvdHMuZGVmYXVsdCkpXG4gIH1cbn0pXG4iLCJpbXBvcnQgeyBoLCBjb21wdXRlZCB9IGZyb20gJ3Z1ZSdcblxuaW1wb3J0IFFCdG4gZnJvbSAnLi4vYnRuL1FCdG4uanMnXG5pbXBvcnQgUUJ0bkdyb3VwIGZyb20gJy4uL2J0bi1ncm91cC9RQnRuR3JvdXAuanMnXG5cbmltcG9ydCB7IGNyZWF0ZUNvbXBvbmVudCB9IGZyb20gJy4uLy4uL3V0aWxzL3ByaXZhdGUuY3JlYXRlL2NyZWF0ZS5qcydcbmltcG9ydCB7IHVzZUZvcm1JbmplY3QsIHVzZUZvcm1Qcm9wcyB9IGZyb20gJy4uLy4uL2NvbXBvc2FibGVzL3VzZS1mb3JtL3ByaXZhdGUudXNlLWZvcm0uanMnXG5cbmltcG9ydCB7IGhNZXJnZVNsb3QgfSBmcm9tICcuLi8uLi91dGlscy9wcml2YXRlLnJlbmRlci9yZW5kZXIuanMnXG5pbXBvcnQgeyBnZXRCdG5EZXNpZ25BdHRyIH0gZnJvbSAnLi4vYnRuL3VzZS1idG4uanMnXG5cbmV4cG9ydCBkZWZhdWx0IGNyZWF0ZUNvbXBvbmVudCh7XG4gIG5hbWU6ICdRQnRuVG9nZ2xlJyxcblxuICBwcm9wczoge1xuICAgIC4uLnVzZUZvcm1Qcm9wcyxcblxuICAgIG1vZGVsVmFsdWU6IHtcbiAgICAgIHJlcXVpcmVkOiB0cnVlXG4gICAgfSxcblxuICAgIG9wdGlvbnM6IHtcbiAgICAgIHR5cGU6IEFycmF5LFxuICAgICAgcmVxdWlyZWQ6IHRydWUsXG4gICAgICB2YWxpZGF0b3I6IHYgPT4gdi5ldmVyeShcbiAgICAgICAgb3B0ID0+ICgnbGFiZWwnIGluIG9wdCB8fCAnaWNvbicgaW4gb3B0IHx8ICdzbG90JyBpbiBvcHQpICYmICd2YWx1ZScgaW4gb3B0XG4gICAgICApXG4gICAgfSxcblxuICAgIC8vIFRvIGF2b2lkIHNlZWluZyB0aGUgYWN0aXZlIHJhaXNlIHNoYWRvdyB0aHJvdWdoXG4gICAgLy8gdGhlIHRyYW5zcGFyZW50IGJ1dHRvbiwgZ2l2ZSBpdCBhIGNvbG9yIChldmVuIHdoaXRlKVxuICAgIGNvbG9yOiBTdHJpbmcsXG4gICAgdGV4dENvbG9yOiBTdHJpbmcsXG4gICAgdG9nZ2xlQ29sb3I6IHtcbiAgICAgIHR5cGU6IFN0cmluZyxcbiAgICAgIGRlZmF1bHQ6ICdwcmltYXJ5J1xuICAgIH0sXG4gICAgdG9nZ2xlVGV4dENvbG9yOiBTdHJpbmcsXG5cbiAgICBvdXRsaW5lOiBCb29sZWFuLFxuICAgIGZsYXQ6IEJvb2xlYW4sXG4gICAgdW5lbGV2YXRlZDogQm9vbGVhbixcbiAgICByb3VuZGVkOiBCb29sZWFuLFxuICAgIHB1c2g6IEJvb2xlYW4sXG4gICAgZ2xvc3N5OiBCb29sZWFuLFxuXG4gICAgc2l6ZTogU3RyaW5nLFxuICAgIHBhZGRpbmc6IFN0cmluZyxcblxuICAgIG5vQ2FwczogQm9vbGVhbixcbiAgICBub1dyYXA6IEJvb2xlYW4sXG4gICAgZGVuc2U6IEJvb2xlYW4sXG4gICAgcmVhZG9ubHk6IEJvb2xlYW4sXG4gICAgZGlzYWJsZTogQm9vbGVhbixcblxuICAgIHN0YWNrOiBCb29sZWFuLFxuICAgIHN0cmV0Y2g6IEJvb2xlYW4sXG5cbiAgICBzcHJlYWQ6IEJvb2xlYW4sXG5cbiAgICBjbGVhcmFibGU6IEJvb2xlYW4sXG5cbiAgICByaXBwbGU6IHtcbiAgICAgIHR5cGU6IFsgQm9vbGVhbiwgT2JqZWN0IF0sXG4gICAgICBkZWZhdWx0OiB0cnVlXG4gICAgfVxuICB9LFxuXG4gIGVtaXRzOiBbICd1cGRhdGU6bW9kZWxWYWx1ZScsICdjbGVhcicsICdjbGljaycgXSxcblxuICBzZXR1cCAocHJvcHMsIHsgc2xvdHMsIGVtaXQgfSkge1xuICAgIGNvbnN0IGhhc0FjdGl2ZVZhbHVlID0gY29tcHV0ZWQoKCkgPT5cbiAgICAgIHByb3BzLm9wdGlvbnMuZmluZChvcHQgPT4gb3B0LnZhbHVlID09PSBwcm9wcy5tb2RlbFZhbHVlKSAhPT0gdm9pZCAwXG4gICAgKVxuXG4gICAgY29uc3QgZm9ybUF0dHJzID0gY29tcHV0ZWQoKCkgPT4gKHtcbiAgICAgIHR5cGU6ICdoaWRkZW4nLFxuICAgICAgbmFtZTogcHJvcHMubmFtZSxcbiAgICAgIHZhbHVlOiBwcm9wcy5tb2RlbFZhbHVlXG4gICAgfSkpXG5cbiAgICBjb25zdCBpbmplY3RGb3JtSW5wdXQgPSB1c2VGb3JtSW5qZWN0KGZvcm1BdHRycylcblxuICAgIGNvbnN0IGJ0bkRlc2lnbkF0dHIgPSBjb21wdXRlZCgoKSA9PiBnZXRCdG5EZXNpZ25BdHRyKHByb3BzKSlcblxuICAgIGNvbnN0IGJ0bk9wdGlvbkRlc2lnbiA9IGNvbXB1dGVkKCgpID0+ICh7XG4gICAgICByb3VuZGVkOiBwcm9wcy5yb3VuZGVkLFxuICAgICAgZGVuc2U6IHByb3BzLmRlbnNlLFxuICAgICAgLi4uYnRuRGVzaWduQXR0ci52YWx1ZVxuICAgIH0pKVxuXG4gICAgY29uc3QgYnRuT3B0aW9ucyA9IGNvbXB1dGVkKCgpID0+IHByb3BzLm9wdGlvbnMubWFwKChpdGVtLCBpKSA9PiB7XG4gICAgICBjb25zdCB7IGF0dHJzLCB2YWx1ZSwgc2xvdCwgLi4ub3B0IH0gPSBpdGVtXG5cbiAgICAgIHJldHVybiB7XG4gICAgICAgIHNsb3QsXG4gICAgICAgIHByb3BzOiB7XG4gICAgICAgICAga2V5OiBpLFxuXG4gICAgICAgICAgJ2FyaWEtcHJlc3NlZCc6IHZhbHVlID09PSBwcm9wcy5tb2RlbFZhbHVlID8gJ3RydWUnIDogJ2ZhbHNlJyxcbiAgICAgICAgICAuLi5hdHRycyxcbiAgICAgICAgICAuLi5vcHQsXG4gICAgICAgICAgLi4uYnRuT3B0aW9uRGVzaWduLnZhbHVlLFxuXG4gICAgICAgICAgZGlzYWJsZTogcHJvcHMuZGlzYWJsZSA9PT0gdHJ1ZSB8fCBvcHQuZGlzYWJsZSA9PT0gdHJ1ZSxcblxuICAgICAgICAgIC8vIE9wdGlvbnMgdGhhdCBjb21lIGZyb20gdGhlIGJ1dHRvbiBzcGVjaWZpYyBvcHRpb25zIGZpcnN0LCB0aGVuIGZyb20gZ2VuZXJhbCBwcm9wc1xuICAgICAgICAgIGNvbG9yOiB2YWx1ZSA9PT0gcHJvcHMubW9kZWxWYWx1ZVxuICAgICAgICAgICAgPyBtZXJnZU9wdChvcHQsICd0b2dnbGVDb2xvcicpXG4gICAgICAgICAgICA6IG1lcmdlT3B0KG9wdCwgJ2NvbG9yJyksXG4gICAgICAgICAgdGV4dENvbG9yOiB2YWx1ZSA9PT0gcHJvcHMubW9kZWxWYWx1ZVxuICAgICAgICAgICAgPyBtZXJnZU9wdChvcHQsICd0b2dnbGVUZXh0Q29sb3InKVxuICAgICAgICAgICAgOiBtZXJnZU9wdChvcHQsICd0ZXh0Q29sb3InKSxcbiAgICAgICAgICBub0NhcHM6IG1lcmdlT3B0KG9wdCwgJ25vQ2FwcycpID09PSB0cnVlLFxuICAgICAgICAgIG5vV3JhcDogbWVyZ2VPcHQob3B0LCAnbm9XcmFwJykgPT09IHRydWUsXG5cbiAgICAgICAgICBzaXplOiBtZXJnZU9wdChvcHQsICdzaXplJyksXG4gICAgICAgICAgcGFkZGluZzogbWVyZ2VPcHQob3B0LCAncGFkZGluZycpLFxuICAgICAgICAgIHJpcHBsZTogbWVyZ2VPcHQob3B0LCAncmlwcGxlJyksXG4gICAgICAgICAgc3RhY2s6IG1lcmdlT3B0KG9wdCwgJ3N0YWNrJykgPT09IHRydWUsXG4gICAgICAgICAgc3RyZXRjaDogbWVyZ2VPcHQob3B0LCAnc3RyZXRjaCcpID09PSB0cnVlLFxuXG4gICAgICAgICAgb25DbGljayAoZSkgeyBzZXQodmFsdWUsIGl0ZW0sIGUpIH1cbiAgICAgICAgfVxuICAgICAgfVxuICAgIH0pKVxuXG4gICAgZnVuY3Rpb24gc2V0ICh2YWx1ZSwgb3B0LCBlKSB7XG4gICAgICBpZiAocHJvcHMucmVhZG9ubHkgIT09IHRydWUpIHtcbiAgICAgICAgaWYgKHByb3BzLm1vZGVsVmFsdWUgPT09IHZhbHVlKSB7XG4gICAgICAgICAgaWYgKHByb3BzLmNsZWFyYWJsZSA9PT0gdHJ1ZSkge1xuICAgICAgICAgICAgZW1pdCgndXBkYXRlOm1vZGVsVmFsdWUnLCBudWxsLCBudWxsKVxuICAgICAgICAgICAgZW1pdCgnY2xlYXInKVxuICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgICBlbHNlIHtcbiAgICAgICAgICBlbWl0KCd1cGRhdGU6bW9kZWxWYWx1ZScsIHZhbHVlLCBvcHQpXG4gICAgICAgIH1cblxuICAgICAgICBlbWl0KCdjbGljaycsIGUpXG4gICAgICB9XG4gICAgfVxuXG4gICAgZnVuY3Rpb24gbWVyZ2VPcHQgKG9wdCwga2V5KSB7XG4gICAgICByZXR1cm4gb3B0WyBrZXkgXSA9PT0gdm9pZCAwID8gcHJvcHNbIGtleSBdIDogb3B0WyBrZXkgXVxuICAgIH1cblxuICAgIGZ1bmN0aW9uIGdldENvbnRlbnQgKCkge1xuICAgICAgY29uc3QgY2hpbGQgPSBidG5PcHRpb25zLnZhbHVlLm1hcChvcHQgPT4ge1xuICAgICAgICByZXR1cm4gaChRQnRuLCBvcHQucHJvcHMsIG9wdC5zbG90ICE9PSB2b2lkIDAgPyBzbG90c1sgb3B0LnNsb3QgXSA6IHZvaWQgMClcbiAgICAgIH0pXG5cbiAgICAgIGlmIChwcm9wcy5uYW1lICE9PSB2b2lkIDAgJiYgcHJvcHMuZGlzYWJsZSAhPT0gdHJ1ZSAmJiBoYXNBY3RpdmVWYWx1ZS52YWx1ZSA9PT0gdHJ1ZSkge1xuICAgICAgICBpbmplY3RGb3JtSW5wdXQoY2hpbGQsICdwdXNoJylcbiAgICAgIH1cblxuICAgICAgcmV0dXJuIGhNZXJnZVNsb3Qoc2xvdHMuZGVmYXVsdCwgY2hpbGQpXG4gICAgfVxuXG4gICAgcmV0dXJuICgpID0+IGgoUUJ0bkdyb3VwLCB7XG4gICAgICBjbGFzczogJ3EtYnRuLXRvZ2dsZScsXG4gICAgICAuLi5idG5EZXNpZ25BdHRyLnZhbHVlLFxuICAgICAgcm91bmRlZDogcHJvcHMucm91bmRlZCxcbiAgICAgIHN0cmV0Y2g6IHByb3BzLnN0cmV0Y2gsXG4gICAgICBnbG9zc3k6IHByb3BzLmdsb3NzeSxcbiAgICAgIHNwcmVhZDogcHJvcHMuc3ByZWFkXG4gICAgfSwgZ2V0Q29udGVudClcbiAgfVxufSlcbiIsIjxzY3JpcHQgc2V0dXA+XG5pbXBvcnQge3JlZiwgY29tcHV0ZWQsIG9uTW91bnRlZH0gZnJvbSAndnVlJ1xuaW1wb3J0IHt1c2VPcmRlclN0b3JlfSBmcm9tIFwic3RvcmVzL29yZGVyLmpzXCI7XG5pbXBvcnQge2FwaX0gZnJvbSBcImJvb3QvYXhpb3MuanNcIjtcbmltcG9ydCB7dXNlU3BlY2lhbGl6YXRpb25zU3RvcmV9IGZyb20gXCJzdG9yZXMvc3BlY2lhbGl6YXRpb25zLmpzXCI7XG5pbXBvcnQge3VzZVJvdXRlcn0gZnJvbSBcInZ1ZS1yb3V0ZXJcIjtcbmltcG9ydCBEZWxldGVDb25maXJtUGFnZSBmcm9tIFwicGFnZXMvRGVsZXRlQ29uZmlybVBhZ2UudnVlXCI7XG5cbmNvbnN0IGRlbGV0ZUNvbmZpcm1QYWdlID0gcmVmKG51bGwpXG5cbmltcG9ydCB7dXNlUXVhc2FyfSBmcm9tIFwicXVhc2FyXCI7XG4vL2ltcG9ydCB7ZGF0YX0gZnJvbSBcImF1dG9wcmVmaXhlclwiO1xuXG5jb25zdCAkcSA9IHVzZVF1YXNhcigpXG5cbmNvbnN0IHJvdXRlciA9IHVzZVJvdXRlcigpXG5jb25zdCBzcGVjaWFsaXphdGlvbnNTdG9yZSA9IHVzZVNwZWNpYWxpemF0aW9uc1N0b3JlKClcbmNvbnN0IHNlbGVjdGVkU3BlY2lhbGl6YXRpb25JZCA9IHNwZWNpYWxpemF0aW9uc1N0b3JlLmdldFNlbGVjdGVkU3BlY2lhbGl6YXRpb24uaWRcblxuY29uc3Qgb3JkZXJTdG9yZSA9IHVzZU9yZGVyU3RvcmUoKVxuXG5jb25zdCBvcmRlclN0YXR1cyA9IHJlZignd2FpdGluZycpO1xuY29uc3QgcGFpZCA9IHJlZihmYWxzZSlcblxuY29uc3Qgb3JkZXIgPSByZWYobnVsbClcbmNvbnN0IHNlcnZpY2VzID0gcmVmKFtdKVxuY29uc3QgbWF0ZXJpYWxzID1yZWYoW10pXG5cbmNvbnN0IHN0b3JlUHJvZHVjdHMgPSByZWYoW10pXG5jb25zdCBwcm9kdWN0cyA9IHJlZihbXSlcbmNvbnN0IHByb2R1Y3RDYXRlZ29yaWVzID0gcmVmKFtdKVxuY29uc3Qgc2VsZWN0ZWRTdG9yZVByb2R1Y3QgPSByZWYobnVsbClcbmNvbnN0IHNlbGVjdGVkUHJvZHVjdENhdGVnb3J5ID0gcmVmKG51bGwpXG5cblxuLy9jb25zdCBjbGllbnROYW1lID0gcmVmKG51bGwpXG5jb25zdCBjbGllbnQgPSByZWYoe1xuICBpZDpudWxsLFxuICBuYW1lOiBudWxsLFxuICBwaG9uZTogbnVsbFxufSlcbmNvbnN0IGNsaWVudElkID0gcmVmKG51bGwpXG5jb25zdCBtb2RlbElkID0gcmVmKG51bGwpXG5jb25zdCBjb21tZW50cyA9IHJlZihudWxsKVxuXG5jb25zdCBjbGllbnRzID0gcmVmKG51bGwpXG5jb25zdCBtb2RlbHMgPSByZWYobnVsbClcblxuY29uc3QgbW9kZWwgPSByZWYoe2lkOiBudWxsLCBuYW1lOiBudWxsfSlcblxuY29uc3Qgc2VsZWN0ZWRTZXJ2aWNlQ2F0ZWdvcnkgPSByZWYobnVsbClcblxuY29uc3Qgc2VydmljZUNhdGVnb3JpZXMgPSByZWYobnVsbClcbmNvbnN0IHNlcnZpY2VzQnlDYXRlZ29yeSA9IHJlZihudWxsKVxuXG5jb25zdCB0YWIgPSByZWYoJ2FsbCcpXG5cbmNvbnN0IGVkaXRNb2RlID0gcmVmKGZhbHNlKVxuY29uc3QgaXNOZXdPcmRlciA9IGNvbXB1dGVkKCgpID0+ICFvcmRlci52YWx1ZT8uaWQpXG5cbmNvbnN0IHNob3dBZGROZXdNYXRlcmlhbERpYWxvZyA9IHJlZihmYWxzZSlcbmNvbnN0IHNob3dBZGROZXdTZXJ2aWNlRGlhbG9nID0gcmVmKGZhbHNlKVxuY29uc3Qgc2hvd0FkZE5ld0NsaWVudERpYWxvZyA9IHJlZihmYWxzZSlcbmNvbnN0IHNob3dBZGROZXdNb2RlbERpYWxvZyA9IHJlZihmYWxzZSlcbmNvbnN0IHNob3dBZGRQcm9kdWN0RnJvbVN0b3JlRGlhbG9nID0gcmVmKGZhbHNlKVxuXG5jb25zdCBuZXdNYXRlcmlhbCA9IHJlZih7XG4gIG5hbWU6ICcnLFxuICBwcmljZTogMCxcbiAgYW1vdW50OiAwXG59KVxuXG5jb25zdCBuZXdTZXJ2aWNlID0gcmVmKHtcbiAgbmFtZTogJycsXG4gIHByaWNlOiAwLFxufSlcblxuY29uc3QgbmV3Q2xpZW50ID0gcmVmKHtcbiAgbmFtZTogJycsXG4gIHBob25lOiAnJyxcbn0pXG5cbmNvbnN0IG5ld01vZGVsID0gcmVmKHtcbiAgbmFtZTogJycsXG59KVxuXG5jb25zdCBnZXRTZXJ2aWNlcyA9IGFzeW5jICgpID0+IHtcbiAgdHJ5IHtcbiAgICBjb25zdCBvcmRlcklkID0gb3JkZXIudmFsdWUuaWRcbiAgICBjb25zdCByZXNwb25zZSA9IGF3YWl0IGFwaS5nZXQoYC9nZXRfc2VydmljZXMvJHtvcmRlcklkfWApXG4gICAgc2VydmljZXMudmFsdWUgPSByZXNwb25zZS5kYXRhXG4gICAgY29uc29sZS5sb2coJ3NlcnZpY2VzOiAnLCBzZXJ2aWNlcy52YWx1ZSApXG4gIH0gIGNhdGNoIChlcnIpIHtcbiAgICBjb25zb2xlLmVycm9yKCfQvtGI0LjQsdC60LAg0LfQsNCz0YDRg9C30LrQuCDRgdC10YDQstC40YHQvtCyJywgZXJyKVxuICB9XG59XG5cbmNvbnN0IHN3aXTRgWhQYWlkU3RhdHVzID0gYXN5bmMgKCkgPT4ge1xuICBjb25zdCBpZCA9IG9yZGVyLnZhbHVlLmlkXG4gIHRyeSB7XG4gICAgY29uc3QgcmVzcG9uc2UgPSBhd2FpdCBhcGkucHV0KGBzd2l0Y2hfcGFpZF9zdGF0dXMvJHtpZH1gKVxuICAgIGlmIChyZXNwb25zZS5zdGF0dXMgPT09IDIwMCkge1xuICAgICAgcGFpZC52YWx1ZSA9ICFwYWlkLnZhbHVlXG4gICAgICBjb25zb2xlLmxvZygn0YHRgtCw0YLRg9GBINC+0L/Qu9Cw0YLRiyDQuNC30LzQtdC90LXQvScpXG4gICAgfVxuICB9IGNhdGNoIChlcnIpIHtcbiAgICBjb25zb2xlLmxvZygn0L7RiNC40LHQutCwINGB0LzQtdC90Ysg0YHRgtCw0YLRg9GB0LA6ICcsIGVycilcbiAgfVxufVxuXG5jb25zdCB1cGRhdGVPcmRlclN0YXR1cyA9IGFzeW5jICgpID0+IHtcbiAgdHJ5IHtcbiAgICBjb25zdCBpZCA9IG9yZGVyLnZhbHVlLmlkXG4gICAgY29uc3QgcmVzcG9uc2UgPSBhd2FpdCBhcGkucHV0KGAvdXBkYXRlX29yZGVyX3N0YXR1cy8ke2lkfWAsIHtcbiAgICAgIHN0YXR1czogb3JkZXJTdGF0dXMudmFsdWVcbiAgICB9KVxuICAgIGlmIChyZXNwb25zZS5zdGF0dXMgPT09IDIwMCkge1xuICAgICAgY29uc29sZS5sb2coJ9GB0YLQsNGC0YPRgSDQvtGA0LTQtdGA0LAg0LjQt9C80LXQvdC10L0g0L3QsDogJywgb3JkZXJTdGF0dXMudmFsdWUpXG4gICAgfSBlbHNlIHtcbiAgICAgIGNvbnNvbGUuZXJyb3IoJ9C+0YjQuNCx0LrQsCDQvtCx0L3QvtCy0LvQtdC90LjRjyDRgdGC0LDRgtGD0YHQsCcsIHJlc3BvbnNlKX1cbiAgfSBjYXRjaCAoZXJyKSB7XG4gICAgY29uc29sZS5lcnJvcign0L7RiNC40LHQutCwINC30LDQv9GA0L7RgdCwOiAnLCBlcnIpXG4gIH1cbn1cblxuY29uc3QgZ2V0TWF0ZXJpYWxzQnlPcmRlciA9IGFzeW5jICgpID0+IHtcbiAgdHJ5IHtcbiAgICBjb25zdCBvcmRlcklkID0gb3JkZXIudmFsdWUuaWRcbiAgICBjb25zdCByZXNwb25zZSA9IGF3YWl0IGFwaS5nZXQoYC9nZXRfbWF0ZXJpYWxzX2J5X29yZGVyLyR7b3JkZXJJZH1gKVxuXG4gICAgbWF0ZXJpYWxzLnZhbHVlID0gW11cbiAgICBwcm9kdWN0cy52YWx1ZSA9IFtdXG5cbiAgICByZXNwb25zZS5kYXRhLmZvckVhY2goaXRlbSA9PiB7XG4gICAgICBpZiAoaXRlbS5wcm9kdWN0X2lkKSB7XG4gICAgICAgIHByb2R1Y3RzLnZhbHVlLnB1c2goey4uLml0ZW19KVxuICAgICAgfSBlbHNlIHtcbiAgICAgICAgbWF0ZXJpYWxzLnZhbHVlLnB1c2goey4uLml0ZW19KVxuICAgICAgfVxuICAgIH0pXG4gICAgY29uc29sZS5sb2coJ21hdGVyaWFsczogJywgbWF0ZXJpYWxzLnZhbHVlKVxuICAgIGNvbnNvbGUubG9nKCdwcm9kdWN0czogJywgcHJvZHVjdHMudmFsdWUpXG4gIH0gY2F0Y2ggKGVycikge1xuICAgIGNvbnNvbGUuZXJyb3IoJ9C+0YjQuNCx0LrQsCDQv9C+0LvRg9GH0LXQvdC40Y8g0LzQsNGC0LXRgNC40LDQu9C+0LI6ICcsIGVycilcbiAgfVxufVxuXG5jb25zdCBnZXRTZXJ2aWNlQ2F0ZWdvcmllcyA9IGFzeW5jICgpID0+IHtcbiAgY29uc3Qgc3BlY2lhbGl6YXRpb25TdG9yZSA9IHVzZVNwZWNpYWxpemF0aW9uc1N0b3JlKClcbiAgdHJ5IHtcbiAgICBjb25zdCBzcGVjaWFsaXphdGlvbklkID0gc3BlY2lhbGl6YXRpb25TdG9yZS5nZXRTZWxlY3RlZFNwZWNpYWxpemF0aW9uLmlkXG4gICAgY29uc29sZS5sb2coJ9GB0L/QtdGG0LjQsNC70LjQt9Cw0YbQuNGPOiAnLCBzcGVjaWFsaXphdGlvbklkKVxuICAgIGNvbnN0IHJlc3BvbnNlID0gYXdhaXQgYXBpLmdldChgL2dldF9jYXRlZ29yaWVzLyR7c3BlY2lhbGl6YXRpb25JZH1gKVxuICAgIHNlcnZpY2VDYXRlZ29yaWVzLnZhbHVlID0gcmVzcG9uc2UuZGF0YVxuICAgIGNvbnNvbGUubG9nKCfRgdC10YDQstC40YEg0LrQsNGC0LXQs9C+0YDQuNC4OiAnLCBzZXJ2aWNlQ2F0ZWdvcmllcy52YWx1ZSlcbiAgfSBjYXRjaCAoZXJyKSB7XG4gICAgY29uc29sZS5lcnJvcign0L7RiNC40LHQutCwINC30LDQs9GA0YPQt9C60Lgg0YHQtdGA0LLQuNGBINC60LDRgtC10LPQvtGA0LjQuScsIGVycilcbiAgfVxufVxuXG5jb25zdCBnZXRTZXJ2aWNlc0J5Q2F0ZWdvcnkgPSBhc3luYyAoY2F0ZWdvcnlJZCkgPT4ge1xuICBjb25zb2xlLmxvZygn0L/QvtC00LPRgNGD0LbQsNC10Lwg0YHQtdGA0LLQuNGB0Ysg0LrQsNGC0LXQs9C+0YDQuNC4OiAnLCBjYXRlZ29yeUlkKVxuICBjb25zb2xlLmxvZygnc2VsZWN0ZWRTZXJ2aWNlQ2F0ZWdvcnk6ICcsIHNlbGVjdGVkU2VydmljZUNhdGVnb3J5KVxuICB0cnkge1xuICAgIGNvbnN0IHJlc3BvbnNlID0gYXdhaXQgYXBpLmdldChgL2dldF9zZXJ2aWNlLyR7Y2F0ZWdvcnlJZH1gKVxuICAgIHNlcnZpY2VzQnlDYXRlZ29yeS52YWx1ZSA9IHJlc3BvbnNlLmRhdGFcbiAgICBjb25zb2xlLmxvZygn0L/QvtC00LPRgNGD0LbQtdC90Ysg0YHQtdGA0LLQuNGB0Ysg0LrQsNGC0LXQs9C+0YDQuNC4OiAnLCBzZXJ2aWNlc0J5Q2F0ZWdvcnkudmFsdWUpXG4gIH0gY2F0Y2ggKGVycikge1xuICAgIGNvbnNvbGUuZXJyb3IoJ9C+0YjQuNCx0LrQsCDQt9Cw0LPRgNGD0LfQutC1INGB0LXRgNCy0LjRgdC+0LIg0LTQsNC90L3QvtC5INC60LDRgtC10LPQvtGA0LjQuDogJywgY2F0ZWdvcnlJZCAsIGVyciApXG4gIH1cbn1cblxuY29uc3QgZGVsZXRlT3JkZXIgPSBhc3luYyAoKSA9PiB7XG5cbiAgZGVsZXRlQ29uZmlybVBhZ2UudmFsdWUub3BlbihcbiAgICAn0J/QvtC00YLQstC10YDQtNC40YLQtSDRg9C00LDQu9C10L3QuNC1JyxcbiAgICBg0JLRiyDRg9Cy0LXRgNC10L3Riywg0YfRgtC+INGF0L7RgtC40YLQtSDRg9C00LDQu9C40YLRjCDQvtGA0LTQtdGAIFwiJHtvcmRlci52YWx1ZS5pZH1cIj9gLFxuICAgIGFzeW5jICgpID0+IHtcbiAgICAgIHRyeSB7XG4gICAgICAgIGNvbnN0IHJlc3BvbnNlID0gYXdhaXQgYXBpLmRlbGV0ZShgL2RlbGV0ZV9vcmRlci8ke29yZGVyLnZhbHVlLmlkfWApXG4gICAgICAgIGlmIChyZXNwb25zZS5zdGF0dXMgPT09IDIwMCkge1xuICAgICAgICAgICRxLm5vdGlmeSh7XG4gICAgICAgICAgICB0eXBlOiAncG9zaXRpdmUnLFxuICAgICAgICAgICAgbWVzc2FnZTogYNC+0YDQtNC10YAgJHtvcmRlci52YWx1ZS5pZH0g0YPQtNCw0LvQtdC9YCxcbiAgICAgICAgICAgIHBvc2l0aW9uOiAndG9wJyxcbiAgICAgICAgICAgIHRpbWVvdXQ6IDUwMFxuICAgICAgICAgIH0pXG4gICAgICAgIH1cbiAgICAgIH0gY2F0Y2ggKGVycikge1xuICAgICAgICBjb25zb2xlLmVycm9yKFwi0J7RiNC40LHQutCwINGD0LTQsNC70LXQvdC40Y8g0L7RgNC00LXRgNCwXCIsIGVycik7XG4gICAgICB9IGZpbmFsbHkge1xuICAgICAgICByb3V0ZXIuYmFjaygpXG4gICAgICB9XG4gICAgfVxuICApO1xufVxuXG5vbk1vdW50ZWQoKCkgPT4ge1xuICBpZihvcmRlclN0b3JlLmN1cnJlbnRPcmRlcil7XG4gICAgb3JkZXIudmFsdWUgPSBvcmRlclN0b3JlLmN1cnJlbnRPcmRlclxuICAgIGNvbnNvbGUubG9nKCfQvtGA0LTQtdGAOiAnLCBvcmRlci52YWx1ZSlcbiAgICBwYWlkLnZhbHVlID0gb3JkZXIudmFsdWUucGFpZFxuICAgIG9yZGVyU3RhdHVzLnZhbHVlID0gb3JkZXIudmFsdWUuc3RhdHVzXG4gICAgY2xpZW50LnZhbHVlLm5hbWUgPSBvcmRlci52YWx1ZS5jbGllbnRfbmFtZVxuICAgIGNsaWVudC52YWx1ZS5pZCA9IG9yZGVyLnZhbHVlLmNsaWVudF9pZFxuICAgIGNsaWVudElkLnZhbHVlID0gb3JkZXIudmFsdWUuY2xpZW50X2lkXG4gICAgbW9kZWxJZC52YWx1ZSA9IG9yZGVyLnZhbHVlLm1vZGVsX2lkXG4gICAgbW9kZWwudmFsdWUubmFtZSA9IG9yZGVyLnZhbHVlLm1vZGVsX25hbWVcbiAgICBpZiAob3JkZXIudmFsdWUubW9kZWxfaWQpe1xuICAgICAgbW9kZWxJZC52YWx1ZSA9IG9yZGVyLnZhbHVlLm1vZGVsX2lkXG4gICAgfVxuICAgIGlmIChvcmRlci52YWx1ZS5jb21tZW50cykge1xuICAgICAgY29tbWVudHMudmFsdWUgPSBvcmRlci52YWx1ZS5jb21tZW50c1xuICAgIH1cbiAgICBnZXRTZXJ2aWNlcygpXG4gICAgZ2V0TWF0ZXJpYWxzQnlPcmRlcigpXG4gIH0gZWxzZSB7XG4gICAgY29uc29sZS5sb2coJ9GA0LXQttC40Lwg0L3QvtCy0L7Qs9C+INC+0YDQtNC10YDQsCcpXG4gICAgb3JkZXIudmFsdWUgPSB7XG4gICAgICBzdGF0dXM6ICd3YWl0aW5nJyxcbiAgICAgIHBhaWQ6IGZhbHNlLFxuICAgICAgY2xpZW50SWQ6IG51bGwsXG4gICAgICBtb2RlbElkOiBudWxsLFxuICAgICAgY29tbWVudHM6ICcnXG4gICAgfVxuICAgIGVkaXRNb2RlLnZhbHVlID0gdHJ1ZVxuICAgIGdldFByb2R1Y3RDYXRlZ29yaWVzKClcbiAgfVxuICBnZXRDbGllbnRzKClcbiAgZ2V0TW9kZWxzKClcbiAgZ2V0U2VydmljZUNhdGVnb3JpZXMoKVxufSlcblxuY29uc3QgZ2V0Q2xpZW50cyA9IGFzeW5jICgpID0+IHtcbiAgdHJ5IHtcbiAgICBjb25zdCByZXNwb25zZSA9IGF3YWl0IGFwaS5nZXQoYC9nZXRfY2xpZW50cy8ke3NlbGVjdGVkU3BlY2lhbGl6YXRpb25JZH1gKVxuICAgIGNsaWVudHMudmFsdWUgPSByZXNwb25zZS5kYXRhXG4gICAgY29uc29sZS5sb2coJ2NsaWVudHM6ICcsIGNsaWVudHMpXG4gIH0gY2F0Y2ggKGVycikge1xuICAgIGNvbnNvbGUuZXJyb3IoJ9C+0YjQuNCx0LrQsCDQv9C+0LvRg9GH0LXQvdC40Y8g0LrQu9C40LXQvdGC0L7QsjogJywgZXJyKVxuICB9XG59XG5cbmNvbnN0IGdldE1vZGVscyA9IGFzeW5jICgpID0+IHtcbiAgdHJ5IHtcbiAgICBjb25zdCByZXNwb25zZSA9IGF3YWl0IGFwaS5nZXQoYC9nZXRfZXF1aXBtZW50X21vZGVscy8ke3NlbGVjdGVkU3BlY2lhbGl6YXRpb25JZH1gKVxuICAgIG1vZGVscy52YWx1ZSA9IHJlc3BvbnNlLmRhdGFcbiAgICBjb25zb2xlLmxvZygnbW9kZWxzOiAnLCBtb2RlbHMpXG4gIH0gY2F0Y2ggKGVycikge1xuICAgIGNvbnNvbGUuZXJyb3IoJ9C+0YjQuNCx0LrQsCDQv9C+0LvRg9GH0LXQvdC40Y8g0LzQvtC00LXQu9C10Lk6ICcsIGVycilcbiAgfVxufVxuXG5jb25zdCBnZXRQcm9kdWN0Q2F0ZWdvcmllcyA9IGFzeW5jICgpID0+IHtcbiAgdHJ5IHtcbiAgICBjb25zdCByZXNwb25zZSA9IGF3YWl0IGFwaS5nZXQoYC9nZXRfcHJvZHVjdF9jYXRlZ29yaWVzLyR7c2VsZWN0ZWRTcGVjaWFsaXphdGlvbklkfWApXG4gICAgcHJvZHVjdENhdGVnb3JpZXMudmFsdWUgPSByZXNwb25zZS5kYXRhXG4gICAgY29uc29sZS5sb2coJ3Byb2R1Y3RDYXRlZ29yaWVzOiAnLCBwcm9kdWN0Q2F0ZWdvcmllcy52YWx1ZSlcbiAgfSBjYXRjaCAoZXJyKXtcbiAgICBjb25zb2xlLmVycm9yKGVycilcbiAgfVxufVxuXG5jb25zdCBnZXRQcm9kdWN0c0J5Q2F0ZWdvcnkgPSBhc3luYyAoc2VsZWN0ZWRQcm9kdWN0Q2F0ZWdvcnkpID0+IHtcbiAgc2VsZWN0ZWRTdG9yZVByb2R1Y3QudmFsdWUgPSBudWxsXG4gIHRyeSB7XG4gICAgY29uc3QgcmVzcG9uc2UgPSBhd2FpdCBhcGkuZ2V0KGAvZ2V0X3Byb2R1Y3RzLyR7c2VsZWN0ZWRQcm9kdWN0Q2F0ZWdvcnkuaWR9YClcbiAgICBzdG9yZVByb2R1Y3RzLnZhbHVlID0gcmVzcG9uc2UuZGF0YVxuICAgIGNvbnNvbGUubG9nKCdwcm9kdWN0czogJywgcHJvZHVjdHMudmFsdWUpXG4gIH0gY2F0Y2ggKGVycil7XG4gICAgY29uc29sZS5lcnJvcihlcnIpXG4gIH1cbn1cblxuY29uc3QgYWN0aXZlRWRpdE1vZGUgPSBhc3luYyAoKSA9PiB7XG4gIGNvbnNvbGUubG9nKCfQsNC60YLQuNCy0LXQvSDRgNC10LbQuNC8INGA0LXQtNCw0LrRgtC+0YDQsCDQvtGA0LTQtdGA0LAnKVxuICBhd2FpdCBnZXRDbGllbnRzKClcbiAgY29uc29sZS5sb2coJ2NsaWVudF9pZDogJywgb3JkZXIudmFsdWUuY2xpZW50X2lkKVxuICBjbGllbnQudmFsdWUuaWQgPSBvcmRlci52YWx1ZS5jbGllbnRfaWRcbiAgYXdhaXQgZ2V0TW9kZWxzKClcbiAgY29uc29sZS5sb2coJ21vZGVsX2lkOiAnLCBvcmRlci52YWx1ZS5tb2RlbF9pZClcbiAgbW9kZWwudmFsdWUuaWQgPSBvcmRlci52YWx1ZS5tb2RlbF9pZFxuICBlZGl0TW9kZS52YWx1ZSA9IHRydWVcbiAgYXdhaXQgZ2V0UHJvZHVjdENhdGVnb3JpZXMoKVxufVxuXG5jb25zdCBzYXZlT3JkZXIgPSBhc3luYyAoKSA9PiB7XG4gIHRyeSB7XG4gICAgaWYoaXNOZXdPcmRlci52YWx1ZSl7XG4gICAgICBhd2FpdCBjcmVhdGVPcmRlcigpXG4gICAgfSBlbHNlIHtcbiAgICAgIGF3YWl0IHVwZGF0ZU9yZGVyKClcbiAgICB9XG4gIH0gY2F0Y2ggKGVycikge1xuICAgICRxLm5vdGlmeSh7XG4gICAgICB0eXBlOiAnbmVnYXRpdmUnLFxuICAgICAgbWVzc2FnZTogJ9C+0YjQuNCx0LrQsCDRgdC+0YXRgNCw0L3QtdC90LjRjyDQt9Cw0LrQsNC30LAnXG4gICAgfSlcbiAgICBjb25zb2xlLmVycm9yKCfQvtGI0LjQsdC60LAg0YHQvtGF0YDQsNC90LXQvdC40Y8g0LfQsNC60LDQt9CwOiAnLCBlcnIpXG4gIH1cblxufVxuXG5jb25zdCBjcmVhdGVPcmRlciA9IGFzeW5jICgpID0+IHtcbiAgY29uc3QgdG9rZW4gPSBsb2NhbFN0b3JhZ2UuZ2V0SXRlbSgnYXV0aFRva2VuJylcbiAgY29uc29sZS5sb2coJ3Rva2VuOiAnLCB0b2tlbilcbiAgY29uc29sZS5sb2coJ9GB0L7RhdGA0L7QvdGP0LXQvCDQvdC+0LLRi9C5INC+0YDQtNC10YAnKVxuICBjb25zb2xlLmxvZygnc2VydmljZXMnLCBzZXJ2aWNlcy52YWx1ZS5tYXAoc2VydmljZSA9PiBzZXJ2aWNlLmlkKSlcbiAgY29uc29sZS5sb2coJ2FkZGVkTWF0ZXJpYWxzOiAnLCBtYXRlcmlhbHMudmFsdWUpXG4gIHRyeSB7XG4gICAgY29uc3QgcmVzcG9uc2UgPSBhd2FpdCBhcGkucG9zdChgL3NhdmVfb3JkZXJgLCB7XG4gICAgICBjbGllbnRJZDogY2xpZW50LnZhbHVlLmlkLFxuICAgICAgbW9kZWxJZDogbW9kZWwudmFsdWUuaWQsXG4gICAgICBzcGVjaWFsaXphdGlvbklkOiBzZWxlY3RlZFNwZWNpYWxpemF0aW9uSWQsXG4gICAgICB0b3RhbEFtb3VudDogdG90YWxTdW1Qcm9kdWN0cy52YWx1ZSArIHRvdGFsU3VtTWF0ZXJpYWxzLnZhbHVlICsgdG90YWxTdW1TZXJ2aWNlcy52YWx1ZSxcbiAgICAgIGFkZGVkTWF0ZXJpYWxzOiBtYXRlcmlhbHMudmFsdWUsXG4gICAgICBhZGRlZFByb2R1Y3RzOiBwcm9kdWN0cy52YWx1ZSxcbiAgICAgIHNlcnZpY2VzSWQ6IHNlcnZpY2VzLnZhbHVlLm1hcChzZXJ2aWNlID0+IHNlcnZpY2UuaWQpLFxuICAgICAgY29tbWVudHM6IGNvbW1lbnRzLnZhbHVlLFxuICAgICAgcGFpZDogcGFpZC52YWx1ZSxcbiAgICAgIHVzZXJPcmRlck51bWJlcjogJycsXG4gICAgICBzdGF0dXM6IG9yZGVyU3RhdHVzLnZhbHVlLFxuICAgICAgbWF0ZXJpYWxzOiAnJ1xuICAgIH0sIHtcbiAgICAgIGhlYWRlcnM6IHtcbiAgICAgICAgQXV0aG9yaXphdGlvbjogYEJlYXJlciAke3Rva2VufWBcbiAgICAgIH1cbiAgICB9KVxuICAgIGNvbnNvbGUubG9nKCdyZXNwb25zZTogJywgcmVzcG9uc2UpXG4gICAgY29uc29sZS5sb2coJ9C/0LXRgNC10YXQvtC0INC90LUg0YDQtdCw0LvQuNC30L7QstCw0L0nKVxuICAgIHJvdXRlci5iYWNrKClcbiAgfSBjYXRjaCAoZXJyKXtcbiAgICBjb25zb2xlLmVycm9yKGVycilcbiAgfVxufVxuXG5jb25zdCB1cGRhdGVPcmRlciA9IGFzeW5jICgpID0+IHtcbiAgY29uc29sZS5sb2coJ9C+0LHQvdC+0LLQu9GP0LXQvCDQvtGA0LTQtdGAINC90LAg0YHQtdGA0LLQtdGA0LUnKVxuICB0cnkge1xuICAgIGNvbnN0IHRvdGFsQW1vdW50ID0gdG90YWxTdW1TZXJ2aWNlcy52YWx1ZSArIHRvdGFsU3VtTWF0ZXJpYWxzLnZhbHVlICsgdG90YWxTdW1Qcm9kdWN0cy52YWx1ZVxuICAgIGNvbnNvbGUubG9nKCdjbGllbnRfaWQ6ICcsIGNsaWVudC52YWx1ZS5pZClcbiAgICBjb25zb2xlLmxvZygnbW9kZWxfaWQnLCBtb2RlbC52YWx1ZS5pZClcbiAgICBjb25zdCByZXNwb25zZSA9IGF3YWl0IGFwaS5wb3N0KCcvdXBkYXRlX29yZGVyJywge1xuICAgICAgaWQ6IG9yZGVyLnZhbHVlLmlkLFxuICAgICAgY2xpZW50X2lkOiBjbGllbnQudmFsdWUuaWQsXG4gICAgICBtb2RlbF9pZDogbW9kZWwudmFsdWUuaWQsXG4gICAgICBzcGVjaWFsaXphdGlvbl9pZDogc2VsZWN0ZWRTcGVjaWFsaXphdGlvbklkLFxuICAgICAgdXNlcl9vcmRlcl9udW1iZXI6ICcnLFxuICAgICAgdG90YWxfYW1vdW50OiB0b3RhbEFtb3VudCxcbiAgICAgIG1hdGVyaWFsczogbWF0ZXJpYWxzLnZhbHVlLFxuICAgICAgcHJvZHVjdHM6IHByb2R1Y3RzLnZhbHVlLFxuICAgICAgY29tbWVudHM6IGNvbW1lbnRzLnZhbHVlLFxuICAgICAgc2VydmljZXM6IHNlcnZpY2VzLnZhbHVlLm1hcChzZXJ2aWNlID0+IHNlcnZpY2UuaWQpLFxuICAgICAgcGFpZDogcGFpZC52YWx1ZVxuICAgIH0pXG4gICAgY29uc29sZS5sb2coJ9C00LDQvdC90YvQtSDQtNC70Y8g0L/QtdGA0LXQtNCw0YfQuDogJywgcmVzcG9uc2UpXG4gICAgY29uc29sZS5sb2coJ9C00LDQvdC90YvQtSDQvtGA0LTQtdGA0LAg0L7QsdC90L7QstC70LXQvdGLJylcbiAgICByb3V0ZXIuYmFjaygpXG4gIH0gY2F0Y2ggKGVycikge1xuICAgIGNvbnNvbGUuZXJyb3IoJ9C+0YjQuNCx0LrQsCDQvtCx0L3QvtCy0LvQtdC90LjRjyDQvtGA0LTQtdGA0LAnLCBlcnIpXG4gIH1cbn1cblxuLy8gY29uc3Qgc2F2ZU9yZGVyID0gKCkgPT4ge1xuLy8gICBlZGl0TW9kZS52YWx1ZSA9IGZhbHNlXG4vLyB9XG5cbi8vINCS0YvRh9C40YHQu9GP0LXQvNCw0Y8g0YHRg9C80LzQsCDQvNCw0YLQtdGA0LjQsNC70L7QslxuY29uc3QgdG90YWxTdW1TZXJ2aWNlcyA9IGNvbXB1dGVkKCgpID0+IHtcbiAgaWYgKCFzZXJ2aWNlcy52YWx1ZSkgcmV0dXJuIDA7XG4gIHJldHVybiBzZXJ2aWNlcy52YWx1ZS5yZWR1Y2UoKHN1bSwgc2VydmljZSkgPT4ge1xuICAgIGNvbnN0IHByaWNlID0gTnVtYmVyKHNlcnZpY2UucHJpY2UpIHx8IDA7XG4gICAgcmV0dXJuIHN1bSArIHByaWNlXG4gIH0sIDApO1xufSlcblxuLy8g0JLRi9GH0LjRgdC70Y/QtdC80LDRjyDRgdGD0LzQvNCwINC80LDRgtC10YDQuNCw0LvQvtCyXG5jb25zdCB0b3RhbFN1bU1hdGVyaWFscyA9IGNvbXB1dGVkKCgpID0+IHtcbiAgaWYgKCFtYXRlcmlhbHMudmFsdWUpIHJldHVybiAwO1xuICByZXR1cm4gbWF0ZXJpYWxzLnZhbHVlLnJlZHVjZSgoc3VtLCBtYXRlcmlhbCkgPT4ge1xuICAgIGNvbnN0IHByaWNlID0gTnVtYmVyKG1hdGVyaWFsLnByaWNlKSB8fCAwO1xuICAgIGNvbnN0IGFtb3VudCA9IE51bWJlcihtYXRlcmlhbC5hbW91bnQpIHx8IDA7XG4gICAgcmV0dXJuIHN1bSArIHByaWNlICogYW1vdW50O1xuICB9LCAwKTtcbn0pXG5cbmNvbnN0IHRvdGFsU3VtUHJvZHVjdHMgPSBjb21wdXRlZCgoKSA9PiB7XG4gIGlmICghcHJvZHVjdHMudmFsdWUpIHJldHVybiAwXG4gIHJldHVybiBwcm9kdWN0cy52YWx1ZS5yZWR1Y2UoKHN1bSwgcHJvZHVjdCkgPT4ge1xuICAgIGNvbnN0IHByaWNlID0gTnVtYmVyKHByb2R1Y3QucHJpY2UpIHx8IDBcbiAgICBjb25zdCBhbW91bnQgPSBOdW1iZXIocHJvZHVjdC5hbW91bnQpIHx8IDBcbiAgICByZXR1cm4gc3VtICsgcHJpY2UgKiBhbW91bnRcbiAgfSwgMClcbn0pXG5cbmNvbnN0IGNvbXB1dGVkVG9nZ2xlQ29sb3IgPSBjb21wdXRlZCgoKSA9PiB7XG4gIHN3aXRjaCAob3JkZXJTdGF0dXMudmFsdWUpIHtcbiAgICBjYXNlICd3YWl0aW5nJzpcbiAgICAgIHJldHVybiAnb3JhbmdlJ1xuICAgIGNhc2UgJ3Byb2Nlc3MnOlxuICAgICAgcmV0dXJuICdyZWQnXG4gICAgY2FzZSAnZG9uZSc6XG4gICAgICByZXR1cm4gJ2dyZWVuJ1xuICAgIGRlZmF1bHQ6XG4gICAgICByZXR1cm4gJ3llbGxvdydcbiAgfVxufSlcblxuY29uc3QgY2xvc2VEaWFsb2cgPSAoKSA9PiB7XG4gIHNob3dBZGROZXdDbGllbnREaWFsb2cudmFsdWUgPSBmYWxzZVxuICBzaG93QWRkTmV3TWF0ZXJpYWxEaWFsb2cudmFsdWUgPSBmYWxzZVxuICBzaG93QWRkTmV3U2VydmljZURpYWxvZy52YWx1ZSA9IGZhbHNlXG4gIHNob3dBZGROZXdNb2RlbERpYWxvZy52YWx1ZSA9IGZhbHNlXG4gIG5ld01hdGVyaWFsLnZhbHVlID0geyBuYW1lOiAnJywgcHJpY2U6IDAsIGFtb3VudDogMCB9XG59XG5cbmNvbnN0IGFkZE5ld1NlcnZpY2UgPSBhc3luYyAoKSA9PiB7XG4gIHRyeSB7XG4gICAgY29uc3QgcmVzcG9uc2UgPSBhd2FpdCBhcGkucG9zdCgnL2FkZF9zZXJ2aWNlJywge1xuICAgICAgc2VydmljZTogbmV3U2VydmljZS52YWx1ZS5uYW1lLFxuICAgICAgcHJpY2U6IG5ld1NlcnZpY2UudmFsdWUucHJpY2UsXG4gICAgICBjYXRlZ29yeV9pZDogc2VsZWN0ZWRTZXJ2aWNlQ2F0ZWdvcnkudmFsdWVcbiAgICB9KVxuICAgIGNvbnNvbGUubG9nKCdyZXNwb25zZTogJywgcmVzcG9uc2UpXG4gICAgYXdhaXQgZ2V0U2VydmljZXNCeUNhdGVnb3J5KHNlbGVjdGVkU2VydmljZUNhdGVnb3J5LnZhbHVlKVxuICAgIGNsb3NlRGlhbG9nKClcbiAgfSBjYXRjaCAoZXJyKSB7XG4gICAgY29uc29sZS5lcnJvcign0L7RiNC40LHQutCwINGB0L7Qt9C00LDQvdC40LUg0L3QvtCy0L7Qs9C+INGB0LXRgNCy0LjRgdCwOiAgJyxlcnIpXG4gIH1cbn1cblxuY29uc3QgYWRkTWF0ZXJpYWwgPSAoKSA9PiB7XG4gIGlmIChcbiAgICBuZXdNYXRlcmlhbC52YWx1ZS5uYW1lLnRyaW0oKSAhPT0gJycgJiZcbiAgICBuZXdNYXRlcmlhbC52YWx1ZS5wcmljZSA+IDAgJiZcbiAgICBuZXdNYXRlcmlhbC52YWx1ZS5hbW91bnQgPiAwXG4gICkge1xuICAgIGNvbnNvbGUubG9nKCfQlNC+0LHQsNCy0LvQtdC90LjQtSDQvdC+0LLQvtCz0L4g0LzQsNGC0LXRgNC40LDQu9CwOicsIHsgLi4ubmV3TWF0ZXJpYWwudmFsdWUgfSlcbiAgICBtYXRlcmlhbHMudmFsdWUucHVzaChuZXdNYXRlcmlhbC52YWx1ZSlcbiAgICBuZXdNYXRlcmlhbC52YWx1ZSA9IHsgbmFtZTogJycsIHByaWNlOiAwLCBhbW91bnQ6IDAgfVxuICAgIHNob3dBZGROZXdNYXRlcmlhbERpYWxvZy52YWx1ZSA9IGZhbHNlXG4gICAgY29uc29sZS5sb2coJ21hdGVyaWFsczogJywgbWF0ZXJpYWxzLnZhbHVlKVxuICB9IGVsc2Uge1xuICAgIGNvbnNvbGUuZXJyb3IoJ9CS0LLQtdC00LjRgtC1INC60L7RgNGA0LXQutGC0L3Ri9C1INC00LDQvdC90YvQtScpXG4gIH1cbn1cblxuY29uc3QgYWRkTmV3Q2xpZW50ID0gYXN5bmMgKCkgPT4ge1xuICB0cnkge1xuICAgIGNvbnN0IHJlc3BvbnNlID0gYXdhaXQgYXBpLnBvc3QoJy9hZGRfY2xpZW50Jywge1xuICAgICAgbmFtZTogbmV3Q2xpZW50LnZhbHVlLm5hbWUsXG4gICAgICBwaG9uZTogbmV3Q2xpZW50LnZhbHVlLnBob25lLFxuICAgICAgc3BlY2lhbGl6YXRpb25faWQ6IHNlbGVjdGVkU3BlY2lhbGl6YXRpb25JZFxuICAgIH0pXG4gICAgc2hvd0FkZE5ld0NsaWVudERpYWxvZy52YWx1ZSA9IGZhbHNlXG4gICAgZ2V0Q2xpZW50cygpXG4gICAgY2xpZW50LnZhbHVlID0gcmVzcG9uc2UuZGF0YS5jbGllbnRcbiAgfSBjYXRjaCAoZXJyKSB7XG4gICAgY29uc29sZS5lcnJvcign0L7RiNC40LHQutCwINC00L7QsdCw0LLQu9C10L3QuNGPINC60LvQuNC10L3RgtCwOiAnLCBlcnIpXG4gIH1cbn1cblxuY29uc3QgYWRkTmV3TW9kZWwgPSBhc3luYyAoKSA9PiB7XG4gIHRyeSB7XG4gICAgY29uc3QgcmVzcG9uc2UgPSBhd2FpdCBhcGkucG9zdCgnL2FkZF9lcXVpcG1lbnRfbW9kZWwnLCB7XG4gICAgICBuYW1lOiBuZXdNb2RlbC52YWx1ZS5uYW1lLFxuICAgICAgc3BlY2lhbGl6YXRpb25faWQ6IHNlbGVjdGVkU3BlY2lhbGl6YXRpb25JZFxuICAgIH0pXG4gICAgc2hvd0FkZE5ld01vZGVsRGlhbG9nLnZhbHVlID0gZmFsc2VcbiAgICBnZXRNb2RlbHMoKVxuICAgIG1vZGVsLnZhbHVlID0gcmVzcG9uc2UuZGF0YS5tb2RlbFxuICB9IGNhdGNoIChlcnIpe1xuICAgIGNvbnNvbGUuZXJyb3IoZXJyKVxuICB9XG59XG5cbmNvbnN0IGFkZFByb2R1Y3RGcm9tU3RvcmUgPSAoKSA9PiB7XG4gIGNvbnNvbGUubG9nKCdzZWxlY3RlZFN0b3JlUHJvZHVjdDogJywgc2VsZWN0ZWRTdG9yZVByb2R1Y3QudmFsdWUpXG4gIC8vcHJvZHVjdHMudmFsdWUucHVzaChzZWxlY3RlZFN0b3JlUHJvZHVjdC52YWx1ZSlcbiAgY29uc3QgcHJvZHVjdCA9IHtcbiAgICBwcm9kdWN0X2lkOiBzZWxlY3RlZFN0b3JlUHJvZHVjdC52YWx1ZS5pZCxcbiAgICBuYW1lOiBzZWxlY3RlZFN0b3JlUHJvZHVjdC52YWx1ZS5uYW1lLFxuICAgIHByaWNlOiBzZWxlY3RlZFN0b3JlUHJvZHVjdC52YWx1ZS5iYXNlX3NhbGVfcHJpY2UsXG4gICAgYW1vdW50OiAxXG4gIH1cbiAgcHJvZHVjdHMudmFsdWUucHVzaChwcm9kdWN0KVxuICBjb25zb2xlLmxvZygncHJvZHVjdHM6ICcsIHByb2R1Y3RzLnZhbHVlKVxuICBzaG93QWRkUHJvZHVjdEZyb21TdG9yZURpYWxvZy52YWx1ZSA9IGZhbHNlXG59XG5cbmNvbnN0IGRlbGV0ZU1hdGVyaWFsRnJvbU9yZGVyID0gKGluZGV4KSA9PiB7XG4gIGNvbnNvbGUubG9nKCdpbmRleDogJywgaW5kZXgpXG4gIG1hdGVyaWFscy52YWx1ZS5zcGxpY2UoaW5kZXgsIDEpXG59XG5cbjwvc2NyaXB0PlxuXG48dGVtcGxhdGU+XG5cbiAgPGRpdiBjbGFzcz1cInJvdyBqdXN0aWZ5LWJldHdlZW5cIj5cbiAgICA8cS1idG4gZmxhdFxuICAgICAgICAgICB2LWlmPVwiIWVkaXRNb2RlXCJcbiAgICAgICAgICAgY29sb3I9XCJ5ZWxsb3dcIlxuICAgICAgICAgICBsYWJlbD1cItCd0JDQl9CQ0JRcIlxuICAgICAgICAgICBAY2xpY2s9XCIkcm91dGVyLmJhY2soKVwiXG4gICAgICAgICAgIHNpemU9XCJtZFwiXG4gICAgICAgICAgIGNsYXNzPVwiYnRuLWZsZXhcIlxuICAgIC8+XG5cbiAgICA8cS1idG4gZmxhdFxuICAgICAgICAgICB2LWlmPVwiZWRpdE1vZGVcIlxuICAgICAgICAgICBjb2xvcj1cInllbGxvd1wiXG4gICAgICAgICAgIGxhYmVsPVwi0L7RgtC80LXQvdCwXCJcbiAgICAgICAgICAgQGNsaWNrPVwiJHJvdXRlci5iYWNrKClcIlxuICAgICAgICAgICBzaXplPVwibWRcIlxuICAgICAgICAgICBjbGFzcz1cImJ0bi1mbGV4XCJcbiAgICAvPlxuXG4gICAgPGRpdj5cblxuICAgICAgPHEtYnRuIGZsYXRcbiAgICAgICAgICAgICB2LWlmPVwiIWVkaXRNb2RlXCJcbiAgICAgICAgICAgICBzaXplPVwibWRcIlxuICAgICAgICAgICAgIGNvbG9yPVwieWVsbG93XCJcbiAgICAgICAgICAgICBjbGFzcz1cImp1c3RpZnktZW5kXCJcbiAgICAgICAgICAgICBpY29uPVwiZGVsZXRlX2ZvcmV2ZXJcIlxuICAgICAgICAgICAgIEBjbGljaz1cImRlbGV0ZU9yZGVyXCJcbiAgICAgIC8+XG5cbiAgICAgIDxxLWJ0biBmbGF0XG4gICAgICAgICAgICAgdi1pZj1cIiFlZGl0TW9kZVwiXG4gICAgICAgICAgICAgc2l6ZT1cIm1kXCJcbiAgICAgICAgICAgICBjb2xvcj1cInllbGxvd1wiXG4gICAgICAgICAgICAgbGFiZWw9XCLQoNCV0JRcIlxuICAgICAgICAgICAgIEBjbGljaz1cImFjdGl2ZUVkaXRNb2RlXCJcbiAgICAgIC8+XG4gICAgICA8cS1idG4gZmxhdFxuICAgICAgICAgICAgIHYtaWY9XCJlZGl0TW9kZVwiXG4gICAgICAgICAgICAgc2l6ZT1cIm1kXCJcbiAgICAgICAgICAgICBjb2xvcj1cInllbGxvd1wiXG4gICAgICAgICAgICAgbGFiZWw9XCLRgdC+0YXRgFwiXG4gICAgICAgICAgICAgQGNsaWNrPVwic2F2ZU9yZGVyXCJcbiAgICAgIC8+XG5cbiAgICA8L2Rpdj5cblxuICAgIDxkaXYgY2xhc3M9XCJpdGVtcy1jZW50ZXIgcm93IHEtZ3V0dGVyLXgtbWRcIj5cblxuICAgICAgPHEtYnRuLXRvZ2dsZVxuICAgICAgICB2LW1vZGVsPVwib3JkZXJTdGF0dXNcIlxuICAgICAgICBzaXplPVwibWRcIlxuICAgICAgICBvdXRsaW5lXG4gICAgICAgIGdsb3NzeVxuICAgICAgICA6dG9nZ2xlLWNvbG9yPVwiY29tcHV0ZWRUb2dnbGVDb2xvclwiXG4gICAgICAgIGNvbG9yPVwiZ3JleVwiXG4gICAgICAgIEB1cGRhdGU6bW9kZWwtdmFsdWU9XCJ1cGRhdGVPcmRlclN0YXR1c1wiXG4gICAgICAgIDpvcHRpb25zPVwiW1xuICAgIHsgbGFiZWw6ICfQvtC20LjQtCcsIHZhbHVlOiAnd2FpdGluZycgfSxcbiAgICB7IGxhYmVsOiAn0LLRgNCw0LEnLCB2YWx1ZTogJ3Byb2Nlc3MnIH0sXG4gICAgeyBsYWJlbDogJ9Cz0L7RgtC+0LLQvicsIHZhbHVlOiAnZG9uZScgfVxuICBdXCJcbiAgICAgID5cbiAgICAgIDwvcS1idG4tdG9nZ2xlPlxuXG4gICAgICA8cS1idG4gb3V0bGluZVxuICAgICAgICAgICAgIHNpemU9XCJtZFwiXG4gICAgICAgICAgICAgQGNsaWNrPVwic3dpdNGBaFBhaWRTdGF0dXNcIlxuICAgICAgICAgICAgIDpjb2xvcj1cInBhaWQgPyAnZ3JlZW4nIDogJ2dyZXknXCJcbiAgICAgICAgICAgICBnbG9zc3lcbiAgICAgICAgICAgICBsYWJlbD1cItC+0L/Qu1wiXG4gICAgICAvPlxuXG4gICAgPC9kaXY+XG5cbiAgPC9kaXY+XG5cbiAgPGRpdiBjbGFzcz1cInJvdyBpdGVtcy1jZW50ZXJcIiA+XG4gICAgPHEtc2VsZWN0IHYtbW9kZWw9XCJjbGllbnRcIlxuICAgICAgICAgICAgICA6b3B0aW9ucz1cImNsaWVudHNcIlxuICAgICAgICAgICAgICBvcHRpb24tdmFsdWU9XCJpZFwiXG4gICAgICAgICAgICAgIG9wdGlvbi1sYWJlbD1cIm5hbWVcIlxuICAgICAgICAgICAgICBsYWJlbD1cItC60LvQuNC10L3RglwiXG4gICAgICAgICAgICAgIGRlbnNlXG4gICAgICAgICAgICAgIDpkaXNhYmxlPVwiIWVkaXRNb2RlXCJcbiAgICAgICAgICAgICAgY2xhc3M9XCJjb2xcIlxuICAgICAgICAgICAgICBjb2xvcj1cInllbGxvd1wiXG4gICAgLz5cblxuICAgIDxkaXYgY2xhc3M9XCJjb2wtYXV0byBzZWxmLWVuZFwiIHYtaWY9XCJlZGl0TW9kZVwiPlxuICAgICAgPHEtYnRuIGNsYXNzPVwiY29sLWF1dG8gdGV4dC15ZWxsb3dcIiBAY2xpY2s9XCJzaG93QWRkTmV3Q2xpZW50RGlhbG9nPXRydWVcIj4rPC9xLWJ0bj5cbiAgICA8L2Rpdj5cblxuICAgIDxxLXNlbGVjdCB2LW1vZGVsPVwibW9kZWxcIlxuICAgICAgICAgICAgICA6b3B0aW9ucz1cIm1vZGVsc1wiXG4gICAgICAgICAgICAgIG9wdGlvbi12YWx1ZT1cImlkXCJcbiAgICAgICAgICAgICAgb3B0aW9uLWxhYmVsPVwibmFtZVwiXG4gICAgICAgICAgICAgIGxhYmVsPVwi0LzQvtC00LXQu9GMXCJcbiAgICAgICAgICAgICAgOmRpc2FibGU9XCIhZWRpdE1vZGVcIlxuICAgICAgICAgICAgICBjbGFzcz1cImNvbFwiXG4gICAgICAgICAgICAgIGRlbnNlXG4gICAgICAgICAgICAgIGNvbG9yPVwieWVsbG93XCJcbiAgICAvPlxuXG4gICAgPGRpdiBjbGFzcz1cImNvbC1hdXRvIHNlbGYtZW5kXCIgdi1pZj1cImVkaXRNb2RlXCI+XG4gICAgICA8cS1idG4gY2xhc3M9XCJjb2wtYXV0byB0ZXh0LXllbGxvd1wiIEBjbGljaz1cInNob3dBZGROZXdNb2RlbERpYWxvZz10cnVlXCI+KzwvcS1idG4+XG4gICAgPC9kaXY+XG5cbiAgPC9kaXY+XG5cbiAgPGRpdj5cbiAgICA8cS1jYXJkPlxuICAgICAgPHEtdGFic1xuICAgICAgICB2LXNob3c9XCJlZGl0TW9kZVwiXG4gICAgICAgIHYtbW9kZWw9XCJ0YWJcIlxuICAgICAgICBkZW5zZVxuICAgICAgICBjbGFzcz1cInRleHQtZ3JleVwiXG4gICAgICAgIGFjdGl2ZS1jb2xvcj1cInllbGxvd1wiXG4gICAgICAgIGluZGljYXRvci1jb2xvcj1cInllbGxvd1wiXG4gICAgICAgIGFsaWduPVwianVzdGlmeVwiXG4gICAgICAgIG5hcnJvdy1pbmRpY2F0b3JcbiAgICAgID5cbiAgICAgICAgPHEtdGFiIG5hbWU9XCJhbGxcIlxuICAgICAgICAgICAgICAgOmxhYmVsPVwiYNGA0LDQsdC+0YI6ICR7KHNlcnZpY2VzPy5sZW5ndGggfHwgMCl9INC80LDRgtC10YDQuNCw0LvQvtCyOiAkeyhtYXRlcmlhbHM/Lmxlbmd0aCB8fCAwKSArIChwcm9kdWN0cz8ubGVuZ3RoIHx8IDApfWBcIlxuICAgICAgICAvPlxuICAgICAgICA8cS10YWIgbmFtZT1cInNlcnZpY2VzQ2hvaWNlXCIgdi1pZj1cImVkaXRNb2RlXCIgbGFiZWw9XCLRgNCw0LHQvtGC0YtcIiAvPlxuICAgICAgICA8cS10YWIgbmFtZT1cIm1hdGVyaWFsc0Nob2ljZVwiIHYtaWY9XCJlZGl0TW9kZVwiIGxhYmVsPVwi0LzQsNGC0LXRgNC40LDQu9GLXCIgLz5cbiAgICAgIDwvcS10YWJzPlxuXG4gICAgICA8cS1zZXBhcmF0b3IgLz5cblxuICAgICAgPHEtdGFiLXBhbmVscyB2LW1vZGVsPVwidGFiXCIgYW5pbWF0ZWQ+XG5cbiAgICAgICAgPCEtLSDQv9Cw0L3QtdC70Ywg0L7RgtC+0LHRgNCw0LbQtdC90LjRjyDQstGL0LHRgNCw0L3QvdGL0YUg0YHQtdGA0LLQuNGB0L7QsiDQuCDQvNCw0YLQtdGA0LjQsNC70L7QsiAtLT5cbiAgICAgICAgPHEtdGFiLXBhbmVsIG5hbWU9XCJhbGxcIiBzdHlsZT1cInBhZGRpbmc6IDBcIj5cbiAgICAgICAgICA8ZGl2PlxuXG4gICAgICAgICAgICA8cS1saXN0IGJvcmRlcmVkIHNlcGFyYXRvciA+XG5cbiAgICAgICAgICAgICAgPHEtaXRlbS1sYWJlbCB2LWlmPVwiIXNlcnZpY2VzXCI+0J3QtdGCINGB0LXRgNCy0LjRgdC+0LI8L3EtaXRlbS1sYWJlbD5cbiAgICAgICAgICAgICAgPHEtaXRlbSB2LWZvcj1cIihzZXJ2aWNlLCBpbmRleCkgaW4gc2VydmljZXNcIlxuICAgICAgICAgICAgICAgICAgICAgIDprZXk9XCJpbmRleFwiXG4gICAgICAgICAgICAgICAgICAgICAgY2xhc3M9XCJ3LTEwMCBqdXN0aWZ5LWJldHdlZW5cIlxuICAgICAgICAgICAgICAgICAgICAgIHN0eWxlPVwid2lkdGg6IDEwMCVcIlxuICAgICAgICAgICAgICA+XG4gICAgICAgICAgICAgICAgPCEtLSDRgdC10YDQstC40YHRiyAtLT5cbiAgICAgICAgICAgICAgICA8cS1pdGVtLXNlY3Rpb24gPlxuICAgICAgICAgICAgICAgICAgICAgIDxxLWl0ZW0tbGFiZWwgY2xhc3M9XCJ0ZXh0LWxlZnRcIj5cbiAgICAgICAgICAgICAgICAgICAgICAgIHt7IHNlcnZpY2Uuc2VydmljZSB9fVxuICAgICAgICAgICAgICAgICAgICAgIDwvcS1pdGVtLWxhYmVsPlxuICAgICAgICAgICAgICAgIDwvcS1pdGVtLXNlY3Rpb24+XG5cblxuICAgICAgICAgICAgICAgIDxxLWl0ZW0tc2VjdGlvbiA+XG4gICAgICAgICAgICAgICAgICAgICAgPHEtaXRlbS1sYWJlbCBjbGFzcz1cInRleHQtcmlnaHRcIj5cbiAgICAgICAgICAgICAgICAgICAgICAgIHt7IHNlcnZpY2UucHJpY2UgfX3RgFxuICAgICAgICAgICAgICAgICAgICAgIDwvcS1pdGVtLWxhYmVsPlxuICAgICAgICAgICAgICAgIDwvcS1pdGVtLXNlY3Rpb24+XG5cblxuICAgICAgICAgICAgICAgIDxxLWl0ZW0tc2VjdGlvbiBjbGFzcz1cImNvbC1hdXRvXCIgdi1pZj1cImVkaXRNb2RlXCI+XG4gICAgICAgICAgICAgICAgICA8cS1idG4gaWNvbj1cImRlbGV0ZV9mb3JldmVyXCIgQGNsaWNrPVwic2VydmljZXMuc3BsaWNlKGluZGV4LCAxKVwiIGNvbG9yPVwicmVkXCIgZmxhdCByb3VuZCAvPlxuICAgICAgICAgICAgICAgIDwvcS1pdGVtLXNlY3Rpb24+XG5cbiAgICAgICAgICAgICAgPC9xLWl0ZW0+XG4gICAgICAgICAgICA8L3EtbGlzdD5cblxuICAgICAgICAgICAgPGRpdiBjbGFzcz1cInRleHQtZ3JleSB0ZXh0LWxlZnRcIiB2LXNob3c9XCJ0b3RhbFN1bVNlcnZpY2VzID4gMCAmJiB0b3RhbFN1bU1hdGVyaWFscyA+IDBcIiA+XG4gICAgICAgICAgICAgINCy0YHQtdCz0L4g0L/QviDRgNCw0LHQvtGC0LUgOiB7e3RvdGFsU3VtU2VydmljZXN9fdGAXG4gICAgICAgICAgICA8L2Rpdj5cblxuICAgICAgICAgICAgPCEtLSDQvtGC0L7QsdGA0LDQttC10L3QuNC1INGB0L/QuNGB0LrQsCDQvNCw0YLQtdGA0LjQsNC70L7QsiAtLT5cbiAgICAgICAgICAgIDxxLWxpc3QgYm9yZGVyZWQgc2VwYXJhdG9yID5cblxuICAgICAgICAgICAgICA8cS1pdGVtLWxhYmVsIHYtaWY9XCIhbWF0ZXJpYWxzXCI+0J3QtdGCINC80LDRgtC10YDQuNCw0LvQvtCyPC9xLWl0ZW0tbGFiZWw+XG4gICAgICAgICAgICAgIDxxLWl0ZW0gdi1mb3I9XCIobWF0ZXJpYWwsIGluZGV4KSBpbiBtYXRlcmlhbHNcIlxuICAgICAgICAgICAgICAgICAgICAgIDprZXk9XCJtYXRlcmlhbC5pZFwiXG4gICAgICAgICAgICAgICAgICAgICAgY2xhc3M9XCJ3LTEwMCBqdXN0aWZ5LWJldHdlZW4gcm93XCJcbiAgICAgICAgICAgICAgICAgICAgICBzdHlsZT1cIndpZHRoOiAxMDAlXCJcbiAgICAgICAgICAgICAgPlxuXG4gICAgICAgICAgICAgICAgPHEtaXRlbS1zZWN0aW9uIGNsYXNzPVwiY29sLTdcIj5cbiAgICAgICAgICAgICAgICAgIDxxLWl0ZW0tbGFiZWwgY2xhc3M9XCJ0ZXh0LWxlZnRcIj5cbiAgICAgICAgICAgICAgICAgICAge3sgbWF0ZXJpYWwubmFtZSB9fVxuICAgICAgICAgICAgICAgICAgPC9xLWl0ZW0tbGFiZWw+XG4gICAgICAgICAgICAgICAgPC9xLWl0ZW0tc2VjdGlvbj5cblxuICAgICAgICAgICAgICAgIDxxLWl0ZW0tc2VjdGlvbiBjbGFzcz1cImNvbC0xXCI+XG4gICAgICAgICAgICAgICAgICA8cS1pdGVtLWxhYmVsIGNsYXNzPVwidGV4dC1yaWdodFwiPlxuICAgICAgICAgICAgICAgICAgICB7eyBtYXRlcmlhbC5wcmljZSB9fdGAXG4gICAgICAgICAgICAgICAgICA8L3EtaXRlbS1sYWJlbD5cbiAgICAgICAgICAgICAgICA8L3EtaXRlbS1zZWN0aW9uPlxuXG4gICAgICAgICAgICAgICAgPHEtaXRlbS1zZWN0aW9uIGNsYXNzPVwiY29sLTFcIj5cbiAgICAgICAgICAgICAgICAgIDxxLWl0ZW0tbGFiZWwgY2xhc3M9XCJ0ZXh0LWNlbnRlclwiPlxuICAgICAgICAgICAgICAgICAgICDRhXt7bWF0ZXJpYWwuYW1vdW50fX1cbiAgICAgICAgICAgICAgICAgIDwvcS1pdGVtLWxhYmVsPlxuICAgICAgICAgICAgICAgIDwvcS1pdGVtLXNlY3Rpb24+XG5cbiAgICAgICAgICAgICAgICA8cS1pdGVtLXNlY3Rpb24gY2xhc3M9XCJjb2wtMVwiPlxuICAgICAgICAgICAgICAgICAgPHEtaXRlbS1sYWJlbCBjbGFzcz1cInRleHQtcmlnaHRcIj5cbiAgICAgICAgICAgICAgICAgICAge3ttYXRlcmlhbC5wcmljZSAqIG1hdGVyaWFsLmFtb3VudH190YBcbiAgICAgICAgICAgICAgICAgIDwvcS1pdGVtLWxhYmVsPlxuICAgICAgICAgICAgICAgIDwvcS1pdGVtLXNlY3Rpb24+XG5cbiAgICAgICAgICAgICAgICA8cS1pdGVtLXNlY3Rpb24gY2xhc3M9XCJjb2wtYXV0b1wiIHYtaWY9XCJlZGl0TW9kZVwiPlxuICAgICAgICAgICAgICAgICAgPHEtYnRuIGljb249XCJkZWxldGVfZm9yZXZlclwiIEBjbGljaz1cImRlbGV0ZU1hdGVyaWFsRnJvbU9yZGVyKGluZGV4KVwiIGNvbG9yPVwicmVkXCIgZmxhdCByb3VuZCAvPlxuICAgICAgICAgICAgICAgIDwvcS1pdGVtLXNlY3Rpb24+XG5cbiAgICAgICAgICAgICAgPC9xLWl0ZW0+XG4gICAgICAgICAgICA8L3EtbGlzdD5cblxuICAgICAgICAgICAgPCEtLSDQvtGC0L7QsdGA0LDQttC10L3QuNC1INGB0L/QuNGB0LrQsCDQv9GA0L7QtNGD0LrRgtC+0LIgLS0+XG4gICAgICAgICAgICA8cS1saXN0IGJvcmRlcmVkIHNlcGFyYXRvciA+XG5cbiAgICAgICAgICAgICAgPHEtaXRlbS1sYWJlbCB2LWlmPVwiIXByb2R1Y3RzXCI+0J3QtdGCINC80LDRgtC10YDQuNCw0LvQvtCyPC9xLWl0ZW0tbGFiZWw+XG4gICAgICAgICAgICAgIDxxLWl0ZW0gdi1mb3I9XCJwcm9kdWN0IGluIHByb2R1Y3RzXCJcbiAgICAgICAgICAgICAgICAgICAgICA6a2V5PVwicHJvZHVjdFwiXG4gICAgICAgICAgICAgICAgICAgICAgY2xhc3M9XCJ3LTEwMCBqdXN0aWZ5LWJldHdlZW4gcm93XCJcbiAgICAgICAgICAgICAgICAgICAgICBzdHlsZT1cIndpZHRoOiAxMDAlXCJcbiAgICAgICAgICAgICAgPlxuXG4gICAgICAgICAgICAgICAgPHEtaXRlbS1zZWN0aW9uIGNsYXNzPVwiY29sLTdcIj5cbiAgICAgICAgICAgICAgICAgIDxxLWl0ZW0tbGFiZWwgY2xhc3M9XCJ0ZXh0LWxlZnRcIj5cbiAgICAgICAgICAgICAgICAgICAge3sgcHJvZHVjdC5uYW1lIH19XG4gICAgICAgICAgICAgICAgICA8L3EtaXRlbS1sYWJlbD5cbiAgICAgICAgICAgICAgICA8L3EtaXRlbS1zZWN0aW9uPlxuXG5cbiAgICAgICAgICAgICAgICA8cS1pdGVtLXNlY3Rpb24gY2xhc3M9XCJjb2wtMVwiPlxuICAgICAgICAgICAgICAgICAgPHEtaXRlbS1sYWJlbCBjbGFzcz1cInRleHQtcmlnaHRcIj5cbiAgICAgICAgICAgICAgICAgICAge3sgcHJvZHVjdC5wcmljZSB9fdGAXG4gICAgICAgICAgICAgICAgICA8L3EtaXRlbS1sYWJlbD5cbiAgICAgICAgICAgICAgICA8L3EtaXRlbS1zZWN0aW9uPlxuXG4gICAgICAgICAgICAgICAgPHEtaXRlbS1zZWN0aW9uIGNsYXNzPVwiY29sLTFcIj5cbiAgICAgICAgICAgICAgICAgIDxxLWl0ZW0tbGFiZWwgY2xhc3M9XCJ0ZXh0LWNlbnRlclwiPlxuICAgICAgICAgICAgICAgICAgICDRhXt7cHJvZHVjdC5hbW91bnR9fVxuICAgICAgICAgICAgICAgICAgPC9xLWl0ZW0tbGFiZWw+XG4gICAgICAgICAgICAgICAgPC9xLWl0ZW0tc2VjdGlvbj5cblxuICAgICAgICAgICAgICAgIDxxLWl0ZW0tc2VjdGlvbiBjbGFzcz1cImNvbC0xXCI+XG4gICAgICAgICAgICAgICAgICA8cS1pdGVtLWxhYmVsIGNsYXNzPVwidGV4dC1yaWdodFwiPlxuICAgICAgICAgICAgICAgICAgICB7e3Byb2R1Y3QucHJpY2UgKiBwcm9kdWN0LmFtb3VudH190YBcbiAgICAgICAgICAgICAgICAgIDwvcS1pdGVtLWxhYmVsPlxuICAgICAgICAgICAgICAgIDwvcS1pdGVtLXNlY3Rpb24+XG5cbiAgICAgICAgICAgICAgICA8cS1pdGVtLXNlY3Rpb24gY2xhc3M9XCJjb2wtYXV0b1wiIHYtaWY9XCJlZGl0TW9kZVwiPlxuICAgICAgICAgICAgICAgICAgPHEtYnRuIGljb249XCJkZWxldGVfZm9yZXZlclwiIEBjbGljaz1cInByb2R1Y3RzLnNwbGljZShpbmRleCwgMSlcIiBjb2xvcj1cInJlZFwiIGZsYXQgcm91bmQgLz5cbiAgICAgICAgICAgICAgICA8L3EtaXRlbS1zZWN0aW9uPlxuXG4gICAgICAgICAgICAgIDwvcS1pdGVtPlxuICAgICAgICAgICAgPC9xLWxpc3Q+XG5cbiAgICAgICAgICAgIDxkaXYgY2xhc3M9XCJ0ZXh0LWdyZXkgdGV4dC1sZWZ0XCIgdi1zaG93PVwidG90YWxTdW1NYXRlcmlhbHMgPiAwICYmIHRvdGFsU3VtU2VydmljZXMgPiAwXCI+XG4gICAgICAgICAgICAgINCy0YHQtdCz0L4g0L/QviDQvNCw0YLQtdGA0LjQsNC70LDQvDoge3t0b3RhbFN1bU1hdGVyaWFscyArIHRvdGFsU3VtUHJvZHVjdHN9fdGAXG4gICAgICAgICAgICA8L2Rpdj5cblxuICAgICAgICAgICAgPGRpdiBjbGFzcz1cInRleHQtZ3JleSB0ZXh0LWNlbnRlciBkaXNwbGF5OiBmbGV4XCIgPlxuICAgICAgICAgICAgICA8ZGl2PlxuICAgICAgICAgICAgICAgINCy0YHQtdCz0L4g0Log0L7Qv9C70LDRgtC1OlxuICAgICAgICAgICAgICA8L2Rpdj5cblxuICAgICAgICAgICAgICA8ZGl2IGNsYXNzPVwidGV4dC1ncmVlblwiPlxuICAgICAgICAgICAgICAgIHt7dG90YWxTdW1NYXRlcmlhbHMgKyB0b3RhbFN1bVByb2R1Y3RzICsgdG90YWxTdW1TZXJ2aWNlc319XG4gICAgICAgICAgICAgIDwvZGl2PlxuICAgICAgICAgICAgICDRgFxuICAgICAgICAgICAgPC9kaXY+XG5cbiAgICAgICAgICA8L2Rpdj5cblxuICAgICAgICAgIDxxLWlucHV0IHR5cGU9XCJ0ZXh0YXJlYVwiXG4gICAgICAgICAgICAgICAgICAgdi1tb2RlbD1cImNvbW1lbnRzXCJcbiAgICAgICAgICAgICAgICAgICBsYWJlbD1cItC60L7QvNC80LXQvdGC0LDRgNC40LhcIlxuICAgICAgICAgICAgICAgICAgIGxhYmVsLWNvbG9yPVwieWVsbG93XCJcbiAgICAgICAgICAgICAgICAgICBjb2xvcj1cInllbGxvd1wiXG4gICAgICAgICAgICAgICAgICAgYXV0b2dyb3dcbiAgICAgICAgICAgICAgICAgICBwbGFjZWhvbGRlcj1cItCa0L7QvNC10L3RgtCw0YDQuNC10LIg0L3QtdGCXCJcbiAgICAgICAgICAgICAgICAgICA6ZGlzYWJsZT1cIiFlZGl0TW9kZVwiXG4gICAgICAgICAgLz5cblxuXG5cbiAgICAgICAgPC9xLXRhYi1wYW5lbD5cblxuICAgICAgICA8IS0tINC/0LDQvdC10LvRjCDQstGL0LHQvtGA0LAg0YHQtdGA0LLQuNGB0L7QsiAtLT5cbiAgICAgICAgPHEtdGFiLXBhbmVsIG5hbWU9XCJzZXJ2aWNlc0Nob2ljZVwiIHN0eWxlPVwicGFkZGluZzogMFwiPlxuICAgICAgICAgIDxxLXNlbGVjdCB2LW1vZGVsPVwic2VsZWN0ZWRTZXJ2aWNlQ2F0ZWdvcnlcIlxuICAgICAgICAgICAgICAgICAgICA6b3B0aW9ucz1cInNlcnZpY2VDYXRlZ29yaWVzXCJcbiAgICAgICAgICAgICAgICAgICAgb3B0aW9uLWxhYmVsPVwiY2F0ZWdvcnlfbmFtZVwiXG4gICAgICAgICAgICAgICAgICAgIG9wdGlvbi12YWx1ZT1cImlkXCJcbiAgICAgICAgICAgICAgICAgICAgZW1pdC12YWx1ZVxuICAgICAgICAgICAgICAgICAgICBtYXAtb3B0aW9uc1xuICAgICAgICAgICAgICAgICAgICBsYWJlbD1cItC60LDRgtC10LPQvtGA0LjQuCDRgNCw0LHQvtGCXCJcbiAgICAgICAgICAgICAgICAgICAgZGVuc2VcbiAgICAgICAgICAgICAgICAgICAgcGxhY2Vob2xkZXI9XCLQvdC10YIg0LrQsNGC0LXQs9C+0YDQuNC5XCJcbiAgICAgICAgICAgICAgICAgICAgbGFiZWwtY29sb3I9XCJncmV5XCJcbiAgICAgICAgICAgICAgICAgICAgY29sb3I9XCJ5ZWxsb3dcIlxuICAgICAgICAgICAgICAgICAgICBAdXBkYXRlOm1vZGVsLXZhbHVlPVwiZ2V0U2VydmljZXNCeUNhdGVnb3J5XCJcbiAgICAgICAgICAvPlxuXG4gICAgICAgICAgPHEtbGlzdCBib3JkZXJlZCBzZXBhcmF0b3IgPlxuICAgICAgICAgICAgPHEtaXRlbS1sYWJlbCB2LWlmPVwiIXNlcnZpY2VzQnlDYXRlZ29yeVwiPtCd0LXRgiDRgdC10YDQstC40YHQvtCyPC9xLWl0ZW0tbGFiZWw+XG4gICAgICAgICAgICA8cS1pdGVtIHYtZm9yPVwic2VydmljZSBpbiBzZXJ2aWNlc0J5Q2F0ZWdvcnlcIlxuICAgICAgICAgICAgICAgICAgICA6a2V5PVwic2VydmljZVwiXG4gICAgICAgICAgICAgICAgICAgIGNsYXNzPVwidy0xMDAganVzdGlmeS1iZXR3ZWVuIHNlbGVjdFNlcnZpY2VcIlxuICAgICAgICAgICAgICAgICAgICBzdHlsZT1cIndpZHRoOiAxMDAlXCJcbiAgICAgICAgICAgICAgICAgICAgY2xpY2thYmxlXG4gICAgICAgICAgICAgICAgICAgIHYtcmlwcGxlXG4gICAgICAgICAgICAgICAgICAgIEBjbGljaz1cInNlcnZpY2VzLnB1c2goeyAuLi5zZXJ2aWNlIH0pXCJcbiAgICAgICAgICAgICAgICAgICAgOnEtaXRlbVxuICAgICAgICAgICAgICAgICAgICA6Y2xhc3M9XCJ7XG4gICAgICAgICAgICAgICAgICAgICAgJ3RleHQteWVsbG93Jzogc2VydmljZXMuc29tZShzID0+IHMuaWQgPT09IHNlcnZpY2UuaWQpLFxuICAgICAgICAgICAgICAgICAgICAgfVwiXG4gICAgICAgICAgICA+XG5cbiAgICAgICAgICAgICAgPHEtaXRlbS1zZWN0aW9uID5cbiAgICAgICAgICAgICAgICA8cS1pdGVtLWxhYmVsIGNsYXNzPVwidGV4dC1sZWZ0XCI+XG4gICAgICAgICAgICAgICAgICB7eyBzZXJ2aWNlLnNlcnZpY2UgfX1cbiAgICAgICAgICAgICAgICA8L3EtaXRlbS1sYWJlbD5cbiAgICAgICAgICAgICAgPC9xLWl0ZW0tc2VjdGlvbj5cblxuICAgICAgICAgICAgICA8cS1pdGVtLXNlY3Rpb24gPlxuICAgICAgICAgICAgICAgIDxxLWl0ZW0tbGFiZWwgY2xhc3M9XCJ0ZXh0LXJpZ2h0XCI+XG4gICAgICAgICAgICAgICAgICB7eyBzZXJ2aWNlLnByaWNlIH19XG4gICAgICAgICAgICAgICAgPC9xLWl0ZW0tbGFiZWw+XG4gICAgICAgICAgICAgIDwvcS1pdGVtLXNlY3Rpb24+XG5cbiAgICAgICAgICAgICAgPHEtYnRuXG4gICAgICAgICAgICAgICAgaWNvbj1cImFkZFwiXG4gICAgICAgICAgICAgICAgcm91bmRcbiAgICAgICAgICAgICAgICBjbGFzcz1cImZhYiBiZy15ZWxsb3cgdGV4dC1ibGFja1wiXG4gICAgICAgICAgICAgICAgQGNsaWNrPVwic2hvd0FkZE5ld1NlcnZpY2VEaWFsb2cgPSB0cnVlXCJcbiAgICAgICAgICAgICAgICBzaXplPVwiMjBweFwiXG4gICAgICAgICAgICAgIC8+XG5cbiAgICAgICAgICAgIDwvcS1pdGVtPlxuICAgICAgICAgIDwvcS1saXN0PlxuXG4gICAgICAgIDwvcS10YWItcGFuZWw+XG5cbiAgICAgICAgPCEtLSDQv9Cw0L3QtdC70Ywg0LLRi9Cx0L7RgNCwINC80LDRgtC10YDQuNCw0LvQvtCyIC0tPlxuICAgICAgICA8cS10YWItcGFuZWwgbmFtZT1cIm1hdGVyaWFsc0Nob2ljZVwiIHN0eWxlPVwicGFkZGluZzogMFwiPlxuXG4gICAgICAgICAgPGRpdiBjbGFzcz1cInRleHQtY2VudGVyIHRleHQtZ3JleVwiPtC80LDRgtC10YDQuNCw0LvRizoge3t0b3RhbFN1bU1hdGVyaWFsc3190YA8L2Rpdj5cbiAgICAgICAgICA8IS0tINC+0YLQvtCx0YDQsNC20LXQvdC40LUg0LzQsNGC0LXRgNC40LDQu9C+0LIgLS0+XG4gICAgICAgICAgPHEtbGlzdCBib3JkZXJlZCBzZXBhcmF0b3I+XG4gICAgICAgICAgICA8cS1pdGVtLWxhYmVsIHYtaWY9XCIhbWF0ZXJpYWxzXCI+INC90LXRgiDQvNCw0YLQtdGA0LjQsNC70L7QsjwvcS1pdGVtLWxhYmVsPlxuICAgICAgICAgICAgPHEtaXRlbSB2LWZvcj1cIihtYXRlcmlhbCwgaW5kZXgpIGluIG1hdGVyaWFsc1wiXG4gICAgICAgICAgICAgICAgICAgIDprZXk9XCJpbmRleFwiXG4gICAgICAgICAgICAgICAgICAgIGNsYXNzPVwidy0xMDAganVzdGlmeS1iZXR3ZWVuIHJvd1wiXG4gICAgICAgICAgICA+XG5cbiAgICAgICAgICAgICAgPHEtaXRlbS1zZWN0aW9uIGNsYXNzPVwiY29sLTdcIj5cbiAgICAgICAgICAgICAgICA8cS1pbnB1dCB2LW1vZGVsPVwibWF0ZXJpYWwubmFtZVwiIC8+XG4gICAgICAgICAgICAgIDwvcS1pdGVtLXNlY3Rpb24+XG5cbiAgICAgICAgICAgICAgPHEtaXRlbS1zZWN0aW9uIGNsYXNzPVwiY29sLTFcIj5cbiAgICAgICAgICAgICAgICA8cS1pbnB1dCB2LW1vZGVsPVwibWF0ZXJpYWwucHJpY2VcIiBpbnB1dC1jbGFzcz1cInRleHQtcmlnaHRcIi8+XG4gICAgICAgICAgICAgIDwvcS1pdGVtLXNlY3Rpb24+XG5cbiAgICAgICAgICAgICAgPHEtaXRlbS1zZWN0aW9uIGNsYXNzPVwiY29sLTFcIj5cbiAgICAgICAgICAgICAgICA8cS1pbnB1dCB2LW1vZGVsPVwibWF0ZXJpYWwuYW1vdW50XCIgaW5wdXQtY2xhc3M9XCJ0ZXh0LXJpZ2h0XCIgcHJlZml4PVwieFwiIC8+XG4gICAgICAgICAgICAgIDwvcS1pdGVtLXNlY3Rpb24+XG5cbiAgICAgICAgICAgICAgPHEtaXRlbS1zZWN0aW9uIGNsYXNzPVwiY29sLTFcIiBkaXNhYmxlZD1cImRpc2FibGVkXCIgaW5wdXQtY2xhc3M9XCJ0ZXh0LXJpZ2h0XCIgPlxuICAgICAgICAgICAgICAgIDxxLWlucHV0IDptb2RlbC12YWx1ZT1cIm1hdGVyaWFsLnByaWNlICogbWF0ZXJpYWwuYW1vdW50XCIgLz5cbiAgICAgICAgICAgICAgPC9xLWl0ZW0tc2VjdGlvbj5cblxuICAgICAgICAgICAgICA8cS1pdGVtLXNlY3Rpb24gY2xhc3M9XCJjb2wtYXV0b1wiPlxuICAgICAgICAgICAgICAgIDxxLWJ0biBpY29uPVwiZGVsZXRlX2ZvcmV2ZXJcIiBAY2xpY2s9XCJtYXRlcmlhbHMuc3BsaWNlKGluZGV4LCAxKVwiIGNvbG9yPVwicmVkXCIgZmxhdCByb3VuZCAvPlxuICAgICAgICAgICAgICA8L3EtaXRlbS1zZWN0aW9uPlxuXG4gICAgICAgICAgICA8L3EtaXRlbT5cblxuICAgICAgICAgIDwvcS1saXN0PlxuXG4gICAgICAgICAgPGRpdiBjbGFzcz1cInRleHQtY2VudGVyIHRleHQtZ3JleVwiPtC/0YDQvtC00YPQutGC0Ys6IHt7dG90YWxTdW1Qcm9kdWN0c319INGAPC9kaXY+XG5cbiAgICAgICAgICA8IS0tINC+0YLQvtCx0YDQsNC20LXQvdC40LUg0L/RgNC+0LTRg9C60YLQvtCyIC0tPlxuICAgICAgICAgIDxxLWxpc3QgYm9yZGVyZWQgc2VwYXJhdG9yPlxuICAgICAgICAgICAgPHEtaXRlbS1sYWJlbCB2LWlmPVwiIXByb2R1Y3RzXCI+INC90LXRgiDQvNCw0YLQtdGA0LjQsNC70L7QsjwvcS1pdGVtLWxhYmVsPlxuICAgICAgICAgICAgPHEtaXRlbSB2LWZvcj1cIihwcm9kdWN0LCBpbmRleCkgaW4gcHJvZHVjdHNcIlxuICAgICAgICAgICAgICAgICAgICA6a2V5PVwiaW5kZXhcIlxuICAgICAgICAgICAgICAgICAgICBjbGFzcz1cInctMTAwIGp1c3RpZnktYmV0d2VlbiByb3dcIlxuICAgICAgICAgICAgPlxuXG4gICAgICAgICAgICAgIDxxLWl0ZW0tc2VjdGlvbiBjbGFzcz1cImNvbC03XCI+XG4gICAgICAgICAgICAgICAgPHEtaW5wdXQgdi1tb2RlbD1cInByb2R1Y3QubmFtZVwiIC8+XG4gICAgICAgICAgICAgIDwvcS1pdGVtLXNlY3Rpb24+XG5cblxuICAgICAgICAgICAgICA8cS1pdGVtLXNlY3Rpb24gY2xhc3M9XCJjb2wtMVwiPlxuICAgICAgICAgICAgICAgIDxxLWlucHV0IHYtbW9kZWw9XCJwcm9kdWN0LnByaWNlXCIgaW5wdXQtY2xhc3M9XCJ0ZXh0LXJpZ2h0XCIvPlxuICAgICAgICAgICAgICA8L3EtaXRlbS1zZWN0aW9uPlxuXG4gICAgICAgICAgICAgIDxxLWl0ZW0tc2VjdGlvbiBjbGFzcz1cImNvbC0xXCI+XG4gICAgICAgICAgICAgICAgPHEtaW5wdXQgdi1tb2RlbD1cInByb2R1Y3QuYW1vdW50XCIgaW5wdXQtY2xhc3M9XCJ0ZXh0LXJpZ2h0XCIgcHJlZml4PVwieFwiIC8+XG4gICAgICAgICAgICAgIDwvcS1pdGVtLXNlY3Rpb24+XG5cbiAgICAgICAgICAgICAgPHEtaXRlbS1zZWN0aW9uIGNsYXNzPVwiY29sLTFcIiBkaXNhYmxlZD1cImRpc2FibGVkXCIgaW5wdXQtY2xhc3M9XCJ0ZXh0LXJpZ2h0XCIgPlxuICAgICAgICAgICAgICAgIDxxLWlucHV0IDptb2RlbC12YWx1ZT1cInByb2R1Y3QucHJpY2UgKiBwcm9kdWN0LmFtb3VudFwiIC8+XG4gICAgICAgICAgICAgIDwvcS1pdGVtLXNlY3Rpb24+XG5cbiAgICAgICAgICAgICAgPHEtaXRlbS1zZWN0aW9uIGNsYXNzPVwiY29sLWF1dG9cIj5cbiAgICAgICAgICAgICAgICA8cS1idG4gaWNvbj1cImRlbGV0ZV9mb3JldmVyXCIgQGNsaWNrPVwicHJvZHVjdHMuc3BsaWNlKGluZGV4LCAxKVwiIGNvbG9yPVwicmVkXCIgZmxhdCByb3VuZCAvPlxuICAgICAgICAgICAgICA8L3EtaXRlbS1zZWN0aW9uPlxuXG4gICAgICAgICAgICA8L3EtaXRlbT5cblxuICAgICAgICAgIDwvcS1saXN0PlxuXG4gICAgICAgICAgPCEtLSDQn9C70LDQstCw0Y7RidCw0Y8g0LrQvdC+0L/QutCwINC00L7QsdCw0LLQu9C10L3QuNGPINC90L7QstC+0LPQviDQvNCw0YLQtdGA0LjQsNC70LAgLS0+XG4gICAgICAgICAgPHEtYnRuXG4gICAgICAgICAgICBpY29uPVwiYWRkXCJcbiAgICAgICAgICAgIHJvdW5kXG4gICAgICAgICAgICBjbGFzcz1cImZhYiBiZy15ZWxsb3cgdGV4dC1ibGFja1wiXG4gICAgICAgICAgICBAY2xpY2s9XCJzaG93QWRkTmV3TWF0ZXJpYWxEaWFsb2cgPSB0cnVlXCJcbiAgICAgICAgICAgIHNpemU9XCIyMHB4XCJcbiAgICAgICAgICAvPlxuXG4gICAgICAgICAgPHEtYnRuXG4gICAgICAgICAgICBpY29uPVwic3RvcmFnZVwiXG4gICAgICAgICAgICByb3VuZFxuICAgICAgICAgICAgY2xhc3M9XCJiZy15ZWxsb3cgdGV4dC1ibGFja1wiXG4gICAgICAgICAgICBzaXplPVwiMThcIlxuICAgICAgICAgICAgQGNsaWNrPVwic2hvd0FkZFByb2R1Y3RGcm9tU3RvcmVEaWFsb2cgPSB0cnVlXCJcbiAgICAgICAgICAgIHN0eWxlPVwicG9zaXRpb246IGZpeGVkOyBib3R0b206IDEwMHB4OyByaWdodDogMTZweDsgei1pbmRleDogMTAwMFwiXG4gICAgICAgICAgLz5cblxuXG4gICAgICAgIDwvcS10YWItcGFuZWw+XG4gICAgICA8L3EtdGFiLXBhbmVscz5cbiAgICA8L3EtY2FyZD5cbiAgPC9kaXY+XG5cbiAgPGRpdj5cbiAgICA8cS1kaWFsb2cgdi1tb2RlbD1cInNob3dBZGROZXdNYXRlcmlhbERpYWxvZ1wiIHBlcnNpc3RlbnQ+XG4gICAgICA8cS1jYXJkPlxuICAgICAgICA8cS1jYXJkLXNlY3Rpb24+XG4gICAgICAgICAgPGRpdiBjbGFzcz1cInRleHQtaDZcIj7QlNC+0LHQsNCy0LvQtdC90LjQtSDQvNCw0YLQtdGA0LjQsNC70LA8L2Rpdj5cbiAgICAgICAgICA8cS1pbnB1dCB2LW1vZGVsPVwibmV3TWF0ZXJpYWwubmFtZVwiIGxhYmVsLWNvbG9yPVwieWVsbG93XCIgY29sb3I9XCJ5ZWxsb3dcIiBsYWJlbD1cItCd0LDQt9Cy0LDQvdC40LVcIiBvdXRsaW5lZCBjbGFzcz1cInEtbWItbWRcIiAvPlxuICAgICAgICAgIDxxLWlucHV0IHYtbW9kZWwubnVtYmVyPVwibmV3TWF0ZXJpYWwucHJpY2VcIiBsYWJlbD1cItCm0LXQvdCwXCIgbGFiZWwtY29sb3I9XCJ5ZWxsb3dcIiBjb2xvcj1cInllbGxvd1wiIHR5cGU9XCJudW1iZXJcIiBvdXRsaW5lZCBjbGFzcz1cInEtbWItbWRcIiAvPlxuICAgICAgICAgIDxxLWlucHV0IHYtbW9kZWwubnVtYmVyPVwibmV3TWF0ZXJpYWwuYW1vdW50XCIgbGFiZWw9XCLQmtC+0LvQuNGH0LXRgdGC0LLQvlwiIGxhYmVsLWNvbG9yPVwieWVsbG93XCIgY29sb3I9XCJ5ZWxsb3dcIiB0eXBlPVwibnVtYmVyXCIgb3V0bGluZWQgLz5cbiAgICAgICAgPC9xLWNhcmQtc2VjdGlvbj5cbiAgICAgICAgPHEtY2FyZC1hY3Rpb25zIGFsaWduPVwicmlnaHRcIj5cbiAgICAgICAgICA8cS1idG4gZmxhdCBsYWJlbD1cItCe0YLQvNC10L3QsFwiIGNvbG9yPVwieWVsbG93XCIgQGNsaWNrPVwiY2xvc2VEaWFsb2dcIiAvPlxuICAgICAgICAgIDxxLWJ0biBmbGF0IGxhYmVsPVwi0JTQvtCx0LDQstC40YLRjFwiIGNvbG9yPVwieWVsbG93XCIgQGNsaWNrPVwiYWRkTWF0ZXJpYWxcIiAvPlxuICAgICAgICA8L3EtY2FyZC1hY3Rpb25zPlxuICAgICAgPC9xLWNhcmQ+XG4gICAgPC9xLWRpYWxvZz5cbiAgPC9kaXY+XG5cbiAgPGRpdj5cbiAgICA8cS1kaWFsb2cgdi1tb2RlbD1cInNob3dBZGROZXdTZXJ2aWNlRGlhbG9nXCIgcGVyc2lzdGVudD5cbiAgICAgIDxxLWNhcmQ+XG4gICAgICAgIDxxLWNhcmQtc2VjdGlvbj5cbiAgICAgICAgICA8ZGl2IGNsYXNzPVwidGV4dC1oNlwiPtCU0L7QsdCw0LLQu9C10L3QuNC1INGB0LXRgNCy0LjRgdCwPC9kaXY+XG4gICAgICAgICAgPHEtaW5wdXQgdi1tb2RlbD1cIm5ld1NlcnZpY2UubmFtZVwiIGxhYmVsLWNvbG9yPVwieWVsbG93XCIgY29sb3I9XCJ5ZWxsb3dcIiBsYWJlbD1cItCd0LDQt9Cy0LDQvdC40LVcIiBvdXRsaW5lZCBjbGFzcz1cInEtbWItbWRcIiAvPlxuICAgICAgICAgIDxxLWlucHV0IHYtbW9kZWwubnVtYmVyPVwibmV3U2VydmljZS5wcmljZVwiIGxhYmVsPVwi0KbQtdC90LBcIiBsYWJlbC1jb2xvcj1cInllbGxvd1wiIGNvbG9yPVwieWVsbG93XCIgdHlwZT1cIm51bWJlclwiIG91dGxpbmVkIGNsYXNzPVwicS1tYi1tZFwiIC8+XG4gICAgICAgIDwvcS1jYXJkLXNlY3Rpb24+XG4gICAgICAgIDxxLWNhcmQtYWN0aW9ucyBhbGlnbj1cInJpZ2h0XCI+XG4gICAgICAgICAgPHEtYnRuIGZsYXQgbGFiZWw9XCLQntGC0LzQtdC90LBcIiBjb2xvcj1cInllbGxvd1wiIEBjbGljaz1cImNsb3NlRGlhbG9nXCIgLz5cbiAgICAgICAgICA8cS1idG4gZmxhdCBsYWJlbD1cItCU0L7QsdCw0LLQuNGC0YxcIiBjb2xvcj1cInllbGxvd1wiIEBjbGljaz1cImFkZE5ld1NlcnZpY2VcIiAvPlxuICAgICAgICA8L3EtY2FyZC1hY3Rpb25zPlxuICAgICAgPC9xLWNhcmQ+XG4gICAgPC9xLWRpYWxvZz5cbiAgPC9kaXY+XG5cbiAgPGRpdj5cbiAgICA8cS1kaWFsb2cgdi1tb2RlbD1cInNob3dBZGROZXdDbGllbnREaWFsb2dcIiBwZXJzaXN0ZW50PlxuICAgICAgPHEtY2FyZD5cbiAgICAgICAgPHEtY2FyZC1zZWN0aW9uPlxuICAgICAgICAgIDxkaXYgY2xhc3M9XCJ0ZXh0LWg2XCI+0JTQvtCx0LDQstC70LXQvdC40LUg0LrQu9C40LXQvdGC0LA8L2Rpdj5cbiAgICAgICAgICA8cS1pbnB1dCB2LW1vZGVsPVwibmV3Q2xpZW50Lm5hbWVcIiBsYWJlbC1jb2xvcj1cInllbGxvd1wiIGNvbG9yPVwieWVsbG93XCIgbGFiZWw9XCLQmNC80Y8g0LrQu9C40LXQvdGC0LBcIiBvdXRsaW5lZCBjbGFzcz1cInEtbWItbWRcIiAvPlxuICAgICAgICAgIDxxLWlucHV0IHYtbW9kZWwubnVtYmVyPVwibmV3Q2xpZW50LnBob25lXCIgbGFiZWw9XCLRgtC10LvQtdGE0L7QvVwiIGxhYmVsLWNvbG9yPVwieWVsbG93XCIgY29sb3I9XCJ5ZWxsb3dcIiB0eXBlPVwidGV4dFwiIG91dGxpbmVkIGNsYXNzPVwicS1tYi1tZFwiIC8+XG4gICAgICAgIDwvcS1jYXJkLXNlY3Rpb24+XG4gICAgICAgIDxxLWNhcmQtYWN0aW9ucyBhbGlnbj1cInJpZ2h0XCI+XG4gICAgICAgICAgPHEtYnRuIGZsYXQgbGFiZWw9XCLQntGC0LzQtdC90LBcIiBjb2xvcj1cInllbGxvd1wiIEBjbGljaz1cImNsb3NlRGlhbG9nXCIgLz5cbiAgICAgICAgICA8cS1idG4gZmxhdCBsYWJlbD1cItCU0L7QsdCw0LLQuNGC0YxcIiBjb2xvcj1cInllbGxvd1wiIEBjbGljaz1cImFkZE5ld0NsaWVudFwiIC8+XG4gICAgICAgIDwvcS1jYXJkLWFjdGlvbnM+XG4gICAgICA8L3EtY2FyZD5cbiAgICA8L3EtZGlhbG9nPlxuICA8L2Rpdj5cblxuICA8ZGl2PlxuICAgIDxxLWRpYWxvZyB2LW1vZGVsPVwic2hvd0FkZE5ld01vZGVsRGlhbG9nXCIgcGVyc2lzdGVudD5cbiAgICAgIDxxLWNhcmQ+XG4gICAgICAgIDxxLWNhcmQtc2VjdGlvbj5cbiAgICAgICAgICA8ZGl2IGNsYXNzPVwidGV4dC1oNlwiPtCU0L7QsdCw0LLQu9C10L3QuNC1INC80L7QtNC10LvQuDwvZGl2PlxuICAgICAgICAgIDxxLWlucHV0IHYtbW9kZWw9XCJuZXdNb2RlbC5uYW1lXCIgbGFiZWwtY29sb3I9XCJ5ZWxsb3dcIiBjb2xvcj1cInllbGxvd1wiIGxhYmVsPVwi0J3QsNC30LLQsNC90LjQtSDQvNC+0LTQtdC70LhcIiBvdXRsaW5lZCBjbGFzcz1cInEtbWItbWRcIiAvPlxuICAgICAgICA8L3EtY2FyZC1zZWN0aW9uPlxuICAgICAgICA8cS1jYXJkLWFjdGlvbnMgYWxpZ249XCJyaWdodFwiPlxuICAgICAgICAgIDxxLWJ0biBmbGF0IGxhYmVsPVwi0J7RgtC80LXQvdCwXCIgY29sb3I9XCJ5ZWxsb3dcIiBAY2xpY2s9XCJjbG9zZURpYWxvZ1wiIC8+XG4gICAgICAgICAgPHEtYnRuIGZsYXQgbGFiZWw9XCLQlNC+0LHQsNCy0LjRgtGMXCIgY29sb3I9XCJ5ZWxsb3dcIiBAY2xpY2s9XCJhZGROZXdNb2RlbFwiIC8+XG4gICAgICAgIDwvcS1jYXJkLWFjdGlvbnM+XG4gICAgICA8L3EtY2FyZD5cbiAgICA8L3EtZGlhbG9nPlxuICA8L2Rpdj5cblxuICA8ZGl2PlxuICAgIDxxLWRpYWxvZyB2LW1vZGVsPVwic2hvd0FkZFByb2R1Y3RGcm9tU3RvcmVEaWFsb2dcIiBwZXJzaXN0ZW50PlxuICAgICAgPHEtY2FyZD5cbiAgICAgICAgPHEtY2FyZC1zZWN0aW9uPlxuICAgICAgICAgIDxkaXYgY2xhc3M9XCJ0ZXh0LWg2XCI+0JTQvtCx0LDQstC70LXQvdC40LUg0YLQvtCy0LDRgNCwINGB0L4g0YHQutC70LDQtNCwPC9kaXY+XG4gICAgICAgICAgPHEtc2VsZWN0IHYtbW9kZWw9XCJzZWxlY3RlZFByb2R1Y3RDYXRlZ29yeVwiXG4gICAgICAgICAgICAgICAgICAgIDpvcHRpb25zPVwicHJvZHVjdENhdGVnb3JpZXNcIlxuICAgICAgICAgICAgICAgICAgICBvcHRpb24tbGFiZWw9XCJuYW1lXCJcbiAgICAgICAgICAgICAgICAgICAgbGFiZWw9XCLQktGL0LHQtdGA0LjRgtC1INC60LDRgtC10LPQvtGA0LjRjlwiXG4gICAgICAgICAgICAgICAgICAgIEB1cGRhdGU6bW9kZWwtdmFsdWU9XCJnZXRQcm9kdWN0c0J5Q2F0ZWdvcnlcIlxuICAgICAgICAgICAgICAgICAgICBsYWJlbC1jb2xvcj1cInllbGxvd1wiXG4gICAgICAgICAgLz5cbiAgICAgICAgICA8cS1zZWxlY3Qgdi1tb2RlbD1cInNlbGVjdGVkU3RvcmVQcm9kdWN0XCJcbiAgICAgICAgICAgICAgICAgICAgOm9wdGlvbnM9XCJzdG9yZVByb2R1Y3RzXCJcbiAgICAgICAgICAgICAgICAgICAgb3B0aW9uLWxhYmVsPVwibmFtZVwiXG4gICAgICAgICAgICAgICAgICAgIGxhYmVsPVwi0LLRi9Cx0LXRgNC40YLQtSDRgtC+0LLQsNGAXCJcbiAgICAgICAgICAgICAgICAgICAgbGFiZWwtY29sb3I9XCJ5ZWxsb3dcIlxuICAgICAgICAgIC8+XG4gICAgICAgIDwvcS1jYXJkLXNlY3Rpb24+XG5cbiAgICAgICAgPHEtY2FyZC1hY3Rpb25zIGFsaWduPVwicmlnaHRcIj5cbiAgICAgICAgICA8cS1idG4gZmxhdCBsYWJlbD1cItC+0YLQvNC10L3QsFwiIGNvbG9yPVwieWVsbG93XCIgQGNsaWNrPVwic2hvd0FkZFByb2R1Y3RGcm9tU3RvcmVEaWFsb2c9ZmFsc2VcIiAvPlxuICAgICAgICAgIDxxLWJ0biBmbGF0IGxhYmVsPVwi0LTQvtCx0LDQstC40YLRjFwiIGNvbG9yPVwieWVsbG93XCIgQGNsaWNrPVwiYWRkUHJvZHVjdEZyb21TdG9yZVwiIC8+XG4gICAgICAgIDwvcS1jYXJkLWFjdGlvbnM+XG4gICAgICA8L3EtY2FyZD5cbiAgICA8L3EtZGlhbG9nPlxuICA8L2Rpdj5cblxuICA8RGVsZXRlQ29uZmlybVBhZ2UgcmVmPVwiZGVsZXRlQ29uZmlybVBhZ2VcIiAvPlxuXG48L3RlbXBsYXRlPlxuXG48c3R5bGUgc2NvcGVkPlxuXG4uc2VsZWN0U2VydmljZTpob3ZlciB7XG4gIGJhY2tncm91bmQtY29sb3I6IHllbGxvdztcbiAgY29sb3I6IGJsYWNrXG59XG5cbi5mYWIge1xuICBwb3NpdGlvbjogZml4ZWQ7XG4gIGJvdHRvbTogMTZweDtcbiAgcmlnaHQ6IDE2cHg7XG4gIHotaW5kZXg6IDEwMDA7IC8qINGH0YLQvtCx0Ysg0LrQvdC+0L/QutCwINCx0YvQu9CwINC/0L7QstC10YDRhSDQvtGB0YLQsNC70YzQvdGL0YUg0Y3Qu9C10LzQtdC90YLQvtCyICovXG59XG5cbjwvc3R5bGU+XG4iXSwibmFtZXMiOlsic2VsZWN0ZWRQcm9kdWN0Q2F0ZWdvcnkiLCJfb3BlbkJsb2NrIiwiX2NyZWF0ZUVsZW1lbnRCbG9jayIsIl9GcmFnbWVudCIsIl9jcmVhdGVFbGVtZW50Vk5vZGUiLCJfY3JlYXRlQmxvY2siLCJfY3JlYXRlQ29tbWVudFZOb2RlIiwiX2NyZWF0ZVZOb2RlIiwiX3dpdGhDdHgiLCJfY3JlYXRlVGV4dFZOb2RlIiwiX3JlbmRlckxpc3QiLCJfdG9EaXNwbGF5U3RyaW5nIiwiX3ZTaG93IiwiX25vcm1hbGl6ZUNsYXNzIl0sIm1hcHBpbmdzIjoiOzs7Ozs7Ozs7OztBQUtBLE1BQUEsWUFBZSxnQkFBZ0I7QUFBQSxFQUM3QixNQUFNO0FBQUEsRUFFTixPQUFPO0FBQUEsSUFDTCxZQUFZO0FBQUEsSUFDWixTQUFTO0FBQUEsSUFDVCxNQUFNO0FBQUEsSUFD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
