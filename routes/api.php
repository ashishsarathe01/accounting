<?php

use Illuminate\Http\Request;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\OwnerController;
use App\Http\Controllers\API\ShareholderController;
use App\Http\Controllers\API\ShareTransferController;
use App\Http\Controllers\API\BankController;
use App\Http\Controllers\API\AccountHeadingController;
use App\Http\Controllers\API\AccountGroupsController;
use App\Http\Controllers\API\AccountsController;
use App\Http\Controllers\API\UnitsController;
use App\Http\Controllers\API\ItemGroupsController;
use App\Http\Controllers\API\ManageItemsController;
use App\Http\Controllers\API\ThirdPartyController;
use App\Http\Controllers\API\BillSundrysController;
use App\Http\Controllers\API\SalesController;
use App\Http\Controllers\API\PurchaseController;
use App\Http\Controllers\API\ModuleController;
use App\Http\Controllers\API\GstSettingController;
use App\Http\Controllers\API\AjaxController;
use App\Http\Controllers\API\SupplierController;
use App\Http\Controllers\API\ProductionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


/********* Login & Registration *********/

Route::post('register',[AuthController::class,'register']);
Route::post('send-otp',[AuthController::class,'sendOtp']);
Route::post('login-with-otp',[AuthController::class,'loginWithOtp']);
//Route::post('login',[AuthController::class,'login']);

Route::post('generate-mpin',[AuthController::class,'generateMpin']);
Route::post('reset-mpin',[AuthController::class,'resetMpin']);
Route::post('login-with-mpin',[AuthController::class,'loginWithMpin']);

Route::middleware('auth:api')->group(function(){
    Route::get('get-user',[AuthController::class,'userInfo']);
    /*********** Settings ***********/

    Route::get('module-list',[ModuleController::class,'moduleList']);
    Route::get('user-assigned-modules',[ModuleController::class,'getAssignedModules']);
    Route::post('update-user-privilege',[ModuleController::class,'assignModule']);

    // GST Configuration
    Route::post('gst-configuration-list',[GstSettingController::class,'gstConfigurationList']);
    Route::post('gst-configuration-branch-list',[GstSettingController::class,'gstConfigurationBranchList']);
    Route::post('add-gst-configuration',[GstSettingController::class,'store']);
    Route::post('add-gst-configuration-branch',[GstSettingController::class,'add_branch']);
    Route::post('update-gst-configuration',[GstSettingController::class,'updateGstSetting']);
    Route::post('update-gst-configuration-branch',[GstSettingController::class,'updateGstBranch']);
    /*********** Company ***********/

    // Company info
    Route::post('create-company',[CompanyController::class,'createCompany']);
    Route::post('company-detail',[CompanyController::class,'companyDetail']);
    Route::post('company-listing',[CompanyController::class,'companyListing']);
    Route::post('update-company-details',[CompanyController::class,'updateCompanyDetails']);

    // GST
    Route::post('verify-gst',[ThirdPartyController::class,'verifyGst']);

    // Owners
    Route::post('create-owner',[OwnerController::class,'createOwner']);
    Route::post('owner-listing',[OwnerController::class,'ownerListing']);
    Route::post('update-owner',[OwnerController::class,'updateOwner']);
    Route::post('owner-resigning',[OwnerController::class,'ownerResigning']);

    // Partners
    Route::post('joint-partner-listing',[OwnerController::class,'jointPartnerListing']);
    Route::post('resigned-partner-listing',[OwnerController::class,'resignedPartnerListing']);

    // Share Holders
    Route::post('create-shareholder',[ShareholderController::class,'createShareholder']);
    Route::post('shareholder-listing',[ShareholderController::class,'shareholderListing']);
    Route::post('update-shareholder',[ShareholderController::class,'updateShareholder']);

    // Share Transfers
    Route::post('create-share-transfer',[ShareTransferController::class,'createShareTransfer']);
    Route::post('share-transfer-listing',[ShareTransferController::class,'shareTransferListing']);
    Route::post('update-share-transfer',[ShareTransferController::class,'updateShareTransfer']);

    // Bank
    Route::post('create-bank',[BankController::class,'createBank']);
    Route::post('bank-listing',[BankController::class,'bankListing']);
    Route::post('update-bank',[BankController::class,'updateBank']);
    /********* Administrator/Master *********/

    // Heading
    Route::post('add-heading',[AccountHeadingController::class,'createAccountHeading']);
    Route::get('heading-list',[AccountHeadingController::class,'headingList']);
    Route::post('get-heading',[AccountHeadingController::class,'edit']);
    Route::post('update-heading',[AccountHeadingController::class,'updateAccountHeading']);
    Route::post('delete-heading',[AccountHeadingController::class,'deleteAccountHeading']);

    // Account Groups

    Route::post('add-account-group',[AccountGroupsController::class,'createAccountGroup']);
    Route::get('account-groups-list',[AccountGroupsController::class,'accountGroupsList']);
    Route::post('get-account-group',[AccountGroupsController::class,'edit']);
    Route::post('update-account-group',[AccountGroupsController::class,'updateAccountGroup']);
    Route::post('delete-account-group',[AccountGroupsController::class,'deleteAccountGroup']);

    // Accounts

    Route::post('add-account',[AccountsController::class,'createAccount']);
    Route::get('account-list',[AccountsController::class,'accountList']);
    Route::post('get-account',[AccountsController::class,'GetAccountbyId']);
    Route::post('update-account',[AccountsController::class,'updateAccount']);
    Route::post('delete-account',[AccountsController::class,'deleteAccount']);

    // Units

    Route::post('add-unit',[UnitsController::class,'createUnit']);
    Route::get('unit-list',[UnitsController::class,'unitList']);
    Route::post('get-unit',[UnitsController::class,'GetUnitbyId']);
    Route::post('update-unit',[UnitsController::class,'updateUnit']);
    Route::post('delete-unit',[UnitsController::class,'deleteUnit']);

    // Item Group

    Route::post('add-item-group',[ItemGroupsController::class,'createItemGroup']);
    Route::get('item-group-list',[ItemGroupsController::class,'itemGroupList']);
    Route::post('get-item-group',[ItemGroupsController::class,'GetItemGroupbyId']);
    Route::post('update-item-group',[ItemGroupsController::class,'updateItemGroup']);
    Route::post('delete-item-group',[ItemGroupsController::class,'deleteItemGroup']);

    // Items

    Route::post('add-item',[ManageItemsController::class,'createItem']);
    Route::get('item-list',[ManageItemsController::class,'itemList']);
    Route::post('get-item',[ManageItemsController::class,'GetItembyId']);
    Route::post('update-item',[ManageItemsController::class,'updateItem']);
    Route::post('delete-item',[ManageItemsController::class,'deleteItem']);

    //  Bill Sundry

    Route::post('add-bill-sundry',[BillSundrysController::class,'createBillSundry']);
    Route::get('bill-sundry-list',[BillSundrysController::class,'billSundryList']);
    Route::post('get-bill-sundry',[BillSundrysController::class,'GetBillSundrybyId']);
    Route::post('update-bill-sundry',[BillSundrysController::class,'updateBillSundry']);
    Route::post('delete-bill-sundry',[BillSundrysController::class,'deleteBillSundry']);


    /********* Transactions *********/

    // Sales

    Route::post('add-sales-voucher',[SalesController::class,'createSalesVoucher']);
    Route::get('sales-voucher-list',[SalesController::class,'SalesVoucherList']);
    Route::post('get-sales-voucher',[SalesController::class,'GetSalesVoucherbyId']);
    Route::post('update-sales-voucher',[SalesController::class,'updateSalesVoucher']);
    Route::post('delete-sales-voucher',[SalesController::class,'deleteSalesVoucher']);

    // Ajax Calculations

    Route::post('get-items-calculation',[AjaxController::class,'getItemDetails']);

    // Purchase

    Route::post('add-purchase-voucher',[PurchaseController::class,'createPurchaseVoucher']);
    Route::get('purchase-voucher-list',[PurchaseController::class,'PurchaseVoucherList']);
    Route::post('get-purchase-voucher',[PurchaseController::class,'GetPurchaseVoucherbyId']);
    Route::post('update-purchase-voucher',[PurchaseController::class,'updatePurchaseVoucher']);
    Route::post('delete-purchase-voucher',[PurchaseController::class,'deletePurchaseVoucher']);

    //Supplier Apis
    Route::post('add-purchase-vehicle-entry',[SupplierController::class,'addPurchaseVehicleEntry']);
    Route::get('purchase-vehicle-entry-list',[SupplierController::class,'purchaseVehicleEntryList']);
    Route::post('edit-purchase-vehicle-entry',[SupplierController::class,'purchaseVehicleEntryList']);
    Route::post('delete-purchase-vehicle-entry',[SupplierController::class,'GetSupplierbyId']);
    Route::post('supplier-head-list',[SupplierController::class,'updateSupplier']);
    Route::post('location-by-account',[SupplierController::class,'deleteSupplier']);
    Route::post('purchase-item-type',[SupplierController::class,'purchaseItemType']);
    Route::post('area-by-account',[SupplierController::class,'areaByAccount']);
    Route::post('contract-rate-by-area',[SupplierController::class,'contractRateByArea']);
   //Production Apis
    Route::post('pop-roll-items',[ProductionController::class,'popRollItems']);
    Route::post('add-pop-roll',[ProductionController::class,'addPopRoll']);
    Route::post('running-pop-roll',[ProductionController::class,'runningPopRoll']);
    Route::post('add-new-pop-roll-quality',[ProductionController::class,'addNewPopRollQuality']);
    Route::post('complete-pop-roll',[ProductionController::class,'completePopRoll']);
    Route::post('stop-pop-roll-machine',[ProductionController::class,'stopPopRollMachine']);
    Route::post('start-pop-roll-machine',[ProductionController::class,'startPopRollMachine']);
    Route::post('completed-pop-rolls',[ProductionController::class,'completedPopRolls']);
    Route::post('complete-pop-roll-summary',[ProductionController::class,'completePopRollSummary']);

    Route::post('start-pop-roll',[ProductionController::class,'startPopRoll']);
    Route::post('start-pop-roll-list',[ProductionController::class,'startPopRollList']);
    Route::post('store-pop-roll-reel-detail',[ProductionController::class,'storePopRollReelDetail']);
    Route::post('generated-pop-roll-reel-list',[ProductionController::class,'generatedPopRollReelList']);
    Route::post('cancel-generated-pop-roll',[ProductionController::class,'cancelGeneratedPopRoll']);
    Route::post('update-generated-pop-roll',[ProductionController::class,'updateGeneratedPopRoll']);
    Route::post('stop-machine-reason',[ProductionController::class,'stopMachineReason']);
 });