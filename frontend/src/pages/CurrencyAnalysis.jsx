import { useState } from 'react';
import { Grid, Paper, Typography, Select, MenuItem, FormControl, InputLabel } from '@mui/material';
import { Line } from 'react-chartjs-2';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
} from 'chart.js';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
);

const dummyData = {
  'USD/TRY': {
    labels: ['1 Saat', '2 Saat', '3 Saat', '4 Saat', '5 Saat'],
    data: [31.2, 31.3, 31.25, 31.4, 31.35],
  },
  'EUR/TRY': {
    labels: ['1 Saat', '2 Saat', '3 Saat', '4 Saat', '5 Saat'],
    data: [33.8, 33.9, 33.85, 34.0, 33.95],
  },
};

function CurrencyAnalysis() {
  const [selectedPair, setSelectedPair] = useState('USD/TRY');

  const chartData = {
    labels: dummyData[selectedPair].labels,
    datasets: [
      {
        label: selectedPair,
        data: dummyData[selectedPair].data,
        borderColor: 'rgb(75, 192, 192)',
        tension: 0.1,
      },
    ],
  };

  const options = {
    responsive: true,
    plugins: {
      legend: {
        position: 'top',
      },
      title: {
        display: true,
        text: 'Döviz Kuru Analizi',
      },
    },
    scales: {
      y: {
        beginAtZero: false,
      },
    },
  };

  return (
    <Grid container spacing={3}>
      <Grid item xs={12}>
        <Typography variant="h4" gutterBottom>
          Döviz Kuru Analizi
        </Typography>
      </Grid>
      <Grid item xs={12} md={4}>
        <FormControl fullWidth>
          <InputLabel>Para Birimi Çifti</InputLabel>
          <Select
            value={selectedPair}
            label="Para Birimi Çifti"
            onChange={(e) => setSelectedPair(e.target.value)}
          >
            <MenuItem value="USD/TRY">USD/TRY</MenuItem>
            <MenuItem value="EUR/TRY">EUR/TRY</MenuItem>
          </Select>
        </FormControl>
      </Grid>
      <Grid item xs={12}>
        <Paper sx={{ p: 2 }}>
          <Line options={options} data={chartData} />
        </Paper>
      </Grid>
    </Grid>
  );
}

export default CurrencyAnalysis; 