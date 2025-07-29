import React from 'react';
import { useTranslation } from 'react-i18next';

const CustomerConsumption: React.FC = () => {
  const { t } = useTranslation();

  return (
    <div className="customer-consumption">
      <h1>{t('dashboard.customer.consumption.title')}</h1>
      
      <div className="filter-controls">
        <select className="filter-select">
          <option value="daily">{t('dashboard.customer.consumption.filterDaily')}</option>
          <option value="weekly">{t('dashboard.customer.consumption.filterWeekly')}</option>
          <option value="monthly">{t('dashboard.customer.consumption.filterMonthly')}</option>
          <option value="yearly">{t('dashboard.customer.consumption.filterYearly')}</option>
        </select>
        
        <select className="filter-select">
          <option value="">{t('dashboard.customer.consumption.filterMeter')}</option>
          <option value="all">{t('dashboard.customer.consumption.allMeters')}</option>
        </select>
      </div>
      
      <div className="consumption-chart">
        <div className="placeholder-content">
          <p>{t('common.comingSoon')}</p>
          <p>{t('dashboard.customer.consumption.pendingImplementation')}</p>
        </div>
      </div>
      
      <div className="consumption-table">
        <div className="placeholder-content">
          <p>{t('common.comingSoon')}</p>
          <p>{t('dashboard.customer.consumption.pendingImplementation')}</p>
        </div>
      </div>
    </div>
  );
};

export default CustomerConsumption;