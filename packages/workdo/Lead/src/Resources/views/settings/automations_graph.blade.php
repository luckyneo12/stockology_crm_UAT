@extends('layouts.main')

@section('page-title')
    {{ __('Workflow Automations') }}
@endsection

@section('page-breadcrumb')
    {{ __('CRM') }},
    {{ __('Automations') }}
@endsection

@section('page-action')
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-light-secondary d-inline-flex align-items-center shadow-sm" id="reset-layout">
            <i class="ti ti-rotate-clockwise me-1"></i> {{ __('Reset Layout') }}
        </button>
        <button class="btn btn-sm btn-primary d-inline-flex align-items-center shadow-sm" id="save-automations">
            <i class="ti ti-device-floppy me-1"></i> {{ __('Save Workflows') }}
        </button>
    </div>
@endsection

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .canvas-viewport {
            width: 100%;
            height: 75vh;
            overflow: auto;
            border-radius: 16px;
            border: 1px solid #cbd5e1;
            box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.04);
            position: relative;
            background: #f8fafc;
        }

        .canvas-viewport::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        .canvas-viewport::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 16px;
        }
        .canvas-viewport::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 16px;
            border: 2px solid #f1f5f9;
        }
        .canvas-viewport::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .canvas-container {
            width: 3500px;
            height: 2000px;
            position: relative;
            background-color: #f8fafc;
            background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px);
            background-size: 24px 24px;
            user-select: none;
        }

        .svg-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .pipeline-card {
            position: absolute;
            width: 270px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            z-index: 10;
            transition: box-shadow 0.15s ease, border-color 0.15s ease, transform 0.1s ease;
            font-family: 'Outfit', sans-serif;
        }

        .pipeline-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -6px rgba(0, 0, 0, 0.04);
            border-color: #cbd5e1;
        }

        .pipeline-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 18px;
            border-top-left-radius: 14px;
            border-top-right-radius: 14px;
            cursor: grab;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .pipeline-header:active {
            cursor: grabbing;
        }

        .pipeline-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stage-list {
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .stage-node {
            position: relative;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.15s ease;
        }

        .stage-node:hover {
            border-color: #cbd5e1;
            background: #f1f5f9;
            color: #0f172a;
        }

        .stage-node:hover .edit-icon {
            opacity: 1 !important;
        }

        /* Connection Ports */
        .port {
            width: 12px;
            height: 12px;
            background: #94a3b8;
            border: 2.5px solid #ffffff;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            cursor: crosshair;
            z-index: 12;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.15s ease;
        }

        .port:hover {
            background: #3b82f6;
            transform: translateY(-50%) scale(1.4);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
        }

        .port.input-port {
            left: -7px;
        }

        .port.output-port {
            right: -7px;
        }

        .port.active-drag {
            background: #10b981;
            box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.25);
        }

        /* Connection Lines */
        .connection-line {
            fill: none;
            stroke: #3b82f6;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-dasharray: 8 6;
            animation: flow 30s linear infinite;
            pointer-events: auto;
            cursor: pointer;
            filter: drop-shadow(0 2px 4px rgba(59, 130, 246, 0.15));
            transition: stroke 0.2s, stroke-width 0.2s, filter 0.2s;
        }

        .connection-line:hover {
            stroke: #ef4444;
            stroke-width: 4;
            filter: drop-shadow(0 2px 6px rgba(239, 68, 68, 0.3));
        }

        @keyframes flow {
            from {
                stroke-dashoffset: 300;
            }
            to {
                stroke-dashoffset: 0;
            }
        }

        /* Floating action buttons style */
        .floating-legend {
            position: absolute;
            bottom: 24px;
            left: 24px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 12px 16px;
            z-index: 100;
            font-family: 'Outfit', sans-serif;
            font-size: 0.78rem;
            color: #475569;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .legend-item:last-child {
            margin-bottom: 0;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            border: 2px solid #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        /* Connection Labels */
        .connection-label {
            position: absolute;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 20px;
            padding: 3px 8px;
            font-size: 0.72rem;
            font-weight: 700;
            color: #475569;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            cursor: pointer;
            user-select: none;
            z-index: 20;
            transform: translate(-50%, -50%);
            transition: all 0.15s ease;
            font-family: 'Outfit', sans-serif;
            pointer-events: auto; /* enable clicks */
        }
        .connection-label:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.15);
            transform: translate(-50%, -50%) scale(1.05);
        }
        .connection-label.copy-label {
            border-color: rgba(59, 130, 246, 0.3);
            color: #3b82f6;
            background: #eff6ff;
        }
        .connection-label.move-label {
            border-color: rgba(16, 185, 129, 0.3);
            color: #10b981;
            background: #ecfdf5;
        }
        .connection-label.wh-label {
            border-color: rgba(255, 159, 115, 0.3);
            color: #ff9f43;
            background: #fff4eb;
        }
        .connection-label.wh-label:hover {
            border-color: #ff9f43;
            color: #ff9f43;
            box-shadow: 0 4px 10px rgba(255, 159, 67, 0.15);
        }

        /* Action Modal styles */
        .action-card {
            border: 2px solid #e2e8f0 !important;
            transition: all 0.2s;
            cursor: pointer;
        }
        .action-card:hover {
            border-color: #3b82f6 !important;
            background: #f8fafc;
        }
        .action-card.selected {
            border-color: #3b82f6 !important;
            background: #eff6ff !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        /* Premium Facebook Modal styles */
        #facebookConfigModal .modal-content {
            border-radius: 20px !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15) !important;
            border: 1px solid #f1f5f9;
            font-family: 'Outfit', sans-serif;
        }

        #facebookConfigModal .modal-header {
            padding: 24px 28px 12px 28px;
        }

        #facebookConfigModal .modal-body {
            padding: 12px 28px 28px 28px;
        }

        #facebookConfigModal .modal-footer {
            padding: 16px 28px 24px 28px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
        }

        #facebookConfigModal .nav-tabs {
            border-bottom: 2px solid #f1f5f9 !important;
            gap: 12px;
        }

        #facebookConfigModal .nav-link {
            border: none !important;
            color: #64748b !important;
            background: transparent !important;
            padding: 8px 16px !important;
            border-radius: 8px !important;
            font-size: 0.82rem;
            transition: all 0.2s ease;
        }

        #facebookConfigModal .nav-link:hover {
            color: #1e293b !important;
            background: #f1f5f9 !important;
        }

        #facebookConfigModal .nav-link.active {
            color: #ffffff !important;
            background: #1877f2 !important;
            box-shadow: 0 4px 12px rgba(24, 119, 242, 0.25) !important;
        }

        #facebookConfigModal .form-label {
            color: #334155;
            font-size: 0.78rem;
            margin-bottom: 6px;
        }

        #facebookConfigModal .form-control,
        #facebookConfigModal .form-select {
            border-radius: 8px !important;
            border: 1px solid #cbd5e1 !important;
            padding: 10px 14px !important;
            font-size: 0.85rem !important;
            color: #0f172a !important;
            transition: all 0.15s ease-in-out;
            background-color: #ffffff;
        }

        #facebookConfigModal .form-control:focus,
        #facebookConfigModal .form-select:focus {
            border-color: #1877f2 !important;
            box-shadow: 0 0 0 4px rgba(24, 119, 242, 0.15) !important;
            outline: none;
        }

        /* Seamless token input group wrapper */
        .token-group {
            display: flex;
            position: relative;
            align-items: stretch;
            width: 100%;
        }

        .token-group .form-control {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            border-right: none !important;
        }

        .token-group .btn-toggle-eye {
            border-top-right-radius: 8px !important;
            border-bottom-right-radius: 8px !important;
            border: 1px solid #cbd5e1 !important;
            border-left: none !important;
            background: #ffffff;
            color: #64748b;
            padding: 0 16px;
            transition: all 0.15s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .token-group .form-control:focus + .btn-toggle-eye {
            border-color: #1877f2 !important;
            border-top-color: #1877f2 !important;
            border-bottom-color: #1877f2 !important;
            box-shadow: 4px 0 0 rgba(24, 119, 242, 0.15), 0 4px 0 rgba(24, 119, 242, 0.15), 0 -4px 0 rgba(24, 119, 242, 0.15) !important;
        }

        /* Premium Buttons */
        #save-fb-rule-btn {
            background: #1877f2 !important;
            border: none !important;
            color: #ffffff !important;
            font-weight: 600;
            padding: 8px 18px !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 10px rgba(24, 119, 242, 0.2) !important;
            transition: all 0.2s ease;
        }

        #save-fb-rule-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(24, 119, 242, 0.3) !important;
            background: #1565c0 !important;
        }

        #test-fb-connection-btn {
            background: #00acc1 !important;
            border: none !important;
            color: #ffffff !important;
            font-weight: 600;
            padding: 8px 18px !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 10px rgba(0, 172, 193, 0.2) !important;
            transition: all 0.2s ease;
        }

        #test-fb-connection-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(0, 172, 193, 0.3) !important;
            background: #00838f !important;
        }

        #delete-fb-rule-btn {
            border: 1.5px solid #ef4444 !important;
            color: #ef4444 !important;
            background: transparent !important;
            font-weight: 600;
            padding: 8px 18px !important;
            border-radius: 8px !important;
            transition: all 0.2s ease;
        }

        #delete-fb-rule-btn:hover {
            background: #ef4444 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2) !important;
        }

        /* Orion Integration Card Styles */
        #pipeline-orion {
            border-color: #8b5cf6 !important;
            box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.1), 0 8px 10px -6px rgba(139, 92, 246, 0.1);
        }
        #pipeline-orion .pipeline-header {
            background: rgba(139, 92, 246, 0.05);
            border-bottom: 1px solid rgba(139, 92, 246, 0.15);
        }
        .connection-label.orion-label {
            border-color: rgba(139, 92, 246, 0.3);
            color: #8b5cf6;
            background: #f5f3ff;
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 20px;
            padding: 3px 8px;
            font-size: 0.72rem;
            font-weight: 700;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            cursor: pointer;
            user-select: none;
            z-index: 20;
            transform: translate(-50%, -50%);
            transition: all 0.15s ease;
            font-family: 'Outfit', sans-serif;
            pointer-events: auto;
        }
        .connection-label.orion-label:hover {
            border-color: #8b5cf6;
            color: #8b5cf6;
            box-shadow: 0 4px 10px rgba(139, 92, 246, 0.15);
            transform: translate(-50%, -50%) scale(1.05);
        }
        .token-group .btn-toggle-eye-orion {
            border-top-right-radius: 8px !important;
            border-bottom-right-radius: 8px !important;
            border: 1px solid #cbd5e1 !important;
            border-left: none !important;
            background: #ffffff;
            color: #64748b;
            padding: 0 16px;
            transition: all 0.15s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .token-group .form-control:focus + .btn-toggle-eye-orion {
            border-color: #8b5cf6 !important;
            border-top-color: #8b5cf6 !important;
            border-bottom-color: #8b5cf6 !important;
            box-shadow: 4px 0 0 rgba(139, 92, 246, 0.15), 0 4px 0 rgba(139, 92, 246, 0.15), 0 -4px 0 rgba(139, 92, 246, 0.15) !important;
        }

        /* Orion Premium Modal Styling */
        .orion-premium-modal .modal-content {
            border-radius: 20px !important;
            background: #ffffff;
            box-shadow: 0 20px 50px rgba(139, 92, 246, 0.15) !important;
            border: 1px solid rgba(139, 92, 246, 0.15) !important;
            overflow: hidden;
        }

        .orion-premium-modal .modal-header {
            background: linear-gradient(135deg, #fdfbfd 0%, #f3ebfc 100%);
            border-bottom: 1px solid rgba(139, 92, 246, 0.1) !important;
            padding: 20px 28px !important;
        }

        .orion-premium-modal .modal-title {
            color: #1e1b4b !important;
            font-size: 1.15rem !important;
            letter-spacing: -0.3px;
        }

        .orion-premium-modal .modal-body {
            padding: 28px !important;
        }

        /* Nav Tabs - Modern Capsule Pill Tabs */
        .orion-premium-modal .nav-tabs {
            border-bottom: none !important;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 12px;
            margin-bottom: 28px !important;
            display: flex;
            gap: 4px;
        }

        .orion-premium-modal .nav-tabs .nav-item {
            flex: 1;
            text-align: center;
        }

        .orion-premium-modal .nav-tabs .nav-link {
            border: none !important;
            background: transparent !important;
            color: #64748b !important;
            border-radius: 8px !important;
            padding: 10px 16px !important;
            font-weight: 600 !important;
            font-size: 0.85rem !important;
            width: 100%;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .orion-premium-modal .nav-tabs .nav-link.active {
            background: #ffffff !important;
            color: #8b5cf6 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
        }
        
        .orion-premium-modal .nav-tabs .nav-link:hover:not(.active) {
            color: #8b5cf6 !important;
            background: rgba(255, 255, 255, 0.5) !important;
        }

        /* Premium Form Inputs */
        .orion-premium-modal .form-label {
            color: #334155 !important;
            font-weight: 600 !important;
            font-size: 0.78rem !important;
            margin-bottom: 6px !important;
            letter-spacing: 0.2px;
        }

        .orion-premium-modal .form-control,
        .orion-premium-modal .form-select {
            border-radius: 10px !important;
            border: 1.5px solid #cbd5e1 !important;
            padding: 10px 14px !important;
            font-size: 0.85rem !important;
            font-weight: 500 !important;
            color: #1e293b !important;
            background-color: #f8fafc !important;
            transition: all 0.25s ease-in-out !important;
            box-shadow: none !important;
        }

        .orion-premium-modal .form-control:focus,
        .orion-premium-modal .form-select:focus {
            border-color: #8b5cf6 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.12) !important;
        }

        /* Token Input Group styling */
        .orion-premium-modal .token-group {
            display: flex;
            position: relative;
        }
        
        .orion-premium-modal .token-group .form-control {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            flex-grow: 1;
        }

        .orion-premium-modal .token-group .btn-toggle-eye-orion {
            border-top-right-radius: 10px !important;
            border-bottom-right-radius: 10px !important;
            border: 1.5px solid #cbd5e1 !important;
            border-left: none !important;
            background-color: #f8fafc;
            padding: 0 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.25s ease-in-out;
        }

        .orion-premium-modal .token-group .form-control:focus + .btn-toggle-eye-orion {
            border-color: #8b5cf6 !important;
            background-color: #ffffff;
        }

        /* Modern Mapping List Layout */
        .orion-mapping-section {
            background: #f8fafc;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            padding: 16px;
        }

        /* Orion Mapping Redesign */
        .orion-mapping-row {
            background: #ffffff;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.01);
            position: relative;
            z-index: 1;
        }
        .orion-mapping-row:focus-within,
        .orion-mapping-row:has(.choices.is-open) {
            z-index: 9999 !important;
            position: relative;
        }
        .orion-mapping-row.unmapped {
            border-left: 4px solid #cbd5e1 !important;
        }
        .orion-mapping-row.mapped {
            background: #faf8ff;
            border-color: #d8b4fe !important;
            border-left: 4px solid #8b5cf6 !important;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.04);
        }
        .orion-mapping-row:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.04);
            z-index: 10;
        }
        
        .orion-mapping-icon-wrapper {
            width: 36px;
            height: 36px;
            background: #f1f5f9;
            border-radius: 10px;
            color: #64748b;
            transition: all 0.25s ease;
        }
        .orion-mapping-row.mapped .orion-mapping-icon-wrapper {
            background: #f3e8ff;
            color: #a855f7;
            transform: scale(1.05);
        }

        .orion-mapping-param-side {
            width: 38%;
        }

        .orion-mapping-param-label {
            font-weight: 700;
            font-size: 0.85rem;
            color: #1e293b;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .orion-mapping-param-code {
            font-family: 'Fira Code', 'Courier New', Courier, monospace;
            font-size: 0.68rem;
            color: #8b5cf6;
            font-weight: 700;
        }

        .orion-connector-line {
            position: relative;
            height: 2px;
            opacity: 0.7;
        }
        .orion-connector-dash {
            width: 100%;
            height: 1.5px;
            background-image: linear-gradient(to right, #cbd5e1 50%, rgba(255, 255, 255, 0) 0%);
            background-position: bottom;
            background-size: 8px 1px;
            background-repeat: repeat-x;
        }
        .orion-mapping-row.mapped .orion-connector-dash {
            background-image: linear-gradient(to right, #d8b4fe 50%, rgba(255, 255, 255, 0) 0%);
        }
        .orion-connector-dot {
            width: 6px;
            height: 6px;
            background: #cbd5e1;
            border-radius: 50%;
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
        }
        .orion-mapping-row.mapped .orion-connector-dot {
            background: #a855f7;
        }
        .orion-connector-badge {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            z-index: 2;
        }
        .orion-mapping-row.mapped .orion-connector-badge {
            border-color: #d8b4fe;
            color: #8b5cf6 !important;
            box-shadow: 0 2px 6px rgba(139, 92, 246, 0.1);
        }
        .orion-mapping-row.mapped .orion-connector-badge i {
            color: #8b5cf6 !important;
        }

        .orion-mapping-select-side {
            width: 45%;
        }

        .orion-mapped-status-pill {
            font-size: 0.7rem !important;
            font-weight: 700 !important;
            padding: 6px 12px !important;
            border-radius: 30px !important;
            display: inline-flex;
            align-items: center;
            letter-spacing: 0.2px;
        }
        .unmapped-pill {
            background: #f1f5f9 !important;
            color: #64748b !important;
            border: 1px solid #e2e8f0;
        }
        .mapped-pill {
            background: #f5f3ff !important;
            color: #8b5cf6 !important;
            border: 1px solid rgba(139, 92, 246, 0.2);
            box-shadow: 0 2px 6px rgba(139, 92, 246, 0.05);
        }

        .orion-mapping-select-container {
            flex-grow: 1;
        }
        .orion-mapping-select-container select {
            width: 100%;
            border-radius: 8px !important;
            border: 1.5px solid #cbd5e1 !important;
            background-color: #ffffff !important;
            font-weight: 600 !important;
            font-size: 0.8rem !important;
            color: #475569 !important;
            padding: 8px 12px !important;
            transition: all 0.2s ease;
        }
        .orion-mapping-row.mapped select {
            border-color: #d8b4fe !important;
            color: #1e1b4b !important;
        }
        .orion-mapping-row.mapped select:focus {
            border-color: #8b5cf6 !important;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.12) !important;
        }

        /* Search Mappings input */
        .search-mapping-container {
            transition: all 0.25s ease;
        }
        .search-mapping-container:focus-within {
            border-color: #8b5cf6 !important;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15) !important;
        }
        .search-mapping-container input::placeholder {
            font-size: 0.75rem !important;
            color: #94a3b8;
        }

        /* Choices.js Custom Tuning for mapping */
        .orion-mapping-select-side .choices {
            margin-bottom: 0 !important;
            width: 100%;
        }
        .orion-mapping-select-side .choices__inner {
            border-radius: 8px !important;
            border: 1.5px solid #cbd5e1 !important;
            background-color: #ffffff !important;
            padding: 4px 10px !important;
            min-height: 38px !important;
            font-size: 0.8rem !important;
            font-weight: 600 !important;
            color: #475569 !important;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }
        .orion-mapping-row.mapped .orion-mapping-select-side .choices__inner {
            border-color: #d8b4fe !important;
            background-color: #ffffff !important;
            color: #1e1b4b !important;
        }
        .orion-mapping-select-side .choices__list--single {
            padding: 0 !important;
            display: flex;
            align-items: center;
        }
        .orion-mapping-select-side .choices__input {
            font-size: 0.8rem !important;
            background-color: transparent !important;
        }
        /* Choices dropdown styling */
        .orion-mapping-select-side .choices__list--dropdown {
            z-index: 10000 !important;
            border-radius: 10px !important;
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.15) !important;
            border: 1.5px solid #cbd5e1 !important;
            border-top: none !important;
            background-color: #ffffff !important;
            overflow: hidden !important;
        }
        .orion-mapping-row.mapped .orion-mapping-select-side .choices__list--dropdown {
            border-color: #d8b4fe !important;
        }
        .orion-mapping-select-side .choices__list--dropdown .choices__item--selectable {
            padding: 8px 12px !important;
            font-size: 0.8rem !important;
            font-weight: 600 !important;
            color: #334155 !important;
        }
        .orion-mapping-select-side .choices__list--dropdown .choices__item--selectable.is-highlighted {
            background-color: #f5f3ff !important;
            color: #8b5cf6 !important;
        }

        /* Buttons Styling */
        .orion-premium-modal .modal-footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0 !important;
            padding: 18px 28px !important;
        }

        .orion-premium-modal .btn-capsule {
            border-radius: 50px !important;
            padding: 8px 20px !important;
            font-weight: 600 !important;
            font-size: 0.82rem !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .orion-premium-modal .btn-capsule:hover {
            transform: translateY(-1px);
        }

        .orion-premium-modal .btn-save {
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%) !important;
            border: none !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(109, 40, 217, 0.25) !important;
        }

        .orion-premium-modal .btn-save:hover {
            box-shadow: 0 6px 18px rgba(109, 40, 217, 0.35) !important;
        }

        .orion-premium-modal .btn-test {
            background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%) !important;
            border: none !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.25) !important;
        }

        .orion-premium-modal .btn-test:hover {
            box-shadow: 0 6px 18px rgba(14, 165, 233, 0.35) !important;
        }

        .orion-premium-modal .btn-delete {
            border: 1.5px solid #ef4444 !important;
            color: #ef4444 !important;
            background: transparent !important;
        }

        .orion-premium-modal .btn-delete:hover {
            background: #ef4444 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25) !important;
        }
    </style>

    <div class="row">
        <div class="col-12 position-relative">
            <div class="canvas-viewport" id="canvas-viewport">
                <div class="canvas-container" id="canvas">
                    <svg class="svg-canvas" id="svg-canvas">
                        <defs>
                            <!-- Arrowhead Marker definition -->
                            <marker id="arrow" viewBox="0 0 10 10" refX="6" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                                <path d="M 0 0 L 10 5 L 0 10 z" fill="#3b82f6" />
                            </marker>
                            <marker id="arrow-hover" viewBox="0 0 10 10" refX="6" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                                <path d="M 0 0 L 10 5 L 0 10 z" fill="#ef4444" />
                            </marker>
                        </defs>
                    </svg>

                    @foreach($pipelines as $pipeline)
                        <div class="pipeline-card" 
                             id="pipeline-{{ $pipeline->id }}" 
                             data-pipeline-id="{{ $pipeline->id }}"
                             style="top: 100px; left: 100px;">
                            <div class="pipeline-header drag-handle">
                                <h5 class="pipeline-title">{{ $pipeline->name }}</h5>
                                <i class="ti ti-grip-vertical text-muted fs-5"></i>
                            </div>
                            <div class="stage-list">
                                @foreach($pipeline->leadStages as $stage)
                                    <div class="stage-node" 
                                         id="stage-{{ $stage->id }}" 
                                         data-stage-id="{{ $stage->id }}">
                                        <!-- Input port (for incoming copies) -->
                                        <div class="port input-port" data-type="input" title="{{ __('Connect target stage here') }}"></div>
                                        
                                        <span class="stage-name">{{ $stage->name }}</span>
                                        
                                        <!-- Output port (for outgoing trigger) -->
                                        <div class="port output-port" data-type="output" title="{{ __('Drag arrow to target stage') }}"></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <!-- Facebook Leads Integration Card -->
                    <div class="pipeline-card integration-card" 
                         id="pipeline-facebook" 
                         data-pipeline-id="facebook"
                         style="top: 150px; left: 50px; border: 2px dashed #1877f2; box-shadow: 0 10px 25px -5px rgba(24, 119, 242, 0.1), 0 8px 10px -6px rgba(24, 119, 242, 0.1);">
                        <div class="pipeline-header drag-handle" style="background: rgba(24, 119, 242, 0.05); border-bottom: 1px solid rgba(24, 119, 242, 0.15);">
                            <h5 class="pipeline-title text-primary d-flex align-items-center mb-0">
                                <i class="ti ti-brand-facebook me-2 fs-4" style="color: #1877f2;"></i> {{ __('Facebook Leads') }}
                            </h5>
                            <span class="badge bg-light-primary text-primary" style="font-size: 0.65rem;">{{ __('Integration') }}</span>
                        </div>
                        <div class="stage-list" id="facebook-rules-list">
                            @foreach($fbSettings as $rule)
                                <div class="stage-node fb-rule-node" 
                                     id="fb-rule-{{ $rule['id'] }}" 
                                     data-rule-id="{{ $rule['id'] }}"
                                     data-page-id="{{ $rule['page_id'] }}"
                                     data-page-access-token="{{ $rule['page_access_token'] }}"
                                     data-page-name="{{ $rule['page_name'] ?? '' }}"
                                     data-form-id="{{ $rule['form_id'] ?? '' }}"
                                     data-pipeline-id="{{ $rule['pipeline_id'] }}"
                                     data-stage-id="{{ $rule['stage_id'] }}"
                                     data-user-id="{{ $rule['user_id'] ?? '' }}"
                                     data-source-id="{{ $rule['source_id'] ?? '' }}"
                                     style="border-left: 3px solid #1877f2;">
                                    <span class="stage-name text-truncate" style="max-width: 170px;">
                                        <i class="ti ti-plug text-primary me-1"></i> {{ $rule['page_name'] ?? ('Page ID: '.$rule['page_id']) }}
                                    </span>
                                    <i class="ti ti-pencil text-muted edit-icon ms-auto me-1" style="font-size: 0.8rem; opacity: 0; transition: opacity 0.15s; pointer-events: none;"></i>
                                    <!-- Output port only, to target pipeline stages -->
                                    <div class="port output-port" data-type="output" title="{{ __('Drag arrow to map to another stage') }}"></div>
                                </div>
                            @endforeach

                            <!-- Add new integration node -->
                            <div class="stage-node text-center justify-content-center py-2" 
                                 id="add-facebook-rule-node" 
                                 style="border: 1px dashed rgba(24, 119, 242, 0.4); background: transparent; cursor: pointer; color: #1877f2; transition: all 0.2s;">
                                <span class="fw-bold"><i class="ti ti-plus me-1"></i> {{ __('Add Facebook Feed') }}</span>
                            </div>
                        </div>

                        <!-- Webhook Info Section -->
                        <div class="border-top p-3 bg-light rounded-bottom text-start" style="font-size: 0.75rem; border-top: 1px dashed rgba(24, 119, 242, 0.2) !important;">
                            <div class="fw-bold text-dark mb-1 d-flex align-items-center">
                                <i class="ti ti-link text-primary me-1 fs-5"></i> {{ __('Meta Webhook Settings') }}
                            </div>
                            <p class="text-muted text-xxs mb-2" style="line-height: 1.2; font-size: 0.65rem;">
                                {{ __('Configure these details in your Meta Developer App (Page Webhooks subscription):') }}
                            </p>
                            
                            <div class="mb-2">
                                <span class="fw-semibold d-block text-muted" style="font-size: 0.65rem;">{{ __('Callback URL') }}:</span>
                                <div class="d-flex align-items-center justify-content-between bg-white p-1 rounded border mt-1">
                                    <code class="text-primary text-truncate me-1" id="canvas-webhook-url" style="max-width: 170px; font-size: 0.65rem;">{{ route('meta.callback') }}</code>
                                    <button type="button" class="btn btn-xs btn-outline-secondary p-0 px-2" style="font-size: 0.65rem;" onclick="copyToClipboard('canvas-webhook-url')">
                                        <i class="ti ti-copy"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div>
                                <span class="fw-semibold d-block text-muted" style="font-size: 0.65rem;">{{ __('Verify Token') }}:</span>
                                <div class="d-flex align-items-center justify-content-between bg-white p-1 rounded border mt-1">
                                    <code class="text-primary" id="canvas-verify-token" style="font-size: 0.65rem;">12345678</code>
                                    <button type="button" class="btn btn-xs btn-outline-secondary p-0 px-2" style="font-size: 0.65rem;" onclick="copyToClipboard('canvas-verify-token')">
                                        <i class="ti ti-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Webhook Endpoints Integration Card -->
                    <div class="pipeline-card integration-card" 
                         id="pipeline-webhook" 
                         data-pipeline-id="webhook"
                         style="top: 150px; left: 350px; border: 2px dashed #ff9f43; box-shadow: 0 10px 25px -5px rgba(255, 159, 67, 0.1), 0 8px 10px -6px rgba(255, 159, 67, 0.1);">
                        <div class="pipeline-header drag-handle" style="background: rgba(255, 159, 67, 0.05); border-bottom: 1px solid rgba(255, 159, 67, 0.15);">
                            <h5 class="pipeline-title text-warning d-flex align-items-center mb-0" style="color: #ff9f43 !important;">
                                <i class="ti ti-link me-2 fs-4" style="color: #ff9f43;"></i> {{ __('Webhook Endpoints') }}
                            </h5>
                            <span class="badge bg-light-warning text-warning" style="font-size: 0.65rem;">{{ __('Integration') }}</span>
                        </div>
                        <div class="stage-list" id="webhook-rules-list">
                            @foreach($webhookEndpoints as $endpoint)
                                <div class="stage-node webhook-rule-node" 
                                     id="wh-rule-{{ $endpoint->id }}" 
                                     data-rule-id="{{ $endpoint->id }}"
                                     data-pipeline-id="{{ $endpoint->pipeline_id }}"
                                     data-stage-id="{{ $endpoint->stage_id }}"
                                     data-url="{{ route('webhook-endpoints.edit', $endpoint->id) }}" 
                                     data-ajax-popup="true" data-size="lg" data-title="{{ __('Edit Webhook Endpoint') }}"
                                     style="border-left: 3px solid #ff9f43; cursor: pointer; display: flex; flex-direction: column; align-items: flex-start; gap: 2px;">
                                    <div class="d-flex align-items-center w-100">
                                        <span class="stage-name text-truncate" style="max-width: 170px;">
                                            <i class="ti ti-plug text-warning me-1"></i> {{ $endpoint->name }}
                                        </span>
                                        <i class="ti ti-pencil text-warning edit-icon ms-auto me-1" style="font-size: 0.8rem; opacity: 0; transition: opacity 0.15s; pointer-events: none;"></i>
                                    </div>
                                    <small class="text-muted text-xxs" style="padding-left: 18px; font-size: 0.65rem;">
                                        {{ $endpoint->auto_convert == 0 ? __('Logs Only') : __('Direct Lead') }}
                                    </small>
                                    <!-- Output port only, to target pipeline stages -->
                                    <div class="port output-port" data-type="output" title="{{ __('Drag arrow to map to another stage') }}"></div>
                                </div>
                            @endforeach

                            <!-- Add new integration node -->
                            <div class="stage-node text-center justify-content-center py-2 text-warning" 
                                 data-url="{{ route('webhook-endpoints.create') }}" 
                                 data-ajax-popup="true" data-size="lg" data-title="{{ __('Create Webhook Endpoint') }}"
                                 style="border: 1px dashed rgba(255, 159, 67, 0.4); background: transparent; cursor: pointer; color: #ff9f43; transition: all 0.2s;"
                                 onmouseover="this.style.background='rgba(255, 159, 67, 0.04)'; this.style.borderColor='#ff9f43';"
                                 onmouseout="this.style.background='transparent'; this.style.borderColor='rgba(255, 159, 67, 0.4)';">
                                <span class="fw-bold"><i class="ti ti-plus me-1"></i> {{ __('Add Webhook') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Orion EKYC Integration Card -->
                    <div class="pipeline-card integration-card" 
                         id="pipeline-orion" 
                         data-pipeline-id="orion"
                         style="top: 150px; left: 650px; border: 2px dashed #8b5cf6; box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.1), 0 8px 10px -6px rgba(139, 92, 246, 0.1);">
                        <div class="pipeline-header drag-handle" style="background: rgba(139, 92, 246, 0.05); border-bottom: 1px solid rgba(139, 92, 246, 0.15);">
                            <h5 class="pipeline-title text-primary d-flex align-items-center mb-0" style="color: #8b5cf6 !important;">
                                <i class="ti ti-api me-2 fs-4" style="color: #8b5cf6;"></i> {{ __('Orion EKYC') }}
                            </h5>
                            <span class="badge bg-light-primary text-primary" style="font-size: 0.65rem; background: rgba(139, 92, 246, 0.1); color: #8b5cf6 !important;">{{ __('Integration') }}</span>
                        </div>
                        <div class="stage-list" id="orion-rules-list">
                            @foreach($orionSettings['rules'] ?? [] as $rule)
                                <div class="stage-node orion-rule-node" 
                                     id="orion-rule-{{ $rule['id'] ?? '' }}" 
                                     data-rule-id="{{ $rule['id'] ?? '' }}"
                                     data-pipeline-id="{{ $rule['pipeline_id'] ?? '' }}"
                                     data-stage-id="{{ $rule['stage_id'] ?? '' }}"
                                     data-trigger-mode="{{ $rule['trigger_mode'] ?? 'manual_fetch' }}"
                                     style="border-left: 3px solid #8b5cf6; cursor: pointer;">
                                    <span class="stage-name text-truncate" style="max-width: 170px;">
                                        <i class="ti ti-plug text-primary me-1" style="color: #8b5cf6;"></i> 
                                        @php $triggerMode = $rule['trigger_mode'] ?? 'manual_fetch'; @endphp
                                        {{ $triggerMode == 'manual_fetch' ? __('Manual Fetch') : ($triggerMode == 'auto_fetch' ? __('Auto Fetch') : ($triggerMode == 'auto_send_ekyc' ? __('Auto Send EKYC') : __('Auto Send Modify'))) }}
                                    </span>
                                    <i class="ti ti-pencil text-muted edit-icon ms-auto me-1" style="font-size: 0.8rem; opacity: 0; transition: opacity 0.15s; pointer-events: none;"></i>
                                    <!-- Output port only, to target pipeline stages -->
                                    <div class="port output-port" data-type="output" title="{{ __('Drag arrow to map to another stage') }}"></div>
                                </div>
                            @endforeach

                            <!-- Add new integration node -->
                            <div class="stage-node text-center justify-content-center py-2" 
                                 id="add-orion-rule-node" 
                                 style="border: 1px dashed rgba(139, 92, 246, 0.4); background: transparent; cursor: pointer; color: #8b5cf6; transition: all 0.2s;"
                                 onmouseover="this.style.background='rgba(139, 92, 246, 0.04)'; this.style.borderColor='#8b5cf6';"
                                 onmouseout="this.style.background='transparent'; this.style.borderColor='rgba(139, 92, 246, 0.4)';">
                                <span class="fw-bold"><i class="ti ti-plus me-1"></i> {{ __('Add Orion Rule / Config') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- WhatsApp Integration Card -->
                    <div class="pipeline-card integration-card" 
                         id="pipeline-whatsapp" 
                         data-pipeline-id="whatsapp"
                         style="top: 150px; left: 950px; border: 2px dashed #25d366; box-shadow: 0 10px 25px -5px rgba(37, 211, 102, 0.1), 0 8px 10px -6px rgba(37, 211, 102, 0.1);">
                        <div class="pipeline-header drag-handle" style="background: rgba(37, 211, 102, 0.05); border-bottom: 1px solid rgba(37, 211, 102, 0.15);">
                            <h5 class="pipeline-title text-success d-flex align-items-center mb-0" style="color: #25d366 !important;">
                                <i class="ti ti-brand-whatsapp me-2 fs-4" style="color: #25d366;"></i> {{ __('WhatsApp') }}
                            </h5>
                            <span class="badge bg-light-success text-success" style="font-size: 0.65rem; background: rgba(37, 211, 102, 0.1); color: #25d366 !important;">{{ __('Integration') }}</span>
                        </div>
                        <div class="stage-list" id="whatsapp-rules-list">
                            @foreach($whatsappConfigs as $config)
                                <div class="stage-node whatsapp-rule-node" 
                                     id="whatsapp-config-{{ $config->id }}" 
                                     data-config-id="{{ $config->id }}"
                                     data-pipeline-id="{{ $config->pipeline_id }}"
                                     data-stage-id="{{ $config->stage_id }}"
                                     data-url="{{ route('whatsapp-config.edit', $config->id) }}" 
                                     data-ajax-popup="true" data-size="lg" data-title="{{ __('Edit WhatsApp Configuration') }}"
                                     style="border-left: 3px solid #25d366; cursor: pointer; display: flex; flex-direction: column; align-items: flex-start; gap: 2px;">
                                    <div class="d-flex align-items-center w-100">
                                        <span class="stage-name text-truncate" style="max-width: 170px;">
                                            <i class="ti ti-plug text-success me-1"></i> {{ $config->name }}
                                        </span>
                                        <i class="ti ti-pencil text-success edit-icon ms-auto me-1" style="font-size: 0.8rem; opacity: 0; transition: opacity 0.15s; pointer-events: none;"></i>
                                    </div>
                                    <small class="text-muted text-xxs" style="padding-left: 18px; font-size: 0.65rem;">
                                        {{ $config->phone_number }}
                                    </small>
                                    <!-- Output port only, to target pipeline stages -->
                                    <div class="port output-port" data-type="output" title="{{ __('Drag arrow to map to default stage') }}"></div>
                                </div>
                            @endforeach

                            <!-- Add new integration node -->
                            <div class="stage-node text-center justify-content-center py-2 text-success" 
                                 data-url="{{ route('whatsapp-config.create') }}" 
                                 data-ajax-popup="true" data-size="lg" data-title="{{ __('Create WhatsApp Configuration') }}"
                                 style="border: 1px dashed rgba(37, 211, 102, 0.4); background: transparent; cursor: pointer; color: #25d366; transition: all 0.2s;"
                                 onmouseover="this.style.background='rgba(37, 211, 102, 0.04)'; this.style.borderColor='#25d366';"
                                 onmouseout="this.style.background='transparent'; this.style.borderColor='rgba(37, 211, 102, 0.4)';">
                                <span class="fw-bold"><i class="ti ti-plus me-1"></i> {{ __('Add WhatsApp Account') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            <!-- Legend (Floating outside viewport so it stays static) -->
            <div class="floating-legend">
                <div class="legend-item">
                    <div class="legend-dot" style="background: #cbd5e1;"></div>
                    <span>{{ __('Connect ports to define workflow') }}</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot" style="background: #3b82f6;"></div>
                    <span>{{ __('Left Port = Target (Input)') }}</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot" style="background: #10b981;"></div>
                    <span>{{ __('Right Port = Source (Output)') }}</span>
                </div>
                <div class="legend-item mt-2 pt-2 border-top">
                    <small class="text-danger fw-bold"><i class="ti ti-info-circle"></i> {{ __('Click connection label to configure / delete') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Selection Modal -->
    <div class="modal fade" id="actionSelectionModal" tabindex="-1" aria-labelledby="actionSelectionModalLabel" aria-hidden="true" style="font-family: 'Outfit', sans-serif;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark fs-5" id="actionSelectionModalLabel">{{ __('Configure Workflow Action') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-muted small mb-4">{{ __('Define the behavior of this stage connection. When a lead enters the source stage, should it copy or move to the target stage?') }}</p>
                    
                    <div class="d-flex flex-column gap-3">
                        <!-- Copy Option Card -->
                        <div class="action-card rounded-3 p-3" id="opt-copy">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-wrap bg-primary-light text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: rgba(59, 130, 246, 0.1);">
                                    <i class="ti ti-copy fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-0 text-dark">{{ __('Copy Lead (Duplicate)') }}</h6>
                                    <small class="text-muted">{{ __('Original remains in source pipeline, duplicate created in target.') }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- Move Option Card -->
                        <div class="action-card rounded-3 p-3" id="opt-move">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-wrap bg-success-light text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: rgba(16, 185, 129, 0.1);">
                                    <i class="ti ti-arrow-right fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-0 text-dark">{{ __('Move Lead (Transfer)') }}</h6>
                                    <small class="text-muted">{{ __('Lead is shifted directly to target pipeline/stage.') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center" id="delete-connection-btn">
                        <i class="ti ti-trash me-1"></i> {{ __('Delete') }}
                    </button>
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Facebook Config Modal -->
    <div class="modal fade" id="facebookConfigModal" tabindex="-1" aria-labelledby="facebookConfigModalLabel" aria-hidden="true" style="font-family: 'Outfit', sans-serif;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark fs-5 d-flex align-items-center" id="facebookConfigModalLabel">
                        <i class="ti ti-brand-facebook text-primary fs-3 me-2" style="color: #1877f2;"></i> {{ __('Facebook Lead Integration') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <!-- Modal Navigation Tabs -->
                    <ul class="nav nav-tabs border-bottom mb-4" id="fbModalTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold text-sm" id="fb-config-tab" data-bs-toggle="tab" data-bs-target="#fb-config-panel" type="button" role="tab" aria-controls="fb-config-panel" aria-selected="true">
                                <i class="ti ti-settings me-1"></i> {{ __('Configuration Mappings') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold text-sm" id="fb-instructions-tab" data-bs-toggle="tab" data-bs-target="#fb-instructions-panel" type="button" role="tab" aria-controls="fb-instructions-panel" aria-selected="false">
                                <i class="ti ti-info-circle me-1"></i> {{ __('Meta Developer Setup Instructions') }}
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="fbModalTabsContent">
                        <!-- Configuration Mappings Tab Panel -->
                        <div class="tab-pane fade show active" id="fb-config-panel" role="tabpanel" aria-labelledby="fb-config-tab">
                            <form id="facebook-config-form">
                                <input type="hidden" name="rule_id" id="fb-rule-id-input">
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-xs">{{ __('Feed Friendly Name') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="page_name" id="fb-page-name-input" class="form-control form-control-sm" placeholder="e.g. Inquiries Page Form" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-xs">{{ __('Facebook Page ID') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="page_id" id="fb-page-id-input" class="form-control form-control-sm" placeholder="e.g. 1029384756" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-xs">{{ __('Page Access Token') }} <span class="text-danger">*</span></label>
                                        <div class="token-group">
                                            <input type="password" name="page_access_token" id="fb-access-token-input" class="form-control form-control-sm" placeholder="EAA..." required>
                                            <button type="button" class="btn-toggle-eye" onclick="toggleTokenVisibility()"><i class="ti ti-eye text-muted fs-5 px-3" id="toggle-token-eye"></i></button>
                                        </div>
                                        <small class="text-muted text-xs d-block mt-1">{{ __('Long-lived page access token with leads_retrieval permission.') }}</small>
                                    </div>
                                     <div class="col-md-6">
                                         <label class="form-label fw-semibold text-xs">{{ __('Facebook Lead Form') }} <span class="text-danger">*</span></label>
                                         <div class="input-group input-group-sm">
                                             <select name="form_id" id="fb-form-id-input" class="form-select form-select-sm" required style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                                 <option value="">{{ __('Select Form') }}</option>
                                             </select>
                                             <button type="button" class="btn btn-primary btn-sm px-3" id="btn-fetch-fb-forms" title="{{ __('Fetch Forms from Meta') }}"><i class="ti ti-refresh text-white"></i></button>
                                         </div>
                                     </div>
                                     <div class="col-md-6">
                                         <label class="form-label fw-semibold text-xs">{{ __('Lead Source') }}</label>
                                         <select name="source_id" id="fb-source-id-input" class="form-select form-select-sm">
                                             <option value="">{{ __('Create or Use "Facebook" Source') }}</option>
                                             @foreach($sources as $src)
                                                 <option value="{{ $src->id }}">{{ $src->name }}</option>
                                             @endforeach
                                         </select>
                                     </div>
                                     
                                     <div class="col-md-6">
                                         <label class="form-label fw-semibold text-xs">{{ __('Target Pipeline') }} <span class="text-danger">*</span></label>
                                         <select name="pipeline_id" id="fb-pipeline-id-input" class="form-select form-select-sm" required>
                                             <option value="">{{ __('Select Pipeline') }}</option>
                                             @foreach($pipelines as $p)
                                                 <option value="{{ $p->id }}">{{ $p->name }}</option>
                                             @endforeach
                                         </select>
                                     </div>

                                     <!-- Mapping Container -->
                                     <div class="col-12 d-none" id="fb-mapping-container">
                                         <hr class="my-3">
                                         <h6 class="fw-bold mb-3 text-dark d-flex align-items-center">
                                             <i class="ti ti-arrows-right-left text-primary me-2"></i> {{ __('Configure Field Mapping') }}
                                         </h6>
                                         <p class="text-muted text-xs mb-3">{{ __('Map the questions (fields) from your Facebook Lead Form to the corresponding CRM lead fields.') }}</p>
                                         
                                         <div class="table-responsive">
                                             <table class="table table-sm align-middle mb-0" style="font-size: 0.8rem;">
                                                 <thead class="table-light">
                                                     <tr>
                                                         <th>{{ __('Facebook Form Field') }}</th>
                                                         <th>{{ __('Maps to CRM Field') }}</th>
                                                     </tr>
                                                 </thead>
                                                 <tbody id="fb-mapping-fields-body">
                                                     <!-- Dynamic mapping rows -->
                                                 </tbody>
                                             </table>
                                         </div>
                                     </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-xs">{{ __('Target Stage') }} <span class="text-danger">*</span></label>
                                        <select name="stage_id" id="fb-stage-id-input" class="form-select form-select-sm" required>
                                            <option value="">{{ __('Select Stage') }}</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-xs">{{ __('Assign Leads To') }}</label>
                                        <select name="user_id" id="fb-user-id-input" class="form-select form-select-sm">
                                            <option value="">{{ __('Select User') }}</option>
                                            @foreach($users as $usr)
                                                <option value="{{ $usr->id }}">{{ $usr->name }} [{{ $usr->type }}]</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="auto_fetch" id="fb-auto-fetch-input" value="1">
                                            <label class="form-check-label fw-bold text-xs" for="fb-auto-fetch-input">
                                                {{ __('Enable Auto-Fetch Scheduler') }}
                                            </label>
                                            <small class="text-muted text-xxs d-block mt-1">
                                                {{ __('If enabled, the background scheduler will automatically pull new leads from this form every 15 minutes as a fallback.') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Setup Instructions Tab Panel -->
                        <div class="tab-pane fade" id="fb-instructions-panel" role="tabpanel" aria-labelledby="fb-instructions-tab">
                            <div class="alert alert-warning border-0 bg-light-warning mb-4 p-3 rounded">
                                <div class="d-flex">
                                    <div class="alert-icon text-warning me-2"><i class="ti ti-alert-triangle f-20"></i></div>
                                    <div class="ms-2">
                                        <h6 class="alert-heading fw-bold mb-1">{{ __('Important HTTPS Requirement') }}</h6>
                                        <p class="text-xs mb-0">{{ __('Facebook webhooks require a secure HTTPS endpoint. For local testing, use ngrok or localltunnel to forward port 80.') }}</p>
                                    </div>
                                </div>
                            </div>

                            <h6 class="fw-bold mb-3 text-dark">{{ __('Meta Setup Walkthrough') }}</h6>
                            <ol class="text-sm g-2" style="padding-left: 20px;">
                                <li class="mb-3">
                                    <strong>{{ __('Create Meta Developer App') }}</strong>: 
                                    {{ __('Navigate to developers.facebook.com and create a Business App.') }}
                                </li>
                                <li class="mb-3">
                                    <strong>{{ __('Configure Webhook Product') }}</strong>:
                                    {{ __('Add Webhooks product, select "Page" from dropdown, click "Subscribe to this object" and enter details:') }}
                                    <div class="p-3 bg-light rounded mt-2 border">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="text-xs fw-semibold">{{ __('Callback URL') }}:</span>
                                            <div class="d-flex gap-2 align-items-center">
                                                <code class="text-xs text-primary" id="webhook-url-code">{{ route('meta.callback') }}</code>
                                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size: 0.65rem;" onclick="copyToClipboard('webhook-url-code')"><i class="ti ti-copy"></i> {{ __('Copy') }}</button>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="text-xs fw-semibold">{{ __('Verify Token') }}:</span>
                                            <div class="d-flex gap-2 align-items-center">
                                                <code class="text-xs text-primary" id="verify-token-code">12345678</code>
                                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size: 0.65rem;" onclick="copyToClipboard('verify-token-code')"><i class="ti ti-copy"></i> {{ __('Copy') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="mb-3">
                                    <strong>{{ __('Subscribe to Leadgen Field') }}</strong>:
                                    {{ __('In Webhook fields subscription list, click "Subscribe" on the "leadgen" row.') }}
                                </li>
                                <li class="mb-3">
                                    <strong>{{ __('Required App Permissions') }}</strong>:
                                    {{ __('Ensure your Page Access Token includes these permissions in Meta Graph Explorer:') }}
                                    <div class="mt-2">
                                        <span class="badge bg-light-secondary text-dark text-xxs me-1">pages_show_list</span>
                                        <span class="badge bg-light-secondary text-dark text-xxs me-1">pages_read_engagement</span>
                                        <span class="badge bg-light-secondary text-dark text-xxs me-1">pages_manage_ads</span>
                                        <span class="badge bg-light-primary text-primary text-xxs">leads_retrieval</span>
                                    </div>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 d-flex justify-content-between">
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center me-1" id="delete-fb-rule-btn" style="display: none;">
                            <i class="ti ti-trash me-1"></i> {{ __('Delete Feed') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-success d-inline-flex align-items-center me-1" id="sync-fb-historical-btn" style="display: none;">
                            <i class="ti ti-refresh me-1" id="sync-historical-icon"></i> <span id="sync-historical-text">{{ __('Sync Historical') }}</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-info d-inline-flex align-items-center" id="test-fb-connection-btn">
                            <i class="ti ti-plug me-1" id="test-conn-icon"></i> <span id="test-conn-text">{{ __('Test Connection') }}</span>
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="button" class="btn btn-sm btn-primary" id="save-fb-rule-btn">{{ __('Save Mapping') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orion Config Modal -->
    <div class="modal fade orion-premium-modal" id="orionConfigModal" tabindex="-1" aria-labelledby="orionConfigModalLabel" aria-hidden="true" style="font-family: 'Outfit', sans-serif;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header border-bottom-0 pb-3">
                    <h5 class="modal-title fw-bold text-dark fs-5 d-flex align-items-center" id="orionConfigModalLabel">
                        <i class="ti ti-api text-primary fs-3 me-2" style="color: #8b5cf6;"></i> {{ __('Orion / FinKORP API Integration') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <!-- Modal Navigation Tabs -->
                    <ul class="nav nav-tabs border-bottom mb-4" id="orionModalTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold text-sm" id="orion-credentials-tab" data-bs-toggle="tab" data-bs-target="#orion-credentials-panel" type="button" role="tab" aria-controls="orion-credentials-panel" aria-selected="true">
                                <i class="ti ti-key"></i> {{ __('Credentials') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold text-sm" id="orion-rules-tab" data-bs-toggle="tab" data-bs-target="#orion-rules-panel" type="button" role="tab" aria-controls="orion-rules-panel" aria-selected="false">
                                <i class="ti ti-settings"></i> {{ __('Workflow & Mappings') }}
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="orionModalTabsContent">
                        <!-- Credentials Tab Panel -->
                        <div class="tab-pane fade show active" id="orion-credentials-panel" role="tabpanel" aria-labelledby="orion-credentials-tab">
                            <form id="orion-credentials-form">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-xs">{{ __('Orion API Base URL') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="base_url" id="orion-base-url-input" class="form-control form-control-sm" placeholder="e.g. http://61.247.230.203:15000/api" value="{{ $orionSettings['credentials']['base_url'] ?? 'http://61.247.230.203:15000/api' }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-xs">{{ __('API Username') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="username" id="orion-username-input" class="form-control form-control-sm" value="{{ $orionSettings['credentials']['username'] ?? '' }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-xs">{{ __('API Password') }} <span class="text-danger">*</span></label>
                                        <div class="token-group">
                                            <input type="password" name="password" id="orion-password-input" class="form-control form-control-sm" value="{{ $orionSettings['credentials']['password'] ?? '' }}" required>
                                            <button type="button" class="btn-toggle-eye-orion" onclick="toggleOrionPasswordVisibility()"><i class="ti ti-eye text-muted fs-5 px-3" id="toggle-orion-password-eye"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-xs">{{ __('Firm ID') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="firm_id" id="orion-firm-id-input" class="form-control form-control-sm" value="{{ $orionSettings['credentials']['firm_id'] ?? '1001' }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-xs">{{ __('Financial Year') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="financial_year" id="orion-financial-year-input" class="form-control form-control-sm" value="{{ $orionSettings['credentials']['financial_year'] ?? '2022-2023' }}" required>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Rule Mapping Tab Panel -->
                        <div class="tab-pane fade" id="orion-rules-panel" role="tabpanel" aria-labelledby="orion-rules-tab">
                            <form id="orion-rule-form">
                                <input type="hidden" name="rule_id" id="orion-rule-id-input">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-xs">{{ __('Target Pipeline') }} <span class="text-danger">*</span></label>
                                        <select name="pipeline_id" id="orion-pipeline-id-input" class="form-select form-select-sm" required>
                                            <option value="">{{ __('Select Pipeline') }}</option>
                                            @foreach($pipelines as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-xs">{{ __('Target Stage') }} <span class="text-danger">*</span></label>
                                        <select name="stage_id" id="orion-stage-id-input" class="form-select form-select-sm" required>
                                            <option value="">{{ __('Select Stage') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-xs">{{ __('Trigger Mode') }} <span class="text-danger">*</span></label>
                                        <select name="trigger_mode" id="orion-trigger-mode-input" class="form-select form-select-sm" required>
                                            <option value="manual_fetch">{{ __('Manual Fetch (Show Button)') }}</option>
                                            <option value="auto_fetch">{{ __('Auto Fetch on Stage Entry') }}</option>
                                            <option value="auto_send_ekyc">{{ __('Auto Send EKYC to Orion') }}</option>
                                            <option value="auto_send_modify">{{ __('Auto Send Modification to Orion') }}</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Dynamic Trigger Mode Description Card -->
                                    <div class="col-12 mt-3" id="orion-trigger-mode-desc-container">
                                        <div class="p-3 rounded-3 border d-flex align-items-start gap-2 bg-light-purple border-purple-light" style="font-size: 0.78rem; background: rgba(139, 92, 246, 0.05); border-color: rgba(139, 92, 246, 0.15) !important;">
                                            <i class="ti ti-info-circle text-purple fs-5 mt-0.5" style="color: #8b5cf6;"></i>
                                            <div>
                                                <strong class="text-purple d-block mb-1" id="orion-trigger-mode-title" style="color: #6d28d9; font-weight: 700;">{{ __('Manual Fetch (Show Button)') }}</strong>
                                                <span class="text-muted" id="orion-trigger-mode-body">{{ __('Adds a "Fetch Orion EKYC" action button to the Lead detail page. Team members can run manual queries at any time to import client data.') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Mappings Section -->
                                    <div class="col-12 mt-4">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 border-bottom pb-3">
                                            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center">
                                                <i class="ti ti-arrows-right-left text-primary me-2" style="color: #8b5cf6;"></i> {{ __('Map API Fields to CRM Fields') }}
                                            </h6>
                                            <div class="d-flex align-items-center gap-2">
                                                <button type="button" class="btn btn-xs btn-light-purple border-0 d-flex align-items-center py-1.5 px-3 rounded-pill" onclick="autoMapOrionFields()" style="font-size: 0.72rem; font-weight: 600; background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                                                    <i class="ti ti-wand me-1"></i> {{ __('Auto Map Fields') }}
                                                </button>
                                                <button type="button" class="btn btn-xs btn-light-danger border-0 d-flex align-items-center py-1.5 px-3 rounded-pill" onclick="clearAllOrionMappings()" style="font-size: 0.72rem; font-weight: 600; background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                                                    <i class="ti ti-trash me-1"></i> {{ __('Clear Mappings') }}
                                                </button>
                                                <div class="input-group search-mapping-container shadow-sm" style="max-width: 200px; border-radius: 20px; overflow: hidden; border: 1.5px solid #cbd5e1;">
                                                    <span class="input-group-text bg-white border-0 pe-1">
                                                        <i class="ti ti-search text-muted" style="font-size: 0.85rem;"></i>
                                                    </span>
                                                    <input type="text" id="orion-mapping-search" class="form-control bg-white border-0 ps-1 py-1" style="font-size: 0.75rem;" placeholder="{{ __('Search parameters...') }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @php
                                            $crmStandardFields = [
                                                'name' => __('Name'),
                                                'email' => __('Email'),
                                                'phone' => __('Phone / Mobile'),
                                                'subject' => __('Subject'),
                                                'pan_number' => __('PAN Number'),
                                                'aadhar_number' => __('Aadhar Number'),
                                                'dp_id' => __('DP ID / Client Code'),
                                            ];
                                            $crmCustomFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', getActiveWorkSpace())->whereNotNull('section_id')->get();
 
                                            $renderSelect = function($key, $label) use ($crmStandardFields, $crmCustomFields) {
                                                $html = '<div class="orion-mapping-row d-flex align-items-center justify-content-between py-3 px-4 mb-2 border rounded-3 position-relative" data-param-key="' . e($key) . '" data-param-label="' . e($label) . '">';
                                                $html .= '  <div class="orion-mapping-param-side d-flex align-items-center">';
                                                $html .= '    <div class="orion-mapping-icon-wrapper d-flex align-items-center justify-content-center me-3">';
                                                $html .= '      <i class="ti ti-plug fs-5"></i>';
                                                $html .= '    </div>';
                                                $html .= '    <div class="orion-mapping-param-label d-flex flex-column">';
                                                $html .= '      <span class="fw-bold text-sm text-dark">' . e($label) . '</span>';
                                                $html .= '      <code class="orion-mapping-param-code mt-0.5">' . e($key) . '</code>';
                                                $html .= '    </div>';
                                                $html .= '  </div>';
                                                $html .= '  <div class="orion-connector-line flex-grow-1 mx-3 d-none d-md-flex align-items-center justify-content-center">';
                                                $html .= '    <span class="orion-connector-dot"></span>';
                                                $html .= '    <div class="orion-connector-dash"></div>';
                                                $html .= '    <span class="orion-connector-badge"><i class="ti ti-arrows-right-left text-muted"></i></span>';
                                                $html .= '  </div>';
                                                $html .= '  <div class="orion-mapping-select-side d-flex align-items-center gap-3">';
                                                $html .= '    <span class="badge orion-mapped-status-pill unmapped-pill"><i class="ti ti-circle-x me-1"></i>Unmapped</span>';
                                                $html .= '    <div class="orion-mapping-select-container">';
                                                $html .= '      <select class="form-select form-select-sm orion-crm-field-select choices" searchEnabled="true" data-orion-key="' . e($key) . '">';
                                                $html .= '        <option value="">' . e(__('Don\'t map this field')) . '</option>';
                                                $html .= '        <optgroup label="' . e(__('Standard Fields')) . '">';
                                                foreach ($crmStandardFields as $crmKey => $crmLabel) {
                                                    $html .= '        <option value="' . e($crmKey) . '">' . e($crmLabel) . '</option>';
                                                }
                                                $html .= '        </optgroup>';
                                                if ($crmCustomFields->count() > 0) {
                                                    $html .= '    <optgroup label="' . e(__('Custom Fields')) . '">';
                                                    foreach ($crmCustomFields as $field) {
                                                        $html .= '    <option value="custom_' . e($field->id) . '">' . e($field->name) . '</option>';
                                                    }
                                                    $html .= '    </optgroup>';
                                                }
                                                $html .= '      </select>';
                                                $html .= '    </div>';
                                                $html .= '  </div>';
                                                $html .= '</div>';
                                                return $html;
                                            };
                                        @endphp

                                        <!-- Mapping Progress Bar -->
                                        <div class="mb-4">
                                            <div class="d-flex align-items-center justify-content-between text-xs text-muted w-100 mb-1" style="font-size: 0.75rem; font-weight: 600;">
                                                <span>{{ __('Fields mapped:') }} <span class="text-purple fw-bold" id="orion-mapped-count-text">0</span> / 42</span>
                                                <span class="text-purple fw-bold" id="orion-mapping-percent-text">0% {{ __('Completed') }}</span>
                                            </div>
                                            <div class="w-100 bg-light rounded-pill overflow-hidden" style="height: 6px; background: #e2e8f0 !important;">
                                                <div class="progress-bar" id="orion-mapping-progress" style="width: 0%; height: 100%; transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1); background-color: #8b5cf6;"></div>
                                            </div>
                                        </div>

                                        <div class="accordion orion-mapping-accordion" id="orionMappingAccordion">
                                            <!-- Accordion Item 1 -->
                                            <div class="accordion-item border-0 mb-3" style="border-radius: 12px; overflow: visible; box-shadow: 0 4px 10px rgba(0,0,0,0.02); border: 1px solid #e2e8f0 !important;">
                                                <h2 class="accordion-header" id="headingOne">
                                                    <button class="accordion-button fw-bold text-dark collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne" style="background: #ffffff; font-size: 0.85rem; padding: 14px 20px;">
                                                        <i class="ti ti-user-circle text-primary me-2 fs-5" style="color: #8b5cf6 !important;"></i> {{ __('1. Personal Profile & Demographics') }}
                                                        <span class="badge orion-accordion-badge bg-light text-muted ms-auto me-3" id="orion-badge-collapseOne">0 / 17 Mapped</span>
                                                    </button>
                                                </h2>
                                                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#orionMappingAccordion">
                                                    <div class="accordion-body p-0" style="overflow: visible;">
                                                        {!! $renderSelect('ClientCode', __('Client Code')) !!}
                                                        {!! $renderSelect('PanNo', __('PAN Number')) !!}
                                                        {!! $renderSelect('ClientName', __('Client Name / Name')) !!}
                                                        {!! $renderSelect('Dob', __('Date of Birth')) !!}
                                                        {!! $renderSelect('Aadhar', __('Aadhar Number')) !!}
                                                        {!! $renderSelect('FatherName', __('Father Name')) !!}
                                                        {!! $renderSelect('MotherName', __('Mother Name')) !!}
                                                        {!! $renderSelect('Gender', __('Gender (M/F/T)')) !!}
                                                        {!! $renderSelect('MaritalStatus', __('Marital Status (M/S/etc.)')) !!}
                                                        {!! $renderSelect('Occupation', __('Occupation Slabs')) !!}
                                                        {!! $renderSelect('PEP', __('PEP Status (Politically Exposed)')) !!}
                                                        {!! $renderSelect('OpenDate', __('Account Opening Date')) !!}
                                                        {!! $renderSelect('AnnualIncome', __('Annual Income')) !!}
                                                        {!! $renderSelect('AnnualIncomeDate', __('Annual Income Date')) !!}
                                                        {!! $renderSelect('NetWorth', __('Net Worth')) !!}
                                                        {!! $renderSelect('NetWorthDate', __('Net Worth Date')) !!}
                                                        {!! $renderSelect('RiskCategory', __('Risk Category')) !!}
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Accordion Item 2 -->
                                            <div class="accordion-item border-0 mb-3" style="border-radius: 12px; overflow: visible; box-shadow: 0 4px 10px rgba(0,0,0,0.02); border: 1px solid #e2e8f0 !important;">
                                                <h2 class="accordion-header" id="headingTwo">
                                                    <button class="accordion-button fw-bold text-dark collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo" style="background: #ffffff; font-size: 0.85rem; padding: 14px 20px;">
                                                        <i class="ti ti-address-book text-primary me-2 fs-5" style="color: #8b5cf6 !important;"></i> {{ __('2. Contact & Address Details') }}
                                                        <span class="badge orion-accordion-badge bg-light text-muted ms-auto me-3" id="orion-badge-collapseTwo">0 / 9 Mapped</span>
                                                    </button>
                                                </h2>
                                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#orionMappingAccordion">
                                                    <div class="accordion-body p-0" style="overflow: visible;">
                                                        {!! $renderSelect('ContactNo', __('Mobile / Phone')) !!}
                                                        {!! $renderSelect('ContactEmail', __('Email Address')) !!}
                                                        {!! $renderSelect('AddressLine1', __('Address Line 1')) !!}
                                                        {!! $renderSelect('AddressLine2', __('Address Line 2')) !!}
                                                        {!! $renderSelect('AddressLine3', __('Address Line 3')) !!}
                                                        {!! $renderSelect('AddressCity', __('City')) !!}
                                                        {!! $renderSelect('AddressPincode', __('Pincode')) !!}
                                                        {!! $renderSelect('AddressState', __('State Code (e.g. DL, MH)')) !!}
                                                        {!! $renderSelect('AddressCountry', __('Country')) !!}
                                                    </div>
                                                </div>
                                            </div>
 
                                            <!-- Accordion Item 3 -->
                                            <div class="accordion-item border-0 mb-3" style="border-radius: 12px; overflow: visible; box-shadow: 0 4px 10px rgba(0,0,0,0.02); border: 1px solid #e2e8f0 !important;">
                                                <h2 class="accordion-header" id="headingThree">
                                                    <button class="accordion-button fw-bold text-dark collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree" style="background: #ffffff; font-size: 0.85rem; padding: 14px 20px;">
                                                        <i class="ti ti-wallet text-primary me-2 fs-5" style="color: #8b5cf6 !important;"></i> {{ __('3. Financial, Depository & Nominee Details') }}
                                                        <span class="badge orion-accordion-badge bg-light text-muted ms-auto me-3" id="orion-badge-collapseThree">0 / 16 Mapped</span>
                                                    </button>
                                                </h2>
                                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#orionMappingAccordion">
                                                    <div class="accordion-body p-0" style="overflow: visible;">
                                                        {!! $renderSelect('BankAccountNumber', __('Bank Account Number')) !!}
                                                        {!! $renderSelect('BankIFSC', __('Bank IFSC')) !!}
                                                        {!! $renderSelect('BankName', __('Bank Name')) !!}
                                                        {!! $renderSelect('FundMandate', __('Fund Mandate (Y/N)')) !!}
                                                        {!! $renderSelect('DepositoryClientID', __('Depository Client ID / DP ID')) !!}
                                                        {!! $renderSelect('DepositoryType', __('Depository Type (NSDL/CDSL)')) !!}
                                                        {!! $renderSelect('NomineeName', __('Nominee Name')) !!}
                                                        {!! $renderSelect('NomineeRelation', __('Nominee Relation')) !!}
                                                        {!! $renderSelect('NomineeDOB', __('Nominee DOB')) !!}
                                                        {!! $renderSelect('NomineePAN', __('Nominee PAN')) !!}
                                                        {!! $renderSelect('NomineeShare', __('Nominee Share Percentage')) !!}
                                                        {!! $renderSelect('TradingSoftwareType', __('Trading Software Type')) !!}
                                                        {!! $renderSelect('PayoutFlag', __('Payout Flag (Y/N/P)')) !!}
                                                        {!! $renderSelect('BankAccountType', __('Bank Account Type')) !!}
                                                        {!! $renderSelect('ECSLimit', __('ECS Mandate Limit Amount')) !!}
                                                        {!! $renderSelect('UMRN', __('Unique Mandate Reference / UMRN')) !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 d-flex justify-content-between">
                    <div>
                        <button type="button" class="btn btn-capsule btn-delete align-items-center me-1" id="delete-orion-rule-btn" style="display: none;">
                            <i class="ti ti-trash me-1"></i> {{ __('Delete Rule') }}
                        </button>
                        <button type="button" class="btn btn-capsule btn-test align-items-center" id="test-orion-connection-btn">
                            <i class="ti ti-plug me-1" id="test-orion-conn-icon"></i> <span id="test-orion-conn-text">{{ __('Test Connection') }}</span>
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-capsule btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="button" class="btn btn-capsule btn-save" id="save-orion-rule-btn">{{ __('Save Configuration') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('canvas');
            const svgCanvas = document.getElementById('svg-canvas');
            
            // Loaded rules and positions from settings
            const savedData = @json($workflowData);
            const initialRules = savedData.rules || [];
            const savedPositions = Object.assign({}, savedData.positions || {}, @json($orionSettings['positions'] ?? []));
            const pipelinesData = @json($pipelines);

            let activeLine = null;
            let currentDragSource = null;
            let connections = []; // Array of {fromPipelineId, fromStageId, toPipelineId, toStageId, pathElement, action, labelElement}

            // Modal related
            const modalEl = document.getElementById('actionSelectionModal');
            const modalObj = new bootstrap.Modal(modalEl);
            let currentEditingConnection = null;
            let isNewConnection = false;
            let actionChosen = false;

            function openConnectionModal(conn, isNew) {
                currentEditingConnection = conn;
                isNewConnection = isNew;
                actionChosen = false;

                // Reset selection styles
                document.querySelectorAll('.action-card').forEach(card => card.classList.remove('selected'));

                // Select current action card
                const action = conn.action || 'copy';
                const activeCard = document.getElementById('opt-' + action);
                if (activeCard) {
                    activeCard.classList.add('selected');
                }

                // Toggle delete button visibility: show if editing, hide if new
                const deleteBtn = document.getElementById('delete-connection-btn');
                if (isNew) {
                    deleteBtn.style.display = 'none';
                } else {
                    deleteBtn.style.display = 'inline-flex';
                }

                modalObj.show();
            }

            function selectAction(action) {
                actionChosen = true;
                currentEditingConnection.action = action;
                modalObj.hide();

                if (isNewConnection) {
                    // Create label element
                    const label = document.createElement('div');
                    label.className = 'connection-label ' + (action === 'move' ? 'move-label' : 'copy-label');
                    label.innerText = action === 'move' ? '{{ __("Move") }}' : '{{ __("Copy") }}';
                    canvas.appendChild(label);

                    currentEditingConnection.labelElement = label;

                    // Add delete/edit handler on click
                    const connRef = currentEditingConnection;
                    const clickHandler = function() {
                        openConnectionModal(connRef, false);
                    };
                    currentEditingConnection.pathElement.addEventListener('click', clickHandler);
                    label.addEventListener('click', clickHandler);

                    // Make line interactive
                    currentEditingConnection.pathElement.style.pointerEvents = 'auto';

                    connections.push(currentEditingConnection);
                } else {
                    // Update existing label class and text
                    const label = currentEditingConnection.labelElement;
                    if (label) {
                        label.className = 'connection-label ' + (action === 'move' ? 'move-label' : 'copy-label');
                        label.innerText = action === 'move' ? '{{ __("Move") }}' : '{{ __("Copy") }}';
                    }
                }

                redrawConnections();
            }

            document.getElementById('opt-copy').addEventListener('click', function() {
                selectAction('copy');
            });
            document.getElementById('opt-move').addEventListener('click', function() {
                selectAction('move');
            });

            document.getElementById('delete-connection-btn').addEventListener('click', function() {
                if (currentEditingConnection) {
                    // Remove from connections array
                    connections = connections.filter(c => c !== currentEditingConnection);
                    
                    // Remove elements from DOM
                    if (currentEditingConnection.pathElement) {
                        currentEditingConnection.pathElement.remove();
                    }
                    if (currentEditingConnection.labelElement) {
                        currentEditingConnection.labelElement.remove();
                    }
                    
                    modalObj.hide();
                    currentEditingConnection = null;
                }
            });

            modalEl.addEventListener('hidden.bs.modal', function () {
                if (isNewConnection && !actionChosen) {
                    if (currentEditingConnection && currentEditingConnection.pathElement) {
                        currentEditingConnection.pathElement.remove();
                    }
                }
                currentEditingConnection = null;
            });

            // 1. Position cards initially
            const cards = document.querySelectorAll('.pipeline-card');
            const spacingX = 300;
            const spacingY = 50;
            let currentX = 50;
            let currentY = 50;

            cards.forEach(card => {
                const id = card.getAttribute('data-pipeline-id');
                if (savedPositions['pipeline_' + id]) {
                    card.style.left = savedPositions['pipeline_' + id].x + 'px';
                    card.style.top = savedPositions['pipeline_' + id].y + 'px';
                } else {
                    card.style.left = currentX + 'px';
                    card.style.top = currentY + 'px';
                    currentX += spacingX;
                    if (currentX > canvas.clientWidth - 260) {
                        currentX = 50;
                        currentY += spacingY + 250;
                    }
                }
                makeDraggable(card);
            });

            // 2. Drag & Drop Connection Logic
            document.querySelectorAll('.port').forEach(port => {
                port.addEventListener('mousedown', function(e) {
                    e.stopPropagation();
                    if (this.getAttribute('data-type') !== 'output') return;

                    currentDragSource = this;
                    this.classList.add('active-drag');

                    // Create dynamic svg path
                    activeLine = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    activeLine.setAttribute('class', 'connection-line');
                    activeLine.setAttribute('marker-end', 'url(#arrow)');
                    svgCanvas.appendChild(activeLine);

                    document.addEventListener('mousemove', onDragConnection);
                    document.addEventListener('mouseup', onStopDragConnection);
                });
            });

            function getPortCoordinates(port) {
                const portRect = port.getBoundingClientRect();
                const canvasRect = canvas.getBoundingClientRect();
                return {
                    x: portRect.left - canvasRect.left + (portRect.width / 2),
                    y: portRect.top - canvasRect.top + (portRect.height / 2)
                };
            }

            function onDragConnection(e) {
                if (!activeLine || !currentDragSource) return;

                const canvasRect = canvas.getBoundingClientRect();
                const start = getPortCoordinates(currentDragSource);
                const end = {
                    x: e.clientX - canvasRect.left,
                    y: e.clientY - canvasRect.top
                };

                drawBezierCurve(activeLine, start.x, start.y, end.x, end.y);
            }

            function drawBezierCurve(pathElement, x1, y1, x2, y2) {
                const controlOffset = Math.abs(x2 - x1) * 0.5;
                const d = `M ${x1} ${y1} C ${x1 + controlOffset} ${y1}, ${x2 - controlOffset} ${y2}, ${x2} ${y2}`;
                pathElement.setAttribute('d', d);
            }

            function onStopDragConnection(e) {
                document.removeEventListener('mousemove', onDragConnection);
                document.removeEventListener('mouseup', onStopDragConnection);

                if (currentDragSource) {
                    currentDragSource.classList.remove('active-drag');
                }

                const targetEl = document.elementFromPoint(e.clientX, e.clientY);
                const targetPort = targetEl ? targetEl.closest('.port') : null;

                if (targetPort && targetPort.getAttribute('data-type') === 'input') {
                    const fromStage = currentDragSource.closest('.stage-node');
                    const toStage = targetPort.closest('.stage-node');

                    const fromPipelineId = currentDragSource.closest('.pipeline-card').getAttribute('data-pipeline-id');
                    const toPipelineId = targetPort.closest('.pipeline-card').getAttribute('data-pipeline-id');
                    const toStageId = toStage.getAttribute('data-stage-id');
                    const fromStageId = fromStage ? fromStage.getAttribute('data-stage-id') : null;

                    if (fromPipelineId === 'facebook') {
                        // Dragged from Facebook rule node to Pipeline Stage!
                        const ruleId = fromStage ? fromStage.getAttribute('data-rule-id') : null;
                        if (ruleId) {
                            const rule = fbRules.find(r => r.id === ruleId);
                            if (rule) {
                                rule.pipeline_id = toPipelineId;
                                rule.stage_id = toStageId;
                                openFbModalWithRule(rule);
                            }
                        } else {
                            openFbModalForNew(toPipelineId, toStageId);
                        }
                        activeLine.remove();
                        activeLine = null;
                        currentDragSource = null;
                        return;
                    }

                    if (fromPipelineId === 'webhook') {
                        const ruleId = fromStage ? fromStage.getAttribute('data-rule-id') : null;
                        if (ruleId) {
                            const btnSave = document.getElementById('save-automations');
                            const origText = btnSave.innerHTML;
                            btnSave.innerHTML = '<i class="ti ti-loader animate-spin"></i> Mapping...';
                            btnSave.disabled = true;

                            fetch('{{ route("crm.automations.webhook-endpoints.update-stage") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    id: ruleId,
                                    pipeline_id: toPipelineId,
                                    stage_id: toStageId
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    toastrs('Success', data.message, 'success');
                                    setTimeout(() => window.location.reload(), 1000);
                                } else {
                                    toastrs('Error', data.message || 'Failed to update Webhook Endpoint mapping', 'error');
                                    btnSave.innerHTML = origText;
                                    btnSave.disabled = false;
                                }
                            })
                            .catch(err => {
                                console.error('Error:', err);
                                toastrs('Error', 'Server connection error', 'error');
                                btnSave.innerHTML = origText;
                                btnSave.disabled = false;
                            });
                        }
                        activeLine.remove();
                        activeLine = null;
                        currentDragSource = null;
                        return;
                    }

                    if (fromPipelineId === 'orion') {
                        const ruleId = fromStage ? fromStage.getAttribute('data-rule-id') : null;
                        if (ruleId) {
                            const rule = orionRules.find(r => r.id === ruleId);
                            if (rule) {
                                rule.pipeline_id = toPipelineId;
                                rule.stage_id = toStageId;
                                openOrionModalWithRule(rule);
                            }
                        } else {
                            openOrionModalForNew(toPipelineId, toStageId);
                        }
                        activeLine.remove();
                        activeLine = null;
                        currentDragSource = null;
                        return;
                    }

                    if (fromPipelineId === 'whatsapp') {
                        const configId = fromStage ? fromStage.getAttribute('data-config-id') : null;
                        if (configId) {
                            const btnSave = document.getElementById('save-automations');
                            const origText = btnSave.innerHTML;
                            btnSave.innerHTML = '<i class="ti ti-loader animate-spin"></i> Mapping...';
                            btnSave.disabled = true;

                            fetch('{{ route("crm.automations.whatsapp.update-stage") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    id: configId,
                                    pipeline_id: toPipelineId,
                                    stage_id: toStageId
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    toastrs('Success', data.message, 'success');
                                    setTimeout(() => window.location.reload(), 1000);
                                } else {
                                    toastrs('Error', data.message || 'Failed to update WhatsApp configuration mapping', 'error');
                                    btnSave.innerHTML = origText;
                                    btnSave.disabled = false;
                                }
                            })
                            .catch(err => {
                                console.error('Error:', err);
                                toastrs('Error', 'Server connection error', 'error');
                                btnSave.innerHTML = origText;
                                btnSave.disabled = false;
                            });
                        }
                        activeLine.remove();
                        activeLine = null;
                        currentDragSource = null;
                        return;
                    }

                    // Check for duplicate connections
                    const exists = connections.some(c => c.fromStageId === fromStageId && c.toStageId === toStageId);

                    if (!exists && fromStageId !== toStageId) {
                        activeLine.setAttribute('data-from-stage', fromStageId);
                        activeLine.setAttribute('data-to-stage', toStageId);
                        
                        let newConn = {
                            fromPipelineId,
                            fromStageId,
                            toPipelineId,
                            toStageId,
                            pathElement: activeLine,
                            action: 'copy' // default
                        };

                        openConnectionModal(newConn, true);
                    } else {
                        activeLine.remove();
                    }
                } else {
                    activeLine.remove();
                }

                activeLine = null;
                currentDragSource = null;
            }

            // 3. Make Pipeline Cards Draggable
            function makeDraggable(element) {
                const handle = element.querySelector('.drag-handle');
                let offsetX = 0, offsetY = 0;

                handle.addEventListener('mousedown', function(e) {
                    offsetX = e.clientX - element.offsetLeft;
                    offsetY = e.clientY - element.offsetTop;
                    
                    document.addEventListener('mousemove', onDragCard);
                    document.addEventListener('mouseup', onStopDragCard);
                });

                function onDragCard(e) {
                    const newX = e.clientX - offsetX;
                    const newY = e.clientY - offsetY;

                    // Boundaries checking
                    if (newX >= 0 && newX <= canvas.clientWidth - element.clientWidth) {
                        element.style.left = newX + 'px';
                    }
                    if (newY >= 0 && newY <= canvas.clientHeight - element.clientHeight) {
                        element.style.top = newY + 'px';
                    }

                    redrawConnections();
                }

                function onStopDragCard() {
                    document.removeEventListener('mousemove', onDragCard);
                    document.removeEventListener('mouseup', onStopDragCard);
                }
            }

            // 4. Draw & Redraw Connection Lines
            function redrawConnections() {
                connections.forEach(conn => {
                    let fromPort = null;
                    let toPort = null;

                    if (conn.isFb || conn.isWh || conn.isOrion || conn.isWhatsApp) {
                        fromPort = conn.fromPort;
                        toPort = conn.toPort;
                    } else {
                        fromPort = document.querySelector(`#stage-${conn.fromStageId} .output-port`);
                        toPort = document.querySelector(`#stage-${conn.toStageId} .input-port`);
                    }

                    if (fromPort && toPort) {
                        const start = getPortCoordinates(fromPort);
                        const end = getPortCoordinates(toPort);
                        drawBezierCurve(conn.pathElement, start.x, start.y, end.x, end.y);

                        if (conn.labelElement) {
                            const midX = (start.x + end.x) / 2;
                            const midY = (start.y + end.y) / 2;
                            conn.labelElement.style.left = midX + 'px';
                            conn.labelElement.style.top = midY + 'px';
                        }
                    }
                });
            }

            // 5. Restore Initial Connections
            initialRules.forEach(rule => {
                const fromPort = document.querySelector(`#stage-${rule.from_stage_id} .output-port`);
                const toPort = document.querySelector(`#stage-${rule.to_stage_id} .input-port`);

                if (fromPort && toPort) {
                    const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    path.setAttribute('class', 'connection-line');
                    path.setAttribute('marker-end', 'url(#arrow)');
                    path.setAttribute('data-from-stage', rule.from_stage_id);
                    path.setAttribute('data-to-stage', rule.to_stage_id);
                    path.style.pointerEvents = 'auto';

                    svgCanvas.appendChild(path);

                    const action = rule.action || 'copy';

                    // Create label element
                    const label = document.createElement('div');
                    label.className = 'connection-label ' + (action === 'move' ? 'move-label' : 'copy-label');
                    label.innerText = action === 'move' ? '{{ __("Move") }}' : '{{ __("Copy") }}';
                    canvas.appendChild(label);

                    const connObj = {
                        fromPipelineId: rule.from_pipeline_id,
                        fromStageId: rule.from_stage_id,
                        toPipelineId: rule.to_pipeline_id,
                        toStageId: rule.to_stage_id,
                        pathElement: path,
                        action: action,
                        labelElement: label
                    };

                    const clickHandler = function() {
                        openConnectionModal(connObj, false);
                    };

                    path.addEventListener('click', clickHandler);
                    label.addEventListener('click', clickHandler);

                    connections.push(connObj);
                }
            });
            setTimeout(redrawConnections, 300);

            // 6. Reset Layout Action
            document.getElementById('reset-layout').addEventListener('click', function() {
                let currentX = 50;
                let currentY = 50;
                cards.forEach(card => {
                    card.style.left = currentX + 'px';
                    card.style.top = currentY + 'px';
                    currentX += spacingX;
                    if (currentX > canvas.clientWidth - 260) {
                        currentX = 50;
                        currentY += spacingY + 250;
                    }
                });
                redrawConnections();
            });

            // 7. Save Automations Action (AJAX payload)
            document.getElementById('save-automations').addEventListener('click', function() {
                const rules = connections.filter(conn => !conn.isFb && !conn.isWh && !conn.isOrion && !conn.isWhatsApp).map(conn => ({
                    from_pipeline_id: conn.fromPipelineId,
                    from_stage_id: conn.fromStageId,
                    to_pipeline_id: conn.toPipelineId,
                    to_stage_id: conn.toStageId,
                    action: conn.action || 'copy'
                }));

                const positions = {};
                cards.forEach(card => {
                    const id = card.getAttribute('data-pipeline-id');
                    positions['pipeline_' + id] = {
                        x: parseInt(card.style.left),
                        y: parseInt(card.style.top)
                    };
                });

                const btn = this;
                const origText = btn.innerHTML;
                btn.innerHTML = '<i class="ti ti-loader animate-spin"></i> Processing...';
                btn.disabled = true;

                fetch('{{ route("crm.automations.save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ rules, positions })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastrs('Success', data.message, 'success');
                    } else {
                        toastrs('Error', data.message || 'Something went wrong', 'error');
                    }
                    btn.innerHTML = origText;
                    btn.disabled = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastrs('Error', 'Network connection error', 'error');
                    btn.innerHTML = origText;
                    btn.disabled = false;
                });
            });

            // ==========================================
            // Facebook Lead Ads Integration Javascript
            // ==========================================
            const fbRules = @json($fbSettings);
            @php
                $crmCustomFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', getActiveWorkSpace())->whereNotNull('section_id')->get();
            @endphp
            const crmCustomFields = @json($crmCustomFields);
            const fbModalEl = document.getElementById('facebookConfigModal');
            const fbModalObj = new bootstrap.Modal(fbModalEl);

            function populateFbStages(pipelineId, selectedStageId = null) {
                const pipeline = pipelinesData.find(p => p.id == pipelineId);
                const stages = pipeline ? pipeline.lead_stages : [];
                const stageSelect = document.getElementById('fb-stage-id-input');
                
                stageSelect.innerHTML = '<option value="">{{ __("Select Stage") }}</option>';
                stages.forEach(stage => {
                    const opt = document.createElement('option');
                    opt.value = stage.id;
                    opt.textContent = stage.name;
                    if (stage.id == selectedStageId) {
                        opt.selected = true;
                    }
                    stageSelect.appendChild(opt);
                });
            }

            document.getElementById('fb-pipeline-id-input').addEventListener('change', function() {
                populateFbStages(this.value);
                
                // Refresh Facebook form questions mapping fields list with new pipeline filter
                const formId = document.getElementById('fb-form-id-input').value;
                const token = document.getElementById('fb-access-token-input').value;
                const pageId = document.getElementById('fb-page-id-input').value;
                const ruleId = document.getElementById('fb-rule-id-input').value;
                
                if (formId && token) {
                    let savedMapping = {};
                    if (ruleId) {
                        const rule = fbRules.find(r => r.id === ruleId);
                        if (rule) savedMapping = rule.field_mapping;
                    }
                    fetchFbQuestions(formId, token, savedMapping, pageId);
                }
            });

            window.toggleTokenVisibility = function() {
                const tokenInput = document.getElementById('fb-access-token-input');
                const eyeIcon = document.getElementById('toggle-token-eye');
                if (tokenInput.type === 'password') {
                    tokenInput.type = 'text';
                    eyeIcon.className = 'ti ti-eye-off text-muted fs-5 px-3';
                } else {
                    tokenInput.type = 'password';
                    eyeIcon.className = 'ti ti-eye text-muted fs-5 px-3';
                }
            };

            window.copyToClipboard = function(elementId) {
                const text = document.getElementById(elementId).innerText;
                navigator.clipboard.writeText(text).then(() => {
                    toastrs('Success', '{{ __("Copied to clipboard!") }}', 'success');
                }).catch(err => {
                    console.error('Failed to copy token:', err);
                });
            };

            // Fetch Page forms from Facebook
            function fetchFbForms(pageId, token, selectedFormId = null, savedMapping = null) {
                const formSelect = document.getElementById('fb-form-id-input');
                formSelect.innerHTML = '<option value="">{{ __("Loading forms...") }}</option>';
                
                const btn = document.getElementById('btn-fetch-fb-forms');
                const origHtml = btn.innerHTML;
                btn.innerHTML = '<i class="ti ti-loader animate-spin text-white"></i>';
                btn.disabled = true;

                fetch(`{{ route("crm.automations.facebook.fetch-forms") }}?page_id=${encodeURIComponent(pageId)}&page_access_token=${encodeURIComponent(token)}`)
                    .then(response => response.json())
                    .then(data => {
                        btn.innerHTML = origHtml;
                        btn.disabled = false;

                        if (data.success) {
                            let finalToken = token;
                            if (data.page_access_token) {
                                document.getElementById('fb-access-token-input').value = data.page_access_token;
                                finalToken = data.page_access_token;
                            }
                            formSelect.innerHTML = '<option value="">{{ __("Select Form") }}</option>';
                            data.forms.forEach(f => {
                                const opt = document.createElement('option');
                                opt.value = f.id;
                                opt.textContent = f.name;
                                if (f.id == selectedFormId) {
                                    opt.selected = true;
                                }
                                formSelect.appendChild(opt);
                            });

                            if (selectedFormId) {
                                fetchFbQuestions(selectedFormId, finalToken, savedMapping, pageId);
                            }
                        } else {
                            formSelect.innerHTML = '<option value="">{{ __("Error loading forms") }}</option>';
                            toastrs('Error', data.message || 'Failed to load forms', 'error');
                        }
                    })
                    .catch(err => {
                        btn.innerHTML = origHtml;
                        btn.disabled = false;
                        formSelect.innerHTML = '<option value="">{{ __("Connection error") }}</option>';
                        toastrs('Error', 'Connection error while fetching forms', 'error');
                    });
            }

            // Fetch Form questions/fields
            function fetchFbQuestions(formId, token, savedMapping = {}, pageId = '') {
                const container = document.getElementById('fb-mapping-container');
                const tbody = document.getElementById('fb-mapping-fields-body');
                tbody.innerHTML = '<tr><td colspan="2" class="text-center py-3"><i class="ti ti-loader animate-spin text-primary"></i> {{ __("Loading form questions...") }}</td></tr>';
                container.classList.remove('d-none');

                const pipelineId = document.getElementById('fb-pipeline-id-input').value;

                fetch(`{{ route("crm.automations.facebook.fetch-questions") }}?form_id=${encodeURIComponent(formId)}&page_access_token=${encodeURIComponent(token)}&page_id=${encodeURIComponent(pageId)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.page_access_token) {
                                document.getElementById('fb-access-token-input').value = data.page_access_token;
                            }
                            tbody.innerHTML = '';
                            if (data.questions.length === 0) {
                                tbody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">{{ __("No questions found on this form.") }}</td></tr>';
                                return;
                            }

                            data.questions.forEach(q => {
                                const tr = document.createElement('tr');
                                
                                // Determine selected mapping value
                                let selectedVal = '';
                                if (savedMapping) {
                                    if (savedMapping.name === q.key) selectedVal = 'name';
                                    else if (savedMapping.email === q.key) selectedVal = 'email';
                                    else if (savedMapping.phone === q.key) selectedVal = 'phone';
                                    else if (savedMapping.subject === q.key) selectedVal = 'subject';
                                    else if (savedMapping.custom) {
                                        for (const [cfId, key] of Object.entries(savedMapping.custom)) {
                                            if (key === q.key) {
                                                selectedVal = 'custom_' + cfId;
                                                break;
                                            }
                                        }
                                    }
                                }

                                // If not pre-selected, let's do smart auto-detection
                                if (!selectedVal) {
                                    const lowKey = q.key.toLowerCase();
                                    if (lowKey === 'full_name' || lowKey === 'name') selectedVal = 'name';
                                    else if (lowKey === 'email') selectedVal = 'email';
                                    else if (lowKey === 'phone' || lowKey === 'phone_number' || lowKey === 'mobile' || lowKey === 'mobile_number') selectedVal = 'phone';
                                }

                                tr.innerHTML = `
                                    <td class="fw-bold">${q.label} <br><small class="text-muted text-xxs">${q.key} [${q.type}]</small></td>
                                    <td>
                                        <select class="form-select form-select-sm fb-crm-field-select" data-fb-key="${q.key}">
                                            <option value="">{{ __("Do Not Map") }}</option>
                                            <option value="name" ${selectedVal === 'name' ? 'selected' : ''}>{{ __("CRM Lead Field: Name") }}</option>
                                            <option value="email" ${selectedVal === 'email' ? 'selected' : ''}>{{ __("CRM Lead Field: Email") }}</option>
                                            <option value="phone" ${selectedVal === 'phone' ? 'selected' : ''}>{{ __("CRM Lead Field: Phone") }}</option>
                                            <option value="subject" ${selectedVal === 'subject' ? 'selected' : ''}>{{ __("CRM Lead Field: Subject") }}</option>
                                            ${crmCustomFields.filter(cf => !cf.pipeline_id || cf.pipeline_id == pipelineId).map(cf => `<option value="custom_${cf.id}" ${selectedVal === 'custom_' + cf.id ? 'selected' : ''}>{{ __("CRM Custom: ") }}${cf.name}</option>`).join('')}
                                        </select>
                                    </td>
                                `;
                                tbody.appendChild(tr);
                            });
                        } else {
                            tbody.innerHTML = `<tr><td colspan="2" class="text-center text-danger"><i class="ti ti-alert-circle"></i> ${data.message || 'Error loading fields'}</td></tr>`;
                        }
                    })
                    .catch(err => {
                        tbody.innerHTML = '<tr><td colspan="2" class="text-center text-danger"><i class="ti ti-alert-circle"></i> Connection error.</td></tr>';
                    });
            }

            document.getElementById('btn-fetch-fb-forms').addEventListener('click', function() {
                const pageId = document.getElementById('fb-page-id-input').value;
                const token = document.getElementById('fb-access-token-input').value;
                if (!pageId || !token) {
                    toastrs('Warning', '{{ __("Please enter Page ID and Access Token first.") }}', 'warning');
                    return;
                }
                fetchFbForms(pageId, token);
            });

            document.getElementById('fb-form-id-input').addEventListener('change', function() {
                const token = document.getElementById('fb-access-token-input').value;
                const pageId = document.getElementById('fb-page-id-input').value;
                if (this.value && token) {
                    fetchFbQuestions(this.value, token, {}, pageId);
                } else {
                    document.getElementById('fb-mapping-container').classList.add('d-none');
                }
            });

            function openFbModalForNew(pipelineId = '', stageId = '') {
                document.getElementById('facebook-config-form').reset();
                document.getElementById('fb-rule-id-input').value = '';
                document.getElementById('fb-auto-fetch-input').checked = false;
                document.getElementById('delete-fb-rule-btn').style.display = 'none';
                document.getElementById('sync-fb-historical-btn').style.display = 'none';
                document.getElementById('fb-form-id-input').innerHTML = '<option value="">{{ __("Select Form") }}</option>';
                document.getElementById('fb-mapping-container').classList.add('d-none');

                // Reset tabs to config tab
                const triggerEl = document.querySelector('#fbModalTabs button[data-bs-target="#fb-config-panel"]');
                if (triggerEl) {
                    bootstrap.Tab.getInstance(triggerEl)?.show() || new bootstrap.Tab(triggerEl).show();
                }

                if (pipelineId) {
                    document.getElementById('fb-pipeline-id-input').value = pipelineId;
                    populateFbStages(pipelineId, stageId);
                } else {
                    document.getElementById('fb-stage-id-input').innerHTML = '<option value="">{{ __("Select Stage") }}</option>';
                }

                fbModalObj.show();
            }

            function openFbModalWithRule(rule) {
                document.getElementById('fb-rule-id-input').value = rule.id || '';
                document.getElementById('fb-page-name-input').value = rule.page_name || '';
                document.getElementById('fb-page-id-input').value = rule.page_id || '';
                document.getElementById('fb-access-token-input').value = rule.page_access_token || '';
                document.getElementById('fb-source-id-input').value = rule.source_id || '';
                document.getElementById('fb-pipeline-id-input').value = rule.pipeline_id || '';
                document.getElementById('fb-user-id-input').value = rule.user_id || '';
                document.getElementById('fb-auto-fetch-input').checked = !!rule.auto_fetch;

                populateFbStages(rule.pipeline_id, rule.stage_id);

                document.getElementById('delete-fb-rule-btn').style.display = 'inline-flex';
                document.getElementById('sync-fb-historical-btn').style.display = 'inline-flex';

                // Fetch forms and select form ID
                fetchFbForms(rule.page_id, rule.page_access_token, rule.form_id, rule.field_mapping);

                // Reset tabs to config tab
                const triggerEl = document.querySelector('#fbModalTabs button[data-bs-target="#fb-config-panel"]');
                if (triggerEl) {
                    bootstrap.Tab.getInstance(triggerEl)?.show() || new bootstrap.Tab(triggerEl).show();
                }

                fbModalObj.show();
            }

            // Bind click handlers to Facebook Rule nodes
            document.querySelectorAll('.fb-rule-node').forEach(node => {
                node.addEventListener('click', function(e) {
                    if (e.target.closest('.port')) return;
                    const ruleId = this.getAttribute('data-rule-id');
                    const rule = fbRules.find(r => r.id === ruleId);
                    if (rule) {
                        openFbModalWithRule(rule);
                    }
                });
            });

            // Prevent port clicks from triggering parent AJAX popups
            document.querySelectorAll('.port').forEach(port => {
                port.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });

            document.getElementById('add-facebook-rule-node').addEventListener('click', function() {
                openFbModalForNew();
            });

            // Sync Historical Leads Button Handler
            document.getElementById('sync-fb-historical-btn').addEventListener('click', function() {
                const ruleId = document.getElementById('fb-rule-id-input').value;
                if (!ruleId) return;

                const btn = this;
                const icon = document.getElementById('sync-historical-icon');
                const text = document.getElementById('sync-historical-text');
                
                const origIconClass = icon.className;
                const origText = text.innerText;

                icon.className = 'ti ti-loader animate-spin';
                text.innerText = 'Syncing...';
                btn.disabled = true;

                fetch(`{{ url("facebook-lead-data") }}/${ruleId}/sync`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    icon.className = origIconClass;
                    text.innerText = origText;
                    btn.disabled = false;

                    if (data.success) {
                        toastrs('Success', data.message, 'success');
                    } else {
                        toastrs('Error', data.message || 'Historical sync failed', 'error');
                    }
                })
                .catch(err => {
                    icon.className = origIconClass;
                    text.innerText = origText;
                    btn.disabled = false;
                    toastrs('Error', 'Historical sync server connection error', 'error');
                });
            });

            // Save rule AJAX
            document.getElementById('save-fb-rule-btn').addEventListener('click', function() {
                const form = document.getElementById('facebook-config-form');
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                const btn = this;
                const origText = btn.innerHTML;
                btn.innerHTML = '<i class="ti ti-loader animate-spin"></i> Saving...';
                btn.disabled = true;

                const formData = new FormData(form);
                const payload = {};
                formData.forEach((val, key) => { payload[key] = val; });
                payload['auto_fetch'] = document.getElementById('fb-auto-fetch-input').checked ? 1 : 0;

                // Construct field mapping
                const fieldMapping = {
                    name: '',
                    email: '',
                    phone: '',
                    subject: '',
                    custom: {}
                };
                document.querySelectorAll('.fb-crm-field-select').forEach(select => {
                    const fbKey = select.getAttribute('data-fb-key');
                    const val = select.value;
                    if (val === 'name') fieldMapping.name = fbKey;
                    else if (val === 'email') fieldMapping.email = fbKey;
                    else if (val === 'phone') fieldMapping.phone = fbKey;
                    else if (val === 'subject') fieldMapping.subject = fbKey;
                    else if (val.startsWith('custom_')) {
                        const cfId = val.split('_')[1];
                        fieldMapping.custom[cfId] = fbKey;
                    }
                });
                payload['field_mapping'] = fieldMapping;

                fetch('{{ route("crm.automations.facebook.save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastrs('Success', data.message, 'success');
                        fbModalObj.hide();
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        toastrs('Error', data.message || 'Failed to save', 'error');
                        btn.innerHTML = origText;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    toastrs('Error', 'Server connection error', 'error');
                    btn.innerHTML = origText;
                    btn.disabled = false;
                });
            });

            // Delete rule AJAX
            document.getElementById('delete-fb-rule-btn').addEventListener('click', function() {
                if (!confirm('{{ __("Are you sure you want to delete this Facebook Lead integration feed?") }}')) {
                    return;
                }

                const ruleId = document.getElementById('fb-rule-id-input').value;
                if (!ruleId) return;

                const btn = this;
                const origText = btn.innerHTML;
                btn.innerHTML = '<i class="ti ti-loader animate-spin"></i> Deleting...';
                btn.disabled = true;

                fetch('{{ route("crm.automations.facebook.delete") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ rule_id: ruleId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastrs('Success', data.message, 'success');
                        fbModalObj.hide();
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        toastrs('Error', data.message || 'Failed to delete', 'error');
                        btn.innerHTML = origText;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    toastrs('Error', 'Server connection error', 'error');
                    btn.innerHTML = origText;
                    btn.disabled = false;
                });
            });

            // Test Connection AJAX
            document.getElementById('test-fb-connection-btn').addEventListener('click', function() {
                const pageId = document.getElementById('fb-page-id-input').value;
                const pageAccessToken = document.getElementById('fb-access-token-input').value;

                if (!pageId || !pageAccessToken) {
                    toastrs('Error', '{{ __("Please fill Page ID and Access Token to test connection.") }}', 'error');
                    return;
                }

                const btn = this;
                const icon = document.getElementById('test-conn-icon');
                const text = document.getElementById('test-conn-text');
                
                const origIconClass = icon.className;
                const origText = text.innerText;

                icon.className = 'ti ti-loader animate-spin';
                text.innerText = 'Testing...';
                btn.disabled = true;

                fetch('{{ route("crm.automations.facebook.test") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ page_id: pageId, page_access_token: pageAccessToken })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastrs('Success', data.message + ' Page Name: ' + data.page_name, 'success');
                        // Pre-populate friendly name if empty
                        const friendlyNameInput = document.getElementById('fb-page-name-input');
                        if (!friendlyNameInput.value) {
                            friendlyNameInput.value = data.page_name;
                        }
                    } else {
                        toastrs('Error', data.message || 'Connection test failed', 'error');
                    }
                    icon.className = origIconClass;
                    text.innerText = origText;
                    btn.disabled = false;
                })
                .catch(err => {
                    console.error('Error:', err);
                    toastrs('Error', 'Failed to connect to Meta Graph API', 'error');
                    icon.className = origIconClass;
                    text.innerText = origText;
                    btn.disabled = false;
                });
            });

            // Restore Facebook Initial Connections
            fbRules.forEach(rule => {
                const fromPort = document.querySelector(`#fb-rule-${rule.id} .output-port`);
                const toPort = document.querySelector(`#stage-${rule.stage_id} .input-port`);

                if (fromPort && toPort) {
                    const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    path.setAttribute('class', 'connection-line');
                    path.setAttribute('marker-end', 'url(#arrow)');
                    path.setAttribute('data-from-fb-rule', rule.id);
                    path.setAttribute('data-to-stage', rule.stage_id);
                    path.style.pointerEvents = 'auto';
                    path.style.stroke = '#1877f2'; // Meta blue

                    svgCanvas.appendChild(path);

                    // Create label element
                    const label = document.createElement('div');
                    label.className = 'connection-label fb-label';
                    label.innerText = 'Meta Feed';
                    label.style.borderColor = 'rgba(24, 119, 242, 0.3)';
                    label.style.color = '#1877f2';
                    label.style.background = '#e8f4fd';
                    canvas.appendChild(label);

                    const connObj = {
                        isFb: true,
                        ruleId: rule.id,
                        fromPipelineId: 'facebook',
                        fromStageId: null,
                        toPipelineId: rule.pipeline_id,
                        toStageId: rule.stage_id,
                        fromPort: fromPort,
                        toPort: toPort,
                        pathElement: path,
                        labelElement: label
                    };

                    const editHandler = function() {
                        openFbModalWithRule(rule);
                    };

                    path.addEventListener('click', editHandler);
                    label.addEventListener('click', editHandler);

                    connections.push(connObj);
                }
            });

            // Restore Webhook Endpoints Connections
            const webhookRules = @json($webhookEndpoints);
            webhookRules.forEach(endpoint => {
                if (endpoint.stage_id) {
                    const fromPort = document.querySelector(`#wh-rule-${endpoint.id} .output-port`);
                    const toPort = document.querySelector(`#stage-${endpoint.stage_id} .input-port`);

                    if (fromPort && toPort) {
                        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                        path.setAttribute('class', 'connection-line');
                        path.setAttribute('marker-end', 'url(#arrow)');
                        path.setAttribute('data-from-wh-rule', endpoint.id);
                        path.setAttribute('data-to-stage', endpoint.stage_id);
                        path.style.pointerEvents = 'auto';
                        path.style.stroke = '#ff9f43'; // Webhook orange

                        svgCanvas.appendChild(path);

                        // Create label element
                        const label = document.createElement('div');
                        label.className = 'connection-label wh-label';
                        label.innerText = endpoint.auto_convert == 0 ? '{{ __("Logs Only") }}' : '{{ __("Direct Lead") }}';
                        canvas.appendChild(label);

                        const connObj = {
                            isWh: true,
                            ruleId: endpoint.id,
                            fromPipelineId: 'webhook',
                            fromStageId: null,
                            toPipelineId: endpoint.pipeline_id,
                            toStageId: endpoint.stage_id,
                            fromPort: fromPort,
                            toPort: toPort,
                            pathElement: path,
                            labelElement: label
                        };

                        const editHandler = function() {
                            const el = document.querySelector(`#wh-rule-${endpoint.id}`);
                            if (el) {
                                el.click();
                            }
                        };

                        path.addEventListener('click', editHandler);
                        label.addEventListener('click', editHandler);

                        connections.push(connObj);
                    }
                }
            });

            // ==========================================
            // Orion / FinKORP API Integration Javascript
            // ==========================================
            const orionSettings = @json($orionSettings);
            const orionRules = orionSettings.rules || [];
            const orionCredentials = orionSettings.credentials || {};
            const orionModalEl = document.getElementById('orionConfigModal');
            const orionModalObj = new bootstrap.Modal(orionModalEl);

            function populateOrionStages(pipelineId, selectedStageId = null) {
                const pipeline = pipelinesData.find(p => p.id == pipelineId);
                const stages = pipeline ? pipeline.lead_stages : [];
                const stageSelect = document.getElementById('orion-stage-id-input');
                
                stageSelect.innerHTML = '<option value="">{{ __("Select Stage") }}</option>';
                stages.forEach(stage => {
                    const opt = document.createElement('option');
                    opt.value = stage.id;
                    opt.textContent = stage.name;
                    if (stage.id == selectedStageId) {
                        opt.selected = true;
                    }
                    stageSelect.appendChild(opt);
                });
            }

            window.updateOrionFieldsForPipeline = function(pipelineId) {
                document.querySelectorAll('.orion-crm-field-select').forEach(select => {
                    const currentVal = select.value;
                    const searchEnabled = select.getAttribute('searchEnabled') === 'true';

                    let html = `<option value="">{{ __("Don't map this field") }}</option>`;
                    
                    html += `<optgroup label="{{ __('Standard Fields') }}">`;
                    const standardFields = {
                        'name': '{{ __("Name") }}',
                        'email': '{{ __("Email") }}',
                        'phone': '{{ __("Phone / Mobile") }}',
                        'subject': '{{ __("Subject") }}',
                        'pan_number': '{{ __("PAN Number") }}',
                        'aadhar_number': '{{ __("Aadhar Number") }}',
                        'dp_id': '{{ __("DP ID / Client Code") }}'
                    };
                    for (const [sKey, sLabel] of Object.entries(standardFields)) {
                        html += `<option value="${sKey}" ${currentVal === sKey ? 'selected' : ''}>${sLabel}</option>`;
                    }
                    html += `</optgroup>`;

                    const filteredCustom = crmCustomFields.filter(cf => !cf.pipeline_id || cf.pipeline_id == pipelineId);
                    if (filteredCustom.length > 0) {
                        html += `<optgroup label="{{ __('Custom Fields') }}">`;
                        filteredCustom.forEach(cf => {
                            const optVal = `custom_${cf.id}`;
                            html += `<option value="${optVal}" ${currentVal === optVal ? 'selected' : ''}>${cf.name}</option>`;
                        });
                        html += `</optgroup>`;
                    }

                    if (select.choicesInstance) {
                        select.choicesInstance.destroy();
                    }

                    select.innerHTML = html;

                    select.choicesInstance = new Choices(select, {
                        removeItemButton: true,
                        loadingText: 'Loading...',
                        searchEnabled: searchEnabled,
                        placeholder: true,
                        placeholderValue: "Please Select"
                    });
                });

                updateOrionMappingRowStates();
            };

            document.getElementById('orion-pipeline-id-input').addEventListener('change', function() {
                populateOrionStages(this.value);
                updateOrionFieldsForPipeline(this.value);
            });

            window.toggleOrionPasswordVisibility = function() {
                const tokenInput = document.getElementById('orion-password-input');
                const eyeIcon = document.getElementById('toggle-orion-password-eye');
                if (tokenInput.type === 'password') {
                    tokenInput.type = 'text';
                    eyeIcon.className = 'ti ti-eye-off text-muted fs-5 px-3';
                } else {
                    tokenInput.type = 'password';
                    eyeIcon.className = 'ti ti-eye text-muted fs-5 px-3';
                }
            };

            function updateOrionMappingRowStates() {
                document.querySelectorAll('.orion-crm-field-select').forEach(select => {
                    const row = select.closest('.orion-mapping-row');
                    if (!row) return;
                    
                    const pill = row.querySelector('.orion-mapped-status-pill');
                    const iconWrapper = row.querySelector('.orion-mapping-icon-wrapper i');
                    
                    if (select.value) {
                        row.classList.remove('unmapped');
                        row.classList.add('mapped');
                        if (pill) {
                            pill.className = 'badge orion-mapped-status-pill mapped-pill';
                            pill.innerHTML = '<i class="ti ti-circle-check me-1"></i>Mapped';
                        }
                        if (iconWrapper) {
                            iconWrapper.className = 'ti ti-link fs-5';
                        }
                    } else {
                        row.classList.remove('mapped');
                        row.classList.add('unmapped');
                        if (pill) {
                            pill.className = 'badge orion-mapped-status-pill unmapped-pill';
                            pill.innerHTML = '<i class="ti ti-circle-x me-1"></i>Unmapped';
                        }
                        if (iconWrapper) {
                            iconWrapper.className = 'ti ti-plug fs-5';
                        }
                    }
                });

                if (typeof updateOrionAccordionBadges === 'function') {
                    updateOrionAccordionBadges();
                }
            }

            document.querySelectorAll('.orion-crm-field-select').forEach(select => {
                select.addEventListener('change', updateOrionMappingRowStates);
                select.addEventListener('choice', updateOrionMappingRowStates);
                select.addEventListener('addItem', updateOrionMappingRowStates);
                select.addEventListener('removeItem', updateOrionMappingRowStates);
            });

            function getBestMatch(key, options) {
                const directSynonyms = {
                    'clientname': ['name'],
                    'contactno': ['phone'],
                    'contactemail': ['email'],
                    'panno': ['pan_number', 'pan'],
                    'aadhar': ['aadhar_number', 'aadhar'],
                    'clientcode': ['dp_id', 'client_code'],
                    'depositoryclientid': ['dp_id', 'client_code'],
                    'depositorytype': ['depository_type'],
                    'nomineename': ['nominee_name'],
                    'nomineerelation': ['nominee_relation'],
                    'nomineedob': ['nominee_dob'],
                    'nomineepan': ['nominee_pan'],
                    'nomineeshare': ['nominee_share'],
                    'bankaccountnumber': ['bank_account_number', 'account_number', 'account_no'],
                    'bankifsc': ['bank_ifsc', 'ifsc'],
                    'bankname': ['bank_name', 'bank'],
                    'maritalstatus': ['marital_status', 'marital'],
                    'fathername': ['father_name'],
                    'mothername': ['mother_name']
                };
                
                const normalize = str => str.toLowerCase().replace(/[^a-z0-9]/g, '');
                const normalizedKey = normalize(key);
                
                let bestVal = '';
                let highestScore = 0;
                
                for (let i = 0; i < options.length; i++) {
                    const option = options[i];
                    if (!option.value) continue;
                    
                    const optVal = option.value.toLowerCase();
                    const optText = option.text.toLowerCase();
                    const normOptVal = normalize(option.value);
                    const normOptText = normalize(option.text);
                    
                    // 1. Check direct synonyms mapping
                    if (directSynonyms[normalizedKey] && directSynonyms[normalizedKey].includes(optVal)) {
                        return option.value;
                    }
                    
                    // 2. Exact match of normalized key with normalized option value or text
                    if (normalizedKey === normOptVal || normalizedKey === normOptText) {
                        return option.value;
                    }
                    
                    // 3. Fuzzy matching based on inclusion and scoring
                    let score = 0;
                    
                    if (normOptText.includes(normalizedKey) || normalizedKey.includes(normOptText)) {
                        score += 10;
                    }
                    
                    // Specific partial matches
                    if (normalizedKey === 'clientname' && normOptText.includes('name')) score += 5;
                    if (normalizedKey === 'contactno' && (normOptText.includes('phone') || normOptText.includes('mobile'))) score += 5;
                    if (normalizedKey === 'contactemail' && normOptText.includes('email')) score += 5;
                    if (normalizedKey === 'panno' && normOptText.includes('pan')) score += 5;
                    if (normalizedKey === 'aadhar' && normOptText.includes('aadhar')) score += 5;
                    if (normalizedKey === 'dob' && (normOptText.includes('dob') || normOptText.includes('birth'))) score += 5;
                    if (normalizedKey === 'fathername' && normOptText.includes('father')) score += 5;
                    if (normalizedKey === 'mothername' && normOptText.includes('mother')) score += 5;
                    if (normalizedKey === 'maritalstatus' && (normOptText.includes('marital') || normOptText.includes('marriage'))) score += 5;
                    
                    if (normalizedKey === 'addressline1' && normOptText.includes('address') && (normOptText.includes('1') || normOptText.includes('line1'))) score += 5;
                    if (normalizedKey === 'addressline2' && normOptText.includes('address') && (normOptText.includes('2') || normOptText.includes('line2'))) score += 5;
                    if (normalizedKey === 'addressline3' && normOptText.includes('address') && (normOptText.includes('3') || normOptText.includes('line3'))) score += 5;
                    if (normalizedKey === 'addresscity' && normOptText.includes('city')) score += 5;
                    if (normalizedKey === 'addresspincode' && (normOptText.includes('pincode') || normOptText.includes('pin'))) score += 5;
                    if (normalizedKey === 'addressstate' && normOptText.includes('state')) score += 5;
                    
                    if (normalizedKey === 'bankaccountnumber' && normOptText.includes('account') && (normOptText.includes('bank') || normOptText.includes('number'))) score += 5;
                    if (normalizedKey === 'bankifsc' && normOptText.includes('ifsc')) score += 5;
                    if (normalizedKey === 'bankname' && normOptText.includes('bank') && !normOptText.includes('account')) score += 5;
                    
                    if (normalizedKey === 'nomineename' && normOptText.includes('nominee') && normOptText.includes('name')) score += 5;
                    if (normalizedKey === 'nomineerelation' && normOptText.includes('nominee') && (normOptText.includes('relation') || normOptText.includes('relationship'))) score += 5;
                    if (normalizedKey === 'nomineedob' && normOptText.includes('nominee') && (normOptText.includes('dob') || normOptText.includes('birth'))) score += 5;
                    if (normalizedKey === 'nomineepan' && normOptText.includes('nominee') && normOptText.includes('pan')) score += 5;
                    
                    if (normalizedKey === 'opendate' && (normOptText.includes('open') || normOptText.includes('opening'))) score += 5;
                    if (normalizedKey === 'annualincome' && (normOptText.includes('annual') || normOptText.includes('income')) && !normOptText.includes('date')) score += 5;
                    if (normalizedKey === 'annualincomedate' && normOptText.includes('income') && normOptText.includes('date')) score += 5;
                    if (normalizedKey === 'networth' && normOptText.includes('net') && normOptText.includes('worth') && !normOptText.includes('date')) score += 5;
                    if (normalizedKey === 'networthdate' && normOptText.includes('worth') && normOptText.includes('date')) score += 5;
                    if (normalizedKey === 'riskcategory' && normOptText.includes('risk')) score += 5;
                    if (normalizedKey === 'addresscountry' && (normOptText.includes('country') || normOptText.includes('nation'))) score += 5;
                    if (normalizedKey === 'bankaccounttype' && normOptText.includes('bank') && normOptText.includes('type')) score += 5;
                    if (normalizedKey === 'ecslimit' && (normOptText.includes('ecs') || normOptText.includes('mandate')) && normOptText.includes('limit')) score += 5;
                    if (normalizedKey === 'umrn' && normOptText.includes('umrn')) score += 5;
                    
                    if (score > highestScore) {
                        highestScore = score;
                        bestVal = option.value;
                    }
                }
                
                return highestScore >= 5 ? bestVal : '';
            }

            window.autoMapOrionFields = function() {
                let matchCount = 0;
                document.querySelectorAll('.orion-crm-field-select').forEach(select => {
                    const key = select.getAttribute('data-orion-key');
                    const bestVal = getBestMatch(key, select.options);
                    if (bestVal) {
                        if (select.choicesInstance) {
                            select.choicesInstance.setChoiceByValue(bestVal);
                        } else {
                            select.value = bestVal;
                        }
                        matchCount++;
                    }
                });
                
                updateOrionMappingRowStates();
                toastrs('Success', `Auto-mapped ${matchCount} fields successfully!`, 'success');
            };

            window.clearAllOrionMappings = function() {
                document.querySelectorAll('.orion-crm-field-select').forEach(select => {
                    if (select.choicesInstance) {
                        select.choicesInstance.setChoiceByValue('');
                    } else {
                        select.value = '';
                    }
                });
                
                updateOrionMappingRowStates();
                toastrs('Success', 'Cleared all mapped fields.', 'success');
            };

            window.updateOrionAccordionBadges = function() {
                const sections = [
                    { id: 'collapseOne', total: 17, badgeId: 'orion-badge-collapseOne' },
                    { id: 'collapseTwo', total: 9, badgeId: 'orion-badge-collapseTwo' },
                    { id: 'collapseThree', total: 16, badgeId: 'orion-badge-collapseThree' }
                ];
                
                let mappedCountAll = 0;
                sections.forEach(sec => {
                    const collapseEl = document.getElementById(sec.id);
                    if (!collapseEl) return;
                    
                    const mappedCount = Array.from(collapseEl.querySelectorAll('.orion-crm-field-select')).filter(select => !!select.value).length;
                    mappedCountAll += mappedCount;
                    
                    const badgeEl = document.getElementById(sec.badgeId);
                    if (badgeEl) {
                        badgeEl.innerText = `${mappedCount} / ${sec.total} Mapped`;
                        
                        if (mappedCount === 0) {
                            badgeEl.style.backgroundColor = '#f1f5f9';
                            badgeEl.style.color = '#64748b';
                            badgeEl.className = 'badge orion-accordion-badge ms-auto me-3';
                        } else if (mappedCount === sec.total) {
                            badgeEl.style.backgroundColor = 'rgba(16, 185, 129, 0.1)';
                            badgeEl.style.color = '#10b981';
                            badgeEl.className = 'badge orion-accordion-badge ms-auto me-3 fw-bold';
                        } else {
                            badgeEl.style.backgroundColor = 'rgba(139, 92, 246, 0.1)';
                            badgeEl.style.color = '#8b5cf6';
                            badgeEl.className = 'badge orion-accordion-badge ms-auto me-3 fw-semibold';
                        }
                    }
                });
                
                const totalFields = 42;
                const progressEl = document.getElementById('orion-mapping-progress');
                const countTextEl = document.getElementById('orion-mapped-count-text');
                const percentTextEl = document.getElementById('orion-mapping-percent-text');
                
                if (progressEl) {
                    const percent = Math.round((mappedCountAll / totalFields) * 100);
                    progressEl.style.width = `${percent}%`;
                    if (percent === 100) {
                        progressEl.style.backgroundColor = '#10b981';
                    } else {
                        progressEl.style.backgroundColor = '#8b5cf6';
                    }
                }
                if (countTextEl) countTextEl.innerText = mappedCountAll;
                if (percentTextEl) percentTextEl.innerText = `${Math.round((mappedCountAll / totalFields) * 100)}% Completed`;
            };

            window.updateOrionTriggerModeDescription = function() {
                const input = document.getElementById('orion-trigger-mode-input');
                if (!input) return;
                
                const val = input.value;
                const titleEl = document.getElementById('orion-trigger-mode-title');
                const bodyEl = document.getElementById('orion-trigger-mode-body');
                
                if (val === 'manual_fetch') {
                    titleEl.innerText = '{{ __("Manual Fetch (Show Button)") }}';
                    bodyEl.innerText = '{{ __("Adds a \"Fetch Orion EKYC\" action button to the Lead detail page. Team members can run manual queries at any time to import client data.") }}';
                } else if (val === 'auto_fetch') {
                    titleEl.innerText = '{{ __("Auto Fetch on Stage Entry") }}';
                    bodyEl.innerText = '{{ __("Automatically queries Orion API using the Lead details as soon as the lead enters this stage, saving manual work.") }}';
                } else if (val === 'auto_send_ekyc') {
                    titleEl.innerText = '{{ __("Auto Send EKYC to Orion") }}';
                    bodyEl.innerText = '{{ __("Automatically pushes lead data as a new EKYC client request to Orion when the lead enters this stage.") }}';
                } else if (val === 'auto_send_modify') {
                    titleEl.innerText = '{{ __("Auto Send Modification to Orion") }}';
                    bodyEl.innerText = '{{ __("Automatically pushes updated lead data as a modification request to Orion when the lead enters this stage.") }}';
                }
            };

            document.getElementById('orion-trigger-mode-input').addEventListener('change', updateOrionTriggerModeDescription);

            // Live Search Parameter Mapping
            const mappingSearchInput = document.getElementById('orion-mapping-search');
            if (mappingSearchInput) {
                mappingSearchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase().trim();
                    const rows = document.querySelectorAll('.orion-mapping-row');
                    const accordions = document.querySelectorAll('.orion-mapping-accordion .accordion-item');

                    rows.forEach(row => {
                        const key = (row.getAttribute('data-param-key') || '').toLowerCase();
                        const label = (row.getAttribute('data-param-label') || '').toLowerCase();
                        if (key.includes(query) || label.includes(query)) {
                            row.style.setProperty('display', 'flex', 'important');
                        } else {
                            row.style.setProperty('display', 'none', 'important');
                        }
                    });

                    // For each accordion card, check if it has any visible rows
                    accordions.forEach(item => {
                        const hasVisibleRows = Array.from(item.querySelectorAll('.orion-mapping-row')).some(row => row.style.display !== 'none');
                        const collapseEl = item.querySelector('.accordion-collapse');
                        const btnEl = item.querySelector('.accordion-button');
                        const bsCollapse = bootstrap.Collapse.getInstance(collapseEl) || new bootstrap.Collapse(collapseEl, { toggle: false });

                        if (query.length > 0) {
                            if (hasVisibleRows) {
                                item.style.setProperty('display', 'block', 'important');
                                // Auto expand matching accordions
                                if (!collapseEl.classList.contains('show')) {
                                    bsCollapse.show();
                                    btnEl.classList.remove('collapsed');
                                }
                            } else {
                                item.style.setProperty('display', 'none', 'important');
                            }
                        } else {
                            // Reset state
                            item.style.setProperty('display', 'block', 'important');
                            bsCollapse.hide();
                            btnEl.classList.add('collapsed');
                        }
                    });
                });
            }

            function openOrionModalForNew(pipelineId = '', stageId = '') {
                document.getElementById('orion-rule-id-input').value = '';
                document.getElementById('orion-trigger-mode-input').value = 'manual_fetch';
                document.getElementById('orion-pipeline-id-input').value = pipelineId;
                populateOrionStages(pipelineId, stageId);
                
                updateOrionFieldsForPipeline(pipelineId);

                document.querySelectorAll('.orion-crm-field-select').forEach(select => {
                    if (select.choicesInstance) {
                        select.choicesInstance.setChoiceByValue('');
                    } else {
                        select.value = '';
                    }
                });

                document.getElementById('delete-orion-rule-btn').style.display = 'none';

                // Reset search query
                if (mappingSearchInput) {
                    mappingSearchInput.value = '';
                    mappingSearchInput.dispatchEvent(new Event('input'));
                }

                // Update states
                updateOrionMappingRowStates();
                updateOrionTriggerModeDescription();

                const tabTrigger = new bootstrap.Tab(document.getElementById('orion-credentials-tab'));
                tabTrigger.show();

                orionModalObj.show();
            }

            function openOrionModalWithRule(rule) {
                document.getElementById('orion-rule-id-input').value = rule.id;
                document.getElementById('orion-trigger-mode-input').value = rule.trigger_mode || 'manual_fetch';
                document.getElementById('orion-pipeline-id-input').value = rule.pipeline_id;
                populateOrionStages(rule.pipeline_id, rule.stage_id);

                updateOrionFieldsForPipeline(rule.pipeline_id);

                const mapping = rule.field_mapping || {};
                document.querySelectorAll('.orion-crm-field-select').forEach(select => {
                    const key = select.getAttribute('data-orion-key');
                    const val = mapping[key] || '';
                    if (select.choicesInstance) {
                        select.choicesInstance.setChoiceByValue(val);
                    } else {
                        select.value = val;
                    }
                });

                document.getElementById('delete-orion-rule-btn').style.display = 'inline-flex';

                // Reset search query
                if (mappingSearchInput) {
                    mappingSearchInput.value = '';
                    mappingSearchInput.dispatchEvent(new Event('input'));
                }

                // Update states
                updateOrionMappingRowStates();
                updateOrionTriggerModeDescription();

                const tabTrigger = new bootstrap.Tab(document.getElementById('orion-rules-tab'));
                tabTrigger.show();

                orionModalObj.show();
            }

            document.getElementById('add-orion-rule-node').addEventListener('click', function() {
                openOrionModalForNew();
            });

            document.querySelectorAll('.orion-rule-node').forEach(node => {
                node.addEventListener('click', function(e) {
                    if (e.target.closest('.port')) return;
                    const ruleId = this.getAttribute('data-rule-id');
                    const rule = orionRules.find(r => r.id === ruleId);
                    if (rule) {
                        openOrionModalWithRule(rule);
                    }
                });
            });

            // Save settings AJAX
            document.getElementById('save-orion-rule-btn').addEventListener('click', function() {
                const btn = this;
                const origText = btn.innerHTML;
                btn.innerHTML = '<i class="ti ti-loader animate-spin"></i> Saving...';
                btn.disabled = true;

                const credentials = {
                    base_url: document.getElementById('orion-base-url-input').value,
                    username: document.getElementById('orion-username-input').value,
                    password: document.getElementById('orion-password-input').value,
                    firm_id: document.getElementById('orion-firm-id-input').value,
                    financial_year: document.getElementById('orion-financial-year-input').value
                };

                const ruleId = document.getElementById('orion-rule-id-input').value;
                const pipelineId = document.getElementById('orion-pipeline-id-input').value;
                const stageId = document.getElementById('orion-stage-id-input').value;
                const triggerMode = document.getElementById('orion-trigger-mode-input').value;

                const fieldMapping = {};
                document.querySelectorAll('.orion-crm-field-select').forEach(select => {
                    const key = select.getAttribute('data-orion-key');
                    if (select.value) {
                        fieldMapping[key] = select.value;
                    }
                });

                const rules = [...orionRules];
                
                if (pipelineId && stageId) {
                    if (ruleId) {
                        const idx = rules.findIndex(r => r.id === ruleId);
                        if (idx > -1) {
                            rules[idx] = {
                                id: ruleId,
                                pipeline_id: pipelineId,
                                stage_id: stageId,
                                trigger_mode: triggerMode,
                                field_mapping: fieldMapping
                            };
                        }
                    } else {
                        rules.push({
                            id: 'orion_rule_' + Date.now(),
                            pipeline_id: pipelineId,
                            stage_id: stageId,
                            trigger_mode: triggerMode,
                            field_mapping: fieldMapping
                        });
                    }
                }

                const positions = {};
                document.querySelectorAll('.pipeline-card').forEach(card => {
                    const id = card.getAttribute('data-pipeline-id');
                    positions['pipeline_' + id] = {
                        x: parseInt(card.style.left),
                        y: parseInt(card.style.top)
                    };
                });

                fetch('{{ route("crm.automations.orion.save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ credentials, rules, positions })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastrs('Success', data.message, 'success');
                        orionModalObj.hide();
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        toastrs('Error', data.message || 'Failed to save configuration', 'error');
                        btn.innerHTML = origText;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    toastrs('Error', 'Server connection error', 'error');
                    btn.innerHTML = origText;
                    btn.disabled = false;
                });
            });

            // Test connection AJAX
            document.getElementById('test-orion-connection-btn').addEventListener('click', function() {
                const baseUrl = document.getElementById('orion-base-url-input').value;
                const username = document.getElementById('orion-username-input').value;
                const password = document.getElementById('orion-password-input').value;

                if (!baseUrl || !username || !password) {
                    toastrs('Error', '{{ __("Please enter Base URL, Username, and Password to test connection.") }}', 'error');
                    return;
                }

                const btn = this;
                const icon = document.getElementById('test-orion-conn-icon');
                const text = document.getElementById('test-orion-conn-text');
                const origIcon = icon.className;
                const origText = text.innerText;

                icon.className = 'ti ti-loader animate-spin';
                text.innerText = 'Testing...';
                btn.disabled = true;

                fetch('{{ route("crm.automations.orion.test") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ base_url: baseUrl, username: username, password: password })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastrs('Success', data.message, 'success');
                    } else {
                        toastrs('Error', data.message || 'Connection test failed', 'error');
                    }
                    icon.className = origIcon;
                    text.innerText = origText;
                    btn.disabled = false;
                })
                .catch(err => {
                    console.error('Error:', err);
                    toastrs('Error', 'Failed to connect to Orion Server', 'error');
                    icon.className = origIcon;
                    text.innerText = origText;
                    btn.disabled = false;
                });
            });

            // Delete rule AJAX
            document.getElementById('delete-orion-rule-btn').addEventListener('click', function() {
                if (!confirm('{{ __("Are you sure you want to delete this Orion workflow rule?") }}')) {
                    return;
                }

                const ruleId = document.getElementById('orion-rule-id-input').value;
                if (!ruleId) return;

                const btn = this;
                const origText = btn.innerHTML;
                btn.innerHTML = '<i class="ti ti-loader animate-spin"></i> Deleting...';
                btn.disabled = true;

                fetch('{{ route("crm.automations.orion.delete") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ rule_id: ruleId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastrs('Success', data.message, 'success');
                        orionModalObj.hide();
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        toastrs('Error', data.message || 'Failed to delete rule', 'error');
                        btn.innerHTML = origText;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    toastrs('Error', 'Server connection error', 'error');
                    btn.innerHTML = origText;
                    btn.disabled = false;
                });
            });

            // Restore Orion Initial Connections
            orionRules.forEach(rule => {
                const fromPort = document.querySelector(`#orion-rule-${rule.id} .output-port`);
                const toPort = document.querySelector(`#stage-${rule.stage_id} .input-port`);

                if (fromPort && toPort) {
                    const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    path.setAttribute('class', 'connection-line');
                    path.setAttribute('marker-end', 'url(#arrow)');
                    path.setAttribute('data-from-orion-rule', rule.id);
                    path.setAttribute('data-to-stage', rule.stage_id);
                    path.style.pointerEvents = 'auto';
                    path.style.stroke = '#8b5cf6'; // Violet

                    svgCanvas.appendChild(path);

                    // Create label element
                    const label = document.createElement('div');
                    label.className = 'connection-label orion-label';
                    label.innerText = rule.trigger_mode === 'manual_fetch' ? 'Orion (Btn)' : 'Orion (Auto)';
                    canvas.appendChild(label);

                    const connObj = {
                        isOrion: true,
                        ruleId: rule.id,
                        fromPipelineId: 'orion',
                        fromStageId: null,
                        toPipelineId: rule.pipeline_id,
                        toStageId: rule.stage_id,
                        fromPort: fromPort,
                        toPort: toPort,
                        pathElement: path,
                        labelElement: label
                    };

                    const editHandler = function() {
                        openOrionModalWithRule(rule);
                    };

                    path.addEventListener('click', editHandler);
                    label.addEventListener('click', editHandler);

                    connections.push(connObj);
                }
            });

            // Restore WhatsApp Initial Connections
            const whatsappRules = @json($whatsappConfigs);
            whatsappRules.forEach(config => {
                if (config.stage_id) {
                    const fromPort = document.querySelector(`#whatsapp-config-${config.id} .output-port`);
                    const toPort = document.querySelector(`#stage-${config.stage_id} .input-port`);

                    if (fromPort && toPort) {
                        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                        path.setAttribute('class', 'connection-line');
                        path.setAttribute('marker-end', 'url(#arrow)');
                        path.setAttribute('data-from-whatsapp-config', config.id);
                        path.setAttribute('data-to-stage', config.stage_id);
                        path.style.pointerEvents = 'auto';
                        path.style.stroke = '#25d366'; // WhatsApp Green

                        svgCanvas.appendChild(path);

                        // Create label element
                        const label = document.createElement('div');
                        label.className = 'connection-label wh-label';
                        label.innerText = '{{ __("WhatsApp") }}';
                        label.style.borderColor = 'rgba(37, 211, 102, 0.3)';
                        label.style.color = '#25d366';
                        label.style.background = '#e8fdf0';
                        canvas.appendChild(label);

                        const connObj = {
                            isWhatsApp: true,
                            ruleId: config.id,
                            fromPipelineId: 'whatsapp',
                            fromStageId: null,
                            toPipelineId: config.pipeline_id,
                            toStageId: config.stage_id,
                            fromPort: fromPort,
                            toPort: toPort,
                            pathElement: path,
                            labelElement: label
                        };

                        const editHandler = function() {
                            const el = document.querySelector(`#whatsapp-config-${config.id}`);
                            if (el) {
                                el.click();
                            }
                        };

                        path.addEventListener('click', editHandler);
                        label.addEventListener('click', editHandler);

                        connections.push(connObj);
                    }
                }
            });

            // Handle window resizing
            window.addEventListener('resize', redrawConnections);

            // Add hover highlight to connections when node is hovered
            document.querySelectorAll('.stage-node').forEach(node => {
                node.addEventListener('mouseenter', function() {
                    const stageId = this.getAttribute('data-stage-id');
                    const ruleId = this.getAttribute('data-rule-id');
                    
                    connections.forEach(conn => {
                        if (conn.fromStageId === stageId || conn.toStageId === stageId || conn.ruleId === ruleId) {
                            conn.pathElement.style.strokeWidth = '5';
                            if (conn.labelElement) {
                                conn.labelElement.style.transform = 'translate(-50%, -50%) scale(1.1)';
                                conn.labelElement.style.boxShadow = '0 6px 15px rgba(0,0,0,0.1)';
                            }
                        }
                    });
                });
                
                node.addEventListener('mouseleave', function() {
                    connections.forEach(conn => {
                        conn.pathElement.style.strokeWidth = '';
                        if (conn.labelElement) {
                            conn.labelElement.style.transform = '';
                            conn.labelElement.style.boxShadow = '';
                        }
                    });
                });
            });
        });
    </script>
@endsection
