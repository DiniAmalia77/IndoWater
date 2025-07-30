import React, { useState } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useAuth } from '../../contexts/AuthContext';

const Login: React.FC = () => {
  const { t } = useTranslation();
  const { login, error: authError, isLoading } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [rememberMe, setRememberMe] = useState(false);
  const [error, setError] = useState('');
  const [message, setMessage] = useState<string | null>(null);

  // Check for message in location state (e.g., from registration or password reset)
  React.useEffect(() => {
    if (location.state && location.state.message) {
      setMessage(location.state.message);
      
      // Clear the location state after displaying the message
      window.history.replaceState({}, document.title);
    }
  }, [location]);

  // Set error from auth context
  React.useEffect(() => {
    if (authError) {
      setError(authError);
    }
  }, [authError]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setMessage(null);
    
    try {
      // Call login function from AuthContext
      await login(email, password, rememberMe);
      
      // Note: Navigation is handled in the AuthContext after successful login
    } catch (err: any) {
      // Error handling is done in the AuthContext
      console.error('Login submission error:', err);
    }
  };

  return (
    <div className="login-page">
      <div className="login-container">
        <h2>{t('auth.login.title')}</h2>
        
        {message && <div className="alert alert-success">{message}</div>}
        {error && <div className="alert alert-danger">{error}</div>}
        
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label htmlFor="email">{t('auth.login.email')}</label>
            <input
              type="email"
              id="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              autoFocus
              disabled={isLoading}
            />
          </div>
          
          <div className="form-group">
            <label htmlFor="password">{t('auth.login.password')}</label>
            <input
              type="password"
              id="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              disabled={isLoading}
            />
          </div>
          
          <div className="form-group remember-me">
            <label>
              <input
                type="checkbox"
                checked={rememberMe}
                onChange={(e) => setRememberMe(e.target.checked)}
                disabled={isLoading}
              />
              {t('auth.login.rememberMe')}
            </label>
          </div>
          
          <div className="form-group">
            <button 
              type="submit" 
              className="btn btn-primary btn-block"
              disabled={isLoading}
            >
              {isLoading ? t('auth.login.loggingIn') : t('auth.login.submit')}
            </button>
          </div>
        </form>
        
        <div className="auth-links">
          <Link to="/forgot-password">{t('auth.login.forgotPassword')}</Link>
          <Link to="/register">{t('auth.login.register')}</Link>
        </div>
      </div>
    </div>
  );
};

export default Login;