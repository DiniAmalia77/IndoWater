import axios, { AxiosError, AxiosRequestConfig, AxiosResponse } from 'axios';

// Define API response interface
interface ApiResponse<T = any> {
  data: T;
  message: string;
  status: string;
}

// Create axios instance with default config
const api = axios.create({
  baseURL: process.env.REACT_APP_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 15000, // 15 seconds timeout
});

// Add a request interceptor
api.interceptors.request.use(
  (config: AxiosRequestConfig): AxiosRequestConfig => {
    // Get token from localStorage or sessionStorage
    const token = localStorage.getItem('token') || sessionStorage.getItem('token');
    
    // If token exists, add it to the request headers
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    // Add timestamp to prevent caching for GET requests
    if (config.method?.toLowerCase() === 'get') {
      config.params = {
        ...config.params,
        _t: Date.now()
      };
    }
    
    return config;
  },
  (error: AxiosError): Promise<AxiosError> => {
    console.error('Request error:', error);
    return Promise.reject(error);
  }
);

// Add a response interceptor
api.interceptors.response.use(
  (response: AxiosResponse): AxiosResponse => {
    return response;
  },
  (error: AxiosError): Promise<AxiosError> => {
    // Log the error for debugging
    console.error('API Error:', error);
    
    // Handle network errors
    if (!error.response) {
      console.error('Network Error: No response from server');
      // Show a notification or handle offline state
      return Promise.reject(error);
    }
    
    // Handle 401 Unauthorized errors
    if (error.response && error.response.status === 401) {
      // Remove token from localStorage and sessionStorage
      localStorage.removeItem('token');
      sessionStorage.removeItem('token');
      
      // Redirect to login page
      window.location.href = '/login';
    }
    
    // Handle 403 Forbidden errors
    if (error.response && error.response.status === 403) {
      // Redirect to unauthorized page
      window.location.href = '/unauthorized';
    }
    
    // Handle 500 Internal Server Error
    if (error.response && error.response.status === 500) {
      // Redirect to server error page
      window.location.href = '/server-error';
    }
    
    // Handle 422 Validation errors
    if (error.response && error.response.status === 422) {
      // Return the validation errors for form handling
      return Promise.reject(error);
    }
    
    return Promise.reject(error);
  }
);

// Helper methods for common API operations
const apiService = {
  // GET request
  get: async <T>(url: string, params?: any): Promise<ApiResponse<T>> => {
    try {
      const response = await api.get<ApiResponse<T>>(url, { params });
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // POST request
  post: async <T>(url: string, data?: any): Promise<ApiResponse<T>> => {
    try {
      const response = await api.post<ApiResponse<T>>(url, data);
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // PUT request
  put: async <T>(url: string, data?: any): Promise<ApiResponse<T>> => {
    try {
      const response = await api.put<ApiResponse<T>>(url, data);
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // PATCH request
  patch: async <T>(url: string, data?: any): Promise<ApiResponse<T>> => {
    try {
      const response = await api.patch<ApiResponse<T>>(url, data);
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // DELETE request
  delete: async <T>(url: string): Promise<ApiResponse<T>> => {
    try {
      const response = await api.delete<ApiResponse<T>>(url);
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Upload file
  upload: async <T>(url: string, formData: FormData): Promise<ApiResponse<T>> => {
    try {
      const response = await api.post<ApiResponse<T>>(url, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      return response.data;
    } catch (error) {
      throw error;
    }
  },
};

export { api, apiService };
export default apiService;