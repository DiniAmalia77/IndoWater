import React from 'react';
import { useTranslation } from 'react-i18next';

const ClientCustomers: React.FC = () => {
  const { t } = useTranslation();

  return (
    <div className="client-customers">
      <h1>{t('dashboard.client.customers.title')}</h1>
      
      <div className="page-actions">
        <button className="btn btn-primary">
          {t('dashboard.client.customers.addCustomer')}
        </button>
      </div>
      
      <div className="search-filters">
        <input 
          type="text" 
          placeholder={t('dashboard.client.customers.searchPlaceholder')} 
          className="search-input"
        />
        <select className="filter-select">
          <option value="">{t('dashboard.client.customers.filterProperty')}</option>
          <option value="all">{t('dashboard.client.customers.allProperties')}</option>
        </select>
        <select className="filter-select">
          <option value="">{t('dashboard.client.customers.filterStatus')}</option>
          <option value="active">{t('dashboard.client.customers.statusActive')}</option>
          <option value="inactive">{t('dashboard.client.customers.statusInactive')}</option>
        </select>
      </div>
      
      <div className="customers-list">
        <div className="placeholder-content">
          <p>{t('common.comingSoon')}</p>
          <p>{t('dashboard.client.customers.pendingImplementation')}</p>
        </div>
      </div>
    </div>
  );
};

export default ClientCustomers;