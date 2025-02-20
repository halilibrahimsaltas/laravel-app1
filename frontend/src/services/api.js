import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
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

// Interceptor'lar ekleyelim
api.interceptors.request.use(
  (config) => {
    // İstek gönderilmeden önce yapılacak işlemler
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

api.interceptors.response.use(
  (response) => {
    // Başarılı yanıtlar için
    return response;
  },
  (error) => {
    if (error.response) {
      // Sunucu yanıtı ile gelen hatalar
      console.error('API Hatası:', error.response.data);
    } else if (error.request) {
      // İstek yapıldı ama yanıt alınamadı
      console.error('Sunucuya ulaşılamıyor');
    } else {
      // İstek oluşturulurken hata oluştu
      console.error('İstek hatası:', error.message);
    }
    return Promise.reject(error);
  }
);

export default api; 