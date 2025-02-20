import { AppBar, Toolbar, Typography, Button, Box } from '@mui/material';
import { Link as RouterLink } from 'react-router-dom';
import CurrencyExchangeIcon from '@mui/icons-material/CurrencyExchange';
import MonetizationOnIcon from '@mui/icons-material/MonetizationOn';
import DashboardIcon from '@mui/icons-material/Dashboard';

function Navbar() {
  return (
    <AppBar position="fixed">
      <Toolbar>
        <Typography variant="h6" component="div" sx={{ flexGrow: 1 }}>
          Finansal Veri Analizi
        </Typography>
        <Box sx={{ display: 'flex', gap: 2 }}>
          <Button
            color="inherit"
            component={RouterLink}
            to="/"
            startIcon={<DashboardIcon />}
          >
            Dashboard
          </Button>
          <Button
            color="inherit"
            component={RouterLink}
            to="/currency"
            startIcon={<CurrencyExchangeIcon />}
          >
            Döviz Analizi
          </Button>
          <Button
            color="inherit"
            component={RouterLink}
            to="/gold"
            startIcon={<MonetizationOnIcon />}
          >
            Altın Analizi
          </Button>
        </Box>
      </Toolbar>
    </AppBar>
  );
}

export default Navbar; 