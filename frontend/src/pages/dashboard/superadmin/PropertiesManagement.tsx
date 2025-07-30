import React from 'react';
import { useTranslation } from 'react-i18next';

const PropertiesManagement: React.FC = () => {
  const { t } = useTranslation();

  return (
    <div className="properties-management">
      <h1>{t('dashboard.superadmin.propertiesManagement.title')}</h1>
      
      <div className="page-actions">
        <button className="btn btn-primary">
          {t('dashboard.superadmin.propertiesManagement.addProperty')}
        </button>
      </div>
      
      <div className="search-filters">
        <input 
          type="text" 
          placeholder={t('dashboard.superadmin.propertiesManagement.searchPlaceholder')} 
          className="search-input"
        />
        <select className="filter-select">
          <option value="">{t('dashboard.superadmin.propertiesManagement.filterClient')}</option>
          <option value="all">{t('dashboard.superadmin.propertiesManagement.allClients')}</option>
        </select>
        <select className="filter-select">
          <option value="">{t('dashboard.superadmin.propertiesManagement.filterType')}</option>
          <option value="residential">{t('dashboard.superadmin.propertiesManagement.typeResidential')}</option>
          <option value="commercial">{t('dashboard.superadmin.propertiesManagement.typeCommercial')}</option>
          <option value="industrial">{t('dashboard.superadmin.propertiesManagement.typeIndustrial')}</option>
        </select>
      </div>
      
      <div className="properties-list">
        <div className="placeholder-content">
          <p>{t('common.comingSoon')}</p>
          <p>{t('dashboard.superadmin.propertiesManagement.pendingImplementation')}</p>
        </div>
      </div>
    </div>
  );
};

export default PropertiesManagement;