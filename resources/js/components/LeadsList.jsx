import React, { useState, useEffect, useRef, useCallback } from 'react';
import {
  Table, Button, Input, Select, DatePicker, Drawer, Space, Tag, Tooltip,
  Popconfirm, Badge, Statistic, Progress, Divider, Switch, Dropdown, Menu,
  Row, Col, Card, Typography, ConfigProvider, theme, Spin, Empty, Checkbox
} from 'antd';
import {
  SearchOutlined, FilterOutlined, ReloadOutlined, PlusOutlined,
  ExportOutlined, DeleteOutlined, EditOutlined, EyeOutlined,
  PhoneOutlined, MailOutlined, UserOutlined, CalendarOutlined,
  CheckCircleOutlined, BellOutlined,
  TeamOutlined, DatabaseOutlined, CloseOutlined, DownOutlined,
  FileExcelOutlined, FileTextOutlined, SettingOutlined,
} from '@ant-design/icons';
import dayjs from 'dayjs';
import LeadDetails from './LeadDetails';

const { Search } = Input;
const { RangePicker } = DatePicker;
const { Text, Title } = Typography;
const { useToken } = theme;

// ─── Color palette for stage badges ────────────────────────────────────────
const STAGE_COLORS = [
  '#6366f1','#0ea5e9','#10b981','#f59e0b','#f43f5e',
  '#8b5cf6','#ec4899','#14b8a6','#f97316'
];

// ─── Avatar Cell ────────────────────────────────────────────────────────────
function NameAvatar({ name = '' }) {
  const initials = name.trim().split(' ').map(w => w[0] || '').join('').toUpperCase().slice(0, 2);
  const colors = ['#6366f1','#059669','#f59e0b','#3b82f6','#ec4899','#14b8a6'];
  const bg = colors[(name.charCodeAt(0) || 0) % colors.length];
  return (
    <div style={{
      width: 32, height: 32, borderRadius: '50%',
      background: `linear-gradient(135deg, ${bg}, ${bg}cc)`,
      display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
      color: '#fff', fontWeight: 800, fontSize: 11, flexShrink: 0,
      border: '2px solid rgba(255,255,255,0.8)',
      boxShadow: `0 2px 8px ${bg}40`
    }}>{initials}</div>
  );
}

// ─── KPI Stat Card ───────────────────────────────────────────────────────────
function StatCard({ title, value, icon, color, suffix = '', loading }) {
  return (
    <Card
      size="small"
      style={{
        borderRadius: 14, border: 'none',
        boxShadow: `0 1px 3px rgba(0,0,0,0.06), 0 4px 16px ${color}18`,
        background: '#fff', overflow: 'hidden', cursor: 'default'
      }}
      bodyStyle={{ padding: '16px 20px' }}
    >
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        <div>
          <div style={{ fontSize: '0.68rem', fontWeight: 700, color: '#94a3b8', textTransform: 'uppercase', letterSpacing: '0.6px', marginBottom: 4 }}>
            {title}
          </div>
          {loading
            ? <div style={{ width: 60, height: 28, borderRadius: 6, background: '#f1f5f9', animation: 'llPulse 1.2s infinite' }} />
            : <div style={{ fontSize: '1.65rem', fontWeight: 800, color: '#0f172a', lineHeight: 1.1 }}>
                {value?.toLocaleString()}<span style={{ fontSize: '0.85rem', color: '#94a3b8', fontWeight: 500 }}>{suffix}</span>
              </div>
          }
        </div>
        <div style={{
          width: 44, height: 44, borderRadius: 12,
          background: `${color}15`,
          display: 'flex', alignItems: 'center', justifyContent: 'center',
          fontSize: 20, color
        }}>
          {icon}
        </div>
      </div>
    </Card>
  );
}

// ═══════════════════════════════════════════════════════════════════════════════
// MAIN COMPONENT
// ═══════════════════════════════════════════════════════════════════════════════
export default function LeadsList() {
  const mountEl = document.getElementById('react-leads-list');
  const cfg = {
    pipelineId:     mountEl?.getAttribute('data-pipeline-id') || '',
    pipelineOpts:   JSON.parse(mountEl?.getAttribute('data-pipeline-options') || '[]'),
    stageOpts:      JSON.parse(mountEl?.getAttribute('data-stage-options') || '[]'),
    sourceOpts:     JSON.parse(mountEl?.getAttribute('data-source-options') || '[]'),
    userOpts:       JSON.parse(mountEl?.getAttribute('data-user-options') || '[]'),
    creatorOpts:    JSON.parse(mountEl?.getAttribute('data-creator-options') || '[]'),
    deptOpts:       JSON.parse(mountEl?.getAttribute('data-dept-options') || '[]'),
    teamOpts:       JSON.parse(mountEl?.getAttribute('data-team-options') || '[]'),
    canCreate:      mountEl?.getAttribute('data-can-create') === '1',
    canEdit:        mountEl?.getAttribute('data-can-edit') === '1',
    canDelete:      mountEl?.getAttribute('data-can-delete') === '1',
    createUrl:      mountEl?.getAttribute('data-create-url') || '#',
    csrf:           mountEl?.getAttribute('data-csrf') || '',
  };

  // ── State ──────────────────────────────────────────────────────────────────
  const [data, setData]               = useState([]);
  const [loading, setLoading]         = useState(true);
  const [statsLoading, setStatsLoading] = useState(true);
  const [stats, setStats]             = useState({});
  const [total, setTotal]             = useState(0);
  const [page, setPage]               = useState(1);
  const [perPage, setPerPage]         = useState(25);
  const [sortField, setSortField]     = useState('created_at');
  const [sortDir, setSortDir]         = useState('descend');
  const [search, setSearch]           = useState('');
  const [searchInput, setSearchInput] = useState('');

  // Pipeline
  const [pipelineId, setPipelineId]   = useState(cfg.pipelineId);

  // Filters (applied)
  const [filters, setFilters]         = useState({});

  // Filter Drawer state
  const [filterOpen, setFilterOpen]   = useState(false);
  const [draftFilters, setDraftFilters] = useState({});

  // Column visibility
  const [colVisible, setColVisible]   = useState({
    subject: true, stage: true, tasks: true, reminders: true,
    owner: true, created_at: true, updated_at: false,
    email: false, phone: false, follow_up: false,
  });
  const [colDrawerOpen, setColDrawerOpen] = useState(false);

  // Selected rows (bulk)
  const [selectedRowKeys, setSelectedRowKeys] = useState([]);

  // Detail drawer
  const [detailOpen, setDetailOpen]   = useState(false);
  const [detailLeadId, setDetailLeadId] = useState(null);

  const searchDebounce = useRef(null);

  // ── Fetch ──────────────────────────────────────────────────────────────────
  const fetchData = useCallback(async (overrides = {}) => {
    setLoading(true);
    if (overrides.page === undefined) setStatsLoading(true);
    try {
      const params = new URLSearchParams();
      const p = { pipelineId, search, page, perPage, sortField, sortDir, ...filters, ...overrides };

      params.set('pipeline_id', p.pipelineId || pipelineId);
      params.set('search', p.search ?? search);
      params.set('page', p.page ?? page);
      params.set('per_page', p.perPage ?? perPage);
      params.set('sort_field', p.sortField ?? sortField);
      params.set('sort_dir', p.sortDir ?? sortDir);

      const appliedFilters = { ...filters, ...overrides };
      if (appliedFilters.stage_id?.length)           appliedFilters.stage_id.forEach(v => params.append('stage_id[]', v));
      if (appliedFilters.source_id?.length)          appliedFilters.source_id.forEach(v => params.append('source_id[]', v));
      if (appliedFilters.responsible_person?.length) appliedFilters.responsible_person.forEach(v => params.append('responsible_person[]', v));
      if (appliedFilters.created_by?.length)         appliedFilters.created_by.forEach(v => params.append('created_by[]', v));
      if (appliedFilters.start_date)                 params.set('start_date', appliedFilters.start_date);
      if (appliedFilters.end_date)                   params.set('end_date', appliedFilters.end_date);
      if (appliedFilters.modified_start_date)        params.set('modified_start_date', appliedFilters.modified_start_date);
      if (appliedFilters.modified_end_date)          params.set('modified_end_date', appliedFilters.modified_end_date);
      if (appliedFilters.duplicates)                 params.set('duplicates', '1');

      const res  = await fetch(`/leads-list-json?${params.toString()}`);
      const json = await res.json();

      if (json.success) {
        setData(json.data);
        setTotal(json.total);
        if (json.stats) { setStats(json.stats); setStatsLoading(false); }
      }
    } catch (e) {
      console.error('LeadsList fetch error:', e);
    } finally {
      setLoading(false);
    }
  }, [pipelineId, search, page, perPage, sortField, sortDir, filters]);

  useEffect(() => { fetchData(); }, [pipelineId, search, page, perPage, sortField, sortDir, filters]);

  // ── Handlers ───────────────────────────────────────────────────────────────
  const handleSearch = (val) => {
    clearTimeout(searchDebounce.current);
    searchDebounce.current = setTimeout(() => {
      setSearch(val);
      setPage(1);
    }, 400);
  };

  const handleTableChange = (pagination, _, sorter) => {
    setPage(pagination.current);
    setPerPage(pagination.pageSize);
    if (sorter?.field) {
      setSortField(sorter.field);
      setSortDir(sorter.order || 'descend');
    }
  };

  const openDetail = (leadId) => {
    setDetailLeadId(leadId);
    setDetailOpen(true);
  };

  const applyFilters = () => {
    setFilters({ ...draftFilters });
    setPage(1);
    setFilterOpen(false);
  };

  const resetFilters = () => {
    setDraftFilters({});
    setFilters({});
    setPage(1);
    setFilterOpen(false);
  };

  const activeFilterCount = Object.values(filters).filter(v => v && (Array.isArray(v) ? v.length > 0 : true)).length;

  const handleDelete = async (lead) => {
    try {
      const form = new FormData();
      form.append('_token', cfg.csrf);
      form.append('_method', 'DELETE');
      const res = await fetch(lead.delete_url, { method: 'POST', body: form });
      const json = await res.json();
      if (json.is_success) {
        fetchData();
        if (window.show_toastr) window.show_toastr('Deleted', `Lead "${lead.name}" deleted.`, 'success');
      }
    } catch (e) { console.error(e); }
  };

  const handleExport = (type) => {
    const params = new URLSearchParams();
    params.set('pipeline_id', pipelineId);
    params.set('action', type);
    if (selectedRowKeys.length) params.set('export_selected_ids', selectedRowKeys.join(','));
    window.open(`/leads-list?${params.toString()}`, '_blank');
  };

  // ── Columns ─────────────────────────────────────────────────────────────────
  const columns = [
    {
      title: '#',
      dataIndex: 'DT_RowIndex',
      width: 52,
      render: (_, __, idx) => (
        <span style={{ fontSize: '0.72rem', color: '#94a3b8', fontWeight: 700 }}>
          {(page - 1) * perPage + idx + 1}
        </span>
      ),
    },
    {
      title: 'Name',
      dataIndex: 'name',
      key: 'name',
      sorter: true,
      render: (name, row) => (
        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
          <NameAvatar name={name} />
          <div style={{ minWidth: 0 }}>
            <div
              style={{ fontWeight: 700, color: '#0f172a', fontSize: '0.82rem', cursor: 'pointer', whiteSpace: 'nowrap' }}
              onClick={() => openDetail(row.id)}
            >
              {name}
            </div>
            {!row.is_active && (
              <Tag color="default" style={{ fontSize: '0.6rem', lineHeight: '14px', borderRadius: 4, padding: '0 5px' }}>
                Inactive
              </Tag>
            )}
          </div>
        </div>
      ),
    },
    colVisible.subject && {
      title: 'Subject',
      dataIndex: 'subject',
      key: 'subject',
      sorter: true,
      render: v => <Text type="secondary" style={{ fontSize: '0.78rem' }}>{v || '—'}</Text>,
    },
    colVisible.email && {
      title: 'Email',
      dataIndex: 'email',
      key: 'email',
      render: v => v ? (
        <a href={`mailto:${v}`} style={{ fontSize: '0.78rem', color: '#6366f1' }}>
          <MailOutlined style={{ marginRight: 4 }} />{v}
        </a>
      ) : <Text type="secondary">—</Text>,
    },
    colVisible.phone && {
      title: 'Phone',
      dataIndex: 'phone',
      key: 'phone',
      render: (v, row) => v ? (
        <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
          <span style={{ fontSize: '0.78rem' }}>{v}</span>
          <Tooltip title="Call">
            <a href="javascript:void(0)" className="click-to-call" data-phone={v}
              style={{ color: '#059669', fontSize: 13 }}>
              <PhoneOutlined />
            </a>
          </Tooltip>
          <Tooltip title="WhatsApp">
            <a href={`/whatsapp-chats?lead_id=${row.id}`}
              style={{ color: '#25d366', fontSize: 13 }}>
              <i className="ti ti-brand-whatsapp" />
            </a>
          </Tooltip>
        </div>
      ) : <Text type="secondary">—</Text>,
    },
    colVisible.stage && {
      title: 'Stage',
      dataIndex: 'stage_name',
      key: 'stage_id',
      width: 160,
      render: (stageName, row) => (
        <div style={{ minWidth: 130 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 6, marginBottom: 5 }}>
            <span style={{ width: 8, height: 8, borderRadius: '50%', background: row.stage_color, display: 'inline-block', flexShrink: 0 }} />
            <span style={{ fontSize: '0.75rem', fontWeight: 700, color: '#1e293b', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', maxWidth: 110 }}>
              {stageName}
            </span>
          </div>
          <Progress
            percent={row.stage_progress}
            showInfo={false}
            strokeColor={row.stage_color}
            trailColor="#f1f5f9"
            strokeWidth={4}
            style={{ margin: 0 }}
          />
        </div>
      ),
    },
    colVisible.tasks && {
      title: 'Tasks',
      key: 'tasks',
      width: 80,
      render: (_, row) => {
        const done = row.tasks_done || 0;
        const total = row.tasks_total || 0;
        const color = done === total && total > 0 ? '#059669' : total > 0 ? '#f59e0b' : '#94a3b8';
        return (
          <Tooltip title={`${done} done / ${total} total`}>
            <Badge
              count={`${done}/${total}`}
              style={{ background: `${color}18`, color, border: 'none', fontWeight: 700, fontSize: '0.68rem' }}
            />
          </Tooltip>
        );
      },
    },
    colVisible.reminders && {
      title: 'Reminders',
      key: 'reminders',
      width: 90,
      render: (_, row) => {
        const today = row.reminders_today || 0;
        const total = row.reminders_total || 0;
        return (
          <Tooltip title={`${today} today / ${total} total`}>
            <span style={{ display: 'flex', alignItems: 'center', gap: 4, fontSize: '0.75rem', fontWeight: 700, color: today > 0 ? '#ef4444' : '#64748b' }}>
              <BellOutlined style={{ fontSize: 12 }} />
              {today}/{total}
            </span>
          </Tooltip>
        );
      },
    },
    colVisible.owner && {
      title: 'Owner',
      key: 'user_id',
      render: (_, row) => row.owner_name !== '-' ? (
        <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
          <div style={{
            background: 'linear-gradient(135deg,#6366f1,#4f46e5)',
            color: '#fff', borderRadius: 6, padding: '2px 8px',
            fontSize: '0.72rem', fontWeight: 700, display: 'inline-flex', alignItems: 'center', gap: 4
          }}>
            <UserOutlined style={{ fontSize: 10 }} />
            {row.owner_name}
          </div>
        </div>
      ) : <Text type="secondary" style={{ fontSize: '0.75rem' }}>—</Text>,
    },
    colVisible.created_at && {
      title: 'Created',
      dataIndex: 'created_at',
      key: 'created_at',
      sorter: true,
      width: 110,
      render: v => (
        <span style={{ fontSize: '0.72rem', color: '#475569', display: 'flex', alignItems: 'center', gap: 4 }}>
          <CalendarOutlined style={{ color: '#6366f1', fontSize: 11 }} />
          {v}
        </span>
      ),
    },
    colVisible.updated_at && {
      title: 'Modified',
      dataIndex: 'updated_at',
      key: 'updated_at',
      sorter: true,
      width: 110,
      render: v => (
        <span style={{ fontSize: '0.72rem', color: '#475569', display: 'flex', alignItems: 'center', gap: 4 }}>
          <CalendarOutlined style={{ color: '#f59e0b', fontSize: 11 }} />
          {v}
        </span>
      ),
    },
    colVisible.follow_up && {
      title: 'Follow Up',
      dataIndex: 'follow_up_date',
      key: 'follow_up_date',
      sorter: true,
      width: 110,
      render: v => v ? (
        <span style={{ fontSize: '0.72rem', color: '#ec4899', fontWeight: 700 }}>
          <CalendarOutlined style={{ marginRight: 4 }} />{v}
        </span>
      ) : <Text type="secondary">—</Text>,
    },
    // Action column
    (cfg.canEdit || cfg.canDelete) && {
      title: '',
      key: 'action',
      fixed: 'right',
      width: 110,
      render: (_, row) => (
        <Space size={4}>
          <Tooltip title="View Details">
            <Button
              type="text" size="small" icon={<EyeOutlined />}
              style={{ color: '#6366f1', borderRadius: 7 }}
              onClick={() => openDetail(row.id)}
            />
          </Tooltip>
          {row.can_show && (
            <Tooltip title="Full Page">
              <Button
                type="text" size="small"
                icon={<i className="ti ti-external-link" style={{ fontSize: 13 }} />}
                style={{ color: '#059669', borderRadius: 7 }}
                onClick={() => window.open(row.show_url, '_blank')}
              />
            </Tooltip>
          )}
          {cfg.canEdit && row.can_edit && (
            <Tooltip title="Edit">
              <Button
                type="text" size="small" icon={<EditOutlined />}
                style={{ color: '#f59e0b', borderRadius: 7 }}
                onClick={() => window.location.href = row.edit_url}
              />
            </Tooltip>
          )}
          {cfg.canDelete && row.can_delete && (
            <Popconfirm
              title="Delete Lead"
              description="This action cannot be undone."
              onConfirm={() => handleDelete(row)}
              okText="Delete"
              cancelText="Cancel"
              okButtonProps={{ danger: true }}
            >
              <Tooltip title="Delete">
                <Button type="text" size="small" danger icon={<DeleteOutlined />} style={{ borderRadius: 7 }} />
              </Tooltip>
            </Popconfirm>
          )}
        </Space>
      ),
    },
  ].filter(Boolean);

  // ── Active filter pills ────────────────────────────────────────────────────
  const filterPills = [];
  if (filters.stage_id?.length) filterPills.push({ key: 'stage_id', label: `Stage (${filters.stage_id.length})` });
  if (filters.source_id?.length) filterPills.push({ key: 'source_id', label: `Source (${filters.source_id.length})` });
  if (filters.responsible_person?.length) filterPills.push({ key: 'responsible_person', label: `Owner (${filters.responsible_person.length})` });
  if (filters.start_date) filterPills.push({ key: 'dates', label: `Date: ${filters.start_date} ~ ${filters.end_date || '…'}` });
  if (filters.duplicates) filterPills.push({ key: 'duplicates', label: 'Duplicates only' });

  const removeFilter = (key) => {
    const next = { ...filters };
    if (key === 'dates') { delete next.start_date; delete next.end_date; }
    else delete next[key];
    setFilters(next);
    setPage(1);
  };

  // ── Render ─────────────────────────────────────────────────────────────────
  return (
    <ConfigProvider theme={{
      token: {
        colorPrimary: '#059669',
        borderRadius: 10,
        fontFamily: "'Plus Jakarta Sans', -apple-system, sans-serif",
        colorBgContainer: '#ffffff',
      }
    }}>
      <div style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}>

        {/* ── KPI Stats ─────────────────────────────────────────────────── */}
        <Row gutter={[14, 14]} style={{ marginBottom: 20 }}>
          <Col xs={12} sm={6}>
            <StatCard title="Total Leads" value={stats.total} icon={<DatabaseOutlined />} color="#6366f1" loading={statsLoading} />
          </Col>
          <Col xs={12} sm={6}>
            <StatCard title="Active Leads" value={stats.active} icon={<CheckCircleOutlined />} color="#059669" loading={statsLoading} />
          </Col>
          <Col xs={12} sm={6}>
            <StatCard title="Reminders Today" value={stats.reminders_today} icon={<BellOutlined />} color="#f59e0b" loading={statsLoading} />
          </Col>
          <Col xs={12} sm={6}>
            <StatCard
              title="Filtered Results" value={total} icon={<TeamOutlined />} color="#ec4899" loading={loading}
              suffix={` / ${(stats.total || 0).toLocaleString()}`}
            />
          </Col>
        </Row>

        {/* ── Main Table Card ────────────────────────────────────────────── */}
        <div style={{
          background: '#fff', borderRadius: 16, border: '1px solid #e2e8f0',
          boxShadow: '0 1px 3px rgba(0,0,0,0.05), 0 4px 16px rgba(0,0,0,0.04)',
          overflow: 'hidden'
        }}>

          {/* ── Toolbar ───────────────────────────────────────────────────── */}
          <div style={{
            padding: '14px 18px', borderBottom: '1px solid #f1f5f9',
            display: 'flex', alignItems: 'center', gap: 10, flexWrap: 'wrap',
            background: 'linear-gradient(135deg, #fafafa, #f8fafc)'
          }}>
            {/* Pipeline selector */}
            {cfg.pipelineOpts.length > 1 && (
              <Select
                value={pipelineId}
                options={cfg.pipelineOpts}
                onChange={v => { setPipelineId(v); setPage(1); }}
                style={{ minWidth: 160, height: 36 }}
                size="middle"
              />
            )}

            {/* Search */}
            <Search
              placeholder="Search name, email, phone…"
              allowClear
              value={searchInput}
              onChange={e => { setSearchInput(e.target.value); handleSearch(e.target.value); }}
              style={{ width: 240, height: 36 }}
              prefix={<SearchOutlined style={{ color: '#94a3b8' }} />}
            />

            <div style={{ flex: 1 }} />

            {/* Active filter pills */}
            {filterPills.map(pill => (
              <Tag
                key={pill.key}
                closable
                onClose={() => removeFilter(pill.key)}
                style={{
                  borderRadius: 20, background: '#f0fdf4', color: '#059669',
                  border: '1px solid #d1fae5', fontWeight: 700, fontSize: '0.72rem',
                  padding: '3px 10px', cursor: 'default'
                }}
              >
                {pill.label}
              </Tag>
            ))}

            {/* Filter btn */}
            <Badge count={activeFilterCount} size="small">
              <Button
                icon={<FilterOutlined />}
                onClick={() => { setDraftFilters({ ...filters }); setFilterOpen(true); }}
                style={{
                  borderRadius: 9, height: 36, fontWeight: 700, fontSize: '0.78rem',
                  borderColor: activeFilterCount > 0 ? '#059669' : '#e2e8f0',
                  color: activeFilterCount > 0 ? '#059669' : '#475569',
                  background: activeFilterCount > 0 ? '#f0fdf4' : '#fff'
                }}
              >
                Filters
              </Button>
            </Badge>

            {/* Column visibility */}
            <Button
              icon={<SettingOutlined />}
              onClick={() => setColDrawerOpen(true)}
              style={{ borderRadius: 9, height: 36, fontWeight: 700, fontSize: '0.78rem', borderColor: '#e2e8f0', color: '#475569' }}
            >
              Columns
            </Button>

            {/* Export */}
            <Dropdown menu={{
              items: [
                { key: 'csv',   label: <span><FileTextOutlined style={{ marginRight: 8 }} />Export CSV</span>,   onClick: () => handleExport('csv') },
                { key: 'excel', label: <span><FileExcelOutlined style={{ marginRight: 8 }} />Export Excel</span>, onClick: () => handleExport('excel') },
              ]
            }} trigger={['click']}>
              <Button
                icon={<ExportOutlined />}
                style={{ borderRadius: 9, height: 36, fontWeight: 700, fontSize: '0.78rem', borderColor: '#e2e8f0', color: '#475569' }}
              >
                Export <DownOutlined style={{ fontSize: 10 }} />
              </Button>
            </Dropdown>

            {/* Refresh */}
            <Tooltip title="Refresh">
              <Button
                icon={<ReloadOutlined spin={loading} />}
                onClick={() => fetchData()}
                style={{ borderRadius: 9, height: 36, color: '#475569', borderColor: '#e2e8f0' }}
              />
            </Tooltip>

            {/* Create */}
            {cfg.canCreate && (
              <Button
                type="primary"
                icon={<PlusOutlined />}
                href={cfg.createUrl}
                style={{
                  height: 36, borderRadius: 9, fontWeight: 700, fontSize: '0.78rem',
                  background: 'linear-gradient(135deg, #059669, #047857)',
                  border: 'none', boxShadow: '0 2px 8px rgba(5,150,105,0.3)'
                }}
              >
                New Lead
              </Button>
            )}
          </div>

          {/* Bulk actions bar */}
          {selectedRowKeys.length > 0 && (
            <div style={{
              padding: '10px 18px', background: '#eff6ff', borderBottom: '1px solid #dbeafe',
              display: 'flex', alignItems: 'center', gap: 12
            }}>
              <span style={{ fontSize: '0.78rem', fontWeight: 700, color: '#2563eb' }}>
                {selectedRowKeys.length} lead{selectedRowKeys.length > 1 ? 's' : ''} selected
              </span>
              <Button size="small" onClick={() => handleExport('csv')} icon={<FileTextOutlined />}>Export Selected</Button>
              {cfg.canDelete && (
                <Popconfirm
                  title={`Delete ${selectedRowKeys.length} leads?`}
                  onConfirm={() => { /* bulk delete TODO */ setSelectedRowKeys([]); }}
                  okText="Delete All" okButtonProps={{ danger: true }}
                >
                  <Button size="small" danger icon={<DeleteOutlined />}>Delete Selected</Button>
                </Popconfirm>
              )}
              <Button size="small" type="text" onClick={() => setSelectedRowKeys([])}>
                <CloseOutlined /> Clear
              </Button>
            </div>
          )}

          {/* ── Table ─────────────────────────────────────────────────────── */}
          <Table
            dataSource={data}
            columns={columns}
            rowKey="id"
            loading={{ spinning: loading, tip: 'Loading leads…' }}
            onChange={handleTableChange}
            scroll={{ x: 900 }}
            rowSelection={{
              selectedRowKeys,
              onChange: setSelectedRowKeys,
              type: 'checkbox',
              columnWidth: 44,
            }}
            onRow={(row) => ({
              style: { cursor: 'pointer' },
              onClick: () => openDetail(row.id),
            })}
            pagination={{
              current: page,
              pageSize: perPage,
              total,
              showSizeChanger: true,
              pageSizeOptions: ['10', '25', '50', '100'],
              showTotal: (tot, range) => (
                <span style={{ fontSize: '0.72rem', color: '#64748b', fontWeight: 600 }}>
                  {range[0]}–{range[1]} of {tot.toLocaleString()} leads
                </span>
              ),
              style: { padding: '12px 18px' }
            }}
            locale={{
              emptyText: (
                <Empty
                  image={Empty.PRESENTED_IMAGE_SIMPLE}
                  description={<span style={{ color: '#94a3b8', fontSize: '0.82rem', fontWeight: 600 }}>No leads found</span>}
                />
              )
            }}
            rowClassName={(row) => !row.is_active ? 'll-row-inactive' : ''}
            size="middle"
          />
        </div>

        {/* ── Filter Drawer ──────────────────────────────────────────────── */}
        <Drawer
          title={
            <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
              <FilterOutlined style={{ color: '#059669' }} />
              <span>Filter Leads</span>
              {activeFilterCount > 0 && (
                <Tag style={{ background: '#f0fdf4', color: '#059669', border: '1px solid #d1fae5', borderRadius: 20, fontWeight: 700, fontSize: '0.68rem' }}>
                  {activeFilterCount} active
                </Tag>
              )}
            </div>
          }
          open={filterOpen}
          onClose={() => setFilterOpen(false)}
          width={400}
          footer={
            <div style={{ display: 'flex', gap: 10, justifyContent: 'flex-end' }}>
              <Button onClick={resetFilters} style={{ borderRadius: 9, fontWeight: 700 }}>Reset All</Button>
              <Button
                type="primary" onClick={applyFilters}
                style={{ borderRadius: 9, fontWeight: 700, background: 'linear-gradient(135deg,#059669,#047857)', border: 'none' }}
              >
                Apply Filters
              </Button>
            </div>
          }
        >
          <div style={{ display: 'flex', flexDirection: 'column', gap: 20 }}>
            <FilterSection label="Stage">
              <Select
                mode="multiple" allowClear placeholder="All stages"
                options={cfg.stageOpts}
                value={draftFilters.stage_id || []}
                onChange={v => setDraftFilters(p => ({ ...p, stage_id: v }))}
                style={{ width: '100%' }}
              />
            </FilterSection>
            <FilterSection label="Source">
              <Select
                mode="multiple" allowClear placeholder="All sources"
                options={cfg.sourceOpts}
                value={draftFilters.source_id || []}
                onChange={v => setDraftFilters(p => ({ ...p, source_id: v }))}
                style={{ width: '100%' }}
              />
            </FilterSection>
            <FilterSection label="Responsible Person">
              <Select
                mode="multiple" allowClear placeholder="All users"
                options={cfg.userOpts}
                value={draftFilters.responsible_person || []}
                onChange={v => setDraftFilters(p => ({ ...p, responsible_person: v }))}
                style={{ width: '100%' }}
              />
            </FilterSection>
            <FilterSection label="Created By">
              <Select
                mode="multiple" allowClear placeholder="All creators"
                options={cfg.creatorOpts}
                value={draftFilters.created_by || []}
                onChange={v => setDraftFilters(p => ({ ...p, created_by: v }))}
                style={{ width: '100%' }}
              />
            </FilterSection>
            <FilterSection label="Created Date Range">
              <RangePicker
                style={{ width: '100%' }}
                value={[
                  draftFilters.start_date ? dayjs(draftFilters.start_date) : null,
                  draftFilters.end_date ? dayjs(draftFilters.end_date) : null,
                ]}
                onChange={dates => setDraftFilters(p => ({
                  ...p,
                  start_date: dates?.[0]?.format('YYYY-MM-DD') || null,
                  end_date:   dates?.[1]?.format('YYYY-MM-DD') || null,
                }))}
                allowClear
              />
            </FilterSection>
            <FilterSection label="Modified Date Range">
              <RangePicker
                style={{ width: '100%' }}
                value={[
                  draftFilters.modified_start_date ? dayjs(draftFilters.modified_start_date) : null,
                  draftFilters.modified_end_date ? dayjs(draftFilters.modified_end_date) : null,
                ]}
                onChange={dates => setDraftFilters(p => ({
                  ...p,
                  modified_start_date: dates?.[0]?.format('YYYY-MM-DD') || null,
                  modified_end_date:   dates?.[1]?.format('YYYY-MM-DD') || null,
                }))}
                allowClear
              />
            </FilterSection>
            {cfg.deptOpts.length > 0 && (
              <FilterSection label="Department">
                <Select
                  mode="multiple" allowClear placeholder="All departments"
                  options={cfg.deptOpts}
                  value={draftFilters.department_id || []}
                  onChange={v => setDraftFilters(p => ({ ...p, department_id: v }))}
                  style={{ width: '100%' }}
                />
              </FilterSection>
            )}
            <FilterSection label="Show Duplicates Only">
              <Switch
                checked={!!draftFilters.duplicates}
                onChange={v => setDraftFilters(p => ({ ...p, duplicates: v ? 1 : 0 }))}
                checkedChildren="Yes" unCheckedChildren="No"
              />
            </FilterSection>
          </div>
        </Drawer>

        {/* ── Column Visibility Drawer ───────────────────────────────────── */}
        <Drawer
          title={<span><SettingOutlined style={{ marginRight: 8, color: '#059669' }} />Column Visibility</span>}
          open={colDrawerOpen}
          onClose={() => setColDrawerOpen(false)}
          width={280}
        >
          <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
            {[
              { key: 'subject',    label: 'Subject' },
              { key: 'email',      label: 'Email' },
              { key: 'phone',      label: 'Phone' },
              { key: 'stage',      label: 'Stage' },
              { key: 'tasks',      label: 'Tasks' },
              { key: 'reminders',  label: 'Reminders' },
              { key: 'owner',      label: 'Owner' },
              { key: 'created_at', label: 'Created Date' },
              { key: 'updated_at', label: 'Modified Date' },
              { key: 'follow_up',  label: 'Follow Up Date' },
            ].map(col => (
              <div key={col.key} style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '8px 12px', borderRadius: 10, background: colVisible[col.key] ? '#f0fdf4' : '#f8fafc', border: '1px solid #e2e8f0' }}>
                <span style={{ fontSize: '0.82rem', fontWeight: 600, color: '#475569' }}>{col.label}</span>
                <Switch
                  size="small"
                  checked={colVisible[col.key]}
                  onChange={v => setColVisible(p => ({ ...p, [col.key]: v }))}
                />
              </div>
            ))}
          </div>
        </Drawer>

        {/* ── Lead Details Drawer ─────────────────────────────────────────── */}
        {detailOpen && detailLeadId && (
          <div
            style={{
              position: 'fixed', inset: 0, zIndex: 1100,
              background: 'rgba(15,23,42,0.45)', backdropFilter: 'blur(5px)',
              display: 'flex', justifyContent: 'flex-end'
            }}
            onClick={() => setDetailOpen(false)}
          >
            <div
              style={{
                width: 870, maxWidth: '96vw', height: '100%',
                background: '#f1f5f9', display: 'flex', flexDirection: 'column',
                boxShadow: '-12px 0 40px rgba(15,23,42,0.18)',
                animation: 'llSlideIn 0.28s cubic-bezier(0.25,0.8,0.25,1)'
              }}
              onClick={e => e.stopPropagation()}
            >
              {/* Drawer Header */}
              <div style={{
                background: '#fff', borderBottom: '1px solid #e2e8f0',
                padding: '10px 16px', display: 'flex', alignItems: 'center', gap: 10, flexShrink: 0
              }}>
                <div style={{ flex: 1, fontSize: '0.9rem', fontWeight: 800, color: '#0f172a' }}>
                  Lead Details
                </div>
                <Button
                  type="text" size="small"
                  icon={<i className="ti ti-arrows-maximize" style={{ fontSize: 13 }} />}
                  style={{ borderRadius: 8, color: '#059669', fontWeight: 700, fontSize: '0.72rem' }}
                  onClick={() => window.open(`/leads/${detailLeadId}/details`, '_blank')}
                >
                  Full Page
                </Button>
                <Button
                  type="text" size="small" danger
                  icon={<CloseOutlined />}
                  style={{ borderRadius: 8 }}
                  onClick={() => { setDetailOpen(false); fetchData(); }}
                />
              </div>
              {/* React LeadDetails Component */}
              <div style={{ flex: 1, overflow: 'hidden', minHeight: 0 }}>
                <LeadDetails
                  leadId={detailLeadId}
                  onClose={() => { setDetailOpen(false); fetchData(); }}
                />
              </div>
            </div>
          </div>
        )}

        {/* ── Styles ─────────────────────────────────────────────────────── */}
        <style>{`
          @keyframes llSlideIn {
            from { transform: translateX(100%); opacity: 0; }
            to   { transform: translateX(0);    opacity: 1; }
          }
          @keyframes llPulse {
            0%,100% { opacity: 0.5; }
            50%      { opacity: 1; }
          }
          .ll-row-inactive td { opacity: 0.55; }
          .ant-table-row:hover td { background: #f0fdf4 !important; }
          .ant-table-thead > tr > th {
            background: #fafafa !important;
            font-size: 0.7rem !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.6px !important;
            color: #94a3b8 !important;
            border-bottom: 1px solid #f1f5f9 !important;
          }
          .ant-table-cell { font-size: 0.82rem !important; padding: 10px 14px !important; }
          .ant-pagination-item-active { border-color: #059669 !important; }
          .ant-pagination-item-active a { color: #059669 !important; }
          .ant-btn-primary:hover { opacity: 0.9; }
          .ant-select-selector { border-radius: 9px !important; }
          .ant-input-affix-wrapper { border-radius: 9px !important; }
        `}</style>
      </div>
    </ConfigProvider>
  );
}

// ── Helper: Filter Section Label ─────────────────────────────────────────────
function FilterSection({ label, children }) {
  return (
    <div>
      <div style={{
        fontSize: '0.65rem', fontWeight: 800, color: '#94a3b8',
        textTransform: 'uppercase', letterSpacing: '0.8px', marginBottom: 8
      }}>{label}</div>
      {children}
    </div>
  );
}
