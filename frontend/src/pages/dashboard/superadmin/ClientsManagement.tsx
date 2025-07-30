import React from 'react';
import { useTranslation } from 'react-i18next';

const ClientsManagement: React.FC = () => {
  const { t } = useTranslation();

  return (
    <div className="clients-management">
      <h1>{t('dashboard.superadmin.clientsManagement.title')}</h1>
      
      <div className="page-actions">
        <button className="btn btn-primary">
          {t('dashboard.superadmin.clientsManagement.addClient')}
        </button>
      </div>
      
      <div className="search-filters">
        <input 
          type="text" 
          placeholder={t('dashboard.superadmin.clientsManagement.searchPlaceholder')} 
          className="search-input"
        />
        <select className="filter-select">
          <option value="">{t('dashboard.superadmin.clientsManagement.filterStatus')}</option>
          <option value="active">{t('dashboard.superadmin.clientsManagement.statusActive')}</option>
          <option value="inactive">{t('dashboard.superadmin.clientsManagement.statusInactive')}</option>
        </select>
      </div>
      
      <div className="clients-list">
        <div className="placeholder-content">
          <p>{t('common.comingSoon')}</p>
          <p>{t('dashboard.superadmin.clientsManagement.pendingImplementation')}</p>
        </div>
      </div>
    </div>
  );
};

export default ClientsManagement;