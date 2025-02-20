import React from 'react';
import { Container, Grid, Paper, Typography } from '@mui/material';
import { Line } from 'react-chartjs-2';
import ApiTest from '../components/ApiTest';
import FinanceOverview from '../components/FinanceOverview';
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
  labels: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs'],
  datasets: [
    {
      label: 'USD/TRY',
      data: [30.5, 31.2, 31.8, 32.1, 31.9],
      borderColor: 'rgb(75, 192, 192)',
      tension: 0.1,
    },
    {
      label: 'Altın/TRY',
      data: [2000, 2100, 2150, 2200, 2180],
      borderColor: 'rgb(255, 99, 132)',
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
      text: 'Finansal Veri Trendi',
    },
  },
};

const Dashboard = () => {
  return (
    <Container maxWidth="lg">
      <Grid container spacing={3}>
        <Grid item xs={12}>
          <Typography variant="h4" gutterBottom>
            Finansal Veri Analiz Paneli
          </Typography>
        </Grid>
        <Grid item xs={12}>
          <Paper elevation={3}>
            <FinanceOverview />
          </Paper>
        </Grid>
        <Grid item xs={12}>
          <Paper sx={{ p: 2 }}>
            <ApiTest />
          </Paper>
        </Grid>
        <Grid item xs={12}>
          <Paper sx={{ p: 2 }}>
            <Line options={options} data={dummyData} />
          </Paper>
        </Grid>
      </Grid>
    </Container>
  );
};

export default Dashboard; 