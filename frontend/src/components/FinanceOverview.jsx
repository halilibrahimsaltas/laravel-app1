import React, { useState, useEffect } from 'react';
import FinanceService from '../services/FinanceService';

const FinanceOverview = () => {
    const [goldData, setGoldData] = useState(null);
    const [exchangeRates, setExchangeRates] = useState(null);
    const [analysis, setAnalysis] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchFinancialData = async () => {
            try {
                setLoading(true);
                const [goldResponse, ratesResponse, analysisResponse] = await Promise.all([
                    FinanceService.getGoldPrices(),
                    FinanceService.getExchangeRates(),
                    FinanceService.getFinancialAnalysis('USD/TRY')
                ]);

                setGoldData(goldResponse);
                setExchangeRates(ratesResponse);
                setAnalysis(analysisResponse);
            } catch (err) {
                setError('Veriler yüklenirken bir hata oluştu');
                console.error(err);
            } finally {
                setLoading(false);
            }
        };

        fetchFinancialData();
        // Her 5 dakikada bir verileri güncelle
        const interval = setInterval(fetchFinancialData, 300000);
        return () => clearInterval(interval);
    }, []);

    if (loading) return <div className="text-center p-4">Yükleniyor...</div>;
    if (error) return <div className="text-red-500 p-4">{error}</div>;

    return (
        <div className="p-4">
            <h2 className="text-2xl font-bold mb-4">Finansal Genel Bakış</h2>
            
            {/* Altın Fiyatları */}
            <div className="mb-6 bg-white p-4 rounded-lg shadow">
                <h3 className="text-xl font-semibold mb-3">Altın Fiyatları</h3>
                {goldData && (
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <p className="font-medium">Gram Altın (TL)</p>
                            <p className="text-lg">{goldData.gram_altin} ₺</p>
                        </div>
                        <div>
                            <p className="font-medium">Ons Altın (USD)</p>
                            <p className="text-lg">${goldData.ons_altin}</p>
                        </div>
                    </div>
                )}
            </div>

            {/* Döviz Kurları */}
            <div className="mb-6 bg-white p-4 rounded-lg shadow">
                <h3 className="text-xl font-semibold mb-3">Döviz Kurları</h3>
                {exchangeRates && (
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <p className="font-medium">USD/TRY</p>
                            <p className="text-lg">{exchangeRates.USD} ₺</p>
                        </div>
                        <div>
                            <p className="font-medium">EUR/TRY</p>
                            <p className="text-lg">{exchangeRates.EUR} ₺</p>
                        </div>
                    </div>
                )}
            </div>

            {/* Analiz Sonuçları */}
            {analysis && (
                <div className="bg-white p-4 rounded-lg shadow">
                    <h3 className="text-xl font-semibold mb-3">Piyasa Analizi</h3>
                    <div className="space-y-4">
                        <div>
                            <p className="font-medium">Günlük Ortalama</p>
                            <p>{analysis.dailyAverage.value} ₺</p>
                        </div>
                        <div>
                            <p className="font-medium">Trend Analizi</p>
                            <p className={`text-${analysis.trend.direction === 'up' ? 'green' : 'red'}-500`}>
                                {analysis.trend.description}
                            </p>
                        </div>
                        {analysis.anomalies.length > 0 && (
                            <div>
                                <p className="font-medium">Anomali Tespiti</p>
                                <ul className="list-disc list-inside">
                                    {analysis.anomalies.map((anomaly, index) => (
                                        <li key={index} className="text-yellow-600">
                                            {anomaly.description}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
};

export default FinanceOverview; 