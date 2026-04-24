<?php

namespace App\Http\Controllers\ConfigurationSettings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfigurationSetting;
use Session;

class ConfigurationSettingsController extends Controller
{
    public function index()
    {
        $companyId = Session::get('user_company_id');

        /* ================= SALES CONFIG ================= */
        $salesConfig = ConfigurationSetting::where('company_id', $companyId)
            ->where('module', 'sales')
            ->first();

        $selectedSales = $salesConfig ? $salesConfig->config_json : [];
        $selectedSalesActions = [
            'add'  => $selectedSales['show_add']  ?? false,
            'view' => $selectedSales['show_view'] ?? false,
        ];
        /* ================= PURCHASE CONFIG ================= */
        $purchaseConfig = ConfigurationSetting::where('company_id', $companyId)
            ->where('module', 'purchase')
            ->first();

        $selectedPurchase = $purchaseConfig ? $purchaseConfig->config_json : [];
        $selectedPurchaseActions = [
            'add'  => $selectedPurchase['show_add']  ?? false,
            'view' => $selectedPurchase['show_view'] ?? false,
        ];

        $saleReturnConfig = ConfigurationSetting::where('company_id', $companyId)
            ->where('module', 'sale_return')
            ->first();

        $selectedSaleReturn = $saleReturnConfig ? $saleReturnConfig->config_json : [];
        $selectedSaleReturnActions = [
            'add'  => $selectedSaleReturn['show_add']  ?? false,
            'view' => $selectedSaleReturn['show_view'] ?? false,
        ];

        $purchaseReturnConfig = ConfigurationSetting::where('company_id', $companyId)
            ->where('module', 'purchase_return')
            ->first();

        $selectedPurchaseReturn = $purchaseReturnConfig ? $purchaseReturnConfig->config_json : [];
        $selectedPurchaseReturnActions = [
            'add'  => $selectedPurchaseReturn['show_add']  ?? false,
            'view' => $selectedPurchaseReturn['show_view'] ?? false,
        ];

        $paymentConfig = ConfigurationSetting::where('company_id', $companyId)
            ->where('module', 'payment')
            ->first();

        $selectedPayment = $paymentConfig ? $paymentConfig->config_json : [];
        $selectedPaymentActions = [
            'add'  => $selectedPayment['show_add']  ?? false,
            'view' => $selectedPayment['show_view'] ?? false,
        ];

        $receiptConfig = ConfigurationSetting::where('company_id', $companyId)
            ->where('module', 'receipt')
            ->first();

        $selectedReceipt = $receiptConfig ? $receiptConfig->config_json : [];
        $selectedReceiptActions = [
            'add'  => $selectedReceipt['show_add']  ?? false,
            'view' => $selectedReceipt['show_view'] ?? false,
        ];

        /* ================= JOURNAL CONFIG ================= */
        $journalConfig = ConfigurationSetting::where('company_id', $companyId)
            ->where('module', 'journal')
            ->first();

        $journalData = $journalConfig ? $journalConfig->config_json : [];

        $selectedJournalActions = [
            'add'  => $journalData['show_add'] ?? false,
            'view' => $journalData['show_view'] ?? false,
        ];

        /* ================= CONTRA CONFIG ================= */
        $contraConfig = ConfigurationSetting::where('company_id', $companyId)
            ->where('module', 'contra')
            ->first();

        $contraData = $contraConfig ? $contraConfig->config_json : [];

        $selectedContraActions = [
            'add'  => $contraData['show_add'] ?? false,
            'view' => $contraData['show_view'] ?? false,
        ];

        /* ================= STOCK JOURNAL CONFIG ================= */
        $stockJournalConfig = ConfigurationSetting::where('company_id', $companyId)
            ->where('module', 'stock_journal')
            ->first();

        $stockJournalData = $stockJournalConfig ? $stockJournalConfig->config_json : [];

        $selectedStockJournalActions = [
            'add'  => $stockJournalData['show_add'] ?? false,
            'view' => $stockJournalData['show_view'] ?? false,
        ];

        /* ================= STOCK TRANSFER CONFIG ================= */
        $stockTransferConfig = ConfigurationSetting::where('company_id', $companyId)
            ->where('module', 'stock_transfer')
            ->first();

        $stockTransferData = $stockTransferConfig ? $stockTransferConfig->config_json : [];

        $selectedStockTransferActions = [
            'add'  => $stockTransferData['show_add'] ?? false,
            'view' => $stockTransferData['show_view'] ?? false,
        ];


        return view('ConfigurationSettings.index', compact(
            'selectedSales',
            'selectedSalesActions',

            'selectedPurchase',
            'selectedPurchaseActions',

            'selectedSaleReturn',
            'selectedSaleReturnActions',

            'selectedPurchaseReturn',
            'selectedPurchaseReturnActions',

            'selectedPayment',
            'selectedPaymentActions',

            'selectedReceipt',
            'selectedReceiptActions',

            'selectedJournalActions',
            'selectedContraActions',
            'selectedStockJournalActions',
            'selectedStockTransferActions'
        ));

    }

    public function save(Request $request)
    {
        $companyId = Session::get('user_company_id');

        /* ================= SALES OPTIONS ================= */
        $salesOptions = [
            'total_sales_count',
            'total_sales_qty',
            'total_sales_amount',
            'sales_with_gst_amount',
            'sales_without_gst_amount',
        ];

        $selectedSales = $request->input('sales', []);

        $salesConfig = [];
        foreach ($salesOptions as $opt) {
            $salesConfig[$opt] = in_array($opt, $selectedSales);
        }
        $salesActions = $request->input('sales_actions', []);
        $salesConfig['show_add']  = isset($salesActions['add']);
        $salesConfig['show_view'] = isset($salesActions['view']);

        ConfigurationSetting::updateOrCreate(
            [
                'company_id' => $companyId,
                'module'     => 'sales',
            ],
            [
                'config_json' => $salesConfig,
            ]
        );

        /* ================= PURCHASE OPTIONS ================= */
        $purchaseOptions = [
            'total_purchase_count',
            'total_purchase_qty',
            'total_purchase_amount',
            'purchase_with_gst_amount',
            'purchase_without_gst_amount',
        ];

        $selectedPurchase = $request->input('purchase', []);

        $purchaseConfig = [];
        foreach ($purchaseOptions as $opt) {
            $purchaseConfig[$opt] = in_array($opt, $selectedPurchase);
        }
        $purchaseActions = $request->input('purchase_actions', []);
        $purchaseConfig['show_add']  = isset($purchaseActions['add']);
        $purchaseConfig['show_view'] = isset($purchaseActions['view']);

        ConfigurationSetting::updateOrCreate(
            [
                'company_id' => $companyId,
                'module'     => 'purchase',
            ],
            [
                'config_json' => $purchaseConfig,
            ]
        );
        /* ================= SALE RETURN OPTIONS ================= */
        $saleReturnOptions = [
            'total_sale_return_count',
            'total_sale_return_qty',
            'total_sale_return_amount',
            'sale_return_with_gst_amount',
            'sale_return_without_gst_amount',
        ];

        $selectedSaleReturn = $request->input('sale_return', []);

        $saleReturnConfig = [];
        foreach ($saleReturnOptions as $opt) {
            $saleReturnConfig[$opt] = in_array($opt, $selectedSaleReturn);
        }
        $saleReturnActions = $request->input('sale_return_actions', []);
        $saleReturnConfig['show_add']  = isset($saleReturnActions['add']);
        $saleReturnConfig['show_view'] = isset($saleReturnActions['view']);

        ConfigurationSetting::updateOrCreate(
            [
                'company_id' => $companyId,
                'module'     => 'sale_return',
            ],
            [
                'config_json' => $saleReturnConfig,
            ]
        );

        /* ================= PURCHASE RETURN OPTIONS ================= */
        $purchaseReturnOptions = [
            'total_purchase_return_count',
            'total_purchase_return_qty',
            'total_purchase_return_amount',
            'purchase_return_with_gst_amount',
            'purchase_return_without_gst_amount',
        ];

        $selectedPurchaseReturn = $request->input('purchase_return', []);

        $purchaseReturnConfig = [];
        foreach ($purchaseReturnOptions as $opt) {
            $purchaseReturnConfig[$opt] = in_array($opt, $selectedPurchaseReturn);
        }
        $purchaseReturnActions = $request->input('purchase_return_actions', []);
        $purchaseReturnConfig['show_add']  = isset($purchaseReturnActions['add']);
        $purchaseReturnConfig['show_view'] = isset($purchaseReturnActions['view']);

        ConfigurationSetting::updateOrCreate(
            [
                'company_id' => $companyId,
                'module'     => 'purchase_return',
            ],
            [
                'config_json' => $purchaseReturnConfig,
            ]
        );

        /* ================= PAYMENT OPTIONS ================= */
        $paymentOptions = [
            'total_paid_count',
            'total_received_count',
            'total_paid_amount',
            'total_received_amount',
        ];

        $selectedPayment = $request->input('payment', []);

        $paymentConfig = [];
        foreach ($paymentOptions as $opt) {
            $paymentConfig[$opt] = in_array($opt, $selectedPayment);
        }
        $paymentActions = $request->input('payment_actions', []);
        $paymentConfig['show_add']  = isset($paymentActions['add']);
        $paymentConfig['show_view'] = isset($paymentActions['view']);

        ConfigurationSetting::updateOrCreate(
            [
                'company_id' => $companyId,
                'module'     => 'payment',
            ],
            [
                'config_json' => $paymentConfig,
            ]
        );

        /* ================= RECEIPT OPTIONS ================= */
        $receiptOptions = [
            'total_received_count',
            'total_paid_count',
            'total_received_amount',
            'total_paid_amount',
        ];

        $selectedReceipt = $request->input('receipt', []);

        $receiptConfig = [];
        foreach ($receiptOptions as $opt) {
            $receiptConfig[$opt] = in_array($opt, $selectedReceipt);
        }
        $receiptActions = $request->input('receipt_actions', []);
        $receiptConfig['show_add']  = isset($receiptActions['add']);
        $receiptConfig['show_view'] = isset($receiptActions['view']);

        ConfigurationSetting::updateOrCreate(
            [
                'company_id' => $companyId,
                'module'     => 'receipt',
            ],
            [
                'config_json' => $receiptConfig,
            ]
        );

        $journalActions = $request->input('journal_actions');

        ConfigurationSetting::updateOrCreate(
            ['company_id' => $companyId, 'module' => 'journal'],
            [
                'config_json' => [
                    'show_add'  => is_array($journalActions) && array_key_exists('add', $journalActions),
                    'show_view' => is_array($journalActions) && array_key_exists('view', $journalActions),
                ],
            ]
        );

        $contraActions = $request->input('contra_actions');

        ConfigurationSetting::updateOrCreate(
            ['company_id' => $companyId, 'module' => 'contra'],
            [
                'config_json' => [
                    'show_add'  => is_array($contraActions) && array_key_exists('add', $contraActions),
                    'show_view' => is_array($contraActions) && array_key_exists('view', $contraActions),
                ],
            ]
        );

        $stockJournalActions = $request->input('stock_journal_actions');

        ConfigurationSetting::updateOrCreate(
            ['company_id' => $companyId, 'module' => 'stock_journal'],
            [
                'config_json' => [
                    'show_add'  => is_array($stockJournalActions) && array_key_exists('add', $stockJournalActions),
                    'show_view' => is_array($stockJournalActions) && array_key_exists('view', $stockJournalActions),
                ],
            ]
        );

        $stockTransferActions = $request->input('stock_transfer_actions');

        ConfigurationSetting::updateOrCreate(
            ['company_id' => $companyId, 'module' => 'stock_transfer'],
            [
                'config_json' => [
                    'show_add'  => is_array($stockTransferActions) && array_key_exists('add', $stockTransferActions),
                    'show_view' => is_array($stockTransferActions) && array_key_exists('view', $stockTransferActions),
                ],
            ]
        );


            return back()->with('success', 'Configuration saved successfully.');
        }

}
