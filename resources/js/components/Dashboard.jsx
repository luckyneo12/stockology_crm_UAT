import React, { useEffect, useState } from 'react';
import { Bar, Doughnut } from 'react-chartjs-2';
import {
  Chart as ChartJS,
  CategoryScale, LinearScale, BarElement,
  ArcElement, Tooltip, Legend
} from 'chart.js';
import socket from '../socket';

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Tooltip, Legend);

// ─── Stat Card ────────────────────────────────────────────────────────────────
function StatCard({ label, value, icon, color, delta }) {
  return (
    <div className={`crm-stat-card crm-stat-card--${color}`}>
      <div className="crm-stat-icon">{icon}</div>
      <div className="crm-stat-info">
        <p className="crm-stat-label">{label}</p>
        <p className="crm-stat-value">{value ?? '–'}</p>
        {delta !== undefined && (
          <p className={`crm-stat-delta ${delta >= 0 ? 'crm-stat-delta--up' : 'crm-stat-delta--down'}`}>
            {delta >= 0 ? '▲' : '▼'} {Math.abs(delta)} today
          </p>
        )}
      </div>
    </div>
  );
}

// ─── Dashboard Component ──────────────────────────────────────────────────────
export default function Dashboard() {
  const [data,    setData]    = useState(null);
  const [loading, setLoading] = useState(true);
  const [online,  setOnline]  = useState([]);

  const fetchData = () => {
    setLoading(true);
    fetch('/api/node/dashboard')
      .then(r => r.json())
      .then(json => { setData(json); setLoading(false); })
      .catch(() => setLoading(false));
  };

  useEffect(() => {
    fetchData();

    // Refresh stats when a new lead arrives
    socket.on('leads:new', fetchData);
    socket.on('presence:update', setOnline);

    // Announce presence
    const uid   = document.querySelector('meta[name="user-id"]')?.content   || 'guest';
    const uname = document.querySelector('meta[name="user-name"]')?.content || 'Guest';
    socket.emit('presence:join', { userId: uid, name: uname });

    return () => {
      socket.off('leads:new', fetchData);
      socket.off('presence:update', setOnline);
    };
  }, []);

  if (loading) return (
    <div className="crm-dash-loader">
      <div className="crm-spinner" />
      <p>Loading dashboard…</p>
    </div>
  );

  if (!data?.success) return (
    <div className="crm-dash-error">
      ⚠️ Could not reach Node API. Make sure the Node server is running (<code>npm run node:dev</code>).
    </div>
  );

  const { stats, stage_chart, monthly_trend, recent_leads } = data;

  // ── Chart data ────────────────────────────────────────────────────────
  const stageColors = ['#6366f1','#22d3ee','#f59e0b','#f43f5e','#10b981','#a855f7'];
  const doughnutData = {
    labels:   stage_chart.map(s => s.stage),
    datasets: [{ data: stage_chart.map(s => s.count), backgroundColor: stageColors, borderWidth: 2 }],
  };

  const barData = {
    labels:   monthly_trend.map(m => m.month),
    datasets: [{
      label: 'Leads',
      data:  monthly_trend.map(m => m.count),
      backgroundColor: 'rgba(99,102,241,0.7)',
      borderRadius: 6,
    }],
  };

  const chartOptions = {
    responsive: true,
    plugins: { legend: { labels: { color: '#e2e8f0' } } },
    scales: {
      x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } },
      y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } },
    },
  };

  return (
    <div className="crm-dashboard">
      {/* ── Header ─────────────────────────────────────────────────── */}
      <div className="crm-dash-header">
        <div>
          <h1 className="crm-dash-title">📊 CRM Dashboard</h1>
          <p className="crm-dash-sub">Live data · Node.js + Socket.IO</p>
        </div>
        <div className="crm-online-pills">
          {online.slice(0, 5).map((u, i) => (
            <span key={i} className="crm-online-pill" title={u.name}>
              {u.name[0].toUpperCase()}
            </span>
          ))}
          {online.length > 0 && <span className="crm-online-count">{online.length} online</span>}
        </div>
      </div>

      {/* ── Stat Cards ──────────────────────────────────────────────── */}
      <div className="crm-stat-grid">
        <StatCard label="Total Leads"    value={stats.total_leads} icon="👥" color="indigo" delta={stats.today_leads} />
        <StatCard label="New"            value={stats.new_leads}   icon="🆕" color="cyan" />
        <StatCard label="Contacted"      value={stats.contacted}   icon="📞" color="amber" />
        <StatCard label="Converted"      value={stats.converted}   icon="🏆" color="green" />
        <StatCard label="Lost"           value={stats.lost}        icon="❌" color="red" />
        <StatCard label="Today's Leads"  value={stats.today_leads} icon="📅" color="purple" />
      </div>

      {/* ── Charts ──────────────────────────────────────────────────── */}
      <div className="crm-charts-grid">
        <div className="crm-chart-card">
          <h2 className="crm-chart-title">Monthly Trend</h2>
          <Bar data={barData} options={chartOptions} />
        </div>
        <div className="crm-chart-card">
          <h2 className="crm-chart-title">Lead Stages</h2>
          <Doughnut data={doughnutData} options={{ responsive: true, plugins: { legend: { labels: { color: '#e2e8f0' } } } }} />
        </div>
      </div>

      {/* ── Recent Leads ─────────────────────────────────────────────── */}
      <div className="crm-recent-card">
        <h2 className="crm-chart-title">🕒 Recent Leads</h2>
        <table className="crm-table">
          <thead>
            <tr>
              <th>Name</th><th>Email</th><th>Stage</th><th>Added</th>
            </tr>
          </thead>
          <tbody>
            {recent_leads.map(l => (
              <tr key={l.id}>
                <td>{l.name}</td>
                <td>{l.email || '–'}</td>
                <td><span className="crm-badge">{l.lead_stage}</span></td>
                <td>{new Date(l.created_at).toLocaleDateString()}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
