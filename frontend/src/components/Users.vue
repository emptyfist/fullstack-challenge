<template>
  <div class="users">
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Location</th>
          <th>Weather</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(user) in users" :key="user.id" @click="showModal(user)">
          <td>{{ user.name }}</td>
          <td>{{ user.email }}</td>
          <td>{{ user.latitude }}, {{ user.longitude}}</td>
          <td></td>
        </tr>
      </tbody>
    </table>
    <div v-if="showUserModal">
      <div class="modal-background" @click="closeModal"></div>
      <div class="modal">
        <h2>{{ selectedUser.name }}'s Weather Report</h2>
        <template v-if="isValid">
          <p>Last Updated: {{ selectedUser.updated }}</p>
          <p>Temperature: {{ selectedUser.temp }}</p>
          <p>Wind Speed: {{ selectedUser.wind }}</p>
          <p>Current Weather: {{ selectedUser.forecast }}</p>
          <p>Detailed: {{ selectedUser.detailed }}</p>
        </template>
        <template v-else>
          <p>Location is not correct</p>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      apiKey: '8e90602fa932c0b2dab3331e65b8afe1',
      users: [],
      isValid: false,
      showUserModal: false,
      weatherDetails: null,
      selectedUser: null
    }
  },
  mounted() {
    this.getUsers()
  },
  methods: {
    async getUsers() {
      const response = await (await fetch('http://localhost')).json();
      if (!response?.users?.length) return;

      this.users = response.users;
      // this.users = 
    },
    async showModal(user) {
      const currentTime = new Date();
      const lastUpdatedTime = new Date(user.lastUpdated);
      const timeDiff = (currentTime - lastUpdatedTime) / (1000 * 60 * 60); // difference in hours

      let response = await (await fetch(`https://api.openweathermap.org/data/2.5/weather?lat=${user.latitude}&lon=${user.longitude}&appid=${this.apiKey}`)).json();
      if (+response.status === 404) {
        this.isValid = false;
      } else {
        user.temp = response.main.temp;
        user.humidity = response.main.humidity;
        user.wind = response.wind.speed;
        user.forecast = response.weather[0].main;
        user.detailed = response.weather[0].description;
        this.isValid = true;
      }

      this.showUserModal = true;
      this.selectedUser = user;
    },
    closeModal() {
      this.showUserModal = false;
      this.selectedUser = null;
    }
  }
};
</script>