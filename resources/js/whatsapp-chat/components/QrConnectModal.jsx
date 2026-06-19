import React, { useState, useEffect, useRef, useCallback } from 'react';
import { io } from 'socket.io-client';

const WA = window.__WA_CONFIG__ || {};
const CSRF = WA.csrf_token || document.querySelector('meta[name="csrf-token"]')?.content;

export default function QrConnectModal({ configId, onClose }) {
    const [status, setStatus]       = useState('loading'); // loading | qr_pending | connected | error
    const [qrDataUrl, setQrDataUrl] = useState(null);
    const [errorMsg, setErrorMsg]   = useState('');
    const [configName, setConfigName] = useState('');
    const socketRef = useRef(null);
    const pollRef   = useRef(null);

    const fetchQr = useCallback(async () => {
        setStatus('loading');
        setErrorMsg('');
        try {
            const res = await fetch(`/whatsapp-config/${configId}/qr`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            });
            const data = await res.json();

            if (data.status === 'connected') {
                setStatus('connected');
                setConfigName(data.config_name || '');
            } else if (data.status === 'qr_pending' && data.qr_data_url) {
                setQrDataUrl(data.qr_data_url);
                setConfigName(data.config_name || '');
                setStatus('qr_pending');
            } else {
                setStatus('error');
                setErrorMsg(data.message || 'Failed to generate QR code.');
            }
        } catch (err) {
            setStatus('error');
            setErrorMsg('Could not reach WhatsApp service. Please ensure it is running.');
        }
    }, [configId]);

    // Subscribe to Socket.io for real-time QR / connected events
    useEffect(() => {
        const socketUrl = WA.socket_url || 'http://localhost:3001';
        const socket = io(socketUrl, { transports: ['websocket', 'polling'] });

        socket.on('connect', () => {
            socket.emit('join_config', { config_id: configId });
        });

        socket.on('whatsapp_qr', (data) => {
            if (String(data.config_id) === String(configId) && data.qr_data_url) {
                setQrDataUrl(data.qr_data_url);
                setStatus('qr_pending');
            }
        });

        socket.on('whatsapp_session_ready', (data) => {
            if (String(data.config_id) === String(configId)) {
                setStatus('connected');
            }
        });

        socketRef.current = socket;

        // Kick off initial QR fetch
        fetchQr();

        return () => {
            socket.disconnect();
            if (pollRef.current) clearInterval(pollRef.current);
        };
    }, [configId, fetchQr]);

    return (
        <div className="wa-modal-overlay" onClick={e => e.target === e.currentTarget && onClose()}>
            <div className="wa-modal">
                {/* Header */}
                <div className="wa-modal-header">
                    <span className="wa-modal-title">
                        📱 Connect WhatsApp {configName && `— ${configName}`}
                    </span>
                    <button className="wa-modal-close" onClick={onClose}>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" style={{ display: 'block' }}>
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                {/* Body */}
                <div className="wa-modal-body">
                    {status === 'loading' && (
                        <div className="wa-modal-loading">
                            <div className="wa-spinner-lg" />
                            <p>Generating QR Code…</p>
                            <p className="wa-modal-hint">This may take 10–30 seconds on first launch</p>
                        </div>
                    )}

                    {status === 'qr_pending' && qrDataUrl && (
                        <div className="wa-qr-wrapper">
                            <img src={qrDataUrl} alt="WhatsApp QR Code" className="wa-qr-image" />
                            <div className="wa-qr-instructions">
                                <h5>How to connect:</h5>
                                <ol>
                                    <li>Open WhatsApp on your phone</li>
                                    <li>Tap <strong>Settings → Linked Devices</strong></li>
                                    <li>Tap <strong>Link a Device</strong></li>
                                    <li>Scan this QR code with your camera</li>
                                </ol>
                                <p className="wa-qr-note">QR code expires in ~60 seconds. A new one will appear automatically.</p>
                            </div>
                        </div>
                    )}

                    {status === 'connected' && (
                        <div className="wa-modal-success">
                            <div className="wa-success-icon">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#25d366" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" style={{ margin: '0 auto 12px auto' }}>
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <h4>WhatsApp Connected!</h4>
                            <p>This WhatsApp account is now linked to your CRM. You can close this window.</p>
                            <button className="wa-btn-primary" onClick={onClose}>Close</button>
                        </div>
                    )}

                    {status === 'error' && (
                        <div className="wa-modal-error">
                            <div className="wa-error-icon">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#e53e3e" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" style={{ margin: '0 auto 12px auto' }}>
                                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                    <line x1="12" y1="9" x2="12" y2="13"></line>
                                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                </svg>
                            </div>
                            <h4>Connection Failed</h4>
                            <p>{errorMsg}</p>
                            <button className="wa-btn-primary" onClick={fetchQr}>Try Again</button>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
