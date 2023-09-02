<script setup>
import {defineProps, ref, watchEffect, onMounted, onUnmounted} from "vue";
import axiosClient from "@/axios";
import router from "@/router";

const emit = defineEmits(['messageSent', 'startTyping', 'stopTyping']);
const body = ref('');
const isTyping = ref(false);
const isAttachmentMode = ref(false);
const fileInputRef = ref(null);

const selectedFile = ref(null);

// Method to toggle between text and attachment mode
const toggleAttachment = (ev) => {
  ev.preventDefault();
  isAttachmentMode.value = !isAttachmentMode.value;
  if (isAttachmentMode.value) {
    fileInputRef.value.click(); // Trigger the file input click event
  }
};

// Method to handle attachment selection
const handleAttachment = (event) => {
  selectedFile.value = event.target.files[0];
};

// Method to remove the selected attachment
const removeAttachment = () => {
  selectedFile.value = null;
};

// Attach the file input reference to the DOM element
onMounted(() => {
  fileInputRef.value = document.getElementById('dz-btn');
});

// Emit the typing event whenever the user starts typing
const startTyping = () => {
  isTyping.value = true;
  emit('startTyping', true); // Emit true when typing starts
};

// Emit the typing event whenever the user stops typing
const stopTyping = () => {
  isTyping.value = false;
  emit('stopTyping', false); // Emit false when typing stops
};

// Method to send the message
const sendMessage = () => {

  if (selectedFile.value) {
    emit("messageSent", selectedFile.value);
    selectedFile.value = null

  } else if (body.value) {
    emit("messageSent", body.value);
    body.value = '';
  } else {
    toastr.error('body Empty')
  }

};

// Method to get the URL for the selected image
const getImageUrl = (file) => {
  if (file) {
    return URL.createObjectURL(file);
  }
  return '';
};
</script>

<template>


  <div class="chat-footer pb-3 pb-lg-7 position-absolute bottom-0 start-0">
    <!-- Chat: Files -->
    <div class="dz-preview bg-dark" id="dz-preview-row" data-horizontal-scroll="">
    </div>
    <!-- Chat: Files -->
    <!-- Chat: Form -->
    <form class="chat-form rounded-pill bg-dark" data-emoji-form="" @submit.prevent="sendMessage"
          enctype="multipart/form-data">
      <div class="row align-items-center gx-0">
        <div class="col-auto">
          <button class="btn btn-icon btn-link text-body rounded-circle dz-clickable" @click="toggleAttachment">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 class="feather feather-paperclip">
              <path
                  d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
            </svg>
          </button>
          <input ref="fileInputRef" type="file" style="display: none" @change="handleAttachment">
        </div>

        <!-- Display the selected file -->
        <div v-if="selectedFile" class="selected-file">
          <button class="remove-file" @click="removeAttachment">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
          <img v-if="selectedFile && selectedFile.type.startsWith('image/')" :src="getImageUrl(selectedFile)"
               alt="Selected File" class="selected-image">
          <span v-else>{{ selectedFile.name }}</span>
        </div>
        <div class="col">
          <!-- Use textarea for text message and display body or file name -->
          <textarea class="form-control px-0" placeholder="Type your message..." rows="1"
                    v-model="body" :disabled="isAttachmentMode" @input="startTyping" @blur="stopTyping">
        </textarea>
        </div>
        <div class="col-auto">
          <button class="btn btn-icon btn-primary rounded-circle ms-5">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 class="feather feather-send">
              <line x1="22" y1="2" x2="11" y2="13"></line>
              <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
          </button>
        </div>
      </div>
    </form>
    <!-- Chat: Form -->
  </div>
</template>

<style scoped>
.selected-image {
  max-width: 100px; /* Adjust the max width to your desired size */
  max-height: 100px; /* Adjust the max height to your desired size */
  display: block;
  margin-top: 10px;
}

.remove-file {
  background: none;
  border: none;
  cursor: pointer;
  color: red;
  margin-left: 10px;
}
</style>