import {ref, computed} from 'vue'
import {defineStore} from 'pinia'

export const userStore = defineStore({
    id: 'userStore',
    state: () => ({
        user: {
            data: {},
            token: sessionStorage.getItem('TOKEN'),
        },
    }),
    actions: {
        setUser(data) {
            this.user.data = { ...data.user };
            this.user.token = data.token
            sessionStorage.setItem('TOKEN', data.token)
        },
        logout() {
            this.user.data = {};
            this.user.token = null;
            sessionStorage.removeItem('TOKEN');
        },
    }
})
