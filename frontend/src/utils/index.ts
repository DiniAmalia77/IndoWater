import { format, parseISO, formatDistanceToNow } from 'date-fns';
import { id as idLocale, enUS } from 'date-fns/locale';

// Date formatting utilities
export const formatDate = (date: string | Date, formatStr = 'dd/MM/yyyy', locale = 'en') => {
  const dateObj = typeof date === 'string' ? parseISO(date) : date;
  const localeObj = locale === 'id' ? idLocale : enUS;
  return format(dateObj, formatStr, { locale: localeObj });
};

export const formatDateTime = (date: string | Date, locale = 'en') => {
  return formatDate(date, 'dd/MM/yyyy HH:mm', locale);
};

export const formatRelativeTime = (date: string | Date, locale = 'en') => {
  const dateObj = typeof date === 'string' ? parseISO(date) : date;
  const localeObj = locale === 'id' ? idLocale : enUS;
  return formatDistanceToNow(dateObj, { addSuffix: true, locale: localeObj });
};

// Currency formatting
export const formatCurrency = (amount: number, currency = 'IDR', locale = 'id-ID') => {
  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency,
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
};

export const formatNumber = (number: number, locale = 'id-ID') => {
  return new Intl.NumberFormat(locale).format(number);
};

// String utilities
export const capitalize = (str: string) => {
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

export const truncate = (str: string, length: number) => {
  return str.length > length ? str.substring(0, length) + '...' : str;
};

export const slugify = (str: string) => {
  return str
    .toLowerCase()
    .replace(/[^\w\s-]/g, '')
    .replace(/[\s_-]+/g, '-')
    .replace(/^-+|-+$/g, '');
};

// Validation utilities
export const isValidEmail = (email: string) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};

export const isValidPhone = (phone: string) => {
  const phoneRegex = /^(\+62|62|0)8[1-9][0-9]{6,9}$/;
  return phoneRegex.test(phone.replace(/\s/g, ''));
};

export const isValidIdNumber = (idNumber: string) => {
  return /^\d{16}$/.test(idNumber);
};

// Array utilities
export const groupBy = <T>(array: T[], key: keyof T) => {
  return array.reduce((groups, item) => {
    const group = (groups[item[key] as string] || []);
    group.push(item);
    groups[item[key] as string] = group;
    return groups;
  }, {} as Record<string, T[]>);
};

export const sortBy = <T>(array: T[], key: keyof T, direction: 'asc' | 'desc' = 'asc') => {
  return [...array].sort((a, b) => {
    const aVal = a[key];
    const bVal = b[key];
    
    if (aVal < bVal) return direction === 'asc' ? -1 : 1;
    if (aVal > bVal) return direction === 'asc' ? 1 : -1;
    return 0;
  });
};

// Object utilities
export const omit = <T extends Record<string, any>, K extends keyof T>(
  obj: T,
  keys: K[]
): Omit<T, K> => {
  const result = { ...obj };
  keys.forEach(key => delete result[key]);
  return result;
};

export const pick = <T extends Record<string, any>, K extends keyof T>(
  obj: T,
  keys: K[]
): Pick<T, K> => {
  const result = {} as Pick<T, K>;
  keys.forEach(key => {
    if (key in obj) {
      result[key] = obj[key];
    }
  });
  return result;
};

// Status utilities
export const getStatusColor = (status: string) => {
  const statusColors: Record<string, string> = {
    active: 'green',
    inactive: 'gray',
    suspended: 'red',
    pending: 'yellow',
    completed: 'green',
    failed: 'red',
    cancelled: 'gray',
    maintenance: 'orange',
    error: 'red',
  };
  return statusColors[status.toLowerCase()] || 'gray';
};

export const getStatusText = (status: string, locale = 'en') => {
  const statusTexts: Record<string, Record<string, string>> = {
    en: {
      active: 'Active',
      inactive: 'Inactive',
      suspended: 'Suspended',
      pending: 'Pending',
      completed: 'Completed',
      failed: 'Failed',
      cancelled: 'Cancelled',
      maintenance: 'Maintenance',
      error: 'Error',
    },
    id: {
      active: 'Aktif',
      inactive: 'Tidak Aktif',
      suspended: 'Ditangguhkan',
      pending: 'Menunggu',
      completed: 'Selesai',
      failed: 'Gagal',
      cancelled: 'Dibatalkan',
      maintenance: 'Pemeliharaan',
      error: 'Error',
    },
  };
  return statusTexts[locale]?.[status.toLowerCase()] || status;
};

// File utilities
export const formatFileSize = (bytes: number) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

export const getFileExtension = (filename: string) => {
  return filename.slice((filename.lastIndexOf('.') - 1 >>> 0) + 2);
};

// URL utilities
export const buildQueryString = (params: Record<string, any>) => {
  const searchParams = new URLSearchParams();
  Object.entries(params).forEach(([key, value]) => {
    if (value !== null && value !== undefined && value !== '') {
      searchParams.append(key, String(value));
    }
  });
  return searchParams.toString();
};

export const parseQueryString = (queryString: string) => {
  const params = new URLSearchParams(queryString);
  const result: Record<string, string> = {};
  params.forEach((value, key) => {
    result[key] = value;
  });
  return result;
};

// Color utilities
export const hexToRgb = (hex: string) => {
  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  return result ? {
    r: parseInt(result[1], 16),
    g: parseInt(result[2], 16),
    b: parseInt(result[3], 16)
  } : null;
};

export const rgbToHex = (r: number, g: number, b: number) => {
  return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
};

// Debounce utility
export const debounce = <T extends (...args: any[]) => any>(
  func: T,
  wait: number
): ((...args: Parameters<T>) => void) => {
  let timeout: NodeJS.Timeout;
  return (...args: Parameters<T>) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => func(...args), wait);
  };
};

// Throttle utility
export const throttle = <T extends (...args: any[]) => any>(
  func: T,
  limit: number
): ((...args: Parameters<T>) => void) => {
  let inThrottle: boolean;
  return (...args: Parameters<T>) => {
    if (!inThrottle) {
      func(...args);
      inThrottle = true;
      setTimeout(() => inThrottle = false, limit);
    }
  };
};

// Deep clone utility
export const deepClone = <T>(obj: T): T => {
  if (obj === null || typeof obj !== 'object') return obj;
  if (obj instanceof Date) return new Date(obj.getTime()) as unknown as T;
  if (obj instanceof Array) return obj.map(item => deepClone(item)) as unknown as T;
  if (typeof obj === 'object') {
    const clonedObj = {} as T;
    Object.keys(obj).forEach(key => {
      (clonedObj as any)[key] = deepClone((obj as any)[key]);
    });
    return clonedObj;
  }
  return obj;
};

// Generate random ID
export const generateId = (length = 8) => {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  let result = '';
  for (let i = 0; i < length; i++) {
    result += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  return result;
};

// Check if object is empty
export const isEmpty = (obj: any): boolean => {
  if (obj == null) return true;
  if (Array.isArray(obj) || typeof obj === 'string') return obj.length === 0;
  if (typeof obj === 'object') return Object.keys(obj).length === 0;
  return false;
};