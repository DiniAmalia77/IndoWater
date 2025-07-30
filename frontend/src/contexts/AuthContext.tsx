import React, { createContext, useContext, useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import apiService from '../services/apiService';

interface User {
  id: string;
  name: string;
  email: string;
  role: 'superadmin' | 'client' | 'customer';
  status: 'active' | 'inactive' | 'pending' | 'suspended';
}

interface AuthContextType {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  error: string | null;
  login: (email: string, password: string, remember?: boolean) => Promise<void>;
  logout: () => Promise<void>;
  register: (userData: any) => Promise<void>;
  forgotPassword: (email: string) => Promise<void>;
  resetPassword: (token: string, password: string, passwordConfirmation: string) => Promise<void>;
  verifyEmail: (token: string) => Promise<void>;
  resendVerification: (email: string) => Promise<void>;
  updateProfile: (userData: any) => Promise<void>;
  updatePassword: (currentPassword: string, newPassword: string, newPasswordConfirmation: string) => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [isAuthenticated, setIsAuthenticated] = useState<boolean>(false);
  const [isLoading, setIsLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);
  const navigate = useNavigate();
  
  useEffect(() => {
    // Check if user is already logged in
    const checkAuth = async () => {
      try {
        setIsLoading(true);
        
        // Get token from localStorage
        const token = localStorage.getItem('auth_token');
        
        if (!token) {
          setIsAuthenticated(false);
          setUser(null);
          setIsLoading(false);
          return;
        }
        
        // Get user data (token is automatically added by the apiService interceptor)
        const response = await apiService.auth.getUser();
        
        if (response.success && response.data) {
          setUser(response.data);
          setIsAuthenticated(true);
        } else {
          // Token is invalid or expired
          localStorage.removeItem('auth_token');
          localStorage.removeItem('user');
          setIsAuthenticated(false);
          setUser(null);
        }
      } catch (error) {
        console.error('Authentication error:', error);
        setIsAuthenticated(false);
        setUser(null);
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
      } finally {
        setIsLoading(false);
      }
    };
    
    checkAuth();
  }, []);
  
  const login = async (email: string, password: string, remember: boolean = false) => {
    try {
      setIsLoading(true);
      setError(null);
      
      const response = await apiService.auth.login(email, password);
      
      if (response.success && response.data) {
        const { token, user } = response.data;
        
        // Save token and user to localStorage
        localStorage.setItem('auth_token', token);
        localStorage.setItem('user', JSON.stringify(user));
        
        setUser(user);
        setIsAuthenticated(true);
        
        // Redirect based on user role
        if (user.role === 'superadmin') {
          navigate('/dashboard/superadmin');
        } else if (user.role === 'client') {
          navigate('/dashboard/client');
        } else if (user.role === 'customer') {
          navigate('/dashboard/customer');
        }
      } else {
        setError(response.message || 'An error occurred during login');
        setIsAuthenticated(false);
        setUser(null);
      }
    } catch (error: any) {
      console.error('Login error:', error);
      setError('An error occurred during login. Please try again later.');
      setIsAuthenticated(false);
      setUser(null);
    } finally {
      setIsLoading(false);
    }
  };
  
  const logout = async () => {
    try {
      setIsLoading(true);
      
      // Call logout API
      await apiService.auth.logout();
      
      // Clean up regardless of API response
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      
      setUser(null);
      setIsAuthenticated(false);
      
      // Redirect to login page
      navigate('/login');
    } catch (error) {
      console.error('Logout error:', error);
      
      // Even if the API call fails, we should still clear local storage and state
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      setUser(null);
      setIsAuthenticated(false);
      navigate('/login');
    } finally {
      setIsLoading(false);
    }
  };
  
  const register = async (userData: any) => {
    try {
      setIsLoading(true);
      setError(null);
      
      const response = await apiService.auth.register(userData);
      
      if (response.success) {
        // Redirect to login page with success message
        navigate('/login', { state: { message: 'Registration successful! Please check your email to verify your account.' } });
      } else {
        setError(response.message || 'An error occurred during registration');
      }
    } catch (error: any) {
      console.error('Registration error:', error);
      setError('An error occurred during registration. Please try again later.');
    } finally {
      setIsLoading(false);
    }
  };
  
  const forgotPassword = async (email: string) => {
    try {
      setIsLoading(true);
      setError(null);
      
      const response = await apiService.auth.forgotPassword(email);
      
      if (response.success) {
        // Redirect to login page with success message
        navigate('/login', { state: { message: 'Password reset link has been sent to your email.' } });
      } else {
        setError(response.message || 'An error occurred while sending reset link');
      }
    } catch (error: any) {
      console.error('Forgot password error:', error);
      setError('An error occurred while sending reset link. Please try again later.');
    } finally {
      setIsLoading(false);
    }
  };
  
  const resetPassword = async (token: string, password: string, passwordConfirmation: string) => {
    try {
      setIsLoading(true);
      setError(null);
      
      const response = await apiService.auth.resetPassword(token, password, passwordConfirmation);
      
      if (response.success) {
        // Redirect to login page with success message
        navigate('/login', { state: { message: 'Password has been reset successfully.' } });
      } else {
        setError(response.message || 'An error occurred while resetting password');
      }
    } catch (error: any) {
      console.error('Reset password error:', error);
      setError('An error occurred while resetting password. Please try again later.');
    } finally {
      setIsLoading(false);
    }
  };
  
  const verifyEmail = async (token: string) => {
    try {
      setIsLoading(true);
      setError(null);
      
      const response = await apiService.auth.verifyEmail(token);
      
      if (response.success) {
        // Redirect to login page with success message
        navigate('/login', { state: { message: 'Email verified successfully.' } });
      } else {
        setError(response.message || 'An error occurred while verifying email');
      }
    } catch (error: any) {
      console.error('Verify email error:', error);
      setError('An error occurred while verifying email. Please try again later.');
    } finally {
      setIsLoading(false);
    }
  };
  
  const resendVerification = async (email: string) => {
    try {
      setIsLoading(true);
      setError(null);
      
      // This endpoint might not be available in the apiService yet
      const response = await apiService.post('/auth/resend-verification', { email });
      
      if (response.success) {
        // Show success message
        alert('Verification email has been resent.');
      } else {
        setError(response.message || 'An error occurred while resending verification email');
      }
    } catch (error: any) {
      console.error('Resend verification error:', error);
      setError('An error occurred while resending verification email. Please try again later.');
    } finally {
      setIsLoading(false);
    }
  };
  
  const updateProfile = async (userData: any) => {
    try {
      setIsLoading(true);
      setError(null);
      
      const response = await apiService.users.updateProfile(userData);
      
      if (response.success && response.data) {
        setUser(response.data);
        
        // Update stored user data
        localStorage.setItem('user', JSON.stringify(response.data));
        
        // Show success message
        alert('Profile updated successfully.');
      } else {
        setError(response.message || 'An error occurred while updating profile');
      }
    } catch (error: any) {
      console.error('Update profile error:', error);
      setError('An error occurred while updating profile. Please try again later.');
    } finally {
      setIsLoading(false);
    }
  };
  
  const updatePassword = async (currentPassword: string, newPassword: string, newPasswordConfirmation: string) => {
    try {
      setIsLoading(true);
      setError(null);
      
      const response = await apiService.users.changePassword(
        currentPassword,
        newPassword,
        newPasswordConfirmation
      );
      
      if (response.success) {
        // Show success message
        alert('Password updated successfully.');
      } else {
        setError(response.message || 'An error occurred while updating password');
      }
    } catch (error: any) {
      console.error('Update password error:', error);
      setError('An error occurred while updating password. Please try again later.');
    } finally {
      setIsLoading(false);
    }
  };
  
  return (
    <AuthContext.Provider
      value={{
        user,
        isAuthenticated,
        isLoading,
        error,
        login,
        logout,
        register,
        forgotPassword,
        resetPassword,
        verifyEmail,
        resendVerification,
        updateProfile,
        updatePassword,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = (): AuthContextType => {
  const context = useContext(AuthContext);
  
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  
  return context;
};