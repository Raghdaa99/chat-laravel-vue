<script setup>
import axiosClient from "@/axios";
import {userStore} from "@/stores";
import router from "@/router";

const useStore = userStore();

const user = {
  email: '',
  password: '',
}

function login() {
  axiosClient.post('/login', user).then((res) => {
    useStore.setUser(res.data.data);
    // console.log(res.data.data)
    toastr.success('Logged in successfully.');
    router.push({
      name: 'Home'
    })
  }).catch(function (error) {
    console.log(error.response.data.message);
    toastr.error(error.response.data.message);
  });

}
</script>

<template>

  <div class="col-12 col-md-5 col-lg-4">
    <div class="card card-shadow border-0">
      <div class="card-body">
        <div class="row g-6">
          <div class="col-12">
            <div class="text-center">
              <h3 class="fw-bold mb-2">Sign In</h3>
              <p>Login to your account</p>
            </div>
          </div>
          <form @submit.prevent="login" action="#" method="post">
            <div class="col-12">
              <div class="form-floating">
                <input
                    type="email"
                    class="form-control"
                    placeholder="Email"
                    id="email" name="email" v-model="user.email"
                />
                <label for="signin-email">Email</label>
              </div>
            </div>

            <div class="col-12">
              <div class="form-floating">
                <input
                    type="password"
                    class="form-control"
                    placeholder="Password"
                    id="password" name="password" v-model="user.password"
                />
                <label for="signin-password">Password</label>
              </div>
            </div>

            <div class="col-12">
              <button
                  class="btn btn-block btn-lg btn-primary w-100"
                  type="submit"
              >
                Sign In
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Text -->
    <div class="text-center mt-8">
      <p>Don't have an account yet?
        <router-link :to="{ name: 'Register' }">Sign up</router-link>
      </p>
      <p>
        <router-link :to="{ name: 'ResetPassword' }">Forgot Password?</router-link>
      </p>
    </div>
  </div>

</template>


<style scoped></style>
