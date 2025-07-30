import React, { useState } from 'react';
import { Outlet, Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useTheme } from '../../contexts/ThemeContext';
import { useAuth } from '../../contexts/AuthContext';

const DashboardLayout: React.FC = () => {
  const { t } = useTranslation();
  const { theme, toggleTheme } = useTheme();
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [sidebarOpen, setSidebarOpen] = useState(true);

  const toggleSidebar = () => {
    setSidebarOpen(!sidebarOpen);
  };

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  // Define navigation links based on user role
  const getNavLinks = () => {
    if (!user) return [];

    switch (user.role) {
      case 'superadmin':
        return [
          { to: '/dashboard/superadmin', label: t('nav.dashboard') },
          { to: '/dashboard/clients', label: t('nav.clients') },
          { to: '/dashboard/properties', label: t('nav.properties') },
          { to: '/dashboard/customers', label: t('nav.customers') },
          { to: '/dashboard/meters', label: t('nav.meters') },
          { to: '/dashboard/payments', label: t('nav.payments') },
          { to: '/dashboard/reports', label: t('nav.reports') },
          { to: '/dashboard/service-fees', label: t('nav.serviceFees') },
          { to: '/dashboard/settings', label: t('nav.settings') },
        ];
      case 'client':
        return [
          { to: '/dashboard/client', label: t('nav.dashboard') },
          { to: '/dashboard/client/properties', label: t('nav.properties') },
          { to: '/dashboard/client/customers', label: t('nav.customers') },
          { to: '/dashboard/client/meters', label: t('nav.meters') },
          { to: '/dashboard/client/payments', label: t('nav.payments') },
          { to: '/dashboard/client/reports', label: t('nav.reports') },
          { to: '/dashboard/client/settings', label: t('nav.settings') },
        ];
      case 'customer':
        return [
          { to: '/dashboard/customer', label: t('nav.dashboard') },
          { to: '/dashboard/customer/meters', label: t('nav.meters') },
          { to: '/dashboard/customer/consumption', label: t('nav.consumption') },
          { to: '/dashboard/customer/payments', label: t('nav.payments') },
          { to: '/dashboard/customer/topup', label: t('nav.topup') },
          { to: '/dashboard/customer/profile', label: t('nav.profile') },
        ];
      default:
        return [];
    }
  };

  return (
    <div className={`dashboard-layout ${theme}`}>
      {/* Sidebar */}
      <aside className={`sidebar ${sidebarOpen ? 'open' : 'closed'}`}>
        <div className="sidebar-header">
          <h2>IndoWater</h2>
          <button onClick={toggleSidebar} className="sidebar-toggle">
            {sidebarOpen ? '◀' : '▶'}
          </button>
        </div>
        <nav className="sidebar-nav">
          <ul>
            {getNavLinks().map((link, index) => (
              <li key={index}>
                <Link to={link.to}>{link.label}</Link>
              </li>
            ))}
          </ul>
        </nav>
      </aside>

      {/* Main Content */}
      <main className="dashboard-main">
        {/* Header */}
        <header className="dashboard-header">
          <div className="header-left">
            <button onClick={toggleSidebar} className="mobile-sidebar-toggle">
              ☰
            </button>
          </div>
          <div className="header-right">
            <button 
              onClick={toggleTheme} 
              className="theme-toggle"
              aria-label="Toggle theme"
            >
              {theme === 'dark' ? '☀️' : '🌙'}
            </button>
            <div className="user-menu">
              <span>{user?.name}</span>
              <button onClick={handleLogout} className="logout-button">
                {t('auth.logout')}
              </button>
            </div>
          </div>
        </header>

        {/* Content */}
        <div className="dashboard-content">
          <Outlet />
        </div>

        {/* Footer */}
        <footer className="dashboard-footer">
          <p>© {new Date().getFullYear()} IndoWater. All rights reserved.</p>
        </footer>
      </main>
    </div>
  );
};

export default DashboardLayout;