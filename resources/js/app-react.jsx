import React from 'react';
import { createRoot } from 'react-dom/client';
import Dashboard from './components/Dashboard';
import LeadsTable from './components/LeadsTable';
import LiveNotifications from './components/LiveNotifications';
import LeadsBoard from './components/LeadsBoard';
import LeadsList from './components/LeadsList';
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

// ── Mount: Leads Board (Kanban) ────────────────────────────────────────────
const boardMount = document.getElementById('react-leads-board');
if (boardMount) {
  createRoot(boardMount).render(<LeadsBoard />);
}

// ── Mount: Leads List (React + Ant Design Table) ───────────────────────────
const listMount = document.getElementById('react-leads-list');
if (listMount) {
  createRoot(listMount).render(<LeadsList />);
}
