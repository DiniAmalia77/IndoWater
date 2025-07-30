import React from 'react';
import { useTranslation } from 'react-i18next';

const ClientProperties: React.FC = () => {
  const { t } = useTranslation();

  return (
    <div className="client-properties">
      <h1>{t('dashboard.client.properties.title')}</h1>
      
      <div className="search-filters">
        <input 
          type="text" 
          placeholder={t('dashboard.client.properties.searchPlaceholder')} 
          className="search-input"
        />
        <select className="filter-select">
          <option value="">{t('dashboard.client.properties.filterType')}</option>
          <option value="residential">{t('dashboard.client.properties.typeResidential')}</option>
          <option value="commercial">{t('dashboard.client.properties.typeCommercial')}</option>
          <option value="industrial">{t('dashboard.client.properties.typeIndustrial')}</option>
        </select>
      </div>
      
      <div className="properties-list">
        <div className="placeholder-content">
          <p>{t('common.comingSoon')}</p>
          <p>{t('dashboard.client.properties.pendingImplementation')}</p>
        </div>
      </div>
    </div>
  );
};

export default ClientProperties;