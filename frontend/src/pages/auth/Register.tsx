import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useAuth } from '../../contexts/AuthContext';

const Register: React.FC = () => {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { register, error: authError, isLoading } = useAuth();
  
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [error, setError] = useState('');

  // Set error from auth context
  React.useEffect(() => {
    if (authError) {
      setError(authError);
    }
  }, [authError]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    
    // Validate password match
    if (password !== passwordConfirmation) {
      setError(t('auth.register.passwordMismatch'));
      return;
    }

    try {
      // Call register function from AuthContext
      await register({
        name,
        email,
        password,
        password_confirmation: passwordConfirmation
      });
      
      // Navigation is handled in the AuthContext after successful registration
    } catch (err: any) {
      // Error handling is done in the AuthContext
      console.error('Registration submission error:', err);
    }
  };

  return (
    <div className="register-page">
      <div className="register-container">
        <h2>{t('auth.register.title')}</h2>
        
        {error && <div className="alert alert-danger">{error}</div>}
        
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label htmlFor="name">{t('auth.register.name')}</label>
            <input
              type="text"
              id="name"
              value={name}
              onChange={(e) => setName(e.target.value)}
              required
              autoFocus
              disabled={isLoading}
            />
          </div>
          
          <div className="form-group">
            <label htmlFor="email">{t('auth.register.email')}</label>
            <input
              type="email"
              id="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              disabled={isLoading}
            />
          </div>
          
          <div className="form-group">
            <label htmlFor="password">{t('auth.register.password')}</label>
            <input
              type="password"
              id="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              minLength={8}
              disabled={isLoading}
            />
          </div>
          
          <div className="form-group">
            <label htmlFor="passwordConfirmation">{t('auth.register.passwordConfirmation')}</label>
            <input
              type="password"
              id="passwordConfirmation"
              value={passwordConfirmation}
              onChange={(e) => setPasswordConfirmation(e.target.value)}
              required
              minLength={8}
              disabled={isLoading}
            />
          </div>
          
          <div className="form-group">
            <button 
              type="submit" 
              className="btn btn-primary btn-block"
              disabled={isLoading}
            >
              {isLoading ? t('auth.register.registering') : t('auth.register.submit')}
            </button>
          </div>
        </form>
        
        <div className="auth-links">
          <p>
            {t('auth.register.alreadyHaveAccount')} <Link to="/login">{t('auth.register.login')}</Link>
          </p>
        </div>
      </div>
    </div>
  );
};

export default Register;