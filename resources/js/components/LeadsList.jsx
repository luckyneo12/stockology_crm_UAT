import React, { useState, useEffect, useRef, useCallback } from 'react';
import {
  MantineProvider, createTheme, Table, Checkbox, Badge, Progress,
  Tooltip, ActionIcon, Menu, Button, Text, TextInput, Select, MultiSelect,
  Drawer, Group, Stack, Flex, Paper, Switch, Popover, Loader, Pagination
} from '@mantine/core';
import { DatePickerInput } from '@mantine/dates';
import {
  Search, Filter, RotateCw, Plus, Download, ChevronDown, Settings, Eye,
  ExternalLink, Edit, Trash2, Mail, Phone, User, Calendar, Bell, Database,
  Users, CheckCircle2, X, ChevronUp, FileText, FileSpreadsheet
} from 'lucide-react';
import dayjs from 'dayjs';
import LeadDetails from './LeadDetails';

import '@mantine/core/styles.css';
import '@mantine/dates/styles.css';

// ─── Theme Configuration ──────────────────────────────────────────────────
const theme = createTheme({
  primaryColor: 'emerald',
  colors: {
    emerald: [
      '#ecfdf5',
      '#d1fae5',
      '#a7f3d0',
      '#6ee7b7',
      '#34d399',
      '#10b981',
      '#059669', // Primary green
      '#047857',
      '#065f46',
      '#064e3b',
    ],
  },
  fontFamily: "'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
});

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
    <Paper
      p="md"
      className="ll-stat-card"
      style={{
        borderRadius: 14, border: 'none',
        boxShadow: `0 1px 3px rgba(0,0,0,0.06), 0 4px 16px ${color}18`,
        background: '#fff', overflow: 'hidden', cursor: 'default',
        width: '100%'
      }}
    >
      <Flex align="center" justify="space-between">
        <div>
          <Text size="xs" fw={700} c="dimmed" style={{ textTransform: 'uppercase', letterSpacing: '0.6px', marginBottom: 4 }}>
            {title}
          </Text>
          {loading ? (
            <div style={{ width: 60, height: 28, borderRadius: 6, background: '#f1f5f9', animation: 'llPulse 1.2s infinite' }} />
          ) : (
            <Text size="xl" fw={800} color="#0f172a" style={{ lineHeight: 1.1 }}>
              {value?.toLocaleString()}<span style={{ fontSize: '0.85rem', color: '#94a3b8', fontWeight: 500 }}>{suffix}</span>
            </Text>
          )}
        </div>
        <div style={{
          width: 44, height: 44, borderRadius: 12,
          background: `${color}15`,
          display: 'flex', alignItems: 'center', justifyContent: 'center',
          fontSize: 20, color
        }}>
          {icon}
        </div>
      </Flex>
    </Paper>
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
  const [lastPage, setLastPage]       = useState(1);
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

  // Delete popover state
  const [deletingRowId, setDeletingRowId] = useState(null);

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
        if (json.last_page) setLastPage(json.last_page);
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

  const handleHeaderClick = (field) => {
    if (sortField === field) {
      setSortDir(prev => prev === 'ascend' ? 'descend' : 'ascend');
    } else {
      setSortField(field);
      setSortDir('descend');
    }
    setPage(1);
  };

  const renderSortIcon = (field) => {
    if (sortField !== field) return null;
    return sortDir === 'ascend' ? (
      <ChevronUp size={13} style={{ marginLeft: 4, display: 'inline-block', verticalAlign: 'middle' }} />
    ) : (
      <ChevronDown size={13} style={{ marginLeft: 4, display: 'inline-block', verticalAlign: 'middle' }} />
    );
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

  // Column vis meta
  const columnMetadata = [
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
  ];

  const columnsCount = 3 +
    (colVisible.subject ? 1 : 0) +
    (colVisible.email ? 1 : 0) +
    (colVisible.phone ? 1 : 0) +
    (colVisible.stage ? 1 : 0) +
    (colVisible.tasks ? 1 : 0) +
    (colVisible.reminders ? 1 : 0) +
    (colVisible.owner ? 1 : 0) +
    (colVisible.created_at ? 1 : 0) +
    (colVisible.updated_at ? 1 : 0) +
    (colVisible.follow_up ? 1 : 0) +
    ((cfg.canEdit || cfg.canDelete) ? 1 : 0);

  // ── Render ─────────────────────────────────────────────────────────────────
  return (
    <MantineProvider theme={theme}>
      <div style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}>

        {/* ── KPI Stats ─────────────────────────────────────────────────── */}
        <Flex gap="md" style={{ marginBottom: 20, flexWrap: 'wrap' }}>
          <div style={{ flex: '1 1 200px', display: 'flex' }}>
            <StatCard title="Total Leads" value={stats.total} icon={<Database size={20} />} color="#6366f1" loading={statsLoading} />
          </div>
          <div style={{ flex: '1 1 200px', display: 'flex' }}>
            <StatCard title="Active Leads" value={stats.active} icon={<CheckCircle2 size={20} />} color="#059669" loading={statsLoading} />
          </div>
          <div style={{ flex: '1 1 200px', display: 'flex' }}>
            <StatCard title="Reminders Today" value={stats.reminders_today} icon={<Bell size={20} />} color="#f59e0b" loading={statsLoading} />
          </div>
          <div style={{ flex: '1 1 200px', display: 'flex' }}>
            <StatCard
              title="Filtered Results" value={total} icon={<Users size={20} />} color="#ec4899" loading={loading}
              suffix={` / ${(stats.total || 0).toLocaleString()}`}
            />
          </div>
        </Flex>

        {/* ── Main Table Card ────────────────────────────────────────────── */}
        <div style={{
          position: 'relative',
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
                data={cfg.pipelineOpts}
                onChange={v => { setPipelineId(v); setPage(1); }}
                style={{ minWidth: 160 }}
                size="sm"
              />
            )}

            {/* Search */}
            <TextInput
              placeholder="Search name, email, phone…"
              value={searchInput}
              onChange={e => { setSearchInput(e.target.value); handleSearch(e.target.value); }}
              style={{ width: 240 }}
              leftSection={<Search size={16} style={{ color: '#94a3b8' }} />}
              rightSection={
                searchInput ? (
                  <ActionIcon variant="transparent" color="gray" onClick={() => { setSearchInput(''); handleSearch(''); }}>
                    <X size={14} />
                  </ActionIcon>
                ) : null
              }
              size="sm"
            />

            <div style={{ flex: 1 }} />

            {/* Active filter pills */}
            {filterPills.map(pill => (
              <Badge
                key={pill.key}
                variant="light"
                color="teal"
                size="md"
                style={{
                  borderRadius: 20, fontWeight: 700, fontSize: '0.72rem',
                  textTransform: 'none', padding: '12px 10px', height: 'auto',
                  cursor: 'default'
                }}
                rightSection={
                  <X
                    size={11}
                    style={{ cursor: 'pointer' }}
                    onClick={() => removeFilter(pill.key)}
                  />
                }
              >
                {pill.label}
              </Badge>
            ))}

            {/* Filter btn */}
            <div style={{ position: 'relative' }}>
              <Button
                leftSection={<Filter size={15} />}
                onClick={() => { setDraftFilters({ ...filters }); setFilterOpen(true); }}
                variant={activeFilterCount > 0 ? 'light' : 'default'}
                color="emerald"
                size="sm"
                style={{
                  borderRadius: 9, fontWeight: 700, fontSize: '0.78rem',
                  height: 36
                }}
              >
                Filters
                {activeFilterCount > 0 && (
                  <span style={{
                    marginLeft: 6, background: '#10b981', color: '#fff',
                    borderRadius: '50%', width: 18, height: 18,
                    display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
                    fontSize: '0.65rem', fontWeight: 800
                  }}>
                    {activeFilterCount}
                  </span>
                )}
              </Button>
            </div>

            {/* Column visibility */}
            <Button
              leftSection={<Settings size={15} />}
              onClick={() => setColDrawerOpen(true)}
              variant="default"
              size="sm"
              style={{ borderRadius: 9, height: 36, fontWeight: 700, fontSize: '0.78rem', color: '#475569' }}
            >
              Columns
            </Button>

            {/* Export */}
            <Menu shadow="md" width={160} position="bottom-end">
              <Menu.Target>
                <Button
                  leftSection={<Download size={15} />}
                  rightSection={<ChevronDown size={12} />}
                  variant="default"
                  size="sm"
                  style={{ borderRadius: 9, height: 36, fontWeight: 700, fontSize: '0.78rem', color: '#475569' }}
                >
                  Export
                </Button>
              </Menu.Target>
              <Menu.Dropdown>
                <Menu.Item leftSection={<FileText size={14} />} onClick={() => handleExport('csv')}>
                  Export CSV
                </Menu.Item>
                <Menu.Item leftSection={<FileSpreadsheet size={14} />} onClick={() => handleExport('excel')}>
                  Export Excel
                </Menu.Item>
              </Menu.Dropdown>
            </Menu>

            {/* Refresh */}
            <Tooltip label="Refresh">
              <ActionIcon
                variant="default"
                onClick={() => fetchData()}
                style={{ borderRadius: 9, height: 36, width: 36, color: '#475569' }}
              >
                <RotateCw size={16} className={loading ? 'll-spin-refresh' : ''} />
              </ActionIcon>
            </Tooltip>

            {/* Create */}
            {cfg.canCreate && (
              <Button
                component="a"
                leftSection={<Plus size={15} />}
                href={cfg.createUrl}
                color="emerald"
                size="sm"
                style={{
                  height: 36, borderRadius: 9, fontWeight: 700, fontSize: '0.78rem',
                  boxShadow: '0 2px 8px rgba(5,150,105,0.3)'
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
              <Button size="xs" variant="default" onClick={() => handleExport('csv')} leftSection={<FileText size={12} />}>
                Export Selected
              </Button>
              {cfg.canDelete && (
                <Popover width={240} position="bottom" withArrow shadow="md">
                  <Popover.Target>
                    <Button size="xs" color="red" variant="light" leftSection={<Trash2 size={12} />}>
                      Delete Selected
                    </Button>
                  </Popover.Target>
                  <Popover.Dropdown>
                    <Text size="xs" fw={700} mb={4}>Bulk Delete Leads</Text>
                    <Text size="xs" c="dimmed" mb={8}>Delete {selectedRowKeys.length} leads? This action cannot be undone.</Text>
                    <Group gap="xs" justify="flex-end">
                      <Button size="xs" variant="default">Cancel</Button>
                      <Button size="xs" color="red" onClick={() => { setSelectedRowKeys([]); }}>Delete All</Button>
                    </Group>
                  </Popover.Dropdown>
                </Popover>
              )}
              <Button size="xs" variant="transparent" onClick={() => setSelectedRowKeys([])} leftSection={<X size={12} />}>
                Clear
              </Button>
            </div>
          )}

          {/* ── Table Loading Overlay ────────────────────────────────────────── */}
          {loading && (
            <div style={{
              position: 'absolute', inset: '60px 0 0 0', background: 'rgba(255,255,255,0.7)',
              zIndex: 10, display: 'flex', alignItems: 'center', justifyContent: 'center',
              backdropFilter: 'blur(1px)'
            }}>
              <Group gap="xs">
                <Loader size="sm" color="emerald" />
                <Text size="sm" fw={600} c="emerald.6">Loading leads…</Text>
              </Group>
            </div>
          )}

          {/* ── Table ─────────────────────────────────────────────────────── */}
          <div style={{ overflowX: 'auto' }}>
            <Table verticalSpacing="sm" highlightOnHover style={{ minWidth: 900 }}>
              <Table.Thead className="ll-table-thead">
                <Table.Tr>
                  <Table.Th style={{ width: 44 }}>
                    <Checkbox
                      checked={data.length > 0 && data.every(row => selectedRowKeys.includes(row.id))}
                      indeterminate={data.some(row => selectedRowKeys.includes(row.id)) && !data.every(row => selectedRowKeys.includes(row.id))}
                      onChange={(event) => {
                        const checked = event.currentTarget.checked;
                        if (checked) {
                          setSelectedRowKeys(prev => {
                            const newKeys = [...prev];
                            data.forEach(row => {
                              if (!newKeys.includes(row.id)) newKeys.push(row.id);
                            });
                            return newKeys;
                          });
                        } else {
                          setSelectedRowKeys(prev => prev.filter(id => !data.some(row => row.id === id)));
                        }
                      }}
                    />
                  </Table.Th>
                  <Table.Th style={{ width: 52 }}>#</Table.Th>
                  <Table.Th onClick={() => handleHeaderClick('name')} style={{ cursor: 'pointer', userSelect: 'none' }}>
                    Name {renderSortIcon('name')}
                  </Table.Th>
                  {colVisible.subject && (
                    <Table.Th onClick={() => handleHeaderClick('subject')} style={{ cursor: 'pointer', userSelect: 'none' }}>
                      Subject {renderSortIcon('subject')}
                    </Table.Th>
                  )}
                  {colVisible.email && <Table.Th>Email</Table.Th>}
                  {colVisible.phone && <Table.Th>Phone</Table.Th>}
                  {colVisible.stage && <Table.Th>Stage</Table.Th>}
                  {colVisible.tasks && <Table.Th>Tasks</Table.Th>}
                  {colVisible.reminders && <Table.Th>Reminders</Table.Th>}
                  {colVisible.owner && <Table.Th>Owner</Table.Th>}
                  {colVisible.created_at && (
                    <Table.Th onClick={() => handleHeaderClick('created_at')} style={{ cursor: 'pointer', userSelect: 'none' }}>
                      Created {renderSortIcon('created_at')}
                    </Table.Th>
                  )}
                  {colVisible.updated_at && (
                    <Table.Th onClick={() => handleHeaderClick('updated_at')} style={{ cursor: 'pointer', userSelect: 'none' }}>
                      Modified {renderSortIcon('updated_at')}
                    </Table.Th>
                  )}
                  {colVisible.follow_up && (
                    <Table.Th onClick={() => handleHeaderClick('follow_up_date')} style={{ cursor: 'pointer', userSelect: 'none' }}>
                      Follow Up {renderSortIcon('follow_up_date')}
                    </Table.Th>
                  )}
                  {(cfg.canEdit || cfg.canDelete) && <Table.Th style={{ width: 110 }} />}
                </Table.Tr>
              </Table.Thead>

              <Table.Tbody>
                {data.map((row, idx) => (
                  <Table.Tr
                    key={row.id}
                    className={`ll-table-row ${!row.is_active ? 'll-row-inactive' : ''}`}
                    style={{ cursor: 'pointer' }}
                    onClick={(e) => {
                      // Avoid opening details if clicking interactive controls
                      if (e.target.closest('input, button, a, .click-to-call, .mantine-Menu-target, .mantine-Popover-dropdown, .mantine-Popover-target')) {
                        return;
                      }
                      openDetail(row.id);
                    }}
                  >
                    <Table.Td onClick={e => e.stopPropagation()}>
                      <Checkbox
                        checked={selectedRowKeys.includes(row.id)}
                        onChange={(event) => {
                          const checked = event.currentTarget.checked;
                          setSelectedRowKeys(prev => checked ? [...prev, row.id] : prev.filter(id => id !== row.id));
                        }}
                      />
                    </Table.Td>
                    <Table.Td className="ll-table-cell">
                      <span style={{ fontSize: '0.72rem', color: '#94a3b8', fontWeight: 700 }}>
                        {(page - 1) * perPage + idx + 1}
                      </span>
                    </Table.Td>
                    <Table.Td className="ll-table-cell">
                      <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                        <NameAvatar name={row.name} />
                        <div style={{ minWidth: 0 }}>
                          <div
                            className="ll-lead-name-link"
                            style={{ fontWeight: 700, color: '#0f172a', fontSize: '0.82rem', cursor: 'pointer', whiteSpace: 'nowrap' }}
                            onClick={() => openDetail(row.id)}
                          >
                            {row.name}
                          </div>
                          {!row.is_active && (
                            <Badge size="xs" color="gray" variant="light" style={{ fontSize: '0.6rem', height: 16, padding: '0 4px', borderRadius: 4 }}>
                              Inactive
                            </Badge>
                          )}
                        </div>
                      </div>
                    </Table.Td>
                    {colVisible.subject && (
                      <Table.Td className="ll-table-cell">
                        <Text size="xs" c="dimmed">{row.subject || '—'}</Text>
                      </Table.Td>
                    )}
                    {colVisible.email && (
                      <Table.Td className="ll-table-cell">
                        {row.email ? (
                          <a href={`mailto:${row.email}`} style={{ fontSize: '0.78rem', color: '#6366f1', display: 'inline-flex', alignItems: 'center', gap: 4 }}>
                            <Mail size={12} />{row.email}
                          </a>
                        ) : <Text size="xs" c="dimmed">—</Text>}
                      </Table.Td>
                    )}
                    {colVisible.phone && (
                      <Table.Td className="ll-table-cell">
                        {row.phone ? (
                          <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                            <span style={{ fontSize: '0.78rem' }}>{row.phone}</span>
                            <Tooltip label="Call">
                              <a href="javascript:void(0)" className="click-to-call" data-phone={row.phone}
                                style={{ color: '#059669', fontSize: 13 }}>
                                <Phone size={12} style={{ verticalAlign: 'middle' }} />
                              </a>
                            </Tooltip>
                            <Tooltip label="WhatsApp">
                              <a href={`/whatsapp-chats?lead_id=${row.id}`}
                                style={{ color: '#25d366', fontSize: 13 }}>
                                <i className="ti ti-brand-whatsapp" style={{ verticalAlign: 'middle' }} />
                              </a>
                            </Tooltip>
                          </div>
                        ) : <Text size="xs" c="dimmed">—</Text>}
                      </Table.Td>
                    )}
                    {colVisible.stage && (
                      <Table.Td className="ll-table-cell">
                        <div style={{ minWidth: 130 }}>
                          <div style={{ display: 'flex', alignItems: 'center', gap: 6, marginBottom: 5 }}>
                            <span style={{ width: 8, height: 8, borderRadius: '50%', background: row.stage_color, display: 'inline-block', flexShrink: 0 }} />
                            <span style={{ fontSize: '0.75rem', fontWeight: 700, color: '#1e293b', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', maxWidth: 110 }}>
                              {row.stage_name}
                            </span>
                          </div>
                          <Progress
                            value={row.stage_progress}
                            color={row.stage_color}
                            size="xs"
                            radius="xl"
                          />
                        </div>
                      </Table.Td>
                    )}
                    {colVisible.tasks && (
                      <Table.Td className="ll-table-cell">
                        {(() => {
                          const done = row.tasks_done || 0;
                          const tot = row.tasks_total || 0;
                          const color = done === tot && tot > 0 ? 'teal' : tot > 0 ? 'orange' : 'gray';
                          return (
                            <Tooltip label={`${done} done / ${tot} total`}>
                              <Badge size="sm" color={color} variant="light" style={{ fontWeight: 700, fontSize: '0.68rem', textTransform: 'none' }}>
                                {done}/{tot}
                              </Badge>
                            </Tooltip>
                          );
                        })()}
                      </Table.Td>
                    )}
                    {colVisible.reminders && (
                      <Table.Td className="ll-table-cell">
                        {(() => {
                          const today = row.reminders_today || 0;
                          const tot = row.reminders_total || 0;
                          return (
                            <Tooltip label={`${today} today / ${tot} total`}>
                              <span style={{ display: 'flex', alignItems: 'center', gap: 4, fontSize: '0.75rem', fontWeight: 700, color: today > 0 ? '#ef4444' : '#64748b' }}>
                                <Bell size={12} />
                                {today}/{tot}
                              </span>
                            </Tooltip>
                          );
                        })()}
                      </Table.Td>
                    )}
                    {colVisible.owner && (
                      <Table.Td className="ll-table-cell">
                        {row.owner_name !== '-' ? (
                          <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                            <div style={{
                              background: 'linear-gradient(135deg,#6366f1,#4f46e5)',
                              color: '#fff', borderRadius: 6, padding: '2px 8px',
                              fontSize: '0.72rem', fontWeight: 700, display: 'inline-flex', alignItems: 'center', gap: 4
                            }}>
                              <User size={10} />
                              {row.owner_name}
                            </div>
                          </div>
                        ) : <Text size="xs" c="dimmed">—</Text>}
                      </Table.Td>
                    )}
                    {colVisible.created_at && (
                      <Table.Td className="ll-table-cell">
                        <span style={{ fontSize: '0.72rem', color: '#475569', display: 'flex', alignItems: 'center', gap: 4 }}>
                          <Calendar size={11} style={{ color: '#6366f1' }} />
                          {row.created_at}
                        </span>
                      </Table.Td>
                    )}
                    {colVisible.updated_at && (
                      <Table.Td className="ll-table-cell">
                        <span style={{ fontSize: '0.72rem', color: '#475569', display: 'flex', alignItems: 'center', gap: 4 }}>
                          <Calendar size={11} style={{ color: '#f59e0b' }} />
                          {row.updated_at}
                        </span>
                      </Table.Td>
                    )}
                    {colVisible.follow_up && (
                      <Table.Td className="ll-table-cell">
                        {row.follow_up_date ? (
                          <span style={{ fontSize: '0.72rem', color: '#ec4899', fontWeight: 700, display: 'inline-flex', alignItems: 'center', gap: 4 }}>
                            <Calendar size={11} /> {row.follow_up_date}
                          </span>
                        ) : <Text size="xs" c="dimmed">—</Text>}
                      </Table.Td>
                    )}
                    {(cfg.canEdit || cfg.canDelete) && (
                      <Table.Td className="ll-table-cell" onClick={e => e.stopPropagation()}>
                        <Group gap={4} wrap="nowrap">
                          <Tooltip label="View Details">
                            <ActionIcon
                              variant="subtle" size="sm" color="indigo"
                              onClick={() => openDetail(row.id)}
                            >
                              <Eye size={14} />
                            </ActionIcon>
                          </Tooltip>
                          {row.can_show && (
                            <Tooltip label="Full Page">
                              <ActionIcon
                                variant="subtle" size="sm" color="teal"
                                onClick={() => window.open(row.show_url, '_blank')}
                              >
                                <ExternalLink size={14} />
                              </ActionIcon>
                            </Tooltip>
                          )}
                          {cfg.canEdit && row.can_edit && (
                            <Tooltip label="Edit">
                              <ActionIcon
                                variant="subtle" size="sm" color="orange"
                                onClick={() => window.location.href = row.edit_url}
                              >
                                <Edit size={14} />
                              </ActionIcon>
                            </Tooltip>
                          )}
                          {cfg.canDelete && row.can_delete && (
                            <Popover opened={deletingRowId === row.id} onClose={() => setDeletingRowId(null)} width={220} position="bottom-end" withArrow shadow="md">
                              <Popover.Target>
                                <ActionIcon variant="subtle" size="sm" color="red" onClick={() => setDeletingRowId(row.id)}>
                                  <Trash2 size={14} />
                                </ActionIcon>
                              </Popover.Target>
                              <Popover.Dropdown onClick={e => e.stopPropagation()}>
                                <Text size="xs" fw={700} mb={4}>Delete Lead</Text>
                                <Text size="xs" c="dimmed" mb={8}>This action cannot be undone.</Text>
                                <Group gap="xs" justify="flex-end">
                                  <Button size="xs" variant="default" onClick={() => setDeletingRowId(null)}>Cancel</Button>
                                  <Button size="xs" color="red" onClick={() => { handleDelete(row); setDeletingRowId(null); }}>Delete</Button>
                                </Group>
                              </Popover.Dropdown>
                            </Popover>
                          )}
                        </Group>
                      </Table.Td>
                    )}
                  </Table.Tr>
                ))}

                {data.length === 0 && !loading && (
                  <Table.Tr>
                    <Table.Td colSpan={columnsCount} style={{ textAlign: 'center', padding: '40px 20px' }}>
                      <Stack align="center" gap="xs">
                        <Database size={32} style={{ color: '#94a3b8' }} />
                        <Text size="sm" fw={600} color="#94a3b8">No leads found</Text>
                      </Stack>
                    </Table.Td>
                  </Table.Tr>
                )}
              </Table.Tbody>
            </Table>
          </div>

          {/* Pagination Footer */}
          <div style={{
            padding: '14px 18px', borderTop: '1px solid #f1f5f9',
            display: 'flex', alignItems: 'center', justifyContent: 'space-between',
            flexWrap: 'wrap', gap: 10
          }}>
            <span style={{ fontSize: '0.72rem', color: '#64748b', fontWeight: 600 }}>
              Showing {total > 0 ? (page - 1) * perPage + 1 : 0}–{Math.min(page * perPage, total)} of {total.toLocaleString()} leads
            </span>

            <Group gap="xs">
              <span style={{ fontSize: '0.72rem', color: '#64748b', fontWeight: 600 }}>Per page:</span>
              <Select
                value={String(perPage)}
                data={['10', '25', '50', '100']}
                onChange={v => { setPerPage(Number(v)); setPage(1); }}
                style={{ width: 80 }}
                size="xs"
              />

              <Pagination
                value={page}
                onChange={setPage}
                total={lastPage}
                size="sm"
                color="teal"
              />
            </Group>
          </div>
        </div>

        {/* ── Filter Drawer ──────────────────────────────────────────────── */}
        <Drawer
          title={
            <Group gap={8}>
              <Filter size={18} style={{ color: '#059669' }} />
              <span style={{ fontWeight: 700 }}>Filter Leads</span>
              {activeFilterCount > 0 && (
                <Badge color="teal" variant="light" size="sm" circle>
                  {activeFilterCount}
                </Badge>
              )}
            </Group>
          }
          opened={filterOpen}
          onClose={() => setFilterOpen(false)}
          position="right"
          size="md"
        >
          <Stack justify="space-between" style={{ height: 'calc(100vh - 100px)' }}>
            <Stack gap="md" style={{ overflowY: 'auto', flex: 1, paddingRight: 4 }}>
              <FilterSection label="Stage">
                <MultiSelect
                  placeholder="All stages"
                  data={cfg.stageOpts}
                  value={draftFilters.stage_id || []}
                  onChange={v => setDraftFilters(p => ({ ...p, stage_id: v }))}
                  clearable
                  searchable
                />
              </FilterSection>

              <FilterSection label="Source">
                <MultiSelect
                  placeholder="All sources"
                  data={cfg.sourceOpts}
                  value={draftFilters.source_id || []}
                  onChange={v => setDraftFilters(p => ({ ...p, source_id: v }))}
                  clearable
                  searchable
                />
              </FilterSection>

              <FilterSection label="Responsible Person">
                <MultiSelect
                  placeholder="All users"
                  data={cfg.userOpts}
                  value={draftFilters.responsible_person || []}
                  onChange={v => setDraftFilters(p => ({ ...p, responsible_person: v }))}
                  clearable
                  searchable
                />
              </FilterSection>

              <FilterSection label="Created By">
                <MultiSelect
                  placeholder="All creators"
                  data={cfg.creatorOpts}
                  value={draftFilters.created_by || []}
                  onChange={v => setDraftFilters(p => ({ ...p, created_by: v }))}
                  clearable
                  searchable
                />
              </FilterSection>

              <FilterSection label="Created Date Range">
                <DatePickerInput
                  type="range"
                  placeholder="Pick date range"
                  value={[
                    draftFilters.start_date ? new Date(draftFilters.start_date) : null,
                    draftFilters.end_date ? new Date(draftFilters.end_date) : null,
                  ]}
                  onChange={dates => setDraftFilters(p => ({
                    ...p,
                    start_date: dates?.[0] ? dayjs(dates[0]).format('YYYY-MM-DD') : null,
                    end_date:   dates?.[1] ? dayjs(dates[1]).format('YYYY-MM-DD') : null,
                  }))}
                  clearable
                />
              </FilterSection>

              <FilterSection label="Modified Date Range">
                <DatePickerInput
                  type="range"
                  placeholder="Pick date range"
                  value={[
                    draftFilters.modified_start_date ? new Date(draftFilters.modified_start_date) : null,
                    draftFilters.modified_end_date ? new Date(draftFilters.modified_end_date) : null,
                  ]}
                  onChange={dates => setDraftFilters(p => ({
                    ...p,
                    modified_start_date: dates?.[0] ? dayjs(dates[0]).format('YYYY-MM-DD') : null,
                    modified_end_date:   dates?.[1] ? dayjs(dates[1]).format('YYYY-MM-DD') : null,
                  }))}
                  clearable
                />
              </FilterSection>

              {cfg.deptOpts.length > 0 && (
                <FilterSection label="Department">
                  <MultiSelect
                    placeholder="All departments"
                    data={cfg.deptOpts}
                    value={draftFilters.department_id || []}
                    onChange={v => setDraftFilters(p => ({ ...p, department_id: v }))}
                    clearable
                    searchable
                  />
                </FilterSection>
              )}

              <FilterSection label="Show Duplicates Only">
                <Switch
                  checked={!!draftFilters.duplicates}
                  onChange={event => setDraftFilters(p => ({ ...p, duplicates: event.currentTarget.checked ? 1 : 0 }))}
                  label="Duplicates filter"
                />
              </FilterSection>
            </Stack>

            <Group justify="flex-end" pt="md" style={{ borderTop: '1px solid #e2e8f0' }}>
              <Button variant="default" onClick={resetFilters} style={{ borderRadius: 9, fontWeight: 700 }}>
                Reset All
              </Button>
              <Button
                color="emerald" onClick={applyFilters}
                style={{ borderRadius: 9, fontWeight: 700 }}
              >
                Apply Filters
              </Button>
            </Group>
          </Stack>
        </Drawer>

        {/* ── Column Visibility Drawer ───────────────────────────────────── */}
        <Drawer
          title={
            <Group gap={8}>
              <Settings size={18} style={{ color: '#059669' }} />
              <span style={{ fontWeight: 700 }}>Column Visibility</span>
            </Group>
          }
          opened={colDrawerOpen}
          onClose={() => setColDrawerOpen(false)}
          position="right"
          size="xs"
        >
          <Stack gap="xs" style={{ maxHeight: 'calc(100vh - 120px)', overflowY: 'auto' }}>
            {columnMetadata.map(col => (
              <Group key={col.key} justify="space-between" p="xs" style={{ borderRadius: 10, background: colVisible[col.key] ? '#f0fdf4' : '#f8fafc', border: '1px solid #e2e8f0' }}>
                <Text size="sm" fw={600} color="#475569">{col.label}</Text>
                <Switch
                  size="sm"
                  checked={colVisible[col.key]}
                  onChange={event => setColVisible(p => ({ ...p, [col.key]: event.currentTarget.checked }))}
                />
              </Group>
            ))}
          </Stack>
        </Drawer>

        {/* ── Lead Details Drawer Overlay ─────────────────────────────────── */}
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
                  variant="subtle" size="xs"
                  leftSection={<ExternalLink size={13} />}
                  style={{ borderRadius: 8, color: '#059669', fontWeight: 700 }}
                  onClick={() => window.open(`/leads/${detailLeadId}/details`, '_blank')}
                >
                  Full Page
                </Button>
                <ActionIcon
                  variant="subtle" color="red" size="sm"
                  style={{ borderRadius: 8 }}
                  onClick={() => { setDetailOpen(false); fetchData(); }}
                >
                  <X size={16} />
                </ActionIcon>
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
          
          .ll-table-thead th {
            background: #fafafa !important;
            font-size: 0.7rem !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.6px !important;
            color: #94a3b8 !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 10px 14px !important;
          }
          .ll-table-cell {
            font-size: 0.82rem !important;
            padding: 10px 14px !important;
            vertical-align: middle !important;
          }
          .ll-table-row:hover td {
            background: #f0fdf4 !important;
          }
          .ll-spin-refresh {
            animation: spin 1s linear infinite;
          }
          @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
          }
          .mantine-Select-input, .mantine-MultiSelect-input, .mantine-TextInput-input, .mantine-DatePickerInput-input {
            border-radius: 9px !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
          }
          .mantine-Select-input:focus, .mantine-MultiSelect-input:focus, .mantine-TextInput-input:focus, .mantine-DatePickerInput-input:focus {
            border-color: #059669 !important;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15) !important;
          }
          .ll-stat-card {
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
          }
          .ll-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08), 0 8px 16px -6px rgba(0,0,0,0.08) !important;
          }
          .ll-lead-name-link {
            transition: color 0.15s ease;
          }
          .ll-lead-name-link:hover {
            color: #059669 !important;
            text-decoration: underline;
          }
          .ll-table-container::-webkit-scrollbar {
            height: 6px;
            width: 6px;
          }
          .ll-table-container::-webkit-scrollbar-track {
            background: transparent;
          }
          .ll-table-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 99px;
          }
          .ll-table-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
          }
        `}</style>
      </div>
    </MantineProvider>
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
