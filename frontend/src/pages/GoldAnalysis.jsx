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
  'XAU/USD': {
    labels: ['1 Saat', '2 Saat', '3 Saat', '4 Saat', '5 Saat'],
    data: [2050, 2055, 2048, 2060, 2058],
  },
  'XAU/TRY': {
    labels: ['1 Saat', '2 Saat', '3 Saat', '4 Saat', '5 Saat'],
    data: [64000, 64200, 64100, 64500, 64400],
  },
};

function GoldAnalysis() {
  const [selectedPair, setSelectedPair] = useState('XAU/USD');

  const chartData = {
    labels: dummyData[selectedPair].labels,
    datasets: [
      {
        label: selectedPair,
        data: dummyData[selectedPair].data,
        borderColor: 'rgb(255, 215, 0)',
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
        text: 'Altın Fiyat Analizi',
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
          Altın Fiyat Analizi
        </Typography>
      </Grid>
      <Grid item xs={12} md={4}>
        <FormControl fullWidth>
          <InputLabel>Para Birimi</InputLabel>
          <Select
            value={selectedPair}
            label="Para Birimi"
            onChange={(e) => setSelectedPair(e.target.value)}
          >
            <MenuItem value="XAU/USD">Altın/USD</MenuItem>
            <MenuItem value="XAU/TRY">Altın/TRY</MenuItem>
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

export default GoldAnalysis; 