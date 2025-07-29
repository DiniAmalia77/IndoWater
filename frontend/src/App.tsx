import React, { useEffect } from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useTheme } from './contexts/ThemeContext';
import { useAuth } from './contexts/AuthContext';

// Layouts
import AuthLayout from './components/layouts/AuthLayout';
import DashboardLayout from './components/layouts/DashboardLayout';

// Auth Pages
import Login from './pages/auth/Login';
import Register from './pages/auth/Register';
import ForgotPassword from './pages/auth/ForgotPassword';
import ResetPassword from './pages/auth/ResetPassword';
import VerifyEmail from './pages/auth/VerifyEmail';

// Dashboard Pages
import SuperadminDashboard from './pages/dashboard/superadmin/Dashboard';
import ClientDashboard from './pages/dashboard/client/Dashboard';
import CustomerDashboard from './pages/dashboard/customer/Dashboard';

// Superadmin Pages
import ClientManagement from './pages/dashboard/superadmin/ClientManagement';

// Client Pages
import PropertyManagement from './pages/dashboard/client/PropertyManagement';
import CustomerManagement from './pages/dashboard/client/CustomerManagement';
import MeterManagement from './pages/dashboard/client/MeterManagement';
import PaymentManagement from './pages/dashboard/client/PaymentManagement';

// Customer Pages
import MyMeters from './pages/dashboard/customer/MyMeters';
import ConsumptionHistory from './pages/dashboard/customer/ConsumptionHistory';
import PaymentHistory from './pages/dashboard/customer/PaymentHistory';
import TopUp from './pages/dashboard/customer/TopUp';

// Common Pages
import Profile from './pages/dashboard/profile/Profile';
import Settings from './pages/dashboard/settings/Settings';

// Error Pages
import NotFound from './pages/errors/NotFound';
import Unauthorized from './pages/errors/Unauthorized';
import ServerError from './pages/errors/ServerError';

// Protected Route Component
const ProtectedRoute = ({ children, allowedRoles }: { children: React.ReactNode, allowedRoles: string[] }) => {
  const { isAuthenticated, user } = useAuth();
  
  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }
  
  if (user && !allowedRoles.includes(user.role)) {
    return <Navigate to="/unauthorized" replace />;
  }
  
  return <>{children}</>;
};

// Dynamic Dashboard Redirect Component
const DynamicDashboardRedirect = () => {
  const { user } = useAuth();
  
  if (!user) {
    return <Navigate to="/login" replace />;
  }
  
  switch (user.role) {
    case 'superadmin':
      return <Navigate to="/dashboard/superadmin" replace />;
    case 'client':
      return <Navigate to="/dashboard/client" replace />;
    case 'customer':
      return <Navigate to="/dashboard/customer" replace />;
    default:
      return <Navigate to="/unauthorized" replace />;
  }
};

function App() {
  const { t } = useTranslation();
  const { theme } = useTheme();
  
  useEffect(() => {
    // Apply theme class to body
    document.body.className = theme;
    
    // Set page title
    document.title = t('app.title');
  }, [theme, t]);
  
  return (
    <div className={`App ${theme}`}>
      <Routes>
        {/* Auth Routes */}
        <Route path="/" element={<AuthLayout />}>
          <Route index element={<Navigate to="/login" replace />} />
          <Route path="login" element={<Login />} />
          <Route path="register" element={<Register />} />
          <Route path="forgot-password" element={<ForgotPassword />} />
          <Route path="reset-password" element={<ResetPassword />} />
          <Route path="verify-email" element={<VerifyEmail />} />
        </Route>
        
        {/* Dashboard Routes */}
        <Route path="/dashboard" element={
          <ProtectedRoute allowedRoles={['superadmin', 'client', 'customer']}>
            <DashboardLayout />
          </ProtectedRoute>
        }>
          <Route index element={<Navigate to="/dashboard/home" replace />} />
          
          {/* Dynamic Dashboard Home based on user role */}
          <Route path="home" element={
            <ProtectedRoute allowedRoles={['superadmin', 'client', 'customer']}>
              <DynamicDashboardRedirect />
            </ProtectedRoute>
          } />
          
          {/* Common Routes */}
          <Route path="profile" element={
            <ProtectedRoute allowedRoles={['superadmin', 'client', 'customer']}>
              <Profile />
            </ProtectedRoute>
          } />
          <Route path="settings" element={
            <ProtectedRoute allowedRoles={['superadmin', 'client', 'customer']}>
              <Settings />
            </ProtectedRoute>
          } />
          
          {/* Superadmin Routes */}
          <Route path="superadmin" element={
            <ProtectedRoute allowedRoles={['superadmin']}>
              <SuperadminDashboard />
            </ProtectedRoute>
          } />
          <Route path="superadmin/clients" element={
            <ProtectedRoute allowedRoles={['superadmin']}>
              <ClientManagement />
            </ProtectedRoute>
          } />
          
          {/* Client Routes */}
          <Route path="client" element={
            <ProtectedRoute allowedRoles={['client']}>
              <ClientDashboard />
            </ProtectedRoute>
          } />
          <Route path="client/properties" element={
            <ProtectedRoute allowedRoles={['client']}>
              <PropertyManagement />
            </ProtectedRoute>
          } />
          <Route path="client/customers" element={
            <ProtectedRoute allowedRoles={['client']}>
              <CustomerManagement />
            </ProtectedRoute>
          } />
          <Route path="client/meters" element={
            <ProtectedRoute allowedRoles={['client']}>
              <MeterManagement />
            </ProtectedRoute>
          } />
          <Route path="client/payments" element={
            <ProtectedRoute allowedRoles={['client']}>
              <PaymentManagement />
            </ProtectedRoute>
          } />
          
          {/* Customer Routes */}
          <Route path="customer" element={
            <ProtectedRoute allowedRoles={['customer']}>
              <CustomerDashboard />
            </ProtectedRoute>
          } />
          <Route path="customer/meters" element={
            <ProtectedRoute allowedRoles={['customer']}>
              <MyMeters />
            </ProtectedRoute>
          } />
          <Route path="customer/consumption" element={
            <ProtectedRoute allowedRoles={['customer']}>
              <ConsumptionHistory />
            </ProtectedRoute>
          } />
          <Route path="customer/payments" element={
            <ProtectedRoute allowedRoles={['customer']}>
              <PaymentHistory />
            </ProtectedRoute>
          } />
          <Route path="customer/topup" element={
            <ProtectedRoute allowedRoles={['customer']}>
              <TopUp />
            </ProtectedRoute>
          } />
        </Route>
        
        {/* Error Routes */}
        <Route path="/unauthorized" element={<Unauthorized />} />
        <Route path="/server-error" element={<ServerError />} />
        <Route path="*" element={<NotFound />} />
      </Routes>
    </div>
  );
}

export default App;