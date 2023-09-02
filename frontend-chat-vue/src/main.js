import {createApp} from 'vue'
import {createPinia} from 'pinia'

import App from './App.vue'
import router from './router'
import toastr from 'toastr'
import 'toastr/build/toastr.min.css'
import {userStore} from "@/stores";
import mitt from "mitt";
const emitter = mitt();
// import {Echo} from './echo';

window.toastr = toastr;

const app = createApp(App)

// app.config.globalProperties.$echo = Echo;


const pinia = createPinia()

pinia.use(context => {
    const storeId = context.store.$id

    const serializer = {
        serialize: JSON.stringify,
        deserialize: JSON.parse
    }

    const data = serializer.deserialize(window.localStorage.getItem(storeId))

    if (data) {
        context.store.$patch(data)
    }

    context.store.$subscribe((m, s) => {
        window.localStorage.setItem(storeId, serializer.serialize(s))
    })

})



app.use(pinia)

app.use(router)

// app.provide('emit-new-messages',emitter)
app.provide('emitter',emitter)

app.mount('#app')

app.config.globalProperties.$token = userStore().user.token;

