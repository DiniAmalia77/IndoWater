// User Types
export interface User {
  id: string;
  email: string;
  firstName: string;
  lastName: string;
  role: 'superadmin' | 'client' | 'customer';
  status: 'active' | 'inactive' | 'suspended';
  emailVerified: boolean;
  createdAt: string;
  updatedAt: string;
}

// Client Types
export interface Client {
  id: string;
  userId: string;
  companyName: string;
  companyAddress: string;
  companyPhone: string;
  companyEmail: string;
  businessLicense: string;
  serviceFeePercentage: number;
  status: 'active' | 'inactive' | 'suspended';
  createdAt: string;
  updatedAt: string;
  user?: User;
}

// Customer Types
export interface Customer {
  id: string;
  userId: string;
  firstName: string;
  lastName: string;
  phone: string;
  address: string;
  city: string;
  postalCode: string;
  idNumber: string;
  status: 'active' | 'inactive' | 'suspended';
  createdAt: string;
  updatedAt: string;
  user?: User;
  properties?: Property[];
}

// Property Types
export interface Property {
  id: string;
  customerId: string;
  clientId: string;
  name: string;
  address: string;
  city: string;
  postalCode: string;
  type: 'residential' | 'commercial' | 'industrial';
  latitude?: number;
  longitude?: number;
  status: 'active' | 'inactive' | 'maintenance';
  createdAt: string;
  updatedAt: string;
  customer?: Customer;
  client?: Client;
  meters?: Meter[];
}

// Meter Types
export interface Meter {
  id: string;
  propertyId: string;
  serialNumber: string;
  deviceType: string;
  installationDate: string;
  lastReadingDate?: string;
  currentReading: number;
  creditBalance: number;
  status: 'active' | 'inactive' | 'maintenance' | 'error';
  createdAt: string;
  updatedAt: string;
  property?: Property;
  readings?: MeterReading[];
}

// Meter Reading Types
export interface MeterReading {
  id: string;
  meterId: string;
  reading: number;
  readingDate: string;
  readingType: 'manual' | 'automatic';
  notes?: string;
  createdAt: string;
  meter?: Meter;
}

// Payment Types
export interface Payment {
  id: string;
  orderId: string;
  customerId: string;
  amount: number;
  type: 'credit_purchase' | 'service_fee' | 'penalty';
  gateway: 'midtrans' | 'doku';
  status: 'pending' | 'completed' | 'failed' | 'cancelled';
  gatewayTransactionId?: string;
  gatewayResponse?: any;
  metadata?: any;
  createdAt: string;
  updatedAt: string;
  customer?: Customer;
}

// Credit Types
export interface Credit {
  id: string;
  customerId: string;
  amount: number;
  type: 'purchase' | 'bonus' | 'refund' | 'adjustment';
  source: 'payment' | 'admin' | 'system';
  sourceId?: string;
  description: string;
  status: 'active' | 'expired' | 'used';
  expiresAt?: string;
  createdAt: string;
  customer?: Customer;
}

// API Response Types
export interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  message?: string;
  errors?: Record<string, string[]>;
}

export interface PaginatedResponse<T = any> {
  data: T[];
  pagination: {
    page: number;
    limit: number;
    total: number;
    totalPages: number;
  };
}

// Auth Types
export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  email: string;
  password: string;
  firstName: string;
  lastName: string;
  role: 'client' | 'customer';
}

export interface AuthResponse {
  user: User;
  token: string;
  refreshToken: string;
}

// Dashboard Types
export interface DashboardStats {
  totalCustomers: number;
  totalProperties: number;
  totalMeters: number;
  totalRevenue: number;
  activeMeters: number;
  pendingPayments: number;
  recentTransactions: Payment[];
  meterStatusDistribution: {
    active: number;
    inactive: number;
    maintenance: number;
    error: number;
  };
}

// Form Types
export interface FormField {
  name: string;
  label: string;
  type: 'text' | 'email' | 'password' | 'number' | 'select' | 'textarea' | 'date' | 'checkbox';
  placeholder?: string;
  required?: boolean;
  options?: { value: string; label: string }[];
  validation?: any;
}

// Table Types
export interface TableColumn<T = any> {
  key: keyof T | string;
  label: string;
  sortable?: boolean;
  render?: (value: any, row: T) => React.ReactNode;
  width?: string;
}

export interface TableProps<T = any> {
  data: T[];
  columns: TableColumn<T>[];
  loading?: boolean;
  pagination?: {
    page: number;
    limit: number;
    total: number;
    onPageChange: (page: number) => void;
  };
  onSort?: (key: string, direction: 'asc' | 'desc') => void;
  onRowClick?: (row: T) => void;
}

// Filter Types
export interface FilterOption {
  key: string;
  label: string;
  type: 'text' | 'select' | 'date' | 'daterange';
  options?: { value: string; label: string }[];
}

export interface FilterValues {
  [key: string]: any;
}

// Chart Types
export interface ChartData {
  labels: string[];
  datasets: {
    label: string;
    data: number[];
    backgroundColor?: string | string[];
    borderColor?: string | string[];
    borderWidth?: number;
  }[];
}

// Notification Types
export interface Notification {
  id: string;
  type: 'info' | 'success' | 'warning' | 'error';
  title: string;
  message: string;
  read: boolean;
  createdAt: string;
}

// Theme Types
export interface Theme {
  mode: 'light' | 'dark';
  primaryColor: string;
  secondaryColor: string;
}

// Language Types
export type Language = 'en' | 'id';

// Route Types
export interface RouteConfig {
  path: string;
  component: React.ComponentType;
  exact?: boolean;
  protected?: boolean;
  roles?: User['role'][];
  title?: string;
}