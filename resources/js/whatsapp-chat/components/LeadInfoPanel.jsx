import React from 'react';

export default function LeadInfoPanel({ activeChat, stages, onUpdateStage, profilePic }) {
    if (!activeChat || !activeChat.lead) {
        return (
            <div className="wa-right-panel">
                <div className="wa-right-empty">
                    <div className="wa-right-empty-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" style={{ opacity: 0.4, marginBottom: '12px' }}>
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <p>Select a chat to view lead details</p>
                </div>
            </div>
        );
    }

    const { lead, assignee, config } = activeChat;
    const stageEntries = Object.entries(stages || {});

    return (
        <div className="wa-right-panel">
            <div className="wa-lead-card">
                {/* Lead Avatar */}
                <div className="wa-lead-avatar">
                    {profilePic ? (
                        <img src={profilePic} alt={lead.name} style={{ width: '100%', height: '100%', borderRadius: '50%', objectFit: 'cover' }} />
                    ) : (
                        (lead.name || '?').substring(0, 2).toUpperCase()
                    )}
                </div>
                <h5 className="wa-lead-name">{lead.name}</h5>
                <span className="wa-lead-status-badge">Active Lead</span>

                {/* Lead Details */}
                <div className="wa-lead-details">
                    {lead.subject && (
                        <div className="wa-lead-detail-row">
                            <span className="wa-detail-label">Subject</span>
                            <span className="wa-detail-value">{lead.subject}</span>
                        </div>
                    )}
                    {lead.email && (
                        <div className="wa-lead-detail-row">
                            <span className="wa-detail-label">Email</span>
                            <span className="wa-detail-value">{lead.email}</span>
                        </div>
                    )}
                    <div className="wa-lead-detail-row">
                        <span className="wa-detail-label">Assigned To</span>
                        <span className="wa-detail-value">{assignee?.name || 'Unassigned'}</span>
                    </div>
                    {config?.pipeline && (
                        <div className="wa-lead-detail-row">
                            <span className="wa-detail-label">Pipeline</span>
                            <span className="wa-detail-value">{config.pipeline.name}</span>
                        </div>
                    )}
                </div>

                {/* Stage Selector */}
                {stageEntries.length > 0 && (
                    <div className="wa-stage-selector">
                        <label className="wa-detail-label">Lead Stage</label>
                        <select
                            className="wa-select"
                            defaultValue={lead.stage_id}
                            onChange={e => onUpdateStage(e.target.value)}
                        >
                            {stageEntries.map(([id, name]) => (
                                <option key={id} value={id}>{name}</option>
                            ))}
                        </select>
                    </div>
                )}

                {/* View Lead CTA */}
                <a
                    href={`/leads/${lead.id}`}
                    target="_blank"
                    rel="noreferrer"
                    className="wa-btn-lead-profile"
                >
                    View Lead Profile ↗
                </a>
            </div>
        </div>
    );
}
