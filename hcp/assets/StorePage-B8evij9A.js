import { Q as QSelect } from "./QSelect-DGItPn-E.js";
import { a as createComponent, h, V as createDirective, W as getPortalProxy, X as closePortals, Y as isKeyCode, _ as _export_sfc, r as ref, b as computed, D as openBlock, K as createElementBlock, G as createVNode, F as withCtx, Z as QCard, $ as QCardSection, R as createBaseVNode, S as toDisplayString, P as withDirectives, O as QBtn, a0 as QInput, a1 as QCardActions, E as createBlock, I as createCommentVNode, a2 as QDialog, L as Fragment, n as onMounted, M as createTextVNode, N as renderList, U as Ripple } from "./index-74sOg8Nl.js";
import { Q as QItemLabel, a as QItem, b as QItemSection } from "./QItem-DuqkKkh7.js";
import { D as DeleteConfirmPage, Q as QList } from "./DeleteConfirmPage-DjUGgkyk.js";
import { api } from "./axios-D58jYJIV.js";
import { u as useSpecializationsStore } from "./specializations-B0lcZ67D.js";
import "./rtl-DDpZOXNn.js";
const QSpace = createComponent({
  name: "QSpace",
  setup() {
    const space = h("div", { class: "q-space" });
    return () => space;
  }
});
function getDepth(value) {
  if (value === false) {
    return 0;
  }
  if (value === true || value === void 0) {
    return 1;
  }
  const depth = parseInt(value, 10);
  return isNaN(depth) ? 0 : depth;
}
const ClosePopup = createDirective(
  {
    name: "close-popup",
    beforeMount(el, { value }) {
      const ctx = {
        depth: getDepth(value),
        handler(evt) {
          ctx.depth !== 0 && setTimeout(() => {
            const proxy = getPortalProxy(el);
            if (proxy !== void 0) {
              closePortals(proxy, evt, ctx.depth);
            }
          });
        },
        handlerKey(evt) {
          isKeyCode(evt, 13) === true && ctx.handler(evt);
        }
      };
      el.__qclosepopup = ctx;
      el.addEventListener("click", ctx.handler);
      el.addEventListener("keyup", ctx.handlerKey);
    },
    updated(el, { value, oldValue }) {
      if (value !== oldValue) {
        el.__qclosepopup.depth = getDepth(value);
      }
    },
    beforeUnmount(el) {
      const ctx = el.__qclosepopup;
      el.removeEventListener("click", ctx.handler);
      el.removeEventListener("keyup", ctx.handlerKey);
      delete el.__qclosepopup;
    }
  }
);
const _sfc_main$3 = {
  __name: "ProductCategoryDialogPage",
  emits: ["product-saved"],
  setup(__props, { expose: __expose, emit: __emit }) {
    const deleteConfirmPage = ref(null);
    const specializationStore = useSpecializationsStore();
    const emit = __emit;
    const currentCategory = ref(null);
    const showDialog = ref(false);
    const name = ref(null);
    const editMode = computed(() => !!currentCategory.value);
    const open = (category) => {
      currentCategory.value = category ? { ...category } : null;
      name.value = category?.name || "";
      showDialog.value = true;
    };
    const saveProductCategory = async () => {
      try {
        if (editMode.value) {
          console.log("редактирование продукта не реализовано");
          await api.post(`/edit_product_category`, {
            id: currentCategory.value.id,
            name: name.value,
            specialization_id: specializationStore.getSelectedSpecialization.id
          });
          showDialog.value = false;
          emit("product-category-saved");
        } else {
          console.log("создаем новый продукт");
          await api.post(`/add_product_category`, {
            name: name.value,
            specialization_id: specializationStore.getSelectedSpecialization.id
          });
          showDialog.value = false;
          emit("product-category-saved");
        }
      } catch (err) {
        console.error(err);
        currentCategory.value = null;
        close();
      }
    };
    const deleteCategory = async () => {
      if (!currentCategory.value) return;
      deleteConfirmPage.value.open(
        "Подтвердите удаление",
        `Вы уверены, что хотите удалить категорию "${currentCategory.value.name}"?`,
        async () => {
          try {
            await api.post(`/delete_product_category`, {
              productCategoryId: currentCategory.value.id
            });
            emit("product-category-saved");
            showDialog.value = false;
          } catch (err) {
            console.error("Ошибка удаления категории", err);
          }
        }
      );
    };
    __expose({ open });
    const __returned__ = { deleteConfirmPage, specializationStore, emit, currentCategory, showDialog, name, editMode, open, saveProductCategory, deleteCategory, ref, computed, get api() {
      return api;
    }, get useSpecializationsStore() {
      return useSpecializationsStore;
    }, DeleteConfirmPage };
    Object.defineProperty(__returned__, "__isScriptSetup", { enumerable: false, value: true });
    return __returned__;
  }
};
const _hoisted_1$3 = { class: "q-ml-sm text-h6" };
const _hoisted_2$3 = { class: "q-gutter-y-md" };
function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
  return openBlock(), createElementBlock(Fragment, null, [
    createVNode(QDialog, {
      modelValue: $setup.showDialog,
      "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.showDialog = $event),
      persistent: ""
    }, {
      default: withCtx(() => [
        createVNode(QCard, { style: { "min-width": "400px" } }, {
          default: withCtx(() => [
            createVNode(QCardSection, { class: "row items-center" }, {
              default: withCtx(() => [
                createBaseVNode("span", _hoisted_1$3, toDisplayString($setup.editMode ? "Редактирование" : "Новая категория"), 1),
                createVNode(QSpace),
                withDirectives(createVNode(QBtn, {
                  icon: "close",
                  flat: "",
                  round: "",
                  dense: ""
                }, null, 512), [
                  [ClosePopup]
                ])
              ]),
              _: 1
            }),
            createVNode(QCardSection, null, {
              default: withCtx(() => [
                createBaseVNode("div", _hoisted_2$3, [
                  createVNode(QInput, {
                    modelValue: $setup.name,
                    "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.name = $event),
                    outlined: "",
                    label: "название категории",
                    placeholder: "Введите название",
                    class: "q-mb-md"
                  }, null, 8, ["modelValue"])
                ])
              ]),
              _: 1
            }),
            createVNode(QCardActions, { align: "right" }, {
              default: withCtx(() => [
                withDirectives(createVNode(QBtn, {
                  flat: "",
                  label: "Отмена",
                  color: "yellow"
                }, null, 512), [
                  [ClosePopup]
                ]),
                createVNode(QBtn, {
                  label: "Сохранить",
                  "text-color": "yellow",
                  onClick: $setup.saveProductCategory
                }),
                $setup.currentCategory ? (openBlock(), createBlock(QBtn, {
                  key: 0,
                  label: "Удалить",
                  flat: "",
                  color: "yellow",
                  onClick: $setup.deleteCategory
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
    createVNode($setup["DeleteConfirmPage"], { ref: "deleteConfirmPage" }, null, 512)
  ], 64);
}
const ProductCategoryDialogPage = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3], ["__file", "ProductCategoryDialogPage.vue"]]);
const _sfc_main$2 = {
  __name: "ArrivalProductDialogPage",
  emits: ["product-arrival-saved"],
  setup(__props, { expose: __expose, emit: __emit }) {
    const deleteConfirmPage = ref(null);
    const emit = __emit;
    const currentProduct = ref(null);
    const showDialog = ref(false);
    const name = ref(null);
    const byPrice = ref(null);
    const arrivalQuantity = ref(null);
    const baseSalePrice = ref(null);
    const open = (product) => {
      console.log("открытие диалогового окна поступления товара");
      currentProduct.value = product ? { ...product } : null;
      name.value = product?.name || "";
      baseSalePrice.value = product?.base_sale_price || "";
      showDialog.value = true;
      console.log("product: ", product);
    };
    const makeArrivalProduct = async () => {
      try {
        if (currentProduct.value) {
          console.log("запрос на поступление товара");
          await api.post(`/arrival_product`, {
            product_id: currentProduct.value.id,
            base_sale_price: baseSalePrice.value,
            by_price: byPrice.value,
            arrival_quantity: arrivalQuantity.value
          });
          showDialog.value = false;
          emit("product-category-saved");
        } else {
          console.log("currentProduct: ", currentProduct.value);
        }
      } catch (err) {
        console.error(err);
        currentProduct.value = null;
        close();
      }
    };
    __expose({ open });
    const __returned__ = { deleteConfirmPage, emit, currentProduct, showDialog, name, byPrice, arrivalQuantity, baseSalePrice, open, makeArrivalProduct, ref, get api() {
      return api;
    }, DeleteConfirmPage };
    Object.defineProperty(__returned__, "__isScriptSetup", { enumerable: false, value: true });
    return __returned__;
  }
};
const _hoisted_1$2 = { class: "q-gutter-y-md" };
const _hoisted_2$2 = { class: "q-gutter-y-md" };
const _hoisted_3$2 = { class: "q-gutter-y-md" };
function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
  return openBlock(), createElementBlock(Fragment, null, [
    createVNode(QDialog, {
      modelValue: $setup.showDialog,
      "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => $setup.showDialog = $event),
      persistent: ""
    }, {
      default: withCtx(() => [
        createVNode(QCard, { style: { "min-width": "400px" } }, {
          default: withCtx(() => [
            createVNode(QCardSection, { class: "row items-center" }, {
              default: withCtx(() => [
                _cache[4] || (_cache[4] = createBaseVNode("span", { class: "q-ml-sm text-h6" }, " Поступление ", -1)),
                createVNode(QSpace),
                withDirectives(createVNode(QBtn, {
                  icon: "close",
                  flat: "",
                  round: "",
                  dense: ""
                }, null, 512), [
                  [ClosePopup]
                ])
              ]),
              _: 1
            }),
            createVNode(QCardSection, null, {
              default: withCtx(() => [
                createBaseVNode("div", _hoisted_1$2, [
                  createVNode(QInput, {
                    modelValue: $setup.byPrice,
                    "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.byPrice = $event),
                    outlined: "",
                    label: "цена закупки",
                    placeholder: "введите цену закупки",
                    class: "q-mb-md"
                  }, null, 8, ["modelValue"])
                ])
              ]),
              _: 1
            }),
            createVNode(QCardSection, null, {
              default: withCtx(() => [
                createBaseVNode("div", _hoisted_2$2, [
                  createVNode(QInput, {
                    modelValue: $setup.baseSalePrice,
                    "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.baseSalePrice = $event),
                    outlined: "",
                    label: "цена продажи",
                    placeholder: "Введите цену продажи",
                    class: "q-mb-md"
                  }, null, 8, ["modelValue"])
                ])
              ]),
              _: 1
            }),
            createVNode(QCardSection, null, {
              default: withCtx(() => [
                createBaseVNode("div", _hoisted_3$2, [
                  createVNode(QInput, {
                    modelValue: $setup.arrivalQuantity,
                    "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => $setup.arrivalQuantity = $event),
                    outlined: "",
                    label: "количество поступило",
                    placeholder: "Введите количество поступления",
                    class: "q-mb-md"
                  }, null, 8, ["modelValue"])
                ])
              ]),
              _: 1
            }),
            createVNode(QCardActions, { align: "right" }, {
              default: withCtx(() => [
                withDirectives(createVNode(QBtn, {
                  flat: "",
                  label: "Отмена",
                  color: "yellow"
                }, null, 512), [
                  [ClosePopup]
                ]),
                createVNode(QBtn, {
                  label: "Сохранить",
                  "text-color": "yellow",
                  onClick: $setup.makeArrivalProduct
                })
              ]),
              _: 1
            })
          ]),
          _: 1
        })
      ]),
      _: 1
    }, 8, ["modelValue"]),
    createVNode($setup["DeleteConfirmPage"], { ref: "deleteConfirmPage" }, null, 512)
  ], 64);
}
const ArrivalProductDialogPage = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2], ["__file", "ArrivalProductDialogPage.vue"]]);
const _sfc_main$1 = {
  __name: "ProductDialogPage",
  emits: ["product-saved"],
  setup(__props, { expose: __expose, emit: __emit }) {
    const deleteConfirmPage = ref(null);
    const arrivalConfirmPage = ref(null);
    const specializationStore = useSpecializationsStore();
    const emit = __emit;
    const currentProduct = ref(null);
    const currentProductCategory = ref(null);
    const showDialog = ref(false);
    const name = ref(null);
    const baseSalePrice = ref(null);
    const editMode = ref(false);
    const newProductMode = ref(false);
    const open = (product, productCategory) => {
      console.log("открытие диалогового продукта");
      editMode.value = false;
      newProductMode.value = false;
      currentProduct.value = product ? { ...product } : null;
      currentProductCategory.value = productCategory || null;
      name.value = product?.name || "";
      baseSalePrice.value = product?.base_sale_price || "";
      if (currentProductCategory.value) {
        editMode.value = true;
        newProductMode.value = true;
      } else {
        editMode.value = false;
      }
      showDialog.value = true;
      console.log("currentProductCategory:", currentProductCategory.value);
      console.log("product: ", product);
      console.log("productCategory: ", productCategory);
      console.log("editMode : ", editMode.value);
      console.log("newProductMode : ", newProductMode.value);
    };
    const saveProduct = async () => {
      try {
        if (editMode.value) {
          console.log("редактирование продукта не реализовано");
          await api.post(`/edit_product`, {
            id: currentProduct.value.id,
            name: name.value,
            specialization_id: specializationStore.getSelectedSpecialization.id
          });
          showDialog.value = false;
          emit("product-category-saved");
        } else {
          console.log("создаем новый продукт: ", currentProduct.value);
          await api.post(`/add_product`, {
            name: name.value,
            base_sale_price: baseSalePrice.value,
            product_category_id: currentProductCategory.value.id
          });
          showDialog.value = false;
          emit("product-added");
        }
      } catch (err) {
        console.error(err);
        currentProduct.value = null;
        close2();
      }
    };
    const deleteCategory = async () => {
      if (!currentProduct.value) return;
      deleteConfirmPage.value.open(
        "Подтвердите удаление",
        `Вы уверены, что хотите удалить товар "${currentProduct.value.name}"?`,
        async () => {
          try {
            await api.post(`/delete_product`, {
              productId: currentProduct.value.id
            });
            emit("product-saved");
            showDialog.value = false;
          } catch (err) {
            console.error("Ошибка удаления товара", err);
          }
        }
      );
    };
    const close2 = () => {
      console.log("закрываем окно");
      editMode.value = false;
      newProductMode.value = false;
    };
    const openArrivalProductDialog = () => {
      console.log("arrival not realized");
      arrivalConfirmPage.value.open(currentProduct.value);
    };
    __expose({ open });
    const __returned__ = { deleteConfirmPage, arrivalConfirmPage, specializationStore, emit, currentProduct, currentProductCategory, showDialog, name, baseSalePrice, editMode, newProductMode, open, saveProduct, deleteCategory, close: close2, openArrivalProductDialog, ref, get api() {
      return api;
    }, get useSpecializationsStore() {
      return useSpecializationsStore;
    }, DeleteConfirmPage, ArrivalProductDialogPage };
    Object.defineProperty(__returned__, "__isScriptSetup", { enumerable: false, value: true });
    return __returned__;
  }
};
const _hoisted_1$1 = {
  key: 0,
  class: "q-ml-sm text-h6"
};
const _hoisted_2$1 = {
  key: 1,
  class: "q-ml-sm text-h6"
};
const _hoisted_3$1 = {
  key: 2,
  class: "q-ml-sm text-h6"
};
const _hoisted_4 = { class: "q-gutter-y-md" };
const _hoisted_5 = { class: "q-gutter-y-md" };
function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
  return openBlock(), createElementBlock(Fragment, null, [
    createVNode(QDialog, {
      modelValue: $setup.showDialog,
      "onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => $setup.showDialog = $event),
      persistent: ""
    }, {
      default: withCtx(() => [
        createVNode(QCard, { style: { "min-width": "400px" } }, {
          default: withCtx(() => [
            createVNode(QCardSection, { class: "row items-center" }, {
              default: withCtx(() => [
                $setup.newProductMode ? (openBlock(), createElementBlock("span", _hoisted_1$1, " Новый товар ")) : createCommentVNode("", true),
                $setup.editMode && !$setup.newProductMode ? (openBlock(), createElementBlock("span", _hoisted_2$1, " редактирование ")) : createCommentVNode("", true),
                !$setup.editMode && !$setup.newProductMode ? (openBlock(), createElementBlock("span", _hoisted_3$1, " товар ")) : createCommentVNode("", true),
                createVNode(QSpace),
                withDirectives(createVNode(QBtn, {
                  icon: "close",
                  flat: "",
                  round: "",
                  dense: ""
                }, null, 512), [
                  [ClosePopup]
                ])
              ]),
              _: 1
            }),
            createVNode(QCardSection, null, {
              default: withCtx(() => [
                createBaseVNode("div", _hoisted_4, [
                  createVNode(QInput, {
                    modelValue: $setup.name,
                    "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $setup.name = $event),
                    outlined: "",
                    label: "название продукта",
                    placeholder: "Введите название",
                    class: "q-mb-md",
                    disable: !$setup.editMode
                  }, null, 8, ["modelValue", "disable"])
                ])
              ]),
              _: 1
            }),
            createVNode(QCardSection, null, {
              default: withCtx(() => [
                createBaseVNode("div", _hoisted_5, [
                  createVNode(QInput, {
                    modelValue: $setup.baseSalePrice,
                    "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $setup.baseSalePrice = $event),
                    outlined: "",
                    label: "цена продажи",
                    placeholder: "Введите цену продажи",
                    class: "q-mb-md",
                    disable: !$setup.editMode
                  }, null, 8, ["modelValue", "disable"])
                ])
              ]),
              _: 1
            }),
            createVNode(QCardActions, { align: "right" }, {
              default: withCtx(() => [
                $setup.editMode ? withDirectives((openBlock(), createBlock(QBtn, {
                  key: 0,
                  flat: "",
                  label: "Отмена",
                  color: "yellow",
                  onClick: _cache[2] || (_cache[2] = ($event) => {
                    $setup.editMode.value = false;
                    $setup.newProductMode.value = false;
                    $setup.currentProductCategory.value = "";
                  })
                }, null, 512)), [
                  [ClosePopup]
                ]) : createCommentVNode("", true),
                !$setup.editMode ? withDirectives((openBlock(), createBlock(QBtn, {
                  key: 1,
                  flat: "",
                  label: "закрыть",
                  color: "yellow",
                  onClick: _cache[3] || (_cache[3] = ($event) => {
                    $setup.newProductMode.value = false;
                    $setup.editMode.value = false;
                    $setup.currentProductCategory.value = "";
                  })
                }, null, 512)), [
                  [ClosePopup]
                ]) : createCommentVNode("", true),
                $setup.editMode ? (openBlock(), createBlock(QBtn, {
                  key: 2,
                  label: "Сохранить",
                  "text-color": "yellow",
                  onClick: $setup.saveProduct
                })) : createCommentVNode("", true),
                !$setup.editMode ? (openBlock(), createBlock(QBtn, {
                  key: 3,
                  label: "редактировать",
                  "text-color": "yellow",
                  onClick: _cache[4] || (_cache[4] = ($event) => $setup.editMode = true)
                })) : createCommentVNode("", true),
                !$setup.editMode ? (openBlock(), createBlock(QBtn, {
                  key: 4,
                  label: "Удалить",
                  flat: "",
                  color: "yellow",
                  onClick: $setup.deleteCategory
                })) : createCommentVNode("", true),
                !$setup.editMode ? (openBlock(), createBlock(QBtn, {
                  key: 5,
                  label: "Поступление",
                  "text-color": "yellow",
                  onClick: $setup.openArrivalProductDialog
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
    createVNode($setup["DeleteConfirmPage"], { ref: "deleteConfirmPage" }, null, 512),
    createVNode($setup["ArrivalProductDialogPage"], { ref: "arrivalConfirmPage" }, null, 512)
  ], 64);
}
const ProductDialogPage = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1], ["__file", "ProductDialogPage.vue"]]);
const _sfc_main = {
  __name: "StorePage",
  setup(__props, { expose: __expose }) {
    __expose();
    const specializationStore = useSpecializationsStore();
    const selectedSpecializationId = specializationStore.getSelectedSpecialization;
    const productCategories = ref([]);
    const products = ref([]);
    const selectedProductCategory = ref(null);
    const selectedProduct = ref(null);
    const productDialog = ref(null);
    const productCategoryDialog = ref(null);
    onMounted(() => {
      getProductCategories();
    });
    const getProductCategories = async () => {
      console.log("selectedSpecializationId: ", selectedSpecializationId);
      try {
        const response = await api.get(`/get_product_categories/${selectedSpecializationId.id}`);
        productCategories.value = response.data;
      } catch (err) {
        console.error(err);
      }
    };
    const getProductsByProductCategories = async () => {
      try {
        const response = await api.get(`/get_product_stocks/${selectedProductCategory.value.id}`);
        products.value = response.data;
        console.log("products: ", products.value);
      } catch (err) {
        console.error(err);
      }
    };
    const openAddProductCategoryDialog = () => {
      productCategoryDialog.value.open();
    };
    const openEditProductCategoryDialog = () => {
      console.log("открываетм редактирование категории: ", selectedProductCategory.value);
      if (!selectedProductCategory.value) return;
      productCategoryDialog.value.open(selectedProductCategory.value);
    };
    const openAddProductDialog = () => {
      selectedProduct.value = null;
      productDialog.value.open(null, selectedProductCategory.value, false);
    };
    const openDetailProductDialog = (product) => {
      selectedProduct.value = product;
      productDialog.value.open(product, null, true);
    };
    const handleProductCategorySaved = () => {
      getProductCategories();
      selectedProductCategory.value = null;
    };
    const handleProductAdded = () => {
      getProductsByProductCategories();
      selectedProduct.value = null;
    };
    const __returned__ = { specializationStore, selectedSpecializationId, productCategories, products, selectedProductCategory, selectedProduct, productDialog, productCategoryDialog, getProductCategories, getProductsByProductCategories, openAddProductCategoryDialog, openEditProductCategoryDialog, openAddProductDialog, openDetailProductDialog, handleProductCategorySaved, handleProductAdded, onMounted, ref, get api() {
      return api;
    }, get useSpecializationsStore() {
      return useSpecializationsStore;
    }, ProductCategoryDialogPage, ProductDialogPage };
    Object.defineProperty(__returned__, "__isScriptSetup", { enumerable: false, value: true });
    return __returned__;
  }
};
const _hoisted_1 = { class: "row items-center" };
const _hoisted_2 = { class: "col-auto self-end" };
const _hoisted_3 = { class: "col-auto self-end" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
  return openBlock(), createElementBlock(Fragment, null, [
    createBaseVNode("div", _hoisted_1, [
      createVNode(QSelect, {
        modelValue: $setup.selectedProductCategory,
        "onUpdate:modelValue": [
          _cache[0] || (_cache[0] = ($event) => $setup.selectedProductCategory = $event),
          $setup.getProductsByProductCategories
        ],
        options: $setup.productCategories,
        "option-label": "name",
        "emit-value": "",
        "map-options": "",
        label: "категория товара",
        dense: "",
        placeholder: "нет категорий",
        "label-color": "grey",
        color: "yellow",
        class: "col-9"
      }, null, 8, ["modelValue", "options"]),
      createBaseVNode("div", _hoisted_2, [
        createVNode(QBtn, {
          class: "col-1 text-yellow",
          onClick: $setup.openAddProductCategoryDialog
        }, {
          default: withCtx(() => _cache[1] || (_cache[1] = [
            createTextVNode("+")
          ])),
          _: 1
        })
      ]),
      createBaseVNode("div", _hoisted_3, [
        createVNode(QBtn, {
          class: "col-1 text-yellow",
          onClick: $setup.openEditProductCategoryDialog,
          icon: "edit"
        })
      ])
    ]),
    createVNode(QList, {
      bordered: "",
      separator: ""
    }, {
      default: withCtx(() => [
        !$setup.products ? (openBlock(), createBlock(QItemLabel, { key: 0 }, {
          default: withCtx(() => _cache[2] || (_cache[2] = [
            createTextVNode("нет товаров")
          ])),
          _: 1
        })) : createCommentVNode("", true),
        (openBlock(true), createElementBlock(Fragment, null, renderList($setup.products, (product) => {
          return withDirectives((openBlock(), createBlock(QItem, {
            key: product,
            class: "w-100 justify-between",
            style: { "width": "100%" },
            clickable: "",
            onClick: ($event) => $setup.openDetailProductDialog(product),
            "q-item": _ctx.qItem
          }, {
            default: withCtx(() => [
              createVNode(QItemSection, { class: "col-8" }, {
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
                  createVNode(QItemLabel, { class: "text-center" }, {
                    default: withCtx(() => [
                      createTextVNode(toDisplayString(product.quantity), 1)
                    ]),
                    _: 2
                  }, 1024)
                ]),
                _: 2
              }, 1024),
              createVNode(QItemSection, { class: "col-2" }, {
                default: withCtx(() => [
                  createVNode(QItemLabel, { class: "text-right" }, {
                    default: withCtx(() => [
                      createTextVNode(toDisplayString(product.base_sale_price), 1)
                    ]),
                    _: 2
                  }, 1024)
                ]),
                _: 2
              }, 1024)
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
      onClick: $setup.openAddProductDialog,
      size: "20px"
    }),
    createVNode($setup["ProductDialogPage"], {
      ref: "productDialog",
      onProductAdded: $setup.handleProductAdded
    }, null, 512),
    createVNode($setup["ProductCategoryDialogPage"], {
      ref: "productCategoryDialog",
      onProductCategorySaved: $setup.handleProductCategorySaved
    }, null, 512)
  ], 64);
}
const StorePage = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render], ["__scopeId", "data-v-b54eb96f"], ["__file", "StorePage.vue"]]);
export {
  StorePage as default
};
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiU3RvcmVQYWdlLUI4ZXZpajlBLmpzIiwic291cmNlcyI6WyIuLi8uLi8uLi9ub2RlX21vZHVsZXMvcXVhc2FyL3NyYy9jb21wb25lbnRzL3NwYWNlL1FTcGFjZS5qcyIsIi4uLy4uLy4uL25vZGVfbW9kdWxlcy9xdWFzYXIvc3JjL2RpcmVjdGl2ZXMvY2xvc2UtcG9wdXAvQ2xvc2VQb3B1cC5qcyIsIi4uLy4uLy4uL3NyYy9wYWdlcy9kaWFsb2dzL1Byb2R1Y3RDYXRlZ29yeURpYWxvZ1BhZ2UudnVlIiwiLi4vLi4vLi4vc3JjL3BhZ2VzL2RpYWxvZ3MvQXJyaXZhbFByb2R1Y3REaWFsb2dQYWdlLnZ1ZSIsIi4uLy4uLy4uL3NyYy9wYWdlcy9kaWFsb2dzL1Byb2R1Y3REaWFsb2dQYWdlLnZ1ZSIsIi4uLy4uLy4uL3NyYy9wYWdlcy9TdG9yZVBhZ2UudnVlIl0sInNvdXJjZXNDb250ZW50IjpbImltcG9ydCB7IGggfSBmcm9tICd2dWUnXG5cbmltcG9ydCB7IGNyZWF0ZUNvbXBvbmVudCB9IGZyb20gJy4uLy4uL3V0aWxzL3ByaXZhdGUuY3JlYXRlL2NyZWF0ZS5qcydcblxuZXhwb3J0IGRlZmF1bHQgY3JlYXRlQ29tcG9uZW50KHtcbiAgbmFtZTogJ1FTcGFjZScsXG5cbiAgc2V0dXAgKCkge1xuICAgIGNvbnN0IHNwYWNlID0gaCgnZGl2JywgeyBjbGFzczogJ3Etc3BhY2UnIH0pXG4gICAgcmV0dXJuICgpID0+IHNwYWNlXG4gIH1cbn0pXG4iLCJpbXBvcnQgeyBjcmVhdGVEaXJlY3RpdmUgfSBmcm9tICcuLi8uLi91dGlscy9wcml2YXRlLmNyZWF0ZS9jcmVhdGUuanMnXG5pbXBvcnQgeyBjbG9zZVBvcnRhbHMsIGdldFBvcnRhbFByb3h5IH0gZnJvbSAnLi4vLi4vdXRpbHMvcHJpdmF0ZS5wb3J0YWwvcG9ydGFsLmpzJ1xuaW1wb3J0IHsgaXNLZXlDb2RlIH0gZnJvbSAnLi4vLi4vdXRpbHMvcHJpdmF0ZS5rZXlib2FyZC9rZXktY29tcG9zaXRpb24uanMnXG5pbXBvcnQgZ2V0U1NSUHJvcHMgZnJvbSAnLi4vLi4vdXRpbHMvcHJpdmF0ZS5ub29wLXNzci1kaXJlY3RpdmUtdHJhbnNmb3JtL25vb3Atc3NyLWRpcmVjdGl2ZS10cmFuc2Zvcm0uanMnXG5cbi8qXG4gKiBkZXB0aFxuICogICA8IDAgIC0tPiBjbG9zZSBhbGwgY2hhaW5cbiAqICAgMCAgICAtLT4gZGlzYWJsZWRcbiAqICAgPiAwICAtLT4gY2xvc2UgY2hhaW4gdXAgdG8gTiBwYXJlbnRcbiAqL1xuXG5mdW5jdGlvbiBnZXREZXB0aCAodmFsdWUpIHtcbiAgaWYgKHZhbHVlID09PSBmYWxzZSkge1xuICAgIHJldHVybiAwXG4gIH1cbiAgaWYgKHZhbHVlID09PSB0cnVlIHx8IHZhbHVlID09PSB2b2lkIDApIHtcbiAgICByZXR1cm4gMVxuICB9XG5cbiAgY29uc3QgZGVwdGggPSBwYXJzZUludCh2YWx1ZSwgMTApXG4gIHJldHVybiBpc05hTihkZXB0aCkgPyAwIDogZGVwdGhcbn1cblxuZXhwb3J0IGRlZmF1bHQgY3JlYXRlRGlyZWN0aXZlKF9fUVVBU0FSX1NTUl9TRVJWRVJfX1xuICA/IHsgbmFtZTogJ2Nsb3NlLXBvcHVwJywgZ2V0U1NSUHJvcHMgfVxuICA6IHtcbiAgICAgIG5hbWU6ICdjbG9zZS1wb3B1cCcsXG5cbiAgICAgIGJlZm9yZU1vdW50IChlbCwgeyB2YWx1ZSB9KSB7XG4gICAgICAgIGNvbnN0IGN0eCA9IHtcbiAgICAgICAgICBkZXB0aDogZ2V0RGVwdGgodmFsdWUpLFxuXG4gICAgICAgICAgaGFuZGxlciAoZXZ0KSB7XG4gICAgICAgICAgICAvLyBhbGxvdyBAY2xpY2sgdG8gYmUgZW1pdHRlZFxuICAgICAgICAgICAgY3R4LmRlcHRoICE9PSAwICYmIHNldFRpbWVvdXQoKCkgPT4ge1xuICAgICAgICAgICAgICBjb25zdCBwcm94eSA9IGdldFBvcnRhbFByb3h5KGVsKVxuICAgICAgICAgICAgICBpZiAocHJveHkgIT09IHZvaWQgMCkge1xuICAgICAgICAgICAgICAgIGNsb3NlUG9ydGFscyhwcm94eSwgZXZ0LCBjdHguZGVwdGgpXG4gICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0pXG4gICAgICAgICAgfSxcblxuICAgICAgICAgIGhhbmRsZXJLZXkgKGV2dCkge1xuICAgICAgICAgICAgaXNLZXlDb2RlKGV2dCwgMTMpID09PSB0cnVlICYmIGN0eC5oYW5kbGVyKGV2dClcbiAgICAgICAgICB9XG4gICAgICAgIH1cblxuICAgICAgICBlbC5fX3FjbG9zZXBvcHVwID0gY3R4XG5cbiAgICAgICAgZWwuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBjdHguaGFuZGxlcilcbiAgICAgICAgZWwuYWRkRXZlbnRMaXN0ZW5lcigna2V5dXAnLCBjdHguaGFuZGxlcktleSlcbiAgICAgIH0sXG5cbiAgICAgIHVwZGF0ZWQgKGVsLCB7IHZhbHVlLCBvbGRWYWx1ZSB9KSB7XG4gICAgICAgIGlmICh2YWx1ZSAhPT0gb2xkVmFsdWUpIHtcbiAgICAgICAgICBlbC5fX3FjbG9zZXBvcHVwLmRlcHRoID0gZ2V0RGVwdGgodmFsdWUpXG4gICAgICAgIH1cbiAgICAgIH0sXG5cbiAgICAgIGJlZm9yZVVubW91bnQgKGVsKSB7XG4gICAgICAgIGNvbnN0IGN0eCA9IGVsLl9fcWNsb3NlcG9wdXBcbiAgICAgICAgZWwucmVtb3ZlRXZlbnRMaXN0ZW5lcignY2xpY2snLCBjdHguaGFuZGxlcilcbiAgICAgICAgZWwucmVtb3ZlRXZlbnRMaXN0ZW5lcigna2V5dXAnLCBjdHguaGFuZGxlcktleSlcbiAgICAgICAgZGVsZXRlIGVsLl9fcWNsb3NlcG9wdXBcbiAgICAgIH1cbiAgICB9XG4pXG4iLCI8c2NyaXB0IHNldHVwPlxuaW1wb3J0IHtyZWYsIGNvbXB1dGVkfSBmcm9tICd2dWUnXG5pbXBvcnQge2FwaX0gZnJvbSAnYm9vdC9heGlvcy5qcydcbmltcG9ydCB7dXNlU3BlY2lhbGl6YXRpb25zU3RvcmV9IGZyb20gXCJzdG9yZXMvc3BlY2lhbGl6YXRpb25zLmpzXCI7XG5pbXBvcnQgRGVsZXRlQ29uZmlybVBhZ2UgZnJvbSBcInBhZ2VzL0RlbGV0ZUNvbmZpcm1QYWdlLnZ1ZVwiO1xuXG5jb25zdCBkZWxldGVDb25maXJtUGFnZSA9IHJlZihudWxsKVxuXG5jb25zdCBzcGVjaWFsaXphdGlvblN0b3JlID0gdXNlU3BlY2lhbGl6YXRpb25zU3RvcmUoKVxuXG5jb25zdCBlbWl0ID0gZGVmaW5lRW1pdHMoWydwcm9kdWN0LXNhdmVkJ10pXG5cbmNvbnN0IGN1cnJlbnRDYXRlZ29yeSA9IHJlZihudWxsKVxuXG5jb25zdCBzaG93RGlhbG9nID0gcmVmKGZhbHNlKVxuXG5jb25zdCBuYW1lID0gcmVmKG51bGwpXG5cbmNvbnN0IGVkaXRNb2RlID0gY29tcHV0ZWQoKCkgPT4gISFjdXJyZW50Q2F0ZWdvcnkudmFsdWUpXG5cbmNvbnN0IG9wZW4gPSAoY2F0ZWdvcnkpID0+IHtcbiAgY3VycmVudENhdGVnb3J5LnZhbHVlID0gY2F0ZWdvcnkgPyB7Li4uY2F0ZWdvcnl9IDogbnVsbFxuICBuYW1lLnZhbHVlID0gY2F0ZWdvcnk/Lm5hbWUgfHwgJydcbiAgc2hvd0RpYWxvZy52YWx1ZSA9IHRydWVcbn1cblxuY29uc3Qgc2F2ZVByb2R1Y3RDYXRlZ29yeSA9IGFzeW5jICgpID0+IHtcbiAgdHJ5IHtcbiAgICAvL2NvbnNvbGUubG9nKCdjdXJyZW50Q2F0ZWdvcnlJZDogJywgY3VycmVudENhdGVnb3J5LnZhbHVlLmlkKVxuICAgIGlmIChlZGl0TW9kZS52YWx1ZSl7XG4gICAgICBjb25zb2xlLmxvZygn0YDQtdC00LDQutGC0LjRgNC+0LLQsNC90LjQtSDQv9GA0L7QtNGD0LrRgtCwINC90LUg0YDQtdCw0LvQuNC30L7QstCw0L3QvicpXG4gICAgICBhd2FpdCBhcGkucG9zdChgL2VkaXRfcHJvZHVjdF9jYXRlZ29yeWAsIHtcbiAgICAgICAgaWQ6IGN1cnJlbnRDYXRlZ29yeS52YWx1ZS5pZCxcbiAgICAgICAgbmFtZTogbmFtZS52YWx1ZSxcbiAgICAgICAgc3BlY2lhbGl6YXRpb25faWQ6IHNwZWNpYWxpemF0aW9uU3RvcmUuZ2V0U2VsZWN0ZWRTcGVjaWFsaXphdGlvbi5pZFxuICAgICAgfSlcbiAgICAgIHNob3dEaWFsb2cudmFsdWUgPSBmYWxzZVxuICAgICAgZW1pdCgncHJvZHVjdC1jYXRlZ29yeS1zYXZlZCcpXG4gICAgfSBlbHNlIHtcbiAgICAgIGNvbnNvbGUubG9nKCfRgdC+0LfQtNCw0LXQvCDQvdC+0LLRi9C5INC/0YDQvtC00YPQutGCJylcbiAgICAgIGF3YWl0IGFwaS5wb3N0KGAvYWRkX3Byb2R1Y3RfY2F0ZWdvcnlgLCB7XG4gICAgICAgIG5hbWU6IG5hbWUudmFsdWUsXG4gICAgICAgIHNwZWNpYWxpemF0aW9uX2lkOiBzcGVjaWFsaXphdGlvblN0b3JlLmdldFNlbGVjdGVkU3BlY2lhbGl6YXRpb24uaWRcbiAgICAgIH0pXG4gICAgICBzaG93RGlhbG9nLnZhbHVlID0gZmFsc2VcbiAgICAgIGVtaXQoJ3Byb2R1Y3QtY2F0ZWdvcnktc2F2ZWQnKVxuICAgIH1cbiAgfSBjYXRjaCAoZXJyKXtcbiAgICBjb25zb2xlLmVycm9yKGVycilcbiAgICBjdXJyZW50Q2F0ZWdvcnkudmFsdWUgPSBudWxsXG4gICAgY2xvc2UoKVxuICB9XG59XG5cbmNvbnN0IGRlbGV0ZUNhdGVnb3J5ID0gYXN5bmMgKCkgPT4ge1xuICBpZiAoIWN1cnJlbnRDYXRlZ29yeS52YWx1ZSkgcmV0dXJuXG5cbiAgLy8g0J/QvtC60LDQt9GL0LLQsNC10Lwg0LTQuNCw0LvQvtCzINC/0L7QtNGC0LLQtdGA0LbQtNC10L3QuNGPXG4gIGRlbGV0ZUNvbmZpcm1QYWdlLnZhbHVlLm9wZW4oXG4gICAgJ9Cf0L7QtNGC0LLQtdGA0LTQuNGC0LUg0YPQtNCw0LvQtdC90LjQtScsXG4gICAgYNCS0Ysg0YPQstC10YDQtdC90YssINGH0YLQviDRhdC+0YLQuNGC0LUg0YPQtNCw0LvQuNGC0Ywg0LrQsNGC0LXQs9C+0YDQuNGOIFwiJHtjdXJyZW50Q2F0ZWdvcnkudmFsdWUubmFtZX1cIj9gLFxuICAgIGFzeW5jICgpID0+IHtcbiAgICAgIHRyeSB7XG4gICAgICAgIGF3YWl0IGFwaS5wb3N0KGAvZGVsZXRlX3Byb2R1Y3RfY2F0ZWdvcnlgLCB7XG4gICAgICAgICAgcHJvZHVjdENhdGVnb3J5SWQ6IGN1cnJlbnRDYXRlZ29yeS52YWx1ZS5pZFxuICAgICAgICB9KTtcbiAgICAgICAgZW1pdCgncHJvZHVjdC1jYXRlZ29yeS1zYXZlZCcpOyAvLyDQntCx0L3QvtCy0LvRj9C10Lwg0YHQv9C40YHQvtC6XG4gICAgICAgIHNob3dEaWFsb2cudmFsdWUgPSBmYWxzZTsgLy8g0JfQsNC60YDRi9Cy0LDQtdC8INC00LjQsNC70L7QsyDRgNC10LTQsNC60YLQuNGA0L7QstCw0L3QuNGPXG4gICAgICB9IGNhdGNoIChlcnIpIHtcbiAgICAgICAgY29uc29sZS5lcnJvcihcItCe0YjQuNCx0LrQsCDRg9C00LDQu9C10L3QuNGPINC60LDRgtC10LPQvtGA0LjQuFwiLCBlcnIpO1xuICAgICAgfVxuICAgIH1cbiAgKTtcbn07XG5cbmRlZmluZUV4cG9zZSh7b3Blbn0pXG5cbjwvc2NyaXB0PlxuXG48dGVtcGxhdGU+XG5cbiAgPHEtZGlhbG9nIHYtbW9kZWw9XCJzaG93RGlhbG9nXCIgcGVyc2lzdGVudD5cbiAgICA8cS1jYXJkIHN0eWxlPVwibWluLXdpZHRoOiA0MDBweFwiPlxuICAgICAgPHEtY2FyZC1zZWN0aW9uIGNsYXNzPVwicm93IGl0ZW1zLWNlbnRlclwiPlxuICAgICAgICA8c3BhbiBjbGFzcz1cInEtbWwtc20gdGV4dC1oNlwiPlxuICAgICAgICAgIHt7IGVkaXRNb2RlID8gJ9Cg0LXQtNCw0LrRgtC40YDQvtCy0LDQvdC40LUnIDogJ9Cd0L7QstCw0Y8g0LrQsNGC0LXQs9C+0YDQuNGPJ319XG4gICAgICAgIDwvc3Bhbj5cbiAgICAgICAgPHEtc3BhY2UgLz5cbiAgICAgICAgPHEtYnRuIGljb249XCJjbG9zZVwiIGZsYXQgcm91bmQgZGVuc2Ugdi1jbG9zZS1wb3B1cCAvPlxuICAgICAgPC9xLWNhcmQtc2VjdGlvbj5cblxuICAgICAgPHEtY2FyZC1zZWN0aW9uPlxuICAgICAgICA8ZGl2IGNsYXNzPVwicS1ndXR0ZXIteS1tZFwiPlxuICAgICAgICAgIDxxLWlucHV0IHYtbW9kZWw9XCJuYW1lXCJcbiAgICAgICAgICAgICAgICAgICBvdXRsaW5lZFxuICAgICAgICAgICAgICAgICAgIGxhYmVsPVwi0L3QsNC30LLQsNC90LjQtSDQutCw0YLQtdCz0L7RgNC40LhcIlxuICAgICAgICAgICAgICAgICAgIHBsYWNlaG9sZGVyPVwi0JLQstC10LTQuNGC0LUg0L3QsNC30LLQsNC90LjQtVwiXG4gICAgICAgICAgICAgICAgICAgY2xhc3M9XCJxLW1iLW1kXCJcbiAgICAgICAgICAvPlxuICAgICAgICA8L2Rpdj5cbiAgICAgIDwvcS1jYXJkLXNlY3Rpb24+XG5cbiAgICAgIDxxLWNhcmQtYWN0aW9ucyBhbGlnbj1cInJpZ2h0XCI+XG5cbiAgICAgICAgPHEtYnRuIGZsYXRcbiAgICAgICAgICAgICAgIGxhYmVsPVwi0J7RgtC80LXQvdCwXCJcbiAgICAgICAgICAgICAgIGNvbG9yPVwieWVsbG93XCJcbiAgICAgICAgICAgICAgIHYtY2xvc2UtcG9wdXBcbiAgICAgICAgLz5cblxuICAgICAgICA8cS1idG4gbGFiZWw9XCLQodC+0YXRgNCw0L3QuNGC0YxcIlxuICAgICAgICAgICAgICAgdGV4dC1jb2xvcj1cInllbGxvd1wiXG4gICAgICAgICAgICAgICBAY2xpY2s9XCJzYXZlUHJvZHVjdENhdGVnb3J5XCJcbiAgICAgICAgLz5cblxuICAgICAgICA8cS1idG4gbGFiZWw9XCLQo9C00LDQu9C40YLRjFwiXG4gICAgICAgICAgICAgICBmbGF0XG4gICAgICAgICAgICAgICBjb2xvcj1cInllbGxvd1wiXG4gICAgICAgICAgICAgICBAY2xpY2s9XCJkZWxldGVDYXRlZ29yeVwiXG4gICAgICAgICAgICAgICB2LWlmPVwiY3VycmVudENhdGVnb3J5XCJcbiAgICAgICAgLz5cblxuICAgICAgPC9xLWNhcmQtYWN0aW9ucz5cblxuXG4gICAgPC9xLWNhcmQ+XG4gIDwvcS1kaWFsb2c+XG5cbiAgPERlbGV0ZUNvbmZpcm1QYWdlIHJlZj1cImRlbGV0ZUNvbmZpcm1QYWdlXCIgLz5cblxuPC90ZW1wbGF0ZT5cblxuPHN0eWxlIHNjb3BlZD5cblxuPC9zdHlsZT5cbiIsIjxzY3JpcHQgc2V0dXA+XG5pbXBvcnQge3JlZn0gZnJvbSAndnVlJ1xuaW1wb3J0IHthcGl9IGZyb20gJ2Jvb3QvYXhpb3MuanMnXG5pbXBvcnQgRGVsZXRlQ29uZmlybVBhZ2UgZnJvbSBcInBhZ2VzL0RlbGV0ZUNvbmZpcm1QYWdlLnZ1ZVwiO1xuXG5jb25zdCBkZWxldGVDb25maXJtUGFnZSA9IHJlZihudWxsKVxuXG5jb25zdCBlbWl0ID0gZGVmaW5lRW1pdHMoWydwcm9kdWN0LWFycml2YWwtc2F2ZWQnXSlcblxuY29uc3QgY3VycmVudFByb2R1Y3QgPSByZWYobnVsbClcblxuY29uc3Qgc2hvd0RpYWxvZyA9IHJlZihmYWxzZSlcblxuY29uc3QgbmFtZSA9IHJlZihudWxsKVxuXG5jb25zdCBieVByaWNlID0gcmVmKG51bGwpXG5jb25zdCBhcnJpdmFsUXVhbnRpdHkgPSByZWYobnVsbClcblxuY29uc3QgYmFzZVNhbGVQcmljZSA9IHJlZihudWxsKVxuXG5jb25zdCBvcGVuID0gKHByb2R1Y3QpID0+IHtcbiAgY29uc29sZS5sb2coJ9C+0YLQutGA0YvRgtC40LUg0LTQuNCw0LvQvtCz0L7QstC+0LPQviDQvtC60L3QsCDQv9C+0YHRgtGD0L/Qu9C10L3QuNGPINGC0L7QstCw0YDQsCcpXG4gIGN1cnJlbnRQcm9kdWN0LnZhbHVlID0gcHJvZHVjdCA/IHsuLi5wcm9kdWN0fSA6IG51bGxcbiAgbmFtZS52YWx1ZSA9IHByb2R1Y3Q/Lm5hbWUgfHwgJydcbiAgYmFzZVNhbGVQcmljZS52YWx1ZSA9IHByb2R1Y3Q/LmJhc2Vfc2FsZV9wcmljZSB8fCAnJ1xuICBzaG93RGlhbG9nLnZhbHVlID0gdHJ1ZVxuICBjb25zb2xlLmxvZygncHJvZHVjdDogJywgcHJvZHVjdClcbn1cblxuY29uc3QgbWFrZUFycml2YWxQcm9kdWN0ID0gYXN5bmMgKCkgPT4ge1xuICB0cnkge1xuICAgIC8vY29uc29sZS5sb2coJ2N1cnJlbnRDYXRlZ29yeUlkOiAnLCBjdXJyZW50Q2F0ZWdvcnkudmFsdWUuaWQpXG4gICAgaWYgKGN1cnJlbnRQcm9kdWN0LnZhbHVlKXtcbiAgICAgIGNvbnNvbGUubG9nKCfQt9Cw0L/RgNC+0YEg0L3QsCDQv9C+0YHRgtGD0L/Qu9C10L3QuNC1INGC0L7QstCw0YDQsCcpXG4gICAgICBhd2FpdCBhcGkucG9zdChgL2Fycml2YWxfcHJvZHVjdGAsIHtcbiAgICAgICAgcHJvZHVjdF9pZDogY3VycmVudFByb2R1Y3QudmFsdWUuaWQsXG4gICAgICAgIGJhc2Vfc2FsZV9wcmljZTogYmFzZVNhbGVQcmljZS52YWx1ZSxcbiAgICAgICAgYnlfcHJpY2U6IGJ5UHJpY2UudmFsdWUsXG4gICAgICAgIGFycml2YWxfcXVhbnRpdHk6IGFycml2YWxRdWFudGl0eS52YWx1ZVxuICAgICAgfSlcbiAgICAgIHNob3dEaWFsb2cudmFsdWUgPSBmYWxzZVxuICAgICAgZW1pdCgncHJvZHVjdC1jYXRlZ29yeS1zYXZlZCcpXG4gICAgfSBlbHNlIHtcbiAgICAgIGNvbnNvbGUubG9nKCdjdXJyZW50UHJvZHVjdDogJywgY3VycmVudFByb2R1Y3QudmFsdWUpXG4gICAgfVxuICB9IGNhdGNoIChlcnIpe1xuICAgIGNvbnNvbGUuZXJyb3IoZXJyKVxuICAgIGN1cnJlbnRQcm9kdWN0LnZhbHVlID0gbnVsbFxuICAgIGNsb3NlKClcbiAgfVxufVxuXG5kZWZpbmVFeHBvc2Uoe29wZW59KVxuXG48L3NjcmlwdD5cblxuPHRlbXBsYXRlPlxuXG4gIDxxLWRpYWxvZyB2LW1vZGVsPVwic2hvd0RpYWxvZ1wiIHBlcnNpc3RlbnQ+XG4gICAgPHEtY2FyZCBzdHlsZT1cIm1pbi13aWR0aDogNDAwcHhcIj5cbiAgICAgIDxxLWNhcmQtc2VjdGlvbiBjbGFzcz1cInJvdyBpdGVtcy1jZW50ZXJcIj5cbiAgICAgICAgPHNwYW4gY2xhc3M9XCJxLW1sLXNtIHRleHQtaDZcIj5cbiAgICAgICAgICDQn9C+0YHRgtGD0L/Qu9C10L3QuNC1XG4gICAgICAgIDwvc3Bhbj5cbiAgICAgICAgPHEtc3BhY2UgLz5cbiAgICAgICAgPHEtYnRuIGljb249XCJjbG9zZVwiIGZsYXQgcm91bmQgZGVuc2Ugdi1jbG9zZS1wb3B1cCAvPlxuICAgICAgPC9xLWNhcmQtc2VjdGlvbj5cblxuICAgICAgPHEtY2FyZC1zZWN0aW9uPlxuICAgICAgICA8ZGl2IGNsYXNzPVwicS1ndXR0ZXIteS1tZFwiPlxuICAgICAgICAgIDxxLWlucHV0IHYtbW9kZWw9XCJieVByaWNlXCJcbiAgICAgICAgICAgICAgICAgICBvdXRsaW5lZFxuICAgICAgICAgICAgICAgICAgIGxhYmVsPVwi0YbQtdC90LAg0LfQsNC60YPQv9C60LhcIlxuICAgICAgICAgICAgICAgICAgIHBsYWNlaG9sZGVyPVwi0LLQstC10LTQuNGC0LUg0YbQtdC90YMg0LfQsNC60YPQv9C60LhcIlxuICAgICAgICAgICAgICAgICAgIGNsYXNzPVwicS1tYi1tZFwiXG4gICAgICAgICAgLz5cbiAgICAgICAgPC9kaXY+XG4gICAgICA8L3EtY2FyZC1zZWN0aW9uPlxuXG4gICAgICA8cS1jYXJkLXNlY3Rpb24+XG4gICAgICAgIDxkaXYgY2xhc3M9XCJxLWd1dHRlci15LW1kXCI+XG4gICAgICAgICAgPHEtaW5wdXQgdi1tb2RlbD1cImJhc2VTYWxlUHJpY2VcIlxuICAgICAgICAgICAgICAgICAgIG91dGxpbmVkXG4gICAgICAgICAgICAgICAgICAgbGFiZWw9XCLRhtC10L3QsCDQv9GA0L7QtNCw0LbQuFwiXG4gICAgICAgICAgICAgICAgICAgcGxhY2Vob2xkZXI9XCLQktCy0LXQtNC40YLQtSDRhtC10L3RgyDQv9GA0L7QtNCw0LbQuFwiXG4gICAgICAgICAgICAgICAgICAgY2xhc3M9XCJxLW1iLW1kXCJcbiAgICAgICAgICAvPlxuICAgICAgICA8L2Rpdj5cbiAgICAgIDwvcS1jYXJkLXNlY3Rpb24+XG5cbiAgICAgIDxxLWNhcmQtc2VjdGlvbj5cbiAgICAgICAgPGRpdiBjbGFzcz1cInEtZ3V0dGVyLXktbWRcIj5cbiAgICAgICAgICA8cS1pbnB1dCB2LW1vZGVsPVwiYXJyaXZhbFF1YW50aXR5XCJcbiAgICAgICAgICAgICAgICAgICBvdXRsaW5lZFxuICAgICAgICAgICAgICAgICAgIGxhYmVsPVwi0LrQvtC70LjRh9C10YHRgtCy0L4g0L/QvtGB0YLRg9C/0LjQu9C+XCJcbiAgICAgICAgICAgICAgICAgICBwbGFjZWhvbGRlcj1cItCS0LLQtdC00LjRgtC1INC60L7Qu9C40YfQtdGB0YLQstC+INC/0L7RgdGC0YPQv9C70LXQvdC40Y9cIlxuICAgICAgICAgICAgICAgICAgIGNsYXNzPVwicS1tYi1tZFwiXG4gICAgICAgICAgLz5cbiAgICAgICAgPC9kaXY+XG4gICAgICA8L3EtY2FyZC1zZWN0aW9uPlxuXG4gICAgICA8cS1jYXJkLWFjdGlvbnMgYWxpZ249XCJyaWdodFwiPlxuXG4gICAgICAgIDxxLWJ0biBmbGF0XG4gICAgICAgICAgICAgICBsYWJlbD1cItCe0YLQvNC10L3QsFwiXG4gICAgICAgICAgICAgICBjb2xvcj1cInllbGxvd1wiXG4gICAgICAgICAgICAgICB2LWNsb3NlLXBvcHVwXG4gICAgICAgIC8+XG5cbiAgICAgICAgPHEtYnRuIGxhYmVsPVwi0KHQvtGF0YDQsNC90LjRgtGMXCJcbiAgICAgICAgICAgICAgIHRleHQtY29sb3I9XCJ5ZWxsb3dcIlxuICAgICAgICAgICAgICAgQGNsaWNrPVwibWFrZUFycml2YWxQcm9kdWN0XCJcbiAgICAgICAgLz5cblxuICAgICAgPC9xLWNhcmQtYWN0aW9ucz5cblxuXG4gICAgPC9xLWNhcmQ+XG4gIDwvcS1kaWFsb2c+XG5cbiAgPERlbGV0ZUNvbmZpcm1QYWdlIHJlZj1cImRlbGV0ZUNvbmZpcm1QYWdlXCIgLz5cblxuPC90ZW1wbGF0ZT5cblxuPHN0eWxlIHNjb3BlZD5cblxuPC9zdHlsZT5cbiIsIjxzY3JpcHQgc2V0dXA+XG5pbXBvcnQge3JlZn0gZnJvbSAndnVlJ1xuaW1wb3J0IHthcGl9IGZyb20gJ2Jvb3QvYXhpb3MuanMnXG5pbXBvcnQge3VzZVNwZWNpYWxpemF0aW9uc1N0b3JlfSBmcm9tIFwic3RvcmVzL3NwZWNpYWxpemF0aW9ucy5qc1wiO1xuaW1wb3J0IERlbGV0ZUNvbmZpcm1QYWdlIGZyb20gXCJwYWdlcy9EZWxldGVDb25maXJtUGFnZS52dWVcIjtcbmltcG9ydCBBcnJpdmFsUHJvZHVjdERpYWxvZ1BhZ2UgZnJvbSBcInBhZ2VzL2RpYWxvZ3MvQXJyaXZhbFByb2R1Y3REaWFsb2dQYWdlLnZ1ZVwiO1xuXG5jb25zdCBkZWxldGVDb25maXJtUGFnZSA9IHJlZihudWxsKVxuY29uc3QgYXJyaXZhbENvbmZpcm1QYWdlID0gcmVmKG51bGwpXG5cbmNvbnN0IHNwZWNpYWxpemF0aW9uU3RvcmUgPSB1c2VTcGVjaWFsaXphdGlvbnNTdG9yZSgpXG5cbmNvbnN0IGVtaXQgPSBkZWZpbmVFbWl0cyhbJ3Byb2R1Y3Qtc2F2ZWQnXSlcblxuY29uc3QgY3VycmVudFByb2R1Y3QgPSByZWYobnVsbClcblxuY29uc3QgY3VycmVudFByb2R1Y3RDYXRlZ29yeSA9IHJlZihudWxsKVxuXG5jb25zdCBzaG93RGlhbG9nID0gcmVmKGZhbHNlKVxuXG5jb25zdCBuYW1lID0gcmVmKG51bGwpXG5jb25zdCBiYXNlU2FsZVByaWNlID0gcmVmKG51bGwpXG5cbmNvbnN0IGVkaXRNb2RlID0gcmVmKGZhbHNlKVxuY29uc3QgbmV3UHJvZHVjdE1vZGUgPSByZWYoZmFsc2UpXG5cbmNvbnN0IG9wZW4gPSAocHJvZHVjdCwgcHJvZHVjdENhdGVnb3J5KSA9PiB7XG4gIGNvbnNvbGUubG9nKCfQvtGC0LrRgNGL0YLQuNC1INC00LjQsNC70L7Qs9C+0LLQvtCz0L4g0L/RgNC+0LTRg9C60YLQsCcpXG4gIGVkaXRNb2RlLnZhbHVlID0gZmFsc2VcbiAgbmV3UHJvZHVjdE1vZGUudmFsdWUgPSBmYWxzZVxuICBjdXJyZW50UHJvZHVjdC52YWx1ZSA9IHByb2R1Y3QgPyB7Li4ucHJvZHVjdH0gOiBudWxsXG4gIGN1cnJlbnRQcm9kdWN0Q2F0ZWdvcnkudmFsdWUgPSBwcm9kdWN0Q2F0ZWdvcnkgfHwgbnVsbFxuICBuYW1lLnZhbHVlID0gcHJvZHVjdD8ubmFtZSB8fCAnJ1xuICBiYXNlU2FsZVByaWNlLnZhbHVlID0gcHJvZHVjdD8uYmFzZV9zYWxlX3ByaWNlIHx8ICcnXG4gIGlmIChjdXJyZW50UHJvZHVjdENhdGVnb3J5LnZhbHVlKXtcbiAgICBlZGl0TW9kZS52YWx1ZSA9IHRydWVcbiAgICBuZXdQcm9kdWN0TW9kZS52YWx1ZSA9IHRydWVcbiAgfSBlbHNlIHtcbiAgICBlZGl0TW9kZS52YWx1ZSA9IGZhbHNlXG4gIH1cbiAgc2hvd0RpYWxvZy52YWx1ZSA9IHRydWVcbiAgY29uc29sZS5sb2coJ2N1cnJlbnRQcm9kdWN0Q2F0ZWdvcnk6JywgY3VycmVudFByb2R1Y3RDYXRlZ29yeS52YWx1ZSlcbiAgY29uc29sZS5sb2coJ3Byb2R1Y3Q6ICcsIHByb2R1Y3QpXG4gIGNvbnNvbGUubG9nKCdwcm9kdWN0Q2F0ZWdvcnk6ICcsIHByb2R1Y3RDYXRlZ29yeSlcbiAgY29uc29sZS5sb2coJ2VkaXRNb2RlIDogJywgZWRpdE1vZGUudmFsdWUpXG4gIGNvbnNvbGUubG9nKCduZXdQcm9kdWN0TW9kZSA6ICcsIG5ld1Byb2R1Y3RNb2RlLnZhbHVlKVxufVxuXG5jb25zdCBzYXZlUHJvZHVjdCA9IGFzeW5jICgpID0+IHtcbiAgdHJ5IHtcbiAgICAvL2NvbnNvbGUubG9nKCdjdXJyZW50Q2F0ZWdvcnlJZDogJywgY3VycmVudENhdGVnb3J5LnZhbHVlLmlkKVxuICAgIGlmIChlZGl0TW9kZS52YWx1ZSl7XG4gICAgICBjb25zb2xlLmxvZygn0YDQtdC00LDQutGC0LjRgNC+0LLQsNC90LjQtSDQv9GA0L7QtNGD0LrRgtCwINC90LUg0YDQtdCw0LvQuNC30L7QstCw0L3QvicpXG4gICAgICBhd2FpdCBhcGkucG9zdChgL2VkaXRfcHJvZHVjdGAsIHtcbiAgICAgICAgaWQ6IGN1cnJlbnRQcm9kdWN0LnZhbHVlLmlkLFxuICAgICAgICBuYW1lOiBuYW1lLnZhbHVlLFxuICAgICAgICBzcGVjaWFsaXphdGlvbl9pZDogc3BlY2lhbGl6YXRpb25TdG9yZS5nZXRTZWxlY3RlZFNwZWNpYWxpemF0aW9uLmlkXG4gICAgICB9KVxuICAgICAgc2hvd0RpYWxvZy52YWx1ZSA9IGZhbHNlXG4gICAgICBlbWl0KCdwcm9kdWN0LWNhdGVnb3J5LXNhdmVkJylcbiAgICB9IGVsc2Uge1xuICAgICAgY29uc29sZS5sb2coJ9GB0L7Qt9C00LDQtdC8INC90L7QstGL0Lkg0L/RgNC+0LTRg9C60YI6ICcsIGN1cnJlbnRQcm9kdWN0LnZhbHVlKVxuICAgICAgYXdhaXQgYXBpLnBvc3QoYC9hZGRfcHJvZHVjdGAsIHtcbiAgICAgICAgbmFtZTogbmFtZS52YWx1ZSxcbiAgICAgICAgYmFzZV9zYWxlX3ByaWNlOiBiYXNlU2FsZVByaWNlLnZhbHVlLFxuICAgICAgICBwcm9kdWN0X2NhdGVnb3J5X2lkOiBjdXJyZW50UHJvZHVjdENhdGVnb3J5LnZhbHVlLmlkXG4gICAgICB9KVxuICAgICAgc2hvd0RpYWxvZy52YWx1ZSA9IGZhbHNlXG4gICAgICBlbWl0KCdwcm9kdWN0LWFkZGVkJylcbiAgICB9XG4gIH0gY2F0Y2ggKGVycil7XG4gICAgY29uc29sZS5lcnJvcihlcnIpXG4gICAgY3VycmVudFByb2R1Y3QudmFsdWUgPSBudWxsXG4gICAgY2xvc2UoKVxuICB9XG59XG5cbmNvbnN0IGRlbGV0ZUNhdGVnb3J5ID0gYXN5bmMgKCkgPT4ge1xuICBpZiAoIWN1cnJlbnRQcm9kdWN0LnZhbHVlKSByZXR1cm5cblxuICAvLyDQn9C+0LrQsNC30YvQstCw0LXQvCDQtNC40LDQu9C+0LMg0L/QvtC00YLQstC10YDQttC00LXQvdC40Y9cbiAgZGVsZXRlQ29uZmlybVBhZ2UudmFsdWUub3BlbihcbiAgICAn0J/QvtC00YLQstC10YDQtNC40YLQtSDRg9C00LDQu9C10L3QuNC1JyxcbiAgICBg0JLRiyDRg9Cy0LXRgNC10L3Riywg0YfRgtC+INGF0L7RgtC40YLQtSDRg9C00LDQu9C40YLRjCDRgtC+0LLQsNGAIFwiJHtjdXJyZW50UHJvZHVjdC52YWx1ZS5uYW1lfVwiP2AsXG4gICAgYXN5bmMgKCkgPT4ge1xuICAgICAgdHJ5IHtcbiAgICAgICAgYXdhaXQgYXBpLnBvc3QoYC9kZWxldGVfcHJvZHVjdGAsIHtcbiAgICAgICAgICBwcm9kdWN0SWQ6IGN1cnJlbnRQcm9kdWN0LnZhbHVlLmlkXG4gICAgICAgIH0pO1xuICAgICAgICBlbWl0KCdwcm9kdWN0LXNhdmVkJyk7IC8vINCe0LHQvdC+0LLQu9GP0LXQvCDRgdC/0LjRgdC+0LpcbiAgICAgICAgc2hvd0RpYWxvZy52YWx1ZSA9IGZhbHNlOyAvLyDQl9Cw0LrRgNGL0LLQsNC10Lwg0LTQuNCw0LvQvtCzINGA0LXQtNCw0LrRgtC40YDQvtCy0LDQvdC40Y9cbiAgICAgIH0gY2F0Y2ggKGVycikge1xuICAgICAgICBjb25zb2xlLmVycm9yKFwi0J7RiNC40LHQutCwINGD0LTQsNC70LXQvdC40Y8g0YLQvtCy0LDRgNCwXCIsIGVycik7XG4gICAgICB9XG4gICAgfVxuICApO1xufTtcblxuY29uc3QgY2xvc2UgPSAoKSA9PiB7XG4gIGNvbnNvbGUubG9nKCfQt9Cw0LrRgNGL0LLQsNC10Lwg0L7QutC90L4nKVxuICBlZGl0TW9kZS52YWx1ZSA9IGZhbHNlXG4gIG5ld1Byb2R1Y3RNb2RlLnZhbHVlID0gZmFsc2Vcbn1cblxuY29uc3Qgb3BlbkFycml2YWxQcm9kdWN0RGlhbG9nID0gKCkgPT4ge1xuICBjb25zb2xlLmxvZygnYXJyaXZhbCBub3QgcmVhbGl6ZWQnKVxuICBhcnJpdmFsQ29uZmlybVBhZ2UudmFsdWUub3BlbihjdXJyZW50UHJvZHVjdC52YWx1ZSlcbn1cblxuZGVmaW5lRXhwb3NlKHtvcGVufSlcblxuPC9zY3JpcHQ+XG5cbjx0ZW1wbGF0ZT5cblxuICA8cS1kaWFsb2cgdi1tb2RlbD1cInNob3dEaWFsb2dcIiBwZXJzaXN0ZW50PlxuICAgIDxxLWNhcmQgc3R5bGU9XCJtaW4td2lkdGg6IDQwMHB4XCI+XG4gICAgICA8cS1jYXJkLXNlY3Rpb24gY2xhc3M9XCJyb3cgaXRlbXMtY2VudGVyXCI+XG48IS0tICAgICAgICA8c3BhbiBjbGFzcz1cInEtbWwtc20gdGV4dC1oNlwiPi0tPlxuPCEtLSAgICAgICAgICB7eyAhbmV3UHJvZHVjdE1vZGUgPyAn0KDQtdC00LDQutGC0LjRgNC+0LLQsNC90LjQtScgOiAn0J3QvtCy0YvQuSDRgtC+0LLQsNGAJ319LS0+XG48IS0tICAgICAgICA8L3NwYW4+LS0+XG4gICAgICAgIDxzcGFuIGNsYXNzPVwicS1tbC1zbSB0ZXh0LWg2XCIgdi1pZj1cIm5ld1Byb2R1Y3RNb2RlXCI+XG4gICAgICAgICAg0J3QvtCy0YvQuSDRgtC+0LLQsNGAXG4gICAgICAgIDwvc3Bhbj5cblxuICAgICAgICA8c3BhbiBjbGFzcz1cInEtbWwtc20gdGV4dC1oNlwiIHYtaWY9XCJlZGl0TW9kZSAmJiAhbmV3UHJvZHVjdE1vZGVcIj5cbiAgICAgICAgICDRgNC10LTQsNC60YLQuNGA0L7QstCw0L3QuNC1XG4gICAgICAgIDwvc3Bhbj5cblxuICAgICAgICA8c3BhbiBjbGFzcz1cInEtbWwtc20gdGV4dC1oNlwiIHYtaWY9XCIhZWRpdE1vZGUgJiYgIW5ld1Byb2R1Y3RNb2RlXCI+XG4gICAgICAgICAg0YLQvtCy0LDRgFxuICAgICAgICA8L3NwYW4+XG5cbiAgICAgICAgPHEtc3BhY2UgLz5cbiAgICAgICAgPHEtYnRuIGljb249XCJjbG9zZVwiIGZsYXQgcm91bmQgZGVuc2Ugdi1jbG9zZS1wb3B1cCAvPlxuICAgICAgPC9xLWNhcmQtc2VjdGlvbj5cblxuICAgICAgPHEtY2FyZC1zZWN0aW9uPlxuICAgICAgICA8ZGl2IGNsYXNzPVwicS1ndXR0ZXIteS1tZFwiPlxuICAgICAgICAgIDxxLWlucHV0IHYtbW9kZWw9XCJuYW1lXCJcbiAgICAgICAgICAgICAgICAgICBvdXRsaW5lZFxuICAgICAgICAgICAgICAgICAgIGxhYmVsPVwi0L3QsNC30LLQsNC90LjQtSDQv9GA0L7QtNGD0LrRgtCwXCJcbiAgICAgICAgICAgICAgICAgICBwbGFjZWhvbGRlcj1cItCS0LLQtdC00LjRgtC1INC90LDQt9Cy0LDQvdC40LVcIlxuICAgICAgICAgICAgICAgICAgIGNsYXNzPVwicS1tYi1tZFwiXG4gICAgICAgICAgICAgICAgICAgOmRpc2FibGU9XCIhZWRpdE1vZGVcIlxuICAgICAgICAgIC8+XG4gICAgICAgIDwvZGl2PlxuICAgICAgPC9xLWNhcmQtc2VjdGlvbj5cblxuICAgICAgPHEtY2FyZC1zZWN0aW9uPlxuICAgICAgICA8ZGl2IGNsYXNzPVwicS1ndXR0ZXIteS1tZFwiPlxuICAgICAgICAgIDxxLWlucHV0IHYtbW9kZWw9XCJiYXNlU2FsZVByaWNlXCJcbiAgICAgICAgICAgICAgICAgICBvdXRsaW5lZFxuICAgICAgICAgICAgICAgICAgIGxhYmVsPVwi0YbQtdC90LAg0L/RgNC+0LTQsNC20LhcIlxuICAgICAgICAgICAgICAgICAgIHBsYWNlaG9sZGVyPVwi0JLQstC10LTQuNGC0LUg0YbQtdC90YMg0L/RgNC+0LTQsNC20LhcIlxuICAgICAgICAgICAgICAgICAgIGNsYXNzPVwicS1tYi1tZFwiXG4gICAgICAgICAgICAgICAgICAgOmRpc2FibGU9XCIhZWRpdE1vZGVcIlxuICAgICAgICAgIC8+XG4gICAgICAgIDwvZGl2PlxuICAgICAgPC9xLWNhcmQtc2VjdGlvbj5cblxuICAgICAgPHEtY2FyZC1hY3Rpb25zIGFsaWduPVwicmlnaHRcIj5cblxuICAgICAgICA8cS1idG4gZmxhdFxuICAgICAgICAgICAgICAgdi1pZj1cImVkaXRNb2RlXCJcbiAgICAgICAgICAgICAgIGxhYmVsPVwi0J7RgtC80LXQvdCwXCJcbiAgICAgICAgICAgICAgIGNvbG9yPVwieWVsbG93XCJcbiAgICAgICAgICAgICAgIHYtY2xvc2UtcG9wdXBcbiAgICAgICAgICAgICAgIEBjbGljaz1cImVkaXRNb2RlLnZhbHVlID0gZmFsc2U7IG5ld1Byb2R1Y3RNb2RlLnZhbHVlID0gZmFsc2U7IGN1cnJlbnRQcm9kdWN0Q2F0ZWdvcnkudmFsdWUgPSAnJ1wiXG4gICAgICAgIC8+XG5cbiAgICAgICAgPHEtYnRuIGZsYXRcbiAgICAgICAgICAgICAgIHYtaWY9XCIhZWRpdE1vZGVcIlxuICAgICAgICAgICAgICAgbGFiZWw9XCLQt9Cw0LrRgNGL0YLRjFwiXG4gICAgICAgICAgICAgICBjb2xvcj1cInllbGxvd1wiXG4gICAgICAgICAgICAgICB2LWNsb3NlLXBvcHVwXG4gICAgICAgICAgICAgICBAY2xpY2s9XCJuZXdQcm9kdWN0TW9kZS52YWx1ZSA9IGZhbHNlOyBlZGl0TW9kZS52YWx1ZSA9IGZhbHNlOyBjdXJyZW50UHJvZHVjdENhdGVnb3J5LnZhbHVlID0gJydcIlxuICAgICAgICAvPlxuXG4gICAgICAgIDxxLWJ0biBsYWJlbD1cItCh0L7RhdGA0LDQvdC40YLRjFwiXG4gICAgICAgICAgICAgICB2LWlmPVwiZWRpdE1vZGVcIlxuICAgICAgICAgICAgICAgdGV4dC1jb2xvcj1cInllbGxvd1wiXG4gICAgICAgICAgICAgICBAY2xpY2s9XCJzYXZlUHJvZHVjdFwiXG4gICAgICAgIC8+XG5cbiAgICAgICAgPHEtYnRuIGxhYmVsPVwi0YDQtdC00LDQutGC0LjRgNC+0LLQsNGC0YxcIlxuICAgICAgICAgICAgICAgdi1pZj1cIiFlZGl0TW9kZVwiXG4gICAgICAgICAgICAgICB0ZXh0LWNvbG9yPVwieWVsbG93XCJcbiAgICAgICAgICAgICAgIEBjbGljaz1cImVkaXRNb2RlID0gdHJ1ZVwiXG4gICAgICAgIC8+XG5cbiAgICAgICAgPHEtYnRuIGxhYmVsPVwi0KPQtNCw0LvQuNGC0YxcIlxuICAgICAgICAgICAgICAgZmxhdFxuICAgICAgICAgICAgICAgY29sb3I9XCJ5ZWxsb3dcIlxuICAgICAgICAgICAgICAgQGNsaWNrPVwiZGVsZXRlQ2F0ZWdvcnlcIlxuICAgICAgICAgICAgICAgdi1pZj1cIiFlZGl0TW9kZVwiXG4gICAgICAgIC8+XG5cbiAgICAgICAgPHEtYnRuIGxhYmVsPVwi0J/QvtGB0YLRg9C/0LvQtdC90LjQtVwiXG4gICAgICAgICAgICAgICB2LWlmPVwiIWVkaXRNb2RlXCJcbiAgICAgICAgICAgICAgIHRleHQtY29sb3I9XCJ5ZWxsb3dcIlxuICAgICAgICAgICAgICAgQGNsaWNrPVwib3BlbkFycml2YWxQcm9kdWN0RGlhbG9nXCJcbiAgICAgICAgLz5cblxuICAgICAgPC9xLWNhcmQtYWN0aW9ucz5cblxuXG4gICAgPC9xLWNhcmQ+XG4gIDwvcS1kaWFsb2c+XG5cbiAgPERlbGV0ZUNvbmZpcm1QYWdlIHJlZj1cImRlbGV0ZUNvbmZpcm1QYWdlXCIgLz5cbiAgPEFycml2YWxQcm9kdWN0RGlhbG9nUGFnZSByZWY9XCJhcnJpdmFsQ29uZmlybVBhZ2VcIiAvPlxuPC90ZW1wbGF0ZT5cblxuPHN0eWxlIHNjb3BlZD5cblxuPC9zdHlsZT5cbiIsIjxzY3JpcHQgc2V0dXA+XG5pbXBvcnQge29uTW91bnRlZCwgcmVmfSBmcm9tIFwidnVlXCI7XG5pbXBvcnQge2FwaX0gZnJvbSBcImJvb3QvYXhpb3MuanNcIjtcbmltcG9ydCB7dXNlU3BlY2lhbGl6YXRpb25zU3RvcmV9IGZyb20gXCJzdG9yZXMvc3BlY2lhbGl6YXRpb25zLmpzXCI7XG5pbXBvcnQgUHJvZHVjdENhdGVnb3J5RGlhbG9nUGFnZSBmcm9tIFwicGFnZXMvZGlhbG9ncy9Qcm9kdWN0Q2F0ZWdvcnlEaWFsb2dQYWdlLnZ1ZVwiO1xuaW1wb3J0IFByb2R1Y3REaWFsb2dQYWdlIGZyb20gXCJwYWdlcy9kaWFsb2dzL1Byb2R1Y3REaWFsb2dQYWdlLnZ1ZVwiO1xuXG5jb25zdCBzcGVjaWFsaXphdGlvblN0b3JlID0gdXNlU3BlY2lhbGl6YXRpb25zU3RvcmUoKVxuY29uc3Qgc2VsZWN0ZWRTcGVjaWFsaXphdGlvbklkID0gc3BlY2lhbGl6YXRpb25TdG9yZS5nZXRTZWxlY3RlZFNwZWNpYWxpemF0aW9uXG5cbmNvbnN0IHByb2R1Y3RDYXRlZ29yaWVzID0gcmVmKFtdKVxuY29uc3QgcHJvZHVjdHMgPSByZWYoW10pXG5cbmNvbnN0IHNlbGVjdGVkUHJvZHVjdENhdGVnb3J5ID0gcmVmKG51bGwpXG5jb25zdCBzZWxlY3RlZFByb2R1Y3QgPSByZWYobnVsbClcblxuLy9jb25zdCBzaG93UHJvZHVjdERldGFpbHMgPSByZWYoZmFsc2UpXG4vL2NvbnN0IHNob3dQcm9kdWN0Q2F0ZWdvcnlEZXRhaWxzID0gcmVmKGZhbHNlKVxuXG5jb25zdCBwcm9kdWN0RGlhbG9nID0gcmVmKG51bGwpXG5jb25zdCBwcm9kdWN0Q2F0ZWdvcnlEaWFsb2cgPSByZWYobnVsbClcblxub25Nb3VudGVkKCgpID0+IHtcbiAgZ2V0UHJvZHVjdENhdGVnb3JpZXMoKVxufSlcblxuY29uc3QgZ2V0UHJvZHVjdENhdGVnb3JpZXMgPSBhc3luYyAoKSA9PiB7XG4gIGNvbnNvbGUubG9nKCdzZWxlY3RlZFNwZWNpYWxpemF0aW9uSWQ6ICcsIHNlbGVjdGVkU3BlY2lhbGl6YXRpb25JZClcbiAgdHJ5IHtcbiAgICBjb25zdCByZXNwb25zZSA9IGF3YWl0IGFwaS5nZXQoYC9nZXRfcHJvZHVjdF9jYXRlZ29yaWVzLyR7c2VsZWN0ZWRTcGVjaWFsaXphdGlvbklkLmlkfWApXG4gICAgcHJvZHVjdENhdGVnb3JpZXMudmFsdWUgPSByZXNwb25zZS5kYXRhXG4gIH0gY2F0Y2ggKGVycikge1xuICAgIGNvbnNvbGUuZXJyb3IoZXJyKVxuICB9XG59XG5cbmNvbnN0IGdldFByb2R1Y3RzQnlQcm9kdWN0Q2F0ZWdvcmllcyA9IGFzeW5jICgpID0+IHtcbiAgdHJ5IHtcbiAgICBjb25zdCByZXNwb25zZSA9IGF3YWl0IGFwaS5nZXQoYC9nZXRfcHJvZHVjdF9zdG9ja3MvJHtzZWxlY3RlZFByb2R1Y3RDYXRlZ29yeS52YWx1ZS5pZH1gKVxuICAgIHByb2R1Y3RzLnZhbHVlID0gcmVzcG9uc2UuZGF0YVxuICAgIGNvbnNvbGUubG9nKCdwcm9kdWN0czogJywgcHJvZHVjdHMudmFsdWUpXG4gIH0gY2F0Y2ggKGVycil7XG4gICAgY29uc29sZS5lcnJvcihlcnIpXG4gIH1cbn1cblxuY29uc3Qgb3BlbkFkZFByb2R1Y3RDYXRlZ29yeURpYWxvZyA9ICgpID0+IHtcbiAgcHJvZHVjdENhdGVnb3J5RGlhbG9nLnZhbHVlLm9wZW4oKVxufVxuXG5jb25zdCBvcGVuRWRpdFByb2R1Y3RDYXRlZ29yeURpYWxvZyA9ICgpID0+IHtcbiAgY29uc29sZS5sb2coJ9C+0YLQutGA0YvQstCw0LXRgtC8INGA0LXQtNCw0LrRgtC40YDQvtCy0LDQvdC40LUg0LrQsNGC0LXQs9C+0YDQuNC4OiAnLCBzZWxlY3RlZFByb2R1Y3RDYXRlZ29yeS52YWx1ZSlcbiAgaWYgKCFzZWxlY3RlZFByb2R1Y3RDYXRlZ29yeS52YWx1ZSkgcmV0dXJuXG4gIHByb2R1Y3RDYXRlZ29yeURpYWxvZy52YWx1ZS5vcGVuKHNlbGVjdGVkUHJvZHVjdENhdGVnb3J5LnZhbHVlKVxufVxuXG5jb25zdCBvcGVuQWRkUHJvZHVjdERpYWxvZyA9ICgpID0+IHtcbiAgc2VsZWN0ZWRQcm9kdWN0LnZhbHVlID0gbnVsbFxuICBwcm9kdWN0RGlhbG9nLnZhbHVlLm9wZW4obnVsbCwgc2VsZWN0ZWRQcm9kdWN0Q2F0ZWdvcnkudmFsdWUsIGZhbHNlKVxufVxuXG5jb25zdCBvcGVuRGV0YWlsUHJvZHVjdERpYWxvZyA9IChwcm9kdWN0KSA9PiB7XG4gIHNlbGVjdGVkUHJvZHVjdC52YWx1ZSA9IHByb2R1Y3RcbiAgcHJvZHVjdERpYWxvZy52YWx1ZS5vcGVuKHByb2R1Y3QsIG51bGwsIHRydWUpXG59XG5cbmNvbnN0IGhhbmRsZVByb2R1Y3RDYXRlZ29yeVNhdmVkID0gKCkgPT4ge1xuICBnZXRQcm9kdWN0Q2F0ZWdvcmllcygpXG4gIHNlbGVjdGVkUHJvZHVjdENhdGVnb3J5LnZhbHVlID0gbnVsbFxufVxuXG5jb25zdCBoYW5kbGVQcm9kdWN0QWRkZWQgPSAoKSA9PiB7XG4gIGdldFByb2R1Y3RzQnlQcm9kdWN0Q2F0ZWdvcmllcygpXG4gIHNlbGVjdGVkUHJvZHVjdC52YWx1ZSA9IG51bGxcbn1cblxuPC9zY3JpcHQ+XG5cbjx0ZW1wbGF0ZT5cbjxkaXYgY2xhc3M9XCJyb3cgaXRlbXMtY2VudGVyXCI+XG4gIDxxLXNlbGVjdCB2LW1vZGVsPVwic2VsZWN0ZWRQcm9kdWN0Q2F0ZWdvcnlcIlxuICAgICAgICAgICAgOm9wdGlvbnM9XCJwcm9kdWN0Q2F0ZWdvcmllc1wiXG4gICAgICAgICAgICBvcHRpb24tbGFiZWw9XCJuYW1lXCJcbiAgICAgICAgICAgIGVtaXQtdmFsdWVcbiAgICAgICAgICAgIG1hcC1vcHRpb25zXG4gICAgICAgICAgICBsYWJlbD1cItC60LDRgtC10LPQvtGA0LjRjyDRgtC+0LLQsNGA0LBcIlxuICAgICAgICAgICAgZGVuc2VcbiAgICAgICAgICAgIHBsYWNlaG9sZGVyPVwi0L3QtdGCINC60LDRgtC10LPQvtGA0LjQuVwiXG4gICAgICAgICAgICBsYWJlbC1jb2xvcj1cImdyZXlcIlxuICAgICAgICAgICAgY29sb3I9XCJ5ZWxsb3dcIlxuICAgICAgICAgICAgY2xhc3M9XCJjb2wtOVwiXG4gICAgICAgICAgICBAdXBkYXRlOm1vZGVsLXZhbHVlPVwiZ2V0UHJvZHVjdHNCeVByb2R1Y3RDYXRlZ29yaWVzXCJcbiAgLz5cblxuICA8ZGl2IGNsYXNzPVwiY29sLWF1dG8gc2VsZi1lbmRcIj5cbiAgICA8cS1idG4gY2xhc3M9XCJjb2wtMSB0ZXh0LXllbGxvd1wiIEBjbGljaz1cIm9wZW5BZGRQcm9kdWN0Q2F0ZWdvcnlEaWFsb2dcIj4rPC9xLWJ0bj5cbiAgPC9kaXY+XG5cbiAgPGRpdiBjbGFzcz1cImNvbC1hdXRvIHNlbGYtZW5kXCI+XG4gICAgPHEtYnRuIGNsYXNzPVwiY29sLTEgdGV4dC15ZWxsb3dcIiBAY2xpY2s9XCJvcGVuRWRpdFByb2R1Y3RDYXRlZ29yeURpYWxvZ1wiIGljb249XCJlZGl0XCIgLz5cbiAgPC9kaXY+XG5cbjwvZGl2PlxuXG48cS1saXN0IGJvcmRlcmVkIHNlcGFyYXRvcj5cbiAgPHEtaXRlbS1sYWJlbCB2LWlmPVwiIXByb2R1Y3RzXCI+0L3QtdGCINGC0L7QstCw0YDQvtCyPC9xLWl0ZW0tbGFiZWw+XG4gIDxxLWl0ZW0gdi1mb3I9XCJwcm9kdWN0IGluIHByb2R1Y3RzXCJcbiAgICAgICAgICA6a2V5PVwicHJvZHVjdFwiXG4gICAgICAgICAgY2xhc3M9XCJ3LTEwMCBqdXN0aWZ5LWJldHdlZW5cIlxuICAgICAgICAgIHN0eWxlPVwid2lkdGg6IDEwMCVcIlxuICAgICAgICAgIGNsaWNrYWJsZVxuICAgICAgICAgIHYtcmlwcGxlXG4gICAgICAgICAgQGNsaWNrPVwib3BlbkRldGFpbFByb2R1Y3REaWFsb2cocHJvZHVjdClcIlxuICAgICAgICAgIDpxLWl0ZW1cbiAgPlxuICAgIDxxLWl0ZW0tc2VjdGlvbiBjbGFzcz1cImNvbC04XCI+XG4gICAgICA8cS1pdGVtLWxhYmVsIGNsYXNzPVwidGV4dC1sZWZ0XCI+XG4gICAgICAgIHt7IHByb2R1Y3QubmFtZSB9fVxuICAgICAgPC9xLWl0ZW0tbGFiZWw+XG4gICAgPC9xLWl0ZW0tc2VjdGlvbj5cblxuXG4gICAgPHEtaXRlbS1zZWN0aW9uIGNsYXNzPVwiY29sLTFcIj5cbiAgICAgIDxxLWl0ZW0tbGFiZWwgY2xhc3M9XCJ0ZXh0LWNlbnRlclwiPlxuICAgICAgICB7eyBwcm9kdWN0LnF1YW50aXR5IH19XG4gICAgICA8L3EtaXRlbS1sYWJlbD5cbiAgICA8L3EtaXRlbS1zZWN0aW9uPlxuXG4gICAgPHEtaXRlbS1zZWN0aW9uIGNsYXNzPVwiY29sLTJcIj5cbiAgICAgIDxxLWl0ZW0tbGFiZWwgY2xhc3M9XCJ0ZXh0LXJpZ2h0XCI+XG4gICAgICAgIHt7cHJvZHVjdC5iYXNlX3NhbGVfcHJpY2V9fVxuICAgICAgPC9xLWl0ZW0tbGFiZWw+XG4gICAgPC9xLWl0ZW0tc2VjdGlvbj5cblxuICA8L3EtaXRlbT5cblxuPC9xLWxpc3Q+XG5cbjxxLWJ0blxuICBpY29uPVwiYWRkXCJcbiAgcm91bmRcbiAgY2xhc3M9XCJmYWIgYmcteWVsbG93IHRleHQtYmxhY2tcIlxuICBAY2xpY2s9XCJvcGVuQWRkUHJvZHVjdERpYWxvZ1wiXG4gIHNpemU9XCIyMHB4XCJcbi8+XG5cbjxQcm9kdWN0RGlhbG9nUGFnZSByZWY9XCJwcm9kdWN0RGlhbG9nXCIgIEBwcm9kdWN0LWFkZGVkPVwiaGFuZGxlUHJvZHVjdEFkZGVkXCIgLz5cbjxQcm9kdWN0Q2F0ZWdvcnlEaWFsb2dQYWdlIHJlZj1cInByb2R1Y3RDYXRlZ29yeURpYWxvZ1wiIEBwcm9kdWN0LWNhdGVnb3J5LXNhdmVkPVwiaGFuZGxlUHJvZHVjdENhdGVnb3J5U2F2ZWRcIiAgLz5cblxuPC90ZW1wbGF0ZT5cblxuPHN0eWxlIHNjb3BlZD5cblxuLmZhYiB7XG4gIHBvc2l0aW9uOiBmaXhlZDtcbiAgYm90dG9tOiA3MHB4O1xuICByaWdodDogMTZweDtcbiAgei1pbmRleDogMTAwMDsgLyog0YfRgtC+0LHRiyDQutC90L7Qv9C60LAg0LHRi9C70LAg0L/QvtCy0LXRgNGFINC+0YHRgtCw0LvRjNC90YvRhSDRjdC70LXQvNC10L3RgtC+0LIgKi9cbn1cblxuXG48L3N0eWxlPlxuIl0sIm5hbWVzIjpbIl9ob2lzdGVkXzEiLCJfaG9pc3RlZF8yIiwiX29wZW5CbG9jayIsIl9jcmVhdGVFbGVtZW50QmxvY2siLCJfRnJhZ21lbnQiLCJfY3JlYXRlVk5vZGUiLCJfd2l0aEN0eCIsIl9jcmVhdGVFbGVtZW50Vk5vZGUiLCJfdG9EaXNwbGF5U3RyaW5nIiwiX2NyZWF0ZUJsb2NrIiwiX2NyZWF0ZUNvbW1lbnRWTm9kZSIsIl9ob2lzdGVkXzMiLCJjbG9zZSIsIl9jcmVhdGVUZXh0Vk5vZGUiLCJfcmVuZGVyTGlzdCJdLCJtYXBwaW5ncyI6Ijs7Ozs7OztBQUlBLE1BQUEsU0FBZSxnQkFBZ0I7QUFBQSxFQUM3QixNQUFNO0FBQUEsRUFFTixRQUFTO0FBQ1AsVUFBTSxRQUFRLEVBQUUsT0FBTyxFQUFFLE9B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
