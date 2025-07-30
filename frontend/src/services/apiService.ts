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

// Define interfaces for API data types
export interface User {
  id: string;
  name: string;
  email: string;
  role: 'superadmin' | 'client' | 'customer';
  status: 'active' | 'inactive';
}

export interface Client {
  id: string;
  name: string;
  email: string;
  phone: string;
  address: string;
  status: 'active' | 'inactive';
  created_at: string;
  properties_count?: number;
  customers_count?: number;
  properties?: Property[];
  stats?: ClientStats;
}

export interface ClientStats {
  total_properties: number;
  total_customers: number;
  active_meters: number;
  inactive_meters: number;
  total_consumption: number;
  total_revenue: number;
  monthly_stats?: MonthlyStats[];
}

export interface MonthlyStats {
  month: string;
  consumption: number;
  revenue: number;
}

export interface Property {
  id: string;
  client_id: string;
  name: string;
  address: string;
  type: 'apartment' | 'residential' | 'commercial';
  status: 'active' | 'inactive';
  created_at: string;
  customers_count?: number;
  meters_count?: number;
  client?: {
    id: string;
    name: string;
  };
  stats?: PropertyStats;
  customers?: Customer[];
}

export interface PropertyStats {
  total_customers: number;
  active_meters: number;
  inactive_meters: number;
  total_consumption: number;
  total_revenue: number;
  monthly_stats?: MonthlyStats[];
}

export interface Customer {
  id: string;
  client_id: string;
  property_id: string;
  name: string;
  email: string;
  phone: string;
  unit: string;
  meter_id: string;
  status: 'active' | 'inactive';
  created_at: string;
  property?: {
    id: string;
    name: string;
    address?: string;
  };
  client?: {
    id: string;
    name: string;
  };
  meter?: Meter;
  consumption_history?: ConsumptionRecord[];
  payment_history?: Payment[];
}

export interface Meter {
  id: string;
  client_id: string;
  property_id: string;
  customer_id: string;
  serial_number: string;
  installation_date: string;
  last_reading_date: string | null;
  last_reading_value: number;
  status: 'active' | 'inactive';
  customer?: {
    id: string;
    name: string;
    unit: string;
  };
  property?: {
    id: string;
    name: string;
    address?: string;
  };
  client?: {
    id: string;
    name: string;
  };
  readings?: MeterReading[];
  maintenance_history?: MaintenanceRecord[];
}

export interface MeterReading {
  id?: string;
  meter_id: string;
  date: string;
  value: number;
  consumption: number;
  status: 'pending_verification' | 'verified' | 'rejected';
  submitted_by?: string;
  submitted_at?: string;
  verified_by?: string;
  verified_at?: string;
}

export interface MaintenanceRecord {
  date: string;
  type: 'installation' | 'inspection' | 'repair' | 'calibration' | 'replacement';
  description: string;
  technician: string;
}

export interface ConsumptionRecord {
  month: string;
  year: number;
  reading_date: string;
  reading_value: number;
  consumption: number;
  amount: number;
  status: 'paid' | 'unpaid' | 'overdue';
}

export interface Payment {
  id: string;
  customer_id: string;
  client_id: string;
  invoice_number: string;
  amount: number;
  period: string;
  due_date: string;
  payment_date: string | null;
  payment_method: 'bank_transfer' | 'credit_card' | 'e_wallet' | null;
  status: 'pending' | 'completed' | 'failed' | 'cancelled';
  customer?: {
    id: string;
    name: string;
  };
  invoice_details?: InvoiceDetails;
  payment_details?: PaymentDetails;
}

export interface InvoiceDetails {
  meter_id: string;
  previous_reading: number;
  current_reading: number;
  consumption: number;
  rate: number;
  subtotal: number;
  tax: number;
  total: number;
}

export interface PaymentDetails {
  transaction_id: string;
  payment_method: 'bank_transfer' | 'credit_card' | 'e_wallet';
  bank?: string;
  account_number?: string;
  card_type?: string;
  card_number?: string;
  wallet_provider?: string;
  wallet_account?: string;
  payment_date: string;
  payment_time: string;
  status: 'completed' | 'pending' | 'failed';
}

export interface PaginatedResponse<T> {
  data: T[];
  pagination: {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
  };
}

// API service methods
const apiService = {
  // Auth endpoints
  auth: {
    login: (email: string, password: string) => 
      request<{ token: string; user: User }>({
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
      request<User>({
        method: 'GET',
        url: '/auth/user',
      }),
  },
  
  // User endpoints
  users: {
    getProfile: () => 
      request<User>({
        method: 'GET',
        url: '/users/me',
      }),
    
    updateProfile: (userData: Partial<User>) => 
      request<User>({
        method: 'PUT',
        url: '/users/me',
        data: userData,
      }),
    
    changePassword: (currentPassword: string, newPassword: string, newPasswordConfirmation: string) => 
      request({
        method: 'PUT',
        url: '/users/me/password',
        data: {
          current_password: currentPassword,
          password: newPassword,
          password_confirmation: newPasswordConfirmation,
        },
      }),
  },
  
  // Client endpoints
  clients: {
    getAll: (params?: { page?: number; limit?: number; status?: string }) => 
      request<PaginatedResponse<Client>>({
        method: 'GET',
        url: '/clients',
        params,
      }),
    
    getById: (id: string) => 
      request<Client>({
        method: 'GET',
        url: `/clients/${id}`,
      }),
    
    create: (clientData: Partial<Client>) => 
      request<Client>({
        method: 'POST',
        url: '/clients',
        data: clientData,
      }),
    
    update: (id: string, clientData: Partial<Client>) => 
      request<Client>({
        method: 'PUT',
        url: `/clients/${id}`,
        data: clientData,
      }),
    
    delete: (id: string) => 
      request({
        method: 'DELETE',
        url: `/clients/${id}`,
      }),
    
    getStats: (id: string) => 
      request<ClientStats>({
        method: 'GET',
        url: `/clients/${id}/stats`,
      }),
  },
  
  // Property endpoints
  properties: {
    getAll: (params?: { 
      page?: number; 
      limit?: number; 
      status?: string;
      type?: string;
      client_id?: string;
    }) => 
      request<PaginatedResponse<Property>>({
        method: 'GET',
        url: '/properties',
        params,
      }),
    
    getById: (id: string) => 
      request<Property>({
        method: 'GET',
        url: `/properties/${id}`,
      }),
    
    create: (propertyData: Partial<Property>) => 
      request<Property>({
        method: 'POST',
        url: '/properties',
        data: propertyData,
      }),
    
    update: (id: string, propertyData: Partial<Property>) => 
      request<Property>({
        method: 'PUT',
        url: `/properties/${id}`,
        data: propertyData,
      }),
    
    delete: (id: string) => 
      request({
        method: 'DELETE',
        url: `/properties/${id}`,
      }),
    
    getStats: (id: string) => 
      request<PropertyStats>({
        method: 'GET',
        url: `/properties/${id}/stats`,
      }),
  },
  
  // Customer endpoints
  customers: {
    getAll: (params?: { 
      page?: number; 
      limit?: number; 
      status?: string;
      search?: string;
      property_id?: string;
      client_id?: string;
    }) => 
      request<PaginatedResponse<Customer>>({
        method: 'GET',
        url: '/customers',
        params,
      }),
    
    getById: (id: string) => 
      request<Customer>({
        method: 'GET',
        url: `/customers/${id}`,
      }),
    
    create: (customerData: Partial<Customer>) => 
      request<Customer>({
        method: 'POST',
        url: '/customers',
        data: customerData,
      }),
    
    update: (id: string, customerData: Partial<Customer>) => 
      request<Customer>({
        method: 'PUT',
        url: `/customers/${id}`,
        data: customerData,
      }),
    
    delete: (id: string) => 
      request({
        method: 'DELETE',
        url: `/customers/${id}`,
      }),
    
    getConsumption: (id: string, params?: { 
      year?: number; 
      month?: string;
      status?: string;
    }) => 
      request<ConsumptionRecord[]>({
        method: 'GET',
        url: `/customers/${id}/consumption`,
        params,
      }),
    
    getPayments: (id: string, params?: { 
      status?: string;
      method?: string;
      period?: string;
    }) => 
      request<Payment[]>({
        method: 'GET',
        url: `/customers/${id}/payments`,
        params,
      }),
  },
  
  // Meter endpoints
  meters: {
    getAll: (params?: { 
      page?: number; 
      limit?: number; 
      status?: string;
      search?: string;
      property_id?: string;
      client_id?: string;
      customer_id?: string;
    }) => 
      request<PaginatedResponse<Meter>>({
        method: 'GET',
        url: '/meters',
        params,
      }),
    
    getById: (id: string) => 
      request<Meter>({
        method: 'GET',
        url: `/meters/${id}`,
      }),
    
    create: (meterData: Partial<Meter>) => 
      request<Meter>({
        method: 'POST',
        url: '/meters',
        data: meterData,
      }),
    
    update: (id: string, meterData: Partial<Meter>) => 
      request<Meter>({
        method: 'PUT',
        url: `/meters/${id}`,
        data: meterData,
      }),
    
    delete: (id: string) => 
      request({
        method: 'DELETE',
        url: `/meters/${id}`,
      }),
    
    submitReading: (id: string, readingData: { reading_value: number; reading_date: string }) => 
      request<MeterReading>({
        method: 'POST',
        url: `/meters/${id}/readings`,
        data: readingData,
      }),
    
    verifyReading: (meterId: string, readingId: string, status: 'verified' | 'rejected', notes?: string) => 
      request<MeterReading>({
        method: 'PUT',
        url: `/meters/${meterId}/readings/${readingId}`,
        data: { status, notes },
      }),
    
    getReadings: (id: string, params?: { 
      status?: string;
      start_date?: string;
      end_date?: string;
    }) => 
      request<MeterReading[]>({
        method: 'GET',
        url: `/meters/${id}/readings`,
        params,
      }),
  },
  
  // Payment endpoints
  payments: {
    getAll: (params?: { 
      page?: number; 
      limit?: number; 
      status?: string;
      period?: string;
      start_date?: string;
      end_date?: string;
      customer_id?: string;
      client_id?: string;
    }) => 
      request<PaginatedResponse<Payment>>({
        method: 'GET',
        url: '/payments',
        params,
      }),
    
    getById: (id: string) => 
      request<Payment>({
        method: 'GET',
        url: `/payments/${id}`,
      }),
    
    create: (paymentData: Partial<Payment>) => 
      request<Payment>({
        method: 'POST',
        url: '/payments',
        data: paymentData,
      }),
    
    update: (id: string, paymentData: Partial<Payment>) => 
      request<Payment>({
        method: 'PUT',
        url: `/payments/${id}`,
        data: paymentData,
      }),
    
    delete: (id: string) => 
      request({
        method: 'DELETE',
        url: `/payments/${id}`,
      }),
    
    process: (id: string, paymentMethod: 'bank_transfer' | 'credit_card' | 'e_wallet', paymentDetails: any) => 
      request<Payment>({
        method: 'POST',
        url: `/payments/${id}/process`,
        data: {
          payment_method: paymentMethod,
          ...paymentDetails,
        },
      }),
    
    generateInvoice: (id: string) => 
      request({
        method: 'GET',
        url: `/payments/${id}/invoice`,
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