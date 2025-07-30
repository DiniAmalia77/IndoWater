import React from 'react';
import { useTranslation } from 'react-i18next';
import { PlaceholderContent } from '../../../components/common';

const CustomerDashboard: React.FC = () => {
  const { t } = useTranslation();

  return (
    <div className="customer-dashboard">
      <h1 className="text-2xl font-bold mb-6">{t('dashboard.customer.title', 'Customer Dashboard')}</h1>
      
      <div className="dashboard-stats">
        <div className="stat-card">
          <h3>{t('dashboard.customer.currentBalance', 'Current Balance')}</h3>
          <p className="stat-value">Rp 0</p>
        </div>
        <div className="stat-card">
          <h3>{t('dashboard.customer.totalMeters', 'Total Meters')}</h3>
          <p className="stat-value">0</p>
        </div>
        <div className="stat-card">
          <h3>{t('dashboard.customer.lastPayment', 'Last Payment')}</h3>
          <p className="stat-value">-</p>
        </div>
        <div className="stat-card">
          <h3>{t('dashboard.customer.currentUsage', 'Current Usage')}</h3>
          <p className="stat-value">0 m³</p>
        </div>
      </div>
      
      <div className="dashboard-section">
        <h2>{t('dashboard.customer.consumptionHistory', 'Consumption History')}</h2>
        <PlaceholderContent 
          title={t('dashboard.customer.consumptionTitle', 'Consumption History Coming Soon')}
          message={t('dashboard.customer.consumptionMessage', 'Your water consumption history will be displayed here with charts and detailed usage data.')}
        />
      </div>
      
      <div className="dashboard-section">
        <h2>{t('dashboard.customer.paymentHistory', 'Payment History')}</h2>
        <PlaceholderContent 
          title={t('dashboard.customer.paymentTitle', 'Payment History Coming Soon')}
          message={t('dashboard.customer.paymentMessage', 'Your payment history will be displayed here with transaction details and receipts.')}
        />
      </div>
      
      <div className="dashboard-section">
        <h2>{t('dashboard.customer.quickActions', 'Quick Actions')}</h2>
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
          <PlaceholderContent 
            title={t('dashboard.customer.topUpTitle', 'Top Up Credit')}
            message={t('dashboard.customer.topUpMessage', 'Add credit to your account to pay for water usage.')}
          />
          <PlaceholderContent 
            title={t('dashboard.customer.reportIssueTitle', 'Report Issue')}
            message={t('dashboard.customer.reportIssueMessage', 'Report problems with your water meter or service.')}
          />
          <PlaceholderContent 
            title={t('dashboard.customer.viewBillTitle', 'View Current Bill')}
            message={t('dashboard.customer.viewBillMessage', 'View your current bill and payment status.')}
          />
        </div>
      </div>
    </div>
  );
};

export default CustomerDashboard;