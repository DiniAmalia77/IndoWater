import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { User } from '../types';
import apiService from '../services/api';

interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  register: (data: any) => Promise<void>;
  logout: () => void;
  refreshToken: () => Promise<void>;
  setUser: (user: User) => void;
  clearAuth: () => void;
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set, get) => ({
      user: null,
      token: null,
      isAuthenticated: false,
      isLoading: false,

      login: async (email: string, password: string) => {
        set({ isLoading: true });
        try {
          const response = await apiService.login({ email, password });
          localStorage.setItem('token', response.token);
          apiService.setAuthToken(response.token);
          set({
            user: response.user,
            token: response.token,
            isAuthenticated: true,
            isLoading: false,
          });
        } catch (error) {
          set({ isLoading: false });
          throw error;
        }
      },

      register: async (data: any) => {
        set({ isLoading: true });
        try {
          const response = await apiService.register(data);
          localStorage.setItem('token', response.token);
          apiService.setAuthToken(response.token);
          set({
            user: response.user,
            token: response.token,
            isAuthenticated: true,
            isLoading: false,
          });
        } catch (error) {
          set({ isLoading: false });
          throw error;
        }
      },

      logout: () => {
        localStorage.removeItem('token');
        apiService.removeAuthToken();
        set({
          user: null,
          token: null,
          isAuthenticated: false,
          isLoading: false,
        });
      },

      refreshToken: async () => {
        try {
          const response = await apiService.refreshToken();
          localStorage.setItem('token', response.token);
          apiService.setAuthToken(response.token);
          set({
            user: response.user,
            token: response.token,
            isAuthenticated: true,
          });
        } catch (error) {
          get().logout();
          throw error;
        }
      },

      setUser: (user: User) => {
        set({ user });
      },

      clearAuth: () => {
        localStorage.removeItem('token');
        apiService.removeAuthToken();
        set({
          user: null,
          token: null,
          isAuthenticated: false,
          isLoading: false,
        });
      },
    }),
    {
      name: 'auth-storage',
      partialize: (state) => ({
        user: state.user,
        token: state.token,
        isAuthenticated: state.isAuthenticated,
      }),
    }
  )
);