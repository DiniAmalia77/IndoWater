import axios, { AxiosRequestConfig, AxiosResponse, AxiosError } from 'axios';

// Define API response interface
export interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  message?: string;
  errors?: Record<string, string[]>;
}

// Create axios instance with default config
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 10000,
});

// Request interceptor for adding auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor for handling common errors
api.interceptors.response.use(
  (response) => response,
  (error: AxiosError) => {
    // Handle session expiration
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    
    // Handle server errors
    if (error.response?.status === 500) {
      console.error('Server error:', error);
    }
    
    return Promise.reject(error);
  }
);

// Generic request method
const request = async <T>(config: AxiosRequestConfig): Promise<ApiResponse<T>> => {
  try {
    const response: AxiosResponse<ApiResponse<T>> = await api(config);
    return response.data;
  } catch (error: any) {
    if (error.response) {
      // The request was made and the server responded with a status code
      // that falls out of the range of 2xx
      return error.response.data as ApiResponse<T>;
    } else if (error.request) {
      // The request was made but no response was received
      console.error('No response received:', error.request);
      return {
        success: false,
        message: 'No response received from server. Please check your internet connection.',
      };
    } else {
      // Something happened in setting up the request that triggered an Error
      console.error('Request error:', error.message);
      return {
        success: false,
        message: 'An error occurred while making the request.',
      };
    }
  }
};

// API service methods
const apiService = {
  // Auth endpoints
  auth: {
    login: (email: string, password: string) => 
      request({
        method: 'POST',
        url: '/auth/login',
        data: { email, password },
      }),
    
    register: (userData: any) => 
      request({
        method: 'POST',
        url: '/auth/register',
        data: userData,
      }),
    
    forgotPassword: (email: string) => 
      request({
        method: 'POST',
        url: '/auth/forgot-password',
        data: { email },
      }),
    
    resetPassword: (token: string, password: string, password_confirmation: string) => 
      request({
        method: 'POST',
        url: '/auth/reset-password',
        data: { token, password, password_confirmation },
      }),
    
    verifyEmail: (token: string) => 
      request({
        method: 'POST',
        url: '/auth/verify-email',
        data: { token },
      }),
    
    logout: () => 
      request({
        method: 'POST',
        url: '/auth/logout',
      }),
    
    getUser: () => 
      request({
        method: 'GET',
        url: '/auth/user',
      }),
  },
  
  // User endpoints
  users: {
    getProfile: () => 
      request({
        method: 'GET',
        url: '/users/profile',
      }),
    
    updateProfile: (userData: any) => 
      request({
        method: 'PUT',
        url: '/users/profile',
        data: userData,
      }),
    
    changePassword: (currentPassword: string, newPassword: string, newPasswordConfirmation: string) => 
      request({
        method: 'PUT',
        url: '/users/change-password',
        data: {
          current_password: currentPassword,
          password: newPassword,
          password_confirmation: newPasswordConfirmation,
        },
      }),
  },
  
  // Generic CRUD methods
  get: <T>(url: string, params?: any) => 
    request<T>({
      method: 'GET',
      url,
      params,
    }),
  
  post: <T>(url: string, data?: any) => 
    request<T>({
      method: 'POST',
      url,
      data,
    }),
  
  put: <T>(url: string, data?: any) => 
    request<T>({
      method: 'PUT',
      url,
      data,
    }),
  
  patch: <T>(url: string, data?: any) => 
    request<T>({
      method: 'PATCH',
      url,
      data,
    }),
  
  delete: <T>(url: string) => 
    request<T>({
      method: 'DELETE',
      url,
    }),
};

export default apiService;