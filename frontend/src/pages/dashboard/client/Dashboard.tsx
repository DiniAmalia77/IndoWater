import React from 'react';
import { useTranslation } from 'react-i18next';
import { PlaceholderContent } from '../../../components/common';

const ClientDashboard: React.FC = () => {
  const { t } = useTranslation();

  return (
    <div className="client-dashboard">
      <h1 className="text-2xl font-bold mb-6">{t('dashboard.client.title', 'Client Dashboard')}</h1>
      
      <div className="dashboard-stats">
        <div className="stat-card">
          <h3>{t('dashboard.client.totalProperties', 'Total Properties')}</h3>
          <p className="stat-value">0</p>
        </div>
        <div className="stat-card">
          <h3>{t('dashboard.client.totalCustomers', 'Total Customers')}</h3>
          <p className="stat-value">0</p>
        </div>
        <div className="stat-card">
          <h3>{t('dashboard.client.totalMeters', 'Total Meters')}</h3>
          <p className="stat-value">0</p>
        </div>
        <div className="stat-card">
          <h3>{t('dashboard.client.totalRevenue', 'Total Revenue')}</h3>
          <p className="stat-value">Rp 0</p>
        </div>
      </div>
      
      <div className="dashboard-section">
        <h2>{t('dashboard.client.recentActivity', 'Recent Activity')}</h2>
        <PlaceholderContent 
          title={t('dashboard.client.activityTitle', 'Activity Feed Coming Soon')}
          message={t('dashboard.client.activityMessage', 'The activity feed will show recent actions and events related to your properties and customers.')}
        />
      </div>
      
      <div className="dashboard-section">
        <h2>{t('dashboard.client.waterConsumption', 'Water Consumption')}</h2>
        <PlaceholderContent 
          title={t('dashboard.client.consumptionTitle', 'Consumption Charts Coming Soon')}
          message={t('dashboard.client.consumptionMessage', 'Water consumption charts and analytics will be displayed here to help you track usage patterns.')}
        />
      </div>
    </div>
  );
};

export default ClientDashboard;