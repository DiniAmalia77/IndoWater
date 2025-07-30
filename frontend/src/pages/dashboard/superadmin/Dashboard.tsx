import React from 'react';
import { useTranslation } from 'react-i18next';
import { PlaceholderContent } from '../../../components/common';

const SuperadminDashboard: React.FC = () => {
  const { t } = useTranslation();

  return (
    <div className="superadmin-dashboard">
      <h1 className="text-2xl font-bold mb-6">{t('dashboard.superadmin.title', 'Superadmin Dashboard')}</h1>
      
      <div className="dashboard-stats">
        <div className="stat-card">
          <h3>{t('dashboard.superadmin.totalClients', 'Total Clients')}</h3>
          <p className="stat-value">0</p>
        </div>
        <div className="stat-card">
          <h3>{t('dashboard.superadmin.totalProperties', 'Total Properties')}</h3>
          <p className="stat-value">0</p>
        </div>
        <div className="stat-card">
          <h3>{t('dashboard.superadmin.totalCustomers', 'Total Customers')}</h3>
          <p className="stat-value">0</p>
        </div>
        <div className="stat-card">
          <h3>{t('dashboard.superadmin.totalMeters', 'Total Meters')}</h3>
          <p className="stat-value">0</p>
        </div>
      </div>
      
      <div className="dashboard-section">
        <h2>{t('dashboard.superadmin.recentActivity', 'Recent Activity')}</h2>
        <PlaceholderContent 
          title={t('dashboard.superadmin.activityTitle', 'Activity Feed Coming Soon')}
          message={t('dashboard.superadmin.activityMessage', 'The activity feed will show recent actions and events across the platform.')}
        />
      </div>
      
      <div className="dashboard-section">
        <h2>{t('dashboard.superadmin.revenueOverview', 'Revenue Overview')}</h2>
        <PlaceholderContent 
          title={t('dashboard.superadmin.revenueTitle', 'Revenue Charts Coming Soon')}
          message={t('dashboard.superadmin.revenueMessage', 'Revenue charts and analytics will be displayed here to help you track financial performance.')}
        />
      </div>
    </div>
  );
};

export default SuperadminDashboard;