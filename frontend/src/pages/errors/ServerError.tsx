import React from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

const ServerError: React.FC = () => {
  const { t } = useTranslation();

  return (
    <div className="error-page server-error">
      <div className="error-container">
        <h1>500</h1>
        <h2>{t('errors.serverError.title')}</h2>
        <p>{t('errors.serverError.message')}</p>
        <Link to="/" className="btn btn-primary">
          {t('errors.backToHome')}
        </Link>
      </div>
    </div>
  );
};

export default ServerError;