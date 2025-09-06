<template>
  <div id="app" class="min-vh-100 bg-dark text-light">
    <!-- header -->
    <header class="bg-gradient-dark shadow-lg border-bottom border-cyan">
      <div class="container py-4">
        <h1 class="h2 mb-0 text-cyan fw-bold text-center">
          <i class="fas fa-database me-3"></i>
          TW2ISM FILE MANAGER
        </h1>
      </div>
    </header>

    <main class="py-5">
      <div class="container">
        <!-- upload Section -->
        <div class="row mb-5">
          <div class="col-lg-6 mb-4">
            <div class="card card-dark">
              <div class="card-header bg-gradient-primary">
                <h3 class="card-title mb-0">
                  <i class="fas fa-cloud-upload-alt me-2"></i>
                  Gestión de Archivos
                </h3>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <input 
                    ref="fileInput"
                    type="file" 
                    accept="image/*,video/*,.gif"
                    multiple
                    class="form-control form-control-dark"
                  >
                </div>
                <button 
                  @click="uploadFiles"
                  class="btn btn-cyber w-100"
                >
                  <i class="fas fa-upload me-2"></i>
                  Subir Archivos
                </button>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="card card-dark">
              <div class="card-header bg-gradient-purple">
                <h3 class="card-title mb-0">
                  <i class="fas fa-music me-2"></i>
                  Audio
                </h3>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <input 
                    type="text" 
                    v-model="audioLink"
                    placeholder="Enlace de audio..."
                    class="form-control form-control-dark"
                  >
                </div>
                <button 
                  @click="uploadAudio" 
                  :disabled="isUploading"
                  class="btn btn-neon w-100"
                >
                  <i class="fas fa-music me-2"></i>
                  {{ isUploading ? 'Subiendo...' : 'Subir Audio' }}
                </button>
                
                <audio 
                  :src="currentAudio" 
                  controls 
                  v-if="currentAudio"
                  class="w-100 mt-3 audio-player"
                ></audio>
              </div>
            </div>
          </div>
        </div>

        <!-- Cards Grid -->
        <div class="row g-3" v-if="mediaItems.length > 0">
          <div 
            v-for="item in mediaItems" 
            :key="item.id"
            class="col-12"
          >
            <div class="card card-cyber card-fixed-height">
              <div class="row g-0 h-100">
                <!-- seccion preview -->
                <div class="col-md-3">
                  <div class="preview-container h-100">
                    <div class="preview-wrapper h-100">
                      <img 
                        v-if="(item.type === 'image' || item.type === 'gif') && item.filename"
                        :src="getPreviewUrl(item.filename)" 
                        :alt="item.title"
                        class="card-img-left preview-img"
                      >
                      <video 
                        v-else-if="item.type === 'video' && item.filename"
                        :src="getPreviewUrl(item.filename)"
                        class="card-img-left preview-video"
                        controls
                      />
                      <div 
                        v-else
                        class="card-img-left no-image d-flex align-items-center justify-content-center"
                      >
                        <i class="fas fa-image fa-2x text-muted"></i>
                      </div>
                    </div>
                    
                    <!-- controls -->
                    <div class="image-overlay">
                      <input 
                        :ref="`fileInput-${item.id}`"
                        type="file" 
                        accept="image/*,video/*,.gif"
                        @change="replaceImage(item, $event)"
                        style="display: none"
                      >
                      <button 
                        @click="$refs[`fileInput-${item.id}`][0].click()"
                        class="btn btn-outline-cyan btn-sm"
                      >
                        <i class="fas fa-edit"></i>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- card  -->
                <div class="col-md-9">
                  <div class="card-body card-body-compact d-flex flex-column h-100">
                    <!-- components form  -->
                    <div class="fields-section mb-2">
                      <div class="row g-2 mb-2">
                        <div class="col-6">
                          <input 
                            v-model="item.title"
                            placeholder="Título..."
                            class="form-control form-control-dark form-control-sm"
                          >
                        </div>
                        <div class="col-6">
                          <input 
                            v-model="item.link"
                            placeholder="Link (opcional)..."
                            class="form-control form-control-dark form-control-sm"
                          >
                        </div>
                      </div>
                      
                      <textarea 
                        v-model="item.description"
                        placeholder="Descripción..."
                        rows="3"
                        class="form-control form-control-dark form-control-sm mb-2"
                      ></textarea>
                    </div>

                    <!-- checkboxes -->
                    <div class="checkboxes-section mb-2">
                      <div class="row g-2">
                        <div class="col-6">
                          <div class="form-check form-check-dark form-check-sm">
                            <input
                              type="checkbox"
                              v-model="item.sound_enabled"
                              :disabled="item.type !== 'video'"
                              class="form-check-input form-check-input-sm"
                              :id="`sound-${item.id}`"
                            />
                            <label class="form-check-label form-check-label-sm" :for="`sound-${item.id}`">
                              <i class="fas fa-volume-up me-1"></i>
                              Audio
                            </label>
                          </div>
                        </div>
                        <div class="col-6">
                          <div class="form-check form-check-dark form-check-sm">
                            <input 
                              v-model="item.active"
                              type="checkbox"
                              class="form-check-input form-check-input-sm"
                              :id="`active-${item.id}`"
                            >
                            <label class="form-check-label form-check-label-sm" :for="`active-${item.id}`">
                              <i class="fas fa-eye me-1"></i>
                              Activo
                            </label>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- botons -->
                    <div class="actions-section mt-auto">
                      <div class="row g-2">
                        <div class="col-6">
                          <button 
                            @click="updateItem(item)"
                            class="btn btn-success-cyber btn-sm w-100"
                          >
                            <i class="fas fa-save me-1"></i>
                            Actualizar
                          </button>
                        </div>
                        <div class="col-6">
                          <button 
                            @click="deleteItem(item.id)"
                            class="btn btn-danger-cyber btn-sm w-100"
                          >
                            <i class="fas fa-trash me-1"></i>
                            Eliminar
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- empty -->
        <div v-if="mediaItems.length === 0" class="empty-state text-center py-5">
          <div class="mb-4">
            <i class="fas fa-folder-open fa-5x text-muted mb-3"></i>
            <h3 class="text-light">No hay archivos subidos aún</h3>
            <p class="text-muted">Selecciona archivos para comenzar a gestionar tu contenido</p>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

// estado reactivo
const mediaItems = ref([])
const fileInput = ref(null)


const audioLink = ref('')
const isUploading = ref(false)


// funcion subir audio
const uploadAudio = async () => {
  if (!audioLink.value.trim()) {
    alert('Por favor ingresa un enlace válido')
    return
  }

  isUploading.value = true

  try {
    const formData = new FormData()
    formData.append('audio_url', audioLink.value.trim())

    const response = await fetch('http://localhost/tw2ism-admin/api/upload_audio.php', {
      method: 'POST',
      body: formData
    })

    const result = await response.json()

    if (result.success) {
      alert('Audio subido exitosamente')
      audioLink.value = '' 
    } else {
      alert('Error: ' + result.message)
    }
  } catch (error) {
    alert('Error de conexión')
    console.error(error)
  } finally {
    isUploading.value = false
  }
}




const currentAudio = ref('')



onMounted(async () => {
 try {
   const response = await fetch('http://localhost/tw2ism-admin/api/get_audio.php?id=1')
   const result = await response.json()
   
   if (result.success && result.audio_url) {
     currentAudio.value = result.audio_url;
     console.log('Audio cargado:', currentAudio.value);
   }
 } catch (error) {
   console.error('Error cargando audio:', error)
 }
})






// manejo de archivos
const uploadFiles = async () => {
  const files = fileInput.value?.files
  if (!files || files.length === 0) {
    alert('Selecciona archivos primero')
    return
  }
  
  for (const file of files) {
    const formData = new FormData()
    formData.append('file', file)
    
    try {
      const response = await fetch('http://localhost/tw2ism-admin/api/upload.php', {
        method: 'POST',
        body: formData
      })
      
      const result = await response.json()
      
      if (result.success) {
        mediaItems.value.push(result.item)
      } else {
        alert('Error: ' + result.error)
      }
    } catch (error) {
      alert('Error subiendo archivo: ' + error.message)
    }
  }
  
  fileInput.value.value = ''
}

// reemplazar imagen
const replaceImage = async (item, event) => {
  const file = event.target.files[0]
  if (!file) return
  
  const formData = new FormData()
  formData.append('file', file)
  formData.append('id', item.id)
  
  try {
    const response = await fetch('http://localhost/tw2ism-admin/api/replace.php', {
      method: 'POST',
      body: formData
    })
    
    const result = await response.json()
    
    if (result.success) {
      // Aactualizar valores del item
      item.filename = result.filename
      item.type = result.type
      // convertir a booleano
      item.sound_enabled = Boolean(result.sound_enabled)
    } else {
      alert('Error: ' + result.error)
    }
  } catch (error) {
    alert('Error reemplazando archivo: ' + error.message)
  }
  
  event.target.value = ''
}

// borrar solo imagen
// const deleteImageOnly = async (item) => {
//   if (confirm('¿Borrar solo la imagen? Los datos se mantienen.')) {
//     try {
//       const response = await fetch(`http://localhost/tw2ism-admin/api/delete-image.php?id=${item.id}`, {
//         method: 'POST'
//       })
      
//       const result = await response.json()
      
//       if (result.success) {
//         item.filename = ''
//       } else {
//         alert('Error: ' + result.error)
//       }
//     } catch (error) {
//       alert('Error borrando imagen: ' + error.message)
//     }
//   }
// }

// URL de preview 
const getPreviewUrl = (filename) => {
  return `http://localhost/tw2ism-admin/uploads/media_scroll/${filename}`
}

// actualizar item
const updateItem = async (item) => {
  try {
    const response = await fetch('http://localhost/tw2ism-admin/api/update.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(item)
    })
    
    const result = await response.json()
    
    if (!result.success) {
      alert('Error actualizando: ' + result.error)
    } else {
      alert('Actualizado correctamente')
    }
  } catch (error) {
    alert('Error actualizando: ' + error.message)
  }
}

// eliminar item
const deleteItem = async (id) => {
  if (confirm('¿Seguro que quieres eliminar este archivo?')) {
    try {
      const response = await fetch(`http://localhost/tw2ism-admin/api/delete.php?id=${id}`, {
        method: 'DELETE'
      })
      
      const result = await response.json()
      
      if (result.success) {
        mediaItems.value = mediaItems.value.filter(item => item.id !== id)
      } else {
        alert('Error eliminando: ' + result.error)
      }
    } catch (error) {
      alert('Error eliminando: ' + error.message)
    }
  }
}

// cargar items al montar
onMounted(async () => {
  try {
    const response = await fetch('http://localhost/tw2ism-admin/api/media.php')
    const result = await response.json()
    
    if (result.success) {
      mediaItems.value = result.items
      console.log('Items cargados:', mediaItems.value)
    } else {
      console.error('Error cargando items:', result.error)
    }
  } catch (error) {
    console.error('Error cargando items:', error)
  }
})







</script>


<style scoped>
:root {
  --cyber-cyan: #00ffff;
  --cyber-purple: #8b5cf6;
  --cyber-pink: #f472b6;
  --dark-bg: #111827;
  --darker-bg: #0f172a;
  --card-bg: #1f2937;
}

.bg-gradient-dark {
  background: linear-gradient(135deg, var(--darker-bg) 0%, var(--dark-bg) 100%);
}

.bg-gradient-primary {
  background: linear-gradient(135deg, var(--cyber-cyan), #0ea5e9);
}

.bg-gradient-purple {
  background: linear-gradient(135deg, var(--cyber-purple), #a855f7);
}

.card-dark {
  background: var(--card-bg);
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.card-dark:hover {
  border-color: var(--cyber-cyan);
  box-shadow: 0 10px 30px rgba(0, 255, 255, 0.1);
}

/* cards */

.card-cyber {
  background: linear-gradient(145deg, #1f2937, #111827);
  border: 1px solid rgba(51, 131, 131, 0.479);
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  padding-block: 8px;
}
.card-fixed-height {
  height: auto; 
  min-height: 230px; 
}
.preview-container, .preview-wrapper, .card-body-compact {
  height: auto !important;
}


.card-cyber:hover {
  transform: translateY(-4px);
  border-color: var(--cyber-cyan);
  box-shadow: 0 15px 30px rgba(0, 255, 255, 0.2);
}

.card-fixed-height {
  height: 180px;
}

.preview-container {
  position: relative;
  overflow: hidden;
  height: 100%;
}

.preview-wrapper {
  position: relative;
  height: 100%;
}

.card-img-left {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: all 0.3s ease;
  filter: brightness(0.9);
}

.card-cyber:hover .card-img-left {
  filter: brightness(1.1);
  transform: scale(1.05);
}

.no-image {
  height: 100%;
  background: linear-gradient(45deg, #374151, #1f2937);
}

.image-overlay {
  position: absolute;
  top: 8px;
  right: 8px;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.card-cyber:hover .image-overlay {
  opacity: 1;
}

.card-body-compact {
  padding: 1rem 0.75rem;
}

.fields-section {
  flex: 1;
}

.checkboxes-section {
  flex-shrink: 0;
}

.actions-section {
  flex-shrink: 0;
}

.form-control-dark {
  background: rgba(17, 24, 39, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #f9fafb;
  transition: all 0.3s ease;
}

.form-control-dark:focus {
  background: rgba(17, 24, 39, 1);
  border-color: var(--cyber-cyan);
  box-shadow: 0 0 0 0.2rem rgba(0, 255, 255, 0.25);
  color: #f9fafb;
}

.form-control-dark::placeholder {
  color: rgba(156, 163, 175, 0.7);
}

.form-control-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.form-check-dark .form-check-input {
  background-color: rgba(17, 24, 39, 0.8);
  border-color: rgba(255, 255, 255, 0.2);
}

.form-check-dark .form-check-input:checked {
  background-color: var(--cyber-cyan);
  border-color: var(--cyber-cyan);
  color: #000;
}

.form-check-dark .form-check-input:focus {
  box-shadow: 0 0 0 0.25rem rgba(0, 255, 255, 0.25);
}

.form-check-sm {
  margin-bottom: 0;
}

.form-check-input-sm {
  width: 1em;
  height: 1em;
}

.form-check-label-sm {
  font-size: 0.875rem;
  margin-bottom: 0;
  color: aqua;
}

.btn-cyber {
  background: linear-gradient(45deg, var(--cyber-cyan), #0ea5e9);
  border: none;
  color: #000;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
}

.btn-cyber:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 255, 255, 0.4);
  color: #000;
}

.btn-neon {
  background: linear-gradient(45deg, var(--cyber-purple), #a855f7);
  border: none;
  color: #fff;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
}

.btn-neon:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(139, 92, 246, 0.4);
  color: #fff;
}

.btn-success-cyber {
  background: linear-gradient(45deg, #10b981, #059669);
  border: none;
  color: #fff;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-success-cyber:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
  color: #fff;
}

.btn-danger-cyber {
  background: linear-gradient(45deg, #ef4444, #dc2626);
  border: none;
  color: #fff;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-danger-cyber:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(239, 68, 68, 0.4);
  color: #fff;
}

.btn-outline-cyan {
  border-color: var(--cyber-cyan);
  color: var(--cyber-cyan);
  background: rgba(0, 255, 255, 0.1);
  backdrop-filter: blur(10px);
}

.btn-outline-cyan:hover {
  background: var(--cyber-cyan);
  color: #000;
  border-color: var(--cyber-cyan);
}

.btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
}

.text-cyan {
  color: var(--cyber-cyan) !important;
}

.border-cyan {
  border-color: rgba(0, 255, 255, 0.3) !important;
}

.audio-player {
  filter: sepia(100%) saturate(200%) hue-rotate(180deg) brightness(0.8);
  border-radius: 8px;
}

.empty-state {
  padding: 4rem 2rem;
}

@media (max-width: 768px) {
  .card-fixed-height {
    height: 200px;
  }
  
  .card-body-compact {
    padding: 0.75rem 0.5rem;
  }
}
</style>