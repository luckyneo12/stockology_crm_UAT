import React, { useState, useEffect } from 'react';
import { 
  Menu, 
  TextInput, 
  Popover, 
  ActionIcon, 
  Button, 
  Badge, 
  Avatar, 
  Text, 
  Group, 
  Stack, 
  Tooltip,
  UnstyledButton,
  useMantineColorScheme
} from '@mantine/core';
import { 
  Search, 
  SlidersHorizontal, 
  PhoneCall, 
  Database, 
  GitBranch, 
  RefreshCw, 
  FileDown, 
  LayoutGrid, 
  List, 
  Plus, 
  Bell, 
  User, 
  LogOut, 
  Menu as MenuIcon, 
  Languages, 
  Check, 
  ChevronDown 
} from 'lucide-react';

export default function ReactHeader() {
  const mountEl = document.getElementById('react-header');
  
  if (!mountEl) return null;

  // ── Retrieve settings and states from data-attributes ─────────────────────
  const user = JSON.parse(mountEl.getAttribute('data-user') || '{}');
  const activeWorkspaceName = mountEl.getAttribute('data-active-workspace-name') || '';
  const activeWorkspaceId = mountEl.getAttribute('data-active-workspace-id') || '';
  const workspaces = JSON.parse(mountEl.getAttribute('data-workspaces') || '[]');
  const currentUserRole = mountEl.getAttribute('data-current-user-role') || '';
  const languages = JSON.parse(mountEl.getAttribute('data-languages') || '[]');
  const activeLanguage = mountEl.getAttribute('data-active-language') || 'en';
  const initialNotificationCount = parseInt(mountEl.getAttribute('data-notification-count') || '0', 10);
  
  const pipelineId = mountEl.getAttribute('data-pipeline-id') || '';
  const pipelineName = mountEl.getAttribute('data-pipeline-name') || '';
  const pipelines = JSON.parse(mountEl.getAttribute('data-pipelines') || '[]');
  const availableApis = JSON.parse(mountEl.getAttribute('data-available-apis') || '[]');
  const activeApiId = mountEl.getAttribute('data-active-api-id') || '';
  const activeApiName = mountEl.getAttribute('data-active-api-name') || '';
  const csrfToken = mountEl.getAttribute('data-csrf') || '';
  
  const routeIsLeads = mountEl.getAttribute('data-route-is-leads') === '1';
  const routeName = mountEl.getAttribute('data-route-name') || '';
  
  const canCreateWorkspace = mountEl.getAttribute('data-can-create-workspace') === '1';
  const canCreateLead = mountEl.getAttribute('data-can-create-lead') === '1';
  const canImportLead = mountEl.getAttribute('data-can-import-lead') === '1';
  
  const createWorkspaceUrl = mountEl.getAttribute('data-create-workspace-url') || '';
  const createLeadUrl = mountEl.getAttribute('data-create-lead-url') || '';
  const importLeadUrl = mountEl.getAttribute('data-import-lead-url') || '';
  const leadsListUrl = mountEl.getAttribute('data-leads-list-url') || '';
  const leadsBoardUrl = mountEl.getAttribute('data-leads-board-url') || '';
  const impersonating = mountEl.getAttribute('data-impersonating') === '1';
  const exitCompanyUrl = mountEl.getAttribute('data-exit-company-url') || '';

  // ── States ─────────────────────────────────────────────────────────────────
  const [searchQuery, setSearchQuery] = useState('');
  const [filterCount, setFilterCount] = useState(0);
  const [notificationCount, setNotificationCount] = useState(initialNotificationCount);
  const [notifications, setNotifications] = useState([]);
  const [loadingNotifs, setLoadingNotifs] = useState(false);
  const [activeExt, setActiveExt] = useState(user.active_extension === 2 ? 2 : 1);

  // ── Effects & Listeners ───────────────────────────────────────────────────
  useEffect(() => {
    // Listen to query search events from parent pages
    const handleSearch = (e) => {
      if (e.detail && typeof e.detail.query === 'string') {
        setSearchQuery(e.detail.query);
      }
    };

    // Listen to active filters count changes
    const handleFilterCount = (e) => {
      if (e.detail && typeof e.detail.count === 'number') {
        setFilterCount(e.detail.count);
      }
    };

    window.addEventListener('crm-search-change', handleSearch);
    window.addEventListener('crm-filters-count-changed', handleFilterCount);

    return () => {
      window.removeEventListener('crm-search-change', handleSearch);
      window.removeEventListener('crm-filters-count-changed', handleFilterCount);
    };
  }, []);

  // Update query search
  const onSearchChange = (val) => {
    setSearchQuery(val);
    window.dispatchEvent(new CustomEvent('crm-search-change', {
      detail: { query: val }
    }));
  };

  // Open filters panel
  const handleOpenFilters = () => {
    window.dispatchEvent(new CustomEvent('crm-open-filters'));
  };

  // Refresh current list/board data
  const handleRefresh = () => {
    window.dispatchEvent(new CustomEvent('crm-refresh-data'));
  };

  // ── API Switcher ──────────────────────────────────────────────────────────
  const handleSwitchApi = async (apiVal) => {
    try {
      const response = await fetch('/lead/call/switch-api', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ active_api_id: apiVal })
      });
      const res = await response.json();
      if (res.status === 'success') {
        if (window.show_toastr) window.show_toastr('Success', res.message, 'success');
        window.location.reload();
      } else {
        if (window.show_toastr) window.show_toastr('Error', res.message || 'Error occurred', 'error');
      }
    } catch (err) {
      if (window.show_toastr) window.show_toastr('Error', 'Failed to switch API route', 'error');
    }
  };

  // ── Extension Switcher ───────────────────────────────────────────────────
  const handleSwitchExt = async (idx) => {
    try {
      const response = await fetch('/lead/call/switch-extension', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ active_index: idx })
      });
      const res = await response.json();
      if (res.status === 'success') {
        if (window.show_toastr) window.show_toastr('Success', res.message, 'success');
        window.location.reload();
      } else {
        if (window.show_toastr) window.show_toastr('Error', res.message || 'Error occurred', 'error');
      }
    } catch (err) {
      if (window.show_toastr) window.show_toastr('Error', 'Failed to switch active extension', 'error');
    }
  };

  // ── Open Call Settings modal (using Swal) ─────────────────────────────────
  const openCallSettings = () => {
    if (typeof window.Swal === 'undefined') return;
    
    const ext1 = user.extension_1 || '';
    const ext2 = user.extension_2 || '';

    window.Swal.fire({
      title: 'Call Settings',
      html: `
        <div style="max-height: 60vh; overflow-y: auto; padding: 4px; text-align: left;">
          <p style="color: #64748b; font-size: 0.8rem; margin-bottom: 12px;">Configure calling extensions and mappings.</p>
          
          <h6 style="border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-top: 16px; font-weight: 700; color: #059669;">Extensions</h6>
          <div style="margin-top: 8px;">
            <label style="font-weight: 600; font-size: 0.8rem; display: block; margin-bottom: 4px;">Extension 1 (Primary)</label>
            <input type="text" id="swal_ext_1" class="form-control" value="${ext1}" placeholder="e.g. 101">
          </div>
          <div style="margin-top: 12px;">
            <label style="font-weight: 600; font-size: 0.8rem; display: block; margin-bottom: 4px;">Extension 2 (Secondary)</label>
            <input type="text" id="swal_ext_2" class="form-control" value="${ext2}" placeholder="e.g. 102">
          </div>
          
          <h6 style="border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-top: 20px; font-weight: 700; color: #3b82f6;">Extension API Mappings</h6>
          <div style="margin-top: 8px;">
            <label style="font-weight: 600; font-size: 0.8rem; display: block; margin-bottom: 4px;">API for Extension 1</label>
            <select id="swal_api_ext_1" class="form-control">
              <option value="">Default API</option>
              ${availableApis.map(api => `<option value="${api.value}" ${activeApiId == api.value ? 'selected' : ''}>${api.label}</option>`).join('')}
            </select>
          </div>
          
          <small style="color: #0d9488; display: block; margin-top: 12px;"><i class="ti ti-info-circle"></i> Extensions can only be updated once in 24 hours.</small>
        </div>
      `,
      showCancelButton: true,
      confirmButtonText: 'Save Settings',
      cancelButtonText: 'Cancel',
      width: '450px',
      preConfirm: () => {
        const e1 = document.getElementById('swal_ext_1').value;
        const e2 = document.getElementById('swal_ext_2').value;
        const api1 = document.getElementById('swal_api_ext_1').value;
        
        if (!e1) {
          window.Swal.showValidationMessage('Extension 1 is required!');
        }
        return { 
          extension_1: e1, 
          extension_2: e2,
          api_ext_1: api1
        };
      }
    }).then((result) => {
      if (result.isConfirmed) {
        // Send save settings POST
        fetch('/lead/call/save-extension', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({
            extension_1: result.value.extension_1,
            extension_2: result.value.extension_2,
            api_ext_1: result.value.api_ext_1,
            _token: csrfToken
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            if (window.show_toastr) window.show_toastr('Success', data.message, 'success');
            window.location.reload();
          } else {
            if (window.show_toastr) window.show_toastr('Error', data.message || 'Failed to save settings', 'error');
          }
        })
        .catch(() => {
          if (window.show_toastr) window.show_toastr('Error', 'Failed to save settings', 'error');
        });
      }
    });
  };

  // ── Switch Pipeline ────────────────────────────────────────────────────────
  const handleSwitchPipeline = (id) => {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/deals/change-pipeline';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    const pipeInput = document.createElement('input');
    pipeInput.type = 'hidden';
    pipeInput.name = 'default_pipeline_id';
    pipeInput.value = id;
    form.appendChild(pipeInput);

    document.body.appendChild(form);
    form.submit();
  };

  // ── Fetch Notifications ────────────────────────────────────────────────────
  const fetchNotifications = async () => {
    if (notifications.length > 0) return; // cache locally
    try {
      setLoadingNotifs(true);
      const res = await fetch('/notifications');
      const data = await res.json();
      setNotifications(data || []);
    } catch (err) {
      console.error(err);
    } finally {
      setLoadingNotifs(false);
    }
  };

  const handleMarkAllRead = async () => {
    try {
      const res = await fetch('/notifications/read', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        }
      });
      const data = await res.json();
      if (data.success) {
        setNotificationCount(0);
        setNotifications(prev => prev.map(n => ({ ...n, is_read: 1 })));
        if (window.show_toastr) window.show_toastr('Success', 'All marked read', 'success');
      }
    } catch (err) {
      console.error(err);
    }
  };

  const handleNotificationClick = async (notif) => {
    if (notif.is_read) {
      if (notif.action_url) window.location.href = notif.action_url;
      return;
    }
    try {
      await fetch('/notifications/read', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ id: notif.id })
      });
      setNotificationCount(prev => Math.max(0, prev - 1));
      setNotifications(prev => prev.map(n => n.id === notif.id ? { ...n, is_read: 1 } : n));
      if (notif.action_url) {
        window.location.href = notif.action_url;
      }
    } catch (err) {
      console.error(err);
    }
  };

  // ── Logout Action ──────────────────────────────────────────────────────────
  const handleLogout = (e) => {
    e.preventDefault();
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/logout';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);
    
    document.body.appendChild(form);
    form.submit();
  };

  // Active Extension Text
  const currentExtNumber = activeExt === 2 ? user.extension_2 : user.extension_1;
  const currentExtLabel = currentExtNumber ? `Ext ${activeExt}: ${currentExtNumber}` : `Ext ${activeExt}: Unset`;

  return (
    <div className="crm-hdr-container">
      {/* ── Left Section: Mobile Collapse, Quick Info / User Dropdown ── */}
      <Group gap="md">
        <ActionIcon
          id="mobile-collapse"
          variant="subtle"
          color="gray"
          className="crm-hdr-mobile-toggle"
          onClick={() => {
            const btn = document.querySelector('.mob-hamburger a#mobile-collapse') || document.getElementById('mobile-collapse');
            if (btn && btn.click) btn.click();
          }}
        >
          <MenuIcon size={20} />
        </ActionIcon>

        {/* User Dropdown */}
        <Menu shadow="md" width={220} radius="md" trigger="hover" openDelay={100} closeDelay={400}>
          <Menu.Target>
            <UnstyledButton className="crm-hdr-user-btn">
              <Group gap={8}>
                <Avatar
                  src={user.avatar}
                  name={user.name}
                  size={32}
                  radius="xl"
                  color="teal"
                  styles={{
                    root: { border: '2px solid rgba(5,150,105,0.2)' }
                  }}
                />
                <div className="crm-hdr-user-info">
                  <Text size="sm" fw={700} c="slate.8" className="crm-hdr-user-name">
                    {user.name}
                  </Text>
                  <Text size="10px" fw={600} c="teal.6" style={{ textTransform: 'uppercase', letterSpacing: '0.5px' }}>
                    {currentUserRole || 'Agent'}
                  </Text>
                </div>
                <ChevronDown size={14} className="crm-hdr-chevron" />
              </Group>
            </UnstyledButton>
          </Menu.Target>
          <Menu.Dropdown>
            <Menu.Label>My Account</Menu.Label>
            <Menu.Item 
              leftSection={<User size={15} />} 
              onClick={() => window.location.href = user.profile_url}
            >
              Profile Settings
            </Menu.Item>
            {impersonating && (
              <Menu.Item 
                leftSection={<LogOut size={15} />} 
                color="red"
                onClick={() => window.location.href = exitCompanyUrl}
              >
                Exit Impersonation
              </Menu.Item>
            )}
            <Menu.Divider />
            <Menu.Item 
              leftSection={<LogOut size={15} />} 
              color="red" 
              onClick={handleLogout}
            >
              Log Out
            </Menu.Item>
          </Menu.Dropdown>
        </Menu>
      </Group>

      {/* ── Center Section: Search & Leads Filters ── */}
      {routeIsLeads && (
        <Group gap="xs" className="crm-hdr-center-actions">
          {/* Quick Search */}
          <TextInput
            placeholder="Quick search..."
            value={searchQuery}
            onChange={(e) => onSearchChange(e.currentTarget.value)}
            leftSection={<Search size={14} style={{ opacity: 0.6 }} />}
            radius="md"
            className="crm-hdr-search"
            styles={{
              input: {
                background: 'rgba(255,255,255,0.7)',
                border: '1.5px solid rgba(15,23,42,0.06)',
                fontWeight: 600,
                fontSize: '0.8rem',
                transition: 'all 0.2s',
                '&:focus': {
                  borderColor: '#059669',
                  background: '#fff'
                }
              }
            }}
          />

          {/* Advanced Filter */}
          <Button
            variant="light"
            color="teal"
            radius="md"
            leftSection={<SlidersHorizontal size={14} />}
            onClick={handleOpenFilters}
            className="crm-hdr-btn"
            styles={{
              root: {
                fontWeight: 700,
                fontSize: '0.8rem',
                border: '1.5px dashed rgba(5,150,105,0.2)',
                background: filterCount > 0 ? 'rgba(5,150,105,0.08)' : 'rgba(255,255,255,0.6)'
              }
            }}
          >
            Advanced Filter
            {filterCount > 0 && (
              <Badge color="red" size="xs" circle style={{ marginLeft: 6 }}>
                {filterCount}
              </Badge>
            )}
          </Button>

          {/* Extension Switcher */}
          <Menu shadow="md" width={200} radius="md">
            <Menu.Target>
              <Button
                variant="light"
                color="teal"
                radius="md"
                leftSection={<PhoneCall size={14} />}
                className="crm-hdr-btn"
                styles={{
                  root: {
                    fontWeight: 700,
                    fontSize: '0.8rem',
                    background: 'rgba(255,255,255,0.6)',
                    border: '1.5px solid rgba(15,23,42,0.06)'
                  }
                }}
              >
                {currentExtLabel}
                <ChevronDown size={12} style={{ marginLeft: 4 }} />
              </Button>
            </Menu.Target>
            <Menu.Dropdown>
              <Menu.Label>Select Calling Extension</Menu.Label>
              <Menu.Item 
                onClick={() => handleSwitchExt(1)}
                leftSection={activeExt === 1 && <Check size={14} style={{ color: '#059669' }} />}
              >
                Extension 1 ({user.extension_1 || 'Unset'})
              </Menu.Item>
              <Menu.Item 
                onClick={() => handleSwitchExt(2)}
                leftSection={activeExt === 2 && <Check size={14} style={{ color: '#059669' }} />}
              >
                Extension 2 ({user.extension_2 || 'Unset'})
              </Menu.Item>
              <Menu.Divider />
              <Menu.Item onClick={openCallSettings}>
                Configure Call Settings
              </Menu.Item>
            </Menu.Dropdown>
          </Menu>

          {/* API Switcher */}
          {availableApis.length > 0 && (
            <Menu shadow="md" width={220} radius="md">
              <Menu.Target>
                <Button
                  variant="light"
                  color="teal"
                  radius="md"
                  leftSection={<Database size={14} />}
                  className="crm-hdr-btn"
                  styles={{
                    root: {
                      fontWeight: 700,
                      fontSize: '0.8rem',
                      background: 'rgba(255,255,255,0.6)',
                      border: '1.5px solid rgba(15,23,42,0.06)'
                    }
                  }}
                >
                  {activeApiName || 'Calling Route'}
                  <ChevronDown size={12} style={{ marginLeft: 4 }} />
                </Button>
              </Menu.Target>
              <Menu.Dropdown>
                <Menu.Label>Switch Outbound Gateway API</Menu.Label>
                {availableApis.map(api => (
                  <Menu.Item 
                    key={api.value}
                    onClick={() => handleSwitchApi(api.value)}
                    leftSection={activeApiId === api.value && <Check size={14} style={{ color: '#059669' }} />}
                  >
                    {api.label}
                  </Menu.Item>
                ))}
              </Menu.Dropdown>
            </Menu>
          )}

          {/* Pipeline Switcher */}
          {pipelines.length > 0 && (
            <Menu shadow="md" width={200} radius="md">
              <Menu.Target>
                <Button
                  variant="light"
                  color="teal"
                  radius="md"
                  leftSection={<GitBranch size={14} />}
                  className="crm-hdr-btn"
                  styles={{
                    root: {
                      fontWeight: 700,
                      fontSize: '0.8rem',
                      background: 'rgba(255,255,255,0.6)',
                      border: '1.5px solid rgba(15,23,42,0.06)'
                    }
                  }}
                >
                  {pipelineName || 'Pipeline'}
                  <ChevronDown size={12} style={{ marginLeft: 4 }} />
                </Button>
              </Menu.Target>
              <Menu.Dropdown>
                <Menu.Label>Switch Pipeline</Menu.Label>
                {pipelines.map(pipe => (
                  <Menu.Item 
                    key={pipe.value}
                    onClick={() => handleSwitchPipeline(pipe.value)}
                    leftSection={pipelineId === pipe.value && <Check size={14} style={{ color: '#059669' }} />}
                  >
                    {pipe.label}
                  </Menu.Item>
                ))}
              </Menu.Dropdown>
            </Menu>
          )}

          {/* Action Buttons: Refresh, Import, Grid, List, Create */}
          <Group gap="5px">
            <Tooltip label="Refresh Leads" position="top" withArrow>
              <ActionIcon 
                variant="light" 
                color="teal" 
                size="md" 
                radius="md"
                onClick={handleRefresh}
                style={{ background: 'rgba(255,255,255,0.6)', border: '1.5px solid rgba(15,23,42,0.06)' }}
              >
                <RefreshCw size={14} />
              </ActionIcon>
            </Tooltip>

            {canImportLead && (
              <Tooltip label="Import Leads" position="top" withArrow>
                <ActionIcon 
                  variant="light" 
                  color="teal" 
                  size="md" 
                  radius="md"
                  onClick={() => window.location.href = importLeadUrl}
                  style={{ background: 'rgba(255,255,255,0.6)', border: '1.5px solid rgba(15,23,42,0.06)' }}
                >
                  <FileDown size={14} />
                </ActionIcon>
              </Tooltip>
            )}

            {/* List / Grid Toggles */}
            <Tooltip label="Kanban Board View" position="top" withArrow>
              <ActionIcon 
                variant={routeName === 'leads.index' ? 'filled' : 'light'} 
                color="teal" 
                size="md" 
                radius="md"
                onClick={() => window.location.href = leadsBoardUrl}
                style={routeName !== 'leads.index' ? { background: 'rgba(255,255,255,0.6)', border: '1.5px solid rgba(15,23,42,0.06)' } : {}}
              >
                <LayoutGrid size={14} />
              </ActionIcon>
            </Tooltip>
            
            <Tooltip label="Table List View" position="top" withArrow>
              <ActionIcon 
                variant={routeName === 'leads.list' ? 'filled' : 'light'} 
                color="teal" 
                size="md" 
                radius="md"
                onClick={() => window.location.href = leadsListUrl}
                style={routeName !== 'leads.list' ? { background: 'rgba(255,255,255,0.6)', border: '1.5px solid rgba(15,23,42,0.06)' } : {}}
              >
                <List size={14} />
              </ActionIcon>
            </Tooltip>

            {canCreateLead && (
              <Tooltip label="Create New Lead" position="top" withArrow>
                <ActionIcon 
                  variant="filled" 
                  color="emerald" 
                  size="md" 
                  radius="md"
                  data-ajax-popup="true"
                  data-url={createLeadUrl}
                  data-title="Create Lead"
                  styles={{
                    root: {
                      background: 'linear-gradient(135deg, #059669, #047857)',
                      boxShadow: '0 2px 8px rgba(5,150,105,0.2)'
                    }
                  }}
                >
                  <Plus size={15} style={{ color: '#fff' }} />
                </ActionIcon>
              </Tooltip>
            )}
          </Group>
        </Group>
      )}

      {/* ── Right Section: Notifications, Workspaces, Languages ── */}
      <Group gap="xs" style={{ marginLeft: 'auto' }}>
        {/* Notifications Popover */}
        <Popover width={320} position="bottom-end" shadow="md" radius="md" withArrow onOpen={fetchNotifications}>
          <Popover.Target>
            <Tooltip label="Notifications" position="top" withArrow>
              <div className="crm-hdr-bell-wrapper">
                <ActionIcon 
                  variant="subtle" 
                  color="gray" 
                  size="lg" 
                  radius="xl"
                  style={{ background: 'rgba(255,255,255,0.6)', border: '1.5px solid rgba(15,23,42,0.06)' }}
                >
                  <Bell size={18} />
                </ActionIcon>
                {notificationCount > 0 && (
                  <span className="crm-hdr-bell-badge">{notificationCount}</span>
                )}
              </div>
            </Tooltip>
          </Popover.Target>
          <Popover.Dropdown style={{ padding: 0 }}>
            <div className="crm-hdr-notif-header">
              <Text fw={700} size="sm">Notifications</Text>
              {notificationCount > 0 && (
                <Button variant="subtle" size="xs" compact color="teal" onClick={handleMarkAllRead}>
                  Mark all read
                </Button>
              )}
            </div>
            
            <div className="crm-hdr-notif-list">
              {loadingNotifs ? (
                <div style={{ display: 'flex', justifyContent: 'center', padding: '20px 0' }}>
                  <Loader size="sm" color="teal" />
                </div>
              ) : notifications.length === 0 ? (
                <div className="crm-hdr-notif-empty">
                  <Text size="xs" c="dimmed">No notifications found.</Text>
                </div>
              ) : (
                notifications.map(n => (
                  <div 
                    key={n.id} 
                    className={`crm-hdr-notif-item ${n.is_read ? 'read' : 'unread'}`}
                    onClick={() => handleNotificationClick(n)}
                  >
                    <div style={{ flex: 1 }}>
                      <Text size="xs" fw={700} c="slate.8">{n.title || 'Notification'}</Text>
                      <Text size="xs" c="slate.5" style={{ marginTop: 2 }}>{n.description || n.message}</Text>
                      <Text size="10px" c="dimmed" style={{ marginTop: 4 }}>
                        {n.created_at_formatted || 'just now'}
                      </Text>
                    </div>
                    {!n.is_read && <span className="crm-hdr-notif-dot" />}
                  </div>
                ))
              )}
            </div>
          </Popover.Dropdown>
        </Popover>

        {/* Create Workspace Short Button */}
        {canCreateWorkspace && (
          <Tooltip label="Create Workspace" position="top" withArrow>
            <ActionIcon
              variant="subtle"
              color="teal"
              size="lg"
              radius="xl"
              data-ajax-popup="true"
              data-url={createWorkspaceUrl}
              data-title="Create Workspace"
              style={{ background: 'rgba(255,255,255,0.6)', border: '1.5px solid rgba(15,23,42,0.06)' }}
            >
              <Plus size={16} />
            </ActionIcon>
          </Tooltip>
        )}

        {/* Active Workspace Selector */}
        <Menu shadow="md" width={220} radius="md">
          <Menu.Target>
            <Button
              variant="light"
              color="teal"
              radius="md"
              leftSection={<LayoutGrid size={14} />}
              className="crm-hdr-btn"
              styles={{
                root: {
                  fontWeight: 700,
                  fontSize: '0.8rem',
                  background: 'rgba(255,255,255,0.6)',
                  border: '1.5px solid rgba(15,23,42,0.06)'
                }
              }}
            >
              {activeWorkspaceName}
              <ChevronDown size={12} style={{ marginLeft: 4 }} />
            </Button>
          </Menu.Target>
          <Menu.Dropdown>
            <Menu.Label>Switch Workspace</Menu.Label>
            {workspaces.map(w => (
              <Menu.Item 
                key={w.id}
                onClick={() => window.location.href = w.change_url}
                leftSection={String(w.id) === String(activeWorkspaceId) && <Check size={14} style={{ color: '#059669' }} />}
              >
                {w.name}
              </Menu.Item>
            ))}
            {canCreateWorkspace && (
              <>
                <Menu.Divider />
                <Menu.Item 
                  leftSection={<Plus size={14} />}
                  data-ajax-popup="true"
                  data-url={createWorkspaceUrl}
                  data-title="Create Workspace"
                >
                  Create Workspace
                </Menu.Item>
              </>
            )}
          </Menu.Dropdown>
        </Menu>

        {/* Language Selector Dropdown */}
        {languages.length > 0 && (
          <Menu shadow="md" width={160} radius="md">
            <Menu.Target>
              <ActionIcon
                variant="subtle"
                color="teal"
                size="lg"
                radius="md"
                style={{ background: 'rgba(255,255,255,0.6)', border: '1.5px solid rgba(15,23,42,0.06)' }}
              >
                <Languages size={16} />
              </ActionIcon>
            </Menu.Target>
            <Menu.Dropdown>
              <Menu.Label>Select Language</Menu.Label>
              {languages.map(lang => (
                <Menu.Item 
                  key={lang.value}
                  onClick={() => window.location.href = lang.change_url}
                  leftSection={lang.value === activeLanguage && <Check size={14} style={{ color: '#059669' }} />}
                >
                  {lang.label}
                </Menu.Item>
              ))}
            </Menu.Dropdown>
          </Menu>
        )}
      </Group>

      {/* ── Core Header Styles ── */}
      <style>{`
        header.dash-header {
          overflow: visible !important;
        }

        .crm-hdr-container {
          display: flex;
          align-items: center;
          height: 70px;
          padding: 0 16px;
          background: rgba(255, 255, 255, 0.45);
          backdrop-filter: blur(16px);
          -webkit-backdrop-filter: blur(16px);
          border-bottom: 1px solid rgba(15, 23, 42, 0.05);
          width: 100%;
          font-family: 'Plus Jakarta Sans', sans-serif;
          position: sticky;
          top: 0;
          z-index: 100;
        }

        .crm-hdr-mobile-toggle {
          display: none;
        }
        @media (max-width: 768px) {
          .crm-hdr-mobile-toggle {
            display: inline-flex;
          }
          .crm-hdr-center-actions {
            display: none !important;
          }
        }

        .crm-hdr-user-btn {
          padding: 4px 8px;
          border-radius: 10px;
          transition: all 0.2s;
        }
        .crm-hdr-user-btn:hover {
          background: rgba(15, 23, 42, 0.03);
        }
        .crm-hdr-user-info {
          display: flex;
          flex-direction: column;
          text-align: left;
        }
        .crm-hdr-user-name {
          line-height: 1.2;
          font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .crm-hdr-chevron {
          opacity: 0.5;
          transition: transform 0.2s;
        }
        .crm-hdr-user-btn:hover .crm-hdr-chevron {
          opacity: 0.8;
          transform: translateY(1px);
        }

        .crm-hdr-search {
          width: 180px;
        }
        @media (min-width: 1200px) {
          .crm-hdr-search {
            width: 240px;
          }
        }

        .crm-hdr-btn {
          height: 36px;
          font-family: 'Plus Jakarta Sans', sans-serif;
          box-shadow: 0 1px 3px rgba(15, 23, 42, 0.02);
          transition: all 0.2s;
        }
        .crm-hdr-btn:hover {
          transform: translateY(-0.5px);
          box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        }

        /* Bell Badge */
        .crm-hdr-bell-wrapper {
          position: relative;
        }
        .crm-hdr-bell-badge {
          position: absolute;
          top: -2px;
          right: -2px;
          background: #ef4444;
          color: #fff;
          font-size: 9px;
          font-weight: 800;
          height: 15px;
          min-width: 15px;
          border-radius: 10px;
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 0 4px;
          border: 1.5px solid #fff;
        }

        /* Notifications popover content */
        .crm-hdr-notif-header {
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 10px 14px;
          border-bottom: 1px solid rgba(15, 23, 42, 0.05);
        }
        .crm-hdr-notif-list {
          max-height: 280px;
          overflow-y: auto;
        }
        .crm-hdr-notif-item {
          display: flex;
          align-items: flex-start;
          padding: 10px 14px;
          cursor: pointer;
          border-bottom: 1px solid rgba(15, 23, 42, 0.03);
          transition: background 0.2s;
          position: relative;
        }
        .crm-hdr-notif-item:hover {
          background: rgba(15, 23, 42, 0.01);
        }
        .crm-hdr-notif-item.unread {
          background: rgba(5, 150, 105, 0.02);
        }
        .crm-hdr-notif-dot {
          width: 7px;
          height: 7px;
          border-radius: 50%;
          background: #059669;
          margin-top: 4px;
          flex-shrink: 0;
          margin-left: 8px;
        }
        .crm-hdr-notif-empty {
          padding: 20px;
          text-align: center;
        }
      `}</style>
    </div>
  );
}
