import React, { useState, useEffect, useRef } from 'react';

// ─── Tiny Icon Helper ──────────────────────────────────────────────────────────
const Icon = ({ name, className = '', style = {} }) => (
  <i className={`ti ${name} ${className}`} style={style} />
);

// ─── Avatar Circle ─────────────────────────────────────────────────────────────
const Avatar = ({ name = '?', size = 32, src = null }) => {
  const initials = name
    .split(' ')
    .map(w => w[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
  const colors = [
    ['#6366f1','#8b5cf6'], ['#059669','#047857'], ['#f59e0b','#d97706'],
    ['#3b82f6','#2563eb'], ['#ec4899','#db2777'], ['#14b8a6','#0d9488']
  ];
  const colorIdx = name.charCodeAt(0) % colors.length;
  const [from, to] = colors[colorIdx];
  if (src) return <img src={src} style={{ width: size, height: size, borderRadius: '50%', objectFit: 'cover' }} alt={name} />;
  return (
    <div style={{
      width: size, height: size, borderRadius: '50%',
      background: `linear-gradient(135deg,${from},${to})`,
      display: 'flex', alignItems: 'center', justifyContent: 'center',
      color: '#fff', fontWeight: 800, fontSize: size * 0.38,
      flexShrink: 0, letterSpacing: '-0.5px'
    }}>{initials}</div>
  );
};

// ─── Status Badge ──────────────────────────────────────────────────────────────
const StatusBadge = ({ status }) => {
  const map = {
    done: { bg: 'rgba(5,150,105,0.1)', color: '#059669', icon: 'ti-circle-check', label: 'Done' },
    pending: { bg: 'rgba(245,158,11,0.1)', color: '#d97706', icon: 'ti-clock', label: 'Pending' },
    overdue: { bg: 'rgba(239,68,68,0.1)', color: '#dc2626', icon: 'ti-alert-circle', label: 'Overdue' },
  };
  const cfg = map[status] || map.pending;
  return (
    <span style={{
      display: 'inline-flex', alignItems: 'center', gap: 4,
      padding: '3px 10px', borderRadius: 20, fontSize: '0.68rem',
      fontWeight: 700, background: cfg.bg, color: cfg.color
    }}>
      <Icon name={cfg.icon} style={{ fontSize: 11 }} />
      {cfg.label}
    </span>
  );
};

// ─── Section Card Wrapper ──────────────────────────────────────────────────────
const SectionCard = ({ id, icon, title, accent = '#059669', headerAction, children, className = '' }) => (
  <div id={id} className={`ld-section-card ${className}`}>
    <div className="ld-section-header" style={{ borderLeft: `4px solid ${accent}` }}>
      <div className="ld-section-header-left">
        <div className="ld-section-icon" style={{ background: `${accent}18`, color: accent }}>
          <Icon name={icon} style={{ fontSize: 16 }} />
        </div>
        <span className="ld-section-title">{title}</span>
      </div>
      {headerAction && <div className="ld-section-action">{headerAction}</div>}
    </div>
    <div className="ld-section-body">{children}</div>
  </div>
);

// ─── Empty State ───────────────────────────────────────────────────────────────
const EmptyState = ({ icon, text }) => (
  <div className="ld-empty-state">
    <div className="ld-empty-icon">
      <Icon name={icon} style={{ fontSize: 22, color: '#94a3b8' }} />
    </div>
    <span className="ld-empty-text">{text}</span>
  </div>
);

// ─── Add Button ───────────────────────────────────────────────────────────────
const AddButton = ({ url, title, label }) => (
  <button
    className="ld-add-btn"
    data-ajax-popup="true"
    data-title={title}
    data-url={url}
  >
    <Icon name="ti-plus" style={{ fontSize: 12 }} />
    {label}
  </button>
);

// ═══════════════════════════════════════════════════════════════════════════════
// MAIN COMPONENT
// ═══════════════════════════════════════════════════════════════════════════════
export default function LeadDetails({ leadId, onClose, workspaceId, currentUserId }) {
  const [loading, setLoading] = useState(true);
  const [lead, setLead] = useState(null);
  const [stages, setStages] = useState([]);
  const [sections, setSections] = useState([]);
  const [tasks, setTasks] = useState([]);
  const [reminders, setReminders] = useState([]);
  const [calls, setCalls] = useState([]);
  const [activities, setActivities] = useState([]);
  const [kycComments, setKycComments] = useState([]);

  const [activeTab, setActiveTab] = useState('general');
  const [editingField, setEditingField] = useState(null);
  const [editValue, setEditValue] = useState('');

  const [newComment, setNewComment] = useState('');
  const [submittingComment, setSubmittingComment] = useState(false);

  const stepperRef = useRef(null);
  const mobileMenuRef = useRef(null);
  const contentRef = useRef(null);

  // ── Data Fetcher ─────────────────────────────────────────────────────────────
  const fetchDetails = async () => {
    try {
      setLoading(true);
      const res = await fetch(`/leads/${leadId}/details-json`);
      const data = await res.json();
      if (data.success) {
        setLead(data.lead);
        setStages(data.stages);
        setSections(data.sections);
        setTasks(data.tasks);
        setReminders(data.reminders);
        setCalls(data.calls);
        setActivities(data.activities);
        setKycComments(data.kyc_comments);
      }
    } catch (err) {
      console.error('Error fetching lead details:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchDetails(); }, [leadId]);

  // Center active stepper node
  useEffect(() => {
    if (!loading && stepperRef.current) {
      setTimeout(() => {
        const active = stepperRef.current.querySelector('.ld-step-item.is-active');
        if (active) {
          const track = stepperRef.current;
          track.scrollTo({ left: active.offsetLeft - track.offsetWidth / 2 + active.offsetWidth / 2, behavior: 'smooth' });
        }
      }, 300);
    }
  }, [loading, lead?.stage_id]);

  // Scroll mobile pills to active
  useEffect(() => {
    if (!loading && mobileMenuRef.current) {
      const pill = mobileMenuRef.current.querySelector('.ld-pill.is-active');
      if (pill) {
        const c = mobileMenuRef.current;
        c.scrollTo({ left: pill.offsetLeft - c.offsetWidth / 2 + pill.offsetWidth / 2, behavior: 'smooth' });
      }
    }
  }, [activeTab, loading]);

  // ── Handlers ──────────────────────────────────────────────────────────────────
  const handleStageChange = async (targetStageId, stageName) => {
    if (String(targetStageId) === String(lead.stage_id)) return;
    const { value: accept } = await Swal.fire({
      title: 'Move Lead Stage?',
      html: `<div style="font-size:0.88rem;color:#64748b;margin-top:4px">Moving to: <strong style="color:#059669">${stageName}</strong></div>`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Move Stage',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#059669',
      cancelButtonColor: '#94a3b8',
      backdrop: 'rgba(15,23,42,0.4)',
      reverseButtons: true,
    });
    if (!accept) return;
    try {
      const form = new FormData();
      form.append('id', lead.id);
      form.append('lead_order', targetStageId);
      form.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
      const res = await fetch('/leads/order', { method: 'POST', body: form });
      const data = await res.json();
      if (data.is_success || data.success) {
        Swal.fire({ icon: 'success', title: 'Stage Updated!', text: `Moved to ${stageName}`, timer: 1400, showConfirmButton: false });
        fetchDetails();
      } else {
        Swal.fire('Error', data.error || 'Failed to update stage.', 'error');
      }
    } catch { Swal.fire('Error', 'Server error while moving stage.', 'error'); }
  };

  const handleToggleTask = async (task) => {
    const next = task.status === 'done' ? 'pending' : 'done';
    setTasks(prev => prev.map(t => t.id === task.id ? { ...t, status: next, status_label: next === 'done' ? 'Done' : 'Pending' } : t));
    try {
      const form = new FormData();
      form.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
      form.append('status', task.status);
      form.append('_method', 'PUT');
      const res = await fetch(task.update_url, { method: 'POST', body: form });
      const data = await res.json();
      if (!data.is_success) fetchDetails();
    } catch { fetchDetails(); }
  };

  const handleSubmitComment = async (e) => {
    e.preventDefault();
    if (!newComment.trim() || submittingComment) return;
    setSubmittingComment(true);
    try {
      const form = new FormData();
      form.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
      form.append('comment', newComment);
      form.append('is_kyc', '1');
      const res = await fetch(`/leads/${lead.id}/discussions`, { method: 'POST', body: form });
      const data = await res.json();
      if (data.is_success) { setNewComment(''); fetchDetails(); }
    } catch (err) { console.error(err); }
    finally { setSubmittingComment(false); }
  };

  const startEdit = (field) => {
    if (!field.can_edit) return;
    setEditingField(field);
    setEditValue(field.value || '');
  };

  const saveEdit = async () => {
    if (!editingField) return;
    try {
      const form = new FormData();
      form.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
      form.append('field_name', editingField.id || editingField.system_field_id);
      form.append('is_system', editingField.is_system ? '1' : '0');
      form.append('field_value', editValue);
      const res = await fetch(`/leads/${lead.id}/inline-update`, { method: 'POST', body: form });
      const data = await res.json();
      if (data.is_success) { setEditingField(null); fetchDetails(); }
      else alert(data.error || 'Failed to update field');
    } catch (err) { console.error(err); }
  };

  const normalizeTitle = (name) => {
    if (!name) return '';
    return name.toLowerCase().split(' ').map(w => w[0].toUpperCase() + w.slice(1)).join(' ');
  };

  // ── Menu Sections ─────────────────────────────────────────────────────────────
  const menuSections = [
    { id: 'general', name: 'Overview', icon: 'ti-layout-dashboard', accent: '#6366f1' },
    ...sections.map(s => ({ id: `section-${s.id}`, name: normalizeTitle(s.name), icon: 'ti-folder', accent: '#059669', raw: s })),
    { id: 'tasks', name: 'Tasks', icon: 'ti-checklist', accent: '#f59e0b' },
    { id: 'reminders', name: 'Reminders', icon: 'ti-bell', accent: '#ec4899' },
    { id: 'calls', name: 'Calls', icon: 'ti-phone-call', accent: '#3b82f6' },
    { id: 'activity', name: 'Activity', icon: 'ti-activity', accent: '#14b8a6' },
    ...(lead?.is_responsible ? [{ id: 'kyc', name: 'KYC Notes', icon: 'ti-shield-check', accent: '#8b5cf6' }] : []),
  ];

  const navigateTo = (id) => {
    setActiveTab(id);
    const el = document.getElementById(id);
    if (el && contentRef.current) {
      const top = el.offsetTop - 16;
      contentRef.current.scrollTo({ top, behavior: 'smooth' });
    }
  };

  // ── Loading ───────────────────────────────────────────────────────────────────
  if (loading) return (
    <div className="ld-loading-screen">
      <div className="ld-spinner" />
      <p className="ld-loading-text">Loading lead details…</p>
    </div>
  );

  if (!lead) return (
    <div className="ld-error-screen">
      <Icon name="ti-alert-triangle" style={{ fontSize: 32, color: '#ef4444' }} />
      <p>Lead not found or permission denied.</p>
    </div>
  );

  const probability = parseInt(lead.percentage) || 0;
  const probabilityColor = probability >= 70 ? '#059669' : probability >= 40 ? '#f59e0b' : '#ef4444';
  const probabilityLabel = probability >= 70 ? '🔥 High Intent' : probability >= 40 ? '⚡ Moderate' : '🧊 Low';
  const activeStageOrder = stages.find(s => s.id === lead.stage_id)?.order || 0;

  return (
    <div className="ld-root">
      {/* ── HEADER STRIP ─────────────────────────────────────────────────────── */}
      <div className="ld-header">
        <div className="ld-header-meta">
          <div className="ld-header-id-badge">#{lead.id}</div>
          <div className="ld-header-pipeline">{lead.pipeline_name}</div>
        </div>
        <div className="ld-header-name-row">
          <Avatar name={lead.name} size={44} />
          <div className="ld-header-name-block">
            <h1 className="ld-lead-name">{lead.name}</h1>
            <div className="ld-header-sub">
              <span><Icon name="ti-user-circle" style={{ marginRight: 4 }} />{lead.creator_name}</span>
              <span className="ld-dot" />
              <span><Icon name="ti-calendar" style={{ marginRight: 4 }} />{lead.created_at}</span>
              {lead.primary_owner && (
                <>
                  <span className="ld-dot" />
                  <span className="ld-owner-badge">
                    <Icon name="ti-crown" style={{ marginRight: 3, fontSize: 10 }} />
                    {lead.primary_owner.name}
                  </span>
                </>
              )}
            </div>
          </div>
        </div>
        {/* Quick Stats Row */}
        <div className="ld-quick-stats">
          <div className="ld-stat-pill" style={{ background: 'rgba(255,255,255,0.15)' }}>
            <Icon name="ti-mail" style={{ fontSize: 12 }} />
            <span>{lead.email || 'No Email'}</span>
          </div>
          <div className="ld-stat-pill" style={{ background: 'rgba(255,255,255,0.15)' }}>
            <Icon name="ti-phone" style={{ fontSize: 12 }} />
            <span>{lead.phone || 'No Phone'}</span>
            {lead.phone && (
              <div className="ld-phone-actions">
                <a href="javascript:void(0)" className="click-to-call" data-phone={lead.phone} title="Click to Call">
                  <Icon name="ti-phone-call" />
                </a>
                <a href={`/whatsapp-chats?lead_id=${lead.id}`} title="WhatsApp">
                  <Icon name="ti-brand-whatsapp" />
                </a>
              </div>
            )}
          </div>
          <div className="ld-stat-pill" style={{ background: `${probabilityColor}30`, color: '#fff' }}>
            <Icon name="ti-percentage" style={{ fontSize: 12 }} />
            <span>{probability}% · {probabilityLabel}</span>
          </div>
        </div>
      </div>

      {/* ── PIPELINE STEPPER ─────────────────────────────────────────────────── */}
      {stages.length > 0 && (
        <div className="ld-stepper-wrap">
          <div className="ld-stepper-info">
            <Icon name="ti-git-branch" style={{ fontSize: 13, color: '#059669' }} />
            <span className="ld-stepper-label">Pipeline Journey</span>
            <span className="ld-stepper-current-badge">{lead.stage_name}</span>
          </div>
          <div className="ld-stepper-track" ref={stepperRef}>
            {stages.map((stage) => {
              const isActive = String(stage.id) === String(lead.stage_id);
              const isCompleted = stage.order < activeStageOrder;
              const canMove = stage.can_move && !isActive;
              return (
                <div
                  key={stage.id}
                  className={`ld-step-item ${isActive ? 'is-active' : ''} ${isCompleted ? 'is-done' : ''} ${canMove ? 'can-move' : ''}`}
                  onClick={() => canMove && handleStageChange(stage.id, stage.name)}
                  title={stage.name}
                >
                  <div className="ld-step-node">
                    {isCompleted ? <Icon name="ti-check" /> : isActive ? <Icon name="ti-map-pin" /> : <span style={{ fontSize: 8, fontWeight: 800 }}>{stage.order}</span>}
                    {isActive && <div className="ld-step-pulse" />}
                  </div>
                  <div className="ld-step-name">{stage.name}</div>
                </div>
              );
            })}
          </div>
        </div>
      )}

      {/* ── MAIN LAYOUT ──────────────────────────────────────────────────────── */}
      <div className="ld-body">
        {/* Sidebar */}
        <nav className="ld-sidebar d-none d-md-flex">
          {menuSections.map(sec => (
            <button
              key={sec.id}
              className={`ld-nav-btn ${activeTab === sec.id ? 'is-active' : ''}`}
              onClick={() => navigateTo(sec.id)}
              style={activeTab === sec.id ? { '--nav-accent': sec.accent } : {}}
            >
              <span className="ld-nav-icon" style={{ background: activeTab === sec.id ? `${sec.accent}18` : 'transparent', color: activeTab === sec.id ? sec.accent : '#94a3b8' }}>
                <Icon name={sec.icon} style={{ fontSize: 15 }} />
              </span>
              <span className="ld-nav-label">{sec.name}</span>
              {activeTab === sec.id && <div className="ld-nav-indicator" style={{ background: sec.accent }} />}
            </button>
          ))}
        </nav>

        {/* Mobile Pills */}
        <div className="ld-mobile-pills d-md-none" ref={mobileMenuRef}>
          {menuSections.map(sec => (
            <button
              key={sec.id}
              className={`ld-pill ${activeTab === sec.id ? 'is-active' : ''}`}
              style={activeTab === sec.id ? { background: sec.accent } : {}}
              onClick={() => navigateTo(sec.id)}
            >
              <Icon name={sec.icon} style={{ fontSize: 12 }} />
              {sec.name}
            </button>
          ))}
        </div>

        {/* Content Scroll Area */}
        <div className="ld-content" ref={contentRef}>

          {/* ── OVERVIEW SECTION ──────────────────────────────────────────── */}
          <div id="general">
            {/* Conversion cards */}
            <div className="ld-2col-grid">

              {/* Contact Card */}
              <div className="ld-info-card">
                <div className="ld-info-card-header">
                  <Icon name="ti-address-book" style={{ color: '#6366f1' }} />
                  Contact Details
                </div>
                <div className="ld-contact-rows">
                  <div className="ld-contact-row">
                    <div className="ld-contact-icon" style={{ background: '#6366f115', color: '#6366f1' }}>
                      <Icon name="ti-mail" style={{ fontSize: 14 }} />
                    </div>
                    <div className="ld-contact-content">
                      <span className="ld-contact-label">Email</span>
                      <span className="ld-contact-value">{lead.email || '—'}</span>
                    </div>
                  </div>
                  <div className="ld-contact-row">
                    <div className="ld-contact-icon" style={{ background: '#059669' + '15', color: '#059669' }}>
                      <Icon name="ti-phone" style={{ fontSize: 14 }} />
                    </div>
                    <div className="ld-contact-content">
                      <span className="ld-contact-label">Phone</span>
                      <span className="ld-contact-value">
                        {lead.phone || '—'}
                        {lead.phone && (
                          <span className="ld-inline-actions">
                            <a href="javascript:void(0)" className="ld-icon-link click-to-call" data-phone={lead.phone} title="Call">
                              <Icon name="ti-phone-call" style={{ fontSize: 13 }} />
                            </a>
                            <a href={`/whatsapp-chats?lead_id=${lead.id}`} className="ld-icon-link ld-wa-link" title="WhatsApp">
                              <Icon name="ti-brand-whatsapp" style={{ fontSize: 13 }} />
                            </a>
                          </span>
                        )}
                      </span>
                    </div>
                  </div>
                  {lead.primary_owner && (
                    <div className="ld-contact-row">
                      <div className="ld-contact-icon" style={{ background: '#f59e0b15', color: '#f59e0b' }}>
                        <Icon name="ti-crown" style={{ fontSize: 14 }} />
                      </div>
                      <div className="ld-contact-content">
                        <span className="ld-contact-label">Owner</span>
                        <span className="ld-contact-value">{lead.primary_owner.name}</span>
                      </div>
                    </div>
                  )}
                </div>
              </div>

              {/* Probability Card */}
              <div className="ld-info-card">
                <div className="ld-info-card-header">
                  <Icon name="ti-chart-bar" style={{ color: probabilityColor }} />
                  Conversion Probability
                </div>
                <div className="ld-probability-body">
                  <div className="ld-prob-gauge">
                    <svg viewBox="0 0 100 60" width="140" height="84">
                      <path d="M10,60 A50,50 0 0,1 90,60" fill="none" stroke="#e2e8f0" strokeWidth="10" strokeLinecap="round" />
                      <path
                        d="M10,60 A50,50 0 0,1 90,60"
                        fill="none"
                        stroke={probabilityColor}
                        strokeWidth="10"
                        strokeLinecap="round"
                        strokeDasharray={`${(probability / 100) * 126} 126`}
                        style={{ transition: 'stroke-dasharray 1s ease' }}
                      />
                      <text x="50" y="58" textAnchor="middle" fill={probabilityColor} fontSize="18" fontWeight="800">{probability}%</text>
                    </svg>
                    <div className="ld-prob-label" style={{ color: probabilityColor }}>{probabilityLabel}</div>
                  </div>
                  <div className="ld-prob-details">
                    <div className="ld-prob-bar-row">
                      <div className="ld-prob-bar-bg">
                        <div className="ld-prob-bar-fill" style={{ width: `${probability}%`, background: probabilityColor }} />
                      </div>
                    </div>
                    <div className="ld-prob-stats-grid">
                      <div className="ld-prob-stat">
                        <Icon name="ti-list-check" style={{ color: '#059669', fontSize: 14 }} />
                        <span>{tasks.filter(t => t.status === 'done').length}/{tasks.length} Tasks</span>
                      </div>
                      <div className="ld-prob-stat">
                        <Icon name="ti-phone-call" style={{ color: '#3b82f6', fontSize: 14 }} />
                        <span>{calls.length} Calls</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* ── DYNAMIC DB SECTIONS ──────────────────────────────────────────── */}
          {sections.map(section => (
            <SectionCard
              key={section.id}
              id={`section-${section.id}`}
              icon="ti-table"
              title={normalizeTitle(section.name)}
              accent="#059669"
              headerAction={
                section.name.toLowerCase().includes('client summary') && lead.client_code_value && lead.active_orion_rule_id ? (
                  <button
                    className="ld-fetch-btn btn-orion-ekyc-fetch-trigger"
                    data-client-code={lead.client_code_value}
                    data-rule-id={lead.active_orion_rule_id}
                  >
                    <Icon name="ti-cloud-download" style={{ fontSize: 12 }} />
                    Fetch eKYC
                  </button>
                ) : null
              }
            >
              <div className="ld-fields-grid">
                {section.fields.map(field => {
                  const isEditing = editingField && editingField.id === field.id && editingField.is_system === field.is_system;
                  return (
                    <div key={field.id || field.system_field_id} className={`ld-field-item ${field.width === 3 ? 'ld-field-full' : field.width === 2 ? 'ld-field-two' : 'ld-field-one'}`}>
                      <span className="ld-field-label">{field.name}</span>
                      {isEditing ? (
                        <div className="ld-edit-row">
                          {field.type === 'select' && field.options ? (
                            <select className="ld-edit-input" value={editValue} onChange={e => setEditValue(e.target.value)}>
                              {field.options.split(',').map(o => (
                                <option key={o.trim()} value={o.trim()}>{o.trim()}</option>
                              ))}
                            </select>
                          ) : field.type === 'textarea' ? (
                            <textarea className="ld-edit-input" value={editValue} onChange={e => setEditValue(e.target.value)} rows={2} />
                          ) : field.type === 'date' ? (
                            <input type="date" className="ld-edit-input" value={editValue} onChange={e => setEditValue(e.target.value)} />
                          ) : (
                            <input type="text" className="ld-edit-input" value={editValue} onChange={e => setEditValue(e.target.value)} />
                          )}
                          <button className="ld-edit-save" onClick={saveEdit}><Icon name="ti-check" /></button>
                          <button className="ld-edit-cancel" onClick={() => setEditingField(null)}><Icon name="ti-x" /></button>
                        </div>
                      ) : (
                        <span
                          className={`ld-field-value ${field.can_edit ? 'is-editable' : ''}`}
                          onClick={() => field.can_edit && startEdit(field)}
                        >
                          {field.value || <em className="ld-empty-val">Not provided</em>}
                          {field.can_edit && <Icon name="ti-pencil" className="ld-edit-icon" />}
                        </span>
                      )}
                    </div>
                  );
                })}
              </div>
            </SectionCard>
          ))}

          {/* ── TASKS ────────────────────────────────────────────────────────── */}
          <SectionCard
            id="tasks"
            icon="ti-checklist"
            title="Tasks Checklist"
            accent="#f59e0b"
            headerAction={
              lead.is_responsible && (
                <AddButton url={`/leads/${lead.id}/task`} title="Create Task" label="Add Task" />
              )
            }
          >
            {tasks.length > 0 ? (
              <div className="ld-task-list">
                {tasks.map(task => (
                  <div key={task.id} className={`ld-task-item ${task.status === 'done' ? 'is-done' : ''}`}>
                    <label className="ld-task-check-wrap">
                      <input
                        type="checkbox"
                        className="ld-task-cb"
                        checked={task.status === 'done'}
                        onChange={() => handleToggleTask(task)}
                      />
                      <span className="ld-task-cb-custom">
                        {task.status === 'done' && <Icon name="ti-check" style={{ fontSize: 10, color: '#fff' }} />}
                      </span>
                    </label>
                    <div className="ld-task-body">
                      <div className="ld-task-name">{task.name}</div>
                      <div className="ld-task-meta">
                        <Icon name="ti-calendar" style={{ fontSize: 11 }} />
                        {task.date} {task.time}
                      </div>
                    </div>
                    <StatusBadge status={task.status} />
                  </div>
                ))}
              </div>
            ) : <EmptyState icon="ti-circle-check" text="No tasks scheduled." />}
          </SectionCard>

          {/* ── REMINDERS ────────────────────────────────────────────────────── */}
          <SectionCard
            id="reminders"
            icon="ti-bell"
            title="Reminders"
            accent="#ec4899"
            headerAction={<AddButton url={`/leads/${lead.id}/reminder`} title="Create Reminder" label="Add Reminder" />}
          >
            {reminders.length > 0 ? (
              <div className="ld-reminder-list">
                {reminders.map(rem => (
                  <div key={rem.id} className="ld-reminder-item">
                    <div className="ld-reminder-icon">
                      <Icon name="ti-bell-ringing" style={{ fontSize: 15, color: '#ec4899' }} />
                    </div>
                    <div className="ld-reminder-body">
                      <div className="ld-reminder-title">{rem.title}</div>
                      <div className="ld-reminder-meta">
                        <Icon name="ti-clock" style={{ fontSize: 11 }} /> {rem.remind_at} · by {rem.user_name}
                      </div>
                    </div>
                    <span className="ld-reminder-type-badge">{rem.type}</span>
                  </div>
                ))}
              </div>
            ) : <EmptyState icon="ti-bell-off" text="No reminders scheduled." />}
          </SectionCard>

          {/* ── CALLS ────────────────────────────────────────────────────────── */}
          <SectionCard
            id="calls"
            icon="ti-phone-call"
            title="Call Logs"
            accent="#3b82f6"
            headerAction={<AddButton url={`/leads/${lead.id}/call`} title="Create Call Log" label="Add Call" />}
          >
            {calls.length > 0 ? (
              <div className="ld-call-list">
                {calls.map(c => (
                  <div key={c.id} className="ld-call-item">
                    <div className="ld-call-icon">
                      <Icon name="ti-phone" style={{ fontSize: 14, color: '#3b82f6' }} />
                    </div>
                    <div className="ld-call-body">
                      <div className="ld-call-subject">{c.subject}</div>
                      {c.summary && <div className="ld-call-summary">{c.summary}</div>}
                      <div className="ld-call-meta">
                        <Icon name="ti-calendar" style={{ fontSize: 11 }} /> {c.date_time} · {c.user_name}
                      </div>
                    </div>
                    <span className="ld-call-duration">{c.duration}m</span>
                  </div>
                ))}
              </div>
            ) : <EmptyState icon="ti-phone-off" text="No calls logged." />}
          </SectionCard>

          {/* ── ACTIVITY FEED ────────────────────────────────────────────────── */}
          <SectionCard id="activity" icon="ti-activity" title="Activity Feed" accent="#14b8a6">
            {activities.length > 0 ? (
              <div className="ld-timeline">
                {activities.map((act, idx) => (
                  <div key={act.id} className={`ld-tl-item ${idx === activities.length - 1 ? 'is-last' : ''}`}>
                    <div className="ld-tl-dot" />
                    <div className="ld-tl-body">
                      <div className="ld-tl-desc">{act.description}</div>
                      <div className="ld-tl-meta">{act.created_at} · {act.user_name}</div>
                    </div>
                  </div>
                ))}
              </div>
            ) : <EmptyState icon="ti-activity" text="No activities logged." />}
          </SectionCard>

          {/* ── KYC COMMENTS ─────────────────────────────────────────────────── */}
          {lead.is_responsible && (
            <SectionCard id="kyc" icon="ti-shield-check" title="KYC Notes & Comments" accent="#8b5cf6">
              <form onSubmit={handleSubmitComment} className="ld-comment-form">
                <input
                  type="text"
                  className="ld-comment-input"
                  placeholder="Write a KYC discussion note…"
                  value={newComment}
                  onChange={e => setNewComment(e.target.value)}
                />
                <button type="submit" className="ld-comment-submit" disabled={submittingComment}>
                  {submittingComment ? <Icon name="ti-loader-2" style={{ animation: 'ld-spin 1s linear infinite' }} /> : <Icon name="ti-send" />}
                  Post
                </button>
              </form>
              <div className="ld-comment-list">
                {kycComments.length > 0 ? kycComments.map(com => (
                  <div key={com.id} className="ld-comment-item">
                    <Avatar name={com.user_name} src={com.user_avatar} size={34} />
                    <div className="ld-comment-bubble">
                      <div className="ld-comment-top">
                        <span className="ld-comment-author">{com.user_name}</span>
                        <span className="ld-comment-time">{com.created_at}</span>
                      </div>
                      <p className="ld-comment-text">{com.comment}</p>
                    </div>
                  </div>
                )) : <EmptyState icon="ti-message-dots" text="No KYC comments yet." />}
              </div>
            </SectionCard>
          )}

          <div style={{ height: 40 }} />
        </div>
      </div>

      {/* ── EMBEDDED STYLES ──────────────────────────────────────────────────── */}
      <style>{`
        /* ── Reset & Fonts ── */
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        .ld-root {
          font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
          display: flex;
          flex-direction: column;
          height: 100%;
          background: #f1f5f9;
          overflow: hidden;
        }

        /* ── Loading / Error ── */
        .ld-loading-screen, .ld-error-screen {
          display: flex; flex-direction: column;
          align-items: center; justify-content: center;
          height: 100%; gap: 12px; color: #64748b;
        }
        .ld-spinner {
          width: 38px; height: 38px;
          border: 3px solid #e2e8f0;
          border-top-color: #059669;
          border-radius: 50%;
          animation: ld-spin 0.75s linear infinite;
        }
        .ld-loading-text { font-size: 0.82rem; font-weight: 600; color: #94a3b8; }
        @keyframes ld-spin { to { transform: rotate(360deg); } }

        /* ── Header ── */
        .ld-header {
          background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f2a1a 100%);
          padding: 20px 20px 16px;
          flex-shrink: 0;
          position: relative;
          overflow: hidden;
        }
        .ld-header::before {
          content: '';
          position: absolute; inset: 0;
          background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23059669' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='20'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
          pointer-events: none;
        }
        .ld-header-meta {
          display: flex; align-items: center; gap: 10px;
          margin-bottom: 12px;
        }
        .ld-header-id-badge {
          background: rgba(5,150,105,0.2); color: #34d399;
          padding: 2px 10px; border-radius: 20px;
          font-size: 0.68rem; font-weight: 800; letter-spacing: 1px;
          border: 1px solid rgba(52,211,153,0.2);
        }
        .ld-header-pipeline {
          font-size: 0.72rem; color: rgba(255,255,255,0.45);
          font-weight: 600; letter-spacing: 0.5px;
        }
        .ld-header-name-row {
          display: flex; align-items: center; gap: 14px;
          margin-bottom: 14px;
        }
        .ld-header-name-block { flex: 1; min-width: 0; }
        .ld-lead-name {
          color: #fff; font-size: clamp(1.25rem, 3vw, 1.75rem);
          font-weight: 800; margin: 0; letter-spacing: -0.5px;
          line-height: 1.15; white-space: nowrap;
          overflow: hidden; text-overflow: ellipsis;
        }
        .ld-header-sub {
          display: flex; align-items: center; gap: 8px;
          flex-wrap: wrap; margin-top: 4px;
          font-size: 0.7rem; color: rgba(255,255,255,0.5); font-weight: 500;
        }
        .ld-dot { width: 3px; height: 3px; border-radius: 50%; background: rgba(255,255,255,0.3); }
        .ld-owner-badge {
          background: rgba(245,158,11,0.15); color: #fbbf24;
          padding: 2px 8px; border-radius: 12px; font-size: 0.65rem;
          font-weight: 700; border: 1px solid rgba(251,191,36,0.2);
        }
        .ld-quick-stats {
          display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
        }
        .ld-stat-pill {
          display: flex; align-items: center; gap: 6px;
          padding: 5px 12px; border-radius: 20px;
          font-size: 0.72rem; font-weight: 600; color: rgba(255,255,255,0.8);
          backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.08);
        }
        .ld-phone-actions {
          display: flex; gap: 6px; margin-left: 6px;
        }
        .ld-phone-actions a {
          color: rgba(255,255,255,0.6); text-decoration: none;
          transition: color 0.2s; font-size: 0.85rem;
        }
        .ld-phone-actions a:hover { color: #34d399; }

        /* ── Stepper ── */
        .ld-stepper-wrap {
          background: #fff; border-bottom: 1px solid #e2e8f0;
          padding: 10px 16px; flex-shrink: 0;
        }
        .ld-stepper-info {
          display: flex; align-items: center; gap: 8px;
          margin-bottom: 8px;
        }
        .ld-stepper-label { font-size: 0.68rem; font-weight: 700; color: #64748b; letter-spacing: 0.6px; text-transform: uppercase; }
        .ld-stepper-current-badge {
          margin-left: auto;
          background: rgba(5,150,105,0.08); color: #059669;
          padding: 2px 10px; border-radius: 12px; font-size: 0.68rem; font-weight: 700;
        }
        .ld-stepper-track {
          display: flex; align-items: flex-start;
          overflow-x: auto; gap: 0; padding-bottom: 4px;
          scrollbar-width: thin; scrollbar-color: rgba(5,150,105,0.2) transparent;
        }
        .ld-stepper-track::-webkit-scrollbar { height: 3px; }
        .ld-stepper-track::-webkit-scrollbar-thumb { background: rgba(5,150,105,0.2); border-radius: 2px; }
        .ld-step-item {
          display: flex; flex-direction: column; align-items: center;
          flex: 0 0 100px; min-width: 90px; position: relative; cursor: default;
        }
        .ld-step-item:not(:last-child)::after {
          content: ''; position: absolute;
          top: 14px; left: calc(50% + 14px); right: calc(-50% + 14px);
          height: 2px; background: #e2e8f0; z-index: 0;
          transition: background 0.4s ease;
        }
        .ld-step-item.is-done:not(:last-child)::after { background: #059669; }
        .ld-step-item.is-active:not(:last-child)::after { background: linear-gradient(to right, #059669, #e2e8f0); }
        .ld-step-node {
          width: 28px; height: 28px; border-radius: 50%;
          display: flex; align-items: center; justify-content: center;
          font-size: 10px; z-index: 1; position: relative;
          border: 2px solid #e2e8f0; background: #fff; color: #94a3b8;
          transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1);
        }
        .ld-step-item.is-active .ld-step-node {
          background: linear-gradient(135deg, #059669, #047857);
          border-color: #059669; color: #fff;
          box-shadow: 0 0 0 5px rgba(5,150,105,0.12), 0 4px 14px rgba(5,150,105,0.3);
          transform: scale(1.15);
        }
        .ld-step-item.is-done .ld-step-node {
          background: #059669; border-color: #059669; color: #fff;
        }
        .ld-step-item.can-move { cursor: pointer; }
        .ld-step-item.can-move:hover .ld-step-node {
          background: linear-gradient(135deg, #059669, #047857);
          border-color: #059669; color: #fff; transform: scale(1.1);
        }
        .ld-step-pulse {
          position: absolute; inset: -5px; border-radius: 50%;
          background: rgba(5,150,105,0.15); animation: ld-pulse 2s ease-in-out infinite;
        }
        @keyframes ld-pulse { 0%,100% { transform: scale(1); opacity: 0.5; } 50% { transform: scale(1.3); opacity: 0.2; } }
        .ld-step-name {
          font-size: 0.62rem; font-weight: 700; color: #94a3b8;
          text-align: center; margin-top: 6px; padding: 0 4px;
          max-width: 90px; line-height: 1.2;
          display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
          overflow: hidden;
        }
        .ld-step-item.is-active .ld-step-name { color: #059669; font-weight: 800; }
        .ld-step-item.is-done .ld-step-name { color: #475569; }

        /* ── Body layout ── */
        .ld-body {
          display: flex; flex: 1; min-height: 0; overflow: hidden;
        }

        /* ── Sidebar ── */
        .ld-sidebar {
          width: 190px; flex-shrink: 0;
          background: #fff; border-right: 1px solid #e2e8f0;
          display: flex; flex-direction: column;
          padding: 12px 8px; gap: 2px;
          overflow-y: auto;
        }
        .ld-nav-btn {
          display: flex; align-items: center; gap: 10px;
          padding: 9px 10px; border-radius: 10px;
          border: none; background: transparent; cursor: pointer;
          position: relative; transition: all 0.2s ease;
          text-align: left;
        }
        .ld-nav-btn:hover { background: #f8fafc; }
        .ld-nav-btn.is-active { background: #f0fdf4; }
        .ld-nav-icon {
          width: 30px; height: 30px; border-radius: 8px;
          display: flex; align-items: center; justify-content: center;
          flex-shrink: 0; transition: all 0.2s ease;
        }
        .ld-nav-label {
          font-size: 0.78rem; font-weight: 600; color: #475569;
          transition: color 0.2s;
        }
        .ld-nav-btn.is-active .ld-nav-label { color: #0f172a; font-weight: 700; }
        .ld-nav-indicator {
          position: absolute; right: 0; top: 50%; transform: translateY(-50%);
          width: 3px; height: 20px; border-radius: 2px 0 0 2px;
        }

        /* ── Mobile Pills ── */
        .ld-mobile-pills {
          display: flex; gap: 8px;
          padding: 10px 12px; background: #fff;
          border-bottom: 1px solid #e2e8f0;
          overflow-x: auto; flex-shrink: 0;
          scrollbar-width: none;
        }
        .ld-mobile-pills::-webkit-scrollbar { display: none; }
        .ld-pill {
          flex: 0 0 auto; display: inline-flex; align-items: center; gap: 5px;
          padding: 6px 14px; border-radius: 20px;
          border: none; background: #f1f5f9; color: #475569;
          font-size: 0.72rem; font-weight: 700; cursor: pointer;
          white-space: nowrap; transition: all 0.2s ease;
        }
        .ld-pill.is-active { color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

        /* ── Content ── */
        .ld-content {
          flex: 1; overflow-y: auto; padding: 16px;
          display: flex; flex-direction: column; gap: 16px;
          scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent;
        }
        .ld-content::-webkit-scrollbar { width: 4px; }
        .ld-content::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 2px; }

        /* ── Info Cards (2-col grid) ── */
        .ld-2col-grid {
          display: grid; grid-template-columns: 1fr 1fr; gap: 14px;
        }
        @media (max-width: 640px) { .ld-2col-grid { grid-template-columns: 1fr; } }
        .ld-info-card {
          background: #fff; border-radius: 14px;
          border: 1px solid #e2e8f0;
          overflow: hidden;
          box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.03);
          transition: box-shadow 0.2s ease;
        }
        .ld-info-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        .ld-info-card-header {
          display: flex; align-items: center; gap: 8px;
          padding: 12px 16px; border-bottom: 1px solid #f1f5f9;
          font-size: 0.72rem; font-weight: 800; color: #0f172a;
          text-transform: uppercase; letter-spacing: 0.6px;
        }
        .ld-contact-rows { padding: 8px 0; }
        .ld-contact-row {
          display: flex; align-items: center; gap: 12px;
          padding: 10px 16px; transition: background 0.15s;
        }
        .ld-contact-row:hover { background: #f8fafc; }
        .ld-contact-icon {
          width: 34px; height: 34px; border-radius: 10px;
          display: flex; align-items: center; justify-content: center;
          flex-shrink: 0;
        }
        .ld-contact-content { flex: 1; min-width: 0; }
        .ld-contact-label { display: block; font-size: 0.62rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
        .ld-contact-value {
          display: flex; align-items: center; gap: 6px;
          font-size: 0.8rem; font-weight: 700; color: #1e293b;
          white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .ld-inline-actions { display: flex; gap: 6px; }
        .ld-icon-link {
          display: flex; align-items: center; justify-content: center;
          width: 24px; height: 24px; border-radius: 6px;
          background: #f1f5f9; color: #3b82f6;
          text-decoration: none; transition: all 0.2s;
        }
        .ld-icon-link:hover { background: #3b82f6; color: #fff; }
        .ld-wa-link { color: #25d366; }
        .ld-wa-link:hover { background: #25d366; color: #fff; }

        /* Probability */
        .ld-probability-body { padding: 12px 16px 16px; }
        .ld-prob-gauge { display: flex; flex-direction: column; align-items: center; margin-bottom: 12px; }
        .ld-prob-label { font-size: 0.75rem; font-weight: 700; margin-top: 2px; }
        .ld-prob-bar-row { margin-bottom: 10px; }
        .ld-prob-bar-bg {
          height: 6px; border-radius: 6px; background: #f1f5f9; overflow: hidden;
        }
        .ld-prob-bar-fill { height: 100%; border-radius: 6px; transition: width 1s ease; }
        .ld-prob-stats-grid { display: flex; gap: 12px; }
        .ld-prob-stat {
          display: flex; align-items: center; gap: 5px;
          font-size: 0.72rem; font-weight: 600; color: #64748b;
        }

        /* ── Section Card ── */
        .ld-section-card {
          background: #fff; border-radius: 14px;
          border: 1px solid #e2e8f0;
          overflow: hidden;
          box-shadow: 0 1px 3px rgba(0,0,0,0.04);
          transition: box-shadow 0.2s;
        }
        .ld-section-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.07); }
        .ld-section-header {
          display: flex; align-items: center; justify-content: space-between;
          padding: 13px 16px; background: #fafafa;
          border-bottom: 1px solid #f1f5f9; padding-left: 14px;
        }
        .ld-section-header-left {
          display: flex; align-items: center; gap: 10px;
        }
        .ld-section-icon {
          width: 32px; height: 32px; border-radius: 9px;
          display: flex; align-items: center; justify-content: center;
        }
        .ld-section-title {
          font-size: 0.82rem; font-weight: 800; color: #0f172a; letter-spacing: -0.2px;
        }
        .ld-section-action {}
        .ld-section-body { padding: 14px 16px; }

        /* Add Btn */
        .ld-add-btn {
          display: inline-flex; align-items: center; gap: 5px;
          padding: 5px 12px; border-radius: 8px;
          border: 1.5px dashed #059669; background: rgba(5,150,105,0.04);
          color: #059669; font-size: 0.72rem; font-weight: 700;
          cursor: pointer; transition: all 0.2s;
        }
        .ld-add-btn:hover { background: #059669; color: #fff; border-style: solid; }

        /* Fetch btn */
        .ld-fetch-btn {
          display: inline-flex; align-items: center; gap: 5px;
          padding: 5px 12px; border-radius: 8px;
          border: none; background: linear-gradient(135deg, #059669, #047857);
          color: #fff; font-size: 0.72rem; font-weight: 700;
          cursor: pointer; transition: all 0.2s;
          box-shadow: 0 2px 8px rgba(5,150,105,0.25);
        }
        .ld-fetch-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(5,150,105,0.35); }

        /* Fields Grid */
        .ld-fields-grid {
          display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px;
          background: #f1f5f9; border-radius: 8px; overflow: hidden;
        }
        @media (max-width: 640px) { .ld-fields-grid { grid-template-columns: repeat(2,1fr); } }
        @media (max-width: 380px) { .ld-fields-grid { grid-template-columns: 1fr; } }
        .ld-field-item { background: #fff; padding: 12px 14px; }
        .ld-field-full { grid-column: 1 / -1; }
        .ld-field-two { grid-column: span 2; }
        .ld-field-one { grid-column: span 1; }
        .ld-field-label {
          display: block; font-size: 0.6rem; font-weight: 800;
          text-transform: uppercase; letter-spacing: 0.8px; color: #94a3b8;
          margin-bottom: 4px;
        }
        .ld-field-value {
          display: flex; align-items: center; gap: 6px;
          font-size: 0.8rem; font-weight: 700; color: #1e293b;
          word-break: break-word; line-height: 1.3;
        }
        .ld-field-value.is-editable { cursor: pointer; }
        .ld-field-value.is-editable:hover { color: #059669; }
        .ld-edit-icon { font-size: 11px; color: #94a3b8; opacity: 0; transition: opacity 0.2s; }
        .ld-field-value.is-editable:hover .ld-edit-icon { opacity: 1; color: #059669; }
        .ld-empty-val { font-size: 0.75rem; font-weight: 400; color: #cbd5e1; font-style: italic; }
        .ld-edit-row { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .ld-edit-input {
          flex: 1; min-width: 0; padding: 5px 9px; font-size: 0.78rem;
          border: 1.5px solid #059669; border-radius: 7px; outline: none;
          font-family: inherit; font-weight: 600; color: #1e293b;
          background: #f0fdf4;
        }
        .ld-edit-save, .ld-edit-cancel {
          width: 28px; height: 28px; border-radius: 7px;
          border: none; cursor: pointer; display: flex;
          align-items: center; justify-content: center; font-size: 13px;
          transition: all 0.2s;
        }
        .ld-edit-save { background: #059669; color: #fff; }
        .ld-edit-save:hover { background: #047857; }
        .ld-edit-cancel { background: #fee2e2; color: #ef4444; }
        .ld-edit-cancel:hover { background: #ef4444; color: #fff; }

        /* ── Tasks ── */
        .ld-task-list { display: flex; flex-direction: column; gap: 6px; }
        .ld-task-item {
          display: flex; align-items: center; gap: 12px;
          padding: 12px 14px; border-radius: 10px;
          border: 1px solid #f1f5f9; background: #fafafa;
          transition: all 0.2s; cursor: default;
        }
        .ld-task-item:hover { background: #f0fdf4; border-color: #d1fae5; }
        .ld-task-item.is-done { opacity: 0.65; }
        .ld-task-check-wrap { display: flex; align-items: center; cursor: pointer; }
        .ld-task-cb { display: none; }
        .ld-task-cb-custom {
          width: 20px; height: 20px; border-radius: 50%;
          border: 2px solid #d1d5db; display: flex;
          align-items: center; justify-content: center;
          transition: all 0.25s cubic-bezier(0.34,1.56,0.64,1);
          flex-shrink: 0;
        }
        .ld-task-cb:checked + .ld-task-cb-custom {
          background: linear-gradient(135deg, #059669, #047857);
          border-color: #059669;
          box-shadow: 0 0 0 3px rgba(5,150,105,0.15);
        }
        .ld-task-check-wrap:hover .ld-task-cb-custom { border-color: #059669; }
        .ld-task-body { flex: 1; min-width: 0; }
        .ld-task-name {
          font-size: 0.82rem; font-weight: 700; color: #1e293b;
          white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .ld-task-item.is-done .ld-task-name { text-decoration: line-through; color: #94a3b8; }
        .ld-task-meta {
          display: flex; align-items: center; gap: 4px;
          font-size: 0.67rem; color: #94a3b8; font-weight: 500; margin-top: 2px;
        }

        /* ── Reminders ── */
        .ld-reminder-list { display: flex; flex-direction: column; gap: 8px; }
        .ld-reminder-item {
          display: flex; align-items: center; gap: 12px;
          padding: 12px 14px; border-radius: 10px;
          background: #fdf2f8; border: 1px solid #fce7f3;
          transition: all 0.2s;
        }
        .ld-reminder-item:hover { background: #fce7f3; }
        .ld-reminder-icon {
          width: 36px; height: 36px; border-radius: 10px;
          background: rgba(236,72,153,0.1); display: flex;
          align-items: center; justify-content: center; flex-shrink: 0;
        }
        .ld-reminder-body { flex: 1; min-width: 0; }
        .ld-reminder-title { font-size: 0.82rem; font-weight: 700; color: #1e293b; }
        .ld-reminder-meta {
          display: flex; align-items: center; gap: 4px;
          font-size: 0.67rem; color: #94a3b8; margin-top: 3px;
        }
        .ld-reminder-type-badge {
          padding: 3px 10px; border-radius: 12px;
          background: rgba(236,72,153,0.1); color: #db2777;
          font-size: 0.65rem; font-weight: 700;
        }

        /* ── Calls ── */
        .ld-call-list { display: flex; flex-direction: column; gap: 8px; }
        .ld-call-item {
          display: flex; align-items: flex-start; gap: 12px;
          padding: 12px 14px; border-radius: 10px;
          background: #eff6ff; border: 1px solid #dbeafe; transition: all 0.2s;
        }
        .ld-call-item:hover { background: #dbeafe; }
        .ld-call-icon {
          width: 36px; height: 36px; border-radius: 10px;
          background: rgba(59,130,246,0.1); display: flex;
          align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;
        }
        .ld-call-body { flex: 1; min-width: 0; }
        .ld-call-subject { font-size: 0.82rem; font-weight: 700; color: #1e293b; }
        .ld-call-summary { font-size: 0.73rem; color: #64748b; margin-top: 3px; }
        .ld-call-meta {
          display: flex; align-items: center; gap: 4px;
          font-size: 0.67rem; color: #94a3b8; margin-top: 4px;
        }
        .ld-call-duration {
          padding: 3px 10px; border-radius: 12px;
          background: rgba(59,130,246,0.1); color: #2563eb;
          font-size: 0.68rem; font-weight: 800; flex-shrink: 0;
        }

        /* ── Timeline ── */
        .ld-timeline { padding-left: 16px; }
        .ld-tl-item {
          position: relative; padding-left: 20px; padding-bottom: 18px;
          border-left: 2px solid #e2e8f0; transition: border-color 0.2s;
        }
        .ld-tl-item.is-last { border-left-color: transparent; padding-bottom: 0; }
        .ld-tl-item:hover { border-left-color: #14b8a6; }
        .ld-tl-dot {
          position: absolute; left: -7px; top: 2px;
          width: 12px; height: 12px; border-radius: 50%;
          background: #e2e8f0; border: 2px solid #fff;
          box-shadow: 0 0 0 2px #e2e8f0;
          transition: all 0.2s;
        }
        .ld-tl-item:hover .ld-tl-dot {
          background: #14b8a6; box-shadow: 0 0 0 3px rgba(20,184,166,0.15);
        }
        .ld-tl-body {}
        .ld-tl-desc { font-size: 0.8rem; font-weight: 600; color: #1e293b; line-height: 1.35; }
        .ld-tl-meta { font-size: 0.67rem; color: #94a3b8; margin-top: 3px; }

        /* ── KYC Comments ── */
        .ld-comment-form {
          display: flex; gap: 10px; margin-bottom: 16px;
          padding: 12px; background: #f8fafc; border-radius: 12px;
          border: 1px solid #e2e8f0;
        }
        .ld-comment-input {
          flex: 1; padding: 8px 14px; border-radius: 8px;
          border: 1.5px solid #e2e8f0; outline: none; font-size: 0.82rem;
          font-family: inherit; font-weight: 500; color: #1e293b;
          background: #fff; transition: border-color 0.2s;
        }
        .ld-comment-input:focus { border-color: #8b5cf6; }
        .ld-comment-submit {
          display: flex; align-items: center; gap: 6px;
          padding: 8px 16px; border-radius: 8px; border: none;
          background: linear-gradient(135deg, #8b5cf6, #7c3aed);
          color: #fff; font-size: 0.78rem; font-weight: 700;
          cursor: pointer; transition: all 0.2s; white-space: nowrap;
          box-shadow: 0 2px 8px rgba(139,92,246,0.3);
        }
        .ld-comment-submit:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(139,92,246,0.4); }
        .ld-comment-submit:disabled { opacity: 0.7; cursor: wait; }
        .ld-comment-list { display: flex; flex-direction: column; gap: 12px; }
        .ld-comment-item { display: flex; align-items: flex-start; gap: 10px; }
        .ld-comment-bubble { flex: 1; min-width: 0; }
        .ld-comment-top {
          display: flex; justify-content: space-between; align-items: center;
          margin-bottom: 4px;
        }
        .ld-comment-author { font-size: 0.78rem; font-weight: 800; color: #1e293b; }
        .ld-comment-time { font-size: 0.65rem; color: #94a3b8; }
        .ld-comment-text {
          font-size: 0.78rem; color: #475569; line-height: 1.5;
          background: #f8fafc; padding: 10px 14px; border-radius: 0 12px 12px 12px;
          border: 1px solid #f1f5f9; margin: 0;
        }

        /* ── Empty State ── */
        .ld-empty-state {
          display: flex; flex-direction: column; align-items: center;
          gap: 8px; padding: 28px; color: #94a3b8;
        }
        .ld-empty-icon {
          width: 50px; height: 50px; border-radius: 14px;
          background: #f8fafc; display: flex; align-items: center;
          justify-content: center; border: 1px solid #e2e8f0;
        }
        .ld-empty-text { font-size: 0.78rem; font-weight: 600; }
      `}</style>
    </div>
  );
}
