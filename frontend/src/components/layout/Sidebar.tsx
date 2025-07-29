import React from 'react';
import { NavLink, useLocation } from 'react-router-dom';
import {
  HomeIcon,
  UsersIcon,
  BuildingOfficeIcon,
  UserGroupIcon,
  HomeModernIcon,
  CpuChipIcon,
  CreditCardIcon,
  ChartBarIcon,
  Cog6ToothIcon,
} from '@heroicons/react/24/outline';
import { useAppStore } from '../../store/appStore';
import { useAuthStore } from '../../store/authStore';
import { useTranslation } from 'react-i18next';

interface MenuItem {
  name: string;
  href: string;
  icon: React.ComponentType<{ className?: string }>;
  roles?: string[];
}

const Sidebar: React.FC = () => {
  const { sidebarOpen } = useAppStore();
  const { user } = useAuthStore();
  const { t } = useTranslation();
  const location = useLocation();

  const menuItems: MenuItem[] = [
    {
      name: t('navigation.dashboard'),
      href: '/dashboard',
      icon: HomeIcon,
    },
    {
      name: t('navigation.users'),
      href: '/users',
      icon: UsersIcon,
      roles: ['superadmin'],
    },
    {
      name: t('navigation.clients'),
      href: '/clients',
      icon: BuildingOfficeIcon,
      roles: ['superadmin'],
    },
    {
      name: t('navigation.customers'),
      href: '/customers',
      icon: UserGroupIcon,
      roles: ['superadmin', 'client'],
    },
    {
      name: t('navigation.properties'),
      href: '/properties',
      icon: HomeModernIcon,
      roles: ['superadmin', 'client'],
    },
    {
      name: t('navigation.meters'),
      href: '/meters',
      icon: CpuChipIcon,
      roles: ['superadmin', 'client'],
    },
    {
      name: t('navigation.payments'),
      href: '/payments',
      icon: CreditCardIcon,
    },
    {
      name: t('navigation.reports'),
      href: '/reports',
      icon: ChartBarIcon,
      roles: ['superadmin', 'client'],
    },
    {
      name: t('navigation.settings'),
      href: '/settings',
      icon: Cog6ToothIcon,
    },
  ];

  const filteredMenuItems = menuItems.filter(item => 
    !item.roles || item.roles.includes(user?.role || '')
  );

  const isActive = (href: string) => {
    return location.pathname === href || location.pathname.startsWith(href + '/');
  };

  return (
    <div className={`fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 shadow-lg transform transition-transform duration-300 ease-in-out ${
      sidebarOpen ? 'translate-x-0' : '-translate-x-48'
    }`}>
      <div className="flex items-center justify-center h-16 px-4 bg-blue-600 dark:bg-blue-700">
        <div className="flex items-center space-x-2">
          <div className="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
            <span className="text-blue-600 font-bold text-lg">IW</span>
          </div>
          {sidebarOpen && (
            <span className="text-white font-semibold text-lg">IndoWater</span>
          )}
        </div>
      </div>

      <nav className="mt-8">
        <div className="px-4 space-y-2">
          {filteredMenuItems.map((item) => (
            <NavLink
              key={item.name}
              to={item.href}
              className={`group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 ${
                isActive(item.href)
                  ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white'
              }`}
            >
              <item.icon
                className={`mr-3 h-5 w-5 flex-shrink-0 ${
                  isActive(item.href)
                    ? 'text-blue-500 dark:text-blue-300'
                    : 'text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300'
                }`}
              />
              {sidebarOpen && item.name}
            </NavLink>
          ))}
        </div>
      </nav>

      {/* User info at bottom */}
      {sidebarOpen && user && (
        <div className="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200 dark:border-gray-700">
          <div className="flex items-center space-x-3">
            <div className="w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center">
              <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
                {user.firstName?.[0]}{user.lastName?.[0]}
              </span>
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-sm font-medium text-gray-900 dark:text-white truncate">
                {user.firstName} {user.lastName}
              </p>
              <p className="text-xs text-gray-500 dark:text-gray-400 truncate capitalize">
                {user.role}
              </p>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default Sidebar;