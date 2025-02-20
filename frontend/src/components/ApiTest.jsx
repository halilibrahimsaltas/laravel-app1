import { useEffect, useState } from 'react';
import { Box, Typography, CircularProgress, Alert } from '@mui/material';
import { getDashboardData } from '../services/api';

function ApiTest() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await getDashboardData();
        console.log('API Yanıtı:', response);
        setData(response);
        setLoading(false);
      } catch (err) {
        console.error('API Hatası:', err);
        setError(err.message);
        setLoading(false);
      }
    };

    fetchData();
  }, []);

  if (loading) {
    return (
      <Box display="flex" justifyContent="center" p={3}>
        <CircularProgress />
      </Box>
    );
  }

  if (error) {
    return (
      <Alert severity="error">
        API Bağlantı Hatası: {error}
      </Alert>
    );
  }

  return (
    <Box p={3}>
      <Typography variant="h6" gutterBottom>
        API Test Sonuçları
      </Typography>
      <pre style={{ background: '#f5f5f5', padding: 15, borderRadius: 4 }}>
        {JSON.stringify(data, null, 2)}
      </pre>
    </Box>
  );
}

export default ApiTest; 