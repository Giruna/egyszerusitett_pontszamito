<script setup lang="ts">
import axios from "axios"
import { ref } from "vue"

import {
    payload1,
    payload2,
    payload3,
    payload4,
    payload5
} from "./data/payloads"

const payloadMap: Record<number, any> = {
    1: payload1,
    2: payload2,
    3: payload3,
    4: payload4,
    5: payload5
}

const message = ref("")
const isSuccess = ref<boolean | null>(null)

const callApi = async (buttonNumber: number) => {
    try {

        const payload = payloadMap[buttonNumber]

        const response = await axios.post(
            `${import.meta.env.VITE_API_BASE_URL}/api/score-calculator`,
            payload
        )

        message.value = response.data.message
        isSuccess.value = response.data.ok

    } catch (error: any) {

        if (error.response) {
            message.value = error.response.data.message
            isSuccess.value = false
        } else {
            message.value = "Unexpected error"
            isSuccess.value = false
        }

    }
}
</script>

<template>
    <div class="container">
        <h1>Egyszerűsített Pontszámító Kalkulátor</h1>

        <div class="buttons">
            <button @click="callApi(1)">Példa 1</button>
            <button @click="callApi(2)">Példa 2</button>
            <button @click="callApi(3)">Példa 3</button>
            <button @click="callApi(4)">Példa 4</button>
            <button @click="callApi(5)">Példa 5</button>
        </div>

        <div
            v-if="message"
            :class="['result', isSuccess ? 'success' : 'error']"
        >
            {{ message }}
        </div>

    </div>
</template>

<style scoped>
.container {
    max-width: 600px;
    margin: 50px auto;
    text-align: center;
    font-family: Arial, Helvetica, sans-serif;
}

.buttons {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-top: 20px;
}

button {
    padding: 10px 16px;
    border: none;
    border-radius: 6px;
    background: #42b883;
    color: white;
    font-size: 14px;
    cursor: pointer;
}

button:hover {
    background: #369870;
}

.result {
    margin-top: 25px;
    font-size: 18px;
    font-weight: bold;
    padding: 12px;
    border-radius: 6px;
}

.success {
    background: #42b883;
    color: white;
}

.error {
    background: #c62828;
    color: white;
}

</style>
