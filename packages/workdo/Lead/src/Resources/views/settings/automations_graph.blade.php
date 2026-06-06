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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('canvas');
            const svgCanvas = document.getElementById('svg-canvas');
            
            // Loaded rules and positions from settings
            const savedData = @json($workflowData);
            const initialRules = savedData.rules || [];
            const savedPositions = savedData.positions || {};

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
                    const fromStageId = fromStage.getAttribute('data-stage-id');
                    const toPipelineId = targetPort.closest('.pipeline-card').getAttribute('data-pipeline-id');
                    const toStageId = toStage.getAttribute('data-stage-id');

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
                    const fromPort = document.querySelector(`#stage-${conn.fromStageId} .output-port`);
                    const toPort = document.querySelector(`#stage-${conn.toStageId} .input-port`);

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
                const rules = connections.map(conn => ({
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

            // Handle window resizing
            window.addEventListener('resize', redrawConnections);
        });
    </script>
@endsection
