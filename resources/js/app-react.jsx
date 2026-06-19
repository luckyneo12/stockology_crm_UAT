import React from 'react';
import { createRoot } from 'react-dom/client';
import { MantineProvider } from '@mantine/core';
import '@mantine/core/styles.css';
import Dashboard from './components/Dashboard';
import LeadsTable from './components/LeadsTable';
import LiveNotifications from './components/LiveNotifications';
import LeadsBoard from './components/LeadsBoard';
import LeadsList from './components/LeadsList';
import ReactHeader from './components/ReactHeader';
import LeadDetails from './components/LeadDetails';
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

// ── Mount: React Header ────────────────────────────────────────────────────
const headerMount = document.getElementById('react-header');
if (headerMount) {
  createRoot(headerMount).render(
    <MantineProvider>
      <ReactHeader />
    </MantineProvider>
  );
}

// ── Mount: Lead Details Full Page ──────────────────────────────────────────
const detailsMount = document.getElementById('react-lead-details');
if (detailsMount) {
  const leadId = detailsMount.getAttribute('data-lead-id');
  const workspaceId = detailsMount.getAttribute('data-workspace-id');
  const currentUserId = detailsMount.getAttribute('data-current-user-id');
  createRoot(detailsMount).render(
    <MantineProvider>
      <LeadDetails 
        leadId={leadId} 
        workspaceId={workspaceId} 
        currentUserId={currentUserId}
        isFullPage={true}
      />
    </MantineProvider>
  );
}
