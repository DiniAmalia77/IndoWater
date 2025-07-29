import React, { useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { ToastContainer } from 'react-toastify';
import { useAuthStore } from './store/authStore';
import { useAppStore } from './store/appStore';
import Layout from './components/layout/Layout';
import LoginPage from './pages/auth/LoginPage';
import RegisterPage from './pages/auth/RegisterPage';
import DashboardPage from './pages/dashboard/DashboardPage';
import ProtectedRoute from './components/common/ProtectedRoute';
import 'react-toastify/dist/ReactToastify.css';
import './styles/index.css';
import './i18n_simple';

function App() {
  const { isAuthenticated, user } = useAuthStore();
  const { theme } = useAppStore();

  useEffect(() => {
    // Apply theme to document
    document.documentElement.classList.toggle('dark', theme === 'dark');
  }, [theme]);

  return (
    <Router>
      <div className="App">
        <Routes>
          {/* Public routes */}
          <Route 
            path="/login" 
            element={
              isAuthenticated ? <Navigate to="/dashboard" replace /> : <LoginPage />
            } 
          />
          <Route 
            path="/register" 
            element={
              isAuthenticated ? <Navigate to="/dashboard" replace /> : <RegisterPage />
            } 
          />
          
          {/* Protected routes */}
          <Route
            path="/"
            element={
              <ProtectedRoute>
                <Layout />
              </ProtectedRoute>
            }
          >
            <Route index element={<Navigate to="/dashboard" replace />} />
            <Route path="dashboard" element={<DashboardPage />} />
            
            {/* User management routes - Super Admin only */}
            {user?.role === 'superadmin' && (
              <>
                <Route path="users" element={<div>Users Page</div>} />
                <Route path="clients" element={<div>Clients Page</div>} />
              </>
            )}
            
            {/* Client and Super Admin routes */}
            {(user?.role === 'client' || user?.role === 'superadmin') && (
              <>
                <Route path="customers" element={<div>Customers Page</div>} />
                <Route path="properties" element={<div>Properties Page</div>} />
                <Route path="meters" element={<div>Meters Page</div>} />
                <Route path="reports" element={<div>Reports Page</div>} />
              </>
            )}
            
            {/* Common routes for all authenticated users */}
            <Route path="payments" element={<div>Payments Page</div>} />
            <Route path="settings" element={<div>Settings Page</div>} />
            
            {/* Catch all route */}
            <Route path="*" element={<Navigate to="/dashboard" replace />} />
          </Route>
        </Routes>
        
        <ToastContainer
          position="top-right"
          autoClose={5000}
          hideProgressBar={false}
          newestOnTop={false}
          closeOnClick
          rtl={false}
          pauseOnFocusLoss
          draggable
          pauseOnHover
          theme={theme}
        />
      </div>
    </Router>
  );
}

export default App;