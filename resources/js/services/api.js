import axios from 'axios'

const api = axios.create({
    baseURL: '/api',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
})

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            window.location.href = '/login'
        }

        return Promise.reject(error)
    },
)

export default api

export const gamesApi = {
    search(params) {
        return api.get('/games/search', { params })
    },

    create(payload) {
        return api.post('/games', payload)
    },
}

export const userGamesApi = {
    list(params = {}) {
        return api.get('/user-games', { params })
    },

    create(payload) {
        return api.post('/user-games', payload)
    },

    update(id, payload) {
        return api.patch(`/user-games/${id}`, payload)
    },

    delete(id) {
        return api.delete(`/user-games/${id}`)
    },
}

export const recommendationsApi = {
    now() {
        return api.get('/recommendations/now')
    },
}