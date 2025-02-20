import axios from 'axios';

const API_BASE_URL = '/api';

class FinanceService {
    // Altın fiyatlarını getir
    static async getGoldPrices() {
        try {
            const response = await axios.get(`${API_BASE_URL}/finance/gold-price`);
            return response.data;
        } catch (error) {
            console.error('Altın fiyatları alınırken hata:', error);
            throw error;
        }
    }

    // Döviz kurlarını getir
    static async getExchangeRates() {
        try {
            const response = await axios.get(`${API_BASE_URL}/finance/exchange-rate`);
            return response.data;
        } catch (error) {
            console.error('Döviz kurları alınırken hata:', error);
            throw error;
        }
    }

    // Finansal analiz verilerini getir
    static async getFinancialAnalysis(currencyPair) {
        try {
            const [dailyAverage, anomalies, trend] = await Promise.all([
                axios.get(`${API_BASE_URL}/v1/analysis/daily-average/${currencyPair}`),
                axios.get(`${API_BASE_URL}/v1/analysis/anomalies/${currencyPair}`),
                axios.get(`${API_BASE_URL}/v1/analysis/trend/${currencyPair}`)
            ]);

            return {
                dailyAverage: dailyAverage.data,
                anomalies: anomalies.data,
                trend: trend.data
            };
        } catch (error) {
            console.error('Finansal analiz verileri alınırken hata:', error);
            throw error;
        }
    }
}

export default FinanceService; 