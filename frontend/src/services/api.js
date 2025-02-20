import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
  },
});

export const getCurrencyData = async (pair) => {
  try {
    const response = await api.get(`/currency/${pair}`);
    return response.data;
  } catch (error) {
    console.error('Döviz verisi alınırken hata oluştu:', error);
    throw error;
  }
};

export const getGoldData = async (currency) => {
  try {
    const response = await api.get(`/gold/${currency}`);
    return response.data;
  } catch (error) {
    console.error('Altın verisi alınırken hata oluştu:', error);
    throw error;
  }
};

export const getDashboardData = async () => {
  try {
    const response = await api.get('/dashboard');
    return response.data;
  } catch (error) {
    console.error('Dashboard verisi alınırken hata oluştu:', error);
    throw error;
  }
};

export default api; 