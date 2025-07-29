import axios, { AxiosInstance } from 'axios';
import { 
  ApiResponse, 
  PaginatedResponse, 
  LoginRequest, 
  RegisterRequest, 
  AuthResponse,
  User,
  Client,
  Customer,
  Property,
  Meter,
  Payment,
  Credit,
  DashboardStats
} from '../types';

class ApiService {
  private api: AxiosInstance;

  constructor() {
    this.api = axios.create({
      baseURL: process.env.REACT_APP_API_URL || 'http://localhost:12000/api',
      timeout: 10000,
      headers: {
        'Content-Type': 'application/json',
      },
    });

    // Request interceptor to add auth token
    this.api.interceptors.request.use(
      (config) => {
        const token = localStorage.getItem('token');
        if (token) {
          config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
      },
      (error) => Promise.reject(error)
    );

    // Response interceptor to handle errors
    this.api.interceptors.response.use(
      (response) => response,
      (error) => {
        if (error.response?.status === 401) {
          localStorage.removeItem('token');
          localStorage.removeItem('user');
          window.location.href = '/login';
        }
        return Promise.reject(error);
      }
    );
  }

  // Auth endpoints
  async login(data: LoginRequest): Promise<AuthResponse> {
    const response = await this.api.post<ApiResponse<AuthResponse>>('/auth/login', data);
    return response.data.data!;
  }

  async register(data: RegisterRequest): Promise<AuthResponse> {
    const response = await this.api.post<ApiResponse<AuthResponse>>('/auth/register', data);
    return response.data.data!;
  }

  async logout(): Promise<void> {
    await this.api.post('/auth/logout');
  }

  async refreshToken(): Promise<AuthResponse> {
    const response = await this.api.post<ApiResponse<AuthResponse>>('/auth/refresh');
    return response.data.data!;
  }

  async forgotPassword(email: string): Promise<void> {
    await this.api.post('/auth/forgot-password', { email });
  }

  async resetPassword(token: string, password: string): Promise<void> {
    await this.api.post('/auth/reset-password', { token, password });
  }

  // User endpoints
  async getUsers(params?: any): Promise<PaginatedResponse<User>> {
    const response = await this.api.get<ApiResponse<PaginatedResponse<User>>>('/users', { params });
    return response.data.data!;
  }

  async getUser(id: string): Promise<User> {
    const response = await this.api.get<ApiResponse<User>>(`/users/${id}`);
    return response.data.data!;
  }

  async createUser(data: Partial<User>): Promise<User> {
    const response = await this.api.post<ApiResponse<User>>('/users', data);
    return response.data.data!;
  }

  async updateUser(id: string, data: Partial<User>): Promise<User> {
    const response = await this.api.put<ApiResponse<User>>(`/users/${id}`, data);
    return response.data.data!;
  }

  async deleteUser(id: string): Promise<void> {
    await this.api.delete(`/users/${id}`);
  }

  // Client endpoints
  async getClients(params?: any): Promise<PaginatedResponse<Client>> {
    const response = await this.api.get<ApiResponse<PaginatedResponse<Client>>>('/clients', { params });
    return response.data.data!;
  }

  async getClient(id: string): Promise<Client> {
    const response = await this.api.get<ApiResponse<Client>>(`/clients/${id}`);
    return response.data.data!;
  }

  async createClient(data: Partial<Client>): Promise<Client> {
    const response = await this.api.post<ApiResponse<Client>>('/clients', data);
    return response.data.data!;
  }

  async updateClient(id: string, data: Partial<Client>): Promise<Client> {
    const response = await this.api.put<ApiResponse<Client>>(`/clients/${id}`, data);
    return response.data.data!;
  }

  async deleteClient(id: string): Promise<void> {
    await this.api.delete(`/clients/${id}`);
  }

  // Customer endpoints
  async getCustomers(params?: any): Promise<PaginatedResponse<Customer>> {
    const response = await this.api.get<ApiResponse<PaginatedResponse<Customer>>>('/customers', { params });
    return response.data.data!;
  }

  async getCustomer(id: string): Promise<Customer> {
    const response = await this.api.get<ApiResponse<Customer>>(`/customers/${id}`);
    return response.data.data!;
  }

  async createCustomer(data: Partial<Customer>): Promise<Customer> {
    const response = await this.api.post<ApiResponse<Customer>>('/customers', data);
    return response.data.data!;
  }

  async updateCustomer(id: string, data: Partial<Customer>): Promise<Customer> {
    const response = await this.api.put<ApiResponse<Customer>>(`/customers/${id}`, data);
    return response.data.data!;
  }

  async deleteCustomer(id: string): Promise<void> {
    await this.api.delete(`/customers/${id}`);
  }

  // Property endpoints
  async getProperties(params?: any): Promise<PaginatedResponse<Property>> {
    const response = await this.api.get<ApiResponse<PaginatedResponse<Property>>>('/properties', { params });
    return response.data.data!;
  }

  async getProperty(id: string): Promise<Property> {
    const response = await this.api.get<ApiResponse<Property>>(`/properties/${id}`);
    return response.data.data!;
  }

  async createProperty(data: Partial<Property>): Promise<Property> {
    const response = await this.api.post<ApiResponse<Property>>('/properties', data);
    return response.data.data!;
  }

  async updateProperty(id: string, data: Partial<Property>): Promise<Property> {
    const response = await this.api.put<ApiResponse<Property>>(`/properties/${id}`, data);
    return response.data.data!;
  }

  async deleteProperty(id: string): Promise<void> {
    await this.api.delete(`/properties/${id}`);
  }

  // Meter endpoints
  async getMeters(params?: any): Promise<PaginatedResponse<Meter>> {
    const response = await this.api.get<ApiResponse<PaginatedResponse<Meter>>>('/meters', { params });
    return response.data.data!;
  }

  async getMeter(id: string): Promise<Meter> {
    const response = await this.api.get<ApiResponse<Meter>>(`/meters/${id}`);
    return response.data.data!;
  }

  async createMeter(data: Partial<Meter>): Promise<Meter> {
    const response = await this.api.post<ApiResponse<Meter>>('/meters', data);
    return response.data.data!;
  }

  async updateMeter(id: string, data: Partial<Meter>): Promise<Meter> {
    const response = await this.api.put<ApiResponse<Meter>>(`/meters/${id}`, data);
    return response.data.data!;
  }

  async deleteMeter(id: string): Promise<void> {
    await this.api.delete(`/meters/${id}`);
  }

  // Payment endpoints
  async getPayments(params?: any): Promise<PaginatedResponse<Payment>> {
    const response = await this.api.get<ApiResponse<PaginatedResponse<Payment>>>('/payments', { params });
    return response.data.data!;
  }

  async getPayment(id: string): Promise<Payment> {
    const response = await this.api.get<ApiResponse<Payment>>(`/payments/${id}`);
    return response.data.data!;
  }

  async createPayment(data: any): Promise<Payment> {
    const response = await this.api.post<ApiResponse<Payment>>('/payments', data);
    return response.data.data!;
  }

  async getPaymentStatus(id: string): Promise<Payment> {
    const response = await this.api.get<ApiResponse<Payment>>(`/payments/${id}/status`);
    return response.data.data!;
  }

  async getPaymentMethods(): Promise<any> {
    const response = await this.api.get<ApiResponse<any>>('/payments/methods');
    return response.data.data!;
  }

  // Credit endpoints
  async getCredits(params?: any): Promise<PaginatedResponse<Credit>> {
    const response = await this.api.get<ApiResponse<PaginatedResponse<Credit>>>('/credits', { params });
    return response.data.data!;
  }

  async getCredit(id: string): Promise<Credit> {
    const response = await this.api.get<ApiResponse<Credit>>(`/credits/${id}`);
    return response.data.data!;
  }

  async createCredit(data: Partial<Credit>): Promise<Credit> {
    const response = await this.api.post<ApiResponse<Credit>>('/credits', data);
    return response.data.data!;
  }

  // Dashboard endpoints
  async getDashboardStats(): Promise<DashboardStats> {
    const response = await this.api.get<ApiResponse<DashboardStats>>('/dashboard/stats');
    return response.data.data!;
  }

  // Health check
  async healthCheck(): Promise<any> {
    const response = await this.api.get('/health');
    return response.data;
  }

  // Generic HTTP methods for direct access
  async get(url: string, config?: any): Promise<any> {
    const response = await this.api.get(url, config);
    return response;
  }

  async post(url: string, data?: any, config?: any): Promise<any> {
    const response = await this.api.post(url, data, config);
    return response;
  }

  async put(url: string, data?: any, config?: any): Promise<any> {
    const response = await this.api.put(url, data, config);
    return response;
  }

  async delete(url: string, config?: any): Promise<any> {
    const response = await this.api.delete(url, config);
    return response;
  }

  // Get axios instance for direct access
  getAxiosInstance(): AxiosInstance {
    return this.api;
  }

  // Set authorization header
  setAuthToken(token: string): void {
    this.api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  }

  // Remove authorization header
  removeAuthToken(): void {
    delete this.api.defaults.headers.common['Authorization'];
  }
}

export const apiService = new ApiService();
export default apiService;