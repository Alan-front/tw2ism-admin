<template>
  <div id="app">
    <header class="app-header">
      <div class="header-inner">
        <span class="header-logo">TW2ISM</span>
        <span class="header-sub">FILE MANAGER</span>
        <p>total altura: <span id="totalAltura">{{ alturaTotal }}</span></p>
      </div>
      <div class="header-actions">
        <div class="upload-group">
          <input
            v-model="audioLink"
            type="text"
            placeholder="URL de audio..."
            class="text-input"
            style="width: 240px"
          />
          <button class="btn-sm" @click="uploadAudio" :disabled="isUploading">
            {{ isUploading ? "..." : "guardar audio" }}
          </button>
          <audio
            v-if="currentAudio"
            :src="currentAudio"
            controls
            class="audio-player"
          />
        </div>
        <button class="btn-new-slide" @click="crearSlide">+ nuevo slide</button>
      </div>
    </header>

    <main class="main">
      <div v-if="slides.length === 0" class="empty">
        No hay slides. Creá uno.
      </div>

      <draggable
        v-model="slides"
        item-key="id"
        handle=".drag-handle"
        animation="150"
        @end="saveOrder"
      >
        <template #item="{ element: slide }">
          <div class="slide-card" :class="{ open: slide._open }">
            <!-- cabecera -->
            <div class="slide-head">
              <span class="drag-handle" title="Arrastrar para reordenar"
                >⠿</span
              >
              <div class="slide-thumb">
                <img
                  v-if="slide.background"
                  :src="`${UPLOADS_BASE}/${slide.background}`"
                />
                <span v-else class="no-bg">sin bg</span>
              </div>
              <div class="slide-info" @click="slide._open = !slide._open">
                <span class="slide-title"
                  >Slide #{{ slide.orden }} · acumulado:
                  {{ alturaAcumuladaPorOrden[slide.orden] }}</span
                >
                <span class="slide-sub"
                  >{{ slide.elementos.length }} elemento(s) ·
                  {{ slide.height_vh }}vh</span
                >
              </div>
              <div class="slide-head-actions">
                <label class="check-label">
                  <input
                    type="checkbox"
                    v-model="slide.active"
                    @change="updateSlide(slide)"
                  />
                  activo
                </label>
                <button class="btn-icon" @click="slide._open = !slide._open">
                  {{ slide._open ? "▲" : "▼" }}
                </button>
                <button class="btn-icon danger" @click="deleteSlide(slide.id)">
                  <i class="fa-solid fa-delete-left"></i>
                </button>
              </div>
            </div>

            <!-- cuerpo -->
            <div v-if="slide._open" class="slide-body">
              <!-- campos slide -->
              <div class="slide-fields">
                <div class="field-group">
                  <label>Background</label>
                  <div class="bg-row">
                    <input
                      type="text"
                      v-model="slide.background"
                      placeholder="archivo..."
                      class="text-input sm"
                      @blur="updateSlide(slide)"
                    />
                    <input
                      :id="`bg-${slide.id}`"
                      type="file"
                      accept="image/*"
                      style="display: none"
                      @change="uploadBackground(slide, $event)"
                    />
                    <button
                      class="btn-sm"
                      @click="triggerInput(`bg-${slide.id}`)"
                    >
                      <i class="fa-solid fa-upload"></i>
                    </button>
                  </div>
                </div>
                <div class="field-group" style="max-width: 110px">
                  <label>Alto (vh)</label>
                  <input
                    type="number"
                    v-model.number="slide.height_vh"
                    class="text-input sm"
                    min="100"
                    step="50"
                    @blur="updateSlide(slide)"
                  />
                </div>
              </div>

              <!-- layout: canvas + lista elementos -->
              <div class="layout-editor">
                <!-- mini canvas -->
                <div class="canvas-wrap">
                  <div class="canvas-label">
                    Vista previa · arrastra los elementos
                  </div>
                  <div
                    class="canvas"
                    :style="{ height: canvasHeight(slide) + 'px' }"
                    :id="`canvas-${slide.id}`"
                    @dragover.prevent
                    @drop="onCanvasDrop(slide, $event)"
                  >
                    <img
                      v-if="slide.background"
                      :src="`${UPLOADS_BASE}/${slide.background}`"
                      class="canvas-bg"
                    />
                    <div v-else class="canvas-bg-empty">sin background</div>

                    <div
                      v-for="el in slide.elementos"
                      :key="el.id"
                      class="canvas-el"
                      :style="canvasElStyle(el)"
                      draggable="true"
                      :title="el.title || el.filename"
                      @dragstart="onElDragStart(el, slide, $event)"
                      @click="slide._selectedEl = el"
                      :class="{
                        selected:
                          slide._selectedEl && slide._selectedEl.id === el.id,
                      }"
                    >
                      <img
                        v-if="el.type === 'image' || el.type === 'gif'"
                        :src="`${UPLOADS_BASE}/${el.filename}`"
                        class="canvas-media"
                        @dragstart.prevent
                      />
                      <video
                        v-else-if="el.type === 'video'"
                        :src="`${UPLOADS_BASE}/${el.filename}`"
                        class="canvas-media"
                        muted
                        @dragstart.prevent
                      />
                      <span class="canvas-el-label">{{
                        el.title || el.type
                      }}</span>
                    </div>

                    <div
                      v-if="slide._nuevoEl && slide._nuevoEl.previewUrl"
                      class="canvas-el nuevo-preview"
                      :style="{ left: '25%', top: '25%', width: '35%' }"
                    >
                      <img
                        v-if="slide._nuevoEl.type !== 'video'"
                        :src="slide._nuevoEl.previewUrl"
                        class="canvas-media"
                      />
                      <video
                        v-else
                        :src="slide._nuevoEl.previewUrl"
                        class="canvas-media"
                        muted
                      />
                      <span class="canvas-el-label nuevo">nuevo</span>
                    </div>
                  </div>
                </div>

                <!-- panel de elementos -->
                <div class="elements-panel">
                  <div class="elementos-label">Elementos</div>

                  <div
                    v-for="el in slide.elementos"
                    :key="el.id"
                    class="elemento-row"
                    :class="{
                      selected:
                        slide._selectedEl && slide._selectedEl.id === el.id,
                    }"
                    @click="slide._selectedEl = el"
                  >
                    <div class="el-preview-thumb">
                      <img
                        v-if="el.type === 'image' || el.type === 'gif'"
                        :src="`${UPLOADS_BASE}/${el.filename}`"
                      />
                      <video
                        v-else
                        :src="`${UPLOADS_BASE}/${el.filename}`"
                        muted
                      />
                      <span class="el-type-badge">{{ el.type }}</span>
                      <input
                        :id="`el-file-${el.id}`"
                        type="file"
                        accept="image/*,video/*,.gif,.svg,image/svg+xml,.avif,.webp"
                        style="display: none"
                        @change="replaceElemento(el, $event)"
                      />
                    </div>

                    <div class="el-fields">
                      <div class="el-row">
                        <input
                          v-model="el.title"
                          type="text"
                          placeholder="Título..."
                          class="text-input sm"
                          style="flex: 1"
                          @blur="updateElemento(el)"
                        />
                        <input
                          v-model="el.url"
                          type="text"
                          placeholder="URL..."
                          class="text-input sm"
                          style="flex: 1"
                          @blur="updateElemento(el)"
                        />
                      </div>
                      <textarea
                        v-model="el.description"
                        placeholder="Descripción..."
                        rows="2"
                        class="text-input sm textarea"
                        @blur="updateElemento(el)"
                      ></textarea>
                      <div class="el-row coords">
                        <span class="coord-label">X%</span>
                        <input
                          v-model.number="el.pos_x"
                          type="number"
                          class="text-input sm mini"
                          min="0"
                          max="100"
                          @change="updateElemento(el)"
                        />
                        <span class="coord-label">Y%</span>
                        <input
                          v-model.number="el.pos_y"
                          type="number"
                          class="text-input sm mini"
                          min="0"
                          max="100"
                          @change="updateElemento(el)"
                        />
                        <span class="coord-label">W%</span>
                        <input
                          v-model.number="el.width"
                          type="number"
                          class="text-input sm mini"
                          min="1"
                          max="100"
                          @change="updateElemento(el)"
                        />
                        <span class="coord-label">R°</span>
                        <input
                          v-model.number="el.rotation"
                          type="number"
                          class="text-input sm mini"
                          min="-180"
                          max="180"
                          @change="updateElemento(el)"
                        />
                        <span class="coord-label">Z</span>
                        <input
                          v-model.number="el.z_index"
                          type="number"
                          class="text-input sm mini"
                          min="0"
                          @change="updateElemento(el)"
                        />
                      </div>
                      <div class="el-checks">
                        <label v-if="el.type === 'video'" class="check-label">
                          <input
                            type="checkbox"
                            v-model="el.sound_enabled"
                            @change="updateElemento(el)"
                          />
                          audio
                        </label>
                      </div>
                    </div>

                    <div class="el-actions">
                      <button
                        class="btn-icon danger"
                        @click.stop="deleteElemento(slide, el.id)"
                      >
                        <i class="fa-solid fa-delete-left"></i>
                      </button>
                    </div>
                  </div>

                  <!-- nuevo elemento -->
                  <div v-if="slide._nuevoEl" class="elemento-row nuevo">
                    <div
                      class="el-preview-thumb clickable"
                      @click="triggerInput(`new-file-${slide.id}`)"
                    >
                      <img
                        v-if="
                          slide._nuevoEl.previewUrl &&
                          slide._nuevoEl.type !== 'video'
                        "
                        :src="slide._nuevoEl.previewUrl"
                      />
                      <video
                        v-else-if="slide._nuevoEl.previewUrl"
                        :src="slide._nuevoEl.previewUrl"
                        muted
                        loop
                        autoplay
                        playsinline
                      />
                      <span v-else style="font-size: 20px; color: #333">+</span>
                      <span v-if="slide._nuevoEl.type" class="el-type-badge">{{
                        slide._nuevoEl.type
                      }}</span>
                      <input
                        :id="`new-file-${slide.id}`"
                        type="file"
                        accept="image/*,video/*,.gif,.svg,image/svg+xml,.avif,.webp"
                        style="display: none"
                        @change="previewNuevoElemento(slide, $event)"
                      />
                    </div>
                    <div class="el-fields">
                      <div class="el-row">
                        <input
                          v-model="slide._nuevoEl.title"
                          type="text"
                          placeholder="Título..."
                          class="text-input sm"
                          style="flex: 1"
                        />
                        <input
                          v-model="slide._nuevoEl.url"
                          type="text"
                          placeholder="URL..."
                          class="text-input sm"
                          style="flex: 1"
                        />
                      </div>
                      <textarea
                        v-model="slide._nuevoEl.description"
                        placeholder="Descripción..."
                        rows="2"
                        class="text-input sm textarea"
                      ></textarea>
                      <div class="el-row coords">
                        <span class="coord-label">X%</span>
                        <input
                          v-model.number="slide._nuevoEl.pos_x"
                          type="number"
                          class="text-input sm mini"
                        />
                        <span class="coord-label">Y%</span>
                        <input
                          v-model.number="slide._nuevoEl.pos_y"
                          type="number"
                          class="text-input sm mini"
                        />
                        <span class="coord-label">W%</span>
                        <input
                          v-model.number="slide._nuevoEl.width"
                          type="number"
                          class="text-input sm mini"
                        />
                        <span class="coord-label">R°</span>
                        <input
                          v-model.number="slide._nuevoEl.rotation"
                          type="number"
                          class="text-input sm mini"
                        />
                        <span class="coord-label">Z</span>
                        <input
                          v-model.number="slide._nuevoEl.z_index"
                          type="number"
                          class="text-input sm mini"
                        />
                      </div>
                      <div class="el-checks">
                        <label
                          v-if="slide._nuevoEl.type === 'video'"
                          class="check-label"
                        >
                          <input
                            type="checkbox"
                            v-model="slide._nuevoEl.sound_enabled"
                          />
                          audio
                        </label>
                      </div>
                    </div>
                    <div class="el-actions">
                      <button
                        class="btn-icon danger"
                        @click="slide._nuevoEl = null"
                      >
                        <i class="fa-solid fa-delete-left"></i>
                      </button>
                    </div>
                  </div>

                  <button
                    class="btn-add-el"
                    @click="iniciarNuevoElemento(slide)"
                  >
                    <i class="fa-solid fa-plus"></i> elemento
                  </button>
                </div>
              </div>
            </div>
          </div>
        </template>
      </draggable>
    </main>
  </div>

  <!-- toast -->

  <div class="toast-container">
    <div v-for="t in toasts" :key="t.id" class="toast" :class="t.type">
      {{ t.message }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import draggable from "vuedraggable";

const API_BASE = import.meta.env.VITE_API_BASE;
const UPLOADS_BASE = import.meta.env.VITE_UPLOADS_BASE;

const slides = ref([]);
const audioLink = ref("");
const isUploading = ref(false);

const CANVAS_W = 320;

const triggerInput = (id) => document.getElementById(id)?.click();

const canvasHeight = (slide) =>
  Math.round(CANVAS_W * (slide.height_vh / 100) * 0.5625);

const canvasElStyle = (el) => ({
  left: `${el.pos_x}%`,
  top: `${el.pos_y}%`,
  width: `${el.width}%`,
  transform: `rotate(${el.rotation}deg)`,
  zIndex: el.z_index,
});

let _draggingEl = null;
let _draggingSlide = null;
let _dragOffsetX = 0;
let _dragOffsetY = 0;

const onElDragStart = (el, slide, event) => {
  _draggingEl = el;
  _draggingSlide = slide;
  const rect = event.target.getBoundingClientRect();
  _dragOffsetX = event.clientX - rect.left;
  _dragOffsetY = event.clientY - rect.top;
  event.dataTransfer.effectAllowed = "move";
};

const onCanvasDrop = (slide, event) => {
  if (!_draggingEl || _draggingSlide.id !== slide.id) return;
  const canvas = document.getElementById(`canvas-${slide.id}`);
  const rect = canvas.getBoundingClientRect();
  const x = ((event.clientX - rect.left - _dragOffsetX) / rect.width) * 100;
  const y = ((event.clientY - rect.top - _dragOffsetY) / rect.height) * 100;
  _draggingEl.pos_x = Math.max(0, Math.min(95, Math.round(x)));
  _draggingEl.pos_y = Math.max(0, Math.min(95, Math.round(y)));
  updateElemento(_draggingEl);
  _draggingEl = null;
};

const currentAudio = ref("");

onMounted(() => {
  cargarSlides();
  cargarAudio();
});

const cargarAudio = async () => {
  try {
    const res = await fetch(`${API_BASE}/get_audio.php`);
    const data = await res.json();
    if (data.success) currentAudio.value = data.audio_url;
  } catch (e) {
    console.error(e);
  }
};

const scroller = ref([]);
// let lasAlturas = ref(0);
// let elTotal = (0);

async function cargarSlides() {
  try {
    const res = await fetch(`${API_BASE}/media.php`);
    const data = await res.json();

    scroller.value = data.slides.map(item =>({
      orden: item.orden,
      altura: item.height_vh
    })

    );


    if (data.success) {
      slides.value = data.slides.map((s) => ({
        ...s,
        active: s.active ?? true,
        _open: false,
        _nuevoEl: null,
        _selectedEl: null,
      }));
    }
  } catch (e) {
    alert("Error cargando: " + e.message);
  }
}

 const alturaTotal = computed(() => {
  let suma = 0;
  let numSlide = 0;
  
  slides.value.forEach(item => {
      suma += item.height_vh/100;
      numSlide = item.orden;
      console.log("Al slide: " + numSlide + " van " + suma);
    });
   
  return suma
  
 }); 

 // Mapa orden -> altura acumulada hasta ese slide (misma lógica del log de arriba)
 // Usa slides.value (reactivo) en vez de scroller.value, así se recalcula
 // automáticamente al editar height_vh o al reordenar los slides.
 const alturaAcumuladaPorOrden = computed(() => {
  let suma = 0;
  const mapa = {};

  slides.value.forEach(item => {
      suma += item.height_vh/100;
      mapa[item.orden] = suma;
    });

  return mapa;
 });

//  const alturaTotal = computed(() => {
//   return scroller.value.reduce((sum, item) => sum + Number(item.valor)/100,0)
//  })
 
// document.getElementById("totalAltura").textContent = String(alturaTotal);



const crearSlide = async () => {
  try {
    const res = await fetch(`${API_BASE}/create_slide.php`, { method: "POST" });
    const data = await res.json();
    if (data.success)
      slides.value.push({
        ...data.slide,
        _open: true,
        _nuevoEl: null,
        _selectedEl: null,
      });
    else alert("Error: " + data.error);
  } catch (e) {
    alert("Error: " + e.message);
  }
};

const updateSlide = async (slide) => {
  // para que el usuario sepa que algo está pasando, ya que a veces tarda un poco por la generación de thumbnails
  try {
    await fetch(`${API_BASE}/update_slide.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        id: slide.id,
        background: slide.background,
        height_vh: slide.height_vh,
        active: slide.active ? 1 : 0,
        orden: slide.orden,
      }),
    });
    toast("Slide guardado");
  } catch (e) {
    alert("Error: " + e.message);
  }
};

const deleteSlide = async (id) => {
  if (!confirm("¿Eliminar este slide y todos sus elementos?")) return;
  try {
    const res = await fetch(`${API_BASE}/delete_slide.php?id=${id}`, {
      method: "DELETE",
    });
    const data = await res.json();
    if (data.success) slides.value = slides.value.filter((s) => s.id !== id);
    else alert("Error: " + data.error);
  } catch (e) {
    alert("Error: " + e.message);
  }
};

const saveOrder = async () => {
  // Actualiza el campo orden de cada slide según su nueva posición en el
  // array (ya reordenado por v-model gracias al drag). Esto es lo que hace
  // reactivos al instante el número "Slide #X" y los acumulados de altura,
  // sin esperar respuesta del backend ni recargar la página.
  slides.value.forEach((slide, index) => {
    slide.orden = index + 1;
  });

  const orden = slides.value.map((s) => s.id);
  try {
    await fetch(`${API_BASE}/save_order.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ orden }),
    });
  } catch (e) {
    console.error(e);
  }
};

const uploadBackground = async (slide, event) => {
  const file = event.target.files[0];
  if (!file) return;
  const fd = new FormData();
  fd.append("file", file);
  fd.append("slide_id", slide.id);
  try {
    const res = await fetch(`${API_BASE}/upload_background.php`, {
      method: "POST",
      body: fd,
    });
    const data = await res.json();
    if (data.success) {
      slide.background = data.filename;
      await updateSlide(slide);
    } else alert("Error: " + data.error);
  } catch (e) {
    alert("Error: " + e.message);
  }
  event.target.value = "";
};

const updateElemento = async (el) => {
  try {
    // para que el usuario sepa que algo está pasando, ya que a veces tarda un poco
    const res = await fetch(`${API_BASE}/update_elemento.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(el),
    });
    const data = await res.json();
    if (!data.success) alert("Error: " + data.error);
  } catch (e) {
    alert("Error: " + e.message);
  }
};

const deleteElemento = async (slide, id) => {
  if (!confirm("¿Eliminar este elemento?")) return;
  try {
    const res = await fetch(`${API_BASE}/delete_elemento.php?id=${id}`, {
      method: "DELETE",
    });
    const data = await res.json();
    if (data.success)
      slide.elementos = slide.elementos.filter((e) => e.id !== id);
    else alert("Error: " + data.error);
  } catch (e) {
    alert("Error: " + e.message);
  }
};

const replaceElemento = async (el, event) => {
  const file = event.target.files[0];
  if (!file) return;
  const fd = new FormData();
  fd.append("file", file);
  fd.append("id", el.id);
  try {
    const res = await fetch(`${API_BASE}/replace_elemento.php`, {
      method: "POST",
      body: fd,
    });
    const data = await res.json();
    if (data.success) {
      el.filename = data.filename;
      el.type = data.type;
    } else alert("Error: " + data.error);
  } catch (e) {
    alert("Error: " + e.message);
  }
  event.target.value = "";
};

const iniciarNuevoElemento = (slide) => {
  slide._nuevoEl = {
    file: null,
    previewUrl: null,
    type: null,
    title: "",
    url: "",
    description: "",
    pos_x: 25,
    pos_y: 25,
    width: 40,
    rotation: 0,
    z_index: 0,
    sound_enabled: false,
  };
};

const previewNuevoElemento = async (slide, event) => {
  const file = event.target.files[0];
  if (!file) return;
  slide._nuevoEl.file = file;
  slide._nuevoEl.previewUrl = URL.createObjectURL(file);
  if (file.type.startsWith("video")) slide._nuevoEl.type = "video";
  else if (file.name.endsWith(".gif") || file.type === "image/gif")
    slide._nuevoEl.type = "gif";
  else slide._nuevoEl.type = "image";
  await guardarNuevoElemento(slide);
};

const guardarNuevoElemento = async (slide) => {
  const el = slide._nuevoEl;
  if (!el.file) {
    alert("Seleccioná un archivo primero");
    return;
  }
  toast("Subiendo...", "info");
  const fd = new FormData();
  fd.append("file", el.file);
  fd.append("slide_id", slide.id);
  fd.append("title", el.title);
  fd.append("url", el.url);
  fd.append("description", el.description);
  fd.append("pos_x", el.pos_x);
  fd.append("pos_y", el.pos_y);
  fd.append("width", el.width);
  fd.append("rotation", el.rotation);
  fd.append("z_index", el.z_index);
  fd.append("sound_enabled", el.sound_enabled ? 1 : 0);
  try {
    const res = await fetch(`${API_BASE}/create_elemento.php`, {
      method: "POST",
      body: fd,
    });
    const data = await res.json();
    if (data.success) {
      const nuevo = {
        ...data.elemento,
        title: el.title,
        url: el.url,
        description: el.description,
      };
      slide.elementos.push(nuevo);
      await updateElemento(nuevo);
      slide._nuevoEl = null;
    } else alert("Error: " + data.error);
  } catch (e) {
    alert("Error: " + e.message);
  }
};

const uploadAudio = async () => {
  if (!audioLink.value.trim()) {
    alert("Ingresá un enlace");
    return;
  }
  isUploading.value = true;
  try {
    const fd = new FormData();
    fd.append("audio_url", audioLink.value.trim());
    const res = await fetch(`${API_BASE}/upload_audio.php`, {
      method: "POST",
      body: fd,
    });
    const data = await res.json();
    if (data.success) {
      currentAudio.value = audioLink.value.trim();
      toast("Audio guardado");
      audioLink.value = "";
    } else alert("Error: " + data.message);
  } catch (e) {
    alert("Error: " + e.message);
  } finally {
    isUploading.value = false;
  }
};

// TOAST

const toasts = ref([]);
const toast = (message, type = "success") => {
  const id = Date.now();
  toasts.value.push({ id, message, type });
  setTimeout(
    () => (toasts.value = toasts.value.filter((t) => t.id !== id)),
    2500,
  );
};
</script>

<style scoped>
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}
#app {
  min-height: 100vh;
  background: #0d0d0d;
  color: #e0e0e0;
  font-family: "Courier New", monospace;
  font-size: 13px;
}
.app-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 20px;
  border-bottom: 1px solid #1e1e1e;
  background: #111;
  position: sticky;
  top: 0;
  z-index: 100;
  gap: 16px;
  flex-wrap: wrap;
}
.header-inner {
  display: flex;
  align-items: baseline;
  gap: 8px;
}
.header-logo {
  font-size: 14px;
  font-weight: bold;
  color: #00ffcc;
  letter-spacing: 2px;
}
.header-sub {
  font-size: 10px;
  color: #444;
}
.header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.upload-group {
  display: flex;
  align-items: center;
  gap: 6px;
}
.btn-new-slide {
  background: #00ffcc;
  color: #000;
  border: none;
  padding: 6px 14px;
  font-family: inherit;
  font-size: 12px;
  font-weight: bold;
  cursor: pointer;
  border-radius: 3px;
  white-space: nowrap;
}
.btn-new-slide:hover {
  background: #00e6b8;
}
.main {
  padding: 14px 20px;
}
.empty {
  color: #333;
  text-align: center;
  padding: 60px;
}
.text-input {
  background: #1a1a1a;
  border: 1px solid #2a2a2a;
  color: #e0e0e0;
  padding: 5px 8px;
  font-family: inherit;
  font-size: 12px;
  border-radius: 3px;
  outline: none;
  width: 100%;
}
.text-input:focus {
  border-color: #00ffcc33;
}
.text-input.sm {
  font-size: 16px;
  padding: 4px 8px;
}
.text-input.mini {
  width: 56px;
  text-align: center;
}
.text-input.textarea {
  resize: vertical;
}
.btn-sm {
  background: #1a1a1a;
  border: 1px solid #2a2a2a;
  color: #888;
  padding: 4px 10px;
  font-family: inherit;
  font-size: 11px;
  cursor: pointer;
  border-radius: 3px;
  white-space: nowrap;
}
.btn-sm:hover {
  border-color: #00ffcc33;
  color: #00ffcc;
}
.btn-save {
  background: #00ffcc0a;
  border: 1px solid #00ffcc22;
  color: #00ffcc;
  padding: 4px 10px;
  font-family: inherit;
  font-size: 11px;
  cursor: pointer;
  border-radius: 3px;
  white-space: nowrap;
}
.btn-save:hover {
  background: #00ffcc18;
}
.btn-save.sm {
  padding: 3px 8px;
}
.btn-icon {
  background: none;
  border: 1px solid #1e1e1e;
  color: #444;
  width: 22px;
  height: 22px;
  font-size: 10px;
  cursor: pointer;
  border-radius: 3px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.btn-icon:hover {
  border-color: #333;
  color: #aaa;
}
.btn-icon.danger:hover {
  border-color: #ff4444;
  color: #ff4444;
}
.slide-card {
  border: 1px solid #1a1a1a;
  border-radius: 6px;
  overflow: hidden;
  background: #111;
  margin-bottom: 6px;
  transition: border-color 0.2s;
}
.slide-card.open {
  border-color: #00ffcc18;
}
.slide-head {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 7px 12px;
  user-select: none;
}
.slide-head:hover {
  background: #131313;
}
.drag-handle {
  color: #2a2a2a;
  font-size: 15px;
  cursor: grab;
  flex-shrink: 0;
  padding: 2px;
}
.drag-handle:hover {
  color: #00ffcc;
}
.slide-thumb {
  width: 54px;
  height: 34px;
  border-radius: 3px;
  background: #1a1a1a;
  border: 1px solid #1e1e1e;
  overflow: hidden;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}
.slide-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.no-bg {
  font-size: 9px;
  color: #2a2a2a;
}
.slide-info {
  flex: 1;
  cursor: pointer;
}
.slide-title {
  font-size: 12px;
  color: #bbb;
  display: block;
}
.slide-sub {
  font-size: 10px;
  color: #383838;
}
.slide-head-actions {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-shrink: 0;
}
.check-label {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 16px;
  color: #76b3a7c0;
  cursor: pointer;
}
.check-label input {
  accent-color: #00ffcc;
}
.slide-body {
  border-top: 1px solid #1a1a1a;
  padding: 12px;
}
.slide-fields {
  display: flex;
  gap: 10px;
  align-items: flex-end;
  margin-bottom: 14px;
  flex-wrap: wrap;
}
.field-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex: 1;
  min-width: 140px;
}
.field-group label {
  font-size: 10px;
  color: #383838;
}
.bg-row {
  display: flex;
  gap: 6px;
}
.layout-editor {
  display: flex;
  gap: 14px;
  align-items: flex-start;
}
.canvas-wrap {
  flex-shrink: 0;
  width: 320px;
}
.canvas-label {
  font-size: 12px;
  color: #a1f0e5b0;
  margin-bottom: 5px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.canvas {
  width: 320px;
  position: relative;
  background: #0a0a0a;
  border: 1px solid #1e1e1e;
  border-radius: 4px;
  overflow: hidden;
  min-height: 180px;
}
.canvas-bg {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0.4;
  pointer-events: none;
}
.canvas-bg-empty {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  color: #1e1e1e;
}
.canvas-el {
  position: absolute;
  cursor: grab;
  border: 1px solid transparent;
  border-radius: 2px;
  transition: border-color 0.15s;
}
.canvas-el:hover {
  border-color: #00ffcc55;
}
.canvas-el.selected {
  border-color: #00ffcc;
}
.canvas-el.nuevo-preview {
  border: 1px dashed #00ffcc55;
  cursor: default;
  opacity: 0.7;
}
.canvas-media {
  width: 100%;
  height: auto;
  display: block;
  object-fit: contain;
  pointer-events: none;
}
.canvas-el-label {
  position: absolute;
  bottom: 100%;
  left: 0;
  font-size: 8px;
  color: #00ffcc;
  background: #000000cc;
  padding: 1px 4px;
  border-radius: 2px;
  white-space: nowrap;
  opacity: 0;
  transition: opacity 0.15s;
  pointer-events: none;
}
.canvas-el:hover .canvas-el-label,
.canvas-el.selected .canvas-el-label {
  opacity: 1;
}
.canvas-el-label.nuevo {
  opacity: 1;
  color: #ffaa00;
}
.elements-panel {
  flex: 1;
  min-width: 0;
  max-height: 520px;
  overflow-y: auto;
}
.elementos-label {
  font-size: 16px;
  color: #8feeceb0;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  margin-bottom: 8px;
}
.elemento-row {
  display: flex;
  gap: 8px;
  align-items: flex-start;
  padding: 7px;
  background: #0d0d0d;
  border: 1px solid #1a1a1a;
  border-radius: 4px;
  margin-bottom: 5px;
  cursor: pointer;
  transition: border-color 0.15s;
}
.elemento-row:hover {
  border-color: #00ffcc11;
}
.elemento-row.selected {
  border-color: #00ffcc33;
}
.elemento-row.nuevo {
  border-color: #00ffcc18;
  cursor: default;
}
.el-preview-thumb {
  width: 128px;
  height: 100px;
  flex-shrink: 0;
  border-radius: 3px;
  background: #b4acac73;
  border: 1px solid #1e1e1e;
  overflow: hidden;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}
.el-preview-thumb.clickable {
  cursor: pointer;
  border-style: dashed;
}
.el-preview-thumb.clickable:hover {
  border-color: #00ffcc44;
}
.el-preview-thumb img,
.el-preview-thumb video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.el-type-badge {
  position: absolute;
  bottom: 2px;
  right: 2px;
  font-size: 8px;
  background: #000000bb;
  color: #00ffcc;
  padding: 1px 3px;
  border-radius: 2px;
}
.btn-replace {
  position: absolute;
  top: 2px;
  right: 2px;
  background: #000000bb;
  border: none;
  color: #888;
  font-size: 10px;
  cursor: pointer;
  padding: 1px 3px;
  border-radius: 2px;
  opacity: 0;
  transition: opacity 0.15s;
}
.el-preview-thumb:hover .btn-replace {
  opacity: 1;
}
.el-fields {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.el-row {
  display: flex;
  gap: 5px;
  align-items: center;
  flex-wrap: wrap;
}
.el-row.coords {
  gap: 4px;
}
.coord-label {
  font-size: 16px;
  color: #97ddcc79;
  white-space: nowrap;
}
.el-checks {
  display: flex;
  gap: 8px;
}
.el-actions {
  display: flex;
  flex-direction: column;
  gap: 3px;
  flex-shrink: 0;
}
.btn-add-el {
  width: 100%;
  background: none;
  border: 1px dashed #1e1e1e;
  color: #333;
  padding: 6px;
  font-family: inherit;
  font-size: 11px;
  cursor: pointer;
  border-radius: 3px;
  margin-top: 4px;
  transition: all 0.2s;
}
.btn-add-el:hover {
  border-color: #00ffcc22;
  color: #00ffcc;
}

.btn-save {
  padding: 8px 16px;
  font-size: 16px;
}

.btn-icon {
  width: 32px;
  height: 32px;
  font-size: 16px;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 16px;
}

.btn-add-el {
  padding: 10px;
  font-size: 16px;
}

@media (max-width: 800px) {
  .layout-editor {
    flex-direction: column;
  }
  .canvas-wrap,
  .canvas {
    width: 100%;
  }
}

.audio-player {
  height: 32px;
  filter: invert(1);
  border-radius: 3px;
}

/* TOAST */

.toast-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.toast {
  background: #222;
  color: #fff;
  padding: 10px 18px;
  border-radius: 6px;
  font-size: 13px;
  opacity: 0.95;
}
.toast.info {
  background: #1a5276;
}
</style>