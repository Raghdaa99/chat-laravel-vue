<script setup>
import axiosClient from "@/axios";
import {userStore} from "@/stores";
import router from "@/router";

const useStore = userStore();

const user = {
  name: '',
  email: '',
  password: '',
}

function register() {
  axiosClient.post('/register', user).then((res) => {
    useStore.setUser(res.data.data);
    toastr.success('User Created successfully.');
    router.push({
      name: 'Home'
    })
  }).catch(function (err) {

    console.log(err.response.data.message);
    toastr.error(err.response.data.message);
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
              <h3 class="fw-bold mb-2">Sign Up</h3>
              <p>Follow the easy steps</p>
            </div>
          </div>
          <form @submit.prevent="register" action="#" method="post">
            <div class="col-12">
              <div class="form-floating">
                <input type="text" class="form-control"  placeholder="Name" id="name" name="name" v-model="user.name">
                <label for="signup-name">Name</label>
              </div>
            </div>

            <div class="col-12">
              <div class="form-floating">
                <input type="email" class="form-control"  placeholder="Email" id="email" name="email" v-model="user.email">
                <label for="signup-email">Email</label>
              </div>
            </div>

            <div class="col-12">
              <div class="form-floating">
                <input type="password" class="form-control" placeholder="Password" id="password" name="password" v-model="user.password">
                <label for="signup-password">Password</label>
              </div>
            </div>
<!--            <div class="col-12">-->
<!--              <div class="form-floating">-->
<!--                <input type="password" class="form-control" placeholder="Password Confirmation" id="password_confirmation"-->
<!--                       name="password_confirmation" v-model="user.password_confirmation">-->
<!--                <label for="password_confirmation">Password Confirmation</label>-->
<!--              </div>-->
<!--            </div>-->
            <div class="col-12">
              <button class="btn btn-block btn-lg btn-primary w-100" type="submit">Create Account</button>
            </div>
          </form>

        </div>
      </div>
    </div>

    <!-- Text -->
    <div class="text-center mt-8">
      <p>Already have an account?
        <router-link :to="{ name: 'Login' }">Sign in</router-link>
      </p>
    </div>

  </div>

</template>


<style scoped>

</style>