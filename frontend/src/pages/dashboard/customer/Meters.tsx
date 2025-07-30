import React from 'react';
import { useTranslation } from 'react-i18next';

const CustomerMeters: React.FC = () => {
  const { t } = useTranslation();

  return (
    <div className="customer-meters">
      <h1>{t('dashboard.customer.meters.title')}</h1>
      
      <div className="meters-list">
        <div className="placeholder-content">
          <p>{t('common.comingSoon')}</p>
          <p>{t('dashboard.customer.meters.pendingImplementation')}</p>
        </div>
      </div>
    </div>
  );
};

export default CustomerMeters;