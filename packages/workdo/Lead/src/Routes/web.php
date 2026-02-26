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



// Public Webhook Test Form


Route::group(['middleware' => ['web', 'auth', 'verified', 'PlanModuleCheck:Lead']], function () {
    Route::get('webhook/test/{url}', [\Workdo\Lead\Http\Controllers\WebhookEndpointController::class , 'testForm'])->name('webhook.test.form');
    Route::get('leads/duplicates', [DuplicateController::class , 'index'])->name('leads.duplicates');
    Route::delete('leads/duplicates/{id}', [DuplicateController::class , 'destroy'])->name('leads.duplicates.destroy');

    Route::get('/leads/kanban-batch', [LeadController::class , 'kanbanBatch'])->name('leads.kanban.batch');
    Route::resource('leads', LeadController::class);
    Route::get('dashboard/crm', [LeadController::class , 'dashboard'])->name('lead.dashboard');

    Route::resource('pipelines', PipelineController::class);

    Route::post('/deals/change-pipeline', [DealController::class , 'changePipeline'])->name('deals.change.pipeline');

    Route::get('/leads-list', [LeadController::class , 'lead_list'])->name('leads.list');

    Route::resource('lead-stages', LeadStageController::class);
    Route::post('/lead_stages/order', [LeadStageController::class , 'order'])->name('lead_stages.order');

    Route::resource('deal-stages', DealStageController::class);
    Route::post('/deal_stages/order', [DealStageController::class , 'order'])->name('deal-stages.order');

    Route::resource('labels', LabelController::class);
    Route::resource('sources', SourceController::class);

    Route::get('/leads-deals/dashboard', [LeadController::class , 'dashboard'])->name('leads.dashboard');
    Route::post('/leads/order', [LeadController::class , 'order'])->name('leads.order');
    Route::post('/leads/json', [LeadController::class , 'json'])->name('leads.json');
    Route::post('/leads/json-designation', [LeadController::class , 'jsonDesignation'])->name('lead.json.designation');
    Route::post('/leads/json-user', [LeadController::class , 'jsonUser'])->name('lead.json.user');
    Route::post('/leads/{id}/file', [LeadController::class , 'fileUpload'])->name('leads.file.upload');
    Route::get('/leads/{id}/file/{fid}', [LeadController::class , 'fileDownload'])->name('leads.file.download');
    Route::delete('/leads/{id}/file/delete/{fid}', [LeadController::class , 'fileDelete'])->name('leads.file.delete');
    Route::post('/leads/{id}/note', [LeadController::class , 'noteStore'])->name('leads.note.store');
    Route::get('/leads/{id}/labels', [LeadController::class , 'labels'])->name('leads.labels');
    Route::post('/leads/{id}/labels', [LeadController::class , 'labelStore'])->name('leads.labels.store');
    Route::get('/leads/{id}/users', [LeadController::class , 'userEdit'])->name('leads.users.edit');
    Route::put('/leads/{id}/users', [LeadController::class , 'userUpdate'])->name('leads.users.update');
    Route::delete('/leads/{id}/users/{uid}', [LeadController::class , 'userDestroy'])->name('leads.users.destroy');
    Route::get('/leads/{id}/products', [LeadController::class , 'productEdit'])->name('leads.products.edit');
    Route::put('/leads/{id}/products', [LeadController::class , 'productUpdate'])->name('leads.products.update');
    Route::delete('/leads/{id}/products/{uid}', [LeadController::class , 'productDestroy'])->name('leads.products.destroy');
    Route::get('/leads/{id}/sources', [LeadController::class , 'sourceEdit'])->name('leads.sources.edit');
    Route::put('/leads/{id}/sources', [LeadController::class , 'sourceUpdate'])->name('leads.sources.update');
    Route::delete('/leads/{id}/sources/{uid}', [LeadController::class , 'sourceDestroy'])->name('leads.sources.destroy');
    Route::get('/leads/{id}/discussions', [LeadController::class , 'discussionCreate'])->name('leads.discussions.create');
    Route::post('/leads/{id}/discussions', [LeadController::class , 'discussionStore'])->name('leads.discussion.store');
    Route::delete('/leads/{id}/discussions/{did}', [LeadController::class , 'discussionDestroy'])->name('leads.discussion.destroy');
    Route::get('/leads/{id}/show_convert', [LeadController::class , 'showConvertToDeal'])->name('leads.convert.deal');
    Route::post('/leads/{id}/convert', [LeadController::class , 'convertToDeal'])->name('leads.convert.to.deal');

    Route::get('/leads/{id}/call', [LeadController::class , 'callCreate'])->name('leads.calls.create');
    Route::post('/leads/{id}/call', [LeadController::class , 'callStore'])->name('leads.calls.store');
    Route::get('/leads/{id}/call/{cid}/edit', [LeadController::class , 'callEdit'])->name('leads.calls.edit');
    Route::put('/leads/{id}/call/{cid}', [LeadController::class , 'callUpdate'])->name('leads.calls.update');
    Route::delete('/leads/{id}/call/{cid}', [LeadController::class , 'callDestroy'])->name('leads.calls.destroy');

    // Lead Email
    Route::get('/leads/{id}/email', [LeadController::class , 'emailCreate'])->name('leads.emails.create');
    Route::post('/leads/{id}/email', [LeadController::class , 'emailStore'])->name('leads.emails.store');

    //Lead import
    Route::get('lead/import/export', [LeadController::class , 'fileImportExport'])->name('lead.file.import');
    Route::post('lead/import', [LeadController::class , 'fileImport'])->name('lead.import');
    Route::get('lead/import/modal', [LeadController::class , 'fileImportModal'])->name('lead.import.modal');
    Route::get('lead/import/stages', [LeadController::class , 'getStages'])->name('lead.import.stages');
    Route::post('lead/data/import/', [LeadController::class , 'leadImportdata'])->name('lead.import.data');
    Route::get('lead/import/duplicates/download', [LeadController::class , 'downloadDuplicateLeads'])->name('lead.import.duplicates.download');

    // Lead Reminder
    Route::get('/leads/{id}/reminder', [LeadController::class , 'reminderCreate'])->name('leads.reminders.create');
    Route::post('/leads/{id}/reminder', [LeadController::class , 'reminderStore'])->name('leads.reminders.store');
    Route::get('/leads/{id}/reminder/{rid}/edit', [LeadController::class , 'reminderEdit'])->name('leads.reminders.edit');
    Route::put('/leads/{id}/reminder/{rid}', [LeadController::class , 'reminderUpdate'])->name('leads.reminders.update');
    Route::delete('/leads/{id}/reminder/{rid}', [LeadController::class , 'reminderDestroy'])->name('leads.reminders.destroy');

    // Lead Task
    Route::get('/leads/{id}/task', [LeadController::class , 'taskCreate'])->name('leads.tasks.create');
    Route::post('/leads/{id}/task', [LeadController::class , 'taskStore'])->name('leads.tasks.store');
    Route::get('/leads/{id}/task/{tid}/edit', [LeadController::class , 'taskEdit'])->name('leads.tasks.edit');
    Route::put('/leads/{id}/task/{tid}', [LeadController::class , 'taskUpdate'])->name('leads.tasks.update');
    Route::put('leads/{id}/task/{task_id}/update-status', [LeadController::class , 'taskUpdateStatus'])->name('leads.tasks.update.status');
    Route::post('leads/filter/save', [LeadController::class , 'saveFilter'])->name('leads.filter.save');
    Route::post('leads/search-settings/save', [LeadController::class , 'saveSearchSettings'])->name('leads.search.settings.save');
    Route::delete('leads/filter/{id}/delete', [LeadController::class , 'deleteFilter'])->name('leads.filter.delete');
    Route::delete('/leads/{id}/task/{tid}', [LeadController::class , 'taskDestroy'])->name('leads.tasks.destroy');
    Route::post('/leads/bulk-action', [LeadController::class , 'bulkAction'])->name('leads.bulk.action');
    Route::get('/leads/duplicates-list', [LeadController::class , 'duplicateList'])->name('leads.duplicates.list');

    // Bulk Task & Reminder
    Route::post('/leads/bulk-task-reminder', [LeadController::class , 'bulkTaskReminderCreate'])->name('leads.bulk.task.reminder.create');
    Route::post('/leads/bulk-task-reminder/store', [LeadController::class , 'bulkTaskReminderStore'])->name('leads.bulk.task.reminder.store');

    // Global Task Management
    Route::get('/lead-tasks', [LeadTaskController::class , 'index'])->name('lead_tasks.index');
    Route::get('/lead-tasks/create', [LeadTaskController::class , 'create'])->name('lead_tasks.create');
    Route::post('/lead-tasks/store', [LeadTaskController::class , 'store'])->name('lead_tasks.store');
    Route::delete('/lead-tasks/{id}', [LeadTaskController::class , 'destroy'])->name('lead_tasks.destroy');
    Route::post('/lead-tasks/bulk-destroy', [LeadTaskController::class , 'bulkDestroy'])->name('lead_tasks.bulk_destroy');

    // Deal Module
    Route::post('/deals/user', [DealController::class , 'jsonUser'])->name('deal.user.json');
    Route::post('/deals/order', [DealController::class , 'order'])->name('deals.order');
    Route::post('/deals/change-pipeline', [DealController::class , 'changePipeline'])->name('deals.change.pipeline');
    Route::post('/deals/change-deal-status/{id}', [DealController::class , 'changeStatus'])->name('deals.change.status');
    Route::get('/deals/{id}/labels', [DealController::class , 'labels'])->name('deals.labels');
    Route::post('/deals/{id}/labels', [DealController::class , 'labelStore'])->name('deals.labels.store');
    Route::get('/deals/{id}/users', [DealController::class , 'userEdit'])->name('deals.users.edit');
    Route::put('/deals/{id}/users', [DealController::class , 'userUpdate'])->name('deals.users.update');
    Route::delete('/deals/{id}/users/{uid}', [DealController::class , 'userDestroy'])->name('deals.users.destroy');
    Route::get('/deals/{id}/clients', [DealController::class , 'clientEdit'])->name('deals.clients.edit');
    Route::put('/deals/{id}/clients', [DealController::class , 'clientUpdate'])->name('deals.clients.update');
    Route::delete('/deals/{id}/clients/{uid}', [DealController::class , 'clientDestroy'])->name('deals.clients.destroy');
    Route::get('/deals/{id}/products', [DealController::class , 'productEdit'])->name('deals.products.edit');
    Route::put('/deals/{id}/products', [DealController::class , 'productUpdate'])->name('deals.products.update');
    Route::delete('/deals/{id}/products/{uid}', [DealController::class , 'productDestroy'])->name('deals.products.destroy');
    Route::get('/deals/{id}/sources', [DealController::class , 'sourceEdit'])->name('deals.sources.edit');
    Route::put('/deals/{id}/sources', [DealController::class , 'sourceUpdate'])->name('deals.sources.update');
    Route::delete('/deals/{id}/sources/{uid}', [DealController::class , 'sourceDestroy'])->name('deals.sources.destroy');
    Route::post('/deals/{id}/file', [DealController::class , 'fileUpload'])->name('deals.file.upload');
    Route::get('/deals/{id}/file/{fid}', [DealController::class , 'fileDownload'])->name('deals.file.download');
    Route::delete('/deals/{id}/file/delete/{fid}', [DealController::class , 'fileDelete'])->name('deals.file.delete');
    Route::post('/deals/{id}/note', [DealController::class , 'noteStore'])->name('deals.note.store');
    Route::get('/deals/{id}/task', [DealController::class , 'taskCreate'])->name('deals.tasks.create');
    Route::post('/deals/{id}/task', [DealController::class , 'taskStore'])->name('deals.tasks.store');
    Route::get('/deals/{id}/task/{tid}/show', [DealController::class , 'taskShow'])->name('deals.tasks.show');
    Route::get('/deals/{id}/task/{tid}/edit', [DealController::class , 'taskEdit'])->name('deals.tasks.edit');
    Route::put('/deals/{id}/task/{tid}', [DealController::class , 'taskUpdate'])->name('deals.tasks.update');
    Route::put('/deals/{id}/task_status/{tid}', [DealController::class , 'taskUpdateStatus'])->name('deals.tasks.update_status');
    Route::delete('/deals/{id}/task/{tid}', [DealController::class , 'taskDestroy'])->name('deals.tasks.destroy');
    Route::get('/deals/{id}/discussions', [DealController::class , 'discussionCreate'])->name('deals.discussions.create');
    Route::post('/deals/{id}/discussions', [DealController::class , 'discussionStore'])->name('deals.discussion.store');
    Route::get('/deals/list', [DealController::class , 'deal_list'])->name('deals.list');

    // Deal Calls
    Route::get('/deals/{id}/call', [DealController::class , 'callCreate'])->name('deals.calls.create');
    Route::post('/deals/{id}/call', [DealController::class , 'callStore'])->name('deals.calls.store');
    Route::get('/deals/{id}/call/{cid}/edit', [DealController::class , 'callEdit'])->name('deals.calls.edit');
    Route::put('/deals/{id}/call/{cid}', [DealController::class , 'callUpdate'])->name('deals.calls.update');
    Route::delete('/deals/{id}/call/{cid}', [DealController::class , 'callDestroy'])->name('deals.calls.destroy');

    // Deal Email
    Route::get('/deals/{id}/email', [DealController::class , 'emailCreate'])->name('deals.emails.create');
    Route::post('/deals/{id}/email', [DealController::class , 'emailStore'])->name('deals.emails.store');

    // Deal Reminder
    Route::get('/deals/{id}/reminder', [DealController::class , 'reminderCreate'])->name('deals.reminders.create');
    Route::post('/deals/{id}/reminder', [DealController::class , 'reminderStore'])->name('deals.reminders.store');
    Route::get('/deals/{id}/reminder/{rid}/edit', [DealController::class , 'reminderEdit'])->name('deals.reminders.edit');
    Route::put('/deals/{id}/reminder/{rid}', [DealController::class , 'reminderUpdate'])->name('deals.reminders.update');
    Route::delete('/deals/{id}/reminder/{rid}', [DealController::class , 'reminderDestroy'])->name('deals.reminders.destroy');

    Route::resource('deals', DealController::class);

    // end Deal Module

    Route::post('/stages/json', [DealStageController::class , 'json'])->name('stages.json');

    // Deal import
    Route::get('deal/import/export', [DealController::class , 'fileImportExport'])->name('deal.file.import');
    Route::post('deal/import', [DealController::class , 'fileImport'])->name('deal.import');
    Route::get('deal/import/modal', [DealController::class , 'fileImportModal'])->name('deal.import.modal');
    Route::post('deal/data/import/', [DealController::class , 'dealImportdata'])->name('deal.import.data');

    // Reports
    Route::get('lead-report', [ReportController::class , 'leadReport'])->name('report.lead');
    Route::get('deal-report', [ReportController::class , 'dealReport'])->name('report.deal');

    // Lead Documents - Removed as per user request
    // Route::resource('lead-documents', \Workdo\Lead\Http\Controllers\LeadDocumentController::class);
    // Route::post('/leads/{id}/document/{document_id}', [\Workdo\Lead\Http\Controllers\LeadDocumentController::class, 'upload'])->name('leads.document.upload');
    // Route::delete('/leads/{id}/document-file/{file_id}', [\Workdo\Lead\Http\Controllers\LeadDocumentController::class, 'deleteFile'])->name('leads.document.delete');

    // Lead Layout Builder
    Route::get('lead-builder', [\Workdo\Lead\Http\Controllers\LeadCustomFieldController::class , 'builder'])->name('lead-builder.index');
    Route::post('lead-builder/save', [\Workdo\Lead\Http\Controllers\LeadCustomFieldController::class , 'saveBuilder'])->name('lead-builder.save');
    Route::post('lead-builder/section', [\Workdo\Lead\Http\Controllers\LeadCustomFieldController::class , 'sectionStore'])->name('lead-builder.section.store');
    Route::put('lead-builder/section/{id}', [\Workdo\Lead\Http\Controllers\LeadCustomFieldController::class , 'sectionUpdate'])->name('lead-builder.section.update');
    Route::delete('lead-builder/section/{id}', [\Workdo\Lead\Http\Controllers\LeadCustomFieldController::class , 'sectionDestroy'])->name('lead-builder.section.destroy');

    Route::get('crm/my-tasks', [LeadController::class , 'myTasks'])->name('leads.my.tasks');
    Route::get('crm/my-reminders', [LeadController::class , 'myReminders'])->name('leads.my.reminders');
    Route::get('crm/visibility-settings', [LeadController::class , 'visibilitySettings'])->name('leads.visibility.settings');
    Route::get('crm/leads/get-stages', [LeadController::class , 'getStagesByPipeline'])->name('leads.get.stages');

    Route::get('crm/settings', [LeadController::class , 'crmSettings'])->name('crm.settings');
    Route::post('crm/settings/save', [LeadController::class , 'saveCrmSettings'])->name('crm.settings.save');
    Route::post('leads/check-duplicate', [LeadController::class , 'checkDuplicate'])->name('leads.check.duplicate');
    Route::get('crm/leads/get-stage-requirements', [LeadController::class , 'getStageRequirements'])->name('leads.get.stage.requirements');

    // Secure Reveal Route
    Route::match (['get', 'post'], 'leads/{lead_id}/reveal/{field_name}', [\Workdo\Lead\Http\Controllers\LeadFieldVisibilityController::class , 'revealField'])->name('lead.reveal.field');
    Route::post('leads/visibility/store', [\Workdo\Lead\Http\Controllers\LeadFieldVisibilityController::class , 'store'])->name('leads.visibility.store');
    Route::get('leads/visibility/{id}/edit', [\Workdo\Lead\Http\Controllers\LeadFieldVisibilityController::class , 'edit'])->name('leads.visibility.edit');
    Route::put('leads/visibility/{id}', [\Workdo\Lead\Http\Controllers\LeadFieldVisibilityController::class , 'update'])->name('leads.visibility.update');
    Route::delete('leads/visibility/{id}', [\Workdo\Lead\Http\Controllers\LeadFieldVisibilityController::class , 'destroy'])->name('leads.visibility.delete');
    Route::get('leads/get-stages', [LeadController::class , 'getStagesByPipeline'])->name('leads.get.stages.old');

    // Lead Webhook Endpoints
    Route::resource('webhook-endpoints', \Workdo\Lead\Http\Controllers\WebhookEndpointController::class);
    Route::get('webhook-data', [\Workdo\Lead\Http\Controllers\WebhookDataController::class , 'index'])->name('webhook-data.index');
    Route::get('webhook-endpoints/{id}/data', [\Workdo\Lead\Http\Controllers\WebhookDataController::class , 'forEndpoint'])->name('webhook-endpoints.data');
    Route::get('webhook-data/{id}/payload', [\Workdo\Lead\Http\Controllers\WebhookDataController::class , 'payload'])->name('webhook-data.payload');
    Route::post('webhook-data/{id}/convert', [\Workdo\Lead\Http\Controllers\WebhookDataController::class , 'convertToLead'])->name('webhook-data.convert');
    Route::post('webhook-data/{id}/transfer', [\Workdo\Lead\Http\Controllers\WebhookDataController::class , 'transfer'])->name('webhook-data.transfer');
    Route::get('webhook-data/{id}/transfer-modal', [\Workdo\Lead\Http\Controllers\WebhookDataController::class , 'transferModal'])->name('webhook-data.transfer-modal');

    // Lead Custom Fields
    Route::post('lead-custom-fields/{id}/duplicate', [\Workdo\Lead\Http\Controllers\LeadCustomFieldController::class , 'duplicate'])->name('lead-custom-fields.duplicate');
    Route::resource('lead-custom-fields', \Workdo\Lead\Http\Controllers\LeadCustomFieldController::class);
});
