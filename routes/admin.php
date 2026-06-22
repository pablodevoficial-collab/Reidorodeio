<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CompetitorRegistrationRequestController as AdminCompetitorRegistrationRequestController;
use App\Http\Controllers\Admin\ProfilePhotoApprovalController;

Route::any('{any?}', function () {
    return redirect()->route('home');
})->where('any', '.*');

// Rotas de autenticaÃ§Ã£o (admin)
Route::namespace('Auth')->group(function () {
    Route::middleware('admin.guest')->group(function () {
        Route::controller('LoginController')->group(function () {
            Route::get('/', 'showLoginForm')->name('login');
            Route::post('/', 'login')->name('login.submit');
            Route::get('logout', 'logout')->middleware('admin')->withoutMiddleware('admin.guest')->name('logout');
        });

        // Admin Password Reset
        Route::controller('ForgotPasswordController')->prefix('password')->name('password.')->group(function () {
            Route::get('reset', 'showLinkRequestForm')->name('reset');
            Route::post('reset', 'sendResetCodeEmail');
            Route::get('code-verify', 'codeVerify')->name('code.verify');
            Route::post('verify-code', 'verifyCode')->name('verify.code');
        });

        Route::controller('ResetPasswordController')->group(function () {
            Route::get('password/reset/{token}', 'showResetForm')->name('password.reset.form');
            Route::post('password/reset/change', 'reset')->name('password.change');
        });
    });
});

// Rotas administrativas (prefixo e name jÃ¡ aplicados em bootstrap/app.php)
Route::middleware(['admin', 'admin.permissions'])->group(function () {
    // Dashboard & Perfil/Admin utilitÃ¡rios
    Route::controller('AdminController')->group(function () {
        // Dashboard
        Route::get('dashboard', 'dashboard')->name('dashboard');

        // Perfil
        Route::get('profile', 'profile')->name('profile');
        Route::post('profile', 'profileUpdate')->name('profile.update');

        // Senha
        Route::get('password', 'password')->name('password');
        Route::post('password', 'passwordUpdate')->name('password.update');

        // NotificaÃ§Ãµes
        Route::get('notifications', 'notifications')->name('notifications');
        Route::get('notification/read/{id}', 'notificationRead')->name('notification.read');
        Route::get('notification/read-all', 'readAllNotification')->name('notification.read.all');
        Route::get('notification/delete-all', 'deleteAllNotification')->name('notification.delete.all');
        Route::get('notification/delete/{id}', 'deleteSingleNotification')->name('notification.delete');

        // Request & Report (menu: admin.request.report)
        Route::get('request-report', 'requestReport')->name('request.report');
        Route::post('request-report', 'reportSubmit')->name('request.report.submit');
    });

    // Painel do app mobile
    Route::controller('AppControlController')->prefix('app-control')->name('app_control.')->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/users', 'users')->name('users.index');
        Route::get('/users/{user}/edit', 'editUser')->name('users.edit');
        Route::put('/users/{user}', 'updateUser')->name('users.update');
        Route::get('/push', 'pushCenter')->name('push.index');
        Route::post('/push/send', 'sendPush')->name('push.send');
        Route::post('/push/firebase-config', 'updateFirebaseConfig')->name('push.firebase_config');
        Route::post('/push/service-account', 'uploadFirebaseServiceAccount')->name('push.service_account');
    });

    // AutomaÃ§Ã£o de odds por modalidade (caixa/lucro/boost)
    Route::controller('ModalidadeOddsController')->prefix('modalidade-odds')->name('modalidade_odds.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{modalidade}/edit', 'edit')->name('edit');
        Route::put('{modalidade}', 'update')->name('update');
    });

    // CRUD completo de Rodeios (admin)
    Route::resource('rodeios', 'RodeioController');
    Route::resource('sponsors', 'SponsorController')->except(['show']);

    // Alias legado opcional (se ainda houver chamadas admin.category.*)
    Route::controller('RodeioController')->prefix('category')->name('category.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('fetch-categories', 'fetchCategories')->name('fetch');
        Route::post('save-categories', 'saveFetchedCategories')->name('fetched.save');
        Route::post('store/{id?}', 'store')->name('store');
        Route::post('status/{id}', 'status')->name('status');
    });

    // Modalidades
    Route::controller('ModalidadeController')->prefix('modalidade')->name('modalidade.')->group(function () {
        Route::post('store', 'store')->name('store');
    });

    // League Manager
    Route::controller('LeagueController')->name('league.')->prefix('leagues')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('in-season', 'inSeason')->name('inseason');
        Route::get('in-season-enabled', 'inSeasonEnabled')->name('inseason.enabled');
        Route::get('in-season-disabled', 'inSeasonDisabled')->name('inseason.disabled');
        Route::get('api-enabled', 'apiEnabled')->name('api.enabled');
        Route::get('manual-enabled', 'manualEnabled')->name('manual.enabled');
        Route::post('store/{id?}', 'store')->name('store');
        Route::post('status/{id}', 'status')->name('status');
        Route::post('bulk/status/{type}/{ids}', 'bulkStatus')->name('bulk.status');
    });

    // CRUD completo de Modalidades (admin)
    Route::resource('modalidades', 'ModalidadeController');

    // Modalidades - GestÃ£o de Competidores (AJAX)
    Route::get('modalidades/{modalidade}/competitors', 'ModalidadeController@competitors')->name('modalidades.competitors');
    Route::post('modalidades/{modalidade}/competitors/attach', 'ModalidadeController@attachCompetitors')->name('modalidades.competitors.attach');
    Route::get('modalidades/{modalidade}/competitors/popout', 'ModalidadeController@competitorsPopout')->name('modalidades.competitors.popout');
    Route::get('modalidades/{modalidade}/groups', 'ModalidadeController@groups')->name('modalidades.groups');
    Route::post('modalidades/{modalidade}/groups', 'ModalidadeController@storeGroup')->name('modalidades.groups.store');
    Route::post('modalidades/{modalidade}/groups/json', 'ModalidadeController@storeGroupJson')->name('modalidades.groups.store_json');
    Route::patch('modalidades/{modalidade}/groups/{group}/divisao', 'ModalidadeController@updateGroupDivisao')->name('modalidades.groups.update_divisao');
    Route::delete('modalidades/{modalidade}/groups/{group}', 'ModalidadeController@destroyGroup')->name('modalidades.groups.destroy');

    // Pausar/Despausar X1 para modalidades
    Route::patch('modalidades/{modalidade}/toggle-pause-x1', 'ModalidadeController@togglePauseX1')->name('modalidades.toggle_pause_x1');

    // Competitors Manager - Rei do Rodeio
    Route::controller('CompetitorController')->name('competitors.')->prefix('competitors')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('{competitor}', 'show')->name('show');
        Route::get('edit/{competitor}', 'edit')->name('edit');
        Route::put('update/{competitor}', 'update')->name('update');
        Route::delete('destroy/{competitor}', 'destroy')->name('destroy');
        Route::post('stats/{competitor}', 'updateStats')->name('stats');
    });

    Route::controller(AdminCompetitorRegistrationRequestController::class)->name('competitors.requests.')->prefix('competitors/requests')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('{requestModel}/approve', 'approve')->name('approve');
        Route::post('{requestModel}/reject', 'reject')->name('reject');
    });

    // Interface de TransmissÃ£o Ao Vivo (Fase 4.1)
    Route::controller('LiveTransmissionController')->name('live_transmission.')->prefix('live-transmission')->group(function () {
        Route::get('/', 'index')->name('index');
    Route::get('modalidades-by-rodeio', 'getModalidadesByRodeio')->name('modalidades_by_rodeio');
        Route::post('event-status', 'updateEventStatus')->name('event_status');
        Route::get('transmission-data', 'getTransmissionData')->name('data');
        Route::post('stream-url', 'saveStreamUrl')->name('stream_url');
        Route::post('save-modalidade', 'saveCurrentModalidade')->name('save_modalidade');
        Route::post('add-score', 'addScore')->name('add_score');
        Route::post('disqualify-competitor', 'disqualifyCompetitor')->name('disqualify_competitor');
        Route::post('mark-competitor-out', 'markCompetitorOut')->name('mark_competitor_out');
        Route::post('can-undo-last-score', 'canUndoLastScore')->name('can_undo_last_score');
        Route::post('undo-last-score', 'undoLastScore')->name('undo_last_score');
    Route::post('finish-modalidade', 'finishModalidade')->name('finish_modalidade');
        Route::post('finalize-classificatoria', 'finalizeClassificatoria')->name('finalize_classificatoria');
        Route::post('check-unfinalized-classificatoria', 'checkUnfinalizedClassificatoria')->name('check_unfinalized');
        Route::post('finalize-divisao', 'finalizeDivisao')->name('finalize_divisao');
        Route::get('viewers-count', 'getViewersCount')->name('viewers');
        Route::post('operation-log', 'addOperationLog')->name('log');
        Route::get('competitors-stats', 'getCompetitorsStats')->name('competitors_stats');
        Route::post('update-competitor-stats', 'updateCompetitorStats')->name('update_competitor_stats');
        Route::get('competitor-scoring-history', 'getCompetitorScoringHistory')->name('competitor_scoring_history');
        Route::get('competitor-stats-summary', 'getCompetitorStatsSummary')->name('competitor_stats_summary');
    });

    // Admin: X1 / Duelo management
    Route::controller('X1AdminController')->name('x1.')->prefix('x1')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{room}', 'show')->name('show');
        Route::post('close/{room}', 'close')->name('close');
        Route::delete('{room}', 'destroy')->name('destroy');
        Route::get('{room}/participants', 'participants')->name('participants');
        Route::post('{room}/mark-prize-paid', 'markPrizePaid')->name('mark-prize-paid');
    });

    // Menu EstatÃ­sticas de Competidores
    Route::controller('CompetitorStatsController')->name('competitor_stats.')->prefix('competitor-stats')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{competitor}', 'show')->name('show');
    });

    // Sistema de SeleÃ§Ã£o DinÃ¢mica (Fase 4.2)
    Route::controller('DynamicSelectionController')->name('dynamic_selection.')->prefix('dynamic-selection')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('competitors', 'getCompetitors')->name('competitors');
        Route::get('search-competitors', 'searchCompetitors')->name('search_competitors');
        Route::get('competitors/{competitor}/details', 'getCompetitorDetails')->name('competitor_details');
        Route::patch('bulk-status', 'bulkStatusUpdate')->name('bulk_status');
        Route::patch('modalidades/{modalidade}/competitors/{competitor}/position', 'updateCompetitorPosition')->name('update_position');
        Route::get('modalidades/{modalidade}/stats', 'getModalidadeStats')->name('modalidade_stats');
        Route::get('real-time-updates', 'getRealTimeUpdates')->name('real_time_updates');
    });

    // Sistema de PontuaÃ§Ã£o RÃ¡pida (Fase 5.1)
    Route::controller('QuickScoringController')->name('quick_scoring.')->prefix('quick-scoring')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('competitors', 'getCompetitors')->name('competitors');
        Route::post('score', 'applyScore')->name('score');
        Route::post('custom-score', 'applyCustomScore')->name('custom_score');
        Route::post('undo', 'undoScore')->name('undo');
        Route::post('disconnect-websockets', 'disconnectWebSockets')->name('disconnect_websockets');
        Route::get('check-updates', 'checkForUpdates')->name('check_updates');
        Route::get('stats', 'getModalidadeStats')->name('stats');
        Route::get('ranking', 'getRanking')->name('ranking');
        Route::get('export', 'exportResults')->name('export');
    });

    // Sistema de Filas (Substituto do WebSocket)
    Route::controller('QueueController')->name('queues.')->prefix('queues')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('status', 'getStatus')->name('status');
        Route::post('start-worker', 'startWorker')->name('start_worker');
        Route::post('pause-worker', 'pauseWorker')->name('pause_worker');
        Route::post('clear', 'clearJobs')->name('clear');
        Route::post('test-job', 'dispatchTestJob')->name('test_job');
    });

    // AnÃºncios (Banners)
    Route::controller('AdsController')->name('ads.')->prefix('ads')->group(function () {
        Route::get('banners', 'banners')->name('banners');
        Route::post('store', 'store')->name('store');
        Route::post('update/{id}', 'update')->name('update');
        Route::post('delete/{id}', 'delete')->name('delete');
    });

    // Affiliate Management
    Route::controller('AffiliateManagementController')->prefix('affiliates')->name('affiliates.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('dashboard', 'dashboard')->name('dashboard');
        Route::get('{id}', 'show')->name('show');
        Route::post('mark-paid/{id}', 'markAsPaid')->name('mark_paid');
        Route::post('toggle-status/{id}', 'toggleStatus')->name('toggle_status');
        Route::post('approve-commission/{id}', 'approveCommission')->name('approve_commission');
        Route::post('withdrawal/{id}', 'processWithdrawal')->name('withdrawal.process');
    });

    // Loja / produtos enviados por clientes
    Route::controller('StoreProductSubmissionController')->prefix('store-submissions')->name('store_submissions.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('{submission}/review', 'review')->name('review');
    });

    // MÃ³dulo desabilitado por requisito legal do projeto

    // Sistema de Fantasy Leagues (Fase 6.2)
    Route::controller('FantasyLeagueController')->name('fantasy_leagues.')->prefix('fantasy-leagues')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('entries', 'entries')->name('entries');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{fantasyLeague}/edit', 'edit')->name('edit');
        Route::put('{fantasyLeague}', 'update')->name('update');
        Route::delete('{fantasyLeague}', 'destroy')->name('destroy');
        Route::post('{fantasyLeague}/toggle-status', 'toggleStatus')->name('toggle_status');
        // FinalizaÃ§Ã£o da liga
        Route::get('{fantasyLeague}/preview-finalize', 'previewFinalize')->name('preview_finalize');
        Route::post('{fantasyLeague}/finalize', 'finalize')->name('finalize');
        Route::post('{fantasyLeague}/finalize-api', 'finalizeApi')->name('finalize_api');
    });

    Route::controller('FantasyPrizeController')->name('fantasy_prizes.')->prefix('fantasy-prizes')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('{fantasyTeam}/mark-paid', 'markPaid')->name('mark_paid');
    });

    // MÃ³dulo desabilitado por requisito legal do projeto

    // Rodeios - Modalidades API
    Route::get('rodeios/{rodeio}/modalidades', function(App\Models\Rodeio $rodeio) {
        return response()->json($rodeio->modalidades()->select('id', 'nome')->get());
    })->name('rodeios.modalidades');

    // MÃ³dulo desabilitado por requisito legal do projeto

    // MÃ³dulo desabilitado por requisito legal do projeto

    // MÃ³dulo desabilitado por requisito legal do projeto

    // MÃ³dulo desabilitado por requisito legal do projeto

    // MÃ³dulo desabilitado por requisito legal do projeto

    // Users Manager
    Route::controller('ManageUsersController')->name('users.')->prefix('users')->group(function () {
        Route::get('/', 'allUsers')->name('all');
        
        // ExportaÃ§Ã£o de Dados
        Route::get('export', 'exportInputs')->name('export');
        Route::post('export', 'exportDownload')->name('export.download');

        Route::get('email-verified', 'emailVerifiedUsers')->name('email.verified');
        Route::get('email-unverified', 'emailUnverifiedUsers')->name('email.unverified');
        Route::get('mobile-unverified', 'mobileUnverifiedUsers')->name('mobile.unverified');
        Route::get('kyc-unverified', 'kycUnverifiedUsers')->name('kyc.unverified');
        Route::get('kyc-pending', 'kycPendingUsers')->name('kyc.pending');
        Route::get('mobile-verified', 'mobileVerifiedUsers')->name('mobile.verified');


        Route::get('detail/{id}', 'detail')->name('detail');
        Route::get('kyc-data/{id}', 'kycDetails')->name('kyc.details');
        Route::post('kyc-approve/{id}', 'kycApprove')->name('kyc.approve');
        Route::post('kyc-reject/{id}', 'kycReject')->name('kyc.reject');
        Route::post('update/{id}', 'update')->name('update');
        Route::post('notify/{id}', 'sendNotificationSingle')->name('notify.single');

        Route::get('login/{id}', 'login')->name('login');
        Route::post('status/{id}', 'status')->name('status');


        Route::get('list', 'list')->name('list');
        Route::get('count-by-segment/{methodName}', 'countBySegment')->name('segment.count');
        Route::get('notification-log/{id}', 'notificationLog')->name('notification.log');
    });

    Route::controller(ProfilePhotoApprovalController::class)->name('users.profile_photos.')->prefix('users/profile-photos')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('{profilePhotoRequest}/approve', 'approve')->name('approve');
        Route::post('{profilePhotoRequest}/reject', 'reject')->name('reject');
    });

    // Bot Management (SECRET)
    Route::controller('BotManagementController')->name('users.bots.')->prefix('users/bots')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('generate-x1', 'generateX1')->name('generate.x1');
        Route::post('generate-fantasy', 'generateFantasy')->name('generate.fantasy');
        Route::post('clear', 'clearAll')->name('clear');
        Route::post('upload', 'uploadBotsFile')->name('upload');
        Route::get('leagues', 'getAvailableLeagues')->name('leagues');
        Route::post('populate', 'populateLeagueWithBots')->name('populate');
        Route::post('remove-bots', 'removeBotsFromLeague')->name('remove.bots');
    });

    // Subscriber
    Route::controller('SubscriberController')->prefix('subscriber')->name('subscriber.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('send-email', 'sendEmailForm')->name('send.email');
        Route::post('remove/{id}', 'remove')->name('remove');
        Route::post('send-email', 'sendEmail')->name('send.email.submit');
    });

    // Report
    Route::controller('ReportController')->prefix('report')->name('report.')->group(function () {
        Route::get('login/history', 'loginHistory')->name('login.history');
        Route::get('login/ipHistory/{ip}', 'loginIpHistory')->name('login.ipHistory');
        Route::get('notification/history', 'notificationHistory')->name('notification.history');
        Route::get('email/detail/{id}', 'emailDetails')->name('email.details');
    });

    // Admin Support
    Route::controller('SupportTicketController')->prefix('ticket')->name('ticket.')->group(function () {
        Route::get('/', 'tickets')->name('index');
        Route::get('pending', 'pendingTicket')->name('pending');
        Route::get('closed', 'closedTicket')->name('closed');
        Route::get('answered', 'answeredTicket')->name('answered');
        Route::get('view/{id}', 'ticketReply')->name('view');
        Route::post('reply/{id}', 'replyTicket')->name('reply');
        Route::post('close/{id}', 'closeTicket')->name('close');
        Route::get('download/{attachment_id}', 'ticketDownload')->name('download');
        Route::post('delete/{id}', 'ticketDelete')->name('delete');
    });

    // Language Manager
    Route::controller('LanguageController')->prefix('language')->name('language.')->group(function () {
        Route::get('/', 'langManage')->name('manage');
        Route::post('/', 'langStore')->name('manage.store');
        Route::post('delete/{id}', 'langDelete')->name('manage.delete');
        Route::post('update/{id}', 'langUpdate')->name('manage.update');
        Route::get('edit/{id}', 'langEdit')->name('key');
        Route::post('import', 'langImport')->name('import.lang');
        Route::post('store/key/{id}', 'storeLanguageJson')->name('store.key');
        Route::post('delete/key/{id}', 'deleteLanguageJson')->name('delete.key');
        Route::post('update/key/{id}', 'updateLanguageJson')->name('update.key');
        Route::get('get-keys', 'getKeys')->name('get.key');
    });

    Route::controller('GeneralSettingController')->group(function () {

        Route::get('system-setting', 'systemSetting')->name('setting.system');

        // General Setting
        Route::get('general-setting', 'general')->name('setting.general');
        Route::post('general-setting', 'generalUpdate');

        Route::get('setting/social/credentials', 'socialiteCredentials')->name('setting.socialite.credentials');
        Route::post('setting/social/credentials/update/{key}', 'updateSocialiteCredential')->name('setting.socialite.credentials.update');
        Route::post('setting/social/credentials/status/{key}', 'updateSocialiteCredentialStatus')->name('setting.socialite.credentials.status.update');

        //configuration
        Route::get('setting/system-configuration', 'systemConfiguration')->name('setting.system.configuration');
        Route::post('setting/system-configuration', 'systemConfigurationSubmit');

        // Logo-Icon
        Route::get('setting/logo-icon', 'logoIcon')->name('setting.logo.icon');
        Route::post('setting/logo-icon', 'logoIconUpdate')->name('setting.logo.icon.update');

        //Custom CSS
        Route::get('custom-css', 'customCss')->name('setting.custom.css');
        Route::post('custom-css', 'customCssSubmit');

        //Custom CSS
        Route::get('sitemap', 'sitemap')->name('setting.sitemap');
        Route::post('sitemap', 'sitemapSubmit');

        //Custom CSS
        Route::get('robot', 'robot')->name('setting.robot');
        Route::post('robot', 'robotSubmit');

        //Cookie
        Route::get('cookie', 'cookie')->name('setting.cookie');
        Route::post('cookie', 'cookieSubmit');

        //maintenance_mode
        Route::get('maintenance-mode', 'maintenanceMode')->name('maintenance.mode');
        Route::post('maintenance-mode', 'maintenanceModeSubmit');
    });

    Route::controller('CronConfigurationController')->name('cron.')->prefix('cron')->group(function () {
        Route::get('index', 'cronJobs')->name('index');
        Route::get('schedule/logs/{id}', 'scheduleLogs')->name('schedule.logs');
        Route::post('schedule/log/resolved/{id}', 'scheduleLogResolved')->name('schedule.log.resolved');
        Route::post('schedule/log/flush/{id}', 'logFlush')->name('log.flush');
    });

    // System Commands (para executar comandos Artisan via painel)
    Route::controller('SystemCommandsController')->prefix('system')->name('system.')->group(function () {
        Route::get('commands', 'index')->name('commands');
        Route::post('warmup', 'warmupCache')->name('warmup');
        Route::post('rankings', 'updateRankings')->name('rankings');
        Route::post('payments', 'processPayments')->name('payments');
        Route::post('clear', 'clearCache')->name('clear');
        Route::post('clean-x1', 'cleanExpiredRooms')->name('clean-x1');
    });

    //KYC setting
    Route::controller('KycController')->group(function () {
        Route::get('kyc-setting', 'setting')->name('kyc.setting');
        Route::post('kyc-setting', 'settingUpdate');
    });

    //Notification Setting
    Route::name('setting.notification.')->controller('NotificationController')->prefix('notification')->group(function () {
        //Template Setting
        Route::get('global/email', 'globalEmail')->name('global.email');
        Route::post('global/email/update', 'globalEmailUpdate')->name('global.email.update');

        Route::get('global/sms', 'globalSms')->name('global.sms');
        Route::post('global/sms/update', 'globalSmsUpdate')->name('global.sms.update');

        Route::get('global/push', 'globalPush')->name('global.push');
        Route::post('global/push/update', 'globalPushUpdate')->name('global.push.update');

        Route::get('templates', 'templates')->name('templates');
        Route::get('template/edit/{type}/{id}', 'templateEdit')->name('template.edit');
        Route::post('template/update/{type}/{id}', 'templateUpdate')->name('template.update');
        Route::get('template/send/{id}', 'templateSend')->name('template.send');
        Route::get('rodeio-reminders', 'rodeioRemindersIndex')->name('rodeio.reminders.index');
        Route::get('rodeio-reminders/{rodeioId}', 'rodeioRemindersShow')->name('rodeio.reminders.show');

        //Email Setting
        Route::get('email/setting', 'emailSetting')->name('email');
        Route::post('email/setting', 'emailSettingUpdate');
        Route::post('email/test', 'emailTest')->name('email.test');

        //SMS Setting
        Route::get('sms/setting', 'smsSetting')->name('sms');
        Route::post('sms/setting', 'smsSettingUpdate');
        Route::post('sms/test', 'smsTest')->name('sms.test');

        Route::get('notification/push/setting', 'pushSetting')->name('push');
        Route::post('notification/push/setting', 'pushSettingUpdate');
        Route::post('notification/push/setting/upload', 'pushSettingUpload')->name('push.upload');
        Route::get('notification/push/setting/download', 'pushSettingDownload')->name('push.download');
        
        // Mass Notify
        Route::get('notify', 'notify')->name('notify');
        Route::post('notify', 'notifySend')->name('notify.send');
    });

    // Plugin
    Route::controller('ExtensionController')->prefix('extensions')->name('extensions.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('update/{id}', 'update')->name('update');
        Route::post('status/{id}', 'status')->name('status');
    });

    //System Information
    Route::controller('SystemController')->name('system.')->prefix('system')->group(function () {
        Route::get('info', 'systemInfo')->name('info');
        Route::get('server-info', 'systemServerInfo')->name('server.info');
        Route::get('optimize', 'optimize')->name('optimize');
        Route::get('optimize-clear', 'optimizeClear')->name('optimize.clear');
        Route::get('system-update', 'systemUpdate')->name('update');
        Route::post('system-update', 'systemUpdateProcess')->name('update.process');
        Route::get('system-update/log', 'systemUpdateLog')->name('update.log');
    });

    // SEO
    Route::get('seo', 'FrontendController@seoEdit')->name('seo');

    // Frontend
    Route::name('frontend.')->prefix('frontend')->group(function () {

        Route::controller('FrontendController')->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('templates', 'templates')->name('templates');
            Route::post('templates', 'templatesActive')->name('templates.active');
            Route::get('frontend-sections/{key?}', 'frontendSections')->name('sections');
            Route::post('frontend-content/{key}', 'frontendContent')->name('sections.content');
            Route::get('frontend-element/{key}/{id?}', 'frontendElement')->name('sections.element');
            Route::get('frontend-slug-check/{key}/{id?}', 'frontendElementSlugCheck')->name('sections.element.slug.check');
            Route::get('frontend-element-seo/{key}/{id}', 'frontendSeo')->name('sections.element.seo');
            Route::post('frontend-element-seo/{key}/{id}', 'frontendSeoUpdate');
            Route::post('remove/{id}', 'remove')->name('remove');
        });
    });

});
