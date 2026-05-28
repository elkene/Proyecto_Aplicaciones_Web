const getApiBaseUrl = () => {
    const currentUrl = window.location.href;
    const protocol = window.location.protocol;
    const host = window.location.host;

    return `${protocol}//${host}/PawMatchV2/backend`;
};

const API_BASE_URL = getApiBaseUrl();

class PawMatchAPI {
    static getToken() {
        return localStorage.getItem('pawmatch_token');
    }

    static setToken(token) {
        localStorage.setItem('pawmatch_token', token);
    }

    static getUser() {
        return JSON.parse(localStorage.getItem('pawmatch_user') || '{}');
    }

    static setUser(user) {
        localStorage.setItem('pawmatch_user', JSON.stringify(user));
    }

    static clearAuth() {
        localStorage.removeItem('pawmatch_token');
        localStorage.removeItem('pawmatch_user');
    }

    static async request(endpoint, method = 'GET', data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json'
            }
        };

        const token = this.getToken();
        if (token) {
            options.headers['Authorization'] = `Bearer ${token}`;
        }

        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        }

        const url = `${API_BASE_URL}/${endpoint}`;

        try {
            const response = await fetch(url, options);
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.error || 'Request failed');
            }

            return result;
        } catch (error) {
            throw error;
        }
    }

    static async register(email, password, name) {
        const result = await this.request('users/register', 'POST', {
            email,
            password,
            name
        });
        this.setToken(result.token);
        this.setUser(result.user);
        return result;
    }

    static async login(email, password) {
        const result = await this.request('users/login', 'POST', {
            email,
            password
        });
        this.setToken(result.token);
        this.setUser(result.user);
        return result;
    }

    static logout() {
        this.clearAuth();
    }

    static async getProfile() {
        return await this.request('users/profile', 'GET');
    }

    static async updateProfile(data) {
        return await this.request('users/profile/update', 'POST', data);
    }

    static async changePassword(oldPassword, newPassword) {
        return await this.request('users/change-password', 'POST', {
            oldPassword,
            newPassword
        });
    }

    static async getPets(filters = {}) {
        let query = '?';
        if (filters.page) query += `page=${filters.page}&`;
        if (filters.limit) query += `limit=${filters.limit}&`;
        if (filters.species) query += `species=${filters.species}&`;
        if (filters.size) query += `size=${filters.size}&`;
        if (filters.energy) query += `energy=${filters.energy}&`;
        if (filters.search) query += `search=${filters.search}&`;

        query = query.slice(0, -1);
        return await this.request(`pets${query}`, 'GET');
    }

    static async getPetById(id) {
        return await this.request(`pets/${id}`, 'GET');
    }

    static async createPet(data) {
        return await this.request('pets', 'POST', data);
    }

    static async updatePet(id, data) {
        return await this.request(`pets/${id}`, 'PUT', data);
    }

    static async deletePet(id) {
        return await this.request(`pets/${id}`, 'DELETE');
    }

    static async createAdoption(petId, message = '') {
        return await this.request('adoptions', 'POST', {
            pet_id: petId,
            message
        });
    }

    static async getMyAdoptions() {
        return await this.request('adoptions', 'GET');
    }

    static async getAllAdoptions() {
        return await this.request('adoptions/all', 'GET');
    }

    static async updateAdoptionStatus(id, status) {
        return await this.request(`adoptions/${id}`, 'PUT', {
            status
        });
    }

    static async deleteAdoption(id) {
        return await this.request(`adoptions/${id}`, 'DELETE');
    }

    static async createDonation(amount, currency = 'MXN', message = '') {
        return await this.request('donations', 'POST', {
            amount,
            currency,
            message
        });
    }

    static async getMyDonations() {
        return await this.request('donations/user/my-donations', 'GET');
    }

    static async getDonationStats() {
        return await this.request('donations/stats', 'GET');
    }

    static async createReport(type, description, latitude, longitude, phone = '', image = '') {
        return await this.request('reported-animals', 'POST', {
            type,
            description,
            latitude,
            longitude,
            phone,
            image
        });
    }

    static async getReports(page = 1, limit = 50, filters = {}) {
        let query = `?page=${page}&limit=${limit}`;
        if (filters.status) query += `&status=${filters.status}`;
        return await this.request(`reported-animals${query}`, 'GET');
    }

    static async getReportById(id) {
        return await this.request(`reported-animals/${id}`, 'GET');
    }

    static async getNearbyReports(latitude, longitude, radius = 5) {
        return await this.request(`reported-animals/nearby?latitude=${latitude}&longitude=${longitude}&radius=${radius}`, 'GET');
    }

    static async updateReportStatus(id, status, notes = '') {
        return await this.request(`reported-animals/${id}`, 'PUT', {
            status,
            notes
        });
    }

    static async updateReportDetails(id, data) {
        return await this.request(`reported-animals/${id}/edit`, 'PUT', data);
    }

    static async updateReport(id, data) {
        return await this.request(`reported-animals/${id}`, 'PUT', data);
    }

    static async addReportUpdate(id, content, type = 'comment', newStatus = null) {
        return await this.request(`reported-animals/${id}/update`, 'POST', {
            content,
            type,
            new_status: newStatus
        });
    }

    static async deleteReport(id) {
        return await this.request(`reported-animals/${id}`, 'DELETE');
    }
}

const StorageHelper = {
    setCompatibilityTest(data) {
        localStorage.setItem('pawmatch_compat_test', JSON.stringify(data));
    },

    getCompatibilityTest() {
        return JSON.parse(localStorage.getItem('pawmatch_compat_test') || '{}');
    },

    clearCompatibilityTest() {
        localStorage.removeItem('pawmatch_compat_test');
    }
};
