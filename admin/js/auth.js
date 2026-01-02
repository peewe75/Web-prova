/**
 * Simple Auth Helper for interacting with PHP Backend
 */
const API_BASE_URL = (window.BCS_CONFIG && window.BCS_CONFIG.API_BASE) ? window.BCS_CONFIG.API_BASE : 'api';

const AuthService = {
    async login(email, password) {
        try {
            const response = await fetch(`${API_BASE_URL}/login.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });
            return await response.json();
        } catch (error) {
            console.error("Login Error:", error);
            return { success: false, message: "Errore di connessione" };
        }
    },

    saveSession(user) {
        localStorage.setItem('user_session', JSON.stringify(user));
    },

    getSession() {
        const session = localStorage.getItem('user_session');
        return session ? JSON.parse(session) : null;
    },

    logout() {
        localStorage.removeItem('user_session');
        window.location.href = 'login.html';
    },

    isAuthenticated() {
        return !!localStorage.getItem('user_session');
    },

    isAdmin() {
        const s = this.getSession();
        return s && s.role === 'admin';
    }
};

// Auto-redirect if trying to access dashboard without auth
if (window.location.pathname.includes('dashboard')) {
    if (!AuthService.isAuthenticated()) {
        window.location.href = 'login.html';
    } else {
        // Role Check
        const user = AuthService.getSession();
        if(/^admin\./.test(window.location.hostname) && user.role !== 'admin') { window.location.href = `https://app.${window.location.hostname.replace(/^admin\./,'')}/`; }
        if(/^app\./.test(window.location.hostname) && user.role === 'admin') { window.location.href = `https://admin.${window.location.hostname.replace(/^app\./,'')}/`; }
    }
}
