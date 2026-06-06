import React, { useState, useEffect, useCallback, useRef } from 'react';
import socket from '../socket';

const STAGES    = ['All','New','Contacted','Qualified','Proposal','Converted','Lost'];
const STAGE_CLR = { New:'#6366f1', Contacted:'#22d3ee', Qualified:'#f59e0b', Proposal:'#a855f7', Converted:'#10b981', Lost:'#f43f5e' };

export default function LeadsTable() {
  const [rows,    setRows]    = useState([]);
  const [total,   setTotal]   = useState(0);
  const [page,    setPage]    = useState(1);
  const [search,  setSearch]  = useState('');
  const [stage,   setStage]   = useState('All');
  const [loading, setLoading] = useState(true);
  const [flash,   setFlash]   = useState(null);  // id of newly added row
  const limit = 25;
  const searchTimer = useRef(null);

  const fetchLeads = useCallback(async (pg = page, q = search) => {
    setLoading(true);
    try {
      const res  = await fetch(`/api/node/leads?page=${pg}&limit=${limit}&search=${encodeURIComponent(q)}`);
      const json = await res.json();
      if (json.success) {
        setRows(json.data);
        setTotal(json.total);
      }
    } finally {
      setLoading(false);
    }
  }, [page, search]);

  useEffect(() => { fetchLeads(page, search); }, [page]);

  // Live: new lead added via Socket.IO
  useEffect(() => {
    const handler = (lead) => {
      setRows(prev => [lead, ...prev.slice(0, limit - 1)]);
      setTotal(t => t + 1);
      setFlash(lead.id);
      setTimeout(() => setFlash(null), 2500);
    };
    socket.on('leads:new', handler);
    return () => socket.off('leads:new', handler);
  }, []);

  const handleSearch = (e) => {
    const v = e.target.value;
    setSearch(v);
    clearTimeout(searchTimer.current);
    searchTimer.current = setTimeout(() => { setPage(1); fetchLeads(1, v); }, 400);
  };

  const filtered = stage === 'All' ? rows : rows.filter(r => r.lead_stage === stage);
  const totalPages = Math.ceil(total / limit);

  return (
    <div className="crm-leads-wrap">
      {/* ── Toolbar ────────────────────────────────────────────────── */}
      <div className="crm-leads-toolbar">
        <div className="crm-search-wrap">
          <span className="crm-search-icon">🔍</span>
          <input
            className="crm-search-input"
            placeholder="Search name, email, phone…"
            value={search}
            onChange={handleSearch}
          />
        </div>
        <div className="crm-stage-filters">
          {STAGES.map(s => (
            <button
              key={s}
              className={`crm-stage-btn ${stage === s ? 'active' : ''}`}
              style={stage === s && s !== 'All' ? { background: STAGE_CLR[s] } : {}}
              onClick={() => setStage(s)}
            >{s}</button>
          ))}
        </div>
        <div className="crm-leads-meta">
          <span className="crm-live-dot" title="Live via Socket.IO" />
          {total} total leads
        </div>
      </div>

      {/* ── Table ──────────────────────────────────────────────────── */}
      <div className="crm-table-wrap">
        {loading && <div className="crm-table-overlay"><div className="crm-spinner" /></div>}
        <table className="crm-table">
          <thead>
            <tr>
              <th>#</th><th>Name</th><th>Email</th><th>Phone</th>
              <th>Stage</th><th>Assigned To</th><th>Created</th>
            </tr>
          </thead>
          <tbody>
            {filtered.length === 0 && !loading
              ? <tr><td colSpan={7} className="crm-empty-row">No leads found</td></tr>
              : filtered.map((lead, i) => (
                <tr
                  key={lead.id}
                  className={`crm-lead-row ${flash === lead.id ? 'crm-lead-row--new' : ''}`}
                >
                  <td className="crm-row-num">{(page - 1) * limit + i + 1}</td>
                  <td className="crm-lead-name">{lead.name}</td>
                  <td>{lead.email || '–'}</td>
                  <td>{lead.phone || '–'}</td>
                  <td>
                    <span
                      className="crm-badge"
                      style={{ background: STAGE_CLR[lead.lead_stage] || '#64748b' }}
                    >{lead.lead_stage}</span>
                  </td>
                  <td>{lead.assigned_to || '–'}</td>
                  <td>{new Date(lead.created_at).toLocaleDateString('en-IN')}</td>
                </tr>
              ))
            }
          </tbody>
        </table>
      </div>

      {/* ── Pagination ─────────────────────────────────────────────── */}
      {totalPages > 1 && (
        <div className="crm-pagination">
          <button disabled={page === 1}            onClick={() => setPage(1)}>«</button>
          <button disabled={page === 1}            onClick={() => setPage(p => p - 1)}>‹</button>
          <span>Page {page} / {totalPages}</span>
          <button disabled={page === totalPages}   onClick={() => setPage(p => p + 1)}>›</button>
          <button disabled={page === totalPages}   onClick={() => setPage(totalPages)}>»</button>
        </div>
      )}
    </div>
  );
}
