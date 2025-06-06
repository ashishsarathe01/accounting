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
      Route::Resource('/merchant',MerchantController::class)->name('*','merchant');
      Route::Resource('/account-head',AccountHeadController::class)->name('*','account-head');
      Route::Resource('/account-group',AccountGroupController::class)->name('*','account-group');
      Route::Resource('/account',AccountController::class)->name('*','account');
   });
   
});

Route::get('/', [AuthController::class, 'index'])->name('password.login');
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
Route::group(['middleware' => ['merchantloginstatus']], function () {
Route::post('change-password', [AuthController::class, 'submitChangePassword'])->name('password.changepassword');
Route::get('dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
Route::post('change-company', [AuthController::class, 'changeCompany'])->name('company.change');
Route::get('logout', [AuthController::class, 'logout'])->name('logout');
Route::get('view-company', [CompanyController::class, 'viewCompany'])->name('view-company');
Route::get('manage-financial-year', [CompanyController::class, 'manageFinancialYear'])->name('manage-financial-year');
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
Route::Resource('account', AccountsController::class);
Route::get('add-account', [AccountsController::class, 'addAccount']);
Route::post('account-update', [AccountsController::class, 'update'])->name('account.update');
Route::post('account-delete', [AccountsController::class, 'delete'])->name('account.delete');
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
Route::Resource('account-manage-item', ManageItemsController::class);
Route::post('account-manage-item-update', [ManageItemsController::class, 'update'])->name('account-manage-item.update');
Route::get('stock-journal', [ManageItemsController::class, 'stockJournal'])->name('stock-journal');
Route::get('add-stock-journal', [ManageItemsController::class, 'addStockJournal'])->name('add-stock-journal');
Route::post('save-stock-journal', [ManageItemsController::class, 'saveStockJournal'])->name('save-stock-journal');
Route::post('delete-stock-journal', [ManageItemsController::class, 'deleteStockJournal'])->name('delete-stock-journal');
Route::get('edit-stock-journal/{id}', [ManageItemsController::class, 'editStockJournal']);
Route::post('update-stock-journal', [ManageItemsController::class, 'updateStockJournal'])->name('update-stock-journal');
Route::post('account-manage-item-delete', [ManageItemsController::class, 'delete'])->name('account-manage-item.delete');
Route::Resource('account-bill-sundry', BillSundrysController::class);
Route::post('account-bill-sundry-update', [BillSundrysController::class, 'update'])->name('account-bill-sundry.update');
Route::post('account-bill-sundry-delete', [BillSundrysController::class, 'delete'])->name('account-bill-sundry.delete');
Route::Resource('purchase', PurchaseController::class);
Route::post('purchase-update', [PurchaseController::class, 'update'])->name('purchase.update');
Route::post('purchase-delete', [PurchaseController::class, 'delete'])->name('purchase.delete');
Route::get('purchase-invoice/{id:?}', [PurchaseController::class, 'purchaseInvoice'])->name('purchase-invoice');
Route::get('purchase-edit/{id}', [PurchaseController::class, 'purchaseEdit']);
Route::post('account-manage-item-store', [SalesController::class, 'manageItemStore'])->name('account-manage-item-store');
Route::post('account-store', [SalesController::class, 'addAccountStore'])->name('account-store');
Route::Resource('sale', SalesController::class);
Route::get('edit-sale/{id}', [SalesController::class, 'edit']);
Route::post('sale-update', [SalesController::class, 'update'])->name('sale.update');
Route::post('sale-delete', [SalesController::class, 'delete'])->name('sale.delete');
Route::get('sale-invoice/{id:?}', [SalesController::class, 'saleInvoice'])->name('sale-invoice');
Route::Resource('sale-return', SalesReturnController::class);
Route::get('sale-return-invoice/{id}', [SalesReturnController::class, 'saleReturnInvoice']);
Route::get('sale-return-edit/{id}', [SalesReturnController::class, 'edit']);
Route::post('sale-return-update', [SalesReturnController::class, 'update'])->name('sale-return-update');
Route::post('sale-return-delete', [SalesReturnController::class, 'delete'])->name('sale-return.delete');
Route::Resource('purchase-return', PurchaseReturnController::class);
Route::post('purchase-return-update', [PurchaseReturnController::class, 'update'])->name('purchase-return-update');
Route::post('purchase-return-delete', [PurchaseReturnController::class, 'delete'])->name('purchase-return.delete');
Route::get('purchase-return-invoice/{id}', [PurchaseReturnController::class, 'purchaseReturnInvoice']);
Route::get('purchase-return-edit/{id}', [PurchaseReturnController::class, 'edit']);
Route::Resource('payment', PaymentController::class);
   Route::post('payment-update', [PaymentController::class, 'update'])->name('payment.update');
   Route::post('payment-delete', [PaymentController::class, 'delete'])->name('payment.delete');
   Route::Resource('receipt', ReceiptController::class);
   Route::post('receipt-update', [ReceiptController::class, 'update'])->name('receipt.update');
   Route::post('receipt-delete', [ReceiptController::class, 'delete'])->name('receipt.delete');
   Route::Resource('journal', JournalController::class);
   Route::post('journal-update', [JournalController::class, 'update'])->name('journal.update');
   Route::post('journal-delete', [JournalController::class, 'delete'])->name('journal.delete');
   Route::Resource('contra', ContraController::class);
   Route::post('contra-update', [ContraController::class, 'update'])->name('contra.update');
   Route::post('contra-delete', [ContraController::class, 'delete'])->name('contra.delete');
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
   Route::get('sale-by-month/{financial_year:?}', [ProfitLossController::class, 'saleByMonth'])->name('sale-by-month');
   Route::get('sale-by-month-detail/{financial_year:?}/{from_date}/{to_date}', [ProfitLossController::class, 'saleByMonthDetail'])->name('sale-by-month-detail');
   Route::get('purchase-by-month/{financial_year:?}', [ProfitLossController::class, 'purchaseByMonth'])->name('purchase-by-month');
   Route::get('purchase-by-month-detail/{financial_year:?}/{from_date}/{to_date}', [ProfitLossController::class, 'purchaseByMonthDetail'])->name('purchase-by-month-detail');
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
   Route::Resource('itemledger', ItemLedgerController::class);
   Route::post('itemledger-update', [ItemLedgerController::class, 'update'])->name('itemledger.update');
   Route::post('itemledger-delete', [ItemLedgerController::class, 'delete'])->name('itemledger.delete');
   Route::get('itemledger-filter', [ItemLedgerController::class, 'filter'])->name('itemledger.filter');
   Route::get('item-ledger-average', [ItemLedgerController::class, 'itemLedgerAverage'])->name('item-ledger-average');
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
   Route::get('parameterized-configuration',[ItemParameterizedController::class,'index'])->name('parameterized-configuration');
   Route::post('store-parameterized-configuration',[ItemParameterizedController::class,'storeParameterizedConfiguration'])->name('store-parameterized-configuration');
   Route::Resource('credit-note-without-item', CreditNoteWithoutItemController::class);
   Route::Resource('debit-note-without-item', DebitNoteWithoutItemController::class);
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
});
