<?php

/* |-------------------------------------------------------------------------- | Web Routes |-------------------------------------------------------------------------- | | Here is where you can register web routes for your application. These | routes are loaded by the RouteServiceProvider within a group which | contains the "web" middleware group. Now create something great! | */

use Illuminate\Support\Facades\Route;
use Workdo\Lead\Http\Controllers\LeadController;
use Workdo\Lead\Http\Controllers\PipelineController;
use Workdo\Lead\Http\Controllers\DealController;
use Workdo\Lead\Http\Controllers\LeadStageController;
use Workdo\Lead\Http\Controllers\DealStageController;
use Workdo\Lead\Http\Controllers\LabelController;
use Workdo\Lead\Http\Controllers\SourceController;
use Workdo\Lead\Http\Controllers\ReportController;
use Workdo\Lead\Http\Controllers\DuplicateController;
use Workdo\Lead\Http\Controllers\LeadTaskController;
use Workdo\Lead\Http\Controllers\Company\SettingsController; // Imported SettingsController



// Public Webhook Test Form


Route::group(['middleware' => ['web', 'auth', 'verified']], function () {
    Route::post('lead/company/settings', [SettingsController::class, 'store'])->name('lead.setting.store');

    // WhatsApp Configuration Routes
    Route::get('whatsapp-config', [\Workdo\Lead\Http\Controllers\WhatsAppConfigController::class, 'index'])->name('whatsapp-config.index');
    Route::get('whatsapp-config/create', [\Workdo\Lead\Http\Controllers\WhatsAppConfigController::class, 'create'])->name('whatsapp-config.create');
    Route::post('whatsapp-config', [\Workdo\Lead\Http\Controllers\WhatsAppConfigController::class, 'store'])->name('whatsapp-config.store');
    Route::get('whatsapp-config/{id}/edit', [\Workdo\Lead\Http\Controllers\WhatsAppConfigController::class, 'edit'])->name('whatsapp-config.edit');
    Route::put('whatsapp-config/{id}', [\Workdo\Lead\Http\Controllers\WhatsAppConfigController::class, 'update'])->name('whatsapp-config.update');
    Route::delete('whatsapp-config/{id}', [\Workdo\Lead\Http\Controllers\WhatsAppConfigController::class, 'destroy'])->name('whatsapp-config.destroy');
    Route::post('whatsapp-config/stages', [\Workdo\Lead\Http\Controllers\WhatsAppConfigController::class, 'getStages'])->name('whatsapp-config.stages');

    // WhatsApp QR Session Routes
    Route::get('whatsapp-config/{id}/qr', [\Workdo\Lead\Http\Controllers\WhatsAppSessionController::class, 'initiateQr'])->name('whatsapp.session.qr');
    Route::get('whatsapp-config/{id}/status', [\Workdo\Lead\Http\Controllers\WhatsAppSessionController::class, 'getStatus'])->name('whatsapp.session.status');
    Route::post('whatsapp-config/{id}/disconnect', [\Workdo\Lead\Http\Controllers\WhatsAppSessionController::class, 'disconnect'])->name('whatsapp.session.disconnect');

    // WhatsApp Teams Routes
    Route::get('whatsapp-teams', [\Workdo\Lead\Http\Controllers\WhatsAppTeamController::class, 'index'])->name('whatsapp-teams.index');
    Route::post('whatsapp-teams', [\Workdo\Lead\Http\Controllers\WhatsAppTeamController::class, 'store'])->name('whatsapp-teams.store');
    Route::get('whatsapp-teams/{id}', [\Workdo\Lead\Http\Controllers\WhatsAppTeamController::class, 'show'])->name('whatsapp-teams.show');
    Route::put('whatsapp-teams/{id}', [\Workdo\Lead\Http\Controllers\WhatsAppTeamController::class, 'update'])->name('whatsapp-teams.update');
    Route::delete('whatsapp-teams/{id}', [\Workdo\Lead\Http\Controllers\WhatsAppTeamController::class, 'destroy'])->name('whatsapp-teams.destroy');
    Route::post('whatsapp-teams/{id}/assign-config', [\Workdo\Lead\Http\Controllers\WhatsAppTeamController::class, 'assignConfig'])->name('whatsapp-teams.assign-config');
    Route::post('whatsapp-teams/{id}/members', [\Workdo\Lead\Http\Controllers\WhatsAppTeamController::class, 'addMember'])->name('whatsapp-teams.members.add');
    Route::delete('whatsapp-teams/{id}/members/{userId}', [\Workdo\Lead\Http\Controllers\WhatsAppTeamController::class, 'removeMember'])->name('whatsapp-teams.members.remove');

    // WhatsApp Chat Routes
    Route::get('whatsapp-chats', [\Workdo\Lead\Http\Controllers\WhatsAppChatController::class, 'index'])->name('whatsapp.chat.index');
    Route::get('whatsapp-chats/{id}/messages', [\Workdo\Lead\Http\Controllers\WhatsAppChatController::class, 'getMessages'])->name('whatsapp.chat.messages');
    Route::post('whatsapp-chats/send', [\Workdo\Lead\Http\Controllers\WhatsAppChatController::class, 'sendMessage'])->name('whatsapp.chat.send');
    Route::post('whatsapp-chats/{id}/update-lead-stage', [\Workdo\Lead\Http\Controllers\WhatsAppChatController::class, 'updateLeadStage'])->name('whatsapp.chat.update-lead-stage');

    // WhatsApp Chat Backup Route
    Route::post('whatsapp-chats/{id}/backup', [\Workdo\Lead\Http\Controllers\WhatsAppSessionController::class, 'backupChat'])->name('whatsapp.chat.backup');

    // Click to Call routes
    Route::post('lead/call/make', [\Workdo\Lead\Http\Controllers\CallController::class, 'makeCall'])->name('lead.call.make');


    Route::post('lead/call/save-extension', [\Workdo\Lead\Http\Controllers\CallController::class, 'saveExtension'])->name('lead.call.save_extension');
    Route::post('lead/call/switch-extension', [\Workdo\Lead\Http\Controllers\CallController::class, 'switchActiveExtension'])->name('lead.call.switch_extension');
    Route::post('lead/call/switch-api', [\Workdo\Lead\Http\Controllers\CallController::class, 'switchActiveApi'])->name('lead.call.switch_api');
    Route::get('lead/call/manager', [\Workdo\Lead\Http\Controllers\CallController::class, 'manager'])->name('lead.call.manager');
    Route::post('lead/call/manager/save', [\Workdo\Lead\Http\Controllers\CallController::class, 'saveManagerSettings'])->name('lead.call.manager.save');

    Route::get('webhook/test/{url}', [\Workdo\Lead\Http\Controllers\WebhookEndpointController::class, 'testForm'])->name('webhook.test.form');
    Route::get('leads/duplicates', [DuplicateController::class, 'index'])->name('leads.duplicates');
    Route::delete('leads/duplicates/{id}', [DuplicateController::class, 'destroy'])->name('leads.duplicates.destroy');

    Route::get('/leads/kanban-batch', [LeadController::class, 'kanbanBatch'])->name('leads.kanban.batch');
    Route::get('/leads/changes-since', [LeadController::class, 'changesSince'])->name('leads.changes.since');
    Route::post('/leads/bulk-action', [LeadController::class, 'bulkAction'])->name('leads.bulk.action');
    Route::get('/leads/bulk-export-download', [LeadController::class, 'bulkExportDownload'])->name('leads.bulk.export.download');

    // Premium Bulk Lead Import Routes
    Route::get('leads/bulk-import', [LeadController::class, 'bulkImportView'])->name('leads.bulk.import');
    Route::get('leads/bulk-import/sample', [LeadController::class, 'bulkImportSample'])->name('leads.bulk.import.sample');
    Route::post('leads/bulk-import/upload', [LeadController::class, 'bulkImportUpload'])->name('leads.bulk.import.upload');
    Route::post('leads/bulk-import/process', [LeadController::class, 'bulkImportProcess'])->name('leads.bulk.import.process');

    Route::get('/leads/kanban-data', [LeadController::class, 'kanbanData'])->name('leads.kanban.data');
    Route::get('/leads/{id}/details-json', [LeadController::class, 'detailsJson'])->name('leads.details.json');
    Route::resource('leads', LeadController::class);
    Route::get('dashboard/crm', [LeadController::class, 'dashboard'])->name('lead.dashboard');

    Route::resource('pipelines', PipelineController::class);

    Route::post('/deals/change-pipeline', [DealController::class, 'changePipeline'])->name('deals.change.pipeline');

    Route::match(['get', 'post'], '/leads-list', [LeadController::class, 'lead_list'])->name('leads.list');
    Route::get('/leads-list-json', [LeadController::class, 'listJson'])->name('leads.list.json');

    Route::resource('lead-stages', LeadStageController::class);
    Route::post('/lead_stages/order', [LeadStageController::class, 'order'])->name('lead_stages.order');

    Route::resource('deal-stages', DealStageController::class);
    Route::post('/deal_stages/order', [DealStageController::class, 'order'])->name('deal-stages.order');

    Route::resource('labels', LabelController::class);
    Route::resource('sources', SourceController::class);

    Route::get('/leads-deals/dashboard', [LeadController::class, 'dashboard'])->name('leads.dashboard');
    Route::post('/leads/order', [LeadController::class, 'order'])->name('leads.order');
    Route::post('/leads/json', [LeadController::class, 'json'])->name('leads.json');
    Route::post('/leads/json-designation', [LeadController::class, 'jsonDesignation'])->name('lead.json.designation');
    Route::post('/leads/json-user', [LeadController::class, 'jsonUser'])->name('lead.json.user');
    Route::post('/leads/{id}/file', [LeadController::class, 'fileUpload'])->name('leads.file.upload');
    Route::get('/leads/{id}/file/{fid}', [LeadController::class, 'fileDownload'])->name('leads.file.download');
    Route::delete('/leads/{id}/file/delete/{fid}', [LeadController::class, 'fileDelete'])->name('leads.file.delete');
    Route::post('/leads/{id}/note', [LeadController::class, 'noteStore'])->name('leads.note.store');
    Route::get('/leads/{id}/labels', [LeadController::class, 'labels'])->name('leads.labels');
    Route::post('/leads/{id}/labels', [LeadController::class, 'labelStore'])->name('leads.labels.store');
    Route::get('/leads/{id}/users', [LeadController::class, 'userEdit'])->name('leads.users.edit');
    Route::put('/leads/{id}/users', [LeadController::class, 'userUpdate'])->name('leads.users.update');
    Route::delete('/leads/{id}/users/{uid}', [LeadController::class, 'userDestroy'])->name('leads.users.destroy');
    Route::get('/leads/{id}/products', [LeadController::class, 'productEdit'])->name('leads.products.edit');
    Route::put('/leads/{id}/products', [LeadController::class, 'productUpdate'])->name('leads.products.update');
    Route::delete('/leads/{id}/products/{uid}', [LeadController::class, 'productDestroy'])->name('leads.products.destroy');
    Route::get('/leads/{id}/sources', [LeadController::class, 'sourceEdit'])->name('leads.sources.edit');
    Route::put('/leads/{id}/sources', [LeadController::class, 'sourceUpdate'])->name('leads.sources.update');
    Route::delete('/leads/{id}/sources/{uid}', [LeadController::class, 'sourceDestroy'])->name('leads.sources.destroy');
    Route::get('/leads/{id}/discussions', [LeadController::class, 'discussionCreate'])->name('leads.discussions.create');
    Route::post('/leads/{id}/discussions', [LeadController::class, 'discussionStore'])->name('leads.discussion.store');
    Route::delete('/leads/{id}/discussions/{did}', [LeadController::class, 'discussionDestroy'])->name('leads.discussion.destroy');
    Route::get('/leads/{id}/show_convert', [LeadController::class, 'showConvertToDeal'])->name('leads.convert.deal');
    Route::post('/leads/{id}/convert', [LeadController::class, 'convertToDeal'])->name('leads.convert.to.deal');
    Route::post('/leads/{id}/inline-update', [LeadController::class, 'inlineUpdate'])->name('leads.inline-update');
    Route::post('/leads/sync-section-api', [LeadController::class, 'syncSectionApi'])->name('leads.sync-section-api');

    Route::get('/leads/{id}/call', [LeadController::class, 'callCreate'])->name('leads.calls.create');
    Route::post('/leads/{id}/call', [LeadController::class, 'callStore'])->name('leads.calls.store');
    Route::get('/leads/{id}/call/{cid}/edit', [LeadController::class, 'callEdit'])->name('leads.calls.edit');
    Route::put('/leads/{id}/call/{cid}', [LeadController::class, 'callUpdate'])->name('leads.calls.update');
    Route::delete('/leads/{id}/call/{cid}', [LeadController::class, 'callDestroy'])->name('leads.calls.destroy');

    // Lead Email
    Route::get('/leads/{id}/email', [LeadController::class, 'emailCreate'])->name('leads.emails.create');
    Route::post('/leads/{id}/email', [LeadController::class, 'emailStore'])->name('leads.emails.store');

    //Lead import
    Route::get('lead/import/export', [LeadController::class, 'fileImportExport'])->name('lead.file.import');
    Route::post('lead/import', [LeadController::class, 'fileImport'])->name('lead.import');
    Route::get('lead/import/modal', [LeadController::class, 'fileImportModal'])->name('lead.import.modal');
    Route::get('lead/import/stages', [LeadController::class, 'getStages'])->name('lead.import.stages');
    Route::post('lead/data/import/', [LeadController::class, 'leadImportdata'])->name('lead.import.data');
    Route::get('lead/import/duplicates/download', [LeadController::class, 'downloadDuplicateLeads'])->name('lead.import.duplicates.download');



    // Lead Reminder
    Route::get('/leads/{id}/reminder', [LeadController::class, 'reminderCreate'])->name('leads.reminders.create');
    Route::post('/leads/{id}/reminder', [LeadController::class, 'reminderStore'])->name('leads.reminders.store');
    Route::get('/leads/{id}/reminder/{rid}/edit', [LeadController::class, 'reminderEdit'])->name('leads.reminders.edit');
    Route::put('/leads/{id}/reminder/{rid}', [LeadController::class, 'reminderUpdate'])->name('leads.reminders.update');
    Route::delete('/leads/{id}/reminder/{rid}', [LeadController::class, 'reminderDestroy'])->name('leads.reminders.destroy');

    // Lead Task
    Route::get('/leads/{id}/task', [LeadController::class, 'taskCreate'])->name('leads.tasks.create');
    Route::post('/leads/{id}/task', [LeadController::class, 'taskStore'])->name('leads.tasks.store');
    Route::get('/leads/{id}/task/{tid}/edit', [LeadController::class, 'taskEdit'])->name('leads.tasks.edit');
    Route::put('/leads/{id}/task/{tid}', [LeadController::class, 'taskUpdate'])->name('leads.tasks.update');
    Route::put('leads/{id}/task/{task_id}/update-status', [LeadController::class, 'taskUpdateStatus'])->name('leads.tasks.update.status');
    Route::post('leads/filter/save', [LeadController::class, 'saveFilter'])->name('leads.filter.save');
    Route::post('leads/search-settings/save', [LeadController::class, 'saveSearchSettings'])->name('leads.search.settings.save');
    Route::post('leads/stats-config/save', [LeadController::class, 'saveStatsConfig'])->name('leads.stats.config.save');
    Route::delete('leads/filter/{id}/delete', [LeadController::class, 'deleteFilter'])->name('leads.filter.delete');
    Route::delete('/leads/{id}/task/{tid}', [LeadController::class, 'taskDestroy'])->name('leads.tasks.destroy');
    Route::get('/leads/duplicates-list', [LeadController::class, 'duplicateList'])->name('leads.duplicates.list');

    // Bulk Task & Reminder
    Route::post('/leads/bulk-task-reminder', [LeadController::class, 'bulkTaskReminderCreate'])->name('leads.bulk.task.reminder.create');
    Route::post('/leads/bulk-task-reminder/store', [LeadController::class, 'bulkTaskReminderStore'])->name('leads.bulk.task.reminder.store');

    // Global Task Management
    Route::get('/lead-tasks', [LeadTaskController::class, 'index'])->name('lead_tasks.index');
    Route::get('/lead-tasks/create', [LeadTaskController::class, 'create'])->name('lead_tasks.create');
    Route::post('/lead-tasks/store', [LeadTaskController::class, 'store'])->name('lead_tasks.store');
    Route::delete('/lead-tasks/{id}', [LeadTaskController::class, 'destroy'])->name('lead_tasks.destroy');
    Route::post('/lead-tasks/bulk-destroy', [LeadTaskController::class, 'bulkDestroy'])->name('lead_tasks.bulk_destroy');

    // Deal Module
    Route::post('/deals/user', [DealController::class, 'jsonUser'])->name('deal.user.json');
    Route::post('/deals/order', [DealController::class, 'order'])->name('deals.order');
    Route::post('/deals/change-pipeline', [DealController::class, 'changePipeline'])->name('deals.change.pipeline');
    Route::post('/deals/change-deal-status/{id}', [DealController::class, 'changeStatus'])->name('deals.change.status');
    Route::get('/deals/{id}/labels', [DealController::class, 'labels'])->name('deals.labels');
    Route::post('/deals/{id}/labels', [DealController::class, 'labelStore'])->name('deals.labels.store');
    Route::get('/deals/{id}/users', [DealController::class, 'userEdit'])->name('deals.users.edit');
    Route::put('/deals/{id}/users', [DealController::class, 'userUpdate'])->name('deals.users.update');
    Route::delete('/deals/{id}/users/{uid}', [DealController::class, 'userDestroy'])->name('deals.users.destroy');
    Route::get('/deals/{id}/clients', [DealController::class, 'clientEdit'])->name('deals.clients.edit');
    Route::put('/deals/{id}/clients', [DealController::class, 'clientUpdate'])->name('deals.clients.update');
    Route::delete('/deals/{id}/clients/{uid}', [DealController::class, 'clientDestroy'])->name('deals.clients.destroy');
    Route::get('/deals/{id}/products', [DealController::class, 'productEdit'])->name('deals.products.edit');
    Route::put('/deals/{id}/products', [DealController::class, 'productUpdate'])->name('deals.products.update');
    Route::delete('/deals/{id}/products/{uid}', [DealController::class, 'productDestroy'])->name('deals.products.destroy');
    Route::get('/deals/{id}/sources', [DealController::class, 'sourceEdit'])->name('deals.sources.edit');
    Route::put('/deals/{id}/sources', [DealController::class, 'sourceUpdate'])->name('deals.sources.update');
    Route::delete('/deals/{id}/sources/{uid}', [DealController::class, 'sourceDestroy'])->name('deals.sources.destroy');
    Route::post('/deals/{id}/file', [DealController::class, 'fileUpload'])->name('deals.file.upload');
    Route::get('/deals/{id}/file/{fid}', [DealController::class, 'fileDownload'])->name('deals.file.download');
    Route::delete('/deals/{id}/file/delete/{fid}', [DealController::class, 'fileDelete'])->name('deals.file.delete');
    Route::post('/deals/{id}/note', [DealController::class, 'noteStore'])->name('deals.note.store');
    Route::get('/deals/{id}/task', [DealController::class, 'taskCreate'])->name('deals.tasks.create');
    Route::post('/deals/{id}/task', [DealController::class, 'taskStore'])->name('deals.tasks.store');
    Route::get('/deals/{id}/task/{tid}/show', [DealController::class, 'taskShow'])->name('deals.tasks.show');
    Route::get('/deals/{id}/task/{tid}/edit', [DealController::class, 'taskEdit'])->name('deals.tasks.edit');
    Route::put('/deals/{id}/task/{tid}', [DealController::class, 'taskUpdate'])->name('deals.tasks.update');
    Route::put('/deals/{id}/task_status/{tid}', [DealController::class, 'taskUpdateStatus'])->name('deals.tasks.update_status');
    Route::delete('/deals/{id}/task/{tid}', [DealController::class, 'taskDestroy'])->name('deals.tasks.destroy');
    Route::get('/deals/{id}/discussions', [DealController::class, 'discussionCreate'])->name('deals.discussions.create');
    Route::post('/deals/{id}/discussions', [DealController::class, 'discussionStore'])->name('deals.discussion.store');
    Route::get('/deals/list', [DealController::class, 'deal_list'])->name('deals.list');

    // Deal Calls
    Route::get('/deals/{id}/call', [DealController::class, 'callCreate'])->name('deals.calls.create');
    Route::post('/deals/{id}/call', [DealController::class, 'callStore'])->name('deals.calls.store');
    Route::get('/deals/{id}/call/{cid}/edit', [DealController::class, 'callEdit'])->name('deals.calls.edit');
    Route::put('/deals/{id}/call/{cid}', [DealController::class, 'callUpdate'])->name('deals.calls.update');
    Route::delete('/deals/{id}/call/{cid}', [DealController::class, 'callDestroy'])->name('deals.calls.destroy');

    // Deal Email
    Route::get('/deals/{id}/email', [DealController::class, 'emailCreate'])->name('deals.emails.create');
    Route::post('/deals/{id}/email', [DealController::class, 'emailStore'])->name('deals.emails.store');

    // Deal Reminder
    Route::get('/deals/{id}/reminder', [DealController::class, 'reminderCreate'])->name('deals.reminders.create');
    Route::post('/deals/{id}/reminder', [DealController::class, 'reminderStore'])->name('deals.reminders.store');
    Route::get('/deals/{id}/reminder/{rid}/edit', [DealController::class, 'reminderEdit'])->name('deals.reminders.edit');
    Route::put('/deals/{id}/reminder/{rid}', [DealController::class, 'reminderUpdate'])->name('deals.reminders.update');
    Route::delete('/deals/{id}/reminder/{rid}', [DealController::class, 'reminderDestroy'])->name('deals.reminders.destroy');

    Route::resource('deals', DealController::class);

    // end Deal Module

    Route::post('/stages/json', [DealStageController::class, 'json'])->name('stages.json');

    // Deal import
    Route::get('deal/import/export', [DealController::class, 'fileImportExport'])->name('deal.file.import');
    Route::post('deal/import', [DealController::class, 'fileImport'])->name('deal.import');
    Route::get('deal/import/modal', [DealController::class, 'fileImportModal'])->name('deal.import.modal');
    Route::post('deal/data/import/', [DealController::class, 'dealImportdata'])->name('deal.import.data');

    // Reports
    Route::get('lead-report', [ReportController::class, 'leadReport'])->name('report.lead');
    Route::get('deal-report', [ReportController::class, 'dealReport'])->name('report.deal');

    // Lead Documents - Removed as per user request
    // Route::resource('lead-documents', \Workdo\Lead\Http\Controllers\LeadDocumentController::class);
    // Route::post('/leads/{id}/document/{document_id}', [\Workdo\Lead\Http\Controllers\LeadDocumentController::class, 'upload'])->name('leads.document.upload');
    // Route::delete('/leads/{id}/document-file/{file_id}', [\Workdo\Lead\Http\Controllers\LeadDocumentController::class, 'deleteFile'])->name('leads.document.delete');

    // Lead Layout Builder
    Route::get('lead-builder', [\Workdo\Lead\Http\Controllers\LeadCustomFieldController::class, 'builder'])->name('lead-builder.index');
    Route::post('lead-builder/save', [\Workdo\Lead\Http\Controllers\LeadCustomFieldController::class, 'saveBuilder'])->name('lead-builder.save');
    Route::post('lead-builder/section', [\Workdo\Lead\Http\Controllers\LeadCustomFieldController::class, 'sectionStore'])->name('lead-builder.section.store');
    Route::put('lead-builder/section/{id}', [\Workdo\Lead\Http\Controllers\LeadCustomFieldController::class, 'sectionUpdate'])->name('lead-builder.section.update');
    Route::delete('lead-builder/section/{id}', [\Workdo\Lead\Http\Controllers\LeadCustomFieldController::class, 'sectionDestroy'])->name('lead-builder.section.destroy');
    Route::post('lead-builder/section/{id}/copy', [\Workdo\Lead\Http\Controllers\LeadCustomFieldController::class, 'sectionCopy'])->name('lead-builder.section.copy');

    Route::get('crm/my-tasks', [LeadController::class, 'myTasks'])->name('leads.my.tasks');
    Route::get('crm/my-reminders', [LeadController::class, 'myReminders'])->name('leads.my.reminders');
    Route::get('crm/visibility-settings', [LeadController::class, 'visibilitySettings'])->name('leads.visibility.settings');
    Route::get('crm/leads/get-stages', [LeadController::class, 'getStagesByPipeline'])->name('leads.get.stages');

    Route::get('crm/settings', [LeadController::class, 'crmSettings'])->name('crm.settings');
    Route::post('crm/settings/save', [LeadController::class, 'saveCrmSettings'])->name('crm.settings.save');
    Route::get('crm/automations', [LeadController::class, 'automationsIndex'])->name('crm.automations.index');
    Route::post('crm/automations/save', [LeadController::class, 'saveAutomations'])->name('crm.automations.save');
    Route::post('crm/automations/facebook/save', [LeadController::class, 'facebookAutomationsSave'])->name('crm.automations.facebook.save');
    Route::post('crm/automations/facebook/delete', [LeadController::class, 'facebookAutomationsDelete'])->name('crm.automations.facebook.delete');
    Route::post('crm/automations/facebook/test', [LeadController::class, 'facebookAutomationsTest'])->name('crm.automations.facebook.test');
    Route::post('crm/automations/webhook-endpoints/update-stage', [LeadController::class, 'webhookEndpointUpdateStage'])->name('crm.automations.webhook-endpoints.update-stage');
    Route::post('crm/automations/whatsapp/update-stage', [\Workdo\Lead\Http\Controllers\WhatsAppConfigController::class, 'updateStage'])->name('crm.automations.whatsapp.update-stage');
    Route::post('leads/check-duplicate', [LeadController::class, 'checkDuplicate'])->name('leads.check.duplicate');
    Route::get('crm/leads/get-stage-requirements', [LeadController::class, 'getStageRequirements'])->name('leads.get.stage.requirements');

    // Secure Reveal Route
    Route::match(['get', 'post'], 'leads/{lead_id}/reveal/{field_name}', [\Workdo\Lead\Http\Controllers\LeadFieldVisibilityController::class, 'revealField'])->name('lead.reveal.field');
    Route::post('leads/visibility/store', [\Workdo\Lead\Http\Controllers\LeadFieldVisibilityController::class, 'store'])->name('leads.visibility.store');
    Route::get('leads/visibility/{id}/edit', [\Workdo\Lead\Http\Controllers\LeadFieldVisibilityController::class, 'edit'])->name('leads.visibility.edit');
    Route::put('leads/visibility/{id}', [\Workdo\Lead\Http\Controllers\LeadFieldVisibilityController::class, 'update'])->name('leads.visibility.update');
    Route::delete('leads/visibility/{id}', [\Workdo\Lead\Http\Controllers\LeadFieldVisibilityController::class, 'destroy'])->name('leads.visibility.delete');
    Route::get('leads/get-stages', [LeadController::class, 'getStagesByPipeline'])->name('leads.get.stages.old');

    // Lead Webhook Endpoints
    Route::resource('webhook-endpoints', \Workdo\Lead\Http\Controllers\WebhookEndpointController::class);
    Route::get('webhook-data', [\Workdo\Lead\Http\Controllers\WebhookDataController::class, 'index'])->name('webhook-data.index');
    Route::get('webhook-endpoints/{id}/data', [\Workdo\Lead\Http\Controllers\WebhookDataController::class, 'forEndpoint'])->name('webhook-endpoints.data');
    Route::get('webhook-data/{id}/payload', [\Workdo\Lead\Http\Controllers\WebhookDataController::class, 'payload'])->name('webhook-data.payload');
    Route::post('webhook-data/{id}/convert', [\Workdo\Lead\Http\Controllers\WebhookDataController::class, 'convertToLead'])->name('webhook-data.convert');
    Route::post('webhook-data/{id}/transfer', [\Workdo\Lead\Http\Controllers\WebhookDataController::class, 'transfer'])->name('webhook-data.transfer');
    Route::get('webhook-data/{id}/transfer-modal', [\Workdo\Lead\Http\Controllers\WebhookDataController::class, 'transferModal'])->name('webhook-data.transfer-modal');

    // Lead Custom Fields
    Route::post('lead-custom-fields/{id}/duplicate', [\Workdo\Lead\Http\Controllers\LeadCustomFieldController::class, 'duplicate'])->name('lead-custom-fields.duplicate');
    Route::resource('lead-custom-fields', \Workdo\Lead\Http\Controllers\LeadCustomFieldController::class);

    // Facebook Lead Integration Data Logs
    Route::get('facebook-lead-data', [\Workdo\Lead\Http\Controllers\FacebookLeadDataController::class, 'index'])->name('facebook-lead-data.index');
    Route::get('facebook-lead-data/{id}/payload', [\Workdo\Lead\Http\Controllers\FacebookLeadDataController::class, 'payload'])->name('facebook-lead-data.payload');
    Route::post('facebook-lead-data/{id}/convert', [\Workdo\Lead\Http\Controllers\FacebookLeadDataController::class, 'convertToLead'])->name('facebook-lead-data.convert');
    Route::post('facebook-lead-data/{rule_id}/sync', [\Workdo\Lead\Http\Controllers\FacebookLeadDataController::class, 'syncHistorical'])->name('facebook-lead-data.sync');

    // Facebook fields and forms retrieval (for UI dropdown & mapping)
    Route::get('crm/automations/facebook/fetch-forms', [LeadController::class, 'facebookFetchForms'])->name('crm.automations.facebook.fetch-forms');
    Route::get('crm/automations/facebook/fetch-questions', [LeadController::class, 'facebookFetchQuestions'])->name('crm.automations.facebook.fetch-questions');

    // Orion / FinKORP integration routes
    Route::get('orion-lead-logs', [\Workdo\Lead\Http\Controllers\OrionIntegrationController::class, 'index'])->name('orion-lead-logs.index');
    Route::get('orion-lead-logs/{id}/payload', [\Workdo\Lead\Http\Controllers\OrionIntegrationController::class, 'payload'])->name('orion-lead-logs.payload');
    Route::post('crm/automations/orion/save', [\Workdo\Lead\Http\Controllers\OrionIntegrationController::class, 'saveOrionRules'])->name('crm.automations.orion.save');
    Route::post('crm/automations/orion/delete', [\Workdo\Lead\Http\Controllers\OrionIntegrationController::class, 'deleteOrionRule'])->name('crm.automations.orion.delete');
    Route::post('crm/automations/orion/test', [\Workdo\Lead\Http\Controllers\OrionIntegrationController::class, 'testOrionConnection'])->name('crm.automations.orion.test');
    Route::post('leads/{id}/orion-fetch', [\Workdo\Lead\Http\Controllers\OrionIntegrationController::class, 'manualFetch'])->name('leads.orion-fetch');

    // CRM Diagnostics Route
    Route::get('/check-crm-diagnostics', function () {
        $user = Auth::user();

        // 1. Handle Workspace Switching Request
        if (request()->has('switch_workspace')) {
            $targetWorkspaceId = (int) request('switch_workspace');
            $targetWorkspace = \App\Models\WorkSpace::find($targetWorkspaceId);
            if ($targetWorkspace) {
                $user->workspace_id = $targetWorkspaceId;
                $user->active_workspace = $targetWorkspaceId;
                $user->save();

                // Sync HRM Employee workspace if active
                if (module_is_active('Hrm') && class_exists('\Workdo\Hrm\Entities\Employee')) {
                    $employee = \Workdo\Hrm\Entities\Employee::where('user_id', $user->id)->first();
                    if ($employee) {
                        $employee->workspace = $targetWorkspaceId;
                        $employee->save();
                    }
                }

                // Clear sidebar menu cache
                \Illuminate\Support\Facades\Cache::forget('sidebar_menu_v2_' . $user->id);

                return redirect('/check-crm-diagnostics?switched=1');
            }
        }

        $workspace = getActiveWorkSpace();
        $creatorId = creatorId();

        $output = "<div style='font-family: Arial, sans-serif; padding: 25px; max-width: 900px; margin: 30px auto; background: #fdfdfd; border: 1px solid #ddd; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>";
        $output .= "<h2 style='color: #2c3e50; margin-top: 0;'>CRM Diagnostics & Workspace Switcher</h2>";
        $output .= "<hr style='border: 0; border-top: 1px solid #eee; margin-bottom: 20px;'>";

        if (request()->has('switched')) {
            $output .= "<div style='background: #d4edda; color: #155724; padding: 12px 15px; border-radius: 5px; margin-bottom: 20px; font-weight: bold; border-left: 5px solid #28a745;'>";
            $output .= "Success: Workspace switched successfully! Please go back to the leads dashboard to check if leads are visible now.";
            $output .= "</div>";
        }

        $output .= "<div style='background: #eaf2f8; border-left: 5px solid #2980b9; padding: 15px; border-radius: 4px; margin-bottom: 25px;'>";
        $output .= "<p style='margin: 0 0 8px 0;'><strong>Logged-in User:</strong> " . e($user->name) . " (ID: " . $user->id . ", Email: " . e($user->email) . ", Type: " . e($user->type) . ", Visibility: " . e($user->visibility_level) . ")</p>";
        $output .= "<p style='margin: 0 0 8px 0;'><strong>Current User Workspace ID:</strong> " . $user->workspace_id . "</p>";
        $output .= "<p style='margin: 0 0 8px 0;'><strong>Current User Active Workspace:</strong> " . $user->active_workspace . "</p>";
        $output .= "<p style='margin: 0;'><strong>Creator ID (creatorId()):</strong> " . $creatorId . "</p>";
        $output .= "</div>";

        // List All Workspaces in the Database
        try {
            $allWorkspaces = \App\Models\WorkSpace::all();
            $output .= "<h3>Available Workspaces in Database (" . $allWorkspaces->count() . "):</h3>";
            $output .= "<table style='width: 100%; border-collapse: collapse; margin-bottom: 25px;'>";
            $output .= "<tr style='background: #f2f2f2; text-align: left;'><th style='padding: 8px; border: 1px solid #ddd;'>ID</th><th style='padding: 8px; border: 1px solid #ddd;'>Workspace Name</th><th style='padding: 8px; border: 1px solid #ddd;'>Created By (ID)</th><th style='padding: 8px; border: 1px solid #ddd;'>Actions</th></tr>";
            foreach ($allWorkspaces as $ws) {
                $isCurrent = ($ws->id == $workspace);
                $rowStyle = $isCurrent ? "style='background: #e8f8f5; font-weight: bold;'" : "";
                
                $output .= "<tr {$rowStyle}>";
                $output .= "<td style='padding: 8px; border: 1px solid #ddd;'>" . $ws->id . "</td>";
                $output .= "<td style='padding: 8px; border: 1px solid #ddd;'>" . e($ws->name) . " " . ($isCurrent ? " <span style='color: #27ae60; font-size: 0.8em;'>(Active)</span>" : "") . "</td>";
                $output .= "<td style='padding: 8px; border: 1px solid #ddd;'>" . $ws->created_by . "</td>";
                $output .= "<td style='padding: 8px; border: 1px solid #ddd;'>";
                if (!$isCurrent) {
                    $output .= "<a href='/check-crm-diagnostics?switch_workspace=" . $ws->id . "' style='background: #3498db; color: white; padding: 4px 8px; border-radius: 4px; text-decoration: none; font-size: 0.85em;'>Switch to this Workspace</a>";
                } else {
                    $output .= "<span style='color: #7f8c8d; font-size: 0.9em;'>Current Active</span>";
                }
                $output .= "</td>";
                $output .= "</tr>";
            }
            $output .= "</table>";
        } catch (\Exception $e) {
            $output .= "<p style='color: red;'><strong>Workspace Fetch Error:</strong> " . e($e->getMessage()) . "</p>";
        }

        // Active Workspace details
        try {
            $workspaceObj = \App\Models\WorkSpace::find($workspace);
            $output .= "<p><strong>Active Workspace Name:</strong> " . ($workspaceObj ? e($workspaceObj->name) : "<span style='color:red;'>Not Found</span>") . "</p>";
        } catch (\Exception $e) {
            $output .= "<p style='color: red;'><strong>Active Workspace Name Error:</strong> " . e($e->getMessage()) . "</p>";
        }

        // Check Pipelines
        try {
            $pipelines = \Workdo\Lead\Entities\Pipeline::where('workspace_id', $workspace)->get();
            $output .= "<h3>Pipelines in Current Workspace (" . $pipelines->count() . "):</h3>";
            if ($pipelines->isEmpty()) {
                $output .= "<p style='color: orange;'>No pipelines found for this workspace in the database.</p>";
            } else {
                $output .= "<ul>";
                foreach ($pipelines as $p) {
                    $output .= "<li>Pipeline ID: " . $p->id . " - <strong>" . e($p->name) . "</strong> (Created By: " . $p->created_by . ")</li>";
                }
                $output .= "</ul>";
            }
        } catch (\Exception $e) {
            $output .= "<p style='color: red;'><strong>Pipeline Query Error:</strong> " . e($e->getMessage()) . "</p>";
        }

        // Check Active Pipeline Loaded by index() Method
        try {
            if ($user->default_pipeline) {
                $pipeline = \Workdo\Lead\Entities\Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $workspace)->where('id', '=', $user->default_pipeline)->first();
                if (!$pipeline) {
                    $pipeline = \Workdo\Lead\Entities\Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $workspace)->first();
                }
            } else {
                $pipeline = \Workdo\Lead\Entities\Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $workspace)->first();
            }

            $output .= "<p><strong>Loaded Active Pipeline:</strong> " . ($pipeline ? "ID: " . $pipeline->id . " - <strong>" . e($pipeline->name) . "</strong>" : "<span style='color: red; font-weight: bold;'>None (Null)</span>") . "</p>";
        } catch (\Exception $e) {
            $output .= "<p style='color: red;'><strong>Active Pipeline Loading Error:</strong> " . e($e->getMessage()) . "</p>";
        }

        // Check Stages
        try {
            $stages = \Workdo\Lead\Entities\LeadStage::where('workspace_id', $workspace)->get();
            $output .= "<h3>Lead Stages in Current Workspace (" . $stages->count() . "):</h3>";
            if ($stages->isEmpty()) {
                $output .= "<p style='color: orange;'>No lead stages found for this workspace in the database.</p>";
            } else {
                $output .= "<ul>";
                foreach ($stages as $s) {
                    $output .= "<li>Stage ID: " . $s->id . " - <strong>" . e($s->name) . "</strong> (Pipeline ID: " . $s->pipeline_id . ", Order: " . $s->order . ")</li>";
                }
                $output .= "</ul>";
            }
        } catch (\Exception $e) {
            $output .= "<p style='color: red;'><strong>Lead Stage Query Error:</strong> " . e($e->getMessage()) . "</p>";
        }

        // Check Leads
        try {
            $leadsCount = \Workdo\Lead\Entities\Lead::where('workspace_id', $workspace)->count();
            $output .= "<p><strong>Total Leads in Workspace:</strong> " . $leadsCount . "</p>";
        } catch (\Exception $e) {
            $output .= "<p style='color: red;'><strong>Leads Query Error:</strong> " . e($e->getMessage()) . "</p>";
        }

        // Check Table Schema / Missing Columns
        try {
            $columns = \DB::getSchemaBuilder()->getColumnListing('leads');
            $output .= "<h3>Leads Table Columns:</h3>";
            $output .= "<p style='font-size: 0.9em; color: #555;'>" . implode(', ', $columns) . "</p>";
        } catch (\Exception $e) {
            $output .= "<p style='color: red;'><strong>Schema Error:</strong> " . e($e->getMessage()) . "</p>";
        }

        $output .= "</div>";
        return $output;
    });

    // Collaborative Sheets Module
    Route::get('crm/sheets', [\Workdo\Lead\Http\Controllers\CrmSheetController::class, 'index'])->name('crm.sheets.index');
    Route::get('crm/sheets/create', [\Workdo\Lead\Http\Controllers\CrmSheetController::class, 'create'])->name('crm.sheets.create');
    Route::post('crm/sheets', [\Workdo\Lead\Http\Controllers\CrmSheetController::class, 'store'])->name('crm.sheets.store');
    Route::get('crm/sheets/{id}', [\Workdo\Lead\Http\Controllers\CrmSheetController::class, 'view'])->name('crm.sheets.view');
    Route::post('crm/sheets/{id}/update-data', [\Workdo\Lead\Http\Controllers\CrmSheetController::class, 'updateData'])->name('crm.sheets.update-data');
    Route::delete('crm/sheets/{id}', [\Workdo\Lead\Http\Controllers\CrmSheetController::class, 'destroy'])->name('crm.sheets.destroy');
    Route::get('crm/sheets/{id}/share', [\Workdo\Lead\Http\Controllers\CrmSheetController::class, 'share'])->name('crm.sheets.share');
    Route::post('crm/sheets/{id}/share', [\Workdo\Lead\Http\Controllers\CrmSheetController::class, 'storeShare'])->name('crm.sheets.store-share');
    Route::post('crm/sheets/collaborator/{id}/accept', [\Workdo\Lead\Http\Controllers\CrmSheetController::class, 'acceptShare'])->name('crm.sheets.accept-share');
    Route::delete('crm/sheets/collaborator/{id}/decline', [\Workdo\Lead\Http\Controllers\CrmSheetController::class, 'declineShare'])->name('crm.sheets.decline-share');
    Route::get('crm/sheets/{id}/export', [\Workdo\Lead\Http\Controllers\CrmSheetController::class, 'export'])->name('crm.sheets.export');
});






