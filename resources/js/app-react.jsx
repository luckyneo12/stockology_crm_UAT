import React from 'react';
import { createRoot } from 'react-dom/client';
import Dashboard from './components/Dashboard';
import LeadsTable from './components/LeadsTable';
import LiveNotifications from './components/LiveNotifications';
import './crm-react.css';

// ── Mount: Live Notifications (global, injected in layout) ─────────────────
const notifMount = document.getElementById('crm-notifications');
if (notifMount) {
  createRoot(notifMount).render(<LiveNotifications />);
}

// ── Mount: Dashboard Widget ────────────────────────────────────────────────
const dashMount = document.getElementById('react-dashboard');
if (dashMount) {
  createRoot(dashMount).render(<Dashboard />);
}

// ── Mount: Leads Table ─────────────────────────────────────────────────────
const leadsMount = document.getElementById('react-leads-table');
if (leadsMount) {
  createRoot(leadsMount).render(<LeadsTable />);
}
