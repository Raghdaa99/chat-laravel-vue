import {defineStore} from 'pinia';
import axiosClient from "@/axios";


export const useChatStore = defineStore({
    id: 'chatStore',
    state: () => ({
        conversations: [],
        selectedConversation: null,
        selectedUser: null,
        // messages: [],
        isTyping: false,
        isAdmin: false,
    }),
    actions: {

        setCurrentConversation(currentConversation) {
            this.selectedConversation = currentConversation
        },
        setCurrentUser(currentUser) {
            this.selectedUser = currentUser
        },


        async fetchConversations() {
            try {
                const response = await axiosClient.get('conversations');
                this.conversations = response.data.data;
            } catch (error) {
                console.error('Error fetching conversations:', error);
            }
        },

        async createConversation(conversationData) {
            try {
                const formData = new FormData();
                formData.append('name', conversationData.name);
                formData.append('desc', conversationData.desc);
                formData.append('imagUrl', conversationData.imagUrl);
                formData.append('userIds', JSON.stringify(conversationData.userIds));
                const response = await axiosClient.post('/create-group-chat', formData);
                this.conversations.push(response.data);
                return response.data;
            } catch (error) {
                console.error('Error creating chat:', error);
                throw error;
            }
        },

        async fetchMessages(conversationId) {
            try {
                const response = await axiosClient.get(`conversation-messages/${conversationId}`);
                this.conversations.messages = response.data.messages;
                // Mark the newly created message as read
                await this.makeAsRead(conversationId);
            } catch (error) {
                console.error('Error fetching messages:', error);
            }
        },

        async sendMessage(body, conversation = null) {
            try {
                const formData = new FormData();
                formData.append('conversation_id', conversation.id);
                formData.append('user_id', conversation.participants[0].id);
                if (body instanceof File) {
                    formData.append('attachment', body);
                } else {
                    formData.append('body', body);
                }

                const response = await axiosClient.post('/messages', formData);
                const newMessage = response.data.message;

                // Mark the newly created message as read
                await this.makeAsRead(response.data.message.id);

                this.conversations.messages.push(newMessage);
            } catch (error) {
                console.error('Error sending message:', error);
                throw error;
            }
        },

        async makeAsRead(conversation_id) {
            await axiosClient.get(`conversations/${conversation_id}/read`)
                .then((res) => {
                    console.log(res.data)
                }).catch((error) => {
                    console.error('Error fetching messages:', error);
                })
        },

        async deleteMessage(message_id) {
            try {
                const response = await axiosClient.delete(`messages/${message_id}`);
                toastr.success(response.data.message);

                // Find the index of the deleted message in the conversations.messages array
                const deletedMessageIndex = this.conversations.messages.findIndex(message => message.id === message_id);

                // If the deleted message was found, remove it from the array
                if (deletedMessageIndex !== -1) {
                    this.conversations.messages.splice(deletedMessageIndex, 1);
                }
            } catch (error) {
                console.error('Error deleting message:', error);
                // Handle the error as needed
            }
        },

        async checkIfAdmin(conversation) {

            if (conversation && conversation.type === 'group') {
                await axiosClient.get(`check-admin/${conversation.id}`)
                    .then((response) => {
                        this.isAdmin = response.data.is_admin;
                        console.log(response.data.is_admin)
                    })
                    .catch((error) => {
                        console.error('Error checking admin status:', error);

                    });
            }
        },
    },
})
