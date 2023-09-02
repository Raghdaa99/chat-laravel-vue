<script setup>

import ChatHeader from '@/components/Chat/ChatHeader.vue';
import ChatContent from '@/components/Chat/ChatContent.vue';
import ChatFooter from '@/components/Chat/ChatFooter.vue';
// import {echo} from '@/echo';

import {initializeEcho} from '@/services/echo';

import {useRoute} from 'vue-router';
import {onMounted, ref, defineProps, watchEffect, provide, onUpdated, watch, inject} from "vue";
import axiosClient from "@/axios";
import Blank from "@/components/Blank.vue";
import {userStore} from '@/stores';
import alertSound from '@/assets/mixkit-correct-answer-tone-2870.wav';

const echo = initializeEcho(userStore().user.token);
let conversationChat = ref({})
let selectedUser = ref({});
const messages = ref([]);
const isTyping = ref(false);
let chatChannel;
const currentUser = userStore().user.data.id;
const emitter_open_conversation = inject('emitter');
let presenceChannelInitialized = {};
const presenceChannel = ref(null); // Declare presenceChannel as a reactive ref
const loading = ref(false);
const isOpenConversation = ref(false);


onUpdated(() => {
  initializeTypingMessageEcho();
});

onMounted(() => {
  emitter_open_conversation.on("open-conversation", async ([conversation, user]) => {
    selectedUser.value = user;
    // console.log("Received conversation and user:", conversation, user);
    isOpenConversation.value = true;
    if (conversation === null) {
      conversationChat.value = {};
      messages.value = [];
    } else {
      getMessages(conversation);
    }
  });

});
const getMessages = (conversation) => {
  loading.value = true;
  axiosClient.get(`conversation-messages/${conversation.id}`)
      .then((res) => {
        conversationChat.value = conversation;
        messages.value = res.data.messages;
        makeAsRead(conversation.id);
        loading.value = false;
      }).catch((error) => {
    console.error("Error fetching conversation messages:", error);
  })
}

function updatePresenceChannel() {
  console.log('updatePresenceChannel ' + conversationChat.value.id);

  const presenceChannel = echo.join(`chat.${conversationChat.value.id}`);

  presenceChannel.here((users) => {

    console.log(`Channel chat.${conversationChat.value.id} is connected`);
    console.log('Users in the channel:', users);
  });

  presenceChannel.listen('.new-message', (data) => {
    console.log('presenceChannel ', conversationChat.value.id);
    if (data.message.conversation_id === conversationChat.value.id && data.message.user_id !== currentUser) {
      messages.value.push(data.message);
      makeAsRead(data.message.conversation_id);
      playAlertSound();
    }
  });

  presenceChannel.listen('.delete-message', (data) => {
    console.log('delete-message - presenceChannel ', conversationChat.value.id);
    if (data.message.conversation_id === conversationChat.value.id && data.message.user_id !== currentUser) {
      const index = messages.value.findIndex(message => message.id === data.message.id);
      if (index !== -1) {
        messages.value.splice(index, 1); // Remove the item at the found index
      }
    }
  });

}


watchEffect(() => {
  if (Object.keys(conversationChat.value).length > 0) {
    updatePresenceChannel();
  }
});

const handleMessageSent = (body) => {
  // console.log(body)
  let bodyMessage = '';
  let formData = new FormData();
  formData.append('conversation_id', Object.keys(conversationChat.value).length !== 0 ? conversationChat.value.id : '');
  formData.append('user_id', selectedUser.value.id);

  if (body instanceof File) {
    formData.append('attachment', body);
  } else {
    bodyMessage = body
  }
  formData.append('body', bodyMessage);


  axiosClient.post('/messages', formData)
      .then((res) => {
        toastr.success('sent in successfully.');
        const newMessage = res.data.message;
        messages.value.push(newMessage);

        makeAsRead(newMessage.conversation_id);

      })
      .catch(function (error) {
        console.log(error.response.data.message);
        toastr.error(error.response.data.message);


      });
};

const startTyping = () => {
  chatChannel.whisper('typing', {
    user_id: userStore().user.data.id,
    typing: true,
  });
};

const stopTyping = () => {
  chatChannel.whisper('stop-typing', {
    user_id: userStore().user.data.id,
    typing: false,
  });
};

const playAlertSound = () => {
  const audio = new Audio(alertSound);
  audio.play();
};

const makeAsRead = (conversation_id) => {

  axiosClient.get(`conversations/${conversation_id}/read`)
      .then((res) => {
      })
}

const deleteMessage = (message_id) => {

  // console.log(action)
  axiosClient.delete(`messages/${message_id}`).then((res) => {
    toastr.success(res.data.message)
    const index = messages.value.findIndex(message => message.id === message_id);
    if (index !== -1) {
      messages.value.splice(index, 1); // Remove the item at the found index
    }
  }).catch((error) => {
    // toastr.error(error)
    console.log(error)
  })
}

function initializeTypingMessageEcho() {
  chatChannel = echo.join(`typing.${conversationChat.value ? conversationChat.value.id : null}`);

  chatChannel.here((users) => {
    // console.log('Users currently in the typing channel:', users);
  });
  chatChannel.listenForWhisper('typing', (event) => {
    isTyping.value = event.typing
  })
  chatChannel.listenForWhisper('stop-typing', (event) => {
    isTyping.value = event.typing
  })
}


</script>


<template>
  <div v-if="loading" class="d-flex flex-column h-100 position-relative justify-content-center">
    <transition name="fade">
      <div class="loading-overlay">
        <div class="loading-spinner"></div>
      </div>
    </transition>
  </div>

  <div v-else-if="isOpenConversation" class="d-flex flex-column h-100 position-relative">

    <!--    {{ currentUser }}-->
    <!-- Chat: Header -->
    <ChatHeader :conversation="conversationChat" :user="selectedUser" :isTyping="isTyping"></ChatHeader>
    <!-- Chat: Header -->

    <!-- Chat: Content -->
    <div v-if="!messages.length" class="chat-body-inner h-100" style="padding-bottom: 87px">
      <div class="d-flex flex-column h-100 justify-content-center">
        <div class="text-center mb-6">
                                            <span class="icon icon-xl text-muted">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                     stroke-width="2"
                                                     stroke-linecap="round" stroke-linejoin="round"
                                                     class="feather feather-send"><line x1="22" y1="2" x2="11"
                                                                                        y2="13"></line><polygon
                                                    points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                                            </span>
        </div>

        <p class="text-center text-muted">No messages yet, <br> start the conversation!</p>
      </div>
    </div>

    <ChatContent v-else :conversation="conversationChat.value" :user="selectedUser" :messages="messages"
                 @deleteMessage="deleteMessage"></ChatContent>
    <!-- Chat: Content -->

    <!-- Chat: Footer -->
    <ChatFooter @messageSent="handleMessageSent" @startTyping="startTyping" @stopTyping="stopTyping"></ChatFooter>
    <!-- Chat: Footer -->
  </div>

  <Blank v-else></Blank>


</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s;
}

.fade-enter, .fade-leave-to {
  opacity: 0;
}

.loading-overlay {
  position: absolute; /* Change to "absolute" to cover the entire parent */
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  /*background: rgba(255, 255, 255, 0.7);*/
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
}

.loading-spinner {
  border: 5px solid rgba(0, 0, 0, 0.1);
  border-top: 5px solid #796f6f;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
