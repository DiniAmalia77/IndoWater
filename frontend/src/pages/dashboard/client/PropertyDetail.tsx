import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import apiService, { Property, Customer } from '../../../services/apiService';
import { LoadingSpinner, ErrorMessage } from '../../../components/common';

interface PropertyParams {
  id: string;
}

const PropertyDetail: React.FC = () => {
  const { t } = useTranslation();
  const { id } = useParams<PropertyParams>();
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);
  const [property, setProperty] = useState<Property | null>(null);
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [activeTab, setActiveTab] = useState<'overview' | 'customers' | 'meters' | 'stats'>('overview');

  useEffect(() => {
    const fetchPropertyData = async () => {
      try {
        setLoading(true);
        
        // Fetch property details
        const propertyResponse = await apiService.properties.getById(id);
        
        if (propertyResponse.success && propertyResponse.data) {
          setProperty(propertyResponse.data);
          
          // Fetch customers for this property
          const customersResponse = await apiService.customers.getAll({ 
            property_id: id,
            limit: 100 // Get all customers for this property
          });
          
          if (customersResponse.success && customersResponse.data) {
            setCustomers(customersResponse.data.data);
          }
        } else {
          setError(propertyResponse.message || t('propertyDetail.error.fetch', 'Failed to fetch property details'));
        }
        
        setLoading(false);
      } catch (error) {
        console.error('Error fetching property data:', error);
        setError(t('propertyDetail.error.fetch', 'Failed to fetch property details'));
        setLoading(false);
      }
    };

    fetchPropertyData();
  }, [id, t]);

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
      month: 'long',
      day: 'numeric'
    }).format(date);
  };

  if (loading) {
    return <LoadingSpinner />;
  }

  if (error) {
    return <ErrorMessage message={error} />;
  }

  if (!property) {
    return <ErrorMessage message={t('propertyDetail.error.notFound', 'Property not found')} />;
  }

  return (
    <div className="property-detail">
      <div className="mb-6">
        <Link to="/properties" className="text-blue-500 hover:underline flex items-center">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            className="h-5 w-5 mr-1"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fillRule="evenodd"
              d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
              clipRule="evenodd"
            />
          </svg>
          {t('propertyDetail.backToProperties', 'Back to Properties')}
        </Link>
      </div>

      <div className="flex justify-between items-center mb-6">
        <div>
          <h1 className="text-2xl font-bold">{property.name}</h1>
          <p className="text-gray-500">{property.address}</p>
        </div>
        <div className="flex space-x-2">
          <Link
            to={`/properties/${property.id}/edit`}
            className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg"
          >
            {t('propertyDetail.edit', 'Edit Property')}
          </Link>
          <Link
            to={`/properties/${property.id}/customers/add`}
            className="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg"
          >
            {t('propertyDetail.addCustomer', 'Add Customer')}
          </Link>
        </div>
      </div>

      <div className="bg-white rounded-lg shadow mb-6">
        <div className="border-b border-gray-200">
          <nav className="flex -mb-px">
            <button
              onClick={() => setActiveTab('overview')}
              className={`py-4 px-6 text-center border-b-2 font-medium text-sm ${
                activeTab === 'overview'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              }`}
            >
              {t('propertyDetail.tabs.overview', 'Overview')}
            </button>
            <button
              onClick={() => setActiveTab('customers')}
              className={`py-4 px-6 text-center border-b-2 font-medium text-sm ${
                activeTab === 'customers'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              }`}
            >
              {t('propertyDetail.tabs.customers', 'Customers')} ({customers.length})
            </button>
            <button
              onClick={() => setActiveTab('meters')}
              className={`py-4 px-6 text-center border-b-2 font-medium text-sm ${
                activeTab === 'meters'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              }`}
            >
              {t('propertyDetail.tabs.meters', 'Meters')} ({property.meters_count || 0})
            </button>
            <button
              onClick={() => setActiveTab('stats')}
              className={`py-4 px-6 text-center border-b-2 font-medium text-sm ${
                activeTab === 'stats'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              }`}
            >
              {t('propertyDetail.tabs.stats', 'Statistics')}
            </button>
          </nav>
        </div>

        <div className="p-6">
          {activeTab === 'overview' && (
            <div className="overview-tab">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <h2 className="text-lg font-semibold mb-4">{t('propertyDetail.propertyInfo', 'Property Information')}</h2>
                  <div className="bg-gray-50 p-4 rounded-lg">
                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <p className="text-sm text-gray-500">{t('propertyDetail.type', 'Type')}</p>
                        <p className="font-medium capitalize">{property.type}</p>
                      </div>
                      <div>
                        <p className="text-sm text-gray-500">{t('propertyDetail.status', 'Status')}</p>
                        <p className="font-medium">
                          <span
                            className={`px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${
                              property.status === 'active'
                                ? 'bg-green-100 text-green-800'
                                : 'bg-red-100 text-red-800'
                            }`}
                          >
                            {property.status}
                          </span>
                        </p>
                      </div>
                      <div>
                        <p className="text-sm text-gray-500">{t('propertyDetail.createdAt', 'Created At')}</p>
                        <p className="font-medium">{formatDate(property.created_at)}</p>
                      </div>
                      <div>
                        <p className="text-sm text-gray-500">{t('propertyDetail.customersCount', 'Customers')}</p>
                        <p className="font-medium">{property.customers_count || 0}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <div>
                  <h2 className="text-lg font-semibold mb-4">{t('propertyDetail.quickStats', 'Quick Stats')}</h2>
                  <div className="grid grid-cols-2 gap-4">
                    <div className="bg-blue-50 p-4 rounded-lg">
                      <p className="text-sm text-blue-500">{t('propertyDetail.activeMeters', 'Active Meters')}</p>
                      <p className="text-2xl font-bold text-blue-700">{property.stats?.active_meters || 0}</p>
                    </div>
                    <div className="bg-red-50 p-4 rounded-lg">
                      <p className="text-sm text-red-500">{t('propertyDetail.inactiveMeters', 'Inactive Meters')}</p>
                      <p className="text-2xl font-bold text-red-700">{property.stats?.inactive_meters || 0}</p>
                    </div>
                    <div className="bg-green-50 p-4 rounded-lg">
                      <p className="text-sm text-green-500">{t('propertyDetail.totalConsumption', 'Total Consumption')}</p>
                      <p className="text-2xl font-bold text-green-700">{property.stats?.total_consumption || 0} m³</p>
                    </div>
                    <div className="bg-purple-50 p-4 rounded-lg">
                      <p className="text-sm text-purple-500">{t('propertyDetail.totalRevenue', 'Total Revenue')}</p>
                      <p className="text-2xl font-bold text-purple-700">{formatCurrency(property.stats?.total_revenue || 0)}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          )}

          {activeTab === 'customers' && (
            <div className="customers-tab">
              <div className="flex justify-between items-center mb-4">
                <h2 className="text-lg font-semibold">{t('propertyDetail.customers', 'Customers')}</h2>
                <Link
                  to={`/properties/${property.id}/customers/add`}
                  className="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg text-sm"
                >
                  {t('propertyDetail.addCustomer', 'Add Customer')}
                </Link>
              </div>

              {customers.length > 0 ? (
                <div className="overflow-x-auto">
                  <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                      <tr>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          {t('propertyDetail.customerName', 'Name')}
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          {t('propertyDetail.customerUnit', 'Unit')}
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          {t('propertyDetail.customerEmail', 'Email')}
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          {t('propertyDetail.customerPhone', 'Phone')}
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          {t('propertyDetail.customerStatus', 'Status')}
                        </th>
                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                          {t('propertyDetail.actions', 'Actions')}
                        </th>
                      </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                      {customers.map((customer) => (
                        <tr key={customer.id} className="hover:bg-gray-50">
                          <td className="px-6 py-4 whitespace-nowrap">
                            <Link
                              to={`/customers/${customer.id}`}
                              className="text-blue-500 hover:underline font-medium"
                            >
                              {customer.name}
                            </Link>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap">
                            {customer.unit}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap">
                            <a href={`mailto:${customer.email}`} className="text-gray-500 hover:text-gray-700">
                              {customer.email}
                            </a>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap">
                            <a href={`tel:${customer.phone}`} className="text-gray-500 hover:text-gray-700">
                              {customer.phone}
                            </a>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap">
                            <span
                              className={`px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${
                                customer.status === 'active'
                                  ? 'bg-green-100 text-green-800'
                                  : 'bg-red-100 text-red-800'
                              }`}
                            >
                              {customer.status}
                            </span>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <Link
                              to={`/customers/${customer.id}`}
                              className="text-blue-500 hover:text-blue-700 mr-4"
                            >
                              {t('propertyDetail.view', 'View')}
                            </Link>
                            <Link
                              to={`/customers/${customer.id}/edit`}
                              className="text-indigo-500 hover:text-indigo-700 mr-4"
                            >
                              {t('propertyDetail.edit', 'Edit')}
                            </Link>
                            <Link
                              to={`/meters/${customer.meter_id}`}
                              className="text-green-500 hover:text-green-700"
                            >
                              {t('propertyDetail.meter', 'Meter')}
                            </Link>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              ) : (
                <div className="bg-gray-50 p-8 text-center rounded-lg">
                  <svg
                    className="mx-auto h-12 w-12 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    aria-hidden="true"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={1}
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                    />
                  </svg>
                  <h3 className="mt-2 text-lg font-medium text-gray-900">
                    {t('propertyDetail.noCustomers', 'No customers found')}
                  </h3>
                  <p className="mt-1 text-sm text-gray-500">
                    {t(
                      'propertyDetail.noCustomersMessage',
                      'Get started by adding a new customer to this property.'
                    )}
                  </p>
                  <div className="mt-6">
                    <Link
                      to={`/properties/${property.id}/customers/add`}
                      className="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                      <svg
                        className="-ml-1 mr-2 h-5 w-5"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                      >
                        <path
                          fillRule="evenodd"
                          d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                          clipRule="evenodd"
                        />
                      </svg>
                      {t('propertyDetail.addCustomer', 'Add Customer')}
                    </Link>
                  </div>
                </div>
              )}
            </div>
          )}

          {activeTab === 'meters' && (
            <div className="meters-tab">
              <div className="flex justify-between items-center mb-4">
                <h2 className="text-lg font-semibold">{t('propertyDetail.meters', 'Meters')}</h2>
                <Link
                  to={`/properties/${property.id}/meters/add`}
                  className="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg text-sm"
                >
                  {t('propertyDetail.addMeter', 'Add Meter')}
                </Link>
              </div>

              {/* Placeholder for meters list - will be implemented in the future */}
              <div className="bg-gray-50 p-8 text-center rounded-lg">
                <svg
                  className="mx-auto h-12 w-12 text-gray-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  aria-hidden="true"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={1}
                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                  />
                </svg>
                <h3 className="mt-2 text-lg font-medium text-gray-900">
                  {t('propertyDetail.metersComingSoon', 'Meters management coming soon')}
                </h3>
                <p className="mt-1 text-sm text-gray-500">
                  {t(
                    'propertyDetail.metersComingSoonMessage',
                    'The meters management feature is currently under development and will be available soon.'
                  )}
                </p>
              </div>
            </div>
          )}

          {activeTab === 'stats' && (
            <div className="stats-tab">
              <h2 className="text-lg font-semibold mb-4">{t('propertyDetail.statistics', 'Statistics')}</h2>
              
              {property.stats?.monthly_stats && property.stats.monthly_stats.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <h3 className="text-md font-medium mb-2">{t('propertyDetail.monthlyConsumption', 'Monthly Consumption (m³)')}</h3>
                    <div className="bg-white p-4 rounded-lg border">
                      <div className="h-64 flex items-end space-x-2">
                        {property.stats.monthly_stats.map((stat, index) => (
                          <div key={index} className="flex flex-col items-center flex-1">
                            <div 
                              className="w-full bg-blue-500 rounded-t" 
                              style={{ 
                                height: `${(stat.consumption / Math.max(...property.stats!.monthly_stats!.map(s => s.consumption))) * 100}%`,
                                minHeight: '10%'
                              }}
                            ></div>
                            <span className="text-xs mt-1">{stat.month.substring(0, 3)}</span>
                          </div>
                        ))}
                      </div>
                    </div>
                  </div>
                  
                  <div>
                    <h3 className="text-md font-medium mb-2">{t('propertyDetail.monthlyRevenue', 'Monthly Revenue')}</h3>
                    <div className="bg-white p-4 rounded-lg border">
                      <div className="h-64 flex items-end space-x-2">
                        {property.stats.monthly_stats.map((stat, index) => (
                          <div key={index} className="flex flex-col items-center flex-1">
                            <div 
                              className="w-full bg-green-500 rounded-t" 
                              style={{ 
                                height: `${(stat.revenue / Math.max(...property.stats!.monthly_stats!.map(s => s.revenue))) * 100}%`,
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
              ) : (
                <div className="bg-gray-50 p-8 text-center rounded-lg">
                  <svg
                    className="mx-auto h-12 w-12 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    aria-hidden="true"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={1}
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                    />
                  </svg>
                  <h3 className="mt-2 text-lg font-medium text-gray-900">
                    {t('propertyDetail.noStats', 'No statistics available')}
                  </h3>
                  <p className="mt-1 text-sm text-gray-500">
                    {t(
                      'propertyDetail.noStatsMessage',
                      'Statistics will be available once customers start using water meters.'
                    )}
                  </p>
                </div>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default PropertyDetail;