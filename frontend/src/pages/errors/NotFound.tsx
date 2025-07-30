import React from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

const NotFound: React.FC = () => {
  const { t } = useTranslation();

  return (
    <div className="error-page not-found">
      <div className="error-container">
        <h1>404</h1>
        <h2>{t('errors.notFound.title')}</h2>
        <p>{t('errors.notFound.message')}</p>
        <Link to="/" className="btn btn-primary">
          {t('errors.backToHome')}
        </Link>
      </div>
    </div>
  );
};

export default NotFound;