import { _ as _export_sfc, a3 as useAuthStore, J as useRouter, r as ref, b as computed, n as onMounted, D as openBlock, K as createElementBlock, G as createVNode, F as withCtx, O as QBtn, R as createBaseVNode, S as toDisplayString, I as createCommentVNode, L as Fragment, M as createTextVNode } from "./index-74sOg8Nl.js";
import { Q as QSelect } from "./QSelect-DGItPn-E.js";
import { u as useSpecializationsStore } from "./specializations-B0lcZ67D.js";
import { api } from "./axios-D58jYJIV.js";
import { u as useQuasar } from "./use-quasar-DEOs6wIv.js";
import "./QItem-DuqkKkh7.js";
import "./rtl-DDpZOXNn.js";
const version = "0.0.2";
const _sfc_main = {
  __name: "OtherPage",
  setup(__props, { expose: __expose }) {
    __expose();
    const $q = useQuasar();
    const authStore = useAuthStore();
    const router = useRouter();
    const specializationStore = useSpecializationsStore();
    const selectedSpecialization = ref(specializationStore.selectSpecialization);
    const specializations = computed(() => specializationStore.specializations);
    const newVersion = ref("");
    const newVersionAnable = ref(null);
    onMounted(() => {
      selectedSpecialization.value = specializationStore.selectedSpecialization;
    });
    const changeSpecialization = () => {
      specializationStore.setSelectedSpecialization(selectedSpecialization.value);
      console.log("текущая специализация: ", specializationStore.selectedSpecialization);
    };
    const logout = () => {
      authStore.logout();
      localStorage.removeItem("authToken");
      router.push("/login");
    };
    const checkAppVersion = async () => {
      try {
        const response = await api.get(`/app-quasar-android-version`);
        console.log("version: ", response);
        if (version === response.data.version) {
          newVersionAnable.value = false;
        } else {
          newVersion.value = response.data.version;
          newVersionAnable.value = true;
        }
      } catch (err) {
        console.error(err);
      }
    };
    const downloadNewVersion = async () => {
      try {
        const response = await api.get("/download-latest-android-apk", {
          responseType: "blob"
        });
        const contentDisposition = response.headers["content-disposition"];
        let fileName = "LedgerCraft-latest.apk";
        if (contentDisposition && contentDisposition.includes("filename=")) {
          fileName = contentDisposition.split("filename=")[1].split(";")[0].replace(/['"]/g, "");
        }
        const filePath = cordova.file.externalRootDirectory + fileName;
        const writeFile = (fileEntry, dataObj) => {
          return new Promise((resolve, reject) => {
            fileEntry.createWriter((fileWriter) => {
              fileWriter.onwriteend = () => {
                resolve();
              };
              fileWriter.onerror = (e) => {
                reject(e);
              };
              fileWriter.write(dataObj);
            });
          });
        };
        window.resolveLocalFileSystemURL(cordova.file.externalRootDirectory, (dirEntry) => {
          dirEntry.getFile(fileName, { create: true }, (fileEntry) => {
            writeFile(fileEntry, response.data).then(() => {
              $q.notify({
                type: "positive",
                message: "Файл успешно скачан",
                position: "top",
                actions: [
                  {
                    label: "Установить",
                    color: "white",
                    handler: () => {
                      cordova.plugins.fileOpener2.open(
                        filePath,
                        "application/vnd.android.package-archive",
                        {
                          error: (e) => {
                            console.error("Ошибка при открытии файла:", e);
                            $q.notify({
                              type: "negative",
                              message: "Ошибка при установке файла",
                              position: "top"
                            });
                          },
                          success: () => {
                            console.log("Файл успешно открыт для установки");
                          }
                        }
                      );
                    }
                  }
                ]
              });
            }).catch((err) => {
              console.error("Ошибка при записи файла:", err);
              $q.notify({
                type: "negative",
                message: "Ошибка при сохранении файла",
                position: "top"
              });
            });
          });
        });
      } catch (err) {
        console.error(err);
        $q.notify({
          type: "negative",
          message: "Ошибка при загрузке файла",
          position: "top"
        });
      }
    };
    const __returned__ = { $q, authStore, router, specializationStore, selectedSpecialization, specializations, newVersion, newVersionAnable, changeSpecialization, logout, checkAppVersion, downloadNewVersion, get useAuthStore() {
      return useAuthStore;
    }, get useRouter() {
      return useRouter;
    }, get useSpecializationsStore() {
      return useSpecializationsStore;
    }, computed, onMounted, ref, get api() {
      return api;
    }, get useQuasar() {
      return useQuasar;
    }, get version() {
      return version;
    } };
    Object.defineProperty(__returned__, "__isScriptSetup", { enumerable: false, value: true });
    return __returned__;
  }
};
const _hoisted_1 = { key: 0 };
const _hoisted_2 = { key: 1 };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
  return openBlock(), createElementBlock(Fragment, null, [
    createVNode(QBtn, {
      class: "bg-primary",
      onClick: $setup.logout
    }, {
      default: withCtx(() => _cache[1] || (_cache[1] = [
        createTextVNode("Logout")
      ])),
      _: 1
    }),
    createBaseVNode("div", null, [
      createVNode(QSelect, {
        modelValue: $setup.selectedSpecialization,
        "onUpdate:modelValue": [
          _cache[0] || (_cache[0] = ($event) => $setup.selectedSpecialization = $event),
          $setup.changeSpecialization
        ],
        options: $setup.specializations,
        "option-value": "id",
        "option-label": "specializationName",
        label: "Выберите специализацию"
      }, null, 8, ["modelValue", "options"])
    ]),
    createBaseVNode("div", null, [
      _cache[2] || (_cache[2] = createBaseVNode("br", null, null, -1)),
      createBaseVNode("p", null, " Версия приложения: " + toDisplayString($setup.version), 1)
    ]),
    $setup.newVersionAnable === true ? (openBlock(), createElementBlock("div", _hoisted_1, [
      createBaseVNode("p", null, " Доступна новая версия " + toDisplayString($setup.newVersion), 1),
      createVNode(QBtn, { onClick: $setup.downloadNewVersion }, {
        default: withCtx(() => _cache[3] || (_cache[3] = [
          createTextVNode("скачать")
        ])),
        _: 1
      })
    ])) : createCommentVNode("", true),
    $setup.newVersionAnable === false ? (openBlock(), createElementBlock("div", _hoisted_2, _cache[4] || (_cache[4] = [
      createBaseVNode("p", null, " У Вас самая последняя версия ", -1)
    ]))) : createCommentVNode("", true),
    createBaseVNode("div", null, [
      createVNode(QBtn, {
        color: "yellow",
        "text-color": "black",
        onClick: $setup.checkAppVersion
      }, {
        default: withCtx(() => _cache[5] || (_cache[5] = [
          createTextVNode("проверить обновление")
        ])),
        _: 1
      })
    ])
  ], 64);
}
const OtherPage = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render], ["__file", "OtherPage.vue"]]);
export {
  OtherPage as default
};
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiT3RoZXJQYWdlLUN3NDhEcHZsLmpzIiwic291cmNlcyI6WyIuLi8uLi8uLi9zcmMvcGFnZXMvT3RoZXJQYWdlLnZ1ZSJdLCJzb3VyY2VzQ29udGVudCI6WyI8c2NyaXB0IHNldHVwPlxuaW1wb3J0IHsgdXNlQXV0aFN0b3JlIH0gZnJvbSAnc3RvcmVzL2F1dGgtc3RvcmUuanMnO1xuaW1wb3J0IHsgdXNlUm91dGVyIH0gZnJvbSAndnVlLXJvdXRlcic7XG5pbXBvcnQge3VzZVNwZWNpYWxpemF0aW9uc1N0b3JlfSBmcm9tIFwic3RvcmVzL3NwZWNpYWxpemF0aW9ucy5qc1wiO1xuaW1wb3J0IHtjb21wdXRlZCwgb25Nb3VudGVkLCByZWZ9IGZyb20gJ3Z1ZSdcbmltcG9ydCB7IGFwaSB9IGZyb20gJ2Jvb3QvYXhpb3MuanMnXG5pbXBvcnQge3VzZVF1YXNhcn0gZnJvbSBcInF1YXNhclwiO1xuXG5jb25zdCAkcSA9IHVzZVF1YXNhcigpXG5cbmltcG9ydCB7dmVyc2lvbn0gZnJvbSAnLi4vLi4vcGFja2FnZS5qc29uJ1xuLy9pbXBvcnQgc3BlY2lhbGl6YXRpb25zIGZyb20gXCJib290L3NwZWNpYWxpemF0aW9ucy5qc1wiO1xuXG5jb25zdCBhdXRoU3RvcmUgPSB1c2VBdXRoU3RvcmUoKTtcbmNvbnN0IHJvdXRlciA9IHVzZVJvdXRlcigpO1xuY29uc3Qgc3BlY2lhbGl6YXRpb25TdG9yZSA9IHVzZVNwZWNpYWxpemF0aW9uc1N0b3JlKClcblxuY29uc3Qgc2VsZWN0ZWRTcGVjaWFsaXphdGlvbiA9IHJlZihzcGVjaWFsaXphdGlvblN0b3JlLnNlbGVjdFNwZWNpYWxpemF0aW9uKVxuXG5jb25zdCBzcGVjaWFsaXphdGlvbnMgPSBjb21wdXRlZCgoKSA9PiBzcGVjaWFsaXphdGlvblN0b3JlLnNwZWNpYWxpemF0aW9ucylcblxuY29uc3QgbmV3VmVyc2lvbiA9IHJlZignJylcblxuY29uc3QgbmV3VmVyc2lvbkFuYWJsZSA9IHJlZihudWxsKVxuXG5vbk1vdW50ZWQoKCkgPT4ge1xuICBzZWxlY3RlZFNwZWNpYWxpemF0aW9uLnZhbHVlID0gc3BlY2lhbGl6YXRpb25TdG9yZS5zZWxlY3RlZFNwZWNpYWxpemF0aW9uXG59KVxuXG5jb25zdCBjaGFuZ2VTcGVjaWFsaXphdGlvbiA9ICgpID0+IHtcbiAgc3BlY2lhbGl6YXRpb25TdG9yZS5zZXRTZWxlY3RlZFNwZWNpYWxpemF0aW9uKHNlbGVjdGVkU3BlY2lhbGl6YXRpb24udmFsdWUpXG4gIGNvbnNvbGUubG9nKCfRgtC10LrRg9GJ0LDRjyDRgdC/0LXRhtC40LDQu9C40LfQsNGG0LjRjzogJywgc3BlY2lhbGl6YXRpb25TdG9yZS5zZWxlY3RlZFNwZWNpYWxpemF0aW9uKVxufVxuLy8g0KTRg9C90LrRhtC40Y8g0LLRi9GF0L7QtNCwINC40Lcg0YHQuNGB0YLQtdC80YtcbmNvbnN0IGxvZ291dCA9ICgpID0+IHtcbiAgYXV0aFN0b3JlLmxvZ291dCgpOyAvLyDQntGH0LjRgdGC0LrQsCDRgtC+0LrQtdC90LAg0LIgUGluaWFcbiAgbG9jYWxTdG9yYWdlLnJlbW92ZUl0ZW0oJ2F1dGhUb2tlbicpOyAvLyDQo9C00LDQu9C10L3QuNC1INGC0L7QutC10L3QsCDQuNC3INC70L7QutCw0LvRjNC90L7Qs9C+INGF0YDQsNC90LjQu9C40YnQsFxuICByb3V0ZXIucHVzaCgnL2xvZ2luJyk7IC8vINCf0LXRgNC10L3QsNC/0YDQsNCy0LvQtdC90LjQtSDQvdCwINGB0YLRgNCw0L3QuNGG0YMg0LLRhdC+0LTQsFxufTtcblxuY29uc3QgY2hlY2tBcHBWZXJzaW9uID0gYXN5bmMgKCkgPT4ge1xuICB0cnkge1xuICAgIGNvbnN0IHJlc3BvbnNlID0gYXdhaXQgYXBpLmdldChgL2FwcC1xdWFzYXItYW5kcm9pZC12ZXJzaW9uYClcbiAgICBjb25zb2xlLmxvZygndmVyc2lvbjogJywgcmVzcG9uc2UpXG4gICAgaWYgKHZlcnNpb24gPT09IHJlc3BvbnNlLmRhdGEudmVyc2lvbikge1xuICAgICAgbmV3VmVyc2lvbkFuYWJsZS52YWx1ZSA9IGZhbHNlXG4gICAgfSBlbHNlIHtcbiAgICAgIG5ld1ZlcnNpb24udmFsdWUgPSByZXNwb25zZS5kYXRhLnZlcnNpb25cbiAgICAgIG5ld1ZlcnNpb25BbmFibGUudmFsdWUgPSB0cnVlXG4gICAgfVxuICB9IGNhdGNoIChlcnIpIHtcbiAgICBjb25zb2xlLmVycm9yKGVycilcbiAgfVxufVxuXG5jb25zdCBkb3dubG9hZE5ld1ZlcnNpb24gPSBhc3luYyAoKSA9PiB7XG4gIHRyeSB7XG4gICAgY29uc3QgcmVzcG9uc2UgPSBhd2FpdCBhcGkuZ2V0KCcvZG93bmxvYWQtbGF0ZXN0LWFuZHJvaWQtYXBrJywge1xuICAgICAgcmVzcG9uc2VUeXBlOiAnYmxvYidcbiAgICB9KTtcblxuICAgIC8vINCY0LfQstC70LXQutCw0LXQvCDQuNC80Y8g0YTQsNC50LvQsCDQuNC3INC30LDQs9C+0LvQvtCy0LrQvtCyINC+0YLQstC10YLQsFxuICAgIGNvbnN0IGNvbnRlbnREaXNwb3NpdGlvbiA9IHJlc3BvbnNlLmhlYWRlcnNbJ2NvbnRlbnQtZGlzcG9zaXRpb24nXTtcbiAgICBsZXQgZmlsZU5hbWUgPSAnTGVkZ2VyQ3JhZnQtbGF0ZXN0LmFwayc7XG4gICAgaWYgKGNvbnRlbnREaXNwb3NpdGlvbiAmJiBjb250ZW50RGlzcG9zaXRpb24uaW5jbHVkZXMoJ2ZpbGVuYW1lPScpKSB7XG4gICAgICBmaWxlTmFtZSA9IGNvbnRlbnREaXNwb3NpdGlvblxuICAgICAgICAuc3BsaXQoJ2ZpbGVuYW1lPScpWzFdXG4gICAgICAgIC5zcGxpdCgnOycpWzBdXG4gICAgICAgIC5yZXBsYWNlKC9bJ1wiXS9nLCAnJyk7IC8vINCj0LHQuNGA0LDQtdC8INC60LDQstGL0YfQutC4LCDQtdGB0LvQuCDQvtC90Lgg0LXRgdGC0YxcbiAgICB9XG5cbiAgICAvLyDQodC+0YXRgNCw0L3Rj9C10Lwg0YTQsNC50Lsg0L3QsCDRg9GB0YLRgNC+0LnRgdGC0LLQvlxuICAgIGNvbnN0IGZpbGVQYXRoID0gY29yZG92YS5maWxlLmV4dGVybmFsUm9vdERpcmVjdG9yeSArIGZpbGVOYW1lO1xuXG4gICAgY29uc3Qgd3JpdGVGaWxlID0gKGZpbGVFbnRyeSwgZGF0YU9iaikgPT4ge1xuICAgICAgcmV0dXJuIG5ldyBQcm9taXNlKChyZXNvbHZlLCByZWplY3QpID0+IHtcbiAgICAgICAgZmlsZUVudHJ5LmNyZWF0ZVdyaXRlcigoZmlsZVdyaXRlcikgPT4ge1xuICAgICAgICAgIGZpbGVXcml0ZXIub253cml0ZWVuZCA9ICgpID0+IHtcbiAgICAgICAgICAgIHJlc29sdmUoKTtcbiAgICAgICAgICB9O1xuICAgICAgICAgIGZpbGVXcml0ZXIub25lcnJvciA9IChlKSA9PiB7XG4gICAgICAgICAgICByZWplY3QoZSk7XG4gICAgICAgICAgfTtcbiAgICAgICAgICBmaWxlV3JpdGVyLndyaXRlKGRhdGFPYmopO1xuICAgICAgICB9KTtcbiAgICAgIH0pO1xuICAgIH07XG5cbiAgICB3aW5kb3cucmVzb2x2ZUxvY2FsRmlsZVN5c3RlbVVSTChjb3Jkb3ZhLmZpbGUuZXh0ZXJuYWxSb290RGlyZWN0b3J5LCAoZGlyRW50cnkpID0+IHtcbiAgICAgIGRpckVudHJ5LmdldEZpbGUoZmlsZU5hbWUsIHsgY3JlYXRlOiB0cnVlIH0sIChmaWxlRW50cnkpID0+IHtcbiAgICAgICAgd3JpdGVGaWxlKGZpbGVFbnRyeSwgcmVzcG9uc2UuZGF0YSkudGhlbigoKSA9PiB7XG4gICAgICAgICAgLy8g0KPQstC10LTQvtC80LvQtdC90LjQtSDQvtCxINGD0YHQv9C10YjQvdC+0Lwg0YHQutCw0YfQuNCy0LDQvdC40LhcbiAgICAgICAgICAkcS5ub3RpZnkoe1xuICAgICAgICAgICAgdHlwZTogJ3Bvc2l0aXZlJyxcbiAgICAgICAgICAgIG1lc3NhZ2U6ICfQpNCw0LnQuyDRg9GB0L/QtdGI0L3QviDRgdC60LDRh9Cw0L0nLFxuICAgICAgICAgICAgcG9zaXRpb246ICd0b3AnLFxuICAgICAgICAgICAgYWN0aW9uczogW1xuICAgICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgbGFiZWw6ICfQo9GB0YLQsNC90L7QstC40YLRjCcsXG4gICAgICAgICAgICAgICAgY29sb3I6ICd3aGl0ZScsXG4gICAgICAgICAgICAgICAgaGFuZGxlcjogKCkgPT4ge1xuICAgICAgICAgICAgICAgICAgLy8g0J7RgtC60YDRi9Cy0LDQtdC8INGE0LDQudC7INC00LvRjyDRg9GB0YLQsNC90L7QstC60LhcbiAgICAgICAgICAgICAgICAgIGNvcmRvdmEucGx1Z2lucy5maWxlT3BlbmVyMi5vcGVuKFxuICAgICAgICAgICAgICAgICAgICBmaWxlUGF0aCxcbiAgICAgICAgICAgICAgICAgICAgJ2FwcGxpY2F0aW9uL3ZuZC5hbmRyb2lkLnBhY2thZ2UtYXJjaGl2ZScsXG4gICAgICAgICAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICAgICAgICBlcnJvcjogKGUpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGNvbnNvbGUuZXJyb3IoJ9Ce0YjQuNCx0LrQsCDQv9GA0Lgg0L7RgtC60YDRi9GC0LjQuCDRhNCw0LnQu9CwOicsIGUpO1xuICAgICAgICAgICAgICAgICAgICAgICAgJHEubm90aWZ5KHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgdHlwZTogJ25lZ2F0aXZlJyxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgbWVzc2FnZTogJ9Ce0YjQuNCx0LrQsCDQv9GA0Lgg0YPRgdGC0LDQvdC+0LLQutC1INGE0LDQudC70LAnLFxuICAgICAgICAgICAgICAgICAgICAgICAgICBwb3NpdGlvbjogJ3RvcCdcbiAgICAgICAgICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICAgICAgICAgICAgc3VjY2VzczogKCkgPT4ge1xuICAgICAgICAgICAgICAgICAgICAgICAgY29uc29sZS5sb2coJ9Ck0LDQudC7INGD0YHQv9C10YjQvdC+INC+0YLQutGA0YvRgiDQtNC70Y8g0YPRgdGC0LDQvdC+0LLQutC4Jyk7XG4gICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICApO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgXVxuICAgICAgICAgIH0pO1xuICAgICAgICB9KS5jYXRjaCgoZXJyKSA9PiB7XG4gICAgICAgICAgY29uc29sZS5lcnJvcign0J7RiNC40LHQutCwINC/0YDQuCDQt9Cw0L/QuNGB0Lgg0YTQsNC50LvQsDonLCBlcnIpO1xuICAgICAgICAgICRxLm5vdGlmeSh7XG4gICAgICAgICAgICB0eXBlOiAnbmVnYXRpdmUnLFxuICAgICAgICAgICAgbWVzc2FnZTogJ9Ce0YjQuNCx0LrQsCDQv9GA0Lgg0YHQvtGF0YDQsNC90LXQvdC40Lgg0YTQsNC50LvQsCcsXG4gICAgICAgICAgICBwb3NpdGlvbjogJ3RvcCdcbiAgICAgICAgICB9KTtcbiAgICAgICAgfSk7XG4gICAgICB9KTtcbiAgICB9KTtcblxuICB9IGNhdGNoIChlcnIpIHtcbiAgICBjb25zb2xlLmVycm9yKGVycik7XG4gICAgJHEubm90aWZ5KHtcbiAgICAgIHR5cGU6ICduZWdhdGl2ZScsXG4gICAgICBtZXNzYWdlOiAn0J7RiNC40LHQutCwINC/0YDQuCDQt9Cw0LPRgNGD0LfQutC1INGE0LDQudC70LAnLFxuICAgICAgcG9zaXRpb246ICd0b3AnXG4gICAgfSk7XG4gIH1cbn07XG5cbjwvc2NyaXB0PlxuXG48dGVtcGxhdGU+XG4gIDxxLWJ0biBjbGFzcz1cImJnLXByaW1hcnlcIiBAY2xpY2s9XCJsb2dvdXRcIj5Mb2dvdXQ8L3EtYnRuPlxuXG4gIDxkaXY+XG4gICAgPHEtc2VsZWN0IHYtbW9kZWw9XCJzZWxlY3RlZFNwZWNpYWxpemF0aW9uXCJcbiAgICAgICAgICAgICAgOm9wdGlvbnM9XCJzcGVjaWFsaXphdGlvbnNcIlxuICAgICAgICAgICAgICBvcHRpb24tdmFsdWU9XCJpZFwiXG4gICAgICAgICAgICAgIG9wdGlvbi1sYWJlbD1cInNwZWNpYWxpemF0aW9uTmFtZVwiXG4gICAgICAgICAgICAgIGxhYmVsPVwi0JLRi9Cx0LXRgNC40YLQtSDRgdC/0LXRhtC40LDQu9C40LfQsNGG0LjRjlwiXG4gICAgICAgICAgICAgIEB1cGRhdGU6bW9kZWwtdmFsdWU9XCJjaGFuZ2VTcGVjaWFsaXphdGlvblwiPlxuICAgIDwvcS1zZWxlY3Q+XG4gIDwvZGl2PlxuXG4gIDxkaXY+XG4gICAgPGJyPlxuICAgIDxwPiDQktC10YDRgdC40Y8g0L/RgNC40LvQvtC20LXQvdC40Y86IHt7dmVyc2lvbn19PC9wPlxuICA8L2Rpdj5cblxuICA8ZGl2IHYtaWY9XCJuZXdWZXJzaW9uQW5hYmxlID09PSB0cnVlXCI+XG4gICAgPHA+INCU0L7RgdGC0YPQv9C90LAg0L3QvtCy0LDRjyDQstC10YDRgdC40Y8ge3tuZXdWZXJzaW9ufX08L3A+XG4gICAgPHEtYnRuIEBjbGljaz1cImRvd25sb2FkTmV3VmVyc2lvblwiID7RgdC60LDRh9Cw0YLRjDwvcS1idG4+XG4gIDwvZGl2PlxuXG4gIDxkaXYgdi1pZj1cIm5ld1ZlcnNpb25BbmFibGUgPT09IGZhbHNlXCI+XG4gICAgPHA+INCjINCS0LDRgSDRgdCw0LzQsNGPINC/0L7RgdC70LXQtNC90Y/RjyDQstC10YDRgdC40Y8gPC9wPlxuICA8L2Rpdj5cblxuICA8ZGl2PlxuICAgIDxxLWJ0biBjb2xvcj1cInllbGxvd1wiIHRleHQtY29sb3I9XCJibGFja1wiIEBjbGljaz1cImNoZWNrQXBwVmVyc2lvblwiPtC/0YDQvtCy0LXRgNC40YLRjCDQvtCx0L3QvtCy0LvQtdC90LjQtTwvcS1idG4+XG4gIDwvZGl2PlxuXG48L3RlbXBsYXRlPlxuXG48c3R5bGUgc2NvcGVkPlxuPC9zdHlsZT5cbiJdLCJuYW1lcyI6WyJfb3BlbkJsb2NrIiwiX2NyZWF0ZUVsZW1lbnRCbG9jayIsIl9GcmFnbWVudCIsIl9jcmVhdGVWTm9kZSIsIl93aXRoQ3R4IiwiX2NyZWF0ZVRleHRWTm9kZSIsIl9jcmVhdGVFbGVtZW50Vk5vZGUiLCJfdG9EaXNwbGF5U3RyaW5nIiwiX2NyZWF0ZUNvbW1lbnRWTm9kZSJdLCJtYXBwaW5ncyI6Ijs7Ozs7Ozs7Ozs7O0FBUUEsVUFBTSxLQUFLLFVBQVM7QUFLcEIsVUFBTSxZQUFZLGFBQWM7QUFDaEMsVUFBTSxTQUFTLFVBQVc7QUFDMUIsVUFBTSxzQkFBc0Isd0JBQXVCO0FBRW5ELFVBQU0seUJBQXlCLElBQUksb0JBQW9CLG9CQUFvQjtBQUUzRSxVQUFNLGtCQUFrQixTQUFTLE1BQU0sb0JBQW9CLGVBQWU7QUFFMUUsVUFBTSxhQUFhLElBQUksRUFBRTtBQUV6QixVQUFNLG1CQUFtQixJQUFJLElBQUk7QUFFakMsY0FBVSxNQUFNO0FBQ2QsNkJBQXVCLFFBQVEsb0JBQW9CO0FBQUEsSUFDckQsQ0FBQztBQUVELFVBQU0sdUJBQXVCLE1BQU07QUFDakMsMEJBQW9CLDBCQUEwQix1QkFBdUIsS0FBSztBQUMxRSxjQUFRLElBQUksMkJBQTJCLG9CQUFvQixzQkFBc0I7QUFBQSxJQUNuRjtBQUVBLFVBQU0sU0FBUyxNQUFNO0FBQ25CLGdCQUFVLE9BQU07QUFDaEIsbUJBQWEsV0FBVyxXQUFXO0FBQ25DLGFBQU8sS0FBSyxRQUFRO0FBQUEsSUFDdEI7QUFFQSxVQUFNLGtCQUFrQixZQUFZO0FBQ2xDLFVBQUk7QUFDRixjQUFNLFdBQVcsTUFBTSxJQUFJLElBQUksNkJBQTZCO0FBQzVELGdCQUFRLElBQUksYUFBYSxRQUFRO0FBQ2pDLFlBQUksWUFBWSxTQUFTLEtBQUssU0FBUztBQUNyQywyQkFBaUIsUUFBUTtBQUFBLFFBQy9CLE9BQVc7QUFDTCxxQkFBVyxRQUFRLFNBQVMsS0FBSztBQUNqQywyQkFBaUIsUUFBUTtBQUFBLFFBQy9CO0FBQUEsTUFDRyxTQUFRLEtBQUs7QUFDWixnQkFBUSxNQUFNLEdBQUc7QUFBQSxNQUNyQjtBQUFBLElBQ0E7QUFFQSxVQUFNLHFCQUFxQixZQUFZO0FBQ3JDLFVBQUk7QUFDRixjQUFNLFdBQVcsTUFBTSxJQUFJLElBQUksZ0NBQWdDO0FBQUEsVUFDN0QsY0FBYztBQUFBLFFBQ3BCLENBQUs7QUFHRCxjQUFNLHFCQUFxQixTQUFTLFFBQVEscUJBQXFCO0FBQ2pFLFlBQUksV0FBVztBQUNmLFlBQUksc0JBQXNCLG1CQUFtQixTQUFTLFdBQVcsR0FBRztBQUNsRSxxQkFBVyxtQkFDUixNQUFNLFdBQVcsRUFBRSxDQUFDLEVBQ3BCLE1BQU0sR0FBRyxFQUFFLENBQUMsRUFDWixRQUFRLFNBQVMsRUFBRTtBQUFBLFFBQzVCO0FBR0ksY0FBTSxXQUFXLFFBQVEsS0FBSyx3QkFBd0I7QUFFdEQsY0FBTSxZQUFZLENBQUMsV0FBVyxZQUFZO0FBQ3hDLGlCQUFPLElBQUksUUFBUSxDQUFDLFNBQVMsV0FBVztBQUN0QyxzQkFBVSxhQUFhLENBQUMsZUFBZTtBQUNyQyx5QkFBVyxhQUFhLE1BQU07QUFDNUIsd0JBQVM7QUFBQSxjQUNWO0FBQ0QseUJBQVcsVUFBVSxDQUFDLE1BQU07QUFDMUIsdUJBQU8sQ0FBQztBQUFBLGNBQ1Q7QUFDRCx5QkFBVyxNQUFNLE9BQU87QUFBQSxZQUNsQyxDQUFTO0FBQUEsVUFDVCxDQUFPO0FBQUEsUUFDRjtBQUVELGVBQU8sMEJBQTBCLFFBQVEsS0FBSyx1QkFBdUIsQ0FBQyxhQUFhO0FBQ2pGLG1CQUFTLFFBQVEsVUFBVSxFQUFFLFFBQVEsS0FBSSxHQUFJLENBQUMsY0FBYztBQUMxRCxzQkFBVSxXQUFXLFNBQVMsSUFBSSxFQUFFLEtBQUssTUFBTTtBQUU3QyxpQkFBRyxPQUFPO0FBQUEsZ0JBQ1IsTUFBTTtBQUFBLGdCQUNOLFNBQVM7QUFBQSxnQkFDVCxVQUFVO0FBQUEsZ0JBQ1YsU0FBUztBQUFBLGtCQUNQO0FBQUEsb0JBQ0UsT0FBTztBQUFBLG9CQUNQLE9BQU87QUFBQSxvQkFDUCxTQUFTLE1BQU07QUFFYiw4QkFBUSxRQUFRLFlBQVk7QUFBQSx3QkFDMUI7QUFBQSx3QkFDQTtBQUFBLHdCQUNBO0FBQUEsMEJBQ0UsT0FBTyxDQUFDLE1BQU07QUFDWixvQ0FBUSxNQUFNLDhCQUE4QixDQUFDO0FBQzdDLCtCQUFHLE9BQU87QUFBQSw4QkFDUixNQUFNO0FBQUEsOEJBQ04sU0FBUztBQUFBLDhCQUNULFVBQVU7QUFBQSw0QkFDcEMsQ0FBeUI7QUFBQSwwQkFDRjtBQUFBLDBCQUNELFNBQVMsTUFBTTtBQUNiLG9DQUFRLElBQUksbUNBQW1DO0FBQUEsMEJBQ3ZFO0FBQUEsd0JBQ0E7QUFBQSxzQkFDbUI7QUFBQSxvQkFDbkI7QUFBQSxrQkFDQTtBQUFBLGdCQUNBO0FBQUEsY0FDQSxDQUFXO0FBQUEsWUFDWCxDQUFTLEVBQUUsTUFBTSxDQUFDLFFBQVE7QUFDaEIsc0JBQVEsTUFBTSw0QkFBNEIsR0FBRztBQUM3QyxpQkFBRyxPQUFPO0FBQUEsZ0JBQ1IsTUFBTTtBQUFBLGdCQUNOLFNBQVM7QUFBQSxnQkFDVCxVQUFVO0FBQUEsY0FDdEIsQ0FBVztBQUFBLFlBQ1gsQ0FBUztBQUFBLFVBQ1QsQ0FBTztBQUFBLFFBQ1AsQ0FBSztBQUFBLE1BRUYsU0FBUSxLQUFLO0FBQ1osZ0JBQVEsTUFBTSxHQUFHO0FBQ2pCLFdBQUcsT0FBTztBQUFBLFVBQ1IsTUFBTTtBQUFBLFVBQ04sU0FBUztBQUFBLFVBQ1QsVUFBVTtBQUFBLFFBQ2hCLENBQUs7QUFBQSxNQUNMO0FBQUEsSUFDQTs7Ozs7Ozs7Ozs7Ozs7Ozs7O3FCQTlJQSxLQUFBLEVBQUE7cUJBQUEsS0FBQSxFQUFBOztBQUFBLFNBQUFBLFVBQUEsR0FBQUMsbUJBQUFDLFVBQUEsTUFBQTtBQUFBLElBbUpFQyxZQUF3RCxNQUFBO0FBQUEsTUFBakQsT0FBTTtBQUFBLE1BQWMsU0FBTyxPQUFNO0FBQUE7TUFuSjFDLFNBQUFDLFFBbUo0QyxNQUFNLE9BQUEsQ0FBQSxNQUFBLE9BQUEsQ0FBQSxJQUFBO0FBQUEsUUFuSmxEQyxnQkFtSjRDLFFBQU07QUFBQTtNQW5KbEQsR0FBQTtBQUFBO0lBcUpFQyxnQkFRTSxPQUFBLE1BQUE7QUFBQSxNQVBKSCxZQU1XLFNBQUE7QUFBQSxRQTVKZixZQXNKdUIsT0FBc0I7QUFBQSxRQXRKN0MsdUJBQUE7QUFBQSxnREFzSnVCLE9BQXNCLHlCQUFBO0FBQUEsVUFLVixPQUFvQjtBQUFBO1FBSnhDLFNBQVMsT0FBZTtBQUFBLFFBQ3pCLGdCQUFhO0FBQUEsUUFDYixnQkFBYTtBQUFBLFFBQ2IsT0FBTTtBQUFBOztJQUtsQkcsZ0JBR00sT0FBQSxNQUFBO0FBQUEsZ0NBRkpBLGdCQUFJLE1BQUEsTUFBQSxNQUFBLEVBQUE7QUFBQSxNQUNKQSxnQkFBc0MsS0FBQSxNQUFuQyx5QkFBb0JDLGdCQUFFLE9BQU8sT0FBQSxHQUFBLENBQUE7QUFBQTtJQUd2QixPQUFnQixxQkFBQSxRQUEzQlAsYUFBQUMsbUJBR00sT0F2S1IsWUFBQTtBQUFBLE1BcUtJSyxnQkFBNEMsS0FBQSxNQUF6Qyw0QkFBdUJDLGdCQUFFLE9BQVUsVUFBQSxHQUFBLENBQUE7QUFBQSxNQUN0Q0osWUFBbUQsTUFBQSxFQUFBLFNBQUEsT0FBdEMsbUJBQW9CLEdBQUE7QUFBQSxRQXRLckMsU0FBQUMsUUFzS3dDLE1BQU8sT0FBQSxDQUFBLE1BQUEsT0FBQSxDQUFBLElBQUE7QUFBQSxVQXRLL0NDLGdCQXNLd0MsU0FBTztBQUFBO1FBdEsvQyxHQUFBO0FBQUE7VUFBQUcsbUJBQUEsSUFBQSxJQUFBO0FBQUEsSUF5S2EsT0FBZ0IscUJBQUEsU0FBM0JSLFVBQUEsR0FBQUMsbUJBRU0sT0EzS1IsWUFBQSxPQUFBLENBQUEsTUFBQSxPQUFBLENBQUEsSUFBQTtBQUFBLE1BMEtJSyxnQkFBcUMsV0FBbEMsa0NBQThCLEVBQUE7QUFBQSxXQTFLckNFLG1CQUFBLElBQUEsSUFBQTtBQUFBLElBNktFRixnQkFFTSxPQUFBLE1BQUE7QUFBQSxNQURKSCxZQUE4RixNQUFBO0FBQUEsUUFBdkYsT0FBTTtBQUFBLFFBQVMsY0FBVztBQUFBLFFBQVMsU0FBTyxPQUFlO0FBQUE7UUE5S3BFLFNBQUFDLFFBOEtzRSxNQUFvQixPQUFBLENBQUEsTUFBQSxPQUFBLENBQUEsSUFBQTtBQUFBLFVBOUsxRkMsZ0JBOEtzRSxzQkFBb0I7QUFBQTtRQTlLMUYsR0FBQTtBQUFBOzs7OzsifQ==
