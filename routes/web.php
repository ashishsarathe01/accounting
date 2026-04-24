<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\company\CompanyController;
use App\Http\Controllers\company\OwnerController;
use App\Http\Controllers\company\ShareholderController;
use App\Http\Controllers\company\BankController;
use App\Http\Controllers\heading\AccountHeadingController;
use App\Http\Controllers\group\AccountGroupsController;
use App\Http\Controllers\unit\UnitsController;
use App\Http\Controllers\itemgroup\ItemGroupsController;
use App\Http\Controllers\manageitem\ManageItemsController;
use App\Http\Controllers\billsundry\BillSundrysController;
use App\Http\Controllers\account\AccountsController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SalesReturnController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\journal\JournalController;
use App\Http\Controllers\receipt\ReceiptController;
use App\Http\Controllers\contra\ContraController;
use App\Http\Controllers\AjaxController;
use App\Http\Controllers\payment\PaymentController;
use App\Http\Controllers\dashboard\GstSettingController;
use App\Http\Controllers\display\BalanceSheetController;
use App\Http\Controllers\display\ProfitLossController;
use App\Http\Controllers\display\TrialBalanceController;
use App\Http\Controllers\accountledger\AccountLedgerController;
use App\Http\Controllers\ItemLedgerController;
use App\Http\Controllers\display\CurrentLiabilitiesController;
use App\Http\Controllers\display\DutyTaxController;
use App\Http\Controllers\display\DisplayCgstController;
use App\Http\Controllers\MerchantEmployeeController;
use App\Http\Controllers\AdminModuleController\DashboradController;
use App\Http\Controllers\AdminModuleController\MerchantController;
use App\Http\Controllers\manageitem\ItemParameterizedController;
use App\Http\Controllers\CreditNoteWithoutItemController;
use App\Http\Controllers\DebitNoteWithoutItemController;
use App\Http\Controllers\AdminModuleController\AccountHeadController;
use App\Http\Controllers\AdminModuleController\AccountGroupController;
use App\Http\Controllers\AdminModuleController\AccountController;
use App\Http\Controllers\AdminModuleController\MerchantPrivilegesController;
use App\Http\Controllers\AdminModuleController\MerchantModulePermissionController;
use App\Http\Controllers\VoucherSeriesConfigurationController;
use App\Http\Controllers\StockTransfer\StockTransferController;
use App\Http\Controllers\gstReturn\gstR1Controller;
use App\Http\Controllers\gstReturn\GstDetailController;
use App\Http\Controllers\gstReturn\GSTR2BController;
use App\Http\Controllers\gstReturn\GSTR2AController;
use App\Http\Controllers\gstReturn\GSTR3BController;
use App\Http\Controllers\ParameterizedStockController;
use App\Http\Controllers\saleOrder\SaleOrderController;
use App\Http\Controllers\Supplier\SupplierController;
use App\Http\Controllers\Supplier\SupplierRateLocationWiseController;
use App\Http\Controllers\Supplier\SupplierPurchaseController;
use App\Http\Controllers\Supplier\SupplierSubHeadController;
use App\Http\Controllers\AdminModuleController\ManageUserController;
use App\Http\Controllers\AdminModuleController\AdminPrivilegesController;
use App\Http\Controllers\deal\DealController;
use App\Http\Controllers\Supplier\FuelSupplierController;
use App\Http\Controllers\Supplier\SparePartController;
use App\Http\Controllers\SaleRegisterController;
use App\Http\Controllers\PurchaseRegisterController;
use App\Http\Controllers\production\ProductionController;
use App\Http\Controllers\consumption\ConsumptionController;
use App\Http\Controllers\ReceivablePayable\ReceivableController;
use App\Http\Controllers\ReceivablePayable\PayableController;
use App\Http\Controllers\production\ReelLedgerController;
use App\Http\Controllers\ReceivablePayable\OverdueAggregationController;
use App\Http\Controllers\ActivityLog\ActivityLogController;
use App\Http\Controllers\BusinessActivityLogs\BusinessActivityLogController;
use App\Http\Controllers\PurchaseConfiguration\PurchaseConfigurationController;
use App\Http\Controllers\PayrollConfiguration\PayrollConfigurationController;
use App\Http\Controllers\JobWork\JobWorkController;
use App\Http\Controllers\JobWorkIn\JobWorkControllerIn;
use App\Http\Controllers\JobWorkStockJournal\JobWorkStockJournalController;
use App\Http\Controllers\JobWorkLedger\JobWorkLedgerController;

use App\Http\Controllers\AccountProduction\AccountProductionController;
use App\Http\Controllers\payroll\MainController;
use App\Http\Controllers\payroll\EsicController;
use App\Http\Controllers\payroll\PfController;
use App\Http\Controllers\TaskManager\TaskManagerController;
use App\Http\Controllers\AccountSummary\AccountSummaryController;
use App\Http\Controllers\ItemSummary\ItemSummaryController;
use App\Http\Controllers\AdminModuleController\AttendanceTypeController;
use App\Http\Controllers\AttendanceManagement\AttendanceManagementController;
use App\Http\Controllers\AdminModuleController\DatabaseBackupController;
use App\Http\Controllers\TransactionReport\TransactionReportController;
use App\Http\Controllers\DbBackup\BackUpController;
use App\Http\Controllers\AdminModuleController\MechantDatabaseBackupController;
use App\Http\Controllers\SparePartLifeChart\SparePartLifeChartController;
use App\Http\Controllers\TransactionIntegrity\TransactionIntegrityController;
use App\Http\Controllers\payroll\PayrollHeadController;
use App\Http\Controllers\AdminModuleController\TdsSectionController;
use App\Http\Controllers\BoxManagement\PartyItemRateController;
use App\Http\Controllers\payroll\PayrollRegisterController;
use App\Http\Controllers\LandingPage\LandingController;
use App\Http\Controllers\display\TestController;
use App\Http\Controllers\DaraReport\DaraReportController;
use App\Http\Controllers\Retail\ManageRateController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
/*Route::get('/', function () {
    return view('welcome');
});*/
Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
//Route::prefix('admin')->name('admin.')->group(function(){
   Route::get('/',[AdminAuthController::class, 'index'])->name('password.login');
   Route::post('/login',[AdminAuthController::class, 'adminLogin'])->name('login');
   Route::group(['middleware' => ['adminAuth']], function() {
      Route::get('/dashboard',[DashboradController::class, 'index'])->name('dashboard');
      Route::get('/logout',[AdminAuthController::class, 'logout'])->name('logout');
      Route::get('/change-password-view',[AdminAuthController::class, 'changePasswordView'])->name('change-password-view');
      Route::post('/change-password-update',[AdminAuthController::class, 'changePasswordUpdate'])->name('change-password-update');
      Route::Resource('/merchant',MerchantController::class)->name('*','merchant');
      Route::Resource('/account-head',AccountHeadController::class)->name('*','account-head');
      Route::Resource('/account-group',AccountGroupController::class)->name('*','account-group');
      Route::Resource('/account',AccountController::class)->name('*','account');
      Route::post('/login-merchant', [MerchantController::class, 'loginMerchant']);
      Route::get('/activate-merchant', [MerchantController::class, 'activateMerchant'])->name('activate.merchant');
      Route::post('/update-merchant-status', [MerchantController::class, 'updateMerchantStatus'])->name('update.merchant.status');
      Route::get('/merchant-module-privileges/{id}',[MerchantController::class, 'merchantPrivileges'])->name('merchant-module-privileges');
      Route::post('/set-merchant-module-privileges',[MerchantController::class, 'setMerchantPrivileges'])->name('set-merchant-module-privileges');
      Route::Resource('/merchant-privilege',MerchantPrivilegesController::class)->name('*','merchant-privilege');
      Route::get('/merchant-module-permission/{merchant_id?}/{company_id?}',[MerchantModulePermissionController::class, 'index'])->name('merchant-module-permission');
      Route::post('/store-merchant-module',[MerchantModulePermissionController::class, 'storeMerchantModule'])->name('store-merchant-module');
      Route::resource('manageUser', ManageUserController::class);
      Route::get('manage-users', [ManageUserController::class,'index'])->name('manageUser.index');
      Route::get('manage-users/create', [ManageUserController::class,'create'])->name('manageUser.create');
      Route::post('manage-users/store', [ManageUserController::class,'store'])->name('manageUser.store');
      Route::get('manage-users/{id}/edit', [ManageUserController::class,'edit'])->name('manageUser.edit');
      Route::put('manage-users/{id}/update', [ManageUserController::class,'update'])->name('manageUser.update');
      Route::delete('manage-users/{id}', [ManageUserController::class, 'destroy'])->name('manageUser.destroy');
      Route::get('manage-users/{id}/privileges', [ManageUserController::class,'privileges'])->name('manageUser.privileges');
      Route::post('manage-users/set-privileges', [ManageUserController::class,'setUserPrivileges'])->name('manageUser.setUserPrivileges');
      Route::get('manage-users/{id}/assign-companies', [ManageUserController::class, 'assignCompanies'])->name('manageUser.assignCompanies');
      Route::post('manage-users/assign-companies', [ManageUserController::class, 'storeAssignCompanies'])->name('manageUser.storeAssignCompanies');
      Route::resource('/admin-privilege', AdminPrivilegesController::class)->name('*','admin-privilege');
      Route::get('manage-users/{id}/admin-privileges', [ManageUserController::class, 'adminPrivileges'])->name('manageUser.adminPrivileges');
      Route::post('manage-users/{id}/admin-privileges/save', [ManageUserController::class, 'saveAdminPrivileges'])->name('manageUser.saveAdminPrivileges');
      Route::get('/database-backup', [DatabaseBackupController::class, 'index'])->name('database.backup.index');
      Route::get('/database-backup/download', [DatabaseBackupController::class, 'download'])->name('database.backup.download');
      
        Route::get('merchant/backup/create/{company_id}/{status:?}',[MechantDatabaseBackupController::class,'createBackup']);
        Route::get('merchant/backup/list/{user_id}/{company_id:?}',[MechantDatabaseBackupController::class,'backupList']);
        Route::post('merchant/backup/restore/{id}',[MechantDatabaseBackupController::class,'restoreBackup']);
        Route::post('merchant/backup/delete/{id}',[MechantDatabaseBackupController::class,'deleteBackup']);
        
        Route::get('/tds-section', [TdsSectionController::class, 'index'])
            ->name('tds.index');
        Route::get('/tds-section/create', [TdsSectionController::class, 'create'])
            ->name('tds.create');
        
        Route::post('/tds-section/store', [TdsSectionController::class, 'store'])
            ->name('tds.store');
            Route::get('/tds-section/edit/{id}', [TdsSectionController::class, 'edit'])
            ->name('tds.edit');
        
        Route::post('/tds-section/update/{id}', [TdsSectionController::class, 'update'])
            ->name('tds.update');
            Route::post('/tds-section/delete', [TdsSectionController::class, 'delete'])
            ->name('tds.delete');

 });
});


// Route::get('/', [LandingController::class, 'welcome'])->name('welcome');
// Route::get('/about', [LandingController::class, 'about'])->name('about');
// Route::get('/contact', [LandingController::class, 'ContactUs'])->name('ContactUs');
// Route::get('/pricing', [LandingController::class, 'pricing'])->name('pricing');
// Route::get('/features', [LandingController::class, 'features'])->name('features');
// Route::post('/contact-us', [ContactUsController::class, 'store'])->name('contact.store');
// Route::get('/login', [AuthController::class, 'index'])->name('password.login');
// Route::get('password-login', [AuthController::class, 'index'])->name('password.login');
// Route::get('forgot-password', [AuthController::class, 'forgotPassword'])->name('password.forgot');
// Route::post('forgot-otp', [AuthController::class, 'forgotOtp'])->name('forgot.otp');
// Route::post('forgot-otp-login', [AuthController::class, 'changePassword'])->name('submit.forgototp');
// Route::get('otp-login', [AuthController::class, 'otpLogin'])->name('otp.login');


Route::get('/', [LandingController::class, 'welcome'])->name('home');
	Route::get('/welcome', [LandingController::class, 'welcome'])->name('welcome');
	Route::get('/about', [LandingController::class, 'about'])->name('about');
	Route::get('/contact', [LandingController::class, 'ContactUs'])->name('ContactUs');
	Route::get('/pricing', [LandingController::class, 'pricing'])->name('pricing');
	Route::get('/features', [LandingController::class, 'features'])->name('features');
	Route::post('/contact-us', [ContactUsController::class, 'store'])->name('contact.store');
	// Auth (merchant)
	Route::get('login', [AuthController::class, 'index'])->name('login');
	Route::get('password-login', [AuthController::class, 'index'])->name('password.login');
	Route::get('forgot-password', [AuthController::class, 'forgotPassword'])->name('password.forgot');
	Route::post('forgot-otp', [AuthController::class, 'forgotOtp'])->name('forgot.otp');
	Route::post('forgot-otp-login', [AuthController::class, 'changePassword'])->name('submit.forgototp');
	Route::get('otp-login', [AuthController::class, 'otpLogin'])->name('otp.login');
	
Route::post('otp-generate', [AuthController::class, 'generate'])->name('otp.generate');
Route::post('submit-otp-login', [AuthController::class, 'loginWithOtp'])->name('submit.otplogin');
Route::post('password-login-check', [AuthController::class, 'passwordLoginCheck'])->name('password.login-check');
Route::get('registration', [AuthController::class, 'registration'])->name('register.user');
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('send-otp', [AuthController::class, 'sendOtp'])->name('send-otp');
Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->name('verify-otp');
Route::post('change-otp-verify-status', [AuthController::class, 'changeOtpVerifyStatus'])->name('change-otp-verify-status');
Route::post('change-password', [AuthController::class, 'submitChangePassword'])->name('password.changepassword');
Route::group(['middleware' => ['merchantloginstatus']], function () {
   Route::get('change-password-view', [AuthController::class, 'changePasswordView'])->name('change-password-view');
   Route::post('change-password-update', [AuthController::class, 'changePasswordUpdate'])->name('change-password-update');
   Route::get('dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
   Route::post('change-company', [AuthController::class, 'changeCompany'])->name('company.change');
   Route::get('logout', [AuthController::class, 'logout'])->name('logout');
   Route::get('view-company', [CompanyController::class, 'viewCompany'])->name('view-company');
   Route::get('/ajax/manage-financial-year', [CompanyController::class, 'manageFinancialYear'])->name('ajax.manage-financial-year');
   Route::post('change-financial-year', [CompanyController::class, 'changeDefaultFY'])->name('change-financial-year');
   Route::get('company-listing', [CompanyController::class, 'companyListing'])->name('company-listing');
   Route::get('add-company', [CompanyController::class, 'addCompany'])->name('add-company');
   Route::post('check-gst', [CompanyController::class, 'checkGst'])->name('check-gst');
   Route::post('submit-add-company', [CompanyController::class, 'submitAddCompany'])->name('submit-add-company');
   Route::post('submit-edit-company', [CompanyController::class, 'submitEditCompany'])->name('submit-edit-company');
   Route::get('add-owner', [OwnerController::class, 'addOwner'])->name('add-owner');
   Route::post('submit-add-owner', [OwnerController::class, 'submitAddOwner'])->name('submit-add-owner');
   Route::post('submit-delete-owner', [OwnerController::class, 'submitDeleteOwner'])->name('submit-delete-owner');
   Route::get('add-shareholder', [ShareholderController::class, 'addShareholder'])->name('add-shareholder');
   Route::post('submit-add-shareholder', [ShareholderController::class, 'submitAddShareholder'])->name('submit-add-shareholder');
   Route::get('add-bank', [BankController::class, 'addBank'])->name('add-bank');
   Route::post('submit-add-bank', [BankController::class, 'submitAddBank'])->name('submit-add-bank');
   Route::post('delete-bank', [BankController::class, 'deleteBank'])->name('delete-bank');
   Route::get('company-edit', [CompanyController::class, 'companyEdit'])->name('company.company-edit');
   Route::get('owner-edit/{id}', [OwnerController::class, 'ownerEdit'])->name('owner.owner-edit');
   Route::get('shareholder-edit/{id}', [ShareholderController::class, 'shareholderEdit'])->name('shareholder.shareholder-edit');
   Route::get('bank-edit/{id}', [BankController::class, 'bankEdit'])->name('shareholder.shareholder-edit');
   Route::get('view-owner/{id}', [OwnerController::class, 'viewOwner'])->name('view-owner');
   Route::get('view-shareholder/{id}', [ShareholderController::class, 'viewShareholder'])->name('view-shareholder');
   Route::get('view-bank/{id}', [BankController::class, 'viewBank'])->name('view-bank');
   Route::post('submit-edit-owner', [OwnerController::class, 'update'])->name('submit-edit-owner.update');
   Route::post('submit-edit-shareholder', [ShareholderController::class, 'update'])->name('submit-edit-shareholder.update');
   Route::post('submit-edit-bank', [BankController::class, 'update'])->name('submit-edit-bank.update');
   
   //AccountHeading CRUD
   Route::Resource('account', AccountsController::class)->except(['show']);
   Route::get('account/datatable', [AccountsController::class, 'datatable'])->name('account.datatable');
   Route::get('add-account', [AccountsController::class, 'addAccount']);
   Route::post('account-update', [AccountsController::class, 'update'])->name('account.update');
   Route::post('account-delete', [AccountsController::class, 'delete'])->name('account.delete');
   Route::get('import-account-view', [AccountsController::class, 'importAccountView'])->name('import-account-view');
   Route::post('import-account-process', [AccountsController::class, 'importAccountProcess'])->name('import-account-process');
   //Route::post('import-account-process', [AccountsController::class, 'importAccountUpdateProcess'])->name('import-account-process');
   Route::Resource('account-heading', AccountHeadingController::class);
   Route::post('account-heading-update', [AccountHeadingController::class, 'update'])->name('account-heading.update');
   Route::post('account-heading-delete', [AccountHeadingController::class, 'delete'])->name('account-heading.delete');
   Route::Resource('account-group', AccountGroupsController::class);
   Route::post('account-group-update', [AccountGroupsController::class, 'update'])->name('account-group.update');
   Route::post('account-group-delete', [AccountGroupsController::class, 'delete'])->name('account-group.delete');
   Route::Resource('account-unit', UnitsController::class);
   Route::post('account-unit-update', [UnitsController::class, 'update'])->name('account-unit.update');
   Route::post('account-unit-delete', [UnitsController::class, 'delete'])->name('account-unit.delete');
   Route::Resource('account-item-group', ItemGroupsController::class);
   Route::post('account-item-group-update', [ItemGroupsController::class, 'update'])->name('account-item-group.update');
   Route::post('account-item-group-delete', [ItemGroupsController::class, 'delete'])->name('account-item-group.delete');
   Route::get(
    'account-manage-item/datatable',
    [ManageItemsController::class,'datatable']
    )->name('account-manage-item.datatable');
   Route::Resource('account-manage-item', ManageItemsController::class);
   Route::post('account-manage-item-update', [ManageItemsController::class, 'update'])->name('account-manage-item.update');
   Route::get('stock-journal', [ManageItemsController::class, 'stockJournal'])->name('stock-journal');
   Route::get('add-stock-journal', [ManageItemsController::class, 'addStockJournal'])->name('add-stock-journal');
   Route::post('save-stock-journal', [ManageItemsController::class, 'saveStockJournal'])->name('save-stock-journal');
   Route::post('delete-stock-journal', [ManageItemsController::class, 'deleteStockJournal'])->name('delete-stock-journal');
   Route::get('edit-stock-journal/{id}', [ManageItemsController::class, 'editStockJournal'])->name('edit-stock-journal');
   Route::post('update-stock-journal', [ManageItemsController::class, 'updateStockJournal'])->name('update-stock-journal');
   Route::post('account-manage-item-delete', [ManageItemsController::class, 'delete'])->name('account-manage-item.delete');
   Route::get('item-import-view', [ManageItemsController::class, 'itemImportView'])->name('item-import-view');
   Route::post('item-import-process', [ManageItemsController::class, 'itemImportProcess'])->name('item-import-process');
      Route::get('itemledger-update-all', [ItemLedgerController::class, 'recalculateStock'])->name('recalculate.stock');

   Route::Resource('account-bill-sundry', BillSundrysController::class);
   Route::post('account-bill-sundry-update', [BillSundrysController::class, 'update'])->name('account-bill-sundry.update');
   Route::post('account-bill-sundry-delete', [BillSundrysController::class, 'delete'])->name('account-bill-sundry.delete');
   Route::post('/purchase/tally-export', [PurchaseController::class, 'purchaseTallyExport'])->name('purchase-tally-export');
    Route::post('/purchase/bulk-roundoff-update', [PurchaseController::class, 'bulkUpdateRoundOff'])
    ->name('purchase.bulk.roundoff');
    
    Route::get('/purchase/roundoff-progress', [PurchaseController::class, 'roundoffProgress']);
    
   Route::Resource('purchase', PurchaseController::class);
   Route::post('purchase-update', [PurchaseController::class, 'update'])->name('purchase.update');
   Route::post('purchase-delete', [PurchaseController::class, 'delete'])->name('purchase.delete');
   Route::get('purchase-invoice/{id:?}', [PurchaseController::class, 'purchaseInvoice'])->name('purchase-invoice');
   Route::get('purchase-edit/{id}', [PurchaseController::class, 'purchaseEdit']);
   Route::get('purchase-import-view', [PurchaseController::class, 'purchaseImportView'])->name('purchase-import-view');
   Route::post('purchase-import-process', [PurchaseController::class, 'purchaseImportProcess'])->name('purchase-import-process');
   Route::post('account-manage-item-store', [SalesController::class, 'manageItemStore'])->name('account-manage-item-store');
   Route::get('/get-item-cost', [SalesController::class, 'getLatestCost']);
   Route::post('account-store', [SalesController::class, 'addAccountStore'])->name('account-store');
   Route::Resource('sale', SalesController::class);
   Route::get('edit-sale/{id}', [SalesController::class, 'edit']);
   Route::post('sale-update', [SalesController::class, 'update'])->name('sale.update');
   Route::post('sale-delete', [SalesController::class, 'delete'])->name('sale.delete');
   Route::get('sale-invoice/{id:?}', [SalesController::class, 'saleInvoice'])->name('sale-invoice');
   Route::get('sale-invoice-preview', [SalesController::class, 'saleInvoicePreview'])->name('sale-invoice-preview');
   Route::get('sale-import-view', [SalesController::class, 'saleImportView'])->name('sale-import-view');
   Route::post('sale-import-process', [SalesController::class, 'saleImportProcess'])->name('sale-import-process');
   Route::get('/sale-invoice/pdf/{id}', [SalesController::class, 'saleInvoicePdf']);
   Route::get('account-group-import-view', [AccountGroupsController::class, 'accountGroupImportView'])->name('account-group-import-view');
   Route::post('account-group-import-process', [AccountGroupsController::class, 'accountGroupImportProcess'])->name('account-group-import-process');
    Route::get('/sale-return/export', [SalesReturnController::class, 'exportView'])->name('sale-return-export-view');

    Route::post('/sale-return/export', [SalesReturnController::class, 'export'])->name('sale-return-export');
   Route::Resource('sale-return', SalesReturnController::class);
   Route::get('sale-return-invoice/{id:?}', [SalesReturnController::class, 'saleReturnInvoice'])->name('sale-return-invoice');
   Route::get('sale-return-without-item-invoice/{id}', [SalesReturnController::class, 'saleReturnWithoutItemInvoice']);
   Route::get('sale-return-without-gst-invoice/{id}', [SalesReturnController::class, 'saleReturnWithoutGstInvoice']);
   Route::get('sale-return-edit/{id}', [SalesReturnController::class, 'edit']);
   Route::post('sale-return-update', [SalesReturnController::class, 'update'])->name('sale-return-update');
   Route::post('sale-return-delete', [SalesReturnController::class, 'delete'])->name('sale-return.delete');
   Route::post('generate-credit-note-einvoice', [SalesReturnController::class, 'generateEinvoice'])->name('generate-credit-note-einvoice');
   Route::post('generate-credit-note-without-item-einvoice', [SalesReturnController::class, 'generateEinvoiceWithoutItem'])->name('generate-credit-note-without-item-einvoice');
   Route::post('generate-ewaybill-sale-return', [SalesReturnController::class, 'generateEinvoice'])->name('generateEwaybillSaleReturn');
   Route::post('cancel-einvoice-sale-return', [SalesReturnController::class, 'generateEinvoice'])->name('cancelEinvoiceSaleReturn');
   Route::post('cancel-ewaybill-sale-return', [SalesReturnController::class, 'cancelEwaybillSaleReturn'])->name('cancel-ewaybill-sale-return');
   Route::get('/purchase-return/export',[PurchaseReturnController::class, 'exportView'])->name('purchase-return-export-view');

Route::post('/purchase-return/export',[PurchaseReturnController::class, 'export'])->name('purchase-return-export');
   Route::Resource('purchase-return', PurchaseReturnController::class);
   Route::post('purchase-return-update', [PurchaseReturnController::class, 'update'])->name('purchase-return-update');
   Route::post('purchase-return-delete', [PurchaseReturnController::class, 'delete'])->name('purchase-return.delete');
   Route::get('purchase-return-invoice/{id:?}', [PurchaseReturnController::class, 'purchaseReturnInvoice'])->name('purchase-return-invoice');
   Route::get('purchase-return-without-item-invoice/{id}', [PurchaseReturnController::class, 'purchaseReturnWithoutItemInvoice']);
   Route::get('purchase-return-without-gst-invoice/{id}', [PurchaseReturnController::class, 'purchaseReturnWithoutGstInvoice']);
   Route::get('purchase-return-vehicle-entry-detail/{id}', [PurchaseReturnController::class, 'purchaseReturnVehicleEntryDetail']);
   Route::get('purchase-return-edit/{id}', [PurchaseReturnController::class, 'edit']);
   Route::post('generate-debit-note-einvoice', [PurchaseReturnController::class, 'generateEinvoice'])->name('generate-debit-note-einvoice');
   Route::post('generate-debit-note-without-item-einvoice', [PurchaseReturnController::class, 'generateEinvoiceWithoutItem'])->name('generate-debit-note-without-item-einvoice');
   Route::post('generate-ewaybill-purchase-return', [PurchaseReturnController::class, 'generateEwaybillPurchaseReturn'])->name('generate-ewaybill-purchase-return');
   Route::post('cancel-einvoice-purchase-return', [PurchaseReturnController::class, 'cancelEinvoicePurchaseReturn'])->name('cancel-einvoice-purchase-return');
   Route::post('cancel-ewaybill-purchase-return', [PurchaseReturnController::class, 'cancelEwaybillPurchaseReturn'])->name('cancel-ewaybill-purchase-return');
   Route::Resource('payment', PaymentController::class);
   Route::post('payment-update', [PaymentController::class, 'update'])->name('payment.update');
   Route::post('payment-delete', [PaymentController::class, 'delete'])->name('payment.delete');
   Route::get('payment-import-view', [PaymentController::class, 'paymentImportView'])->name('payment-import-view');
   Route::post('payment-import-process', [PaymentController::class, 'paymentImportProcess'])->name('payment-import-process');
   Route::Resource('receipt', ReceiptController::class);
   Route::post('receipt-update', [ReceiptController::class, 'update'])->name('receipt.update');
   Route::post('receipt-delete', [ReceiptController::class, 'delete'])->name('receipt.delete');
   Route::get('receipt-import-view', [ReceiptController::class, 'receiptImportView'])->name('receipt-import-view');
   Route::post('receipt-import-process', [ReceiptController::class, 'receiptImportProcess'])->name('receipt-import-process');
   Route::Resource('journal', JournalController::class);
   Route::post('journal-update', [JournalController::class, 'update'])->name('journal.update');
   Route::post('journal-delete', [JournalController::class, 'delete'])->name('journal.delete');
   Route::get('journal-import-view', [JournalController::class, 'journalImportView'])->name('journal-import-view');
   Route::post('journal-import-process', [JournalController::class, 'journalImportProcess'])->name('journal-import-process');
   Route::get('/contra/export',[ContraController::class,'exportView'])->name('contra-export-view');
   Route::post('/contra/export',[ContraController::class,'export'])->name('contra-export');
   Route::Resource('contra', ContraController::class);
   Route::post('contra-update', [ContraController::class, 'update'])->name('contra.update');
   Route::post('contra-delete', [ContraController::class, 'delete'])->name('contra.delete');
   Route::get('contra-import-view', [ContraController::class, 'contraImportView'])->name('contra-import-view');
   Route::post('contra-import-process', [ContraController::class, 'contraImportProcess'])->name('contra-import-process');
   Route::Resource('gst-setting', GstSettingController::class);
   Route::post('gst-setting-update', [GstSettingController::class, 'update'])->name('gst-setting.update');
   Route::post('gst-setting-delete', [GstSettingController::class, 'delete'])->name('gst-setting.delete');
   Route::Resource('balancesheet', BalanceSheetController::class);
   Route::post('balancesheet-update', [BalanceSheetController::class, 'update'])->name('balancesheet.update');
   Route::post('balancesheet-delete', [BalanceSheetController::class, 'delete'])->name('balancesheet.delete');
   Route::get('balancesheet-filter', [BalanceSheetController::class, 'filter'])->name('balancesheet.filter');
   Route::get('group-balance-by-head/{id}/{from_date}/{to_date}', [BalanceSheetController::class, 'groupBalanceByHead']);
   Route::get('account-balance-by-group/bs/{id}/{from_date}/{to_date}/{type}', [BalanceSheetController::class, 'accountBalanceByGroup']);
   Route::Resource('profitloss', ProfitLossController::class);
   Route::post('profitloss-update', [ProfitLossController::class, 'update'])->name('profitloss.update');
   Route::post('profitloss-delete', [ProfitLossController::class, 'delete'])->name('profitloss.delete');
   Route::get('profitloss-filter', [ProfitLossController::class, 'filter'])->name('profitloss.filter');
   Route::get('sale-by-month/{financial_year:?}/{from_date?}/{to_date?}/', [ProfitLossController::class, 'saleByMonth'])->name('sale-by-month');
   Route::get('sale-by-month-detail/{financial_year:?}/{from_date}/{to_date}/{search_type:?}', [ProfitLossController::class, 'saleByMonthDetail'])->name('sale-by-month-detail');
//   Route::get('purchase-by-month/{financial_year:?}', [ProfitLossController::class, 'purchaseByMonth'])->name('purchase-by-month');
   Route::get(
    'purchase-by-month/{financial_year}/{from_date}/{to_date}',
    [ProfitLossController::class, 'purchaseByMonth']
)->name('purchase-by-month');
   Route::get('purchase-by-month-detail/{financial_year:?}/{from_date}/{to_date}/{search_type:?}', [ProfitLossController::class, 'purchaseByMonthDetail'])->name('purchase-by-month-detail');
   Route::get('purchase-by-month-detail-transit/{financial_year:?}/{from_date}/{to_date}', [ProfitLossController::class, 'purchaseByMonthDetailInTransit'])->name('purchase-by-month-detail-transit');
   Route::get('purchase-by-month-detail-transit-opening/{financial_year:?}/{from_date}/{to_date}', [ProfitLossController::class, 'purchaseByMonthDetailInTransitOpening'])->name('purchase-by-month-detail-transit-opening');
   Route::get('account-balance-by-group/{id}/{financial_year}/{from_date}/{to_date}', [ProfitLossController::class, 'accountBalanceByGroup']);
   Route::get('account-monthly-summary/{id}/{financial_year}', [ProfitLossController::class, 'accountMonthlySummary']);
   Route::Resource('trialbalance', TrialBalanceController::class);
   Route::post('trialbalance-update', [TrialBalanceController::class, 'update'])->name('trialbalance.update');
   Route::post('trialbalance-delete', [TrialBalanceController::class, 'delete'])->name('trialbalance.delete');
   Route::get('trialbalance-filter', [TrialBalanceController::class, 'filter'])->name('trialbalance.filter');
   Route::Resource('accountledger', AccountLedgerController::class);
   Route::post('accountledger-update', [AccountLedgerController::class, 'update'])->name('accountledger.update');
   Route::post('accountledger-delete', [AccountLedgerController::class, 'delete'])->name('accountledger.delete');
   Route::get('accountledger-filter', [AccountLedgerController::class, 'filter'])->name('accountledger.filter');
   Route::get('/ledger/export-pdf', [AccountLedgerController::class, 'exportPdf'])->name('ledger.export.pdf');
   Route::get('/ledger/export-csv', [AccountLedgerController::class, 'exportCsv'])->name('ledger.export.csv');
   Route::Resource('itemledger', ItemLedgerController::class);
   Route::post('itemledger-update', [ItemLedgerController::class, 'update'])->name('itemledger.update');
   Route::post('itemledger-delete', [ItemLedgerController::class, 'delete'])->name('itemledger.delete');
   Route::get('itemledger-filter', [ItemLedgerController::class, 'filter'])->name('itemledger.filter');
   Route::get('item-ledger-average', [ItemLedgerController::class, 'itemLedgerAverage'])->name('item-ledger-average');
   Route::get('item-ledger-average-by-godown', [ItemLedgerController::class, 'itemLedgerAverageByGodown'])->name('item-ledger-average-by-godown');
   Route::Resource('currentliabilities', CurrentLiabilitiesController::class);
   Route::post('currentliabilities-update', [CurrentLiabilitiesController::class, 'update'])->name('currentliabilities.update');
   Route::post('currentliabilities-delete', [CurrentLiabilitiesController::class, 'delete'])->name('currentliabilities.delete');
   Route::Resource('dutytax', DutyTaxController::class);
   Route::post('dutytax-update', [DutyTaxController::class, 'update'])->name('dutytax.update');
   Route::post('dutytax-delete', [DutyTaxController::class, 'delete'])->name('dutytax.delete');
   Route::Resource('displaycgst', DisplayCgstController::class);
   Route::post('displaycgst-update', [DisplayCgstController::class, 'update'])->name('displaycgst.update');
   Route::post('displaycgst-delete', [DisplayCgstController::class, 'delete'])->name('displaycgst.delete');
   //User Management
   Route::Resource('manage-merchant-employee', MerchantEmployeeController::class);
   Route::get('merchant-employee-privileges/{id}',[MerchantEmployeeController::class,'employeePrivileges']);
   Route::post('set-employee-privileges',[MerchantEmployeeController::class,'setEmployeePrivileges'])->name('set-employee-privileges');
   Route::get('parameterized-configuration',[ItemParameterizedController::class,'index'])->name('parameterized-configuration');
   Route::post('store-parameterized-configuration',[ItemParameterizedController::class,'storeParameterizedConfiguration'])->name('store-parameterized-configuration');
   Route::get('voucher-series-configuration',[VoucherSeriesConfigurationController::class,'index'])->name('voucher-series-configuration');
   Route::post('add-series-configuration',[VoucherSeriesConfigurationController::class,'addSeriesConfiguration'])->name('add-series-configuration');
   Route::post('series-configuration-by-series',[VoucherSeriesConfigurationController::class,'seriesConfigurationBySeries'])->name('series-configuration-by-series');
   Route::get('sale-invoice-configuration',[SalesController::class,'saleInvoiceConfiguration'])->name('sale-invoice-configuration');
   Route::post('add-sale-invoice-configuration',[SalesController::class,'addSaleInvoiceConfiguration'])->name('add-sale-invoice-configuration');
   Route::Resource('credit-note-without-item', CreditNoteWithoutItemController::class);
   Route::Resource('debit-note-without-item', DebitNoteWithoutItemController::class);
   
   
   Route::Resource('stock-transfer', StockTransferController::class);
   //Route::get('login', [AuthController::class, 'index'])->name('otp.login');
   //Route::get('/', 'AuthController@index')->name('index');
   //Route::post('dashboard', [AuthController::class, 'dashboard']); 
   //Route::post('/', [AuthController::class, 'index'])->name('login');
   /*Route::get('login', [AuthController::class, 'index'])->name('login');
   Route::get('password-login', [AuthController::class, 'passwordLogin'])->name('password.login');
   Route::post('custom-login', [AuthController::class, 'customLogin'])->name('login.custom'); 

   Route::post('custom-registration', [AuthController::class, 'customRegistration'])->name('register.custom'); 
   Route::get('signout', [AuthController::class, 'signOut'])->name('signout');*/

   // Ajax requests
   Route::post('check-debit-credit-note-voucherno', [AjaxController::class, 'checkDebitCreditNoteVoucherno']);
   Route::post('get/items/details', [AjaxController::class, 'getItemDetails']);
   Route::post('get/billsundry/details', [AjaxController::class, 'getBillSundryDetails']);
   Route::post('get/accounts/details', [AjaxController::class, 'getAccountsDetails']);
   Route::post('get/invoice/details', [AjaxController::class, 'getInvoiceDetails']);
   Route::post('get/saleitems/details', [AjaxController::class, 'getSaleItemsDetails']);
   Route::post('get/purchaseitems/details', [AjaxController::class, 'getPurchaseItemsDetails']);
   Route::post('get/purchaseinvoice/details', [AjaxController::class, 'getPurchaseInvoiceDetails']);
   Route::post('set-redircet-url', [AjaxController::class, 'setRedircetUrl']);
   Route::post('set-primary-bank', [AjaxController::class, 'setPrimaryBank']);
   Route::post('get-party-list', [AjaxController::class, 'getPartyList']);
   Route::post('get-item-list', [AjaxController::class, 'getItemList']);
   Route::post('check-account-name', [AjaxController::class, 'checkAccountName']);
   Route::post('check-gstin', [AjaxController::class, 'checkGstin']);
   Route::post('update-item-stock', [AjaxController::class, 'updateItemStock']);
   Route::post('get-next-invoiceno', [AjaxController::class, 'getNextInvoiceno']);
   Route::post('get-next-salereturnno', [AjaxController::class, 'getNextSaleReturnno']);
   Route::post('get-next-purchasereturnno', [AjaxController::class, 'getNextPurchaseReturnno']);
   Route::post('get-parameter-data', [AjaxController::class, 'getParameterData']);
   Route::post('get-item-parameter', [AjaxController::class, 'getItemParameter']);
   Route::post('generate-einvoice', [SalesController::class, 'generateEinvoice']);
   Route::post('generate-ewaybill', [SalesController::class, 'generateEwayBill']);
   Route::post('cancel-einvoice', [SalesController::class, 'cancelEinvoice']);
   Route::post('cancel-ewaybill', [SalesController::class, 'cancelEwayBill']);
   Route::post('get-item-size-quantity', [SalesController::class, 'getItemSizeQuantity']);
   
   Route::post('get-item-average-details', [ItemLedgerController::class, 'itemAverageDetails']);
   Route::get('/gstr1', [gstR1Controller::class, 'gstmain'])->name('gstr1');
   Route::get('/gst/b2c-normal-statewise', [gstR1Controller::class, 'B2Cstatewise'])->name('gst.b2c.normal.statewise');
   Route::get('/gst/b2b-detailed-billwise', [gstR1Controller::class, 'B2Bdetailed'])->name('gst.b2b.detailed.billwise');
   Route::get('/gst/b2c-large-detailed', [gstR1Controller::class, 'b2cLargedetailed'])->name('gst.b2c.large.detailed');
   Route::get('/report/filter', [gstR1Controller::class, 'filterform'])->name('report.filter.data');
   Route::get('gst2a', [GSTR2AController::class, 'index'])->name('gst2a');
   Route::post('gstr2a-detail', [GSTR2AController::class, 'gstr2aDetail'])->name('gstr2a-detail');
   Route::get('gstr2a-all-info/{month}/{gstin}/{ctin}', [GSTR2AController::class, 'gstr2aAllInfo'])->name('gstr2a-all-info');
   Route::get('parameterized-stock', [ParameterizedStockController::class,'index'])->name('parameterized-stock');  
   Route::get('gst2b', [GSTR2BController::class, 'index'])->name('gst2b');  
   Route::post('gstr2b-detail', [GSTR2BController::class, 'gstr2bDetail'])->name('gstr2b-detail');
   Route::get('gstr2b-all-info/{month}/{gstin}/{ctin}', [GSTR2BController::class, 'gstr2bAllInfo'])->name('gstr2b-all-info');
   Route::post('reject-gstr2b-entry', [GSTR2BController::class, 'rejectEntry'])->name('reject-gstr2b-entry');
   Route::post('get-unlinked-cdnr', [GSTR2BController::class, 'getUnlinkedCdnr'])->name('get-unlinked-cdnr');
   Route::post('link-cdnr', [GSTR2BController::class, 'linkCdnr'])->name('link-cdnr');
   Route::post('get-unlink-invoice-entry', [GSTR2BController::class, 'getUnlinkInvoiceEntry'])->name('get-unlink-invoice-entry');
   Route::post('link-gstr2b-invoice-entry', [GSTR2BController::class, 'linkGstr2bInvoiceEntry'])->name('link-gstr2b-invoice-entry');
   Route::get('gstr2b-reconciliation-data/{month}/{gstin}', [GSTR2BController::class, 'gstr2bReconciliationData'])->name('gstr2b-reconciliation-data');
   Route::post('gstr2b-reconciliation-detail', [GSTR2BController::class, 'gstr2bReconciliationDetail'])->name('gstr2b-reconciliation-detail');
   Route::post('accept-gstr2b-entry', [GSTR2BController::class, 'acceptGstr2bEntry'])->name('accept-gstr2b-entry');
   Route::post('verify-gst-token-otp', [AjaxController::class, 'verifyGstTokenOtp'])->name('verify-gst-token-otp');
   Route::get('/report/nilratedreginter', [gstR1Controller::class, 'nilRatedAndExemptedCombined'])->name('nilratedreginter');
   Route::get('/report/debitnote', [gstR1Controller::class, 'combinedNoteRegister'])->name('debitNote');
   Route::get('/report/debitnote/Unreg', [gstR1Controller::class, 'combinedNoteUnreegister'])->name('debitNoteUnreg');
   //  Route::get('/report/debitnote/state', [gstR1Controller::class, 'B2Cstatewise1'])->name('state');
   Route::get('/report/hsn', [gstR1Controller::class, 'hsnSummary'])->name('hsnSummary');
   Route::get('/document-summary', [gstR1Controller::class, 'documentIssuedSummary'])->name('docIssued');
   Route::get('/gstr1/view', [GstR1Controller::class, 'showView'])->name('gstr1.view');
   Route::post('/gstr1-detail', [GstR1Controller::class, 'gstr1Detail'])->name('gstr1-detail');
   Route::get('/gstr3b/view', [GSTR3BController::class, 'index'])->name('gstr3B.view');
   Route::get('/report/filter/3b', [GSTR3BController::class, 'filterform'])->name('report.filter.data.3b');
   Route::get('/gstr3b/view/outwarddetails', [GSTR3BController::class, 'OutwardDetails'])->name('OutwardDetails.view');
   Route::get('/gstr3b/view/itcdetails', [GSTR3BController::class, 'itcDetails'])->name('itcDetails.view');
   Route::get('/settings', [SettingsController::class, 'index'])->name('viewSettings');
   Route::post('/gstr1/send-to-gstmaster', [Gstr1Controller::class, 'sendGstr1ToGSTMaster'])->name('gstr1.send');
   Route::get('debit-note-import-view', [PurchaseReturnController::class, 'debitNoteImportView'])->name('debit-note-import-view');
   Route::post('debit-note-import-process', [PurchaseReturnController::class, 'debitNoteImportProcess'])->name('debit-note-import-process');
   Route::get('credit-note-import-view', [SalesReturnController::class, 'creditNoteImportView'])->name('credit-note-import-view');
   Route::post('credit-note-import-process', [SalesReturnController::class, 'creditNoteImportProcess'])->name('credit-note-import-process');
   Route::get('import-stock-transfer-view', [StockTransferController::class, 'importStockTransferView'])->name('import-stock-transfer-view');
   Route::post('import-stock-transfer-process', [StockTransferController::class, 'importStockTransferProcess'])->name('import-stock-transfer-process');
   Route::Resource('supplier', SupplierController::class);
   Route::post('get-supplier-location', [SupplierController::class, 'getSupplierLocation'])->name('get-supplier-location');
   Route::post('store-supplier-location', [SupplierController::class, 'storeSupplierLocation'])->name('store-supplier-location');
   //Route::get('manage-supplier-rate/{date?}', [SupplierRateLocationWiseController::class, 'manageSupplierRate'])->name('manage-supplier-rate');
   Route::POST('store-supplier-rate', [SupplierRateLocationWiseController::class, 'storeSupplierRate'])->name('store-supplier-rate');
   Route::get('manage-supplier-purchase', [SupplierPurchaseController::class, 'manageSupplierPurchase'])->name('manage-supplier-purchase');
   Route::post('get-supplier-rate-by-location', [SupplierPurchaseController::class, 'getSupplierRateByLocation'])->name('get-supplier-rate-by-location');
   ('manage-supplier-purchase');
   Route::post('store-supplier-purchase-report', [SupplierPurchaseController::class, 'storeSupplierPurchaseReport'])->name('store-supplier-purchase-report');
   Route::get('complete-supplier-purchase/{id?}', [SupplierPurchaseController::class, 'completeSupplierPurchase'])->name('complete-supplier-purchase');
   Route::get('manage-supplier-purchase-report/{id?}/{from_date?}/{to_date?}', [SupplierPurchaseController::class, 'manageSupplierPurchaseReport'])->name('manage-supplier-purchase-report');
   Route::post('view-complete-purchase-info/{id?}', [SupplierPurchaseController::class, 'viewCompletePurchaseInfo'])->name('view-complete-purchase-info');
   Route::post('get-location-by-supplier', [SupplierPurchaseController::class, 'getLocationBySupplier'])->name('get-location-by-supplier');
   Route::Resource('supplier-sub-head', SupplierSubHeadController::class);
   Route::post('store-rate-difference', [SupplierController::class, 'storeRateDifference'])->name('store-rate-difference');
   Route::post('rate-by-location', [SupplierRateLocationWiseController::class, 'rateByLocation'])->name('rate-by-location');
   Route::post('get-supplier-bonus', [SupplierController::class, 'getSupplierBonus'])->name('get-supplier-bonus');
   Route::post('reset-supplier-bonus', [SupplierController::class, 'resetSupplierBonus'])->name('reset-supplier-bonus');
   Route::post('update-supplier-purchase-report', [SupplierPurchaseController::class, 'updateSupplierPurchaseReport'])->name('update-supplier-purchase-report');
   Route::post('upload-purchase-image', [SupplierPurchaseController::class, 'uploadPurchaseImage'])->name('upload-purchase-image');
   Route::get('pending-for-approval', [SupplierPurchaseController::class, 'pendingForApproval'])->name('pending-for-approval');
   Route::post('reject-purchase-report', [SupplierPurchaseController::class, 'rejectPurchaseReport'])->name('reject-purchase-report');
   Route::post('approve-purchase-report', [SupplierPurchaseController::class, 'approvePurchaseReport'])->name('approve-purchase-report');
   Route::get('view-approved-purchase-detail/{id?}/{from_date?}/{to_date?}/{group_id?}', [SupplierPurchaseController::class, 'viewApprovedPurchaseDetail'])->name('view-approved-purchase-detail');
   Route::post('perform-action-on-purchase', [SupplierPurchaseController::class, 'performActionOnPurchase'])->name('perform-action-on-purchase');
   Route::post('store-turnover', [gstR1Controller::class, 'storeTurnOver'])->name('store-turnover');
   Route::get('manage-purchase-info', [SupplierPurchaseController::class, 'managePurchaseInfo'])->name('manage-purchase-info');
   Route::get('add-purchase-info', [SupplierPurchaseController::class, 'addPurchaseInfo'])->name('add-purchase-info');
   Route::post('store-purchase-info', [SupplierPurchaseController::class, 'storePurchaseInfo'])->name('store-purchase-info');
   Route::get('edit-purchase-info/{id}', [SupplierPurchaseController::class, 'editPurchaseInfo'])->name('edit-purchase-info');
   Route::post('update-purchase-info/{id}', [SupplierPurchaseController::class, 'updatePurchaseInfo'])->name('update-purchase-info');
   Route::post('delete-purchase-info', [SupplierPurchaseController::class, 'deletePurchaseInfo'])->name('delete-purchase-info');
   Route::post('item-by-group', [SupplierPurchaseController::class, 'itemByGroup'])->name('item-by-group');
   Route::get('supplier-purchase-setting', [SupplierPurchaseController::class, 'supplierPurchaseSetting'])->name('supplier-purchase-setting');
   Route::post('store-supplier-purchase-setting', [SupplierPurchaseController::class, 'storeSupplierPurchaseSetting'])->name('store-supplier-purchase-setting');
   Route::post('accounts-by-group', [SupplierPurchaseController::class, 'getAccountsByGroup']);
   Route::get('/user-waste-kraft-status', [SupplierPurchaseController::class, 'getUserDefaultStatus']);
   Route::get('/user-boiler-fuel-status', [SupplierPurchaseController::class, 'getUserDefaultStatusBoilerFuel']);

   
   
   Route::get('/sale-order', [SaleOrderController::class, 'index'])->name('sale-order.index');
   Route::get('sale-order/settings', [SaleOrderController::class, 'saleOrderSetting'])->name('sale-order.settings');
   Route::post('sale-order/settings/update', [SaleOrderController::class, 'updateSaleOrderSettings'])->name('sale-order.settings.update');
   Route::Resource('sale-order', SaleOrderController::class);
   Route::post('/check-duplicate-voucher', [PurchaseController::class, 'checkDuplicateVoucher'])->name('check.duplicate.voucher');
   Route::get('sale-order-start/{id}', [SaleOrderController::class, 'saleOrderStart'])->name('sale-order-start');
   Route::post('sale-order.delete', [SaleOrderController::class, 'saleOrderDelete'])->name('sale-order.delete');
   Route::post('sale-order-convert-in-pending', [SaleOrderController::class, 'saleOrderConvertInPending'])->name('sale-order-convert-in-pending');
   
   //Manage Deal Route
   Route::Resource('deal', DealController::class);
   //Fuel Supplier Route
   Route::Resource('fuel-supplier', FuelSupplierController::class);
   Route::post('store-fuel-item-rate', [FuelSupplierController::class, 'storeFuelItemRate'])->name('store-fuel-item-rate');
   Route::post('fuel_price-by-item', [FuelSupplierController::class, 'fuelPriceByItem'])->name('fuel_price-by-item');
   Route::post('get-supplier-rate-by-item', [FuelSupplierController::class, 'getSupplierRateByItem'])->name('get-supplier-rate-by-item');

// payroll 
Route::post('/payroll/esic/save',[EsicController::class, 'saveEsicPayroll'])->name('payroll.esic.save');
      Route::get('payroll/sheet', [MainController::class, 'index'])->name('payroll.index');
  Route::get('/payroll-esic-sheet', [EsicController::class, 'esicPayrollSheet'])->name('payroll.esic');
  Route::get('/payroll-pf-sheet', [PfController::class, 'PayrollSheet'])->name('payroll.pf');
Route::post('/payroll/esic/export',[EsicController::class, 'exportEsicExcel'])->name('payroll.esic.export');
Route::post('payroll/store', [MainController::class, 'store'])->name('payroll.store');
Route::post('/payroll/esic/save',[EsicController::class, 'saveEsicPayroll'])->name('payroll.esic.save');
Route::get('/payroll/register', [PayrollRegisterController::class, 'index'])->name('payroll.register');
Route::post('/payroll/register/generate', [PayrollRegisterController::class, 'generate'])->name('payroll.register.generate');
Route::post('/payroll/register/store',[PayrollRegisterController::class, 'store'])->name('payroll.register.store');


   Route::get('/Salesbook', [SaleRegisterController::class, 'index'])->name('salebook.index');
   Route::get('/Purchasebook', [PurchaseRegisterController::class, 'index'])->name('purchasebook.index');
     Route::post('/get-deals-by-party', [DealController::class, 'getDealsByParty']);
   Route::post('/get-deal-details', [DealController::class, 'getDealDetails']);

   //Production Route
   Route::get('/production.set_item', [ProductionController::class, 'setItems'])->name('production.set_item');
   Route::get('/production.set_item/add', [ProductionController::class, 'addItem'])->name('production.set_item.create');
   Route::post('/production.set_item/store', [ProductionController::class, 'storeItem'])->name('production.set_item.store');
   Route::get('/production.set_item/edit/{id}', [ProductionController::class, 'editItem'])->name('production.set_item.edit');
   Route::put('/production.set_item/update/{id}', [ProductionController::class, 'updateItem'])->name('production.set_item.update');
   Route::delete('/production.set_item/delete/{id}', [ProductionController::class, 'destroyItem'])->name('production.set_item.destroy');
   
   Route::Resource('deckle-process', ProductionController::class);
   Route::post('deckle-process.add_quality', [ProductionController::class, 'addQuality'])->name('deckle-process.add_quality');
   Route::post('stop-deckle-process', [ProductionController::class, 'stopDeckleProcess'])->name('stop-deckle-process');
   Route::post('stop-deckle-machine', [ProductionController::class, 'stopDeckleMachine'])->name('stop-deckle-machine');
   Route::post('start-deckle-machine', [ProductionController::class, 'startDeckleMachine'])->name('start-deckle-machine');
   Route::get('deckle-process.manage-reel', [ProductionController::class, 'deckleReelProcess'])->name('deckle-process.manage-reel');
   Route::post('quality-by-poproll', [ProductionController::class, 'qualityByPoproll'])->name('quality-by-poproll');
   Route::post('store-deckle-item', [ProductionController::class, 'storeDeckleItem'])->name('store-deckle-item');
   Route::post('start-deckle', [ProductionController::class, 'startDeckle'])->name('start-deckle');
   Route::get('deckle-process.manage-stock', [ProductionController::class, 'manageStock'])->name('deckle-process.manage-stock');
   Route::get('/stock/details/{item_id}', [ProductionController::class, 'getReelDetails'])->name('stock.details');
   Route::post('cancel-pop-roll-reel', [ProductionController::class, 'cancelPopRollReel'])->name('cancel-pop-roll-reel');
   Route::get('edit-pop-roll-reel/{id}', [ProductionController::class, 'editPopRollReel'])->name('edit-pop-roll-reel');
   Route::post('update-pop-roll-reel', [ProductionController::class, 'updatePopRollReel'])->name('update-pop-roll-reel');
   Route::get('deckle-process/{id}/edit', [ProductionController::class, 'edit'])->name('deckle-process.edit');
   Route::get('/get-item-details/{item_id}', [ProductionController::class, 'getItemDetails']);
   Route::get('/stock/create', [ProductionController::class, 'createNewReel'])->name('production.add.stock');
   Route::post('/stock/store', [ProductionController::class, 'storeNewReel'])->name('stock.store');
   Route::get('/stock/check-reel', [ProductionController::class, 'checkReel'])->name('stock.checkReel');
   Route::get('/item-size-stocks', [ProductionController::class, 'indexManual'])->name('item-size-stocks.index'); 
   Route::get('/item-size-stocks/{id}/edit', [ProductionController::class, 'editManual'])->name('item-size-stocks.edit'); 
   Route::post('/item-size-stocks/{id}', [ProductionController::class, 'updateManual'])->name('item-size-stocks.update');
   Route::post('validate-stock-weight', [ProductionController::class, 'validateStockWeight'])->name('validate-stock-weight');
   Route::get('/reels/import', [ProductionController::class, 'reelImportView'])->name('reel.import.view');
   Route::post('/reels/import', [ProductionController::class, 'reelImportProcess'])->name('reel.import.process');
   Route::get('/deckle-process/manage-reel/export', [ProductionController::class, 'exportReelCSV'])->name('deckle-process.manage-reel.export');
   Route::get('deckle-process/{id}/delete', [ProductionController::class, 'CancelCompletedDeckle'])->name('deckle-process.delete');

   Route::get('/boiler-fuel', [SupplierPurchaseController::class, 'boilerFuel'])->name('supplier.boiler_fuel');
   Route::get('/waste-kraft', [SupplierPurchaseController::class, 'wasteKraft'])->name('supplier.waste_kraft');
   
   Route::Resource('spare-part', SparePartController::class);
   Route::get('manage-sub-item/{id}', [ManageItemsController::class, 'subItemList'])->name('manage-sub-item');
   Route::get('add-sub-item/{id}', [ManageItemsController::class, 'addSubItem'])->name('add-sub-item');
   Route::post('store-sub-item', [ManageItemsController::class, 'storeSubItem'])->name('store-sub-item');

   Route::get('import-stock-journal-view', [ManageItemsController::class, 'importStockJournalView'])->name('import-stock-journal-view');
   Route::post('stock-journal-import-process', [ManageItemsController::class, 'stockJournalImportProcess'])->name('stock-journal-import-process');

   //Consumption
   Route::get('consumption', [ConsumptionController::class, 'index'])->name('consumption.index');
   Route::get('consumption/manage', [ConsumptionController::class, 'manage'])->name('consumption.manage');
   Route::post('save-stock-journal-consumption', [ConsumptionController::class, 'store'])->name('save-stock-journal-consumption');
   Route::get('/get-item-average-price', [ConsumptionController::class, 'getItemAveragePrice'])->name('get-item-average-price');
   Route::get('/consumption/settings', function () {
    return view('consumption.settings');
    })->name('consumption.settings');
    Route::post('revert-deckle', [ProductionController::class, 'revertPopRoll'])->name('revert-deckle');
    Route::get('set-sale-order', [SaleOrderController::class, 'setSaleOrder'])->name('set-sale-order');
    Route::get('set-sale-order-quantity/{id}', [SaleOrderController::class, 'setSaleOrderQuantity'])->name('set-sale-order-quantity');
    Route::post('save-sale-order-quantity', [SaleOrderController::class, 'saveSaleOrderQuantity'])->name('save-sale-order-quantity');
    Route::get('set-sale-order-deckle', [SaleOrderController::class, 'setSaleOrderDeckle'])->name('set-sale-order-deckle');
    
    Route::post('/deal/auto-complete/{id}', [DealController::class, 'autoComplete'])->name('deal.autoComplete');
    Route::get('/get-unit-name/{id}', [SparePartController::class, 'getUnitName']);
    // Route::prefix('supplier')->group(function () {
    //     Route::get('spare-part/show/{id}', [SparePartController::class, 'show'])->name('spare-part.show');
    //     Route::get('spare-part/download-pdf/{id}', [SparePartController::class, 'downloadPdf'])->name('spare-part.download-pdf');
    //     Route::get('spare-part/start/{id}', [SparePartController::class, 'createStart'])->name('spare-part.start');
    //     Route::post('spare-part/start/post/{id}', [SparePartController::class, 'updateStart'])->name('spare-part.start.post');
    //     Route::get('spare-part/start-new/{id}', [SparePartController::class, 'createStartNew'])->name('spare-part.start.new');
    //     Route::post('spare-part/start-new/{id}', [SparePartController::class, 'updateStartNew'])->name('spare-part.start.post.new');
    //     //Route::get('spare-part/start/edit/{id}', [SparePartController::class, 'editStart'])->name('spare-part.start.edit');
    //     Route::get('spare-part/start/view/{id}', [SparePartController::class, 'viewStart'])->name('spare-part.start.view');
    //     Route::get('spare-part/items', [SparePartController::class, 'items'])->name('spare-part.items');
    //     Route::post('spare-part/next', [SparePartController::class, 'nextFromItemList'])->name('spare-part.next');
    //     Route::post('spare-part/save-maintain-qty', [SparePartController::class, 'saveMaintainQuantity'])->name('spare-part.save-maintain-qty');
    //     Route::post('spare-part/draft/save-offers',[SparePartController::class, 'saveDraftOffers'])->name('spare-part.draft.save-offers');
    //     Route::post('spare-part/{id}/save-offers',[SparePartController::class, 'saveOffers'])->name('spare-part.save-offers');
    //     Route::post('spare-part/{id}/finalize', [SparePartController::class, 'finalizeSupplier'])->name('spare-part.finalize');
    //     Route::get('spare-part/offer/fetch', [SparePartController::class, 'fetchSupplierOffer'])->name('spare-part.offer.fetch');
    //     Route::post('spare-part/offer/update', [SparePartController::class, 'updateSupplierOffer'])->name('spare-part.offer.update');
    //     Route::post('spare-part/offer/delete', [SparePartController::class, 'deleteSupplierOffer'])->name('spare-part.offer.delete');
    //     Route::get('spare-part/sub-items', [SparePartController::class, 'subItems'])->name('spare-part.sub-items');
    
    //     Route::get('sale-order/credit-days', [SaleOrderController::class, 'creditDays'])->name('sale-order.credit-days');
    //     Route::get('sale-order/credit-days/create', [SaleOrderController::class, 'createCreditDay'])->name('sale-order.credit-days.create');
    //     Route::post('sale-order/credit-days/store', [SaleOrderController::class, 'storeCreditDay'])->name('sale-order.credit-days.store');
    //     Route::get('sale-order/credit-days/{id}/edit', [SaleOrderController::class, 'editDay'])->name('sale-order.credit-days.edit');
    //     Route::post('sale-order/credit-days/{id}/update', [SaleOrderController::class, 'updateDay'])->name('sale-order.credit-days.update');
    //     // CREDIT RATES 
    //     Route::get('sale-order/credit-rates', [SaleOrderController::class, 'creditRates'])->name('sale-order.credit-days.rates');
    //     Route::get('sale-order/credit-rates/edit', [SaleOrderController::class, 'editCreditRates'])->name('sale-order.credit-days.rates.edit');
    //     Route::post('sale-order/credit-rates/store', [SaleOrderController::class, 'storeCreditRates'])->name('sale-order.credit-days.rates.store');
    // });
    Route::prefix('supplier')->group(function () {
        Route::get('spare-part/show/{id}', [SparePartController::class, 'show'])->name('spare-part.show');
        Route::get('spare-part/download-pdf/{id}', [SparePartController::class, 'downloadPdf'])->name('spare-part.download-pdf');
        Route::get('spare-part/start/{id}', [SparePartController::class, 'createStart'])->name('spare-part.start');
        Route::post('spare-part/start/post/{id}', [SparePartController::class, 'updateStart'])->name('spare-part.start.post');
        Route::get('spare-part/start-new/{id}', [SparePartController::class, 'createStartNew'])->name('spare-part.start.new');
        Route::post('spare-part/start-new/{id}', [SparePartController::class, 'updateStartNew'])->name('spare-part.start.post.new');
        //Route::get('spare-part/start/edit/{id}', [SparePartController::class, 'editStart'])->name('spare-part.start.edit');
        Route::get('spare-part/start/view/{id}', [SparePartController::class, 'viewStart'])->name('spare-part.start.view');
        Route::get('spare-part/items', [SparePartController::class, 'items'])->name('spare-part.items');
        Route::post('spare-part/next', [SparePartController::class, 'nextFromItemList'])->name('spare-part.next');
        Route::post('spare-part/save-maintain-qty', [SparePartController::class, 'saveMaintainQuantity'])->name('spare-part.save-maintain-qty');
        Route::post('spare-part/draft/save-offers',[SparePartController::class, 'saveDraftOffers'])->name('spare-part.draft.save-offers');
        Route::post('spare-part/{id}/save-offers',[SparePartController::class, 'saveOffers'])->name('spare-part.save-offers');
        Route::post('spare-part/{id}/finalize', [SparePartController::class, 'finalizeSupplier'])->name('spare-part.finalize');
        Route::get('spare-part/offer/fetch', [SparePartController::class, 'fetchSupplierOffer'])->name('spare-part.offer.fetch');
        Route::post('spare-part/offer/update', [SparePartController::class, 'updateSupplierOffer'])->name('spare-part.offer.update');
        Route::post('spare-part/offer/delete', [SparePartController::class, 'deleteSupplierOffer'])->name('spare-part.offer.delete');
        Route::get('spare-part/sub-items', [SparePartController::class, 'subItems'])->name('spare-part.sub-items');
    
            // Spare Part Supplier
        Route::get('spare-part/suppliers',[SparePartController::class, 'viewSparePartSupplier'])->name('spare-part.suppliers');
        
        Route::get('spare-part/suppliers/add',[SparePartController::class, 'addSparePartSupplier'])->name('spare-part.suppliers.add');
        
        Route::post('spare-part/suppliers/store',[SparePartController::class, 'storeSparePartSupplier'])->name('spare-part.suppliers.store');
        
        Route::delete('spare-part/suppliers/delete/{id}',[SparePartController::class, 'deleteSparePartSupplier'])->name('spare-part.suppliers.delete');
        
        Route::get('spare-part/vehicle-entry',[SupplierPurchaseController::class, 'sparePartVehicleEntry'])->name('spare-part.vehicle.index');
        Route::get('spare-part/pending-for-purchase',[SparePartController::class, 'getPendingSparePartsForModal'])->name('spare-part.pending.modal');
        
        Route::get('spare-part/start-new/{sparePartId}/vehicle-entry/{vehicleEntryId}',[SparePartController::class, 'createStartNewFromVehicle'])->name('spare-part.start.new.vehicle');
        Route::get('/spare-part/maintain-quantity',[SparePartController::class, 'maintainQuantityView'])->name('spare-part.maintain');
        
        Route::post('/spare-part/maintain-quantity/save',[SparePartController::class, 'saveMaintainQuantity'])->name('spare-part.maintain.save');
    
        Route::get('sale-order/credit-days', [SaleOrderController::class, 'creditDays'])->name('sale-order.credit-days');
        Route::get('sale-order/credit-days/create', [SaleOrderController::class, 'createCreditDay'])->name('sale-order.credit-days.create');
        Route::post('sale-order/credit-days/store', [SaleOrderController::class, 'storeCreditDay'])->name('sale-order.credit-days.store');
        Route::get('sale-order/credit-days/{id}/edit', [SaleOrderController::class, 'editDay'])->name('sale-order.credit-days.edit');
        Route::post('sale-order/credit-days/{id}/update', [SaleOrderController::class, 'updateDay'])->name('sale-order.credit-days.update');
        // CREDIT RATES 
        Route::get('sale-order/credit-rates', [SaleOrderController::class, 'creditRates'])->name('sale-order.credit-days.rates');
        Route::get('sale-order/credit-rates/edit', [SaleOrderController::class, 'editCreditRates'])->name('sale-order.credit-days.rates.edit');
        Route::post('sale-order/credit-rates/store', [SaleOrderController::class, 'storeCreditRates'])->name('sale-order.credit-days.rates.store');
    
         
    });
    
    //Receivable and payable routes
    Route::get('/receiable/index',[ReceivableController::class, 'index'])->name('receiable.index');
      Route::get('/overdue-details/{account_id}', [ReceivableController::class, 'overdueDetails'])->name('overdue.report');
     Route::get('/overdue-report-pdf/{account_id}', [ReceivableController::class, 'overdueReportPdf'])->name('overdue.pdf.download');
    Route::get('/payable/index',[PayableController::class, 'index'])->name('payable.index');
    Route::get('/overdue-report-pdf/payable/{account_id}', [PayableController::class, 'overdueReportPdf'])->name('payable.overdue.pdf.download');
    Route::get('/overdue-details/payable/{account_id}', [PayableController::class, 'overdueDetails'])->name('payable.overdue.report');
    
    Route::post('sale/cancel', [SalesController::class, 'cancel'])->name('sale.cancel');
    Route::get('/get-item-price', [ManageItemsController::class, 'getItemPrice']);
    Route::get('/get-item-priceSO', [SaleOrderController::class, 'getItemPriceSO']);
    
    
   Route::get('/production/fetch-item-opening/{id}', [ProductionController::class, 'updateQtycreate'])
    ->name('production.item.fetch');
    Route::post('/production/read-csv-weight', [ProductionController::class, 'readCsvWeight'])
     ->name('production.csv.readWeight');

    Route::post('revert-in-process-purchase-report', [SupplierPurchaseController::class, 'revertInProcessPurchaseReport'])->name('revert-in-process-purchase-report');
    Route::get('/get-group-type/{groupId}', [SupplierPurchaseController::class, 'getGroupType']);
    Route::post('update-deckle-end-time', [ProductionController::class, 'updateDeckleEndTime'])->name('update-deckle-end-time');
    Route::post('sale-order.ready-to-dispatch', [SaleOrderController::class, 'readyToDispatch'])->name('sale-order.ready-to-dispatch');
    Route::post('sale-order-start-process', [SaleOrderController::class, 'startOrder'])->name('sale-order-start-process');
    Route::post('check.credit.note.no', [SalesReturnController::class, 'checkCreditNoteNo'])->name('check.credit.note.no');
    Route::post('save_sale_order_deckle_range', [SaleOrderController::class, 'saveDeckleRange'])->name('save_sale_order_deckle_range');
    Route::get('/production/item-consumption-rate', [ProductionController::class, 'ConsumptionRateIndex'])->name('ConsumptionRate');
   Route::get('/production/item-consumption-rate/add', [ProductionController::class, 'AddConsumptionRate'])->name('ConsumptionRate.add');
   Route::post('/production/item-consumption-rate/add', [ProductionController::class, 'StoreConsumptionRate'])->name('ConsumptionRate.store');
   Route::get('/consumption-rate/edit/{id}', [ProductionController::class, 'EditConsumptionRate'])->name('consumption_rate.edit');
   Route::put('/consumption-rate/update/{id}', [ProductionController::class, 'UpdateConsumptionRate'])->name('consumption_rate.update');
   Route::get('consumption-rate/delete/{id}', [ProductionController::class, 'delete'])->name('consumption_rate.delete');
   Route::get('/get-consumption-items/{item_id}', [ProductionController::class, 'getConsumptionItems']);
   Route::post('sale-order.back-to-pending', [SaleOrderController::class, 'backToPending'])->name('sale-order.back-to-pending');
  Route::post('sale-order.back-to-set-quantity', [SaleOrderController::class, 'backToSetQuantity'])->name('sale-order.back-to-set-quantity');
  
  Route::get('/supplier/waste-kraft/suppliers', [SupplierController::class, 'wasteKraftSupplier'])->name('supplier.waste_kraft_supplier');
    Route::get('/supplier/boiler-fuel/suppliers', [SupplierController::class, 'boilerFuelSupplier'])->name('supplier.boiler_fuel_supplier');
    Route::get('/supplier/waste-kraft/create', [SupplierController::class, 'createWasteKraft'])->name('supplier.waste_kraft_create');
    Route::get('supplier/boiler-fuel/create', [FuelSupplierController::class, 'createBoilerFuel'])->name('supplier.boiler_fuel_create');
    Route::post('supplier/waste-kraft/store',[SupplierController::class, 'store'])->name('supplier.waste_kraft.store');
    Route::post('supplier/boiler-fuel/store',[FuelSupplierController::class, 'store'])->name('supplier.boiler_fuel.store');   
    Route::get('supplier/waste-kraft/{id}/edit', [SupplierController::class, 'editWasteKraft'])->name('supplier.wastekraft.edit');
    Route::put('supplier/waste-kraft/{id}',[SupplierController::class, 'updateWasteKraft'])->name('supplier.wastekraft.update');
    Route::get('supplier/boiler-fuel/{id}/edit', [FuelSupplierController::class, 'editBoilerFuel'])->name('supplier.boilerfuel.edit');
    Route::put('supplier/boiler-fuel/{id}', [FuelSupplierController::class, 'updateBoilerFuel'])->name('supplier.boilerfuel.update');
    Route::delete('supplier/waste-kraft/{id}/delete',[SupplierController::class, 'destroyWasteKraft'])->name('supplier.wastekraft.destroy');
    Route::delete('supplier/boiler-fuel/{id}/delete',[FuelSupplierController::class, 'destroyBoilerFuel'])->name('fuel-supplier.destroy');
    Route::get('manage-supplier-rate/waste-kraft/{date?}', [SupplierRateLocationWiseController::class, 'wasteKraftRate'])->name('manage-supplier-rate.wastekraft');
    Route::get('manage-supplier-rate/boiler-fuel/{date?}', [SupplierRateLocationWiseController::class, 'boilerFuelRate'])->name('manage-supplier-rate.boilerfuel');
    Route::post('/check-voucher-no', [SalesController::class, 'checkVoucherNo'])->name('check.voucher.no');
    
   Route::post('/get-item-size-quantity-edit', [SalesController::class, 'getItemSizeQuantityForEdit'])->name('get-item-size-quantity-edit');
   Route::get('wastekraft-purchase-report/{id?}/{from_date?}/{to_date?}', [SupplierPurchaseController::class, 'wasteKraftPurchaseReport'])->name('wastekraft-purchase-report');
    Route::get('boilerfuel-purchase-report/{id?}/{from_date?}/{to_date?}', [SupplierPurchaseController::class, 'boilerFuelPurchaseReport'])->name('boilerfuel-purchase-report');
    Route::get('wastekraft-view-detail/{id}/{from}/{to}/{group_id}', [SupplierPurchaseController::class, 'viewApprovedPurchaseDetail'])->name('wastekraft-view-detail');
    Route::get('boilerfuel-view-detail/{id}/{from}/{to}/{group_id}', [SupplierPurchaseController::class, 'viewApprovedPurchaseDetail'])->name('boilerfuel-view-detail');
    
       Route::get('/opening-stock/filter', [ReelLedgerController::class, 'filter'])
        ->name('openingstock.filter');
        Route::get('/closing-stock/reels', [ReelLedgerController::class, 'ManageStock'])
        ->name('ManageStock.filter');
        Route::get('/closing-stock/reels/detail', [ReelLedgerController::class, 'ItemWiseReelStock'])
        ->name('ManageStock.filter.detail');
    Route::get('/closing-stock/reels/detail/pdf', [ReelLedgerController::class, 'downloadReelStockPDF'])->name('ReelStock.pdf');
    Route::post('/response/store', [ReceivableController::class, 'storeResponse'])->name('response.store');
    Route::get('/account/last-responses/{id}', [ReceivableController::class, 'lastResponses']);
    Route::get('resize-images', [SupplierPurchaseController::class, 'resizeImages'])->name('resize-images');
  
    Route::get('/aging-buckets/set', [ReceivableController::class, 'createAging'])->name('bucket.set');
    Route::post('/aging-buckets/store', [ReceivableController::class, 'storeAging'])->name('bucket.store');
    Route::post('/aging-buckets/update', [ReceivableController::class, 'updateAging'])->name('bucket.update');
    Route::get('/agingReport', [ReceivableController::class, 'AgingReport'])->name('AgingReport');
    Route::get('/export-sales', [SalesController::class, 'exportSalesView'])->name('sale-export-view');
    Route::post('/export-sales', [SalesController::class, 'exportSales'])->name('sale-export');
    Route::get('/export-sale-bill', [SalesController::class, 'exportSaleBillView'])->name('sale-bill-export-view');
    Route::post('/export-sale-bill', [SalesController::class, 'exportSaleBill'])->name('sale-bill-export');
    Route::get('/export-purchases', [PurchaseController::class, 'exportPurchasesView'])->name('purchase-export-view');
    Route::post('/export-purchases', [PurchaseController::class, 'exportPurchases'])->name('purchase-export');
    Route::get('/export-purchase-bill', [PurchaseController::class, 'exportPurchaseBillView'])->name('purchase-bill-export-view');
    Route::post('/export-purchase-bill', [PurchaseController::class, 'exportPurchaseBill'])->name('purchase-bill-export');
    
    Route::get('/export-payments', [PaymentController::class, 'exportPaymentView'])->name('payment-export-view');
    Route::post('/export-payments', [PaymentController::class, 'exportPayment'])->name('payment-export');
    
    Route::get('export-receipt', [ReceiptController::class, 'exportReceiptView'])->name('receipt-export-view');
    Route::post('export-receipt', [ReceiptController::class, 'exportReceipt'])->name('receipt-export');
    Route::post('/sale-order/get-items', [SaleOrderController::class, 'getSaleOrderItems'])->name('sale-order.get-items');
    Route::post('/save-sale-order-deckle-status', [SaleOrderController::class, 'saveDeckleStatus']);
    Route::post('/deckle/get-saved', [SaleOrderController::class, 'getSavedDeckleSizes']);
    Route::post('/deckle/cancel-size', [SaleOrderController::class, 'cancelDeckleSize']);
    Route::get(
      '/deckle-quality-production/{deckle_id}',
      [ProductionController::class, 'getDeckleQualityProduction']
    )->name('deckle.quality.production');
    Route::get('/RcmReport', [RcmController::class, 'RcmReport'])->name('RcmReport');
    Route::post('/RcmReport/store', [RcmController::class, 'storeRCM'])->name('rcm.store');
    Route::put('/rcm/update/{journal_id}', [RCMController::class, 'updateRCM'])
        ->name('rcm.update');
    Route::get('supplier/purchase/reports-dashboard', [SupplierPurchaseController::class,'reportsDashboard'])->name('supplier.purchase.reports.dashboard');
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity.logs');
    Route::post('/activity-logs/{id}/approve', [ActivityLogController::class, 'approve'])->name('activity_logs.approve');
    Route::get('supplier/spare-part/configuration',[SparePartController::class, 'configuration'])->name('supplier.sparepart.configuration');
    Route::post('supplier/spare-part/configuration/save',[SparePartController::class, 'saveConfiguration'])->name('supplier.sparepart.configuration.save');
    Route::get('configuration/settings',[App\Http\Controllers\ConfigurationSettings\ConfigurationSettingsController::class, 'index'])->name('configuration.settings');
    Route::post('configuration/settings/save',[App\Http\Controllers\ConfigurationSettings\ConfigurationSettingsController::class, 'save'])->name('configuration.settings.save');
    Route::post( '/deckle/cancel-item', [SaleOrderController::class, 'cancelDeckleItem'] )->name('deckle.cancel-item');
    Route::post( 'save-sale-order-filler-range', [SaleOrderController::class, 'saveFillerRange'] )->name('save_sale_order_filler_range');
    Route::post( '/deckle/cancel-item', [SaleOrderController::class, 'cancelDeckleItem'] )->name('deckle.cancel-item');

    Route::post('/deckle/remove-complete-gsm',[SaleOrderController::class, 'removeCompleteDeckleGsm'])->name('deckle.remove-complete');
    Route::post('/deckle/remove-single',[SaleOrderController::class, 'removeSingleDeckleCombination'])->name('deckle.remove-single');
      Route::get('/business/activity-logs', 
          [BusinessActivityLogController::class, 'index']
      )->name('business.activity.logs');
      Route::post(
          '/business/activity-logs/{id}/approve',
          [BusinessActivityLogController::class, 'approve']
      )->name('business.activity.logs.approve');
      Route::get('/purchase-configuration', [PurchaseConfigurationController::class, 'index'])
    ->name('purchase.configuration.index');
    Route::post('/purchase-configuration/store', [PurchaseConfigurationController::class, 'store'])
        ->name('purchase.configuration.store');
        Route::get('sale-invoice/ewaybill/{id}', [SalesController::class, 'downloadEwayBill'])->name('sale.invoice.ewaybill.pdf');
        Route::post('check-gstin-exists', [AccountsController::class, 'checkGstinExists']);
        Route::post('/account/update-gst', 
       [AccountsController::class, 'updateGst']
    )->name('account.update.gst');
    Route::get('/payroll/configuration', [PayrollConfigurationController::class, 'index'])
    ->name('payroll.configuration.index');
    
    Route::post('/payroll/configuration/store',
        [PayrollConfigurationController::class, 'store']
    )->name('payroll.configuration.store');
    Route::prefix('job-work')->group(function () {
    Route::get('/out/raw', [JobWorkController::class, 'index'])
        ->defaults('type', 'raw')
        ->name('jobwork.out.raw');
    Route::get('/out/finished', [JobWorkController::class, 'index'])
        ->defaults('type', 'finished')
        ->name('jobwork.out.finished');
    Route::get('/create/raw', [JobWorkController::class, 'create'])
        ->defaults('type', 'raw')
        ->name('jobwork.create.raw');
    Route::get('/create/finished', [JobWorkController::class, 'create'])
        ->defaults('type', 'finished')
        ->name('jobwork.create.finished');
    Route::post('/store', [JobWorkController::class, 'store'])->name('jobwork.store');
Route::get('/edit/raw/{id}', [JobWorkController::class, 'edit'])
    ->defaults('type', 'raw')
    ->name('jobwork.edit.raw');
Route::get('/edit/finished/{id}', [JobWorkController::class, 'edit'])
    ->defaults('type', 'finished')
    ->name('jobwork.edit.finished');
    Route::post('/update/{id}', [JobWorkController::class, 'update'])->name('jobwork.update');
    Route::post('/delete', [JobWorkController::class, 'delete'])
         ->name('jobwork.delete');
    Route::get('/view/{type}/{id}', [JobWorkController::class, 'view'])
     ->name('jobwork.view');
         Route::get('jobwork/pdf/{id}', [JobWorkController::class, 'pdf'])->name('jobwork.pdf');
         Route::post('/check-voucher', [JobWorkController::class, 'checkVoucher'])
        ->name('jobwork.checkVoucher');
        Route::get('/get-in-vouchers', [JobWorkController::class, 'getInVouchers'])
        ->name('jobwork.getInVouchers');

    Route::get('/get-in-items', [JobWorkController::class, 'getInItems'])
        ->name('jobwork.getInItems');
});
Route::prefix('job-work-in')->group(function () {
    Route::get('/raw', [JobWorkControllerIn::class, 'index'])
        ->defaults('type', 'raw')
        ->name('jobworkin.raw');
    Route::get('/finished', [JobWorkControllerIn::class, 'index'])
        ->defaults('type', 'finished')
        ->name('jobworkin.finished');
    Route::get('/create/raw', [JobWorkControllerIn::class, 'create'])
        ->defaults('type', 'raw')
        ->name('jobworkin.create.raw');
    Route::get('/create/finished', [JobWorkControllerIn::class, 'create'])
        ->defaults('type', 'finished')
        ->name('jobworkin.create.finished');
    Route::post('/store', [JobWorkControllerIn::class, 'store'])->name('jobworkin.store');
    Route::get('/edit/raw/{id}', [JobWorkControllerIn::class, 'edit'])
        ->defaults('type', 'raw')
        ->name('jobworkin.edit.raw');
    Route::get('/edit/finished/{id}', [JobWorkControllerIn::class, 'edit'])
        ->defaults('type', 'finished')
        ->name('jobworkin.edit.finished');
    Route::post('/{id}/update', [JobWorkControllerIn::class, 'update'])->name('jobworkin.update');
    Route::post('/delete', [JobWorkControllerIn::class, 'delete'])->name('jobworkin.delete');
    Route::get('/{id}/view', [JobWorkControllerIn::class, 'view'])->name('jobworkin.view');
    Route::post('/check-voucher', [JobWorkControllerIn::class, 'checkVoucher'])
        ->name('jobworkin.checkVoucher');
        Route::get(
    'jobwork-in/get-out-vouchers',
    [JobWorkControllerIn::class, 'getOutVouchers']
)->name('jobworkin.getOutVouchers');
Route::get('/out-items', [JobWorkControllerIn::class, 'getOutItems'])
         ->name('jobworkin.outItems');
});

Route::prefix('job-work-stock-journal')->group(function () {

    Route::get('/', [JobWorkStockJournalController::class, 'index'])
        ->name('jobwork.stockjournal.index');

    Route::get('/create', [JobWorkStockJournalController::class, 'create'])
        ->name('jobwork.stockjournal.create');

    Route::post('/store', [JobWorkStockJournalController::class, 'store'])
        ->name('jobwork.stockjournal.store');

    Route::post('/get-pending-items', [JobWorkStockJournalController::class, 'getPendingItems'])
        ->name('jobwork.stockjournal.pending-items');

    Route::post('/party-vouchers', [JobWorkStockJournalController::class, 'getPartyVouchers'])
        ->name('jobwork.stockjournal.party-vouchers');

    Route::get('jobwork/stockjournal/{id}/edit', [JobWorkStockJournalController::class, 'edit'])
    ->name('jobwork.stockjournal.edit');
    Route::put('jobwork/stockjournal/{id}', [JobWorkStockJournalController::class, 'update'])
        ->name('jobwork.stockjournal.update');
    });
    Route::get('production/machine-time-loss', 
        [ProductionController::class, 'machineTimeLoss']
    )->name('machine.time.loss');
     Route::get('account-production/export-csv', [AccountProductionController::class, 'exportCsv'])->name('account.production.export.csv');
    Route::resource('account-production', AccountProductionController::class);
   
    
    
    //Task Manager
    Route::get('task', [TaskManagerController::class, 'index'])->name('task.index');
    Route::get('task/create',[TaskManagerController::class,'create'])->name('task.create');
    Route::post('task/store',[TaskManagerController::class,'store'])->name('task.store');
    Route::get('task/edit/{id}', [TaskManagerController::class, 'edit'])->name('task.edit');
    Route::post('task/update', [TaskManagerController::class, 'update'])->name('task.update');
    Route::post('task/update-status',[TaskManagerController::class,'updateStatus'])->name('task.updateStatus');
    Route::post('task/add-response',[TaskManagerController::class,'addResponse'])->name('task.addResponse');
    Route::post('task/delete', [TaskManagerController::class, 'delete'])->name('task.delete');
    Route::post('/task/approve/{id}', [TaskManagerController::class, 'approveTask'])->name('task.approve');
    Route::post('/task/delegate', [TaskManagerController::class, 'delegateTask'])->name('task.delegate');
    Route::get('/my-tasks', [TaskManagerController::class, 'myTasks'])
        ->name('task.myTasks');
    Route::get('task/monthly', [TaskManagerController::class, 'monthlyIndex'])
        ->name('task.monthly.index');
    
    Route::get('task/monthly/create', [TaskManagerController::class, 'monthlyCreate'])
        ->name('task.monthly.create');
    
    Route::post('task/monthly/store', [TaskManagerController::class, 'monthlyStore'])
        ->name('task.monthly.store');
    
    Route::get('task/monthly/edit/{id}', [TaskManagerController::class, 'monthlyEdit'])
        ->name('task.monthly.edit');
    
    Route::post('task/monthly/update', [TaskManagerController::class, 'monthlyUpdate'])
        ->name('task.monthly.update');
    Route::get('task/{id}',[TaskManagerController::class,'detail'])->name('task.detail');
    Route::post('task/monthly/delete', [TaskManagerController::class, 'monthlyDelete'])
        ->name('task.monthly.delete');
        
        Route::get(
        '/job-work-ledger',
        [JobWorkLedgerController::class, 'index']
    )->name('job_work_ledger.index');
    Route::post(
        '/job-work-ledger',
        [JobWorkLedgerController::class, 'fetch']
        )->name('job_work_ledger.fetch');
    Route::get('/account-summary', 
            [AccountSummaryController::class, 'index']
        )->name('account.summary');
    Route::get(
        'account-summary/month',
        [AccountSummaryController::class, 'monthSummary']
    )->name('account.month.summary');
    Route::get(
        'account-summary/ledger',
        [AccountSummaryController::class, 'ledger']
    )->name('account.ledger');
    Route::get('account/bulk-update', [AccountsController::class, 'bulkUpdatePage'])->name('account.bulk.update');
    Route::post('account/bulk-update-save', [AccountsController::class, 'bulkUpdateSave'])->name('account.bulk.update.save');
    Route::post('account/bulk-update-fetch', [AccountsController::class, 'bulkUpdateFetch'])
        ->name('account.bulk.update.fetch');
        Route::get('/payroll/settings', [MainController::class, 'settings'])->name('payroll.settings');
    Route::post('/payroll/settings/save', [MainController::class, 'saveSettings'])->name('payroll.settings.save');
    Route::post('/account/allow-without-gst', [AccountsController::class, 'allowWithoutGst'])->name('account.allow.without.gst');
    Route::get('/info', [SaleOrderController::class, 'saleOrderInfo'])
            ->name('sale-order.info');

    Route::get('/info/edit', [SaleOrderController::class, 'editSaleOrderInfo'])
            ->name('sale-order.info.edit');

    Route::post('/info/store', [SaleOrderController::class, 'storeSaleOrderInfo'])
        ->name('sale-order.info.store');
    Route::get('/add-location-price', [SaleOrderController::class, 'addLocationPrice'])
        ->name('sale-order.add-location-price');
    Route::post('/store-location-price', [SaleOrderController::class, 'storeLocationPrice'])
        ->name('sale-order.store-location-price');
        Route::get('/order-preview', [SaleOrderController::class, 'saleOrderPreview'])
        ->name('sale-order.order-preview');
    Route::prefix('accounting')->group(function () {
        Route::get('/item-summary', [ItemSummaryController::class, 'index'])->name('item-summary.index');
        Route::get('/item-summary/group/{group_id}', [ItemSummaryController::class, 'items'])->name('item-summary.items');
        Route::get('/item-summary/item/{item_id}', [ItemSummaryController::class, 'monthly'])->name('item-summary.monthly');
    });
    
    Route::get('export-account-master', [AccountsController::class, 'exportAccountMaster'])
    ->name('export-account-master');
    Route::get('attendance-types', [AttendanceTypeController::class, 'index'])->name('attendance.types');

    Route::post('attendance-types/store', [AttendanceTypeController::class, 'store'])->name('attendance.types.store');

    Route::get('attendance-types/delete/{id}', [AttendanceTypeController::class, 'delete'])->name('attendance.types.delete');
    Route::get('/attendance/weekly-off-setting', 
        [AttendanceManagementController::class, 'weeklyOffIndex']
    )->name('attendance.weeklyoff.index');
    
    Route::post('/attendance/weekly-off-setting', 
        [AttendanceManagementController::class, 'weeklyOffStore']
    )->name('attendance.weeklyoff.store');
    Route::get('/attendance/monthly-off-calendar',
        [AttendanceManagementController::class, 'monthlyOffCalendar']
    )->name('attendance.monthlyoff.calendar');
    
    Route::post('/attendance/monthly-off-calendar',
        [AttendanceManagementController::class, 'monthlyOffCalendar']
    )->name('attendance.monthlyoff.calendar.filter');
    
    Route::post('/attendance/monthly-off-save',
        [AttendanceManagementController::class, 'saveMonthlyHoliday']
    )->name('attendance.monthlyoff.save');
    
    Route::post('sale-invoice/email/{id}', [SalesController::class, 'emailInvoice'])->name('sale.emailInvoice');

    Route::get('/company/mail-settings', [CompanyController::class, 'editMailSettings'])
        ->name('company.mail.settings');
    
    Route::post('/company/mail-settings', [CompanyController::class, 'updateMailSettings'])
        ->name('company.mail.settings.update');
    
    Route::post('/company/mail-settings/test', [CompanyController::class, 'testMailSettings'])
        ->name('company.mail.settings.test');
        Route::get('update-account-opening', [AccountsController::class, 'updateOpeningBalance']);
    Route::post('machine-loss/store',[ProductionController::class,'storeMachineLoss']);
        Route::get('machine-loss/get/{id}', [ProductionController::class,'getMachineLoss']);
    Route::post('machine-loss/update', [ProductionController::class,'updateMachineLoss']);
    
    Route::get('/export-account-groups',[AccountGroupsController::class,'exportAccountGroups'])
    ->name('exportAccountGroups');
    
    Route::get('export-items',
        [ManageItemsController::class,'exportItems']
    );
    Route::get(
    'supplier/waste-kraft/datatable',
    [SupplierController::class,'wasteKraftSupplierDatatable']
)->name('supplier.wastekraft.datatable');
Route::get(
    'supplier/boiler-fuel/datatable',
    [SupplierController::class,'boilerFuelSupplierDatatable']
)->name('supplier.boilerfuel.datatable');
Route::get('/vehicle-report', [SaleOrderController::class, 'vehicleReport'])->name('vehicle.report');
Route::get('/transaction-report', [TransactionReportController::class, 'index'])
    ->name('transaction.report');
Route::get('/transaction/view',[TransactionReportController::class,'viewTransaction'])
->name('transaction.view');
Route::post('/transaction/approve', [TransactionReportController::class,'approveTransaction'])
    ->name('transaction.approve');
    Route::post('delete-deckle-quality', [ProductionController::class, 'deleteDeckleQuality'])->name('delete-deckle-quality');
    Route::get('backup/create',[BackUpController::class,'createBackup']);
    Route::get('/backup/list',[BackUpController::class,'backupList']);
    Route::get('/backup/restore/{file}',[BackUpController::class,'restoreBackup']);
    
    Route::post('cancel-sale-return', [SalesReturnController::class, 'cancelSaleReturn']);
    Route::post('cancel-purchase-return', [PurchaseReturnController::class, 'cancelPurchaseReturn']);
    Route::get('/account-summary/export/csv', [AccountSummaryController::class, 'exportCSV'])
    ->name('account.summary.export.csv');
    Route::get('/account-summary/export/details-csv', [AccountSummaryController::class, 'exportDetailsCSV'])
    ->name('account.summary.export.details.csv');
    Route::get('/account-summary/export/month-csv', [AccountSummaryController::class, 'exportMonthCSV'])
    ->name('account.summary.export.month.csv');
    Route::get('/account-summary/export-pdf', [AccountSummaryController::class, 'exportPDF'])
    ->name('account.summary.export.pdf');
    Route::get('/account-summary/export-details-pdf', [AccountSummaryController::class, 'exportDetailsPDF'])
    ->name('account.summary.export.details.pdf');
    Route::post('account-summary/export-details-excel',[AccountSummaryController::class, 'exportDetailsExcel'])->name('account.summary.export.details.excel');    
Route::post('account-summary/export-details-pdf', [AccountSummaryController::class, 'exportDetailsPDF'])
    ->name('account.summary.export.details.pdf');
    Route::get('/account-summary/export-month-pdf', [AccountSummaryController::class, 'exportMonthPDF'])
    ->name('account.summary.export.month.pdf');
    Route::get('/summary-item-details', [SaleOrderController::class, 'summaryItemDetails'])
    ->name('summary.item.details');
    Route::get('item-ledger-average-csv', [ItemLedgerController::class, 'exportCsv']);
    Route::get('item-stock-csv', [ItemLedgerController::class, 'exportStockCsv']);
    Route::get('item-ledger-main-csv', [ItemLedgerController::class, 'exportMainLedgerCsv']);
    Route::get('/spare-part-life-chart', [SparePartLifeChartController::class, 'index'])
    ->name('spare-part-life-chart.index');
    Route::post('/part-life-chart/store', [SparePartLifeChartController::class, 'store'])
        ->name('part-life-chart.store');
    Route::get('/part-life-chart/locations', [SparePartLifeChartController::class, 'viewLocations'])
        ->name('part-life.locations');
    Route::get('/part-life-chart/locations/add', [SparePartLifeChartController::class, 'addLocation'])
        ->name('part-life.locations.add');
    Route::post('/part-life-chart/locations/store', [SparePartLifeChartController::class, 'storeLocation'])
        ->name('part-life.locations.store');
    Route::delete('/part-life-chart/locations/delete/{id}', [SparePartLifeChartController::class, 'deleteLocation'])
        ->name('part-life.locations.delete');
    Route::get('/part-life-chart/brands', [SparePartLifeChartController::class, 'viewBrands'])
        ->name('part-life.brands');
    Route::get('/part-life-chart/brands/add', [SparePartLifeChartController::class, 'addBrand'])
        ->name('part-life.brands.add');
    Route::post('/part-life-chart/brands/store', [SparePartLifeChartController::class, 'storeBrand'])
        ->name('part-life.brands.store');
    Route::delete('/part-life-chart/brands/delete/{id}', [SparePartLifeChartController::class, 'deleteBrand'])
        ->name('part-life.brands.delete');
    Route::get('/part-life-chart/entries', [SparePartLifeChartController::class, 'viewEntries'])
        ->name('part-life.entries');
    Route::get('/part-life-chart/entries/add', [SparePartLifeChartController::class, 'addEntry'])
        ->name('part-life.entries.add');
    Route::post('/part-life-chart/entries/store', [SparePartLifeChartController::class, 'storeEntry'])
        ->name('part-life.entries.store');
    Route::get('/part-life-chart/entries/edit/{group_id}', [SparePartLifeChartController::class, 'editEntry'])
        ->name('part-life.entries.edit');
    Route::put('/part-life-chart/entries/update/{group_id}', [SparePartLifeChartController::class, 'updateEntry'])
        ->name('part-life.entries.update');
    Route::delete('/part-life-chart/entries/delete/{group_id}', [SparePartLifeChartController::class, 'deleteEntry'])
        ->name('part-life.entries.delete');
    Route::post('part-life/check-date', [SparePartLifeChartController::class, 'checkDate'])->name('part-life.check-date');
    Route::get('get-part-life-items', [SparePartLifeChartController::class, 'getPartLifeItems']);
    Route::get('/jobwork/get-next-voucher', [JobWorkController::class, 'getNextVoucher'])->name('jobwork.getNextVoucher');
    Route::get('/journal-export', [JournalController::class, 'exportView'])
    ->name('journal.export.view');
    Route::post('/journal-export', [JournalController::class, 'export'])
    ->name('journal-export');
   Route::post('get-item-gst-rate', [ManageItemsController::class, 'getItemGstRate'])->name('get-item-gst-rate');
   
   Route::post('/check-party-group', [AccountLedgerController::class, 'checkPartyGroup']);
   Route::get('/part-life/brands/edit/{id}', [SparePartLifeChartController::class, 'editBrand'])
    ->name('part-life.brands.edit');
Route::post('/part-life/brands/update/{id}', [SparePartLifeChartController::class, 'updateBrand'])
    ->name('part-life.brands.update');
Route::get('/part-life/locations/edit/{id}', [SparePartLifeChartController::class, 'editLocation'])
    ->name('part-life.locations.edit');
Route::post('/part-life/locations/update/{id}', [SparePartLifeChartController::class, 'updateLocation'])
    ->name('part-life.locations.update');
    Route::get('transaction-integrity', [TransactionIntegrityController::class, 'index']);
    
    Route::prefix('payroll')->group(function () {
Route::get('/', [PayrollHeadController::class, 'index'])
    ->name('payroll.index');
    Route::get('/create', [PayrollHeadController::class, 'create'])
        ->name('payroll.create');

    Route::post('/store', [PayrollHeadController::class, 'store'])
        ->name('payroll.store');
    Route::get('/edit/{id}', [PayrollHeadController::class, 'edit'])
        ->name('payroll.edit');

    Route::post('/update/{id}', [PayrollHeadController::class, 'update'])
        ->name('payroll.update');

    Route::post('/delete', [PayrollHeadController::class, 'destroy'])
    ->name('payroll.delete');
});
Route::get('/journal/print/{id}', [JournalController::class, 'print'])->name('journal.print');
//Route::get('/migrate-item-gst', [ManageItemsController::class, 'migrateItemGST'])->name('migrate.item.gst');

    //Box Mnagement Url
    Route::get('party-item-rate/create', [PartyItemRateController::class, 'create'])->name('party-item-rate.create');
    Route::post('party-item-rate/store', [PartyItemRateController::class, 'store'])->name('party-item-rate.store');
    Route::get('party-item-rate', [PartyItemRateController::class, 'index'])->name('party-item-rate.index');
    Route::get('party-item-rate/{party_id}/edit', [PartyItemRateController::class, 'edit'])->name('party-item-rate.edit');
    Route::post('party-item-rate/{party_id}/update', [PartyItemRateController::class, 'update'])->name('party-item-rate.update');
    Route::get('party-item-rate/delete/{party_id}', [PartyItemRateController::class, 'destroy'])->name('party-item-rate.delete');
    Route::get('get-party-item-price', [PartyItemRateController::class, 'getPrice']);
    Route::get('test', [TestController::class, 'index']);
    Route::get('dara-report', [DaraReportController::class, 'index'])->name('dara.report');
    
    Route::get('retail-item-rate/create', [ManageRateController::class, 'create'])->name('retail-item-rate.create');
    Route::post('/retail-item-rate/store', [ManageRateController::class, 'store'])->name('retail-item-rate.store');
    Route::get('/retail-rate/{id}/edit', [ManageRateController::class, 'edit'])->name('retail-rate.edit');
    Route::post('/retail-rate/update/{id}', [ManageRateController::class, 'update'])->name('retail-rate.update');
    Route::get('retail-item-rate', [ManageRateController::class, 'index'])->name('retail-item-rate.index');
    Route::get('retail-rate/delete/{party_id}', [ManageRateController::class, 'destroy'])->name('retail-rate.delete');
    Route::post('/check-latest-datetime', [ManageRateController::class, 'checkLatestDateTime'])->name('check.latest.datetime');
    Route::post('/get-item-rate-by-date', [ManageRateController::class, 'getItemRateByDate'])->name('get.item.rate.by.date');
});