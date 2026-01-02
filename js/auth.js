/**
 * Simple Auth Helper for interacting with PHP Backend
 */
const API_BASE_URL = 'api'; // Percorso relativo, funziona se servito dalla stessa root

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

            if (!response.ok) {
                // Try fallback endpoint
                const fallback = await fetch(`${API_BASE_URL}/login_simple.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });
                return await fallback.json();
            }

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
        if (window.location.pathname.includes('admin') && user.role !== 'admin') {
            window.location.href = 'dashboard-cliente.html';
        }
        if (window.location.pathname.includes('cliente') && user.role === 'admin') {
            // Optional: Admins can view client dashboard? Maybe better to redirect or allow. 
            // For now, let's keep them on admin unless they explicitly go there? 
            // Better: Redirect to admin if they land on client dashboard by accident
            window.location.href = 'dashboard-admin.html';
        }
    }
}
