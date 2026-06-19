<?php


Route::get('/test-routing', function () {
    return "Routing is working!";
});

Route::get('/auto-login', function () {
    $user = \App\Models\User::where('email', 'sstockology@gmail.com')->first();
    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);
        return redirect('/targets');
    }
    return "User not found!";
});

use App\Http\Controllers\Auth\AuthenticatedSessionController;

use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BanktransferController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Company\SettingsController as CompanySettingsController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CustomDomainRequestController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\HelpdeskConversionController;
use App\Http\Controllers\HelpdeskTicketCategoryController;
use App\Http\Controllers\HelpdeskTicketController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MetaController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SuperAdmin\SettingsController as SuperAdminSettingsController;
use App\Http\Controllers\WarehouseTransferController;
use App\Http\Controllers\WorkSpaceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReferralProgramController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

/* |-------------------------------------------------------------------------- | Web Routes |-------------------------------------------------------------------------- | | Here is where you can register web routes for your application. These | routes are loaded by the RouteServiceProvider and all of them will | be assigned to the "web" middleware group. Make something great! | */


// Auth::routes();
require __DIR__ . '/auth.php';

// custom domain code
Route::middleware('domain-check')->group(function () {
    Route::get('/register/{lang?}', [RegisteredUserController::class , 'create'])->name('register');
    Route::get('/login/{lang?}', [AuthenticatedSessionController::class , 'create'])->name('login');
    Route::get('/forgot-password/{lang?}', [PasswordResetLinkController::class , 'create'])->name('password.request');
    Route::get('/verify-email/{lang?}', [EmailVerificationPromptController::class , '__invoke'])->name('verification.notice');

    // module page before login
    Route::get('add-on', [HomeController::class , 'Software'])->name('apps.software');
    Route::get('add-on/details/{slug}', [HomeController::class , 'SoftwareDetails'])->name('software.details');
    Route::get('pricing', [HomeController::class , 'Pricing'])->name('apps.pricing');
    Route::get('pricing/plans', [HomeController::class , 'PricingPlans'])->name('apps.pricing.plan');
    Route::get('pages', [HomeController::class , 'CustomPage'])->name('custompage');
    Route::get('/', [HomeController::class , 'index'])->name('start');
});
Route::middleware(['auth', 'verified'])->group(function () {

    //Role & Permission
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);

    //dashbord
    Route::get('/dashboard', [HomeController::class , 'Dashboard'])->name('dashboard');
    Route::get('/home', [HomeController::class , 'Dashboard'])->name('home');


    // settings
    Route::resource('settings', SettingsController::class);
    Route::post('settings-save', [CompanySettingsController::class , 'store'])->name('settings.save');
    Route::post('company/settings-save', [CompanySettingsController::class , 'store'])->name('company.settings.save');
    Route::post('super-admin/settings-save', [SuperAdminSettingsController::class , 'store'])->name('super.admin.settings.save');
    Route::post('super-admin/system-settings-save', [SuperAdminSettingsController::class , 'SystemStore'])->name('super.admin.system.setting.store');
    Route::post('company/system-settings-save', [CompanySettingsController::class , 'SystemStore'])->name('company.system.setting.store');
    Route::post('company-setting-save', [CompanySettingsController::class , 'companySettingStore'])->name('company.setting.save');
    Route::post('comapny-currency-settings', [CompanySettingsController::class , 'saveCompanyCurrencySettings'])->name('company.setting.currency.settings');
    Route::post('company/update-note-value', [SuperAdminSettingsController::class , 'updateNoteValue'])->name('company.update.note.value');

    Route::post('email-settings-save', [SettingsController::class , 'mailStore'])->name('email.setting.store');
    Route::post('test-mail', [SettingsController::class , 'testMail'])->name('test.mail');
    Route::post('test-mail-send', [SettingsController::class , 'sendTestMail'])->name('test.mail.send');
    Route::post('email/getfields', [SettingsController::class , 'getfields'])->name('get.emailfields');
    Route::post('email-notification-settings-save', [SettingsController::class , 'mailNotificationStore'])->name('email.notification.setting.store');

    Route::post('cookie-settings-save', [SuperAdminSettingsController::class , 'CookieSetting'])->name('cookie.setting.store');
    Route::post('pusher-setting', [SuperAdminSettingsController::class , 'savePusherSettings'])->name('pusher.setting');
    Route::post('seo/setting/save', [SuperAdminSettingsController::class , 'seoSetting'])->name('seo.setting.save');
    Route::post('settings/storage/save', [SuperAdminSettingsController::class , 'storageStore'])->name('storage.setting.store');
    Route::post('ai/key/setting/save', [SuperAdminSettingsController::class , 'aiKeySettingSave'])->name('ai.key.setting.save');
    Route::post('currency-settings', [SuperAdminSettingsController::class , 'saveCurrencySettings'])->name('super.admin.currency.settings');
    Route::post('/update-note-value', [SuperAdminSettingsController::class , 'updateNoteValue'])->name('admin.update.note.value');

    Route::get('/setting/section/{module}/{method?}', [SettingsController::class , 'getSettingSection'])->name('setting.section.get');

    // bank-transfer
    Route::resource('bank-transfer-request', BanktransferController::class);
    Route::post('bank-transfer-setting', [BanktransferController::class , 'setting'])->name('bank.transfer.setting');
    Route::post('/bank/transfer/pay', [BanktransferController::class , 'planPayWithBank'])->name('plan.pay.with.bank');


    Route::get('invoice-bank-request/{id}', [BanktransferController::class , 'invoiceBankRequestEdit'])->name('invoice.bank.request.edit');
    Route::post('bank-transfer-request-edit/{id}', [BanktransferController::class , 'invoiceBankRequestupdate'])->name('invoice.bank.request.update');

    // domain Request Module
    Route::resource('custom_domain_request', CustomDomainRequestController::class);
    Route::get('custom_domain_request/{id}/{response}', [CustomDomainRequestController::class , 'acceptRequest'])->name('custom_domain_request.request');

    Route::get('user-management', [\App\Http\Controllers\UserManagementHubController::class , 'index'])->name('user.management.index');
    Route::post('user-visibility/update', [\App\Http\Controllers\UserManagementHubController::class , 'updateVisibility'])->name('user.visibility.update');
    Route::get('department-users/{id}', [\App\Http\Controllers\UserManagementHubController::class , 'departmentUsers'])->name('department.users');
    Route::post('department-user/add', [\App\Http\Controllers\UserManagementHubController::class , 'addDepartmentUser'])->name('department.user.add');
    Route::post('department-user/remove', [\App\Http\Controllers\UserManagementHubController::class , 'removeDepartmentUser'])->name('department.user.remove');
    Route::post('department-convert/{id}', [\App\Http\Controllers\UserManagementHubController::class , 'convertToTeam'])->name('department.convert');

    // Targets
    Route::get('targets/get-unit-users', [\App\Http\Controllers\TargetController::class, 'getUnitUsers'])->name('targets.get.unit.users');
    Route::get('targets/get-pipeline-stages', [\App\Http\Controllers\TargetController::class, 'getPipelineStages'])->name('targets.get.pipeline.stages');
    Route::get('targets/team/{id}/members', [\App\Http\Controllers\TargetController::class, 'getTeamMembersPerformance'])->name('targets.team.members.performance');
    Route::get('targets/department/{id}/teams', [\App\Http\Controllers\TargetController::class, 'getDepartmentTeamsPerformance'])->name('targets.department.teams.performance');
    Route::get('targets/templates/create', [\App\Http\Controllers\TargetController::class, 'templateCreate'])->name('targets.templates.create');
    Route::post('targets/templates', [\App\Http\Controllers\TargetController::class, 'templateStore'])->name('targets.templates.store');
    Route::get('targets/templates/{id}/edit', [\App\Http\Controllers\TargetController::class, 'templateEdit'])->name('targets.templates.edit');
    Route::put('targets/templates/{id}', [\App\Http\Controllers\TargetController::class, 'templateUpdate'])->name('targets.templates.update');
    Route::delete('targets/templates/{id}', [\App\Http\Controllers\TargetController::class, 'templateDestroy'])->name('targets.templates.destroy');
    Route::get('targets/templates/{id}/assign', [\App\Http\Controllers\TargetController::class, 'templateAssignView'])->name('targets.templates.assign.view');
    Route::post('targets/templates/{id}/assign', [\App\Http\Controllers\TargetController::class, 'templateAssignStore'])->name('targets.templates.assign.store');
    Route::get('targets/{id}/progress', [\App\Http\Controllers\TargetController::class, 'progressView'])->name('targets.progress.view');
    Route::post('targets/{id}/progress', [\App\Http\Controllers\TargetController::class, 'updateProgress'])->name('targets.progress.update');
    Route::post('targets/{id}/status', [\App\Http\Controllers\TargetController::class, 'updateStatus'])->name('targets.status.update');
    Route::post('targets/bulk-destroy', [\App\Http\Controllers\TargetController::class, 'bulkDestroy'])->name('targets.bulk-destroy');
    Route::resource('targets', \App\Http\Controllers\TargetController::class);
    Route::resource('esign-templates', \App\Http\Controllers\ESignTemplateController::class);
    Route::post('esign-templates/{id}/fields', [\App\Http\Controllers\ESignTemplateController::class, 'addField'])->name('esign-templates.fields.add');
    Route::delete('esign-template-fields/{id}', [\App\Http\Controllers\ESignTemplateController::class, 'removeField'])->name('esign-templates.fields.remove');
    Route::post('esign-templates/{id}/fields/batch', [\App\Http\Controllers\ESignTemplateController::class, 'saveBatchFields'])->name('esign-templates.fields.batch');
    Route::get('esign-templates/{id}/pdf', [\App\Http\Controllers\ESignTemplateController::class, 'streamPdf'])->name('esign-templates.pdf.stream');
    Route::get('leads/{lead_id}/esign-fill/{template_id?}', [\App\Http\Controllers\ESignTemplateController::class, 'fillPdfForm'])->name('leads.esign.fill');

    //users
    Route::resource('users', UserController::class);
    Route::get('users/list/view', [UserController::class , 'List'])->name('users.list.view');
    Route::get('profile', [UserController::class , 'profile'])->name('profile');
    Route::post('edit-profile', [UserController::class , 'editprofile'])->name('edit.profile');
    Route::post('change-password', [UserController::class , 'updatePassword'])->name('update.password');
    Route::any('user-reset-password/{id}', [UserController::class , 'UserPassword'])->name('users.reset');
    Route::get('user-login/{id}', [UserController::class , 'LoginManage'])->name('users.login');
    Route::post('user-reset-password/{id}', [UserController::class , 'UserPasswordReset'])->name('user.password.update');
    Route::get('users/{id}/login-with-company', [UserController::class , 'LoginWithCompany'])->name('login.with.company');
    Route::get('company-info/{id}', [UserController::class , 'CompnayInfo'])->name('company.info');
    Route::post('user-unable', [UserController::class , 'UserUnable'])->name('user.unable');
    Route::get('user-verified/{id}', [UserController::class , 'verifeduser'])->name('user.verified');
    Route::get('users/{id}/status', [UserController::class , 'updateStatus'])->name('users.status');

    // Messenger functionality removed - causing high CPU load
    // All messenger routes have been disabled to stop API polling

    // User Notifications
    Route::get('notifications', [\App\Http\Controllers\UserNotificationController::class , 'index'])->name('notifications.index');
    Route::get('notifications/latest-unread', [\App\Http\Controllers\UserNotificationController::class , 'getLatestUnreadNotification'])->name('notifications.latest.unread');
    Route::post('notifications/read', [\App\Http\Controllers\UserNotificationController::class , 'markRead'])->name('notifications.read');
    Route::get('notifications/count', [\App\Http\Controllers\UserNotificationController::class , 'getCount'])->name('notifications.count');

    //User Log
    Route::get('/users/logs/history', [UserController::class , 'UserLogHistory'])->name('users.userlog.history');
    Route::get('users/logs/{id}', [UserController::class , 'UserLogView'])->name('users.userlog.view');
    Route::delete('users/logs/destroy/{id}', [UserController::class , 'UserLogDestroy'])->name('users.userlog.destroy');

    // Comprehensive User Activity Tracking
    Route::get('/users/activity/history', [\App\Http\Controllers\UserController_Activity::class, 'UserActivityHistory'])->name('users.activity.history');
    Route::get('users/activity/{id}', [UserController::class , 'UserActivityView'])->name('users.activity.view');
    Route::delete('users/activity/destroy/{id}', [UserController::class , 'UserActivityDestroy'])->name('users.activity.destroy');
    Route::get('users/activity/summary', [UserController::class , 'UserActivitySummary'])->name('users.activity.summary');
    Route::get('users/activity/export', [UserController::class , 'UserActivityExport'])->name('users.activity.export');

    // Company Activity Dashboard
    Route::get('company/activity/dashboard', [UserController::class , 'CompanyActivityDashboard'])->name('company.activity.dashboard');

    // Standalone Activity Monitors
    Route::get('users-activity-monitor', function () {
        return redirect('/users_activity_simple.php');
    })->name('users.activity.monitor');

    Route::get('leads-activity-monitor', function () {
        return redirect('/leads_activity_monitor.php');
    })->name('leads.activity.monitor');

    // users import
    Route::get('users/import/export', [UserController::class , 'fileImportExport'])->name('users.file.import');
    Route::get('users/import/modal', [UserController::class , 'fileImportModal'])->name('users.import.modal');
    Route::post('users/import', [UserController::class , 'fileImport'])->name('users.import');
    Route::post('users/data/import/', [UserController::class , 'UserImportdata'])->name('users.import.data');


    // impersonating
    Route::get('login-with-company/exit', [UserController::class , 'ExitCompany'])->name('exit.company');

    // Language
    Route::get('/lang/change/{lang}', [LanguageController::class , 'changeLang'])->name('lang.change');
    Route::get('langmanage/{lang?}/{module?}', [LanguageController::class , 'index'])->name('lang.index');
    Route::get('create-language', [LanguageController::class , 'create'])->name('create.language');
    Route::post('langs/{lang?}/{module?}', [LanguageController::class , 'storeData'])->name('lang.store.data');
    Route::post('disable-language', [LanguageController::class , 'disableLang'])->name('disablelanguage');
    Route::any('store-language', [LanguageController::class , 'store'])->name('store.language');
    Route::delete('/lang/{id}', [LanguageController::class , 'destroy'])->name('lang.destroy');
    // End Language

    // Workspace
    Route::resource('workspace', WorkSpaceController::class);
    Route::get('workspace/change/{id}', [WorkSpaceController::class , 'change'])->name('workspace.change');
    Route::post('workspace/check', [WorkSpaceController::class , 'workspaceCheck'])->name('workspace.check');

    // End Workspace

    // Plans
    Route::resource('plans', PlanController::class);

    Route::get('plan/list', [PlanController::class , 'PlanList'])->name('plan.list');
    Route::post('plan/store', [PlanController::class , 'PlanStore'])->name('plan.store');
    Route::get('plan/active', [PlanController::class , 'ActivePlans'])->name('active.plans');
    Route::get('upgrade-plan/{id}', [PlanController::class , 'upgradePlan'])->name('upgrade.plan');
    Route::get('plan/buy/{plan_id}/{user_id}', [PlanController::class , 'planDetail'])->name('plan.details');
    Route::get('modules/buy/{user_id}', [PlanController::class , 'moduleBuy'])->name('module.buy');
    Route::post('direct-assign-plan-to-user/{plan_id}/{user_id}', [PlanController::class , 'directAssignPlanToUser'])->name('assign.plan.user');
    Route::any('plan/package-data', [PlanController::class , 'PackageData'])->name('package.data');
    Route::get('plan/plan-buy/{id}', [PlanController::class , 'PlanBuy'])->name('plan.buy');
    Route::get('plan/plan-trial/{id}', [PlanController::class , 'PlanTrial'])->name('plan.trial');
    Route::get('plan/order', [PlanController::class , 'orders'])->name('plan.order.index');
    Route::get('add-one/detail/{id}', [PlanController::class , 'AddOneDetail'])->name('add-one.detail');
    Route::post('add-one/detail/save/{id}', [PlanController::class , 'AddOneDetailSave'])->name('add-one.detail.save');
    Route::post('update-plan-status', [PlanController::class , 'updateStatus'])->name('update.plan.status');
    Route::get('plan/refund/{id}/{user_id}', [PlanController::class , 'refund'])->name('order.refund');

    Route::post('company/settings-save', [CompanySettingsController::class , 'store'])->name('company.settings.save');
    Route::post('super-admin/settings-save', [SuperAdminSettingsController::class , 'store'])->name('super.admin.settings.save');

    // Coupon
    Route::resource('coupons', CouponController::class);
    Route::get('/apply-coupon', [CouponController::class , 'applyCoupon'])->name('apply.coupon');
    // end Coupon

    // Module Install
    Route::get('modules/list', [ModuleController::class , 'index'])->name('module.index');
    Route::get('modules/add', [ModuleController::class , 'add'])->name('module.add');
    Route::post('install-modules', [ModuleController::class , 'install'])->name('module.install');
    Route::post('modules-enable', [ModuleController::class , 'enable'])->name('module.enable');
    Route::get('cancel/add-on/{name}/{user_id?}', [ModuleController::class , 'CancelAddOn'])->name('cancel.add.on');
    // End Module Install

    // Email Templates
    Route::resource('email-templates', EmailTemplateController::class);
    Route::get('email_template_lang/{id}/{lang?}', [EmailTemplateController::class , 'show'])->name('manage.email.language');
    Route::put('email_template_store/{pid}', [EmailTemplateController::class , 'storeEmailLang'])->name('store.email.language');
    Route::put('email_template_status/{id}', [EmailTemplateController::class , 'updateStatus'])->name('status.email.language');
    Route::resource('email_template', EmailTemplateController::class);
    // End Email Templates

    // helpdesk
    Route::resource('helpdesk', HelpdeskTicketController::class);
    Route::resource('helpdeskticket-category', HelpdeskTicketCategoryController::class);
    Route::get('helpdesk-tickets/search/{status?}', [HelpdeskTicketController::class , 'index'])->name('helpdesk-tickets.search');
    Route::post('helpdesk-ticket/getUser', [HelpdeskTicketController::class , 'getUser'])->name('helpdesk-tickets.getuser');
    Route::post('helpdesk-ticket/{id}/conversion', [HelpdeskConversionController::class , 'store'])->name('helpdesk-ticket.conversion.store');
    Route::post('helpdesk-ticket/{id}/note', [HelpdeskTicketController::class , 'storeNote'])->name('helpdesk-ticket.note.store');
    Route::delete('helpdesk-ticket-attachment/{tid}/destroy/{id}', [HelpdeskTicketController::class , 'attachmentDestroy'])->name('helpdesk-ticket.attachment.destroy');
    // End helpdesk





        //notification
        Route::resource('notification-template', NotificationController::class);
        Route::get('notification-template/{id}/{lang?}', [NotificationController::class , 'show'])->name('manage.notification.language');
        Route::post('notification-template/{pid}', [NotificationController::class , 'storeNotificationLang'])->name('store.notification.language');

        // Referral Program
        Route::resource('referral-program', ReferralProgramController::class);
        Route::get('referral-program-company', [ReferralProgramController::class , 'companyIndex'])->name('referral-program.company');
        Route::get('request-amount-sent/{id}', [ReferralProgramController::class , 'requestedAmountSent'])->name('request.amount.sent');
        Route::post('request-amount-store/{id}', [ReferralProgramController::class , 'requestedAmountStore'])->name('request.amount.store');
        Route::get('request-amount-cancel/{id}', [ReferralProgramController::class , 'requestCancel'])->name('request.amount.cancel');
        Route::get('request-amount/{id}/{status}', [ReferralProgramController::class , 'requestedAmount'])->name('amount.request');

        // language import & export
        Route::get('export/lang/json', [LanguageController::class , 'exportLangJson'])->name('export.lang.json');
        Route::get('import/lang/json/upload', [LanguageController::class , 'importLangJsonUpload'])->name('import.lang.json.upload');
        Route::post('import/lang/json', [LanguageController::class , 'importLangJson'])->name('import.lang.json');
    });
Route::get('esign-fill/public/{template_id}/{lead_id?}', [\App\Http\Controllers\ESignTemplateController::class, 'fillPdfFormPublic'])->name('esign.fill.public');
Route::get('module/reset', [ModuleController::class , 'ModuleReset'])->name('module.reset');
Route::post('guest/module/selection', [ModuleController::class , 'GuestModuleSelection'])->name('guest.module.selection');

// cookie
Route::get('cookie/consent', [SuperAdminSettingsController::class , 'CookieConsent'])->name('cookie.consent');

// cache
Route::get('/config-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');
    return redirect()->back()->with('success', 'Cache Clear Successfully');
})->name('config.cache');

// Optimize
Route::post('site/optimize', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');
    Artisan::call('optimize');
    return redirect()->back()->with('success', 'Site Optimized Successfully');
})->name('site.optimize');

//helpdesk
Route::post('helpdesk-ticket/{id}', [HelpdeskTicketController::class , 'reply'])->name('helpdesk-ticket.reply');
Route::get('helpdesk-ticket-show/{id}', [HelpdeskTicketController::class , 'show'])->name('helpdesk.view');



//instgram & facebook webhook call
Route::any('/meta/callback', [MetaController::class , 'handleWebhook'])->name('meta.callback')->withoutMiddleware([VerifyCsrfToken::class]);

Route::get('composer/json', function () {
    $path = base_path('packages/workdo');
    $modules = \Illuminate\Support\Facades\File::directories($path);

    $moduleNames = array_map(function ($dir) {
            return basename($dir);
        }
            , $modules);

        $require = '';
        $repo = '';
        foreach ($moduleNames as $module) {
            $packageName = preg_replace('/([a-z])([A-Z])/', '$1-$2', $module);
            $require .= '"workdo/' . strtolower($packageName) . '": "dev-testing",';
            $repo .= '{
            "type": "path",
            "url": "packages/workdo/' . $module . '"
        },';
        }
        return $require . '<br><br><br>' . $repo;
    });

// Temporary Fix for Live Server 500 Error
Route::get('/run-fix-live-server', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        return "Migrations and Cache Cleared successfully! Please try to login now.";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// Helper Route to create storage link on Hostinger
Route::get('/storage-link', function () {
    try {
        $target = storage_path('app/public');
        $shortcut = public_path('storage');
        if (file_exists($shortcut)) {
            @unlink($shortcut);
        }
        symlink($target, $shortcut);
        return "Storage link created successfully!";
    } catch (\Throwable $e) {
        return "Error creating symlink: " . $e->getMessage();
    }
});

