import React, { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import apiService, { ClientStats, Property, Customer, Payment } from '../../../services/apiService';
import { LoadingSpinner, ErrorMessage } from '../../../components/common';

const ClientDashboard: React.FC = () => {
  const { t } = useTranslation();
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);
  const [stats, setStats] = useState<ClientStats | null>(null);
  const [properties, setProperties] = useState<Property[]>([]);
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [recentPayments, setRecentPayments] = useState<Payment[]>([]);
  const [userId, setUserId] = useState<string>('');

  useEffect(() => {
    const fetchUserData = async () => {
      try {
        const userResponse = await apiService.auth.getUser();
        if (userResponse.success && userResponse.data) {
          setUserId(userResponse.data.id);
          return userResponse.data.id;
        }
        return null;
      } catch (error) {
        console.error('Error fetching user data:', error);
        setError(t('dashboard.error.user', 'Failed to fetch user data'));
        return null;
      }
    };

    const fetchDashboardData = async (clientId: string) => {
      try {
        setLoading(true);
        
        // Fetch client stats
        const statsResponse = await apiService.clients.getStats(clientId);
        if (statsResponse.success && statsResponse.data) {
          setStats(statsResponse.data);
        }
        
        // Fetch properties
        const propertiesResponse = await apiService.properties.getAll({ 
          client_id: clientId,
          limit: 5
        });
        if (propertiesResponse.success && propertiesResponse.data) {
          setProperties(propertiesResponse.data.data);
        }
        
        // Fetch recent customers
        const customersResponse = await apiService.customers.getAll({ 
          client_id: clientId,
          limit: 5
        });
        if (customersResponse.success && customersResponse.data) {
          setCustomers(customersResponse.data.data);
        }
        
        // Fetch recent payments
        const paymentsResponse = await apiService.payments.getAll({ 
          client_id: clientId,
          limit: 5
        });
        if (paymentsResponse.success && paymentsResponse.data) {
          setRecentPayments(paymentsResponse.data.data);
        }
        
        setLoading(false);
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
        setError(t('dashboard.error.data', 'Failed to fetch dashboard data'));
        setLoading(false);
      }
    };

    const initData = async () => {
      const clientId = await fetchUserData();
      if (clientId) {
        await fetchDashboardData(clientId);
      }
    };

    initData();
  }, [t]);

  // Format currency
  const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(amount);
  };

  // Format date
  const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    }).format(date);
  };

  if (loading) {
    return <LoadingSpinner />;
  }

  if (error) {
    return <ErrorMessage message={error} />;
  }

  return (
    <div className="client-dashboard">
      <h1 className="text-2xl font-bold mb-6">{t('dashboard.client.title', 'Client Dashboard')}</h1>
      
      <div className="dashboard-stats grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div className="stat-card bg-white p-4 rounded-lg shadow">
          <h3 className="text-gray-500 text-sm font-medium">{t('dashboard.client.totalProperties', 'Total Properties')}</h3>
          <p className="stat-value text-2xl font-bold">{stats?.total_properties || 0}</p>
          <Link to="/properties" className="text-blue-500 text-sm hover:underline">
            {t('dashboard.client.viewAll', 'View All')}
          </Link>
        </div>
        <div className="stat-card bg-white p-4 rounded-lg shadow">
          <h3 className="text-gray-500 text-sm font-medium">{t('dashboard.client.totalCustomers', 'Total Customers')}</h3>
          <p className="stat-value text-2xl font-bold">{stats?.total_customers || 0}</p>
          <Link to="/customers" className="text-blue-500 text-sm hover:underline">
            {t('dashboard.client.viewAll', 'View All')}
          </Link>
        </div>
        <div className="stat-card bg-white p-4 rounded-lg shadow">
          <h3 className="text-gray-500 text-sm font-medium">{t('dashboard.client.activeMeters', 'Active Meters')}</h3>
          <p className="stat-value text-2xl font-bold">{stats?.active_meters || 0}</p>
          <span className="text-sm text-gray-500">
            {t('dashboard.client.inactiveMeters', 'Inactive')}: {stats?.inactive_meters || 0}
          </span>
        </div>
        <div className="stat-card bg-white p-4 rounded-lg shadow">
          <h3 className="text-gray-500 text-sm font-medium">{t('dashboard.client.totalRevenue', 'Total Revenue')}</h3>
          <p className="stat-value text-2xl font-bold">{formatCurrency(stats?.total_revenue || 0)}</p>
          <span className="text-sm text-gray-500">
            {t('dashboard.client.consumption', 'Consumption')}: {stats?.total_consumption || 0} m³
          </span>
        </div>
      </div>
      
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="dashboard-section bg-white p-4 rounded-lg shadow">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-lg font-semibold">{t('dashboard.client.recentProperties', 'Recent Properties')}</h2>
            <Link to="/properties" className="text-blue-500 text-sm hover:underline">
              {t('dashboard.client.viewAll', 'View All')}
            </Link>
          </div>
          
          {properties.length > 0 ? (
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      {t('properties.name', 'Name')}
                    </th>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      {t('properties.type', 'Type')}
                    </th>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      {t('properties.customers', 'Customers')}
                    </th>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      {t('properties.status', 'Status')}
                    </th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {properties.map((property) => (
                    <tr key={property.id} className="hover:bg-gray-50">
                      <td className="px-4 py-2 whitespace-nowrap">
                        <Link to={`/properties/${property.id}`} className="text-blue-500 hover:underline">
                          {property.name}
                        </Link>
                      </td>
                      <td className="px-4 py-2 whitespace-nowrap capitalize">
                        {property.type}
                      </td>
                      <td className="px-4 py-2 whitespace-nowrap">
                        {property.customers_count || 0}
                      </td>
                      <td className="px-4 py-2 whitespace-nowrap">
                        <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                          property.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        }`}>
                          {property.status}
                        </span>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <p className="text-gray-500 text-center py-4">
              {t('dashboard.client.noProperties', 'No properties found')}
            </p>
          )}
        </div>
        
        <div className="dashboard-section bg-white p-4 rounded-lg shadow">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-lg font-semibold">{t('dashboard.client.recentCustomers', 'Recent Customers')}</h2>
            <Link to="/customers" className="text-blue-500 text-sm hover:underline">
              {t('dashboard.client.viewAll', 'View All')}
            </Link>
          </div>
          
          {customers.length > 0 ? (
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      {t('customers.name', 'Name')}
                    </th>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      {t('customers.property', 'Property')}
                    </th>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      {t('customers.unit', 'Unit')}
                    </th>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      {t('customers.status', 'Status')}
                    </th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {customers.map((customer) => (
                    <tr key={customer.id} className="hover:bg-gray-50">
                      <td className="px-4 py-2 whitespace-nowrap">
                        <Link to={`/customers/${customer.id}`} className="text-blue-500 hover:underline">
                          {customer.name}
                        </Link>
                      </td>
                      <td className="px-4 py-2 whitespace-nowrap">
                        {customer.property?.name || '-'}
                      </td>
                      <td className="px-4 py-2 whitespace-nowrap">
                        {customer.unit}
                      </td>
                      <td className="px-4 py-2 whitespace-nowrap">
                        <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                          customer.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        }`}>
                          {customer.status}
                        </span>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <p className="text-gray-500 text-center py-4">
              {t('dashboard.client.noCustomers', 'No customers found')}
            </p>
          )}
        </div>
      </div>
      
      <div className="dashboard-section bg-white p-4 rounded-lg shadow mt-6">
        <div className="flex justify-between items-center mb-4">
          <h2 className="text-lg font-semibold">{t('dashboard.client.recentPayments', 'Recent Payments')}</h2>
          <Link to="/payments" className="text-blue-500 text-sm hover:underline">
            {t('dashboard.client.viewAll', 'View All')}
          </Link>
        </div>
        
        {recentPayments.length > 0 ? (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {t('payments.invoice', 'Invoice')}
                  </th>
                  <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {t('payments.customer', 'Customer')}
                  </th>
                  <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {t('payments.amount', 'Amount')}
                  </th>
                  <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {t('payments.date', 'Date')}
                  </th>
                  <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {t('payments.status', 'Status')}
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {recentPayments.map((payment) => (
                  <tr key={payment.id} className="hover:bg-gray-50">
                    <td className="px-4 py-2 whitespace-nowrap">
                      <Link to={`/payments/${payment.id}`} className="text-blue-500 hover:underline">
                        {payment.invoice_number}
                      </Link>
                    </td>
                    <td className="px-4 py-2 whitespace-nowrap">
                      {payment.customer?.name || '-'}
                    </td>
                    <td className="px-4 py-2 whitespace-nowrap font-medium">
                      {formatCurrency(payment.amount)}
                    </td>
                    <td className="px-4 py-2 whitespace-nowrap">
                      {payment.payment_date ? formatDate(payment.payment_date) : '-'}
                    </td>
                    <td className="px-4 py-2 whitespace-nowrap">
                      <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                        payment.status === 'completed' ? 'bg-green-100 text-green-800' : 
                        payment.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                        'bg-red-100 text-red-800'
                      }`}>
                        {payment.status}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <p className="text-gray-500 text-center py-4">
            {t('dashboard.client.noPayments', 'No payments found')}
          </p>
        )}
      </div>
      
      {stats?.monthly_stats && stats.monthly_stats.length > 0 && (
        <div className="dashboard-section bg-white p-4 rounded-lg shadow mt-6">
          <h2 className="text-lg font-semibold mb-4">{t('dashboard.client.monthlyStats', 'Monthly Statistics')}</h2>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <h3 className="text-md font-medium mb-2">{t('dashboard.client.consumption', 'Water Consumption (m³)')}</h3>
              <div className="h-64 flex items-end space-x-2">
                {stats.monthly_stats.map((stat, index) => (
                  <div key={index} className="flex flex-col items-center flex-1">
                    <div 
                      className="w-full bg-blue-500 rounded-t" 
                      style={{ 
                        height: `${(stat.consumption / Math.max(...stats.monthly_stats.map(s => s.consumption))) * 100}%`,
                        minHeight: '10%'
                      }}
                    ></div>
                    <span className="text-xs mt-1">{stat.month.substring(0, 3)}</span>
                  </div>
                ))}
              </div>
            </div>
            
            <div>
              <h3 className="text-md font-medium mb-2">{t('dashboard.client.revenue', 'Revenue')}</h3>
              <div className="h-64 flex items-end space-x-2">
                {stats.monthly_stats.map((stat, index) => (
                  <div key={index} className="flex flex-col items-center flex-1">
                    <div 
                      className="w-full bg-green-500 rounded-t" 
                      style={{ 
                        height: `${(stat.revenue / Math.max(...stats.monthly_stats.map(s => s.revenue))) * 100}%`,
                        minHeight: '10%'
                      }}
                    ></div>
                    <span className="text-xs mt-1">{stat.month.substring(0, 3)}</span>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default ClientDashboard;