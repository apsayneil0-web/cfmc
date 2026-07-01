/**
 * Manager Modules Utility Functions
 * Handles CRUD operations for all cooperative modules
 */

const ModuleManager = {
    // Base API endpoints
    endpoints: {
        farmers: '/farmers',
        loans: '/loans',
        payments: '/payments',
        machinery: '/machinery',
        schedules: '/schedules',
        complaints: '/complaints'
    },

    /**
     * Fetch all records for a module
     * @param {string} module - Module name (farmers, loans, payments, etc.)
     * @returns {Promise}
     */
    fetchAll: async function(module) {
        try {
            const endpoint = this.endpoints[module];
            if (!endpoint) throw new Error(`Invalid module: ${module}`);
            
            const response = await fetch(endpoint, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) throw new Error(`Failed to fetch ${module}`);
            return await response.json();
        } catch (error) {
            console.error(`Error fetching ${module}:`, error);
            throw error;
        }
    },

    /**
     * Fetch a single record by ID
     * @param {string} module - Module name
     * @param {number} id - Record ID
     * @returns {Promise}
     */
    fetchById: async function(module, id) {
        try {
            const endpoint = `${this.endpoints[module]}/${id}`;
            const response = await fetch(endpoint, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) throw new Error(`Failed to fetch ${module} with ID ${id}`);
            return await response.json();
        } catch (error) {
            console.error(`Error fetching ${module} ID ${id}:`, error);
            throw error;
        }
    },

    /**
     * Create a new record
     * @param {string} module - Module name
     * @param {object} data - Record data
     * @returns {Promise}
     */
    create: async function(module, data) {
        try {
            const endpoint = this.endpoints[module];
            if (!endpoint) throw new Error(`Invalid module: ${module}`);
            
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': this.getCsrfToken()
                },
                body: JSON.stringify(data)
            });
            
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || `Failed to create ${module}`);
            }
            return await response.json();
        } catch (error) {
            console.error(`Error creating ${module}:`, error);
            throw error;
        }
    },

    /**
     * Update a record
     * @param {string} module - Module name
     * @param {number} id - Record ID
     * @param {object} data - Updated data
     * @returns {Promise}
     */
    update: async function(module, id, data) {
        try {
            const endpoint = `${this.endpoints[module]}/${id}`;
            
            const response = await fetch(endpoint, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': this.getCsrfToken()
                },
                body: JSON.stringify(data)
            });
            
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || `Failed to update ${module}`);
            }
            return await response.json();
        } catch (error) {
            console.error(`Error updating ${module} ID ${id}:`, error);
            throw error;
        }
    },

    /**
     * Delete a record
     * @param {string} module - Module name
     * @param {number} id - Record ID
     * @returns {Promise}
     */
    delete: async function(module, id) {
        try {
            const endpoint = `${this.endpoints[module]}/${id}`;
            
            const response = await fetch(endpoint, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': this.getCsrfToken()
                }
            });
            
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || `Failed to delete ${module}`);
            }
            return await response.json();
        } catch (error) {
            console.error(`Error deleting ${module} ID ${id}:`, error);
            throw error;
        }
    },

    /**
     * Get CSRF token from meta tag or cookie
     * @returns {string}
     */
    getCsrfToken: function() {
        const token = document.querySelector('meta[name="csrf-token"]')?.content ||
                      document.querySelector('input[name="_token"]')?.value ||
                      this.getCookie('XSRF-TOKEN');
        return token || '';
    },

    /**
     * Get cookie by name
     * @param {string} name - Cookie name
     * @returns {string|null}
     */
    getCookie: function(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    },

    // ===== MODULE-SPECIFIC FUNCTIONS =====

    /**
     * FARMERS MODULE
     */
    Farmers: {
        fetchAll: () => ModuleManager.fetchAll('farmers'),
        fetchById: (id) => ModuleManager.fetchById('farmers', id),
        create: (data) => ModuleManager.create('farmers', data),
        update: (id, data) => ModuleManager.update('farmers', id, data),
        delete: (id) => ModuleManager.delete('farmers', id),
        
        getActiveFarmers: async function() {
            const farmers = await this.fetchAll();
            return farmers.filter(f => f.status === 'active');
        }
    },

    /**
     * LOANS MODULE
     */
    Loans: {
        fetchAll: () => ModuleManager.fetchAll('loans'),
        fetchById: (id) => ModuleManager.fetchById('loans', id),
        create: (data) => ModuleManager.create('loans', data),
        update: (id, data) => ModuleManager.update('loans', id, data),
        delete: (id) => ModuleManager.delete('loans', id),
        
        getPendingLoans: async function() {
            const loans = await this.fetchAll();
            return loans.filter(l => l.status === 'pending');
        },

        getApprovedLoans: async function() {
            const loans = await this.fetchAll();
            return loans.filter(l => l.status === 'approved');
        }
    },

    /**
     * PAYMENTS MODULE
     */
    Payments: {
        fetchAll: () => ModuleManager.fetchAll('payments'),
        fetchById: (id) => ModuleManager.fetchById('payments', id),
        create: (data) => ModuleManager.create('payments', data),
        update: (id, data) => ModuleManager.update('payments', id, data),
        delete: (id) => ModuleManager.delete('payments', id),
        
        getUnpaidPayments: async function() {
            const payments = await this.fetchAll();
            return payments.filter(p => p.status === 'unpaid');
        },

        calculateTotalPayments: async function() {
            const payments = await this.fetchAll();
            return payments.reduce((total, p) => total + (p.amount || 0), 0);
        }
    },

    /**
     * MACHINERY MODULE
     */
    Machinery: {
        fetchAll: () => ModuleManager.fetchAll('machinery'),
        fetchById: (id) => ModuleManager.fetchById('machinery', id),
        create: (data) => ModuleManager.create('machinery', data),
        update: (id, data) => ModuleManager.update('machinery', id, data),
        delete: (id) => ModuleManager.delete('machinery', id),
        
        getAvailableMachinery: async function() {
            const machinery = await this.fetchAll();
            return machinery.filter(m => m.status === 'available');
        },

        getMaintenanceRequired: async function() {
            const machinery = await this.fetchAll();
            return machinery.filter(m => m.status === 'maintenance');
        }
    },

    /**
     * SCHEDULES MODULE
     */
    Schedules: {
        fetchAll: () => ModuleManager.fetchAll('schedules'),
        fetchById: (id) => ModuleManager.fetchById('schedules', id),
        create: (data) => ModuleManager.create('schedules', data),
        update: (id, data) => ModuleManager.update('schedules', id, data),
        delete: (id) => ModuleManager.delete('schedules', id),
        
        getUpcomingSchedules: async function() {
            const schedules = await this.fetchAll();
            const today = new Date();
            return schedules.filter(s => new Date(s.scheduled_date) >= today);
        },

        getPastSchedules: async function() {
            const schedules = await this.fetchAll();
            const today = new Date();
            return schedules.filter(s => new Date(s.scheduled_date) < today);
        }
    },

    /**
     * COMPLAINTS MODULE
     */
    Complaints: {
        fetchAll: () => ModuleManager.fetchAll('complaints'),
        fetchById: (id) => ModuleManager.fetchById('complaints', id),
        create: (data) => ModuleManager.create('complaints', data),
        update: (id, data) => ModuleManager.update('complaints', id, data),
        delete: (id) => ModuleManager.delete('complaints', id),
        
        getOpenComplaints: async function() {
            const complaints = await this.fetchAll();
            return complaints.filter(c => c.status === 'open');
        },

        getResolvedComplaints: async function() {
            const complaints = await this.fetchAll();
            return complaints.filter(c => c.status === 'resolved');
        }
    }
};

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ModuleManager;
}
