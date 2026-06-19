import React, { useState, useEffect, useRef, useCallback } from 'react';
import socket from '../socket';
import LeadDetails from './LeadDetails';

export default function LeadsBoard() {
  const containerRef = useRef(null);
  
  // Read configuration metadata printed on the mount container
  const [pipelines, setPipelines] = useState({});
  const [stages, setStages] = useState([]);
  const [sources, setSources] = useState({});
  const [users, setUsers] = useState({});
  const [departments, setDepartments] = useState({});
  const [teams, setTeams] = useState({});
  
  const [currentPipelineId, setCurrentPipelineId] = useState('');
  const [workspaceId, setWorkspaceId] = useState('');
  const [currentUserId, setCurrentUserId] = useState('');

  // Active filters state
  const [search, setSearch] = useState('');
  const [selectedUser, setSelectedUser] = useState('');
  const [selectedSource, setSelectedSource] = useState('');
  const [selectedDept, setSelectedDept] = useState('');
  const [selectedTeam, setSelectedTeam] = useState('');
  const [startDate, setStartDate] = useState('');
  const [endDate, setEndDate] = useState('');

  // Kanban Leads Data State: { [stageId]: { leads: [], offset: 0, hasMore: false, loading: false } }
  const [boardData, setBoardData] = useState({});
  
  // Slide-over detail drawer state
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [activeLead, setActiveLead] = useState(null);
  const [iframeLoading, setIframeLoading] = useState(false);
  const [drawerFullPage, setDrawerFullPage] = useState(false);

  // Drag-and-drop state
  const [draggedLeadId, setDraggedLeadId] = useState(null);
  const [draggedSourceStageId, setDraggedSourceStageId] = useState(null);
  const [draggedOverStageId, setDraggedOverStageId] = useState(null);

  // Read initial data attributes from HTML container on mount
  useEffect(() => {
    const mountEl = document.getElementById('react-leads-board');
    if (mountEl) {
      try {
        const parsedPipelines = JSON.parse(mountEl.getAttribute('data-pipelines') || '{}');
        const parsedStages = JSON.parse(mountEl.getAttribute('data-stages') || '[]');
        const parsedSources = JSON.parse(mountEl.getAttribute('data-sources') || '{}');
        const parsedUsers = JSON.parse(mountEl.getAttribute('data-users') || '{}');
        const parsedDepts = JSON.parse(mountEl.getAttribute('data-departments') || '{}');
        const parsedTeams = JSON.parse(mountEl.getAttribute('data-teams') || '{}');
        
        setPipelines(parsedPipelines);
        setStages(parsedStages);
        setSources(parsedSources);
        setUsers(parsedUsers);
        setDepartments(parsedDepts);
        setTeams(parsedTeams);
        
        setCurrentPipelineId(mountEl.getAttribute('data-current-pipeline-id') || '');
        setWorkspaceId(mountEl.getAttribute('data-workspace-id') || '');
        setCurrentUserId(mountEl.getAttribute('data-current-user-id') || '');
      } catch (err) {
        console.error('Failed to parse Leads Board mount metadata:', err);
      }
    }
  }, []);

  // Sync Socket.IO room membership
  useEffect(() => {
    if (workspaceId) {
      socket.emit('join_workspace', { workspace_id: workspaceId });
      console.log(`[Socket] Requested to join workspace: ${workspaceId}`);
    }
  }, [workspaceId]);

  // Fetch leads for a specific stage column
  const fetchStageLeads = useCallback(async (stageId, reset = false) => {
    if (!stageId) return;

    setBoardData(prev => ({
      ...prev,
      [stageId]: {
        ...(prev[stageId] || { leads: [], offset: 0, hasMore: true }),
        loading: true
      }
    }));

    try {
      const currentStageState = boardData[stageId] || { leads: [], offset: 0, hasMore: true };
      const offset = reset ? 0 : currentStageState.offset;
      const limit = 30;

      // Construct URL parameters with current active filters
      const params = new URLSearchParams();
      params.append('stage_id', stageId);
      params.append('offset', offset);
      params.append('limit', limit);
      params.append('pipeline_id', currentPipelineId);
      if (search) params.append('search', search);
      if (selectedUser) params.append('responsible_person', selectedUser);
      if (selectedSource) params.append('source_id', selectedSource);
      if (selectedDept) params.append('department_id', selectedDept);
      if (selectedTeam) params.append('team_id', selectedTeam);
      if (startDate) params.append('start_date', startDate);
      if (endDate) params.append('end_date', endDate);

      const response = await fetch(`/leads/kanban-data?${params.toString()}`);
      const result = await response.json();

      if (result.success) {
        setBoardData(prev => {
          const currentStage = prev[stageId] || { leads: [], offset: 0 };
          const newLeads = reset ? result.leads : [...currentStage.leads, ...result.leads];
          
          // Remove duplicate lead IDs just in case
          const uniqueLeads = Array.from(new Map(newLeads.map(item => [item.id, item])).values());
          
          return {
            ...prev,
            [stageId]: {
              leads: uniqueLeads,
              offset: reset ? result.count : (currentStage.offset || 0) + result.count,
              hasMore: result.has_more,
              loading: false
            }
          };
        });
      } else {
        setBoardData(prev => ({
          ...prev,
          [stageId]: {
            ...(prev[stageId] || { leads: [], offset: 0, hasMore: false }),
            loading: false
          }
        }));
      }
    } catch (err) {
      console.error(`Error loading leads for stage ${stageId}:`, err);
      setBoardData(prev => ({
        ...prev,
        [stageId]: {
          ...(prev[stageId] || { leads: [], offset: 0, hasMore: false }),
          loading: false
        }
      }));
    }
  }, [currentPipelineId, search, selectedUser, selectedSource, selectedDept, selectedTeam, startDate, endDate, boardData]);

  // Load all stage columns on configuration initialization or filter adjustment
  useEffect(() => {
    if (stages.length > 0) {
      stages.forEach(stage => {
        fetchStageLeads(stage.id, true);
      });
    }
  }, [stages, search, selectedUser, selectedSource, selectedDept, selectedTeam, startDate, endDate]);

  // Listen to Socket.IO real-time CRM updates
  useEffect(() => {
    const handleRemoteLeadMove = ({ lead_id, stage_id, order }) => {
      console.log(`[Socket] Lead ${lead_id} moved to stage ${stage_id} by another agent`);
      
      setBoardData(prev => {
        let foundLead = null;
        let originalStageId = null;
        
        // Find lead across all local columns and remove it
        const updatedBoard = { ...prev };
        Object.keys(updatedBoard).forEach(sid => {
          const leadsList = updatedBoard[sid]?.leads || [];
          const idx = leadsList.findIndex(l => String(l.id) === String(lead_id));
          if (idx !== -1) {
            foundLead = { ...leadsList[idx] };
            originalStageId = sid;
            const nextLeads = [...leadsList];
            nextLeads.splice(idx, 1);
            updatedBoard[sid] = {
              ...updatedBoard[sid],
              leads: nextLeads,
              offset: Math.max(0, (updatedBoard[sid]?.offset || 0) - 1)
            };
          }
        });

        // If found, append it to the target column
        if (foundLead && stage_id) {
          foundLead.stage_id = stage_id;
          const targetStage = updatedBoard[stage_id] || { leads: [], offset: 0, hasMore: false };
          const nextLeads = [...targetStage.leads];
          
          // Reinsert based on order or prepend to top
          nextLeads.unshift(foundLead);
          
          // Highlight target column dynamically
          updatedBoard[stage_id] = {
            ...targetStage,
            leads: nextLeads,
            offset: (targetStage.offset || 0) + 1
          };
          
          // Show brief visual feedback via window banner
          if (window.show_toastr) {
            window.show_toastr('Stage Changed', `${foundLead.name} was moved to another column.`, 'info');
          }
        } else if (stage_id) {
          // If not found locally (e.g. wasn't in active columns), refresh the target column to fetch it
          setTimeout(() => fetchStageLeads(stage_id, true), 1000);
        }

        return updatedBoard;
      });
    };

    const handleRemoteLeadCreate = ({ lead }) => {
      console.log('[Socket] New lead created:', lead);
      const stageId = lead.stage_id;
      if (stageId) {
        setBoardData(prev => {
          const currentStage = prev[stageId] || { leads: [], offset: 0, hasMore: false };
          if (currentStage.leads.some(l => String(l.id) === String(lead.id))) return prev; // Avoid duplicates
          
          if (window.show_toastr) {
            window.show_toastr('New Lead', `Lead "${lead.name}" has been created.`, 'success');
          }
          
          return {
            ...prev,
            [stageId]: {
              ...currentStage,
              leads: [lead, ...currentStage.leads],
              offset: (currentStage.offset || 0) + 1
            }
          };
        });
      }
    };

    const handleRemoteLeadUpdate = ({ lead_id, lead }) => {
      console.log(`[Socket] Lead ${lead_id} updated:`, lead);
      setBoardData(prev => {
        const updatedBoard = { ...prev };
        Object.keys(updatedBoard).forEach(sid => {
          const leadsList = updatedBoard[sid]?.leads || [];
          const idx = leadsList.findIndex(l => String(l.id) === String(lead_id));
          if (idx !== -1) {
            const nextLeads = [...leadsList];
            nextLeads[idx] = { ...nextLeads[idx], ...lead };
            updatedBoard[sid] = {
              ...updatedBoard[sid],
              leads: nextLeads
            };
          }
        });
        return updatedBoard;
      });
    };

    socket.on('lead_moved', handleRemoteLeadMove);
    socket.on('lead_created', handleRemoteLeadCreate);
    socket.on('lead_updated', handleRemoteLeadUpdate);

    return () => {
      socket.off('lead_moved', handleRemoteLeadMove);
      socket.off('lead_created', handleRemoteLeadCreate);
      socket.off('lead_updated', handleRemoteLeadUpdate);
    };
  }, [stages, fetchStageLeads]);

  // Handle column infinite scroll pagination
  const handleColumnScroll = (e, stageId) => {
    const el = e.target;
    const stageState = boardData[stageId];
    if (!stageState || stageState.loading || !stageState.hasMore) return;

    // Detect if scroll position reaches near the bottom of scroll height
    if (el.scrollTop + el.clientHeight >= el.scrollHeight - 120) {
      fetchStageLeads(stageId);
    }
  };

  // Drag and drop: Drag Start
  const handleDragStart = (e, lead, sourceStageId) => {
    if (!lead.can_move) {
      e.preventDefault();
      return;
    }
    setDraggedLeadId(lead.id);
    setDraggedSourceStageId(sourceStageId);
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', String(lead.id));
  };

  // Drag and drop: Drag Over
  const handleDragOver = (e, stageId) => {
    e.preventDefault();
    if (draggedOverStageId !== stageId) {
      setDraggedOverStageId(stageId);
    }
  };

  // Drag and drop: Card Drop
  const handleDrop = async (e, targetStageId) => {
    e.preventDefault();
    setDraggedOverStageId(null);

    const leadId = draggedLeadId || e.dataTransfer.getData('text/plain');
    const sourceStageId = draggedSourceStageId;

    if (!leadId || !targetStageId || String(sourceStageId) === String(targetStageId)) {
      setDraggedLeadId(null);
      setDraggedSourceStageId(null);
      return;
    }

    // Optimistic UI state update
    let movedLead = null;
    setBoardData(prev => {
      const nextBoard = { ...prev };
      
      // Source column processing
      const sourceColumn = nextBoard[sourceStageId] || { leads: [], offset: 0 };
      const sourceLeads = [...sourceColumn.leads];
      const leadIdx = sourceLeads.findIndex(l => String(l.id) === String(leadId));
      
      if (leadIdx !== -1) {
        movedLead = { ...sourceLeads[leadIdx] };
        sourceLeads.splice(leadIdx, 1);
        nextBoard[sourceStageId] = {
          ...sourceColumn,
          leads: sourceLeads,
          offset: Math.max(0, sourceColumn.offset - 1)
        };
      }

      // Target column processing
      if (movedLead) {
        movedLead.stage_id = targetStageId;
        const targetColumn = nextBoard[targetStageId] || { leads: [], offset: 0 };
        const targetLeads = [movedLead, ...targetColumn.leads];
        nextBoard[targetStageId] = {
          ...targetColumn,
          leads: targetLeads,
          offset: targetColumn.offset + 1
        };
      }

      return nextBoard;
    });

    // Reset drag states
    setDraggedLeadId(null);
    setDraggedSourceStageId(null);

    // Call Laravel endpoint to persist order/stage updates in database
    try {
      const form = new FormData();
      form.append('lead_id', leadId);
      form.append('stage_id', targetStageId);
      form.append('old_status', sourceStageId);
      form.append('pipeline_id', currentPipelineId);
      form.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');

      // Build explicit lead order mapping in target column
      const targetStageData = boardData[targetStageId] || { leads: [] };
      const orderIds = [leadId, ...targetStageData.leads.map(l => l.id).filter(id => String(id) !== String(leadId))];
      form.append('order', orderIds.join(','));

      const response = await fetch('/leads/order', {
        method: 'POST',
        body: form
      });
      const result = await response.json();

      if (result.error) {
        // Rollback drag and drop if server errors out
        if (window.show_toastr) window.show_toastr('Error', result.error, 'error');
        fetchStageLeads(sourceStageId, true);
        fetchStageLeads(targetStageId, true);
      } else {
        if (window.show_toastr) window.show_toastr('Success', result.success || 'Stage updated.', 'success');
        
        // Broadcast the move event over Socket.io
        socket.emit('lead_moved', {
          workspace_id: workspaceId,
          lead_id: leadId,
          stage_id: targetStageId,
          order: orderIds
        });
      }
    } catch (err) {
      console.error('Failed to save lead move sequence:', err);
      fetchStageLeads(sourceStageId, true);
      fetchStageLeads(targetStageId, true);
    }
  };

  // Open the detail drawer
  const openDetails = (lead) => {
    setActiveLead(lead);
    setDrawerOpen(true);
    setIframeLoading(true);
  };

  // Close details drawer and reload/sync the edited card
  const closeDetails = async () => {
    setDrawerOpen(false);
    setDrawerFullPage(false);
    const closedLeadId = activeLead?.id;
    setActiveLead(null);

    if (closedLeadId) {
      try {
        // Fetch updated details for the closed card
        const response = await fetch(`/leads/kanban-data?lead_id=${closedLeadId}`);
        const result = await response.json();
        
        if (result.success && result.lead) {
          const updatedLead = result.lead;
          const targetStageId = updatedLead.stage_id;

          setBoardData(prev => {
            const nextBoard = { ...prev };
            let oldStageId = null;

            // Remove card from old position if stage changed during edit
            Object.keys(nextBoard).forEach(sid => {
              const leads = nextBoard[sid]?.leads || [];
              const idx = leads.findIndex(l => String(l.id) === String(closedLeadId));
              if (idx !== -1) {
                oldStageId = sid;
                if (String(sid) !== String(targetStageId)) {
                  const nextLeads = [...leads];
                  nextLeads.splice(idx, 1);
                  nextBoard[sid] = {
                    ...nextBoard[sid],
                    leads: nextLeads,
                    offset: Math.max(0, nextBoard[sid].offset - 1)
                  };
                }
              }
            });

            // Insert/update card in target stage column
            const targetCol = nextBoard[targetStageId] || { leads: [], offset: 0 };
            const nextLeads = [...targetCol.leads];
            const targetIdx = nextLeads.findIndex(l => String(l.id) === String(closedLeadId));

            if (targetIdx !== -1) {
              nextLeads[targetIdx] = updatedLead;
            } else {
              nextLeads.unshift(updatedLead);
              targetCol.offset = (targetCol.offset || 0) + 1;
            }
            targetCol.leads = nextLeads;
            nextBoard[targetStageId] = targetCol;

            return nextBoard;
          });

          // Notify Socket.io collaborators
          socket.emit('lead_updated', {
            workspace_id: workspaceId,
            lead_id: closedLeadId,
            lead: updatedLead
          });
        }
      } catch (err) {
        console.error('Failed to sync edited lead details:', err);
      }
    }
  };

  // Reset all active filters
  const resetFilters = () => {
    setSearch('');
    setSelectedUser('');
    setSelectedSource('');
    setSelectedDept('');
    setSelectedTeam('');
    setStartDate('');
    setEndDate('');
  };

  return (
    <div className="crm-react-board-wrapper" ref={containerRef}>
      
      {/* ── Filter Bar ────────────────────────────────────────────── */}
      <div className="leads-filter-bar-row mb-4">
        <div className="d-flex flex-wrap align-items-center gap-3">
          
          <div className="filter-input-wrap flex-grow-1" style={{ minWidth: '200px' }}>
            <span className="search-icon-inside">🔍</span>
            <input 
              type="text" 
              className="form-control ps-5"
              placeholder="Search name, email, phone..." 
              value={search}
              onChange={(e) => setSearch(e.target.value)}
            />
          </div>

          <div className="filter-select-wrap">
            <select 
              className="form-select form-control"
              value={selectedUser}
              onChange={(e) => setSelectedUser(e.target.value)}
            >
              <option value="">Responsible Person</option>
              {Object.entries(users).map(([id, name]) => (
                <option key={id} value={id}>{name}</option>
              ))}
            </select>
          </div>

          <div className="filter-select-wrap">
            <select 
              className="form-select form-control"
              value={selectedSource}
              onChange={(e) => setSelectedSource(e.target.value)}
            >
              <option value="">Source</option>
              {Object.entries(sources).map(([id, name]) => (
                <option key={id} value={id}>{name}</option>
              ))}
            </select>
          </div>

          {Object.keys(departments).length > 0 && (
            <div className="filter-select-wrap">
              <select 
                className="form-select form-control"
                value={selectedDept}
                onChange={(e) => setSelectedDept(e.target.value)}
              >
                <option value="">Department</option>
                {Object.entries(departments).map(([id, name]) => (
                  <option key={id} value={id}>{name}</option>
                ))}
              </select>
            </div>
          )}

          {Object.keys(teams).length > 0 && (
            <div className="filter-select-wrap">
              <select 
                className="form-select form-control"
                value={selectedTeam}
                onChange={(e) => setSelectedTeam(e.target.value)}
              >
                <option value="">Team</option>
                {Object.entries(teams).map(([id, name]) => (
                  <option key={id} value={id}>{name}</option>
                ))}
              </select>
            </div>
          )}

          <div className="d-flex align-items-center gap-2">
            <input 
              type="date" 
              className="form-control py-1 px-2" 
              style={{ fontSize: '0.8rem' }}
              value={startDate}
              onChange={(e) => setStartDate(e.target.value)}
            />
            <span className="text-muted" style={{ fontSize: '0.8rem' }}>to</span>
            <input 
              type="date" 
              className="form-control py-1 px-2" 
              style={{ fontSize: '0.8rem' }}
              value={endDate}
              onChange={(e) => setEndDate(e.target.value)}
            />
          </div>

          {(search || selectedUser || selectedSource || selectedDept || selectedTeam || startDate || endDate) && (
            <button 
              className="btn btn-sm btn-light-danger border d-flex align-items-center gap-1 py-2 px-3"
              onClick={resetFilters}
            >
              <i className="ti ti-trash"></i> Reset
            </button>
          )}
        </div>
      </div>

      {/* ── Kanban Columns Grid ───────────────────────────────────── */}
      <div className="kanban-columns-scrollable pt-1">
        <div className="d-flex flex-row gap-3 align-items-stretch" style={{ height: '100%', minHeight: '550px' }}>
          
          {stages.map((stage) => {
            const stageState = boardData[stage.id] || { leads: [], offset: 0, hasMore: false, loading: false };
            const isTarget = draggedOverStageId === stage.id;
            
            return (
              <div 
                key={stage.id} 
                className={`kanban-column-container ${isTarget ? 'column-drag-hovered' : ''}`}
                onDragOver={(e) => handleDragOver(e, stage.id)}
                onDrop={(e) => handleDrop(e, stage.id)}
              >
                
                {/* Column Header */}
                <div className="column-header-card d-flex align-items-center justify-content-between">
                  <span className="column-title fw-bold text-truncate">{stage.name}</span>
                  <div className="d-flex align-items-center gap-1.5">
                    {!stage.permissions?.can_move && (
                      <i 
                        className="ti ti-lock text-danger" 
                        title="Stage Locked: Cards cannot be moved into/out of this stage" 
                        style={{ fontSize: '0.8rem' }}
                      />
                    )}
                    <span className="badge bg-primary rounded-pill px-2.5 py-1 text-xs">
                      {stageState.leads.length}
                    </span>
                  </div>
                </div>

                {/* Column Body Cards Box */}
                <div 
                  className="column-cards-scroller"
                  onScroll={(e) => handleColumnScroll(e, stage.id)}
                >
                  <div className="d-flex flex-column gap-2.5 p-1">
                    
                    {stageState.leads.map((lead) => (
                      <div 
                        key={lead.id} 
                        className={`lead-kanban-card shadow-sm ${!lead.is_active ? 'lead-card-inactive' : ''}`}
                        draggable={lead.can_move}
                        onDragStart={(e) => handleDragStart(e, lead, stage.id)}
                        onClick={() => openDetails(lead)}
                      >
                        
                        {/* Card Header: Name + Call Delegation */}
                        <div className="d-flex align-items-center justify-content-between mb-1.5">
                          <h6 className="lead-card-name text-truncate mb-0 fw-bold">{lead.name}</h6>
                          
                          <div className="d-flex align-items-center gap-2" onClick={(e) => e.stopPropagation()}>
                            {lead.phone && (
                              <a 
                                href="javascript:void(0)" 
                                className="call-btn-enhanced click-to-call" 
                                data-phone={lead.phone}
                                title="Click to Call"
                              >
                                <i className="ti ti-phone-call f-11"></i>
                              </a>
                            )}
                          </div>
                        </div>

                        {/* Labels Pill Wrapper */}
                        {lead.labels && lead.labels.length > 0 && (
                          <div className="d-flex flex-wrap gap-1 mb-2">
                            {lead.labels.map((label, idx) => (
                              <span 
                                key={idx} 
                                className={`badge bg-${label.color} text-xxs px-1.5 py-0.5 rounded-sm`}
                                style={{ fontSize: '7px', textTransform: 'uppercase' }}
                              >
                                {label.name}
                              </span>
                            ))}
                          </div>
                        )}

                        {/* Stats Info Matrix */}
                        <div className="d-flex align-items-center justify-content-between text-muted text-xs mb-2 mt-1 px-1">
                          <div className="d-flex align-items-center gap-1 bg-light rounded px-1.5 py-0.5" title="Tasks">
                            <i className="ti ti-list f-11" />
                            <span>{lead.task_completed}/{lead.task_total}</span>
                          </div>
                          
                          <div className="d-flex align-items-center gap-1 bg-light rounded px-1.5 py-0.5" title="Reminders">
                            <i className={`ti ti-bell f-11 ${lead.reminder_today > 0 ? 'text-danger animate-pulse' : ''}`} />
                            <span className={lead.reminder_today > 0 ? 'text-danger fw-bold' : ''}>
                              {lead.reminder_today}/{lead.reminder_total}
                            </span>
                          </div>

                          {lead.date && (
                            <div className="d-flex align-items-center gap-1 bg-light rounded px-1.5 py-0.5" title="Created At">
                              <i className="ti ti-calendar f-11" />
                              <span>{lead.date}</span>
                            </div>
                          )}
                        </div>

                        <hr className="my-1.5" style={{ opacity: '0.06' }} />

                        {/* Card Footer: Metadata (Owner + Team) */}
                        <div className="d-flex align-items-center justify-content-between pt-0.5">
                          <div className="d-flex align-items-center gap-2">
                            {lead.kyc_comment_count > 0 && (
                              <div className="d-flex align-items-center gap-0.5 text-xs text-primary" title="KYC Comments">
                                <i className="ti ti-shield-check f-12" />
                                <span className="fw-bold">{lead.kyc_comment_count}</span>
                              </div>
                            )}
                            {lead.sources_count > 0 && (
                              <div className="d-flex align-items-center gap-0.5 text-xs text-muted" title="Sources">
                                <i className="ti ti-circles f-12" />
                                <span>{lead.sources_count}</span>
                              </div>
                            )}
                          </div>

                          <div className="d-flex flex-column align-items-end gap-1" style={{ maxWidth: '60%' }}>
                            {lead.owner_name && (
                              <span className="badge bg-white border text-dark text-xxs text-truncate d-flex align-items-center gap-1 shadow-sm px-1.5 py-0.5" style={{ maxWidth: '100%' }}>
                                <i className="ti ti-user text-muted" style={{ fontSize: '9px' }} />
                                <span className="text-truncate">{lead.owner_name}</span>
                              </span>
                            )}
                            {lead.team_name && (
                              <span className="badge text-xxs text-truncate px-1.5 py-0.5" style={{ background: '#f1f5f9', color: '#64748b', border: '1px solid #e2e8f0', maxWidth: '100%' }}>
                                <i className="ti ti-users text-muted me-1" style={{ fontSize: '8px' }} />
                                <span className="text-truncate">{lead.team_name}</span>
                              </span>
                            )}
                          </div>
                        </div>

                      </div>
                    ))}

                    {/* Column Spinner Loader */}
                    {stageState.loading && (
                      <div className="d-flex justify-content-center align-items-center py-3 text-muted">
                        <div className="spinner-border spinner-border-sm text-primary" role="status" />
                      </div>
                    )}

                    {!stageState.loading && stageState.leads.length === 0 && (
                      <div className="text-center text-muted py-4" style={{ fontSize: '0.75rem' }}>
                        No leads
                      </div>
                    )}
                  </div>
                </div>

              </div>
            );
          })}

        </div>
      </div>

      {/* ── Slide-over Lead Detail Drawer ────────────────────────── */}
      {drawerOpen && activeLead && (
        <div
          className={`drawer-overlay-wrap ${drawerFullPage ? 'is-fullpage' : ''}`}
          onClick={drawerFullPage ? undefined : closeDetails}
        >
          <div
            className={`detail-drawer-panel ${drawerFullPage ? 'is-fullpage' : ''}`}
            onClick={(e) => e.stopPropagation()}
          >

            {/* Drawer Header */}
            <div className="drawer-header border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
              <div className="d-flex align-items-center gap-2">
                <div className="drawer-header-avatar">
                  {activeLead.name.slice(0,2).toUpperCase()}
                </div>
                <div>
                  <h5 className="mb-0 fw-bold text-dark" style={{fontSize:'0.95rem',lineHeight:1.2}}>{activeLead.name}</h5>
                  <small className="text-muted" style={{fontSize:'0.68rem'}}>Lead details &amp; progression panel</small>
                </div>
              </div>

              {/* Header Action Buttons */}
              <div className="d-flex align-items-center gap-2">

                {/* Full Page Toggle */}
                <button
                  type="button"
                  className="drawer-action-btn"
                  title={drawerFullPage ? 'Exit Full Page' : 'Open Full Page'}
                  onClick={() => setDrawerFullPage(prev => !prev)}
                >
                  <i className={`ti ${drawerFullPage ? 'ti-arrows-minimize' : 'ti-arrows-maximize'}`} />
                  <span>{drawerFullPage ? 'Exit Full Page' : 'Full Page'}</span>
                </button>

                {/* Open in New Tab */}
                <a
                  href={`/leads/${activeLead.id}/details`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="drawer-action-btn"
                  title="Open in new tab"
                >
                  <i className="ti ti-external-link" />
                  <span>New Tab</span>
                </a>

                {/* Close */}
                <button
                  type="button"
                  className="drawer-close-btn"
                  onClick={closeDetails}
                  title="Close"
                >
                  <i className="ti ti-x" />
                </button>

              </div>
            </div>

            {/* Drawer Body */}
            <div className="drawer-iframe-container" style={{ position: 'relative', flex: 1, overflow: 'hidden', minHeight: 0 }}>
              <LeadDetails
                leadId={activeLead.id}
                onClose={closeDetails}
                workspaceId={workspaceId}
                currentUserId={currentUserId}
              />
            </div>

          </div>
        </div>
      )}

      {/* ── Embedded CSS Styles for Premium UI & Animations ───────── */}
      <style>{`
        .crm-react-board-wrapper {
          width: 100%;
          min-height: calc(100vh - 190px);
          font-family: 'Plus Jakarta Sans', sans-serif;
        }
        
        .kanban-columns-scrollable {
          overflow-x: auto;
          scrollbar-width: thin;
          scrollbar-color: #cbd5e1 #f8fafc;
          padding-bottom: 12px;
          height: calc(100vh - 270px);
          min-height: 520px;
        }
        
        .kanban-columns-scrollable::-webkit-scrollbar {
          height: 8px;
        }
        .kanban-columns-scrollable::-webkit-scrollbar-thumb {
          background: #cbd5e1;
          border-radius: 4px;
        }
        
        .kanban-column-container {
          flex: 0 0 310px;
          width: 310px;
          max-width: 310px;
          background: #f8fafc;
          border-radius: 12px;
          border: 1px solid #e2e8f0;
          display: flex;
          flex-direction: column;
          height: 100%;
          transition: all 0.25s ease;
        }
        
        .column-drag-hovered {
          background: #f0fdf4 !important;
          border: 1px dashed #22c55e !important;
          transform: translateY(2px);
        }
        
        .column-header-card {
          padding: 12px 16px;
          background: #ffffff;
          border-bottom: 1px solid #e2e8f0;
          border-radius: 12px 12px 0 0;
          z-index: 10;
        }
        
        .column-title {
          font-size: 0.85rem;
          font-weight: 700;
          color: #0f172a;
          letter-spacing: 0.2px;
        }
        
        .column-cards-scroller {
          flex: 1 1 auto;
          overflow-y: auto;
          overflow-x: hidden;
          scrollbar-width: thin;
          padding: 8px 4px;
        }
        .column-cards-scroller::-webkit-scrollbar {
          width: 5px;
        }
        .column-cards-scroller::-webkit-scrollbar-thumb {
          background: #cbd5e1;
          border-radius: 3px;
        }
        
        .lead-kanban-card {
          background: #ffffff;
          border-radius: 10px;
          border: 1px solid rgba(15, 23, 42, 0.05);
          padding: 12px;
          cursor: grab;
          transition: all 0.2s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .lead-kanban-card:hover {
          transform: translateY(-2px);
          box-shadow: 0 8px 16px rgba(15, 23, 42, 0.04) !important;
          border-color: rgba(5, 150, 105, 0.25);
        }
        .lead-kanban-card:active {
          cursor: grabbing;
        }
        
        .lead-card-inactive {
          opacity: 0.65;
          background: #f1f5f9;
        }
        
        .lead-card-name {
          font-size: 0.82rem;
          color: #1e293b;
        }
        
        .text-xxs {
          font-size: 7.5px;
        }
        .text-xs {
          font-size: 10px;
        }
        
        .bg-light {
          background-color: #f8fafc !important;
        }
        
        .call-btn-enhanced {
          background: rgba(0, 179, 136, 0.06);
          color: #00B388;
          width: 22px;
          height: 22px;
          display: flex;
          align-items: center;
          justify-content: center;
          border-radius: 5px;
          transition: all 0.2s ease;
          text-decoration: none !important;
        }
        .call-btn-enhanced:hover {
          background: #00B388;
          color: white;
          transform: scale(1.1) rotate(3deg);
        }
        
        .animate-pulse {
          animation: pulse 1.6s infinite alternate;
        }
        @keyframes pulse {
          0% { transform: scale(1); opacity: 0.75; }
          100% { transform: scale(1.15); opacity: 1; color: #ef4444; }
        }
        
        /* ── Drawer Slide-over Styling ── */
        .drawer-overlay-wrap {
          position: fixed;
          top: 0; left: 0; right: 0; bottom: 0;
          background: rgba(15, 23, 42, 0.45);
          backdrop-filter: blur(5px);
          z-index: 2000;
          display: flex;
          justify-content: flex-end;
          animation: fadeInOverlay 0.22s ease-out;
        }
        .drawer-overlay-wrap.is-fullpage {
          justify-content: stretch;
          background: rgba(15, 23, 42, 0.65);
        }

        .detail-drawer-panel {
          width: 870px;
          max-width: 96vw;
          height: 100%;
          background: #f1f5f9;
          box-shadow: -12px 0 40px rgba(15, 23, 42, 0.18);
          display: flex;
          flex-direction: column;
          animation: slideInDrawer 0.28s cubic-bezier(0.25, 0.8, 0.25, 1);
          transition: width 0.38s cubic-bezier(0.25, 0.8, 0.25, 1),
                      max-width 0.38s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .detail-drawer-panel.is-fullpage {
          width: 100vw !important;
          max-width: 100vw !important;
          box-shadow: none;
        }

        /* Drawer Header */
        .drawer-header {
          background: #ffffff;
          flex-shrink: 0;
        }
        .drawer-header-avatar {
          width: 36px; height: 36px;
          border-radius: 10px;
          background: linear-gradient(135deg, #059669, #047857);
          color: #fff; font-weight: 800; font-size: 0.75rem;
          display: flex; align-items: center; justify-content: center;
          letter-spacing: -0.5px; flex-shrink: 0;
        }
        .drawer-action-btn {
          display: inline-flex; align-items: center; gap: 5px;
          padding: 5px 12px; border-radius: 8px;
          border: 1.5px solid #e2e8f0;
          background: #f8fafc; color: #475569;
          font-size: 0.72rem; font-weight: 700;
          cursor: pointer; white-space: nowrap;
          transition: all 0.2s ease;
          text-decoration: none;
          font-family: inherit;
        }
        .drawer-action-btn:hover {
          border-color: #059669;
          background: #f0fdf4;
          color: #059669;
        }
        .drawer-action-btn i { font-size: 13px; }
        .drawer-close-btn {
          width: 32px; height: 32px;
          border-radius: 8px;
          border: 1.5px solid #e2e8f0;
          background: #f8fafc; color: #64748b;
          display: flex; align-items: center; justify-content: center;
          font-size: 14px; cursor: pointer;
          transition: all 0.2s ease;
          font-family: inherit;
        }
        .drawer-close-btn:hover {
          background: #fee2e2; border-color: #fecaca; color: #dc2626;
        }

        .iframe-spinner-overlay {
          position: absolute;
          top: 0; left: 0; right: 0; bottom: 0;
          background: #ffffff;
          z-index: 10;
        }

        @keyframes fadeInOverlay {
          from { opacity: 0; }
          to { opacity: 1; }
        }
        @keyframes slideInDrawer {
          from { transform: translateX(100%); opacity: 0; }
          to { transform: translateX(0); opacity: 1; }
        }
        
        .search-icon-inside {
          position: absolute;
          left: 15px;
          top: 50%;
          transform: translateY(-50%);
          color: #94a3b8;
          font-size: 0.85rem;
        }
        .filter-input-wrap {
          position: relative;
        }
        .leads-filter-bar-row {
          background: #ffffff;
          padding: 12px 20px;
          border: 1px solid #e2e8f0;
          border-radius: 12px;
          box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);
        }
        
        .form-select.form-control, .form-control {
          border-radius: 8px !important;
          border-color: #cbd5e1;
          font-size: 0.85rem !important;
          height: 38px;
        }
        .form-select.form-control:focus, .form-control:focus {
          border-color: #059669;
          box-shadow: 0 0 0 3px rgba(5,150,105,0.15);
        }
        .btn-light-danger {
          background: #fef2f2;
          color: #ef4444;
          border-color: #fee2e2;
        }
        .btn-light-danger:hover {
          background: #fee2e2;
          color: #dc2626;
        }
      `}</style>

    </div>
  );
}
