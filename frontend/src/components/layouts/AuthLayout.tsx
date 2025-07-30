import React from 'react';
import { Outlet } from 'react-router-dom';
import { useTheme } from '../../contexts/ThemeContext';

const AuthLayout: React.FC = () => {
  const { theme, toggleTheme } = useTheme();

  return (
    <div className={`auth-layout ${theme}`}>
      <div className="auth-container">
        <div className="auth-header">
          <div className="logo">
            <h1>IndoWater</h1>
          </div>
          <button 
            onClick={toggleTheme} 
            className="theme-toggle"
            aria-label="Toggle theme"
          >
            {theme === 'dark' ? '☀️' : '🌙'}
          </button>
        </div>
        <div className="auth-content">
          <Outlet />
        </div>
        <div className="auth-footer">
          <p>© {new Date().getFullYear()} IndoWater. All rights reserved.</p>
        </div>
      </div>
    </div>
  );
};

export default AuthLayout;