import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import LanguageDetector from 'i18next-browser-languagedetector';

// Translation resources
const resources = {
  en: {
    translation: {
      // Common
      common: {
        search: 'Search...',
        profile: 'Profile',
        settings: 'Settings',
        email: 'Email',
        password: 'Password',
        confirmPassword: 'Confirm Password',
        firstName: 'First Name',
        lastName: 'Last Name',
        role: 'Role',
        save: 'Save',
        cancel: 'Cancel',
        delete: 'Delete',
        edit: 'Edit',
        view: 'View',
        add: 'Add',
        create: 'Create',
        update: 'Update',
        loading: 'Loading...',
        noData: 'No data available',
      },
      
      // Navigation
      navigation: {
        dashboard: 'Dashboard',
        users: 'Users',
        clients: 'Clients',
        customers: 'Customers',
        properties: 'Properties',
        meters: 'Meters',
        payments: 'Payments',
        reports: 'Reports',
        settings: 'Settings',
      },
      
      // Auth
      auth: {
        login: {
          title: 'Sign in to your account',
          subtitle: 'Welcome back to IndoWater',
          emailPlaceholder: 'Enter your email',
          passwordPlaceholder: 'Enter your password',
          rememberMe: 'Remember me',
          submit: 'Sign in',
          success: 'Login successful',
          error: 'Login failed',
          link: 'Sign in',
        },
        register: {
          title: 'Create your account',
          subtitle: 'Join IndoWater today',
          firstNamePlaceholder: 'Enter your first name',
          lastNamePlaceholder: 'Enter your last name',
          emailPlaceholder: 'Enter your email',
          passwordPlaceholder: 'Enter your password',
          confirmPasswordPlaceholder: 'Confirm your password',
          submit: 'Create account',
          success: 'Registration successful',
          error: 'Registration failed',
          link: 'Sign up',
        },
        logout: 'Sign out',
        forgotPassword: {
          link: 'Forgot your password?',
        },
      },
      
      // Roles
      roles: {
        superadmin: 'Super Admin',
        client: 'Client',
        customer: 'Customer',
      },
      
      // Dashboard
      dashboard: {
        title: 'Dashboard',
        welcome: 'Welcome back, {{name}}!',
        recentActivity: 'Recent Activity',
        stats: {
          totalCustomers: 'Total Customers',
          totalClients: 'Total Clients',
          totalProperties: 'Total Properties',
          totalRevenue: 'Total Revenue',
          myCustomers: 'My Customers',
          myProperties: 'My Properties',
          activeMeters: 'Active Meters',
          monthlyRevenue: 'Monthly Revenue',
          creditBalance: 'Credit Balance',
          pendingPayments: 'Pending Payments',
        },
        charts: {
          revenue: 'Revenue Trend',
          meterStatus: 'Meter Status Distribution',
        },
      },
      
      // Validation
      validation: {
        email: {
          required: 'Email is required',
          invalid: 'Please enter a valid email',
        },
        password: {
          required: 'Password is required',
          minLength: 'Password must be at least 6 characters',
        },
        confirmPassword: {
          required: 'Please confirm your password',
          match: 'Passwords must match',
        },
        firstName: {
          required: 'First name is required',
          minLength: 'First name must be at least 2 characters',
        },
        lastName: {
          required: 'Last name is required',
          minLength: 'Last name must be at least 2 characters',
        },
        role: {
          required: 'Role is required',
          invalid: 'Please select a valid role',
        },
      },
    },
  },
  id: {
    translation: {
      // Common
      common: {
        search: 'Cari...',
        profile: 'Profil',
        settings: 'Pengaturan',
        email: 'Email',
        password: 'Kata Sandi',
        confirmPassword: 'Konfirmasi Kata Sandi',
        firstName: 'Nama Depan',
        lastName: 'Nama Belakang',
        role: 'Peran',
        save: 'Simpan',
        cancel: 'Batal',
        delete: 'Hapus',
        edit: 'Edit',
        view: 'Lihat',
        add: 'Tambah',
        create: 'Buat',
        update: 'Perbarui',
        loading: 'Memuat...',
        noData: 'Tidak ada data',
      },
      
      // Navigation
      navigation: {
        dashboard: 'Dasbor',
        users: 'Pengguna',
        clients: 'Klien',
        customers: 'Pelanggan',
        properties: 'Properti',
        meters: 'Meter',
        payments: 'Pembayaran',
        reports: 'Laporan',
        settings: 'Pengaturan',
      },
      
      // Auth
      auth: {
        login: {
          title: 'Masuk ke akun Anda',
          subtitle: 'Selamat datang kembali di IndoWater',
          emailPlaceholder: 'Masukkan email Anda',
          passwordPlaceholder: 'Masukkan kata sandi Anda',
          rememberMe: 'Ingat saya',
          submit: 'Masuk',
          success: 'Login berhasil',
          error: 'Login gagal',
          link: 'Masuk',
        },
        register: {
          title: 'Buat akun Anda',
          subtitle: 'Bergabunglah dengan IndoWater hari ini',
          firstNamePlaceholder: 'Masukkan nama depan Anda',
          lastNamePlaceholder: 'Masukkan nama belakang Anda',
          emailPlaceholder: 'Masukkan email Anda',
          passwordPlaceholder: 'Masukkan kata sandi Anda',
          confirmPasswordPlaceholder: 'Konfirmasi kata sandi Anda',
          submit: 'Buat akun',
          success: 'Registrasi berhasil',
          error: 'Registrasi gagal',
          link: 'Daftar',
        },
        logout: 'Keluar',
        forgotPassword: {
          link: 'Lupa kata sandi?',
        },
      },
      
      // Roles
      roles: {
        superadmin: 'Super Admin',
        client: 'Klien',
        customer: 'Pelanggan',
      },
      
      // Dashboard
      dashboard: {
        title: 'Dasbor',
        welcome: 'Selamat datang kembali, {{name}}!',
        recentActivity: 'Aktivitas Terbaru',
        stats: {
          totalCustomers: 'Total Pelanggan',
          totalClients: 'Total Klien',
          totalProperties: 'Total Properti',
          totalRevenue: 'Total Pendapatan',
          myCustomers: 'Pelanggan Saya',
          myProperties: 'Properti Saya',
          activeMeters: 'Meter Aktif',
          monthlyRevenue: 'Pendapatan Bulanan',
          creditBalance: 'Saldo Kredit',
          pendingPayments: 'Pembayaran Tertunda',
        },
        charts: {
          revenue: 'Tren Pendapatan',
          meterStatus: 'Distribusi Status Meter',
        },
      },
      
      // Validation
      validation: {
        email: {
          required: 'Email wajib diisi',
          invalid: 'Masukkan email yang valid',
        },
        password: {
          required: 'Kata sandi wajib diisi',
          minLength: 'Kata sandi minimal 6 karakter',
        },
        confirmPassword: {
          required: 'Konfirmasi kata sandi wajib diisi',
          match: 'Kata sandi harus sama',
        },
        firstName: {
          required: 'Nama depan wajib diisi',
          minLength: 'Nama depan minimal 2 karakter',
        },
        lastName: {
          required: 'Nama belakang wajib diisi',
          minLength: 'Nama belakang minimal 2 karakter',
        },
        role: {
          required: 'Peran wajib dipilih',
          invalid: 'Pilih peran yang valid',
        },
      },
    },
  },
};

i18n
  .use(LanguageDetector)
  .use(initReactI18next)
  .init({
    resources,
    fallbackLng: 'en',
    debug: process.env.NODE_ENV === 'development',
    
    interpolation: {
      escapeValue: false,
    },
    
    detection: {
      order: ['localStorage', 'navigator', 'htmlTag'],
      caches: ['localStorage'],
    },
  });

export default i18n;