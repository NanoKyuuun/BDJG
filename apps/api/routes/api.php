<?php

use App\Domains\Audit\Controllers\Admin\AuditLogController;
use App\Domains\Billing\Controllers\Admin\InvoiceController;
use App\Domains\Billing\Controllers\ClientPortal\ClientInvoiceController;
use App\Domains\Catalog\Controllers\Admin\AddOnController;
use App\Domains\Catalog\Controllers\Admin\PackageController;
use App\Domains\Catalog\Controllers\Admin\ServiceController;
use App\Domains\Catalog\Controllers\Public\CatalogController;
use App\Domains\Clients\Controllers\Admin\ClientController;
use App\Domains\Clients\Controllers\ClientInvitationController;
use App\Domains\Clients\Controllers\ClientPortal\ClientProfileController;
use App\Domains\Commercial\Controllers\Admin\QuotationController;
use App\Domains\Commercial\Controllers\ClientPortal\ClientQuotationController;
use App\Domains\CRM\Controllers\Admin\InquiryController;
use App\Domains\CRM\Controllers\Public\PublicInquiryController;
use App\Domains\Dashboard\Controllers\AdminDashboardController;
use App\Domains\Delivery\Controllers\Admin\DeliveryPackageController as AdminDeliveryPackageController;
use App\Domains\Delivery\Controllers\ClientPortal\ClientDeliveryController;
use App\Domains\Media\Controllers\Admin\MediaAssetController as AdminMediaAssetController;
use App\Domains\Media\Controllers\ClientPortal\ClientMediaController;
use App\Domains\Media\Controllers\DirectStreamController;
use App\Domains\Media\Controllers\WorkerPortal\WorkerMediaController;
use App\Domains\Payments\Controllers\Admin\PaymentTransactionController;
use App\Domains\Payments\Controllers\ClientPortal\ClientPaymentController;
use App\Domains\Payments\Controllers\Webhooks\DuitkuWebhookController;
use App\Domains\Projects\Controllers\Admin\ProjectController;
use App\Domains\Projects\Controllers\ClientPortal\ClientProjectController;
use App\Domains\Projects\Controllers\WorkerPortal\WorkerProjectController;
use App\Domains\Revisions\Controllers\Admin\RevisionController as AdminRevisionController;
use App\Domains\Revisions\Controllers\ClientPortal\ClientRevisionController;
use App\Domains\Revisions\Controllers\WorkerPortal\WorkerRevisionController;
use App\Domains\Schedules\Controllers\Admin\ScheduleController;
use App\Domains\Tasks\Controllers\Admin\TaskController;
use App\Domains\Users\Controllers\Admin\UserManagementController;
use App\Domains\Workers\Controllers\Admin\ProjectAssignmentController;
use App\Domains\Workers\Controllers\Admin\WorkerProfileController;
use App\Http\Controllers\Api\V1\MeController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn (): JsonResponse => response()->json(['status' => 'ok']));

// Direct signed media stream & simulated direct upload
Route::put('/v1/media-upload/{public_id}', [DirectStreamController::class, 'directUpload'])->name('media.local-direct-upload');
Route::get('/v1/media-stream/{mediaAsset}', [DirectStreamController::class, 'stream'])->name('media.stream');

// Public Catalog, Lead Ingestion & Client Invitation
Route::prefix('v1')->group(function () {
    Route::prefix('catalog')->group(function () {
        Route::get('/services', [CatalogController::class, 'services']);
        Route::get('/packages', [CatalogController::class, 'packages']);
        Route::get('/add-ons', [CatalogController::class, 'addOns']);
    });

    Route::post('/inquiries', PublicInquiryController::class);

    Route::prefix('auth/invitations')->group(function () {
        Route::get('/{token}', [ClientInvitationController::class, 'show']);
        Route::post('/{token}/accept', [ClientInvitationController::class, 'accept']);
    });

    // Payment Webhook (unauthenticated, HMAC-SHA256 signature verified)
    Route::post('/webhooks/duitku', DuitkuWebhookController::class);
});

// Authenticated Endpoints
Route::middleware(['auth:sanctum', 'active.user'])->group(function () {
    Route::get('/v1/me', MeController::class);

    // Notifications Center
    Route::prefix('v1/notifications')->group(function () {
        Route::get('/', \App\Domains\Notifications\Controllers\NotificationController::class . '@index');
        Route::patch('/{id}/read', [\App\Domains\Notifications\Controllers\NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [\App\Domains\Notifications\Controllers\NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [\App\Domains\Notifications\Controllers\NotificationController::class, 'destroy']);
    });

    // Admin Operations (Restricted to OWNER and ADMIN roles)
    Route::prefix('v1/admin')->middleware('role:OWNER|ADMIN')->group(function () {
        // Dashboard Metrics
        Route::get('dashboard/metrics', [AdminDashboardController::class, 'metrics']);

        // Catalog
        Route::prefix('catalog')->group(function () {
            Route::apiResource('services', ServiceController::class);
            Route::apiResource('packages', PackageController::class);
            Route::apiResource('add-ons', AddOnController::class);
        });

        // Clients
        Route::post('clients/{client}/invite', [ClientInvitationController::class, 'invite']);
        Route::apiResource('clients', ClientController::class);

        // CRM / Inquiries
        Route::patch('inquiries/{inquiry}/status', [InquiryController::class, 'changeStatus']);
        Route::patch('inquiries/{inquiry}/assign', [InquiryController::class, 'assign']);
        Route::post('inquiries/{inquiry}/convert-to-client', [InquiryController::class, 'convertToClient']);
        Route::apiResource('inquiries', InquiryController::class);

        // Service Orders (Client Self-Service & Hybrid Ordering)
        Route::get('service-orders', [\App\Domains\Orders\Controllers\Admin\AdminServiceOrderController::class, 'index']);
        Route::get('service-orders/{serviceOrder}', [\App\Domains\Orders\Controllers\Admin\AdminServiceOrderController::class, 'show']);
        Route::post('service-orders/{serviceOrder}/request-information', [\App\Domains\Orders\Controllers\Admin\AdminServiceOrderController::class, 'requestInformation']);
        Route::post('service-orders/{serviceOrder}/attach-quotation', [\App\Domains\Orders\Controllers\Admin\AdminServiceOrderController::class, 'attachQuotation']);
        Route::post('service-orders/{serviceOrder}/confirm-availability', [\App\Domains\Orders\Controllers\Admin\AdminServiceOrderController::class, 'confirmAvailability']);
        Route::post('service-orders/{serviceOrder}/messages', [\App\Domains\Orders\Controllers\Admin\AdminServiceOrderController::class, 'sendMessage']);
        Route::post('service-orders/{serviceOrder}/cancel', [\App\Domains\Orders\Controllers\Admin\AdminServiceOrderController::class, 'cancel']);

        // Commercial / Quotations
        Route::post('quotations/{quotation}/versions', [QuotationController::class, 'createRevision']);
        Route::post('quotations/{quotation}/send', [QuotationController::class, 'send']);
        Route::post('quotations/{quotation}/generate-dp-invoice', [InvoiceController::class, 'generateDpInvoice']);
        Route::apiResource('quotations', QuotationController::class)->except(['update']);

        // Billing / Invoices
        Route::post('invoices/{invoice}/issue', [InvoiceController::class, 'issue']);
        Route::post('invoices/{invoice}/void', [InvoiceController::class, 'void']);
        Route::apiResource('invoices', InvoiceController::class)->except(['update']);

        // Payments
        Route::get('payments', [PaymentTransactionController::class, 'index']);
        Route::get('payments/{transaction}', [PaymentTransactionController::class, 'show']);
        Route::post('payments/{transaction}/check-status', [PaymentTransactionController::class, 'checkStatus']);

        // Projects
        Route::patch('projects/{project}/status', [ProjectController::class, 'changeStatus']);
        Route::get('projects/{project}/assignments', [ProjectAssignmentController::class, 'index']);
        Route::post('projects/{project}/assignments', [ProjectAssignmentController::class, 'assign']);
        Route::delete('projects/{project}/assignments/{assignment}', [ProjectAssignmentController::class, 'remove']);
        Route::apiResource('projects', ProjectController::class)->except(['update']);

        // Media Management
        Route::get('projects/{project}/media', [AdminMediaAssetController::class, 'index']);
        Route::post('media/upload-intent', [AdminMediaAssetController::class, 'uploadIntent']);
        Route::post('media/finalize-upload', [AdminMediaAssetController::class, 'finalizeUpload']);
        Route::get('media/{mediaAsset}', [AdminMediaAssetController::class, 'show']);
        Route::get('media/{mediaAsset}/signed-url', [AdminMediaAssetController::class, 'signedUrl']);
        Route::patch('media/{mediaAsset}/release', [AdminMediaAssetController::class, 'release']);
        Route::delete('media/{mediaAsset}', [AdminMediaAssetController::class, 'destroy']);

        // Revisions & Feedback
        Route::get('projects/{project}/revisions', [AdminRevisionController::class, 'index']);
        Route::post('projects/{project}/revisions', [AdminRevisionController::class, 'store']);
        Route::get('revisions/{revision}', [AdminRevisionController::class, 'show']);
        Route::post('revisions/{revision}/comments', [AdminRevisionController::class, 'addComment']);
        Route::post('revision-comments/{comment}/resolve', [AdminRevisionController::class, 'resolveComment']);

        // Final Deliveries Handover
        Route::get('projects/{project}/deliveries', [AdminDeliveryPackageController::class, 'index']);
        Route::post('projects/{project}/deliveries', [AdminDeliveryPackageController::class, 'store']);
        Route::get('deliveries/{deliveryPackage}', [AdminDeliveryPackageController::class, 'show']);
        Route::get('deliveries/{deliveryPackage}/download-url', [AdminDeliveryPackageController::class, 'downloadUrl']);
        Route::delete('deliveries/{deliveryPackage}', [AdminDeliveryPackageController::class, 'destroy']);

        // Workers
        Route::apiResource('workers', WorkerProfileController::class);

        // Tasks
        Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus']);
        Route::apiResource('tasks', TaskController::class)->except(['update']);

        // Schedules
        Route::apiResource('schedules', ScheduleController::class)->except(['update']);

        // Finance & Expenses
        Route::prefix('finance')->group(function () {
            Route::get('summary', [\App\Domains\Finance\Controllers\Admin\FinanceController::class, 'overallSummary']);
            Route::get('expenses', [\App\Domains\Finance\Controllers\Admin\FinanceController::class, 'expenses']);
            Route::patch('expenses/{expense}/approve', [\App\Domains\Finance\Controllers\Admin\FinanceController::class, 'approveExpense']);
            Route::patch('expenses/{expense}/reject', [\App\Domains\Finance\Controllers\Admin\FinanceController::class, 'rejectExpense']);
            Route::get('projects/{project}/profit', [\App\Domains\Finance\Controllers\Admin\FinanceController::class, 'projectProfitSummary']);
        });

        // System / Users & Roles & Activity Logs
        Route::prefix('system')->group(function () {
            Route::get('roles', [UserManagementController::class, 'roles']);
            Route::patch('users/{user}/status', [UserManagementController::class, 'updateStatus']);
            Route::apiResource('users', UserManagementController::class)->except(['destroy']);
            Route::get('activity', [AuditLogController::class, 'index']);
        });
    });

    // Worker Portal (Restricted to WORKER, OWNER, and ADMIN roles)
    Route::prefix('v1/worker')->middleware('role:WORKER|OWNER|ADMIN')->group(function () {
        Route::get('projects', [WorkerProjectController::class, 'index']);
        Route::get('projects/{project}', [WorkerProjectController::class, 'show']);
        Route::get('tasks', [WorkerProjectController::class, 'tasks']);
        Route::patch('tasks/{task}/status', [WorkerProjectController::class, 'updateTaskStatus']);
        Route::get('schedules', [WorkerProjectController::class, 'schedules']);

        // Worker Expenses
        Route::get('expenses', [\App\Domains\Finance\Controllers\WorkerPortal\WorkerExpenseController::class, 'index']);
        Route::post('expenses', [\App\Domains\Finance\Controllers\WorkerPortal\WorkerExpenseController::class, 'store']);
        Route::get('expenses/{expense}', [\App\Domains\Finance\Controllers\WorkerPortal\WorkerExpenseController::class, 'show']);

        // Worker Media
        Route::get('projects/{project}/media', [WorkerMediaController::class, 'index']);
        Route::post('media/upload-intent', [WorkerMediaController::class, 'uploadIntent']);
        Route::post('media/finalize-upload', [WorkerMediaController::class, 'finalizeUpload']);
        Route::get('media/{mediaAsset}/signed-url', [WorkerMediaController::class, 'signedUrl']);

        // Worker Revisions & Feedback
        Route::get('projects/{project}/revisions', [WorkerRevisionController::class, 'index']);
        Route::get('revisions/{revision}', [WorkerRevisionController::class, 'show']);
        Route::post('revisions/{revision}/comments', [WorkerRevisionController::class, 'addComment']);
        Route::post('revision-comments/{comment}/resolve', [WorkerRevisionController::class, 'resolveComment']);
    });

    // Client Portal (Restricted to CLIENT, OWNER, and ADMIN roles)
    Route::prefix('v1/client')->middleware('role:CLIENT|OWNER|ADMIN')->group(function () {
        Route::get('/profile', [ClientProfileController::class, 'show']);
        Route::put('/profile', [ClientProfileController::class, 'update']);

        // Client Catalog Browsing
        Route::get('/catalog/services', [\App\Domains\Orders\Controllers\Client\ClientCatalogController::class, 'services']);
        Route::get('/catalog/packages/{package}', [\App\Domains\Orders\Controllers\Client\ClientCatalogController::class, 'showPackage']);

        // Client Service Orders & Briefs
        Route::get('/service-orders', [\App\Domains\Orders\Controllers\Client\ClientServiceOrderController::class, 'index']);
        Route::post('/service-orders', [\App\Domains\Orders\Controllers\Client\ClientServiceOrderController::class, 'store']);
        Route::get('/service-orders/{serviceOrder}', [\App\Domains\Orders\Controllers\Client\ClientServiceOrderController::class, 'show']);
        Route::patch('/service-orders/{serviceOrder}/brief', [\App\Domains\Orders\Controllers\Client\ClientServiceOrderController::class, 'updateBrief']);
        Route::post('/service-orders/{serviceOrder}/submit', [\App\Domains\Orders\Controllers\Client\ClientServiceOrderController::class, 'submit']);
        Route::post('/service-orders/{serviceOrder}/attachments', [\App\Domains\Orders\Controllers\Client\ClientServiceOrderController::class, 'uploadAttachment']);
        Route::post('/service-orders/{serviceOrder}/messages', [\App\Domains\Orders\Controllers\Client\ClientServiceOrderController::class, 'sendMessage']);
        Route::post('/service-orders/{serviceOrder}/accept-quotation', [\App\Domains\Orders\Controllers\Client\ClientServiceOrderController::class, 'acceptQuotation']);
        Route::post('/service-orders/{serviceOrder}/request-quotation-revision', [\App\Domains\Orders\Controllers\Client\ClientServiceOrderController::class, 'requestQuotationRevision']);
        Route::post('/service-orders/{serviceOrder}/decline-quotation', [\App\Domains\Orders\Controllers\Client\ClientServiceOrderController::class, 'declineQuotation']);
        Route::post('/projects/{project}/reorder', [\App\Domains\Orders\Controllers\Client\ClientServiceOrderController::class, 'reorder']);

        // Quotations
        Route::get('/quotations', [ClientQuotationController::class, 'index']);
        Route::get('/quotations/{quotation}', [ClientQuotationController::class, 'show']);
        Route::post('/quotations/{quotation}/accept', [ClientQuotationController::class, 'accept']);
        Route::post('/quotations/{quotation}/request-revision', [ClientQuotationController::class, 'requestRevision']);
        Route::post('/quotations/{quotation}/decline', [ClientQuotationController::class, 'decline']);

        // Invoices
        Route::get('/invoices', [ClientInvoiceController::class, 'index']);
        Route::get('/invoices/{invoice}', [ClientInvoiceController::class, 'show']);
        Route::post('/invoices/{invoice}/pay', [ClientPaymentController::class, 'pay']);
        Route::get('/payments/{transaction}/status', [ClientPaymentController::class, 'status']);

        // Projects & Media
        Route::get('/projects', [ClientProjectController::class, 'index']);
        Route::get('/projects/{project}', [ClientProjectController::class, 'show']);
        Route::get('/projects/{project}/media', [ClientMediaController::class, 'index']);
        Route::get('/media/{mediaAsset}/signed-url', [ClientMediaController::class, 'signedUrl']);

        // Client Schedules & Itinerary
        Route::get('/schedules', [\App\Domains\Schedules\Controllers\ClientPortal\ClientScheduleController::class, 'index']);

        // Client Revisions & Feedback
        Route::get('/projects/{project}/revisions', [ClientRevisionController::class, 'index']);
        Route::post('/projects/{project}/revisions', [ClientRevisionController::class, 'store']);
        Route::get('/revisions/{revision}', [ClientRevisionController::class, 'show']);
        Route::post('/revisions/{revision}/comments', [ClientRevisionController::class, 'addComment']);

        // Client Deliveries
        Route::get('/projects/{project}/deliveries', [ClientDeliveryController::class, 'index']);
        Route::get('/deliveries/{deliveryPackage}', [ClientDeliveryController::class, 'show']);
        Route::get('/deliveries/{deliveryPackage}/download-url', [ClientDeliveryController::class, 'downloadUrl']);
    });
});
