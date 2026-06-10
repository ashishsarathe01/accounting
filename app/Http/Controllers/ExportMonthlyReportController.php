<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Accounts;
use App\Models\Companies;
use App\Models\AccountLedger;
use Carbon\Carbon;

use DB;
use Session;
use App\Helpers\CommonHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class ExportMonthlyReportController extends Controller
{
    
    public function index()
    {
        return view(
            'ExportMonthlyReport'
        );
    }


public function download(Request $request)
{
    $reportType = $request->report_type;
    $month = $request->month;

    $lastDate = date(
        'Y-m-t',
        strtotime($month)
    );

if($reportType == 'complete')
{

    $spreadsheet =
        new Spreadsheet();
$company_id =
    Session::get('user_company_id');


$stockType =
    $request->stock_type;


$bank = DB::table('banks')

    ->where(
        'id',
        $request->bank_id
    )

    ->first();


$company = DB::table('companies')

    ->where(
        'id',
        $company_id
    )

    ->first();


$reportData = [];

$grandTotal = 0;

$sr = 1;

// =========================================
// PART - B DEBTORS
// =========================================

$upto90Total = 0;

$days91to180Total = 0;

$moreThan180Total = 0;


// =========================================
// SALES DURING FINANCIAL YEAR
// =========================================

$selectedMonthStart =
    date(
        'Y-m-01',
        strtotime($lastDate)
    );


$financialYearStartMonth =
    date(
        'm',
        strtotime($lastDate)
    ) >= 4
    ?
    date(
        'Y',
        strtotime($lastDate)
    )
    :
    (
        date(
            'Y',
            strtotime($lastDate)
        ) - 1
    );


$financialYearStart =
    $financialYearStartMonth
    .
    '-04-01';


// ===== SALES UPTO LAST MONTH =====

// =========================================
// SALES UPTO LAST MONTH
// =========================================


$salesOpening = 0;


// ===== OPENING ENTRY =====

$openingEntry = DB::table('account_ledger')

    ->where(
        'account_id',
        35
    )

    ->where(
        'company_id',
        $company_id
    )

    ->where(
        'entry_type',
        '-1'
    )

    ->where(
        'delete_status',
        '0'
    )

    ->first();


if($openingEntry)
{
    if($openingEntry->credit != "")
    {
        $salesOpening =
            -$openingEntry->credit;
    }
    else if($openingEntry->debit != "")
    {
        $salesOpening =
            $openingEntry->debit;
    }
}


// ===== LEDGER TOTAL =====

$salesLedger = DB::table('account_ledger')

    ->selectRaw(
        '
        SUM(debit) as debit,
        SUM(credit) as credit
        '
    )

    ->where(
        'account_id',
        35
    )

    ->where(
        'company_id',
        $company_id
    )

    ->where(
        'txn_date',
        '<',
        $selectedMonthStart
    )

    ->where(
        'status',
        '1'
    )

    ->where(
        'delete_status',
        '0'
    )

    ->first();


// ===== BALANCE =====

$salesUptoLastMonth =
    $salesOpening
    +
    (
        ($salesLedger->debit ?? 0)
        -
        ($salesLedger->credit ?? 0)
    );


// ===== ABS =====

$salesUptoLastMonth =
    abs(
        round(
            $salesUptoLastMonth,
            2
        )
    );


// ===== IN LACS =====

$salesUptoLastMonthLacs =
    round(
        $salesUptoLastMonth / 100000,
        2
    );


// =========================================
// SALES DURING MONTH
// =========================================


$salesDuringMonthLedger = DB::table('account_ledger')

    ->selectRaw(
        '
        SUM(debit) as debit,
        SUM(credit) as credit
        '
    )

    ->where(
        'account_id',
        35
    )

    ->where(
        'company_id',
        $company_id
    )

    ->whereBetween(
        'txn_date',
        [
            $selectedMonthStart,
            $lastDate
        ]
    )

    ->where(
        'status',
        '1'
    )

    ->where(
        'delete_status',
        '0'
    )

    ->first();


// ===== MONTH BALANCE =====

$salesDuringMonth =
    ($salesDuringMonthLedger->debit ?? 0)
    -
    ($salesDuringMonthLedger->credit ?? 0);


// ===== ABS =====

$salesDuringMonth =
    abs(
        round(
            $salesDuringMonth,
            2
        )
    );


// ===== IN LACS =====

$salesDuringMonthLacs =
    round(
        $salesDuringMonth / 100000,
        2
    );


// ===== TOTAL SALES =====

$totalSales =
    $salesUptoLastMonth
    +
    $salesDuringMonth;


// ===== CONVERT TO LACS =====

$salesUptoLastMonthLacs =
    round(
        $salesUptoLastMonth / 100000,
        2
    );

$salesDuringMonthLacs =
    round(
        $salesDuringMonth / 100000,
        2
    );

$totalSalesLacs =
    round(
        $totalSales / 100000,
        2
    );


// =========================================
// DEBTOR GROUPS
// =========================================

$top_groups_list = [11];

$all_groups_list = [];


foreach ($top_groups_list as $gid)
{
    $all_groups_list[] = $gid;

    $all_groups_list = array_merge(
        $all_groups_list,

        CommonHelper::getAllChildGroupIds(
            $gid,
            $company_id
        )
    );
}


$all_groups_list = array_unique(
    $all_groups_list
);


// =========================================
// ACCOUNTS
// =========================================

$accounts = DB::table('accounts')

    ->where(
        'company_id',
        $company_id
    )

    ->where(
        'delete',
        '0'
    )

    ->whereIn(
        'under_group',
        $all_groups_list
    )

    ->get();


// =========================================
// SALES
// =========================================

$allSales = DB::table('sales')

    ->where(
        'company_id',
        $company_id
    )

    ->where(
        'date',
        '<=',
        $lastDate
    )

    ->where(
        'status',
        '1'
    )

    ->where(
        'delete',
        '0'
    )

    ->get()

    ->groupBy('party');


// =========================================
// LOOP
// =========================================

foreach ($accounts as $acc)
{

    $ledger = DB::table('account_ledger')

        ->where(
            'company_id',
            $company_id
        )

        ->where(
            'account_id',
            $acc->id
        )

        ->where(
            'txn_date',
            '<=',
            $lastDate
        )

        ->where(
            'delete_status',
            '0'
        )

        ->selectRaw(
            '
            SUM(debit) as dr,
            SUM(credit) as cr
            '
        )

        ->first();


    $open = DB::table('account_ledger')

        ->where(
            'company_id',
            $company_id
        )

        ->where(
            'account_id',
            $acc->id
        )

        ->where(
            'entry_type',
            '-1'
        )

        ->selectRaw(
            '
            SUM(debit) as dr,
            SUM(credit) as cr
            '
        )

        ->first();


    $total =
        ($ledger->dr - $ledger->cr)
        +
        ($open->dr - $open->cr);


    if($total <= 0)
    {
        continue;
    }


    $agingRows = [];


    if(($open->dr - $open->cr) > 0)
    {
        $agingRows[] = [

            'age' =>
                Carbon::parse(
                    DB::table('companies')
                    ->where('id', $company_id)
                    ->value('books_start_from')
                )->diffInDays($lastDate),

            'amount' =>
                ($open->dr - $open->cr)
        ];
    }


    $sales =
        $allSales[$acc->id]
        ??
        collect();


    foreach ($sales as $inv)
    {
        $agingRows[] = [

            'age' =>
                Carbon::parse(
                    $inv->date
                )->diffInDays($lastDate),

            'amount' =>
                $inv->total
        ];
    }


    usort($agingRows, function ($a, $b) {
        return $b['age'] <=> $a['age'];
    });


    $originalTotal =
        array_sum(
            array_column(
                $agingRows,
                'amount'
            )
        );


    $payment =
        $originalTotal - $total;


    foreach ($agingRows as $key => $row)
    {
        if($payment <= 0)
        {
            break;
        }

        $deduct =
            min(
                $row['amount'],
                $payment
            );

        $agingRows[$key]['amount']
            -=
            $deduct;

        $payment -= $deduct;
    }


    foreach ($agingRows as $row)
    {

        if($row['amount'] <= 0)
        {
            continue;
        }

        $age = (int) $row['age'];


        if($age <= 90)
        {
            $upto90Total +=
                $row['amount'];
        }

        else if(
            $age >= 91
            &&
            $age <= 180
        )
        {
            $days91to180Total +=
                $row['amount'];
        }

        else if($age > 180)
        {
            $moreThan180Total +=
                $row['amount'];
        }
    }
}


$debtorsGrandTotal =
    $upto90Total
    +
    $days91to180Total
    +
    $moreThan180Total;



// =========================================
// CREDITORS TOTAL
// =========================================


$creditorsTotal = 0;


$sundry_root_groups = [3];

$sundry_group_ids = [];


foreach ($sundry_root_groups as $gid)
{
    $sundry_group_ids[] = $gid;

    $sundry_group_ids = array_merge(
        $sundry_group_ids,

        CommonHelper::getAllChildGroupIds(
            $gid,
            $company_id
        )
    );
}


$sundry_group_ids = array_unique(
    $sundry_group_ids
);


$creditorAccounts = Accounts::whereIn(
        'company_id',
        [
            $company_id,
            0
        ]
    )

    ->where('delete', '0')

    ->where('status', '1')

    ->whereIn(
        'under_group',
        $sundry_group_ids
    )

    ->get();


foreach($creditorAccounts as $account)
{

    $ledger = DB::table('account_ledger')

        ->selectRaw(
            '
            SUM(debit) as total_debit,
            SUM(credit) as total_credit
            '
        )

        ->where(
            'account_id',
            $account->id
        )

        ->where(
            'company_id',
            $company_id
        )

        ->where(
            'status',
            '1'
        )

        ->where(
            'delete_status',
            '0'
        )

        ->whereDate(
            'txn_date',
            '<=',
            $lastDate
        )

        ->first();


    $balance = 0;


    if($ledger)
    {
        $balance =
            ($ledger->total_debit ?? 0)
            -
            ($ledger->total_credit ?? 0);
    }


    $openingEntry = DB::table('account_ledger')

        ->where(
            'account_id',
            $account->id
        )

        ->where(
            'company_id',
            $company_id
        )

        ->where(
            'entry_type',
            '-1'
        )

        ->where(
            'delete_status',
            '0'
        )

        ->first();


    if($openingEntry)
    {
        if(!empty($openingEntry->credit))
        {
            $balance =
                $balance
                -
                $openingEntry->credit;
        }
        else if(!empty($openingEntry->debit))
        {
            $balance =
                $balance
                +
                $openingEntry->debit;
        }
    }


    if(round($balance, 2) == 0)
    {
        continue;
    }


    $creditorsTotal += $balance;
}


$creditorsTotal =
    abs(
        round(
            $creditorsTotal,
            2
        )
    );

// =========================================
// PURCHASE DURING FINANCIAL YEAR
// =========================================


$selectedMonthStart =
    date(
        'Y-m-01',
        strtotime($lastDate)
    );


$financialYearStartMonth =
    date(
        'm',
        strtotime($lastDate)
    ) >= 4
    ?
    date(
        'Y',
        strtotime($lastDate)
    )
    :
    (
        date(
            'Y',
            strtotime($lastDate)
        ) - 1
    );


$financialYearStart =
    $financialYearStartMonth
    .
    '-04-01';


// =========================================
// PURCHASE UPTO LAST MONTH
// =========================================


$purchaseOpening = 0;


// ===== OPENING ENTRY =====

$openingEntry = DB::table('account_ledger')

    ->where(
        'account_id',
        36
    )

    ->where(
        'company_id',
        $company_id
    )

    ->where(
        'entry_type',
        '-1'
    )

    ->where(
        'delete_status',
        '0'
    )

    ->first();


if($openingEntry)
{
    if($openingEntry->credit != "")
    {
        $purchaseOpening =
            -$openingEntry->credit;
    }
    else if($openingEntry->debit != "")
    {
        $purchaseOpening =
            $openingEntry->debit;
    }
}


// ===== LEDGER TOTAL =====

$purchaseLedger = DB::table('account_ledger')

    ->selectRaw(
        '
        SUM(debit) as debit,
        SUM(credit) as credit
        '
    )

    ->where(
        'account_id',
        36
    )

    ->where(
        'company_id',
        $company_id
    )

    ->where(
        'txn_date',
        '<',
        $selectedMonthStart
    )

    ->where(
        'status',
        '1'
    )

    ->where(
        'delete_status',
        '0'
    )

    ->first();


// ===== BALANCE =====

$purchaseUptoLastMonth =
    $purchaseOpening
    +
    (
        ($purchaseLedger->debit ?? 0)
        -
        ($purchaseLedger->credit ?? 0)
    );


// ===== ABS =====

$purchaseUptoLastMonth =
    abs(
        round(
            $purchaseUptoLastMonth,
            2
        )
    );


// ===== IN LACS =====

$purchaseUptoLastMonthLacs =
    round(
        $purchaseUptoLastMonth / 100000,
        2
    );


// =========================================
// PURCHASE DURING MONTH
// =========================================


$purchaseDuringMonthLedger = DB::table('account_ledger')

    ->selectRaw(
        '
        SUM(debit) as debit,
        SUM(credit) as credit
        '
    )

    ->where(
        'account_id',
        36
    )

    ->where(
        'company_id',
        $company_id
    )

    ->whereBetween(
        'txn_date',
        [
            $selectedMonthStart,
            $lastDate
        ]
    )

    ->where(
        'status',
        '1'
    )

    ->where(
        'delete_status',
        '0'
    )

    ->first();


// ===== MONTH BALANCE =====

$purchaseDuringMonth =
    ($purchaseDuringMonthLedger->debit ?? 0)
    -
    ($purchaseDuringMonthLedger->credit ?? 0);


// ===== ABS =====

$purchaseDuringMonth =
    abs(
        round(
            $purchaseDuringMonth,
            2
        )
    );


// ===== IN LACS =====

$purchaseDuringMonthLacs =
    round(
        $purchaseDuringMonth / 100000,
        2
    );


// ===== TOTAL PURCHASE =====

$totalPurchase =
    $purchaseUptoLastMonth
    +
    $purchaseDuringMonth;


// ===== CONVERT TO LACS =====

$purchaseUptoLastMonthLacs =
    round(
        $purchaseUptoLastMonth / 100000,
        2
    );

$purchaseDuringMonthLacs =
    round(
        $purchaseDuringMonth / 100000,
        2
    );

$totalPurchaseLacs =
    round(
        $totalPurchase / 100000,
        2
    );
    // =========================================
    // ITEM WISE
    // =========================================

    if($stockType == 'item')
    {

        $sub = DB::table('item_averages')

            ->select(
                DB::raw(
                    'MAX(id) as latest_id'
                )
            )

            ->where(
                'stock_date',
                '<=',
                $lastDate
            )

            ->where(
                'company_id',
                $company_id
            )

            ->groupBy('item_id');


        $items = DB::table('item_averages')

            ->join(
                'manage_items',
                'item_averages.item_id',
                '=',
                'manage_items.id'
            )

            ->whereIn(
                'item_averages.id',
                $sub
            )

            ->select(
                'manage_items.name as item_name',
                'item_averages.average_weight as qty',
                'item_averages.amount as value'
            )

            ->orderBy(
                'manage_items.name'
            )

            ->get();


        foreach($items as $item)
        {

            if(
                round($item->qty,2) == 0
                &&
                round($item->value,2) == 0
            )
            {
                continue;
            }


            $rate = 0;

if(
    (float)$item->qty > 0
)
{
    $rate = round(
        (
            (float)$item->value
            /
            (float)$item->qty
        ),
        2
    );
}


            $grandTotal +=
                $item->value;


            $reportData[] = [

                'sr_no' =>
                    $sr++,

                'name' =>
                    $item->item_name,

                'qty' =>
                    $item->qty,

                'rate' =>
                    $rate,

                'value' =>
                    $item->value
            ];
        }
    }


    // =========================================
    // GROUP WISE
    // =========================================

    if($stockType == 'group')
    {

        $sub = DB::table('item_averages')

            ->select(
                DB::raw(
                    'MAX(id) as latest_id'
                )
            )

            ->where(
                'stock_date',
                '<=',
                $lastDate
            )

            ->where(
                'company_id',
                $company_id
            )

            ->groupBy('item_id');


        $items = DB::table('item_averages')

            ->join(
                'manage_items',
                'item_averages.item_id',
                '=',
                'manage_items.id'
            )

            ->join(
                'item_groups',
                'manage_items.g_name',
                '=',
                'item_groups.id'
            )

            ->whereIn(
                'item_averages.id',
                $sub
            )

            ->select(
                'item_groups.group_name',
                DB::raw(
                    'SUM(item_averages.average_weight) as qty'
                ),
                DB::raw(
                    'SUM(item_averages.amount) as value'
                )
            )

            ->groupBy(
                'item_groups.group_name'
            )

            ->orderBy(
                'item_groups.group_name'
            )

            ->get();


        foreach($items as $item)
        {

            if(
                round($item->qty,2) == 0
                &&
                round($item->value,2) == 0
            )
            {
                continue;
            }


            $rate = 0;

if(
    (float)$item->qty > 0
)
{
    $rate = round(
        (
            (float)$item->value
            /
            (float)$item->qty
        ),
        2
    );
}


            $grandTotal +=
                $item->value;


            $reportData[] = [

                'sr_no' =>
                    $sr++,

                'name' =>
                    $item->group_name,

                'qty' =>
                    $item->qty,

                'rate' =>
                    $rate,

                'value' =>
                    $item->value
            ];
        }
    }
// =========================================
// SHEET 1
// =========================================

$sheet1 =
    $spreadsheet->getActiveSheet();

$sheet1->setTitle(
    'Stock Report'
);

// =========================================
// STOCK REPORT HEADER
// =========================================


// ===== COLUMN WIDTH =====

$sheet1->getColumnDimension('A')->setWidth(10);

$sheet1->getColumnDimension('B')->setWidth(35);

$sheet1->getColumnDimension('C')->setWidth(18);

$sheet1->getColumnDimension('D')->setWidth(18);

$sheet1->getColumnDimension('E')->setWidth(18);

$sheet1->getColumnDimension('F')->setWidth(18);

$sheet1->getColumnDimension('G')->setWidth(20);


// ===== PART A =====

$sheet1->mergeCells('F1:G1');

$sheet1->setCellValue(
    'F1',
    'PART - A'
);

$sheet1->getStyle('F1:G1')->getFont()
    ->setBold(true)
    ->setSize(14);

$sheet1->getStyle('F1:G1')->getAlignment()
    ->setHorizontal(
        \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
    );


// ===== ANNEXURE =====

$sheet1->mergeCells('F2:G2');

$sheet1->setCellValue(
    'F2',
    'ANNEXURE'
);

$sheet1->getStyle('F2:G2')->getFont()
    ->setBold(true)
    ->setSize(14);

$sheet1->getStyle('F2:G2')->getAlignment()
    ->setHorizontal(
        \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
    );


// ===== BANK =====

$sheet1->mergeCells('A4:G4');

$sheet1->setCellValue(
    'A4',
    $bank->bank_name
);

$sheet1->getStyle('A4:G4')->getFont()
    ->setBold(true)
    ->setSize(22);

$sheet1->getStyle('A4:G4')->getAlignment()
    ->setHorizontal(
        \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    );


// ===== STOCK STATEMENT =====

$sheet1->mergeCells('A5:G5');

$sheet1->setCellValue(
    'A5',
    'STOCK STATEMENT (REVISED PROFORMA)'
);

$sheet1->getStyle('A5:G5')->getFont()
    ->setBold(true)
    ->setSize(18);

$sheet1->getStyle('A5:G5')->getAlignment()
    ->setHorizontal(
        \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    );


// ===== BORROWER =====

$sheet1->mergeCells('A6:G6');

$sheet1->setCellValue(
    'A6',
    '(TO BE SUBMITTED BY THE BORROWER)'
);

$sheet1->getStyle('A6:G6')->getFont()
    ->setBold(true)
    ->setSize(14);

$sheet1->getStyle('A6:G6')->getAlignment()
    ->setHorizontal(
        \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    );


// ===== PERIODICITY =====

$sheet1->mergeCells('A8:G8');

$sheet1->setCellValue(
    'A8',
    'Preodicity of submission of stock statement : Fortnightly / Monthly / quarterly / half yearly.'
);

$sheet1->getStyle('A8:G8')->getFont()
    ->setSize(13);


// ===== STATEMENT =====

$sheet1->mergeCells('A10:G10');

$sheet1->setCellValue(
    'A10',

    'Statement as on '
    .
    strtoupper(
        date(
            'd-M-y',
            strtotime($lastDate)
        )
    )
    .
    ' belonging to M/s '
    .
    $company->company_name
    .
    ' '
    .
    $company->address
    .
    ' Hypothecated as security with '
    .
    $bank->bank_name
    .
    ', '
    .
    $bank->branch
);

$sheet1->getStyle('A10:G10')->getAlignment()
    ->setWrapText(true);

$sheet1->getStyle('A10:G10')->getFont()
    ->setSize(13);

$sheet1->getRowDimension(10)
    ->setRowHeight(45);


// ===== ACCOUNT =====

$sheet1->mergeCells('A12:C12');

$sheet1->setCellValue(
    'A12',
    'A/c No. : '.$bank->account_no
);


$sheet1->mergeCells('D12:E12');

$sheet1->setCellValue(
    'D12',
    'Facility'
);


$sheet1->mergeCells('F12:G12');

$sheet1->setCellValue(
    'F12',
    'Cash Credit'
);


// ===== LIMIT =====

$sheet1->mergeCells('A14:G14');

$sheet1->setCellValue(
    'A14',
    'Limit Rs. :'
);

$sheet1->getStyle('A14:G14')->getFont()
    ->setBold(true);


// =========================================
// STOCK TABLE
// =========================================


// ===== HEADERS =====

$sheet1->setCellValue('A16', 'Sr No');

$sheet1->setCellValue('B16', 'Particulars of Goods');

$sheet1->setCellValue('C16', 'Where Lying');

$sheet1->setCellValue('D16', 'Quantity In Kgs');

$sheet1->setCellValue('E16', 'Rate');

$sheet1->setCellValue('F16', 'Value');

$sheet1->setCellValue('G16', 'Remarks');


$sheet1->getStyle('A16:G16')->getFont()
    ->setBold(true);


$sheet1->getStyle('A16:G16')->getAlignment()
    ->setHorizontal(
        \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    );


// ===== BORDER =====

$sheet1->getStyle('A16:G16')
    ->getBorders()
    ->getAllBorders()
    ->setBorderStyle(
        \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
    );
// =========================================
// STOCK DATA ROWS
// =========================================

$rowNumber = 17;


foreach($reportData as $row)
{

    $sheet1->setCellValue(
        'A'.$rowNumber,
        $row['sr_no']
    );

    $sheet1->setCellValue(
        'B'.$rowNumber,
        $row['name']
    );

    $sheet1->setCellValue(
        'C'.$rowNumber,
        'FACTORY'
    );

    $sheet1->setCellValue(
        'D'.$rowNumber,
        FormatIndianNumber(
            $row['qty'],
            2
        )
    );

    $sheet1->setCellValue(
        'E'.$rowNumber,
        FormatIndianNumber(
            $row['rate'],
            2
        )
    );

    $sheet1->setCellValue(
        'F'.$rowNumber,
        FormatIndianNumber(
            $row['value'],
            2
        )
    );

    $sheet1->setCellValue(
        'G'.$rowNumber,
        ''
    );


    // ===== ALIGN =====

    $sheet1->getStyle(
        'D'.$rowNumber.':F'.$rowNumber
    )->getAlignment()->setHorizontal(
        \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
    );


    // ===== BORDERS =====

    $sheet1->getStyle(
        'A'.$rowNumber.':G'.$rowNumber
    )
    ->getBorders()
    ->getAllBorders()
    ->setBorderStyle(
        \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
    );


    $rowNumber++;
}


// =========================================
// TOTAL ROW
// =========================================

$sheet1->mergeCells(
    'A'.$rowNumber.':E'.$rowNumber
);

$sheet1->setCellValue(
    'A'.$rowNumber,
    'TOTAL'
);

$sheet1->setCellValue(
    'F'.$rowNumber,

    FormatIndianNumber(
        $grandTotal,
        2
    )
);


$sheet1->getStyle(
    'A'.$rowNumber.':G'.$rowNumber
)->getFont()->setBold(true);


$sheet1->getStyle(
    'A'.$rowNumber.':G'.$rowNumber
)
->getBorders()
->getAllBorders()
->setBorderStyle(
    \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
    );


// =========================================
// FOOTER
// =========================================

$rowNumber += 2;


$sheet1->mergeCells(
    'A'.$rowNumber.':G'.$rowNumber
);

$sheet1->setCellValue(
    'A'.$rowNumber,
    '(Extra Sheet to be attached in case of Need)'
);


$rowNumber++;


$sheet1->mergeCells(
    'A'.$rowNumber.':G'.$rowNumber
);

$sheet1->setCellValue(
    'A'.$rowNumber,
    'PNB 938 revised (8/2009)'
);
// =========================================
// PART - B
// =========================================


$rowNumber += 3;


// ===== PART B =====

$sheet1->mergeCells(
    'A'.$rowNumber.':G'.$rowNumber
);

$sheet1->setCellValue(
    'A'.$rowNumber,
    'PART-B'
);

$sheet1->getStyle(
    'A'.$rowNumber
)->getFont()
 ->setBold(true)
 ->setSize(18);


$rowNumber += 2;


// ===== TITLE =====

$sheet1->mergeCells(
    'A'.$rowNumber.':G'.$rowNumber
);

$sheet1->setCellValue(
    'A'.$rowNumber,
    'Sundry Debtors (Receivables) @'
);

$sheet1->getStyle(
    'A'.$rowNumber
)->getFont()
 ->setBold(true)
 ->setSize(16);


$rowNumber += 2;


// =========================================
// DEBTORS TABLE
// =========================================


// ===== HEADERS =====

$sheet1->setCellValue(
    'A'.$rowNumber,
    'S.NO'
);

$sheet1->mergeCells(
    'B'.$rowNumber.':C'.$rowNumber
);

$sheet1->setCellValue(
    'B'.$rowNumber,
    'List of Debtors as per Annexure'
);

$sheet1->setCellValue(
    'D'.$rowNumber,
    'Amount (Rs.)'
);


$sheet1->getStyle(
    'A'.$rowNumber.':D'.$rowNumber
)->getFont()->setBold(true);


$sheet1->getStyle(
    'A'.$rowNumber.':D'.$rowNumber
)->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
);


$sheet1->getStyle(
    'A'.$rowNumber.':D'.$rowNumber
)->getBorders()->getAllBorders()->setBorderStyle(
    \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
);


$rowNumber++;


// ===== ROW 1 =====

$sheet1->setCellValue(
    'A'.$rowNumber,
    'I'
);

$sheet1->mergeCells(
    'B'.$rowNumber.':C'.$rowNumber
);

$sheet1->setCellValue(
    'B'.$rowNumber,
    'Upto 90 Days'
);

$sheet1->setCellValue(
    'D'.$rowNumber,
    FormatIndianNumber(
        $upto90Total,
        2
    )
);


$sheet1->getStyle(
    'D'.$rowNumber
)->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
);


$sheet1->getStyle(
    'A'.$rowNumber.':D'.$rowNumber
)->getBorders()->getAllBorders()->setBorderStyle(
    \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
);


$rowNumber++;


// ===== ROW 2 =====

$sheet1->setCellValue(
    'A'.$rowNumber,
    'II'
);

$sheet1->mergeCells(
    'B'.$rowNumber.':C'.$rowNumber
);

$sheet1->setCellValue(
    'B'.$rowNumber,
    '>90 Days To 180 Days'
);

$sheet1->setCellValue(
    'D'.$rowNumber,
    FormatIndianNumber(
        $days91to180Total,
        2
    )
);


$sheet1->getStyle(
    'D'.$rowNumber
)->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
);


$sheet1->getStyle(
    'A'.$rowNumber.':D'.$rowNumber
)->getBorders()->getAllBorders()->setBorderStyle(
    \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
);


$rowNumber++;


// ===== ROW 3 =====

$sheet1->setCellValue(
    'A'.$rowNumber,
    'III'
);

$sheet1->mergeCells(
    'B'.$rowNumber.':C'.$rowNumber
);

$sheet1->setCellValue(
    'B'.$rowNumber,
    '>180 Days'
);

$sheet1->setCellValue(
    'D'.$rowNumber,
    FormatIndianNumber(
        $moreThan180Total,
        2
    )
);


$sheet1->getStyle(
    'D'.$rowNumber
)->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
);


$sheet1->getStyle(
    'A'.$rowNumber.':D'.$rowNumber
)->getBorders()->getAllBorders()->setBorderStyle(
    \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
);


$rowNumber++;


// ===== TOTAL =====

$sheet1->mergeCells(
    'A'.$rowNumber.':C'.$rowNumber
);

$sheet1->setCellValue(
    'A'.$rowNumber,
    'TOTAL'
);

$sheet1->setCellValue(
    'D'.$rowNumber,
    FormatIndianNumber(
        $debtorsGrandTotal,
        2
    )
);


$sheet1->getStyle(
    'D'.$rowNumber
)->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
);


$sheet1->getStyle(
    'A'.$rowNumber.':D'.$rowNumber
)->getFont()->setBold(true);


$sheet1->getStyle(
    'A'.$rowNumber.':D'.$rowNumber
)->getBorders()->getAllBorders()->setBorderStyle(
    \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
);


$rowNumber += 2;


// =========================================
// NOTES
// =========================================

$sheet1->mergeCells(
    'A'.$rowNumber.':G'.$rowNumber
);

$sheet1->setCellValue(
    'A'.$rowNumber,
    '@ Sundry debtors acceptable as per terms of sanction'
);


$rowNumber++;


$sheet1->mergeCells(
    'A'.$rowNumber.':G'.$rowNumber
);

$sheet1->setCellValue(
    'A'.$rowNumber,
    '$ Separate Annexure for i, ii and iii to be enclosed'
);


$rowNumber += 3;


// =========================================
// SALES DURING YEAR
// =========================================


// ===== TITLE =====

$sheet1->mergeCells(
    'A'.$rowNumber.':C'.$rowNumber
);

$sheet1->setCellValue(
    'A'.$rowNumber,
    'Sales during the financial year'
);

$sheet1->getStyle(
    'A'.$rowNumber
)->getFont()
 ->setBold(true)
 ->setSize(14);


$sheet1->setCellValue(
    'D'.$rowNumber,
    'In Lacs'
);

$sheet1->getStyle(
    'D'.$rowNumber
)->getFont()->setBold(true);


$sheet1->getStyle(
    'D'.$rowNumber
)->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
);


$rowNumber += 2;


// ===== SALES 1 =====

$sheet1->setCellValue(
    'A'.$rowNumber,
    '1'
);

$sheet1->mergeCells(
    'B'.$rowNumber.':C'.$rowNumber
);

$sheet1->setCellValue(
    'B'.$rowNumber,
    'Sales upto last month'
);

$sheet1->setCellValue(
    'D'.$rowNumber,
    FormatIndianNumber(
        $salesUptoLastMonthLacs,
        2
    )
);


$sheet1->getStyle(
    'D'.$rowNumber
)->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
);


$rowNumber++;


// ===== SALES 2 =====

$sheet1->setCellValue(
    'A'.$rowNumber,
    '2'
);

$sheet1->mergeCells(
    'B'.$rowNumber.':C'.$rowNumber
);

$sheet1->setCellValue(
    'B'.$rowNumber,
    'Sales during the month'
);

$sheet1->setCellValue(
    'D'.$rowNumber,
    FormatIndianNumber(
        $salesDuringMonthLacs,
        2
    )
);


$sheet1->getStyle(
    'D'.$rowNumber
)->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
);


$rowNumber++;


// ===== SALES 3 =====

$sheet1->setCellValue(
    'A'.$rowNumber,
    '3'
);

$sheet1->mergeCells(
    'B'.$rowNumber.':C'.$rowNumber
);

$sheet1->setCellValue(
    'B'.$rowNumber,
    'Total Sales'
);

$sheet1->setCellValue(
    'D'.$rowNumber,
    FormatIndianNumber(
        $totalSalesLacs,
        2
    )
);


$sheet1->getStyle(
    'D'.$rowNumber
)->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
);


$sheet1->getStyle(
    'D'.($rowNumber-2).':D'.$rowNumber
)->getFont()->setBold(true);


// =========================================
// PART - C
// =========================================


$rowNumber += 4;


// ===== PART C =====

$sheet1->mergeCells(
    'A'.$rowNumber.':D'.$rowNumber
);

$sheet1->setCellValue(
    'A'.$rowNumber,
    'PART - C'
);

$sheet1->getStyle(
    'A'.$rowNumber
)->getFont()
 ->setBold(true)
 ->setSize(18);


$rowNumber += 2;


// ===== TITLE =====

$sheet1->mergeCells(
    'A'.$rowNumber.':D'.$rowNumber
);

$sheet1->setCellValue(
    'A'.$rowNumber,
    'Sundry Creditors'
);

$sheet1->getStyle(
    'A'.$rowNumber
)->getFont()
 ->setBold(true)
 ->setSize(16);


$rowNumber += 2;


// =========================================
// CREDITORS TABLE
// =========================================


// ===== HEADER =====

$sheet1->mergeCells(
    'A'.$rowNumber.':C'.$rowNumber
);

$sheet1->setCellValue(
    'D'.$rowNumber,
    'Amount (Rs.)'
);


$sheet1->getStyle(
    'A'.$rowNumber.':D'.$rowNumber
)->getFont()->setBold(true);


$sheet1->getStyle(
    'A'.$rowNumber.':D'.$rowNumber
)->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
);


$sheet1->getStyle(
    'A'.$rowNumber.':D'.$rowNumber
)->getBorders()->getAllBorders()->setBorderStyle(
    \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
);


$rowNumber++;


// ===== DATA =====

$sheet1->mergeCells(
    'A'.$rowNumber.':C'.$rowNumber
);

$sheet1->setCellValue(
    'A'.$rowNumber,
    'List of Creditors as per Annexure #'
);

$sheet1->setCellValue(
    'D'.$rowNumber,
    FormatIndianNumber(
        $creditorsTotal,
        2
    )
);


$sheet1->getStyle(
    'D'.$rowNumber
)->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
);


$sheet1->getStyle(
    'A'.$rowNumber.':D'.$rowNumber
)->getBorders()->getAllBorders()->setBorderStyle(
    \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
);


$rowNumber += 2;


// ===== NOTE =====

$sheet1->mergeCells(
    'A'.$rowNumber.':G'.$rowNumber
);

$sheet1->setCellValue(
    'A'.$rowNumber,
    '# List of Creditors as per Annexure to be enclosed.'
);


$rowNumber += 3;


// =========================================
// PURCHASE DURING YEAR
// =========================================


// ===== TITLE =====

$sheet1->mergeCells(
    'A'.$rowNumber.':C'.$rowNumber
);

$sheet1->setCellValue(
    'A'.$rowNumber,
    'Purchase during the Financial Year'
);

$sheet1->getStyle(
    'A'.$rowNumber
)->getFont()
 ->setBold(true)
 ->setSize(14);


$sheet1->setCellValue(
    'D'.$rowNumber,
    'In Lacs'
);

$sheet1->getStyle(
    'D'.$rowNumber
)->getFont()->setBold(true);


$sheet1->getStyle(
    'D'.$rowNumber
)->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
);


$rowNumber += 2;


// ===== PURCHASE 1 =====

$sheet1->setCellValue(
    'A'.$rowNumber,
    '1'
);

$sheet1->mergeCells(
    'B'.$rowNumber.':C'.$rowNumber
);

$sheet1->setCellValue(
    'B'.$rowNumber,
    'Purchases upto last month'
);

$sheet1->setCellValue(
    'D'.$rowNumber,
    FormatIndianNumber(
        $purchaseUptoLastMonthLacs,
        2
    )
);


$sheet1->getStyle(
    'D'.$rowNumber
)->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
);


$rowNumber++;


// ===== PURCHASE 2 =====

$sheet1->setCellValue(
    'A'.$rowNumber,
    '2'
);

$sheet1->mergeCells(
    'B'.$rowNumber.':C'.$rowNumber
);

$sheet1->setCellValue(
    'B'.$rowNumber,
    'Purchases during the month'
);

$sheet1->setCellValue(
    'D'.$rowNumber,
    FormatIndianNumber(
        $purchaseDuringMonthLacs,
        2
    )
);


$sheet1->getStyle(
    'D'.$rowNumber
)->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
);


$rowNumber++;


// ===== PURCHASE 3 =====

$sheet1->setCellValue(
    'A'.$rowNumber,
    '3'
);

$sheet1->mergeCells(
    'B'.$rowNumber.':C'.$rowNumber
);

$sheet1->setCellValue(
    'B'.$rowNumber,
    'Total Purchase'
);

$sheet1->setCellValue(
    'D'.$rowNumber,
    FormatIndianNumber(
        $totalPurchaseLacs,
        2
    )
);


$sheet1->getStyle(
    'D'.$rowNumber
)->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
);


$sheet1->getStyle(
    'D'.($rowNumber-2).':D'.$rowNumber
)->getFont()->setBold(true);



// =========================================
// NOTES
// =========================================


$rowNumber += 3;


// ===== PERIODICITY NOTE =====

$sheet1->mergeCells(
    'A'.$rowNumber.':G'.$rowNumber
);

$sheet1->setCellValue(
    'A'.$rowNumber,
    '* Note: as per the periodicity of submission of stock statement in terms of sanction ( fortnightly / monthly / quarterly / half yearly )'
);

$sheet1->getStyle(
    'A'.$rowNumber
)->getAlignment()->setWrapText(true);


$sheet1->getRowDimension(
    $rowNumber
)->setRowHeight(40);


$rowNumber += 2;


// ===== EXTRA SHEET =====

$sheet1->mergeCells(
    'A'.$rowNumber.':G'.$rowNumber
);

$sheet1->setCellValue(
    'A'.$rowNumber,
    '(Extra sheet to be attached in case of need)'
);


$sheet1->getStyle(
    'A'.$rowNumber
)->getFont()->setItalic(true);


// =========================================
// DECLARATION
// =========================================


$rowNumber += 4;


$declarations = [

    'I/We declare and acknowledge that all the goods noted above stand hypothecated to the bank and the same are my/our own property and that I/We/am/are entitled to hypothecate them with the bank. They are unencumbered and are not subject to any other lien, claim or charge of any sort.',

    'I/We certify that the quality and quantity of the stock are correct and in accordance with the entries in our record. The stock shown do not include damaged unsaleable / obsolete / old goods.',

    'I/We certify that the valuation of stocks has been made as per mandatory Accounting Standard (AS-2) (i.e. cost price / Net Realisable Value whichever is lower) as prescribed by ICAI.',

    'I/We certify that the above goods are adequately covered by insurance against fire and other necessary risks in terms of sanction. All premia on insurance policies have been paid and these are in force.',

    'I/We certify that the amount of sundry debtors / sundry creditors and Sales / Purchase are correct and in accordance with the entries in our record.',

    'In case the above contain any mis-statement (of which the bank is the sole judge) or there be any shortage of security, I/We shall render myself / ourselves liable to legal action.'
];


$point = 1;


foreach($declarations as $text)
{

    $sheet1->setCellValue(
        'A'.$rowNumber,
        $point.')'
    );

    $sheet1->mergeCells(
        'B'.$rowNumber.':G'.$rowNumber
    );

    $sheet1->setCellValue(
        'B'.$rowNumber,
        $text
    );

    $sheet1->getStyle(
        'B'.$rowNumber
    )->getAlignment()->setWrapText(true);


    $sheet1->getRowDimension(
        $rowNumber
    )->setRowHeight(55);


    $rowNumber += 2;

    $point++;
}


// =========================================
// SIGNATURE SECTION
// =========================================


$rowNumber += 2;


$sheet1->mergeCells(
    'E'.$rowNumber.':G'.$rowNumber
);

$sheet1->setCellValue(
    'E'.$rowNumber,
    'BORROWER / AUTHORISED SIGNATORY'
);

$sheet1->getStyle(
    'E'.$rowNumber
)->getFont()
 ->setBold(true)
 ->setSize(14);

$sheet1->getStyle(
    'E'.$rowNumber
)->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
);


$rowNumber += 4;


$sheet1->mergeCells(
    'E'.$rowNumber.':G'.$rowNumber
);

$sheet1->setCellValue(
    'E'.$rowNumber,
    'For '.$company->company_name
);

$sheet1->getStyle(
    'E'.$rowNumber
)->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
);


$rowNumber += 3;


$sheet1->mergeCells(
    'E'.$rowNumber.':G'.$rowNumber
);

$sheet1->setCellValue(
    'E'.$rowNumber,
    'Director'
);

$sheet1->getStyle(
    'E'.$rowNumber
)->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
);


// =========================================
// OFFICE USE
// =========================================


$rowNumber += 5;


$sheet1->mergeCells(
    'A'.$rowNumber.':G'.$rowNumber
);

$sheet1->setCellValue(
    'A'.$rowNumber,
    'FOR OFFICE USE ONLY'
);

$sheet1->getStyle(
    'A'.$rowNumber
)->getFont()
 ->setBold(true)
 ->setSize(16);


$rowNumber += 2;


$officeUse = [

    '1.  Limit _______________________',

    '2.  Value of security (value of stock minus surplus sundry creditors, if any, to be deducted in terms of sanction) _______________________',

    '3.  Margin (as per sanction) _______________________',

    '4.  Drawing power (value of security as per above less margin) _______________________',

    '5.  SRM updated on _________ Entered by (Name) __________________',

    'Verified by (Name) __________________',

    '6.  Inspected on _________ by (Name) __________________',

    'Designation ___________________________',

    '(Signature) ___________________________'
];


foreach($officeUse as $text)
{

    $sheet1->mergeCells(
        'A'.$rowNumber.':G'.$rowNumber
    );

    $sheet1->setCellValue(
        'A'.$rowNumber,
        $text
    );

    $sheet1->getStyle(
        'A'.$rowNumber
    )->getAlignment()->setWrapText(true);


    $sheet1->getRowDimension(
        $rowNumber
    )->setRowHeight(35);


    $rowNumber++;
}
// =========================================
// SHEET 2
// =========================================

$sheet2 =
    $spreadsheet->createSheet();

$sheet2->setTitle(
    'Creditors'
);

// =========================================
// CREDITORS DATA
// =========================================


// ===== GROUPS =====

$sundry_root_groups = [3];

$sundry_group_ids = [];


foreach ($sundry_root_groups as $gid)
{
    $sundry_group_ids[] = $gid;

    $sundry_group_ids = array_merge(
        $sundry_group_ids,

        CommonHelper::getAllChildGroupIds(
            $gid,
            Session::get('user_company_id')
        )
    );
}


$sundry_group_ids = array_unique(
    $sundry_group_ids
);


// ===== ACCOUNTS =====

$accounts = Accounts::whereIn(
        'company_id',
        [
            Session::get('user_company_id'),
            0
        ]
    )

    ->where('delete', '0')

    ->where('status', '1')

    ->whereIn(
        'under_group',
        $sundry_group_ids
    )

    ->orderBy('account_name')

    ->get();


$reportData = [];

$sr = 1;

$grandTotal = 0;


// =========================================
// LOOP
// =========================================

foreach($accounts as $account)
{

    $ledger = DB::table('account_ledger')

        ->selectRaw(
            '
            SUM(debit) as total_debit,
            SUM(credit) as total_credit
            '
        )

        ->where(
            'account_id',
            $account->id
        )

        ->where(
            'company_id',
            Session::get('user_company_id')
        )

        ->where(
            'status',
            '1'
        )

        ->where(
            'delete_status',
            '0'
        )

        ->whereDate(
            'txn_date',
            '<=',
            $lastDate
        )

        ->first();


    $balance = 0;


    if($ledger)
    {
        $balance =
            ($ledger->total_debit ?? 0)
            -
            ($ledger->total_credit ?? 0);
    }


    $openingEntry = DB::table('account_ledger')

        ->where(
            'account_id',
            $account->id
        )

        ->where(
            'company_id',
            Session::get('user_company_id')
        )

        ->where(
            'entry_type',
            '-1'
        )

        ->where(
            'delete_status',
            '0'
        )

        ->first();


    if($openingEntry)
    {
        if(!empty($openingEntry->credit))
        {
            $balance =
                $balance
                -
                $openingEntry->credit;
        }
        else if(!empty($openingEntry->debit))
        {
            $balance =
                $balance
                +
                $openingEntry->debit;
        }
    }


    if(round($balance, 2) == 0)
    {
        continue;
    }


    $grandTotal += $balance;


    if($balance < 0)
    {
        $finalBalance =
            formatIndianNumber(
                abs(round($balance,2))
            );
    }
    else
    {
        $finalBalance =
            '('
            .
            formatIndianNumber(
                abs(round($balance,2))
            )
            .
            ')';
    }


    $reportData[] = [

        'sr_no' =>
            $sr++,

        'account_name' =>
            $account->account_name,

        'closing_balance' =>
            $finalBalance
    ];
}


// =========================================
// TOTAL
// =========================================

if($grandTotal < 0)
{
    $totalBalance =
        formatIndianNumber(
            abs(round($grandTotal,2))
        );
}
else
{
    $totalBalance =
        '('
        .
        formatIndianNumber(
            abs(round($grandTotal,2))
        )
        .
        ')';
}


// =========================================
// COMPANY
// =========================================

$company = Companies::where(
    'id',
    Session::get('user_company_id')
)->first();


// =========================================
// SHEET DESIGN
// =========================================


// ===== COLUMN WIDTH =====

$sheet2->getColumnDimension('A')->setWidth(10);

$sheet2->getColumnDimension('B')->setWidth(45);

$sheet2->getColumnDimension('C')->setWidth(25);


// ===== COMPANY =====

$sheet2->mergeCells('A1:C1');

$sheet2->setCellValue(
    'A1',
    $company->company_name
);

$sheet2->getStyle('A1:C1')->getFont()
    ->setBold(true)
    ->setSize(16);

$sheet2->getStyle('A1:C1')->getAlignment()
    ->setHorizontal(
        \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    );


// ===== TITLE =====

$sheet2->mergeCells('A2:C2');

$sheet2->setCellValue(
    'A2',

    'Sundry Creditors Closing Balance As On '
    .
    date(
        'd-m-Y',
        strtotime($lastDate)
    )
);

$sheet2->getStyle('A2:C2')->getFont()
    ->setBold(true);

$sheet2->getStyle('A2:C2')->getAlignment()
    ->setHorizontal(
        \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    );


// ===== HEADERS =====

$sheet2->setCellValue('A4', 'Sr No');

$sheet2->setCellValue('B4', 'Account Name');

$sheet2->setCellValue('C4', 'Amount');


$sheet2->getStyle('A4:C4')->getFont()
    ->setBold(true);

$sheet2->getStyle('A4:C4')->getAlignment()
    ->setHorizontal(
        \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    );


// ===== HEADER BORDER =====

$sheet2->getStyle('A4:C4')

    ->getBorders()

    ->getAllBorders()

    ->setBorderStyle(
        \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
    );


// =========================================
// DATA
// =========================================

$rowNumber = 5;


foreach($reportData as $row)
{

    $sheet2->setCellValue(
        'A'.$rowNumber,
        $row['sr_no']
    );

    $sheet2->setCellValue(
        'B'.$rowNumber,
        $row['account_name']
    );

    $sheet2->setCellValue(
        'C'.$rowNumber,
        $row['closing_balance']
    );


    // ===== ALIGN =====

    $sheet2->getStyle(
        'C'.$rowNumber
    )->getAlignment()->setHorizontal(
        \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
    );


    // ===== BORDER =====

    $sheet2->getStyle(
        'A'.$rowNumber.':C'.$rowNumber
    )
    ->getBorders()
    ->getAllBorders()
    ->setBorderStyle(
        \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
    );


    $rowNumber++;
}


// =========================================
// TOTAL ROW
// =========================================

$sheet2->mergeCells(
    'A'.$rowNumber.':B'.$rowNumber
);

$sheet2->setCellValue(
    'A'.$rowNumber,
    'TOTAL'
);

$sheet2->setCellValue(
    'C'.$rowNumber,
    $totalBalance
);


$sheet2->getStyle(
    'A'.$rowNumber.':C'.$rowNumber
)->getFont()->setBold(true);


$sheet2->getStyle(
    'A'.$rowNumber.':C'.$rowNumber
)
->getBorders()
->getAllBorders()
->setBorderStyle(
    \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
);


$sheet2->getStyle(
    'C'.$rowNumber
)->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
);


// =========================================
// SHEET 3
// =========================================

$sheet3 =
    $spreadsheet->createSheet();

$sheet3->setTitle(
    'Debtors'
);

// =========================================
// DEBTORS DATA
// =========================================


$company_id =
    Session::get('user_company_id');


// ===== DEBTOR GROUPS =====

$top_groups_list = [11];

$all_groups_list = [];


foreach ($top_groups_list as $gid)
{
    $all_groups_list[] = $gid;

    $all_groups_list = array_merge(
        $all_groups_list,

        CommonHelper::getAllChildGroupIds(
            $gid,
            $company_id
        )
    );
}


$all_groups_list = array_unique(
    $all_groups_list
);


// ===== ACCOUNTS =====

$accounts = DB::table('accounts')

    ->where(
        'company_id',
        $company_id
    )

    ->where(
        'delete',
        '0'
    )

    ->whereIn(
        'under_group',
        $all_groups_list
    )

    ->get();


$reportData = [];

$sr = 1;

$greater90Total = 0;

$less90Total = 0;


// ===== SALES =====

$allSales = DB::table('sales')

    ->where(
        'company_id',
        $company_id
    )

    ->where(
        'date',
        '<=',
        $lastDate
    )

    ->where(
        'status',
        '1'
    )

    ->where(
        'delete',
        '0'
    )

    ->get()

    ->groupBy('party');


// =========================================
// LOOP
// =========================================

foreach ($accounts as $acc)
{

    $ledger = DB::table('account_ledger')

        ->where(
            'company_id',
            $company_id
        )

        ->where(
            'account_id',
            $acc->id
        )

        ->where(
            'txn_date',
            '<=',
            $lastDate
        )

        ->where(
            'delete_status',
            '0'
        )

        ->selectRaw(
            '
            SUM(debit) as dr,
            SUM(credit) as cr
            '
        )

        ->first();


    $open = DB::table('account_ledger')

        ->where(
            'company_id',
            $company_id
        )

        ->where(
            'account_id',
            $acc->id
        )

        ->where(
            'entry_type',
            '-1'
        )

        ->selectRaw(
            '
            SUM(debit) as dr,
            SUM(credit) as cr
            '
        )

        ->first();


    $total =
        ($ledger->dr - $ledger->cr)
        +
        ($open->dr - $open->cr);


    if($total <= 0)
    {
        continue;
    }


    $agingRows = [];


    if(($open->dr - $open->cr) > 0)
    {
        $agingRows[] = [

            'age' =>
                Carbon::parse(
                    DB::table('companies')
                    ->where('id', $company_id)
                    ->value('books_start_from')
                )->diffInDays($lastDate),

            'amount' =>
                ($open->dr - $open->cr)
        ];
    }


    $sales =
        $allSales[$acc->id]
        ??
        collect();


    foreach ($sales as $inv)
    {
        $agingRows[] = [

            'age' =>
                Carbon::parse(
                    $inv->date
                )->diffInDays($lastDate),

            'amount' =>
                $inv->total
        ];
    }


    usort($agingRows, function ($a, $b) {
        return $b['age'] <=> $a['age'];
    });


    $originalTotal =
        array_sum(
            array_column(
                $agingRows,
                'amount'
            )
        );


    $payment =
        $originalTotal - $total;


    foreach ($agingRows as $key => $row)
    {
        if($payment <= 0)
        {
            break;
        }

        $deduct =
            min(
                $row['amount'],
                $payment
            );

        $agingRows[$key]['amount']
            -=
            $deduct;

        $payment -= $deduct;
    }


    $greater90 = 0;

    $less90 = 0;


    foreach ($agingRows as $row)
    {

        if($row['amount'] <= 0)
        {
            continue;
        }

        $age = (int) $row['age'];


        if($age <= 90)
        {
            $less90 += $row['amount'];
        }


        if($age >= 91)
        {
            $greater90 += $row['amount'];
        }
    }


    $greater90Total += $greater90;

    $less90Total += $less90;


    $reportData[] = [

        'sr_no' =>
            $sr++,

        'party' =>
            $acc->account_name,

        'greater90' =>
            $greater90,

        'less90' =>
            $less90
    ];
}


// =========================================
// SHEET DESIGN
// =========================================


// ===== WIDTH =====

$sheet3->getColumnDimension('A')->setWidth(10);

$sheet3->getColumnDimension('B')->setWidth(45);

$sheet3->getColumnDimension('C')->setWidth(20);

$sheet3->getColumnDimension('D')->setWidth(20);


// ===== COMPANY =====

$sheet3->mergeCells('A1:D1');

$sheet3->setCellValue(
    'A1',
    $company->company_name
);

$sheet3->getStyle('A1:D1')->getFont()
    ->setBold(true)
    ->setSize(16);

$sheet3->getStyle('A1:D1')->getAlignment()
    ->setHorizontal(
        \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    );


// ===== TITLE =====

$sheet3->mergeCells('A2:D2');

$sheet3->setCellValue(
    'A2',

    'Sundry Debtors As On '
    .
    date(
        'd-m-Y',
        strtotime($lastDate)
    )
);

$sheet3->getStyle('A2:D2')->getFont()
    ->setBold(true);

$sheet3->getStyle('A2:D2')->getAlignment()
    ->setHorizontal(
        \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    );


// ===== HEADERS =====

$sheet3->setCellValue('A4', 'Sr No');

$sheet3->setCellValue('B4', 'Particulars');

$sheet3->setCellValue('C4', '<= 90 Days');

$sheet3->setCellValue('D4', '>= 91 Days');


$sheet3->getStyle('A4:D4')->getFont()
    ->setBold(true);

$sheet3->getStyle('A4:D4')->getAlignment()
    ->setHorizontal(
        \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    );


// ===== BORDER =====

$sheet3->getStyle('A4:D4')
    ->getBorders()
    ->getAllBorders()
    ->setBorderStyle(
        \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
    );


// =========================================
// DATA
// =========================================

$rowNumber = 5;


foreach($reportData as $row)
{

    $sheet3->setCellValue(
        'A'.$rowNumber,
        $row['sr_no']
    );

    $sheet3->setCellValue(
        'B'.$rowNumber,
        $row['party']
    );

    $sheet3->setCellValue(
        'C'.$rowNumber,
        FormatIndianNumber(
            $row['less90'],
            2
        )
    );

    $sheet3->setCellValue(
        'D'.$rowNumber,
        FormatIndianNumber(
            $row['greater90'],
            2
        )
    );


    // ===== ALIGN =====

    $sheet3->getStyle(
        'C'.$rowNumber.':D'.$rowNumber
    )->getAlignment()->setHorizontal(
        \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
    );


    // ===== BORDER =====

    $sheet3->getStyle(
        'A'.$rowNumber.':D'.$rowNumber
    )
    ->getBorders()
    ->getAllBorders()
    ->setBorderStyle(
        \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
    );


    $rowNumber++;
}


// =========================================
// TOTAL
// =========================================

$sheet3->mergeCells(
    'A'.$rowNumber.':B'.$rowNumber
);

$sheet3->setCellValue(
    'A'.$rowNumber,
    'TOTAL'
);

$sheet3->setCellValue(
    'C'.$rowNumber,

    FormatIndianNumber(
        $less90Total,
        2
    )
);

$sheet3->setCellValue(
    'D'.$rowNumber,

    FormatIndianNumber(
        $greater90Total,
        2
    )
);


$sheet3->getStyle(
    'A'.$rowNumber.':D'.$rowNumber
)->getFont()->setBold(true);


$sheet3->getStyle(
    'A'.$rowNumber.':D'.$rowNumber
)
->getBorders()
->getAllBorders()
->setBorderStyle(
    \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
);


$sheet3->getStyle(
    'C'.$rowNumber.':D'.$rowNumber
)->getAlignment()->setHorizontal(
    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
);


// =========================================
// SHEET 4
// =========================================

$sheet4 =
    $spreadsheet->createSheet();

$sheet4->setTitle(
    'Calculation'
);

// =========================================
// CALCULATION DATA
// =========================================


$company_id =
    Session::get('user_company_id');


$company = DB::table('companies')

    ->where(
        'id',
        $company_id
    )

    ->first();


$upto90Total = 0;

$creditorsTotal = 0;

$stockTotal = 0;


// =========================================
// DEBTORS <= 90
// =========================================

$top_groups_list = [11];

$all_groups_list = [];


foreach ($top_groups_list as $gid)
{
    $all_groups_list[] = $gid;

    $all_groups_list = array_merge(
        $all_groups_list,

        CommonHelper::getAllChildGroupIds(
            $gid,
            $company_id
        )
    );
}


$all_groups_list = array_unique(
    $all_groups_list
);


$accounts = DB::table('accounts')

    ->where(
        'company_id',
        $company_id
    )

    ->where(
        'delete',
        '0'
    )

    ->whereIn(
        'under_group',
        $all_groups_list
    )

    ->get();


$allSales = DB::table('sales')

    ->where(
        'company_id',
        $company_id
    )

    ->where(
        'date',
        '<=',
        $lastDate
    )

    ->where(
        'status',
        '1'
    )

    ->where(
        'delete',
        '0'
    )

    ->get()

    ->groupBy('party');


foreach ($accounts as $acc)
{

    $ledger = DB::table('account_ledger')

        ->where(
            'company_id',
            $company_id
        )

        ->where(
            'account_id',
            $acc->id
        )

        ->where(
            'txn_date',
            '<=',
            $lastDate
        )

        ->where(
            'delete_status',
            '0'
        )

        ->selectRaw(
            '
            SUM(debit) as dr,
            SUM(credit) as cr
            '
        )

        ->first();


    $open = DB::table('account_ledger')

        ->where(
            'company_id',
            $company_id
        )

        ->where(
            'account_id',
            $acc->id
        )

        ->where(
            'entry_type',
            '-1'
        )

        ->selectRaw(
            '
            SUM(debit) as dr,
            SUM(credit) as cr
            '
        )

        ->first();


    $total =
        ($ledger->dr - $ledger->cr)
        +
        ($open->dr - $open->cr);


    if($total <= 0)
    {
        continue;
    }


    $agingRows = [];


    if(($open->dr - $open->cr) > 0)
    {
        $agingRows[] = [

            'age' =>
                Carbon::parse(
                    DB::table('companies')
                    ->where('id', $company_id)
                    ->value('books_start_from')
                )->diffInDays($lastDate),

            'amount' =>
                ($open->dr - $open->cr)
        ];
    }


    $sales =
        $allSales[$acc->id]
        ??
        collect();


    foreach ($sales as $inv)
    {
        $agingRows[] = [

            'age' =>
                Carbon::parse(
                    $inv->date
                )->diffInDays($lastDate),

            'amount' =>
                $inv->total
        ];
    }


    usort($agingRows, function ($a, $b) {
        return $b['age'] <=> $a['age'];
    });


    $originalTotal =
        array_sum(
            array_column(
                $agingRows,
                'amount'
            )
        );


    $payment =
        $originalTotal - $total;


    foreach ($agingRows as $key => $row)
    {
        if($payment <= 0)
        {
            break;
        }

        $deduct =
            min(
                $row['amount'],
                $payment
            );

        $agingRows[$key]['amount']
            -=
            $deduct;

        $payment -= $deduct;
    }


    foreach ($agingRows as $row)
    {

        if($row['amount'] <= 0)
        {
            continue;
        }

        $age = (int) $row['age'];


        if($age <= 90)
        {
            $upto90Total +=
                $row['amount'];
        }
    }
}


// =========================================
// CREDITORS
// =========================================

$sundry_root_groups = [3];

$sundry_group_ids = [];


foreach ($sundry_root_groups as $gid)
{
    $sundry_group_ids[] = $gid;

    $sundry_group_ids = array_merge(
        $sundry_group_ids,

        CommonHelper::getAllChildGroupIds(
            $gid,
            $company_id
        )
    );
}


$sundry_group_ids = array_unique(
    $sundry_group_ids
);


$accounts = Accounts::whereIn(
        'company_id',
        [
            $company_id,
            0
        ]
    )

    ->where('delete', '0')

    ->where('status', '1')

    ->whereIn(
        'under_group',
        $sundry_group_ids
    )

    ->get();


foreach($accounts as $account)
{

    $ledger = DB::table('account_ledger')

        ->selectRaw(
            '
            SUM(debit) as total_debit,
            SUM(credit) as total_credit
            '
        )

        ->where(
            'account_id',
            $account->id
        )

        ->where(
            'company_id',
            $company_id
        )

        ->where(
            'status',
            '1'
        )

        ->where(
            'delete_status',
            '0'
        )

        ->whereDate(
            'txn_date',
            '<=',
            $lastDate
        )

        ->first();


    $balance = 0;


    if($ledger)
    {
        $balance =
            ($ledger->total_debit ?? 0)
            -
            ($ledger->total_credit ?? 0);
    }


    $openingEntry = DB::table('account_ledger')

        ->where(
            'account_id',
            $account->id
        )

        ->where(
            'company_id',
            $company_id
        )

        ->where(
            'entry_type',
            '-1'
        )

        ->where(
            'delete_status',
            '0'
        )

        ->first();


    if($openingEntry)
    {
        if(!empty($openingEntry->credit))
        {
            $balance =
                $balance
                -
                $openingEntry->credit;
        }
        else if(!empty($openingEntry->debit))
        {
            $balance =
                $balance
                +
                $openingEntry->debit;
        }
    }


    if(round($balance, 2) == 0)
    {
        continue;
    }


    $creditorsTotal += $balance;
}


$creditorsTotal =
    abs(
        round(
            $creditorsTotal,
            2
        )
    );


// =========================================
// STOCK
// =========================================

$sub = DB::table('item_averages')

    ->select(
        DB::raw(
            'MAX(id) as latest_id'
        )
    )

    ->where(
        'stock_date',
        '<=',
        $lastDate
    )

    ->where(
        'company_id',
        $company_id
    )

    ->groupBy('item_id');


$stockTotal = DB::table('item_averages')

    ->whereIn(
        'id',
        $sub
    )

    ->sum('amount');


// =========================================
// FINAL
// =========================================

$debtorsAvailability =
    round(
        (
            (
                $upto90Total
                -
                $creditorsTotal
            )
            *
            70
        ) / 100,
        0
    );


$stockAvailability =
    round(
        (
            $stockTotal
            *
            75
        ) / 100,
        0
    );


$dp =
    $debtorsAvailability
    +
    $stockAvailability;


// =========================================
// DESIGN
// =========================================


// ===== WIDTH =====

$sheet4->getColumnDimension('A')->setWidth(25);

$sheet4->getColumnDimension('B')->setWidth(25);

$sheet4->getColumnDimension('C')->setWidth(25);

$sheet4->getColumnDimension('D')->setWidth(25);


// ===== TITLE =====

$sheet4->mergeCells('A1:D1');

$sheet4->setCellValue(
    'A1',
    'FOR BANK'
);

$sheet4->getStyle('A1:D1')->getFont()
    ->setBold(true)
    ->setSize(18);

$sheet4->getStyle('A1:D1')->getAlignment()
    ->setHorizontal(
        \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    );


// ===== HEADER =====

$sheet4->setCellValue('A3', '');

$sheet4->setCellValue('B3', 'Debtors');

$sheet4->setCellValue('C3', 'Creditors');

$sheet4->setCellValue('D3', 'Availability');


$sheet4->getStyle('A3:D3')->getFont()
    ->setBold(true);

$sheet4->getStyle('A3:D3')->getAlignment()
    ->setHorizontal(
        \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    );


// ===== ROW 1 =====

$sheet4->setCellValue('A4', 'Assesable');

$sheet4->setCellValue(
    'B4',
    FormatIndianNumber(
        $upto90Total,
        2
    )
);

$sheet4->setCellValue(
    'C4',
    FormatIndianNumber(
        $creditorsTotal,
        2
    )
);

$sheet4->setCellValue(
    'D4',
    FormatIndianNumber(
        $debtorsAvailability,
        2
    )
);


// ===== ROW 2 =====

$sheet4->setCellValue('A5', 'Stock');

$sheet4->setCellValue(
    'C5',
    FormatIndianNumber(
        $stockTotal,
        2
    )
);

$sheet4->setCellValue(
    'D5',
    FormatIndianNumber(
        $stockAvailability,
        2
    )
);


// ===== ROW 3 =====

$sheet4->mergeCells('A6:C6');

$sheet4->setCellValue(
    'A6',
    'D.P'
);

$sheet4->setCellValue(
    'D6',
    FormatIndianNumber(
        $dp,
        2
    )
);


// ===== STYLE =====

$sheet4->getStyle('A3:D6')
    ->getBorders()
    ->getAllBorders()
    ->setBorderStyle(
        \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
    );


$sheet4->getStyle('A3:D6')->getFont()
    ->setBold(true);


$sheet4->getStyle('B4:D6')
    ->getAlignment()
    ->setHorizontal(
        \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
    );


    $writer =
        new Xlsx(
            $spreadsheet
        );


    $fileName =
        'Complete_Report_'
        .
        $month
        .
        '.xlsx';


    header(
        'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    );

    header(
        'Content-Disposition: attachment; filename="'.$fileName.'"'
    );

    header(
        'Cache-Control: max-age=0'
    );


    $writer->save(
        'php://output'
    );

    exit;
}
if($reportType == 'stock')
{

    $company_id =
        Session::get('user_company_id');

    $stockType =
        $request->stock_type;
$bank = DB::table('banks')

    ->where(
        'id',
        $request->bank_id
    )

    ->first();

    $company = DB::table('companies')
        ->where('id', $company_id)
        ->first();


    $reportData = [];

    $grandTotal = 0;

    $sr = 1;
// =========================================
// PART - B DEBTORS
// =========================================

$upto90Total = 0;

$days91to180Total = 0;

$moreThan180Total = 0;

// =========================================
// SALES DURING FINANCIAL YEAR
// =========================================

$selectedMonthStart =
    date(
        'Y-m-01',
        strtotime($lastDate)
    );


$financialYearStartMonth =
    date(
        'm',
        strtotime($lastDate)
    ) >= 4
    ?
    date(
        'Y',
        strtotime($lastDate)
    )
    :
    (
        date(
            'Y',
            strtotime($lastDate)
        ) - 1
    );


$financialYearStart =
    $financialYearStartMonth
    .
    '-04-01';


// ===== SALES UPTO LAST MONTH =====

// =========================================
// SALES UPTO LAST MONTH
// =========================================


$salesOpening = 0;


// ===== OPENING ENTRY =====

$openingEntry = DB::table('account_ledger')

    ->where(
        'account_id',
        35
    )

    ->where(
        'company_id',
        $company_id
    )

    ->where(
        'entry_type',
        '-1'
    )

    ->where(
        'delete_status',
        '0'
    )

    ->first();


if($openingEntry)
{
    if($openingEntry->credit != "")
    {
        $salesOpening =
            -$openingEntry->credit;
    }
    else if($openingEntry->debit != "")
    {
        $salesOpening =
            $openingEntry->debit;
    }
}


// ===== LEDGER TOTAL =====

$salesLedger = DB::table('account_ledger')

    ->selectRaw(
        '
        SUM(debit) as debit,
        SUM(credit) as credit
        '
    )

    ->where(
        'account_id',
        35
    )

    ->where(
        'company_id',
        $company_id
    )

    ->where(
        'txn_date',
        '<',
        $selectedMonthStart
    )

    ->where(
        'status',
        '1'
    )

    ->where(
        'delete_status',
        '0'
    )

    ->first();


// ===== BALANCE =====

$salesUptoLastMonth =
    $salesOpening
    +
    (
        ($salesLedger->debit ?? 0)
        -
        ($salesLedger->credit ?? 0)
    );


// ===== ABS =====

$salesUptoLastMonth =
    abs(
        round(
            $salesUptoLastMonth,
            2
        )
    );


// ===== IN LACS =====

$salesUptoLastMonthLacs =
    round(
        $salesUptoLastMonth / 100000,
        2
    );


// =========================================
// SALES DURING MONTH
// =========================================


$salesDuringMonthLedger = DB::table('account_ledger')

    ->selectRaw(
        '
        SUM(debit) as debit,
        SUM(credit) as credit
        '
    )

    ->where(
        'account_id',
        35
    )

    ->where(
        'company_id',
        $company_id
    )

    ->whereBetween(
        'txn_date',
        [
            $selectedMonthStart,
            $lastDate
        ]
    )

    ->where(
        'status',
        '1'
    )

    ->where(
        'delete_status',
        '0'
    )

    ->first();


// ===== MONTH BALANCE =====

$salesDuringMonth =
    ($salesDuringMonthLedger->debit ?? 0)
    -
    ($salesDuringMonthLedger->credit ?? 0);


// ===== ABS =====

$salesDuringMonth =
    abs(
        round(
            $salesDuringMonth,
            2
        )
    );


// ===== IN LACS =====

$salesDuringMonthLacs =
    round(
        $salesDuringMonth / 100000,
        2
    );


// ===== TOTAL SALES =====

$totalSales =
    $salesUptoLastMonth
    +
    $salesDuringMonth;


// ===== CONVERT TO LACS =====

$salesUptoLastMonthLacs =
    round(
        $salesUptoLastMonth / 100000,
        2
    );

$salesDuringMonthLacs =
    round(
        $salesDuringMonth / 100000,
        2
    );

$totalSalesLacs =
    round(
        $totalSales / 100000,
        2
    );
// ===== DEBTOR GROUPS =====

$top_groups_list = [11];

$all_groups_list = [];


foreach ($top_groups_list as $gid)
{
    $all_groups_list[] = $gid;

    $all_groups_list = array_merge(
        $all_groups_list,

        CommonHelper::getAllChildGroupIds(
            $gid,
            $company_id
        )
    );
}


$all_groups_list = array_unique(
    $all_groups_list
);


// ===== ACCOUNTS =====

$accounts = DB::table('accounts')

    ->where(
        'company_id',
        $company_id
    )

    ->where(
        'delete',
        '0'
    )

    ->whereIn(
        'under_group',
        $all_groups_list
    )

    ->get();


// ===== SALES =====

$allSales = DB::table('sales')

    ->where(
        'company_id',
        $company_id
    )

    ->where(
        'date',
        '<=',
        $lastDate
    )

    ->where(
        'status',
        '1'
    )

    ->where(
        'delete',
        '0'
    )

    ->get()

    ->groupBy('party');


// ===== LOOP =====

foreach ($accounts as $acc)
{

    $ledger = DB::table('account_ledger')

        ->where(
            'company_id',
            $company_id
        )

        ->where(
            'account_id',
            $acc->id
        )

        ->where(
            'txn_date',
            '<=',
            $lastDate
        )

        ->where(
            'delete_status',
            '0'
        )

        ->selectRaw(
            '
            SUM(debit) as dr,
            SUM(credit) as cr
            '
        )

        ->first();


    $open = DB::table('account_ledger')

        ->where(
            'company_id',
            $company_id
        )

        ->where(
            'account_id',
            $acc->id
        )

        ->where(
            'entry_type',
            '-1'
        )

        ->selectRaw(
            '
            SUM(debit) as dr,
            SUM(credit) as cr
            '
        )

        ->first();


    $total =
        ($ledger->dr - $ledger->cr)
        +
        ($open->dr - $open->cr);


    if($total <= 0)
    {
        continue;
    }


    $agingRows = [];


    // ===== OPENING =====

    if(($open->dr - $open->cr) > 0)
    {
        $agingRows[] = [

            'age' =>
                Carbon::parse(
                    DB::table('companies')
                    ->where('id', $company_id)
                    ->value('books_start_from')
                )->diffInDays($lastDate),

            'amount' =>
                ($open->dr - $open->cr)
        ];
    }


    // ===== SALES =====

    $sales =
        $allSales[$acc->id]
        ??
        collect();


    foreach ($sales as $inv)
    {
        $agingRows[] = [

            'age' =>
                Carbon::parse(
                    $inv->date
                )->diffInDays($lastDate),

            'amount' =>
                $inv->total
        ];
    }


    usort($agingRows, function ($a, $b) {
        return $b['age'] <=> $a['age'];
    });


    $originalTotal =
        array_sum(
            array_column(
                $agingRows,
                'amount'
            )
        );


    $payment =
        $originalTotal - $total;


    foreach ($agingRows as $key => $row)
    {
        if($payment <= 0)
        {
            break;
        }

        $deduct =
            min(
                $row['amount'],
                $payment
            );

        $agingRows[$key]['amount']
            -=
            $deduct;

        $payment -= $deduct;
    }


    foreach ($agingRows as $row)
    {

        if($row['amount'] <= 0)
        {
            continue;
        }

        $age = (int) $row['age'];


        // UPTO 90

        if($age <= 90)
        {
            $upto90Total += $row['amount'];
        }


        // 91 TO 180

        else if(
            $age >= 91
            &&
            $age <= 180
        )
        {
            $days91to180Total += $row['amount'];
        }


        // MORE THAN 180

        else if($age > 180)
        {
            $moreThan180Total += $row['amount'];
        }
    }
}


$debtorsGrandTotal =
    $upto90Total
    +
    $days91to180Total
    +
    $moreThan180Total;


// =========================================
// PART - C CREDITORS TOTAL
// =========================================


// ===== GROUPS =====

$sundry_root_groups = [3];

$sundry_group_ids = [];

foreach ($sundry_root_groups as $gid)
{
    $sundry_group_ids[] = $gid;

    $sundry_group_ids = array_merge(
        $sundry_group_ids,

        CommonHelper::getAllChildGroupIds(
            $gid,
            $company_id
        )
    );
}

$sundry_group_ids = array_unique(
    $sundry_group_ids
);


// ===== ACCOUNTS =====

$accounts = Accounts::whereIn(
        'company_id',
        [
            $company_id,
            0
        ]
    )

    ->where('delete', '0')

    ->where('status', '1')

    ->whereIn(
        'under_group',
        $sundry_group_ids
    )

    ->get();


// ===== TOTAL =====

$creditorsTotal = 0;


foreach($accounts as $account)
{

    $ledger = DB::table('account_ledger')

        ->selectRaw(
            '
            SUM(debit) as total_debit,
            SUM(credit) as total_credit
            '
        )

        ->where(
            'account_id',
            $account->id
        )

        ->where(
            'company_id',
            $company_id
        )

        ->where(
            'status',
            '1'
        )

        ->where(
            'delete_status',
            '0'
        )

        ->whereDate(
            'txn_date',
            '<=',
            $lastDate
        )

        ->first();


    $balance = 0;


    if($ledger)
    {
        $balance =
            ($ledger->total_debit ?? 0)
            -
            ($ledger->total_credit ?? 0);
    }


    // ===== OPENING ENTRY =====

    $openingEntry = DB::table('account_ledger')

        ->where(
            'account_id',
            $account->id
        )

        ->where(
            'company_id',
            $company_id
        )

        ->where(
            'entry_type',
            '-1'
        )

        ->where(
            'delete_status',
            '0'
        )

        ->first();


    if($openingEntry)
    {
        if(!empty($openingEntry->credit))
        {
            $balance =
                $balance
                -
                $openingEntry->credit;
        }
        else if(!empty($openingEntry->debit))
        {
            $balance =
                $balance
                +
                $openingEntry->debit;
        }
    }


    if(round($balance, 2) == 0)
    {
        continue;
    }


    // ===== CREDITORS ONLY =====

    if($balance < 0)
    {
        $creditorsTotal +=
            abs($balance);
    }
}

// =========================================
// PURCHASES DURING FINANCIAL YEAR
// =========================================


$selectedMonthStart =
    date(
        'Y-m-01',
        strtotime($lastDate)
    );


$financialYearStartMonth =
    date(
        'm',
        strtotime($lastDate)
    ) >= 4
    ?
    date(
        'Y',
        strtotime($lastDate)
    )
    :
    (
        date(
            'Y',
            strtotime($lastDate)
        ) - 1
    );


$financialYearStart =
    $financialYearStartMonth
    .
    '-04-01';


// =========================================
// PURCHASE UPTO LAST MONTH
// =========================================


$purchaseOpening = 0;


// ===== OPENING ENTRY =====

$openingEntry = DB::table('account_ledger')

    ->where(
        'account_id',
        36
    )

    ->where(
        'company_id',
        $company_id
    )

    ->where(
        'entry_type',
        '-1'
    )

    ->where(
        'delete_status',
        '0'
    )

    ->first();


if($openingEntry)
{
    if($openingEntry->credit != "")
    {
        $purchaseOpening =
            -$openingEntry->credit;
    }
    else if($openingEntry->debit != "")
    {
        $purchaseOpening =
            $openingEntry->debit;
    }
}


// ===== LEDGER TOTAL =====

$purchaseLedger = DB::table('account_ledger')

    ->selectRaw(
        '
        SUM(debit) as debit,
        SUM(credit) as credit
        '
    )

    ->where(
        'account_id',
        36
    )

    ->where(
        'company_id',
        $company_id
    )

    ->where(
        'txn_date',
        '<',
        $selectedMonthStart
    )

    ->where(
        'status',
        '1'
    )

    ->where(
        'delete_status',
        '0'
    )

    ->first();


// ===== BALANCE =====

$purchaseUptoLastMonth =
    $purchaseOpening
    +
    (
        ($purchaseLedger->debit ?? 0)
        -
        ($purchaseLedger->credit ?? 0)
    );


// ===== ABS =====

$purchaseUptoLastMonth =
    abs(
        round(
            $purchaseUptoLastMonth,
            2
        )
    );


// ===== IN LACS =====

$purchaseUptoLastMonthLacs =
    round(
        $purchaseUptoLastMonth / 100000,
        2
    );


// =========================================
// PURCHASE DURING MONTH
// =========================================


$purchaseDuringMonthLedger = DB::table('account_ledger')

    ->selectRaw(
        '
        SUM(debit) as debit,
        SUM(credit) as credit
        '
    )

    ->where(
        'account_id',
        36
    )

    ->where(
        'company_id',
        $company_id
    )

    ->whereBetween(
        'txn_date',
        [
            $selectedMonthStart,
            $lastDate
        ]
    )

    ->where(
        'status',
        '1'
    )

    ->where(
        'delete_status',
        '0'
    )

    ->first();


// ===== MONTH BALANCE =====

$purchaseDuringMonth =
    ($purchaseDuringMonthLedger->debit ?? 0)
    -
    ($purchaseDuringMonthLedger->credit ?? 0);


// ===== ABS =====

$purchaseDuringMonth =
    abs(
        round(
            $purchaseDuringMonth,
            2
        )
    );


// ===== IN LACS =====

$purchaseDuringMonthLacs =
    round(
        $purchaseDuringMonth / 100000,
        2
    );


// ===== TOTAL PURCHASE =====

$totalPurchase =
    $purchaseUptoLastMonth
    +
    $purchaseDuringMonth;


// ===== IN LACS =====

$purchaseUptoLastMonthLacs =
    round(
        $purchaseUptoLastMonth / 100000,
        2
    );

$purchaseDuringMonthLacs =
    round(
        $purchaseDuringMonth / 100000,
        2
    );

$totalPurchaseLacs =
    round(
        $totalPurchase / 100000,
        2
    );
    // =========================================
    // ITEM WISE
    // =========================================

    if($stockType == 'item')
    {

        $sub = DB::table('item_averages')

            ->select(
                DB::raw(
                    'MAX(id) as latest_id'
                )
            )

            ->where(
                'stock_date',
                '<=',
                $lastDate
            )

            ->where(
                'company_id',
                $company_id
            )

            ->groupBy('item_id');


        $items = DB::table('item_averages')

            ->join(
                'manage_items',
                'item_averages.item_id',
                '=',
                'manage_items.id'
            )

            ->whereIn(
                'item_averages.id',
                $sub
            )

            ->select(
                'manage_items.name as item_name',
                'item_averages.average_weight as qty',
                'item_averages.amount as value'
            )

            ->orderBy(
                'manage_items.name'
            )

            ->get();


        foreach($items as $item)
        {

            if(
                round($item->qty,2) == 0
                &&
                round($item->value,2) == 0
            )
            {
                continue;
            }


            $rate = 0;

if(
    (float)$item->qty > 0
)
{
    $rate = round(
        (
            (float)$item->value
            /
            (float)$item->qty
        ),
        2
    );
}


            $grandTotal +=
                $item->value;


            $reportData[] = [

                'sr_no' =>
                    $sr++,

                'name' =>
                    $item->item_name,

                'qty' =>
                    $item->qty,

                'rate' =>
                    $rate,

                'value' =>
                    $item->value
            ];
        }
    }


    // =========================================
    // GROUP WISE
    // =========================================

    if($stockType == 'group')
    {

        $sub = DB::table('item_averages')

            ->select(
                DB::raw(
                    'MAX(id) as latest_id'
                )
            )

            ->where(
                'stock_date',
                '<=',
                $lastDate
            )

            ->where(
                'company_id',
                $company_id
            )

            ->groupBy('item_id');


        $items = DB::table('item_averages')

            ->join(
                'manage_items',
                'item_averages.item_id',
                '=',
                'manage_items.id'
            )

            ->join(
                'item_groups',
                'manage_items.g_name',
                '=',
                'item_groups.id'
            )

            ->whereIn(
                'item_averages.id',
                $sub
            )

            ->select(
                'item_groups.group_name',
                DB::raw(
                    'SUM(item_averages.average_weight) as qty'
                ),
                DB::raw(
                    'SUM(item_averages.amount) as value'
                )
            )

            ->groupBy(
                'item_groups.group_name'
            )

            ->orderBy(
                'item_groups.group_name'
            )

            ->get();


        foreach($items as $item)
        {

            if(
                round($item->qty,2) == 0
                &&
                round($item->value,2) == 0
            )
            {
                continue;
            }


            $rate = 0;

if(
    (float)$item->qty > 0
)
{
    $rate = round(
        (
            (float)$item->value
            /
            (float)$item->qty
        ),
        2
    );
}


            $grandTotal +=
                $item->value;


            $reportData[] = [

                'sr_no' =>
                    $sr++,

                'name' =>
                    $item->group_name,

                'qty' =>
                    $item->qty,

                'rate' =>
                    $rate,

                'value' =>
                    $item->value
            ];
        }
    }


    header(
        "Content-Type: application/vnd.ms-excel"
    );

    header(
        "Content-Disposition: attachment; filename=Stock_Report_".$month.".xls"
    );


    echo '

<table
    border="0"
    style="
        width:100%;
        border-collapse:collapse;
        font-family:Arial;
        font-size:13px;
    ">


    <tr>

        <td colspan="5"></td>

        <td colspan="2"
            style="
                text-align:right;
                font-weight:bold;
                font-size:14px;
            ">

            PART - A

        </td>

    </tr>


    <tr>

        <td colspan="5"></td>

        <td colspan="2"
            style="
                text-align:right;
                font-weight:bold;
                font-size:14px;
                padding-bottom:15px;
            ">

            ANNEXURE

        </td>

    </tr>


    <tr>

        <td colspan="7"
            style="
                text-align:center;
                font-size:22px;
                font-weight:bold;
                padding-top:10px;
            ">

            '.$bank->bank_name.'

        </td>

    </tr>


    <tr>

        <td colspan="7"
            style="
                text-align:center;
                font-size:18px;
                font-weight:bold;
                padding-top:8px;
            ">

            STOCK STATEMENT
            (REVISED PROFORMA)

        </td>

    </tr>


    <tr>

        <td colspan="7"
            style="
                text-align:center;
                font-size:15px;
                font-weight:bold;
                padding-top:5px;
                padding-bottom:20px;
            ">

            (TO BE SUBMITTED BY THE BORROWER)

        </td>

    </tr>


    <tr>

        <td colspan="7"
            style="
                font-size:14px;
                padding-bottom:15px;
            ">

            <b>
                Preodicity of submission of stock statement :
            </b>

            Fortnightly / Monthly / quarterly / half yearly.

        </td>

    </tr>


    <tr>

        <td colspan="7"
            style="
                font-size:14px;
                line-height:24px;
                padding-bottom:20px;
            ">

            Statement as on

            <b>
                '
                .
                strtoupper(
                    date(
                        'd-M-y',
                        strtotime($lastDate)
                    )
                )
                .
            '
            </b>

            belonging to

            <b>
                M/s '.$company->company_name.'
            </b>

            '.$company->address.'

            Hypothecated as security with

            <b>
                '.$bank->bank_name.',
                '.$bank->branch.'
            </b>

        </td>

    </tr>


    <tr>

        <td colspan="3"
            style="
                font-size:14px;
                padding-bottom:15px;
            ">

            <b>
                A/c No.
            </b>

            :
            '.$bank->account_no.'

        </td>


        <td colspan="2"
            style="
                font-size:14px;
                padding-bottom:15px;
            ">

            <b>
                Facility
            </b>

        </td>


        <td colspan="2"
            style="
                font-size:14px;
                padding-bottom:15px;
            ">

            Cash Credit

        </td>

    </tr>


    <tr>

        <td colspan="7"
            style="
                font-size:14px;
                padding-bottom:25px;
            ">

            <b>
                Limit Rs.
            </b>

            :

        </td>

    </tr>

</table>


<table
    border="1"
    style="
        width:100%;
        border-collapse:collapse;
        font-family:Arial;
        font-size:13px;
    ">

    <tr
        style="
            height:35px;
            text-align:center;
            font-weight:bold;
        ">

        <th>Sr No</th>

        <th>Particulars of Goods</th>

        <th>Where Lying</th>

        <th>Quantity In Kgs</th>

        <th>Rate</th>

        <th>Value</th>

        <th>Remarks</th>

    </tr>
';


    foreach($reportData as $row)
    {

        echo '

        <tr>

            <td>
                '.$row['sr_no'].'
            </td>

            <td>
                '.$row['name'].'
            </td>

            <td>
                FACTORY
            </td>

            <td style="text-align:right;">
                '.FormatIndianNumber(
                    $row['qty'],
                    2
                ).'
            </td>

            <td style="text-align:right;">
                '.FormatIndianNumber(
                    $row['rate'],
                    2
                ).'
            </td>

            <td style="text-align:right;">
                '.FormatIndianNumber(
                    $row['value'],
                    2
                ).'
            </td>

            <td>

            </td>

        </tr>
        ';
    }


    echo '

        <tr>

            <th colspan="5"
                style="text-align:right;">

                TOTAL

            </th>

            <th style="text-align:right;">

                '.FormatIndianNumber(
                    $grandTotal,
                    2
                ).'

            </th>

            <th>

            </th>

        </tr>

    </table>

<br>

<table border="0"
       width="100%">

    <tr>

        <td style="
            font-size:14px;
        ">

            (Extra Sheet to be attached in case of Need)

        </td>

    </tr>


    <tr>

        <td style="
            padding-top:10px;
            font-size:14px;
        ">

            PNB 938 revised (8/2009)

        </td>

    </tr>

</table>

<br><br><br>

<table border="0"
       width="100%">

    <tr>

        <td style="
            font-size:22px;
            font-weight:bold;
        ">

            PART-B

        </td>

    </tr>

</table>


<br>


<table border="0"
       width="100%">

    <tr>

        <td style="
            font-size:18px;
            font-weight:bold;
        ">

            Sundry Debtors (Receivables) @

        </td>

    </tr>

</table>


<br>


<table border="1"
       width="70%"
       style="
            border-collapse:collapse;
            font-size:14px;
       ">

    <tr
        style="
            height:35px;
            font-weight:bold;
            text-align:center;
        ">

        <th width="15%">

            S.NO

        </th>

        <th width="55%">

            List of Debtors as per Annesure

        </th>

        <th width="30%">

            Amount (Rs.)

        </th>

    </tr>


    <tr style="height:35px;">

        <td style="text-align:center;">

            I

        </td>

        <td>

            Upto 90 Days

        </td>

        <td style="text-align:right;">

            '.FormatIndianNumber(
                $upto90Total,
                2
            ).'

        </td>

    </tr>


    <tr style="height:35px;">

        <td style="text-align:center;">

            ii

        </td>

        <td>

            >90 Days To 180 Days

        </td>

        <td style="text-align:right;">

            '.FormatIndianNumber(
                $days91to180Total,
                2
            ).'

        </td>

    </tr>


    <tr style="height:35px;">

        <td style="text-align:center;">

            iii

        </td>

        <td>

            >180 Days

        </td>

        <td style="text-align:right;">

            '.FormatIndianNumber(
                $moreThan180Total,
                2
            ).'

        </td>

    </tr>


    <tr style="height:38px;">

        <th colspan="2"
            style="
                text-align:center;
            ">

            TOTAL

        </th>

        <th style="
            text-align:right;
        ">

            '.FormatIndianNumber(
                $debtorsGrandTotal,
                2
            ).'

        </th>

    </tr>

</table>


<br>


<table border="0"
       width="100%">

    <tr>

        <td style="
            font-size:15px;
        ">

            @ Sundry debtors acceptable as per terms of sanction

        </td>

    </tr>


    <tr>

        <td style="
            font-size:15px;
            padding-top:10px;
        ">

            $ Separate Annexure for i, ii and iii to be enclosed

        </td>

    </tr>

</table>

<br><br>


<table border="0"
       width="60%"
       style="
            font-size:15px;
       ">

    <tr>

        <td colspan="3"
            style="
                font-weight:bold;
                padding-bottom:15px;
            ">

            Sales during the financial year

        </td>

    </tr>


    <tr>

        <td width="10%"></td>

        <td width="60%"></td>

        <td width="30%"
            style="
                font-weight:bold;
                text-align:center;
            ">

            In Lacs

        </td>

    </tr>


    <tr style="height:35px;">

        <td>

            1.

        </td>

        <td>

            Sales upto last month

        </td>

        <td style="
            text-align:right;
            font-weight:bold;
        ">

            '.FormatIndianNumber(
                $salesUptoLastMonthLacs,
                2
            ).'

        </td>

    </tr>


    <tr style="height:35px;">

        <td>

            2.

        </td>

        <td>

            Sales during the month

        </td>

        <td style="
            text-align:right;
            font-weight:bold;
        ">

            '.FormatIndianNumber(
                $salesDuringMonthLacs,
                2
            ).'

        </td>

    </tr>


    <tr style="height:35px;">

        <td>

            3.

        </td>

        <td>

            Total Sales

        </td>

        <td style="
            text-align:right;
            font-weight:bold;
        ">

            '.FormatIndianNumber(
                $totalSalesLacs,
                2
            ).'

        </td>

    </tr>

</table>

<br><br><br>


<table border="0"
       width="100%">

    <tr>

        <td style="
            font-size:22px;
            font-weight:bold;
        ">

            PART -C

        </td>

    </tr>


    <tr>

        <td style="
            font-size:18px;
            font-weight:bold;
            padding-top:10px;
        ">

            Sundry Creditors

        </td>

    </tr>

</table>


<br>


<table border="1"
       width="65%"
       style="
            border-collapse:collapse;
            font-size:14px;
       ">

    <tr>

        <th width="70%">

            

        </th>

        <th width="30%">

            Amount (Rs.)

        </th>

    </tr>


    <tr style="height:40px;">

        <td>List of Creditors as per Annexure #</td>

        <td style="
            text-align:right;
            padding-right:10px;
        ">

            '.FormatIndianNumber(
                $creditorsTotal,
                2
            ).'

        </td>

    </tr>

</table>


<br>


<table border="0"
       width="100%">

    <tr>

        <td style="
            font-size:15px;
        ">

            # List of Creditors as per Annexure to be enclosed.

        </td>

    </tr>


</table>
<br><br>


<table border="0"
       width="60%"
       style="
            font-size:15px;
       ">

    <tr>

        <td colspan="3"
            style="
                font-weight:bold;
                padding-bottom:15px;
            ">

            Purchase during the Financial Year

        </td>


        <td style="
            font-weight:bold;
            text-align:center;
        ">

            In Lacs

        </td>

    </tr>


    <tr style="height:35px;">

        <td width="10%">

            1.

        </td>

        <td colspan="2">

            Purchases upto last month

        </td>

        <td style="
            text-align:right;
            font-weight:bold;
        ">

            '.FormatIndianNumber(
                $purchaseUptoLastMonthLacs,
                2
            ).'

        </td>

    </tr>


    <tr style="height:35px;">

        <td>

            2.

        </td>

        <td colspan="2">

            Purchases during the month

        </td>

        <td style="
            text-align:right;
            font-weight:bold;
        ">

            '.FormatIndianNumber(
                $purchaseDuringMonthLacs,
                2
            ).'

        </td>

    </tr>


    <tr style="height:35px;">

        <td>

            3.

        </td>

        <td colspan="2">

            Total Purchase

        </td>

        <td style="
            text-align:right;
            font-weight:bold;
        ">

            '.FormatIndianNumber(
                $totalPurchaseLacs,
                2
            ).'

        </td>

    </tr>

</table>
<br><br><br><br>


<table border="0"
       width="100%"
       style="
            font-size:15px;
            line-height:30px;
       ">

    <tr valign="top">

        <td width="5%">

            1)

        </td>

        <td width="95%">

            I/We declare and acknowledge that all the goods noted above
            stand hypothecated to the bank and the same are my/ our own
            property and that I/We/am/are entitled to hypothecate them
            with the bank. They are unencumbered and are not subject to
            any other lie, claim or charge of any sort.

        </td>

    </tr>


    <tr valign="top">

        <td>

            2)

        </td>

        <td>

            I/We certify that the quality and quantity of the stock are
            correct and in accordance with the entries in out record.
            The stock shown do not include damage unsaleable/obsolete
            /old goods

        </td>

    </tr>


    <tr valign="top">

        <td>

            3)

        </td>

        <td>

            I/we certify that the valuation of stocks has been made as
            per madatery Accounting Standard (AS-2)
            (i.e. cost price/Net RealisableRate, whichever is lower)
            as prescribed by ICAI

        </td>

    </tr>


    <tr valign="top">

        <td>

            4)

        </td>

        <td>

            I/We certify that te above goods are adequately covered by
            isurance against firm and other necessary
            <b>
                risks in terms of sanction.
            </b>

            Allpremia on insurance policies have been paid and these are in force.

        </td>

    </tr>


    <tr valign="top">

        <td>

            5)

        </td>

        <td>

            I/we certify that the amout of sundry debtors /sundr creditors
            and Sales/Purchase are correct and in accordance with the
            entries in our record.

        </td>

    </tr>


    <tr valign="top">

        <td>

            6)

        </td>

        <td>

            In case the above contain any mis- statement(
            of which the bank is the sole judge) or there be any shortage
            of security, I /We shall render myself/ourselves liable
            to legal action.

        </td>

    </tr>

</table>


<br><br><br>


<table border="0"
       width="100%">

    <tr>

        <td width="55%"></td>

        <td width="45%"
            style="
                text-align:center;
                font-size:18px;
                font-weight:bold;
            ">

            BORROWER/AUTHORISED SIGNATORY

        </td>

    </tr>


    <tr>

        <td></td>

        <td style="
            text-align:center;
            padding-top:40px;
            font-size:18px;
        ">

            For '.$company->company_name.'

        </td>

    </tr>


    <tr>

        <td></td>

        <td style="
            text-align:center;
            padding-top:30px;
            font-size:18px;
        ">

            Director

        </td>

    </tr>

</table>


<br><br><br>


<table border="0"
       width="100%"
       style="
            font-size:16px;
            line-height:35px;
       ">

    <tr>

        <td colspan="2"
            style="
                font-weight:bold;
                font-size:18px;
            ">

            FOR OFFICE USE ONLY

        </td>

    </tr>


    <tr>

        <td width="5%">

            1.

        </td>

        <td>

            Limit _______________________

        </td>

    </tr>


    <tr>

        <td>

            2.

        </td>

        <td>

            Value of security
            (value of stoc minus surplius sundr creditors,
            if any, to be deducted in terms of sanctin)
            _______________________

        </td>

    </tr>


    <tr>

        <td>

            3.

        </td>

        <td>

            margin&nbsp;&nbsp;
            ( as per sanction)
            _______________________

        </td>

    </tr>


    <tr>

        <td>

            4.

        </td>

        <td>

            Drawing power value of security as per (2)
            above less margin)
            _______________________

            <br>

            (
            Sanctioned Limit or Drawing power whichever
            is less to be taken
            )

        </td>

    </tr>


    <tr>

        <td>

            5.

        </td>

        <td>

            SRM updated on

            &nbsp;&nbsp;&nbsp;

            _________

            &nbsp;&nbsp;&nbsp;

            Entered by ( Name)

            __________________

        </td>

    </tr>


    <tr>

        <td></td>

        <td>

            Verified by ( Name)

            __________________

        </td>

    </tr>


    <tr>

        <td>

            6

        </td>

        <td>

            <b>
                Inspected on
            </b>

            &nbsp;&nbsp;

            _________

            &nbsp;&nbsp;

            by (Name)

            __________________

        </td>

    </tr>


    <tr>

        <td></td>

        <td>

            <b>
                Designation
            </b>

            &nbsp;&nbsp;

            ___________________________

        </td>

    </tr>


    <tr>

        <td></td>

        <td style="
            padding-top:35px;
        ">

            (
            Signature
            )

            ___________________________

        </td>

    </tr>

</table>
';

    exit;
}

if($reportType == 'calculation')
{

    $company_id =
        Session::get('user_company_id');


    $company = DB::table('companies')

        ->where(
            'id',
            $company_id
        )

        ->first();


    $upto90Total = 0;

    $creditorsTotal = 0;

    $stockTotal = 0;


    // =========================================
    // DEBTORS <= 90
    // =========================================

    $top_groups_list = [11];

    $all_groups_list = [];


    foreach ($top_groups_list as $gid)
    {
        $all_groups_list[] = $gid;

        $all_groups_list = array_merge(
            $all_groups_list,

            CommonHelper::getAllChildGroupIds(
                $gid,
                $company_id
            )
        );
    }


    $all_groups_list = array_unique(
        $all_groups_list
    );


    $accounts = DB::table('accounts')

        ->where(
            'company_id',
            $company_id
        )

        ->where(
            'delete',
            '0'
        )

        ->whereIn(
            'under_group',
            $all_groups_list
        )

        ->get();


    $allSales = DB::table('sales')

        ->where(
            'company_id',
            $company_id
        )

        ->where(
            'date',
            '<=',
            $lastDate
        )

        ->where(
            'status',
            '1'
        )

        ->where(
            'delete',
            '0'
        )

        ->get()

        ->groupBy('party');


    foreach ($accounts as $acc)
    {

        $ledger = DB::table('account_ledger')

            ->where(
                'company_id',
                $company_id
            )

            ->where(
                'account_id',
                $acc->id
            )

            ->where(
                'txn_date',
                '<=',
                $lastDate
            )

            ->where(
                'delete_status',
                '0'
            )

            ->selectRaw(
                '
                SUM(debit) as dr,
                SUM(credit) as cr
                '
            )

            ->first();


        $open = DB::table('account_ledger')

            ->where(
                'company_id',
                $company_id
            )

            ->where(
                'account_id',
                $acc->id
            )

            ->where(
                'entry_type',
                '-1'
            )

            ->selectRaw(
                '
                SUM(debit) as dr,
                SUM(credit) as cr
                '
            )

            ->first();


        $total =
            ($ledger->dr - $ledger->cr)
            +
            ($open->dr - $open->cr);


        if($total <= 0)
        {
            continue;
        }


        $agingRows = [];


        if(($open->dr - $open->cr) > 0)
        {
            $agingRows[] = [

                'age' =>
                    Carbon::parse(
                        DB::table('companies')
                        ->where('id', $company_id)
                        ->value('books_start_from')
                    )->diffInDays($lastDate),

                'amount' =>
                    ($open->dr - $open->cr)
            ];
        }


        $sales =
            $allSales[$acc->id]
            ??
            collect();


        foreach ($sales as $inv)
        {
            $agingRows[] = [

                'age' =>
                    Carbon::parse(
                        $inv->date
                    )->diffInDays($lastDate),

                'amount' =>
                    $inv->total
            ];
        }


        usort($agingRows, function ($a, $b) {
            return $b['age'] <=> $a['age'];
        });


        $originalTotal =
            array_sum(
                array_column(
                    $agingRows,
                    'amount'
                )
            );


        $payment =
            $originalTotal - $total;


        foreach ($agingRows as $key => $row)
        {
            if($payment <= 0)
            {
                break;
            }

            $deduct =
                min(
                    $row['amount'],
                    $payment
                );

            $agingRows[$key]['amount']
                -=
                $deduct;

            $payment -= $deduct;
        }


        foreach ($agingRows as $row)
        {

            if($row['amount'] <= 0)
            {
                continue;
            }

            $age = (int) $row['age'];


            if($age <= 90)
            {
                $upto90Total +=
                    $row['amount'];
            }
        }
    }


    // =========================================
    // CREDITORS TOTAL
    // =========================================


    $sundry_root_groups = [3];

    $sundry_group_ids = [];


    foreach ($sundry_root_groups as $gid)
    {
        $sundry_group_ids[] = $gid;

        $sundry_group_ids = array_merge(
            $sundry_group_ids,

            CommonHelper::getAllChildGroupIds(
                $gid,
                $company_id
            )
        );
    }


    $sundry_group_ids = array_unique(
        $sundry_group_ids
    );


    $accounts = Accounts::whereIn(
            'company_id',
            [
                $company_id,
                0
            ]
        )

        ->where('delete', '0')

        ->where('status', '1')

        ->whereIn(
            'under_group',
            $sundry_group_ids
        )

        ->get();


    foreach($accounts as $account)
    {

        $ledger = DB::table('account_ledger')

            ->selectRaw(
                '
                SUM(debit) as total_debit,
                SUM(credit) as total_credit
                '
            )

            ->where(
                'account_id',
                $account->id
            )

            ->where(
                'company_id',
                $company_id
            )

            ->where(
                'status',
                '1'
            )

            ->where(
                'delete_status',
                '0'
            )

            ->whereDate(
                'txn_date',
                '<=',
                $lastDate
            )

            ->first();


        $balance = 0;


        if($ledger)
        {
            $balance =
                ($ledger->total_debit ?? 0)
                -
                ($ledger->total_credit ?? 0);
        }


        $openingEntry = DB::table('account_ledger')

            ->where(
                'account_id',
                $account->id
            )

            ->where(
                'company_id',
                $company_id
            )

            ->where(
                'entry_type',
                '-1'
            )

            ->where(
                'delete_status',
                '0'
            )

            ->first();


        if($openingEntry)
        {
            if(!empty($openingEntry->credit))
            {
                $balance =
                    $balance
                    -
                    $openingEntry->credit;
            }
            else if(!empty($openingEntry->debit))
            {
                $balance =
                    $balance
                    +
                    $openingEntry->debit;
            }
        }


        if(round($balance, 2) == 0)
        {
            continue;
        }


        $creditorsTotal += $balance;
    }
$creditorsTotal =
    abs(
        round(
            $creditorsTotal,
            2
        )
    );

    // =========================================
    // STOCK TOTAL
    // =========================================


    $sub = DB::table('item_averages')

        ->select(
            DB::raw(
                'MAX(id) as latest_id'
            )
        )

        ->where(
            'stock_date',
            '<=',
            $lastDate
        )

        ->where(
            'company_id',
            $company_id
        )

        ->groupBy('item_id');


    $stockTotal = DB::table('item_averages')

        ->whereIn(
            'id',
            $sub
        )

        ->sum('amount');


    // =========================================
    // AVAILABILITY
    // =========================================


    $debtorsAvailability =
        round(
            (
                (
                    $upto90Total
                    -
                    $creditorsTotal
                )
                *
                70
            ) / 100,
            0
        );


    $stockAvailability =
        round(
            (
                $stockTotal
                *
                75
            ) / 100,
            0
        );


    $dp =
        $debtorsAvailability
        +
        $stockAvailability;


    header(
        "Content-Type: application/vnd.ms-excel"
    );

    header(
        "Content-Disposition: attachment; filename=Calculation_Report_".$month.".xls"
    );


    echo '

    <table border="1"
           style="
                border-collapse:collapse;
                width:70%;
                font-family:Arial;
                font-size:18px;
           ">

        <tr>

            <th colspan="4"
                style="
                    text-align:center;
                    font-size:24px;
                    padding:10px;
                ">

                FOR BANK

            </th>

        </tr>


        <tr style="
            text-align:center;
            font-weight:bold;
            height:40px;
        ">

            <th width="25%"></th>

            <th width="25%">

                Debtors

            </th>

            <th width="25%">

                Creditors

            </th>

            <th width="25%">

                Availability

            </th>

        </tr>


        <tr style="height:45px;">

            <td>

                Assesable

            </td>

            <td style="text-align:right;">

                '.FormatIndianNumber(
                    $upto90Total,
                    2
                ).'

            </td>

            <td style="text-align:right;">

                '.FormatIndianNumber(
                    $creditorsTotal,
                    2
                ).'

            </td>

            <td style="text-align:right;">

                '.FormatIndianNumber(
                    $debtorsAvailability,
                    2
                ).'

            </td>

        </tr>


        <tr style="height:45px;">

            <td>

                Stock

            </td>

            <td></td>

            <td style="text-align:right;">

                '.FormatIndianNumber(
                    $stockTotal,
                    2
                ).'

            </td>

            <td style="text-align:right;">

                '.FormatIndianNumber(
                    $stockAvailability,
                    2
                ).'

            </td>

        </tr>


        <tr style="
            height:45px;
            font-weight:bold;
        ">

            <td colspan="3"
                style="
                    text-align:right;
                ">

                D.P

            </td>

            <td style="
                text-align:right;
                font-size:24px;
            ">

                '.FormatIndianNumber(
                    $dp,
                    2
                ).'

            </td>

        </tr>

    </table>
    ';

    exit;
}
if($reportType == 'debtors')
{

    $company_id =
        Session::get('user_company_id');


    // ===== DEBTOR GROUPS =====

    $top_groups_list = [11];

    $all_groups_list = [];


    foreach ($top_groups_list as $gid)
    {
        $all_groups_list[] = $gid;

        $all_groups_list = array_merge(
            $all_groups_list,

            CommonHelper::getAllChildGroupIds(
                $gid,
                $company_id
            )
        );
    }


    $all_groups_list = array_unique(
        $all_groups_list
    );


    // ===== ACCOUNTS =====

    $accounts = DB::table('accounts')

        ->where(
            'company_id',
            $company_id
        )

        ->where(
            'delete',
            '0'
        )

        ->whereIn(
            'under_group',
            $all_groups_list
        )

        ->get();


    $reportData = [];

    $sr = 1;

    $greater90Total = 0;

    $less90Total = 0;
// ===== ALL SALES =====

$allSales = DB::table('sales')

    ->where(
        'company_id',
        $company_id
    )

    ->where(
        'date',
        '<=',
        $lastDate
    )

    ->where(
        'status',
        '1'
    )

    ->where(
        'delete',
        '0'
    )

    ->get()

    ->groupBy('party');

    foreach ($accounts as $acc)
    {

        // ===== CLOSING =====

        $ledger = DB::table('account_ledger')

            ->where(
                'company_id',
                $company_id
            )

            ->where(
                'account_id',
                $acc->id
            )

            ->where(
                'txn_date',
                '<=',
                $lastDate
            )

            ->where(
                'delete_status',
                '0'
            )

            ->selectRaw(
                '
                SUM(debit) as dr,
                SUM(credit) as cr
                '
            )

            ->first();


        // ===== OPENING =====

        $open = DB::table('account_ledger')

            ->where(
                'company_id',
                $company_id
            )

            ->where(
                'account_id',
                $acc->id
            )

            ->where(
                'entry_type',
                '-1'
            )

            ->selectRaw(
                '
                SUM(debit) as dr,
                SUM(credit) as cr
                '
            )

            ->first();


        $total =
            ($ledger->dr - $ledger->cr)
            +
            ($open->dr - $open->cr);


        if($total <= 0)
        {
            continue;
        }


        // ===== FIFO ROWS =====

        $agingRows = [];


        // ===== OPENING =====

        if(($open->dr - $open->cr) > 0)
        {
            $agingRows[] = [

                'age' =>
                    Carbon::parse(
                        DB::table('companies')
                        ->where('id', $company_id)
                        ->value('books_start_from')
                    )->diffInDays($lastDate),

                'amount' =>
                    ($open->dr - $open->cr)
            ];
        }
// ===== SALES =====

$sales = $allSales[$acc->id] ?? collect();

        foreach ($sales as $inv)
        {
            $agingRows[] = [

                'age' =>
                    Carbon::parse(
                        $inv->date
                    )->diffInDays($lastDate),

                'amount' =>
                    $inv->total
            ];
        }


        // ===== SORT =====

        usort($agingRows, function ($a, $b) {
            return $b['age'] <=> $a['age'];
        });


        // ===== FIFO ADJUST =====

        $originalTotal =
            array_sum(
                array_column(
                    $agingRows,
                    'amount'
                )
            );


        $payment =
            $originalTotal - $total;


        foreach ($agingRows as $key => $row)
        {
            if($payment <= 0)
            {
                break;
            }

            $deduct =
                min(
                    $row['amount'],
                    $payment
                );

            $agingRows[$key]['amount']
                -=
                $deduct;

            $payment -= $deduct;
        }


        // ===== BUCKETS =====

        $greater90 = 0;

        $less90 = 0;


foreach ($agingRows as $row)
{

    if($row['amount'] <= 0)
    {
        continue;
    }

    $age = (int) $row['age'];

    // <= 90 DAYS

    if($age <= 90)
    {
        $less90 += $row['amount'];
    }

    // >= 91 DAYS

    if($age >= 91)
    {
        $greater90 += $row['amount'];
    }
}


        $greater90Total += $greater90;

        $less90Total += $less90;


        $reportData[] = [

            'sr_no' =>
                $sr++,

            'party' =>
                $acc->account_name,

            'greater90' =>
                $greater90,

            'less90' =>
                $less90
        ];
    }


    $company = DB::table('companies')
        ->find($company_id);


    header(
        "Content-Type: application/vnd.ms-excel"
    );

    header(
        "Content-Disposition: attachment; filename=Sundry_Debtors_".$month.".xls"
    );


    echo '

    <table border="1">

        <tr>

            <th colspan="4"
                style="font-size:18px;">

                '.$company->company_name.'

            </th>

        </tr>


        <tr>

            <th colspan="4">

                Sundry Debtors As On '
                .
                date(
                    'd-m-Y',
                    strtotime($lastDate)
                )
                .
            '

            </th>

        </tr>


        <tr>

    <th>Sr No</th>

    <th>Particulars</th>

    <th><= 90 Days</th>

    <th>>= 91 Days</th>

</tr>
    ';


    foreach($reportData as $row)
    {

        echo '

        <tr>

            <td>
                '.$row['sr_no'].'
            </td>

            <td>
                '.$row['party'].'
            </td>

            <td style="text-align:right;">
    '.FormatIndianNumber(
        $row['less90'],
        2
    ).'
</td>

<td style="text-align:right;">
    '.FormatIndianNumber(
        $row['greater90'],
        2
    ).'
</td>

        </tr>
        ';
    }


    echo '

        <tr>

            <th colspan="2"
                style="text-align:right;">

                TOTAL

            </th>

            <th style="text-align:right;">

    '.FormatIndianNumber(
        $less90Total,
        2
    ).'

</th>

<th style="text-align:right;">

    '.FormatIndianNumber(
        $greater90Total,
        2
    ).'

</th>

        </tr>

    </table>
    ';

    exit;
}
    // ===== GROUPS =====

    $sundry_root_groups = [3];

    $sundry_group_ids = [];

    foreach ($sundry_root_groups as $gid)
    {
        $sundry_group_ids[] = $gid;

        $sundry_group_ids = array_merge(
            $sundry_group_ids,

            CommonHelper::getAllChildGroupIds(
                $gid,
                Session::get('user_company_id')
            )
        );
    }

    $sundry_group_ids = array_unique(
        $sundry_group_ids
    );


    // ===== ACCOUNTS =====

    $accounts = Accounts::whereIn(
            'company_id',
            [
                Session::get('user_company_id'),
                0
            ]
        )

        ->where('delete', '0')

        ->where('status', '1')

        ->whereIn(
            'under_group',
            $sundry_group_ids
        )

        ->orderBy('account_name')

        ->get();


    $reportData = [];

    $sr = 1;

    $grandTotal = 0;


    foreach($accounts as $account)
    {

        $ledger = DB::table('account_ledger')

            ->selectRaw(
                '
                SUM(debit) as total_debit,
                SUM(credit) as total_credit
                '
            )

            ->where(
                'account_id',
                $account->id
            )

            ->where(
                'company_id',
                Session::get('user_company_id')
            )

            ->where(
                'status',
                '1'
            )

            ->where(
                'delete_status',
                '0'
            )

            ->whereDate(
                'txn_date',
                '<=',
                $lastDate
            )

            ->first();


        $balance = 0;


        if($ledger)
        {
            $balance =
                ($ledger->total_debit ?? 0)
                -
                ($ledger->total_credit ?? 0);
        }


        // ===== OPENING ENTRY =====

        $openingEntry = DB::table('account_ledger')

            ->where(
                'account_id',
                $account->id
            )

            ->where(
                'company_id',
                Session::get('user_company_id')
            )

            ->where(
                'entry_type',
                '-1'
            )

            ->where(
                'delete_status',
                '0'
            )

            ->first();


        if($openingEntry)
        {
            if(!empty($openingEntry->credit))
            {
                $balance =
                    $balance
                    -
                    $openingEntry->credit;
            }
            else if(!empty($openingEntry->debit))
            {
                $balance =
                    $balance
                    +
                    $openingEntry->debit;
            }
        }


        if(round($balance, 2) == 0)
        {
            continue;
        }


        $grandTotal += $balance;


        // ===== CREDIT =====

        if($balance < 0)
{
    // CREDIT

    $finalBalance =
        formatIndianNumber(
            abs(round($balance,2))
        );
}
else
{
    // DEBIT

    $finalBalance =
        '('
        .
        formatIndianNumber(
            abs(round($balance,2))
        )
        .
        ')';
}


        $reportData[] = [

            'sr_no' =>
                $sr++,

            'account_name' =>
                $account->account_name,

            'closing_balance' =>
                $finalBalance
        ];
    }


    // ===== TOTAL =====

    if($grandTotal < 0)
    {
        $totalBalance =
            formatIndianNumber(
                abs(round($grandTotal,2))
            );
    }
    else
    {
        $totalBalance =
            '('
            .
            formatIndianNumber(
                abs(round($grandTotal,2))
            )
            .
            ')';
    }


    $company = Companies::where(
        'id',
        Session::get('user_company_id')
    )->first();


    header("Content-Type: application/vnd.ms-excel");

    header(
        "Content-Disposition: attachment; filename=Sundry_Creditors_".$month.".xls"
    );


    echo '
    <table border="1">

        <tr>
            <th colspan="3" style="font-size:18px;">
                '.$company->company_name.'
            </th>
        </tr>

        <tr>
            <th colspan="3">
                Sundry Creditors Closing Balance As On '
                .
                date(
                    'd-m-Y',
                    strtotime($lastDate)
                )
                .
            '
            </th>
        </tr>

        <tr>
            <th>Sr No</th>
            <th>Account Name</th>
            <th>Amount</th>
        </tr>
    ';


    foreach($reportData as $row)
    {
        echo '

        <tr>

            <td>
                '.$row['sr_no'].'
            </td>

            <td>
                '.$row['account_name'].'
            </td>

            <td style="
                text-align:right;
                mso-number-format:\'\\@\';
            ">
                '.$row['closing_balance'].'
            </td>

        </tr>
        ';
    }


    echo '

        <tr>

            <th colspan="2"
                style="text-align:right;">

                TOTAL

            </th>

            <th style="
                text-align:right;
                mso-number-format:\'\\@\';
            ">

                '.$totalBalance.'

            </th>

        </tr>

    </table>';

    exit;
}
    // =========================================
    // GET REPORT DATA (single source of truth)
    // =========================================

    private function getReportData(
        string $month,
        string $stockType,
        int $bankId
    ): array {

        $company_id = Session::get('user_company_id');

        $lastDate = date('Y-m-t', strtotime($month));

        $selectedMonthStart = date('Y-m-01', strtotime($lastDate));

        $company = DB::table('companies')
            ->where('id', $company_id)
            ->first();

        $bank = DB::table('banks')
            ->where('id', $bankId)
            ->first();


        // =========================================
        // STOCK ROWS
        // =========================================

        $reportData = [];
        $grandTotal  = 0;
        $sr          = 1;

        if ($stockType == 'item') {

            $sub = DB::table('item_averages')
                ->select(DB::raw('MAX(id) as latest_id'))
                ->where('stock_date', '<=', $lastDate)
                ->where('company_id', $company_id)
                ->groupBy('item_id');

            $items = DB::table('item_averages')
                ->join('manage_items', 'item_averages.item_id', '=', 'manage_items.id')
                ->whereIn('item_averages.id', $sub)
                ->select(
                    'manage_items.name as item_name',
                    'item_averages.average_weight as qty',
                    'item_averages.amount as value'
                )
                ->orderBy('manage_items.name')
                ->get();

        } else {

            $sub = DB::table('item_averages')
                ->select(DB::raw('MAX(id) as latest_id'))
                ->where('stock_date', '<=', $lastDate)
                ->where('company_id', $company_id)
                ->groupBy('item_id');

            $items = DB::table('item_averages')
                ->join('manage_items', 'item_averages.item_id', '=', 'manage_items.id')
                ->join('item_groups', 'manage_items.g_name', '=', 'item_groups.id')
                ->whereIn('item_averages.id', $sub)
                ->select(
                    'item_groups.group_name as item_name',
                    DB::raw('SUM(item_averages.average_weight) as qty'),
                    DB::raw('SUM(item_averages.amount) as value')
                )
                ->groupBy('item_groups.group_name')
                ->orderBy('item_groups.group_name')
                ->get();
        }

        foreach ($items as $item) {

            if (round($item->qty, 2) == 0 && round($item->value, 2) == 0) {
                continue;
            }

            $rate = (float)$item->qty > 0
                ? round((float)$item->value / (float)$item->qty, 2)
                : 0;

            $grandTotal += $item->value;

            $reportData[] = [
                'sr_no' => $sr++,
                'name'  => $item->item_name,
                'qty'   => $item->qty,
                'rate'  => $rate,
                'value' => $item->value,
            ];
        }


        // =========================================
        // DEBTORS AGING (shared helper)
        // =========================================

        $debtorBuckets = $this->calcDebtorBuckets(
            $company_id,
            $lastDate
        );

        $upto90Total      = $debtorBuckets['upto90'];
        $days91to180Total = $debtorBuckets['days91to180'];
        $moreThan180Total = $debtorBuckets['moreThan180'];
        $debtorsGrandTotal = $upto90Total + $days91to180Total + $moreThan180Total;


        // =========================================
        // DEBTORS LIST (Sheet 3)
        // =========================================

        $debtorsListData  = $this->calcDebtorsList($company_id, $lastDate);
        $less90Total      = $debtorsListData['less90Total'];
        $greater90Total   = $debtorsListData['greater90Total'];
        $debtorsRows      = $debtorsListData['rows'];


        // =========================================
        // CREDITORS TOTAL (Sheet 1 Part C)
        // =========================================

        $creditorsResult = $this->calcCreditorsTotal($company_id, $lastDate);
        $creditorsTotal  = $creditorsResult['total'];
        $creditorsRows   = $creditorsResult['rows'];
        $creditorsTotalBalance = $creditorsResult['totalBalance'];


        // =========================================
        // SALES
        // =========================================

        $salesData = $this->calcSales($company_id, $selectedMonthStart, $lastDate);

        $salesUptoLastMonthLacs  = $salesData['uptoLastMonthLacs'];
        $salesDuringMonthLacs    = $salesData['duringMonthLacs'];
        $totalSalesLacs          = $salesData['totalLacs'];


        // =========================================
        // PURCHASES
        // =========================================

        $purchaseData = $this->calcPurchases($company_id, $selectedMonthStart, $lastDate);

        $purchaseUptoLastMonthLacs = $purchaseData['uptoLastMonthLacs'];
        $purchaseDuringMonthLacs   = $purchaseData['duringMonthLacs'];
        $totalPurchaseLacs         = $purchaseData['totalLacs'];


        // =========================================
        // CALCULATION SHEET
        // =========================================

        $sub = DB::table('item_averages')
            ->select(DB::raw('MAX(id) as latest_id'))
            ->where('stock_date', '<=', $lastDate)
            ->where('company_id', $company_id)
            ->groupBy('item_id');

        $stockTotal = DB::table('item_averages')
            ->whereIn('id', $sub)
            ->sum('amount');

        $debtorsAvailability = round((($upto90Total - $creditorsTotal) * 70) / 100, 0);
        $stockAvailability   = round(($stockTotal * 75) / 100, 0);
        $dp                  = $debtorsAvailability + $stockAvailability;


        // =========================================
        // RETURN ALL DATA
        // =========================================

        return [

            'company'    => $company,
            'bank'       => $bank,
            'month'      => $month,
            'lastDate'   => $lastDate,
            'stockType'  => $stockType,

            // Sheet 1 - Stock
            'stockRows'  => $reportData,
            'grandTotal' => $grandTotal,

            // Sheet 1 - Part B Debtors summary
            'upto90Total'       => $upto90Total,
            'days91to180Total'  => $days91to180Total,
            'moreThan180Total'  => $moreThan180Total,
            'debtorsGrandTotal' => $debtorsGrandTotal,

            // Sheet 1 - Sales
            'salesUptoLastMonthLacs' => $salesUptoLastMonthLacs,
            'salesDuringMonthLacs'   => $salesDuringMonthLacs,
            'totalSalesLacs'         => $totalSalesLacs,

            // Sheet 1 - Part C Creditors summary
            'creditorsTotal' => $creditorsTotal,

            // Sheet 1 - Purchases
            'purchaseUptoLastMonthLacs' => $purchaseUptoLastMonthLacs,
            'purchaseDuringMonthLacs'   => $purchaseDuringMonthLacs,
            'totalPurchaseLacs'         => $totalPurchaseLacs,

            // Sheet 2 - Creditors list
            'creditorsRows'        => $creditorsRows,
            'creditorsTotalBalance' => $creditorsTotalBalance,

            // Sheet 3 - Debtors list
            'debtorsRows'    => $debtorsRows,
            'less90Total'    => $less90Total,
            'greater90Total' => $greater90Total,

            // Sheet 4 - Calculation
            'stockTotal'           => $stockTotal,
            'debtorsAvailability'  => $debtorsAvailability,
            'stockAvailability'    => $stockAvailability,
            'dp'                   => $dp,
        ];
    }


    // =========================================
    // HELPER: DEBTORS AGING BUCKETS
    // =========================================

    private function calcDebtorBuckets(int $company_id, string $lastDate): array
    {
        $upto90      = 0;
        $days91to180 = 0;
        $moreThan180 = 0;

        $top_groups_list = [11];
        $all_groups_list = [];

        foreach ($top_groups_list as $gid) {
            $all_groups_list[] = $gid;
            $all_groups_list   = array_merge(
                $all_groups_list,
                CommonHelper::getAllChildGroupIds($gid, $company_id)
            );
        }

        $all_groups_list = array_unique($all_groups_list);

        $accounts = DB::table('accounts')
            ->where('company_id', $company_id)
            ->where('delete', '0')
            ->whereIn('under_group', $all_groups_list)
            ->get();

        $allSales = DB::table('sales')
            ->where('company_id', $company_id)
            ->where('date', '<=', $lastDate)
            ->where('status', '1')
            ->where('delete', '0')
            ->get()
            ->groupBy('party');

        $booksStart = DB::table('companies')
            ->where('id', $company_id)
            ->value('books_start_from');

        foreach ($accounts as $acc) {

            $ledger = DB::table('account_ledger')
                ->where('company_id', $company_id)
                ->where('account_id', $acc->id)
                ->where('txn_date', '<=', $lastDate)
                ->where('delete_status', '0')
                ->selectRaw('SUM(debit) as dr, SUM(credit) as cr')
                ->first();

            $open = DB::table('account_ledger')
                ->where('company_id', $company_id)
                ->where('account_id', $acc->id)
                ->where('entry_type', '-1')
                ->selectRaw('SUM(debit) as dr, SUM(credit) as cr')
                ->first();

            $total = ($ledger->dr - $ledger->cr) + ($open->dr - $open->cr);

            if ($total <= 0) continue;

            $agingRows = [];

            if (($open->dr - $open->cr) > 0) {
                $agingRows[] = [
                    'age'    => Carbon::parse($booksStart)->diffInDays($lastDate),
                    'amount' => ($open->dr - $open->cr),
                ];
            }

            $sales = $allSales[$acc->id] ?? collect();

            foreach ($sales as $inv) {
                $agingRows[] = [
                    'age'    => Carbon::parse($inv->date)->diffInDays($lastDate),
                    'amount' => $inv->total,
                ];
            }

            usort($agingRows, fn($a, $b) => $b['age'] <=> $a['age']);

            $payment = array_sum(array_column($agingRows, 'amount')) - $total;

            foreach ($agingRows as $key => $row) {
                if ($payment <= 0) break;
                $deduct = min($row['amount'], $payment);
                $agingRows[$key]['amount'] -= $deduct;
                $payment -= $deduct;
            }

            foreach ($agingRows as $row) {
                if ($row['amount'] <= 0) continue;
                $age = (int)$row['age'];
                if ($age <= 90)                    $upto90      += $row['amount'];
                elseif ($age >= 91 && $age <= 180) $days91to180 += $row['amount'];
                elseif ($age > 180)                $moreThan180 += $row['amount'];
            }
        }

        return [
            'upto90'      => $upto90,
            'days91to180' => $days91to180,
            'moreThan180' => $moreThan180,
        ];
    }


    // =========================================
    // HELPER: DEBTORS LIST ROWS
    // =========================================

    private function calcDebtorsList(int $company_id, string $lastDate): array
    {
        $top_groups_list = [11];
        $all_groups_list = [];

        foreach ($top_groups_list as $gid) {
            $all_groups_list[] = $gid;
            $all_groups_list   = array_merge(
                $all_groups_list,
                CommonHelper::getAllChildGroupIds($gid, $company_id)
            );
        }

        $all_groups_list = array_unique($all_groups_list);

        $accounts = DB::table('accounts')
            ->where('company_id', $company_id)
            ->where('delete', '0')
            ->whereIn('under_group', $all_groups_list)
            ->get();

        $allSales = DB::table('sales')
            ->where('company_id', $company_id)
            ->where('date', '<=', $lastDate)
            ->where('status', '1')
            ->where('delete', '0')
            ->get()
            ->groupBy('party');

        $booksStart = DB::table('companies')
            ->where('id', $company_id)
            ->value('books_start_from');

        $rows           = [];
        $sr             = 1;
        $less90Total    = 0;
        $greater90Total = 0;

        foreach ($accounts as $acc) {

            $ledger = DB::table('account_ledger')
                ->where('company_id', $company_id)
                ->where('account_id', $acc->id)
                ->where('txn_date', '<=', $lastDate)
                ->where('delete_status', '0')
                ->selectRaw('SUM(debit) as dr, SUM(credit) as cr')
                ->first();

            $open = DB::table('account_ledger')
                ->where('company_id', $company_id)
                ->where('account_id', $acc->id)
                ->where('entry_type', '-1')
                ->selectRaw('SUM(debit) as dr, SUM(credit) as cr')
                ->first();

            $total = ($ledger->dr - $ledger->cr) + ($open->dr - $open->cr);

            if ($total <= 0) continue;

            $agingRows = [];

            if (($open->dr - $open->cr) > 0) {
                $agingRows[] = [
                    'age'    => Carbon::parse($booksStart)->diffInDays($lastDate),
                    'amount' => ($open->dr - $open->cr),
                ];
            }

            $sales = $allSales[$acc->id] ?? collect();

            foreach ($sales as $inv) {
                $agingRows[] = [
                    'age'    => Carbon::parse($inv->date)->diffInDays($lastDate),
                    'amount' => $inv->total,
                ];
            }

            usort($agingRows, fn($a, $b) => $b['age'] <=> $a['age']);

            $payment = array_sum(array_column($agingRows, 'amount')) - $total;

            foreach ($agingRows as $key => $row) {
                if ($payment <= 0) break;
                $deduct = min($row['amount'], $payment);
                $agingRows[$key]['amount'] -= $deduct;
                $payment -= $deduct;
            }

            $less90    = 0;
            $greater90 = 0;

            foreach ($agingRows as $row) {
                if ($row['amount'] <= 0) continue;
                $age = (int)$row['age'];
                if ($age <= 90) $less90    += $row['amount'];
                else            $greater90 += $row['amount'];
            }

            $less90Total    += $less90;
            $greater90Total += $greater90;

            $rows[] = [
                'sr_no'     => $sr++,
                'party'     => $acc->account_name,
                'less90'    => $less90,
                'greater90' => $greater90,
            ];
        }

        return [
            'rows'           => $rows,
            'less90Total'    => $less90Total,
            'greater90Total' => $greater90Total,
        ];
    }


    // =========================================
    // HELPER: CREDITORS TOTAL + ROWS
    // =========================================

    private function calcCreditorsTotal(int $company_id, string $lastDate): array
    {
        $sundry_group_ids = [];

        foreach ([3] as $gid) {
            $sundry_group_ids[] = $gid;
            $sundry_group_ids   = array_merge(
                $sundry_group_ids,
                CommonHelper::getAllChildGroupIds($gid, $company_id)
            );
        }

        $sundry_group_ids = array_unique($sundry_group_ids);

        $accounts = \App\Models\Accounts::whereIn('company_id', [$company_id, 0])
            ->where('delete', '0')
            ->where('status', '1')
            ->whereIn('under_group', $sundry_group_ids)
            ->orderBy('account_name')
            ->get();

        $rows       = [];
        $sr         = 1;
        $grandTotal = 0;

        foreach ($accounts as $account) {

            $ledger = DB::table('account_ledger')
                ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                ->where('account_id', $account->id)
                ->where('company_id', $company_id)
                ->where('status', '1')
                ->where('delete_status', '0')
                ->whereDate('txn_date', '<=', $lastDate)
                ->first();

            $balance = $ledger
                ? ($ledger->total_debit ?? 0) - ($ledger->total_credit ?? 0)
                : 0;

            $openingEntry = DB::table('account_ledger')
                ->where('account_id', $account->id)
                ->where('company_id', $company_id)
                ->where('entry_type', '-1')
                ->where('delete_status', '0')
                ->first();

            if ($openingEntry) {
                if (!empty($openingEntry->credit)) $balance -= $openingEntry->credit;
                elseif (!empty($openingEntry->debit)) $balance += $openingEntry->debit;
            }

            if (round($balance, 2) == 0) continue;

            $grandTotal += $balance;

            $finalBalance = $balance < 0
                ? formatIndianNumber(abs(round($balance, 2)))
                : '(' . formatIndianNumber(abs(round($balance, 2))) . ')';

            $rows[] = [
                'sr_no'           => $sr++,
                'account_name'    => $account->account_name,
                'closing_balance' => $finalBalance,
                'raw_balance'     => $balance,
            ];
        }

        $totalBalance = $grandTotal < 0
            ? formatIndianNumber(abs(round($grandTotal, 2)))
            : '(' . formatIndianNumber(abs(round($grandTotal, 2))) . ')';

        return [
            'total'        => abs(round($grandTotal, 2)),
            'rows'         => $rows,
            'totalBalance' => $totalBalance,
        ];
    }


    // =========================================
    // HELPER: SALES
    // =========================================

    private function calcSales(int $company_id, string $selectedMonthStart, string $lastDate): array
    {
        $salesOpening = 0;

        $openingEntry = DB::table('account_ledger')
            ->where('account_id', 35)
            ->where('company_id', $company_id)
            ->where('entry_type', '-1')
            ->where('delete_status', '0')
            ->first();

        if ($openingEntry) {
            if ($openingEntry->credit != '')  $salesOpening = -$openingEntry->credit;
            elseif ($openingEntry->debit != '') $salesOpening = $openingEntry->debit;
        }

        $salesLedger = DB::table('account_ledger')
            ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
            ->where('account_id', 35)
            ->where('company_id', $company_id)
            ->where('txn_date', '<', $selectedMonthStart)
            ->where('status', '1')
            ->where('delete_status', '0')
            ->first();

        $salesUptoLastMonth = abs(round(
            $salesOpening + (($salesLedger->debit ?? 0) - ($salesLedger->credit ?? 0)),
            2
        ));

        $salesDuringMonthLedger = DB::table('account_ledger')
            ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
            ->where('account_id', 35)
            ->where('company_id', $company_id)
            ->whereBetween('txn_date', [$selectedMonthStart, $lastDate])
            ->where('status', '1')
            ->where('delete_status', '0')
            ->first();

        $salesDuringMonth = abs(round(
            ($salesDuringMonthLedger->debit ?? 0) - ($salesDuringMonthLedger->credit ?? 0),
            2
        ));

        $totalSales = $salesUptoLastMonth + $salesDuringMonth;

        return [
            'uptoLastMonthLacs' => round($salesUptoLastMonth / 100000, 2),
            'duringMonthLacs'   => round($salesDuringMonth / 100000, 2),
            'totalLacs'         => round($totalSales / 100000, 2),
        ];
    }


    // =========================================
    // HELPER: PURCHASES
    // =========================================

    private function calcPurchases(int $company_id, string $selectedMonthStart, string $lastDate): array
    {
        $purchaseOpening = 0;

        $openingEntry = DB::table('account_ledger')
            ->where('account_id', 36)
            ->where('company_id', $company_id)
            ->where('entry_type', '-1')
            ->where('delete_status', '0')
            ->first();

        if ($openingEntry) {
            if ($openingEntry->credit != '')  $purchaseOpening = -$openingEntry->credit;
            elseif ($openingEntry->debit != '') $purchaseOpening = $openingEntry->debit;
        }

        $purchaseLedger = DB::table('account_ledger')
            ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
            ->where('account_id', 36)
            ->where('company_id', $company_id)
            ->where('txn_date', '<', $selectedMonthStart)
            ->where('status', '1')
            ->where('delete_status', '0')
            ->first();

        $purchaseUptoLastMonth = abs(round(
            $purchaseOpening + (($purchaseLedger->debit ?? 0) - ($purchaseLedger->credit ?? 0)),
            2
        ));

        $purchaseDuringMonthLedger = DB::table('account_ledger')
            ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
            ->where('account_id', 36)
            ->where('company_id', $company_id)
            ->whereBetween('txn_date', [$selectedMonthStart, $lastDate])
            ->where('status', '1')
            ->where('delete_status', '0')
            ->first();

        $purchaseDuringMonth = abs(round(
            ($purchaseDuringMonthLedger->debit ?? 0) - ($purchaseDuringMonthLedger->credit ?? 0),
            2
        ));

        $totalPurchase = $purchaseUptoLastMonth + $purchaseDuringMonth;

        return [
            'uptoLastMonthLacs' => round($purchaseUptoLastMonth / 100000, 2),
            'duringMonthLacs'   => round($purchaseDuringMonth / 100000, 2),
            'totalLacs'         => round($totalPurchase / 100000, 2),
        ];
    }


    // =========================================
    // PREVIEW
    // =========================================

    public function preview(Request $request)
    {
        $request->validate([
            'month'      => 'required',
            'bank_id'    => 'required|integer',
            'stock_type' => 'required|in:item,group',
        ]);

        $data = $this->getReportData(
            $request->month,
            $request->stock_type,
            (int)$request->bank_id
        );

        return view('ExportMonthlyReportPreview', $data);
    }


    // =========================================
    // DOWNLOAD EXCEL (from edited preview values)
    // =========================================

    public function downloadExcel(Request $request)
    {
        $month     = $request->input('month');
        $stockType = $request->input('stock_type');
        
        $bankId    = (int)$request->input('bank_id');
        $limitAmount = $request->input('limit_amount', '');

        // Use edited values from POST — not DB
        $company_id = Session::get('user_company_id');
        $company    = DB::table('companies')->where('id', $company_id)->first();
        $bank       = DB::table('banks')->where('id', $bankId)->first();
        $lastDate   = date('Y-m-t', strtotime($month));

        // ===== EDITED STOCK ROWS =====
        $stockNames  = $request->input('stock_name', []);
        $stockQtys   = $request->input('stock_qty', []);
        $stockRates  = $request->input('stock_rate', []);
        $stockValues = $request->input('stock_value', []);

        $reportData = [];
        $grandTotal = 0;

        foreach ($stockNames as $i => $name) {
            $value = (float)str_replace(',', '', $stockValues[$i] ?? 0);
            $grandTotal += $value;
            $reportData[] = [
                'sr_no' => $i + 1,
                'name'  => $name,
                'qty'   => (float)str_replace(',', '', $stockQtys[$i]  ?? 0),
                'rate'  => (float)str_replace(',', '', $stockRates[$i] ?? 0),
                'value' => $value,
            ];
        }

        // ===== EDITED SINGLE VALUES =====
        $upto90Total = (float)str_replace(
            ',',
            '',
            $request->input(
                'calc_upto90',
                $request->input('upto90_total', 0)
            )
        );
        $days91to180Total = (float)str_replace(',', '', $request->input('days91to180_total', 0));
        $moreThan180Total = (float)str_replace(',', '', $request->input('moreThan180_total', 0));
        $debtorsGrandTotal = $upto90Total + $days91to180Total + $moreThan180Total;

        $salesUptoLastMonthLacs  = (float)str_replace(',', '', $request->input('sales_upto_last_month_lacs', 0));
        $salesDuringMonthLacs    = (float)str_replace(',', '', $request->input('sales_during_month_lacs', 0));
        $totalSalesLacs          = $salesUptoLastMonthLacs + $salesDuringMonthLacs;

        $creditorsTotal = (float)str_replace(
            ',',
            '',
            $request->input(
                'calc_creditors',
                $request->input('creditors_total', 0)
            )
        );

        $purchaseUptoLastMonthLacs = (float)str_replace(',', '', $request->input('purchase_upto_last_month_lacs', 0));
        $purchaseDuringMonthLacs   = (float)str_replace(',', '', $request->input('purchase_during_month_lacs', 0));
        $totalPurchaseLacs         = $purchaseUptoLastMonthLacs + $purchaseDuringMonthLacs;

        // ===== EDITED CREDITORS ROWS =====
        $creditorNames    = $request->input('creditor_name', []);
        $creditorBalances = $request->input('creditor_balance', []);
        $creditorsRows    = [];

        foreach ($creditorNames as $i => $name) {
            $creditorsRows[] = [
                'sr_no'           => $i + 1,
                'account_name'    => $name,
                'closing_balance' => $creditorBalances[$i] ?? '',
            ];
        }

        // ===== EDITED DEBTORS ROWS =====
        $debtorParties  = $request->input('debtor_party', []);
        $debtorLess90   = $request->input('debtor_less90', []);
        $debtorGreater90 = $request->input('debtor_greater90', []);
        $debtorsRows    = [];
        $less90Total    = 0;
        $greater90Total = 0;

        foreach ($debtorParties as $i => $party) {
            $l = (float)str_replace(',', '', $debtorLess90[$i]    ?? 0);
            $g = (float)str_replace(',', '', $debtorGreater90[$i] ?? 0);
            $less90Total    += $l;
            $greater90Total += $g;
            $debtorsRows[] = [
                'sr_no'     => $i + 1,
                'party'     => $party,
                'less90'    => $l,
                'greater90' => $g,
            ];
        }

        // ===== CALCULATION =====
        $stockTotal          = (float)str_replace(',', '', $request->input('stock_total', 0));
        $debtorsAvailability = (float)str_replace(',', '', $request->input('debtors_availability', 0));
        $stockAvailability   = (float)str_replace(',', '', $request->input('stock_availability', 0));
        $dp                  = $debtorsAvailability + $stockAvailability;

        // ===== NOW BUILD EXCEL (reusing your existing PhpSpreadsheet logic) =====

        $spreadsheet = new Spreadsheet();

        // --- SHEET 1 ---
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Stock Report');

        $sheet1->getColumnDimension('A')->setWidth(10);
        $sheet1->getColumnDimension('B')->setWidth(35);
        $sheet1->getColumnDimension('C')->setWidth(18);
        $sheet1->getColumnDimension('D')->setWidth(18);
        $sheet1->getColumnDimension('E')->setWidth(18);
        $sheet1->getColumnDimension('F')->setWidth(18);
        $sheet1->getColumnDimension('G')->setWidth(20);

        $sheet1->mergeCells('F1:G1');
        $sheet1->setCellValue('F1', 'PART - A');
        $sheet1->getStyle('F1:G1')->getFont()->setBold(true)->setSize(14);
        $sheet1->getStyle('F1:G1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        $sheet1->mergeCells('F2:G2');
        $sheet1->setCellValue('F2', 'ANNEXURE');
        $sheet1->getStyle('F2:G2')->getFont()->setBold(true)->setSize(14);
        $sheet1->getStyle('F2:G2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        $sheet1->mergeCells('A4:G4');
        $sheet1->setCellValue('A4', $bank->bank_name);
        $sheet1->getStyle('A4:G4')->getFont()->setBold(true)->setSize(22);
        $sheet1->getStyle('A4:G4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet1->mergeCells('A5:G5');
        $sheet1->setCellValue('A5', 'STOCK STATEMENT (REVISED PROFORMA)');
        $sheet1->getStyle('A5:G5')->getFont()->setBold(true)->setSize(18);
        $sheet1->getStyle('A5:G5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet1->mergeCells('A6:G6');
        $sheet1->setCellValue('A6', '(TO BE SUBMITTED BY THE BORROWER)');
        $sheet1->getStyle('A6:G6')->getFont()->setBold(true)->setSize(14);
        $sheet1->getStyle('A6:G6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet1->mergeCells('A8:G8');
        $sheet1->setCellValue('A8', 'Preodicity of submission of stock statement : Fortnightly / Monthly / quarterly / half yearly.');
        $sheet1->getStyle('A8:G8')->getFont()->setSize(13);

        $sheet1->mergeCells('A10:G10');
        $sheet1->setCellValue('A10',
            'Statement as on ' . strtoupper(date('d-M-y', strtotime($lastDate))) .
            ' belonging to M/s ' . $company->company_name . ' ' . $company->address .
            ' Hypothecated as security with ' . $bank->bank_name . ', ' . $bank->branch
        );
        $sheet1->getStyle('A10:G10')->getAlignment()->setWrapText(true);
        $sheet1->getStyle('A10:G10')->getFont()->setSize(13);
        $sheet1->getRowDimension(10)->setRowHeight(45);

        $sheet1->mergeCells('A12:C12');
        $sheet1->setCellValue('A12', 'A/c No. : ' . $bank->account_no);
        $sheet1->mergeCells('D12:E12');
        $sheet1->setCellValue('D12', 'Facility');
        $sheet1->mergeCells('F12:G12');
        $sheet1->setCellValue('F12', 'Cash Credit');

        $sheet1->mergeCells('A14:G14');
        $sheet1->setCellValue('A14', 'Limit Rs. :'.$limitAmount);
        $sheet1->getStyle('A14:G14')->getFont()->setBold(true);

        $sheet1->setCellValue('A16', 'Sr No');
        $sheet1->setCellValue('B16', 'Particulars of Goods');
        $sheet1->setCellValue('C16', 'Where Lying');
        $sheet1->setCellValue('D16', 'Quantity In Kgs');
        $sheet1->setCellValue('E16', 'Rate');
        $sheet1->setCellValue('F16', 'Value');
        $sheet1->setCellValue('G16', 'Remarks');
        $sheet1->getStyle('A16:G16')->getFont()->setBold(true);
        $sheet1->getStyle('A16:G16')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle('A16:G16')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $rowNumber = 17;
        foreach ($reportData as $row) {
            $sheet1->setCellValue('A'.$rowNumber, $row['sr_no']);
            $sheet1->setCellValue('B'.$rowNumber, $row['name']);
            $sheet1->setCellValue('C'.$rowNumber, 'FACTORY');
            $sheet1->setCellValue('D'.$rowNumber, FormatIndianNumber($row['qty'], 2));
            $sheet1->setCellValue('E'.$rowNumber, FormatIndianNumber($row['rate'], 2));
            $sheet1->setCellValue('F'.$rowNumber, FormatIndianNumber($row['value'], 2));
            $sheet1->setCellValue('G'.$rowNumber, '');
            $sheet1->getStyle('D'.$rowNumber.':F'.$rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet1->getStyle('A'.$rowNumber.':G'.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $rowNumber++;
        }

        $sheet1->mergeCells('A'.$rowNumber.':E'.$rowNumber);
        $sheet1->setCellValue('A'.$rowNumber, 'TOTAL');
        $sheet1->setCellValue('F'.$rowNumber, FormatIndianNumber($grandTotal, 2));
        $sheet1->getStyle('A'.$rowNumber.':G'.$rowNumber)->getFont()->setBold(true);
        $sheet1->getStyle('A'.$rowNumber.':G'.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $rowNumber += 2;
        $sheet1->mergeCells('A'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue('A'.$rowNumber, '(Extra Sheet to be attached in case of Need)');
        $rowNumber++;
        $sheet1->mergeCells('A'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue('A'.$rowNumber, 'PNB 938 revised (8/2009)');

        // PART B
        $rowNumber += 3;
        $sheet1->mergeCells('A'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue('A'.$rowNumber, 'PART-B');
        $sheet1->getStyle('A'.$rowNumber)->getFont()->setBold(true)->setSize(18);

        $rowNumber += 2;
        $sheet1->mergeCells('A'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue('A'.$rowNumber, 'Sundry Debtors (Receivables) @');
        $sheet1->getStyle('A'.$rowNumber)->getFont()->setBold(true)->setSize(16);

        $rowNumber += 2;
        $sheet1->setCellValue('A'.$rowNumber, 'S.NO');
        $sheet1->mergeCells('B'.$rowNumber.':C'.$rowNumber);
        $sheet1->setCellValue('B'.$rowNumber, 'List of Debtors as per Annexure');
        $sheet1->setCellValue('D'.$rowNumber, 'Amount (Rs.)');
        $sheet1->getStyle('A'.$rowNumber.':D'.$rowNumber)->getFont()->setBold(true);
        $sheet1->getStyle('A'.$rowNumber.':D'.$rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle('A'.$rowNumber.':D'.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $rowNumber++;

        foreach ([
            ['I',   'Upto 90 Days',        $upto90Total],
            ['II',  '>90 Days To 180 Days', $days91to180Total],
            ['III', '>180 Days',            $moreThan180Total],
        ] as [$sno, $label, $val]) {
            $sheet1->setCellValue('A'.$rowNumber, $sno);
            $sheet1->mergeCells('B'.$rowNumber.':C'.$rowNumber);
            $sheet1->setCellValue('B'.$rowNumber, $label);
            $sheet1->setCellValue('D'.$rowNumber, FormatIndianNumber($val, 2));
            $sheet1->getStyle('D'.$rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet1->getStyle('A'.$rowNumber.':D'.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $rowNumber++;
        }

        $sheet1->mergeCells('A'.$rowNumber.':C'.$rowNumber);
        $sheet1->setCellValue('A'.$rowNumber, 'TOTAL');
        $sheet1->setCellValue('D'.$rowNumber, FormatIndianNumber($debtorsGrandTotal, 2));
        $sheet1->getStyle('D'.$rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet1->getStyle('A'.$rowNumber.':D'.$rowNumber)->getFont()->setBold(true);
        $sheet1->getStyle('A'.$rowNumber.':D'.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $rowNumber += 2;
        $sheet1->mergeCells('A'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue('A'.$rowNumber, '@ Sundry debtors acceptable as per terms of sanction');
        $rowNumber++;
        $sheet1->mergeCells('A'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue('A'.$rowNumber, '$ Separate Annexure for i, ii and iii to be enclosed');

        $rowNumber += 3;
        $sheet1->mergeCells('A'.$rowNumber.':C'.$rowNumber);
        $sheet1->setCellValue('A'.$rowNumber, 'Sales during the financial year');
        $sheet1->getStyle('A'.$rowNumber)->getFont()->setBold(true)->setSize(14);
        $sheet1->setCellValue('D'.$rowNumber, 'In Lacs');
        $sheet1->getStyle('D'.$rowNumber)->getFont()->setBold(true);
        $sheet1->getStyle('D'.$rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $rowNumber += 2;
        foreach ([
            ['1', 'Sales upto last month',   $salesUptoLastMonthLacs],
            ['2', 'Sales during the month',  $salesDuringMonthLacs],
            ['3', 'Total Sales',             $totalSalesLacs],
        ] as [$num, $label, $val]) {
            $sheet1->setCellValue('A'.$rowNumber, $num);
            $sheet1->mergeCells('B'.$rowNumber.':C'.$rowNumber);
            $sheet1->setCellValue('B'.$rowNumber, $label);
            $sheet1->setCellValue('D'.$rowNumber, FormatIndianNumber($val, 2));
            $sheet1->getStyle('D'.$rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $rowNumber++;
        }
        $sheet1->getStyle('D'.($rowNumber-3).':D'.($rowNumber-1))->getFont()->setBold(true);

        // PART C
        $rowNumber += 4;
        $sheet1->mergeCells('A'.$rowNumber.':D'.$rowNumber);
        $sheet1->setCellValue('A'.$rowNumber, 'PART - C');
        $sheet1->getStyle('A'.$rowNumber)->getFont()->setBold(true)->setSize(18);

        $rowNumber += 2;
        $sheet1->mergeCells('A'.$rowNumber.':D'.$rowNumber);
        $sheet1->setCellValue('A'.$rowNumber, 'Sundry Creditors');
        $sheet1->getStyle('A'.$rowNumber)->getFont()->setBold(true)->setSize(16);

        $rowNumber += 2;
        $sheet1->mergeCells('A'.$rowNumber.':C'.$rowNumber);
        $sheet1->setCellValue('D'.$rowNumber, 'Amount (Rs.)');
        $sheet1->getStyle('A'.$rowNumber.':D'.$rowNumber)->getFont()->setBold(true);
        $sheet1->getStyle('A'.$rowNumber.':D'.$rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle('A'.$rowNumber.':D'.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $rowNumber++;

        $sheet1->mergeCells('A'.$rowNumber.':C'.$rowNumber);
        $sheet1->setCellValue('A'.$rowNumber, 'List of Creditors as per Annexure #');
        $sheet1->setCellValue('D'.$rowNumber, FormatIndianNumber($creditorsTotal, 2));
        $sheet1->getStyle('D'.$rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet1->getStyle('A'.$rowNumber.':D'.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $rowNumber += 2;
        $sheet1->mergeCells('A'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue('A'.$rowNumber, '# List of Creditors as per Annexure to be enclosed.');

        $rowNumber += 3;
        $sheet1->mergeCells('A'.$rowNumber.':C'.$rowNumber);
        $sheet1->setCellValue('A'.$rowNumber, 'Purchase during the Financial Year');
        $sheet1->getStyle('A'.$rowNumber)->getFont()->setBold(true)->setSize(14);
        $sheet1->setCellValue('D'.$rowNumber, 'In Lacs');
        $sheet1->getStyle('D'.$rowNumber)->getFont()->setBold(true);
        $sheet1->getStyle('D'.$rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $rowNumber += 2;
        foreach ([
            ['1', 'Purchases upto last month',   $purchaseUptoLastMonthLacs],
            ['2', 'Purchases during the month',  $purchaseDuringMonthLacs],
            ['3', 'Total Purchase',              $totalPurchaseLacs],
        ] as [$num, $label, $val]) {
            $sheet1->setCellValue('A'.$rowNumber, $num);
            $sheet1->mergeCells('B'.$rowNumber.':C'.$rowNumber);
            $sheet1->setCellValue('B'.$rowNumber, $label);
            $sheet1->setCellValue('D'.$rowNumber, FormatIndianNumber($val, 2));
            $sheet1->getStyle('D'.$rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $rowNumber++;
        }
        $sheet1->getStyle('D'.($rowNumber-3).':D'.($rowNumber-1))->getFont()->setBold(true);

        // Notes + declarations (static - same as your original)
        $rowNumber += 3;
        $sheet1->mergeCells('A'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue('A'.$rowNumber, '* Note: as per the periodicity of submission of stock statement in terms of sanction ( fortnightly / monthly / quarterly / half yearly )');
        $sheet1->getStyle('A'.$rowNumber)->getAlignment()->setWrapText(true);
        $sheet1->getRowDimension($rowNumber)->setRowHeight(40);

        $rowNumber += 2;
        $sheet1->mergeCells('A'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue('A'.$rowNumber, '(Extra sheet to be attached in case of need)');
        $sheet1->getStyle('A'.$rowNumber)->getFont()->setItalic(true);

        $rowNumber += 4;
        $declarations = [
            'I/We declare and acknowledge that all the goods noted above stand hypothecated to the bank and the same are my/our own property and that I/We/am/are entitled to hypothecate them with the bank. They are unencumbered and are not subject to any other lien, claim or charge of any sort.',
            'I/We certify that the quality and quantity of the stock are correct and in accordance with the entries in our record. The stock shown do not include damaged unsaleable / obsolete / old goods.',
            'I/We certify that the valuation of stocks has been made as per mandatory Accounting Standard (AS-2) (i.e. cost price / Net Realisable Value whichever is lower) as prescribed by ICAI.',
            'I/We certify that the above goods are adequately covered by insurance against fire and other necessary risks in terms of sanction. All premia on insurance policies have been paid and these are in force.',
            'I/We certify that the amount of sundry debtors / sundry creditors and Sales / Purchase are correct and in accordance with the entries in our record.',
            'In case the above contain any mis-statement (of which the bank is the sole judge) or there be any shortage of security, I/We shall render myself / ourselves liable to legal action.',
        ];
        foreach ($declarations as $i => $text) {
            $sheet1->setCellValue('A'.$rowNumber, ($i+1).')');
            $sheet1->mergeCells('B'.$rowNumber.':G'.$rowNumber);
            $sheet1->setCellValue('B'.$rowNumber, $text);
            $sheet1->getStyle('B'.$rowNumber)->getAlignment()->setWrapText(true);
            $sheet1->getRowDimension($rowNumber)->setRowHeight(55);
            $rowNumber += 2;
        }

        $rowNumber += 2;
        $sheet1->mergeCells('E'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue('E'.$rowNumber, 'BORROWER / AUTHORISED SIGNATORY');
        $sheet1->getStyle('E'.$rowNumber)->getFont()->setBold(true)->setSize(14);
        $sheet1->getStyle('E'.$rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $rowNumber += 4;
        $sheet1->mergeCells('E'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue('E'.$rowNumber, 'For '.$company->company_name);
        $sheet1->getStyle('E'.$rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $rowNumber += 3;
        $sheet1->mergeCells('E'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue('E'.$rowNumber, 'Director');
        $sheet1->getStyle('E'.$rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $rowNumber += 5;

        $sheet1->mergeCells('A'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue('A'.$rowNumber, 'FOR OFFICE USE ONLY');
        $sheet1->getStyle('A'.$rowNumber)->getFont()->setBold(true)->setSize(14);

        $rowNumber += 2;
        $sheet1->mergeCells('A'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue(
            'A'.$rowNumber,
            '1. Limit _______________________'
        );

        $rowNumber += 2;
        $sheet1->mergeCells('A'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue(
            'A'.$rowNumber,
            '2. Value of security (value of stock minus surplus sundry creditors, if any, to be deducted in terms of sanction) _______________________'
        );

        $sheet1->getStyle('A'.$rowNumber)
            ->getAlignment()
            ->setWrapText(true);

        $rowNumber += 3;
        $sheet1->mergeCells('A'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue(
            'A'.$rowNumber,
            '3. Margin (as per sanction) _______________________'
        );

        $rowNumber += 2;
        $sheet1->mergeCells('A'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue(
            'A'.$rowNumber,
            '4. Drawing power (value of security as per above less margin) _______________________'
        );

        $rowNumber += 2;
        $sheet1->mergeCells('A'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue(
            'A'.$rowNumber,
            '5. SRM updated on _________ Entered by (Name) __________________'
        );

        $rowNumber += 1;
        $sheet1->mergeCells('A'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue(
            'A'.$rowNumber,
            '   Verified by (Name) __________________'
        );

        $rowNumber += 2;
        $sheet1->mergeCells('A'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue(
            'A'.$rowNumber,
            '6. Inspected on _________ by (Name) __________________'
        );

        $rowNumber += 2;
        $sheet1->mergeCells('A'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue(
            'A'.$rowNumber,
            '   Designation ___________________________'
        );

        $rowNumber += 3;
        $sheet1->mergeCells('A'.$rowNumber.':G'.$rowNumber);
        $sheet1->setCellValue(
            'A'.$rowNumber,
            '   (Signature) ___________________________'
        );
        // --- SHEET 2: CREDITORS ---
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Creditors');
        $sheet2->getColumnDimension('A')->setWidth(10);
        $sheet2->getColumnDimension('B')->setWidth(45);
        $sheet2->getColumnDimension('C')->setWidth(25);

        $sheet2->mergeCells('A1:C1');
        $sheet2->setCellValue('A1', $company->company_name);
        $sheet2->getStyle('A1:C1')->getFont()->setBold(true)->setSize(16);
        $sheet2->getStyle('A1:C1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet2->mergeCells('A2:C2');
        $sheet2->setCellValue('A2', 'Sundry Creditors Closing Balance As On ' . date('d-m-Y', strtotime($lastDate)));
        $sheet2->getStyle('A2:C2')->getFont()->setBold(true);
        $sheet2->getStyle('A2:C2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet2->setCellValue('A4', 'Sr No');
        $sheet2->setCellValue('B4', 'Account Name');
        $sheet2->setCellValue('C4', 'Amount');
        $sheet2->getStyle('A4:C4')->getFont()->setBold(true);
        $sheet2->getStyle('A4:C4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle('A4:C4')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $rowNumber = 5;
        foreach ($creditorsRows as $row) {
            $sheet2->setCellValue('A'.$rowNumber, $row['sr_no']);
            $sheet2->setCellValue('B'.$rowNumber, $row['account_name']);
            $sheet2->setCellValue('C'.$rowNumber, $row['closing_balance']);
            $sheet2->getStyle('C'.$rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet2->getStyle('A'.$rowNumber.':C'.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $rowNumber++;
        }

        $creditorsTotalBalance = $request->input('creditors_total_balance', '');
        $sheet2->mergeCells('A'.$rowNumber.':B'.$rowNumber);
        $sheet2->setCellValue('A'.$rowNumber, 'TOTAL');
        $sheet2->setCellValue('C'.$rowNumber, $creditorsTotalBalance);
        $sheet2->getStyle('A'.$rowNumber.':C'.$rowNumber)->getFont()->setBold(true);
        $sheet2->getStyle('A'.$rowNumber.':C'.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet2->getStyle('C'.$rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // --- SHEET 3: DEBTORS ---
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Debtors');
        $sheet3->getColumnDimension('A')->setWidth(10);
        $sheet3->getColumnDimension('B')->setWidth(45);
        $sheet3->getColumnDimension('C')->setWidth(20);
        $sheet3->getColumnDimension('D')->setWidth(20);

        $sheet3->mergeCells('A1:D1');
        $sheet3->setCellValue('A1', $company->company_name);
        $sheet3->getStyle('A1:D1')->getFont()->setBold(true)->setSize(16);
        $sheet3->getStyle('A1:D1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet3->mergeCells('A2:D2');
        $sheet3->setCellValue('A2', 'Sundry Debtors As On ' . date('d-m-Y', strtotime($lastDate)));
        $sheet3->getStyle('A2:D2')->getFont()->setBold(true);
        $sheet3->getStyle('A2:D2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet3->setCellValue('A4', 'Sr No');
        $sheet3->setCellValue('B4', 'Particulars');
        $sheet3->setCellValue('C4', '<= 90 Days');
        $sheet3->setCellValue('D4', '>= 91 Days');
        $sheet3->getStyle('A4:D4')->getFont()->setBold(true);
        $sheet3->getStyle('A4:D4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet3->getStyle('A4:D4')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $rowNumber = 5;
        foreach ($debtorsRows as $row) {
            $sheet3->setCellValue('A'.$rowNumber, $row['sr_no']);
            $sheet3->setCellValue('B'.$rowNumber, $row['party']);
            $sheet3->setCellValue('C'.$rowNumber, FormatIndianNumber($row['less90'], 2));
            $sheet3->setCellValue('D'.$rowNumber, FormatIndianNumber($row['greater90'], 2));
            $sheet3->getStyle('C'.$rowNumber.':D'.$rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet3->getStyle('A'.$rowNumber.':D'.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $rowNumber++;
        }

        $sheet3->mergeCells('A'.$rowNumber.':B'.$rowNumber);
        $sheet3->setCellValue('A'.$rowNumber, 'TOTAL');
        $sheet3->setCellValue('C'.$rowNumber, FormatIndianNumber($less90Total, 2));
        $sheet3->setCellValue('D'.$rowNumber, FormatIndianNumber($greater90Total, 2));
        $sheet3->getStyle('A'.$rowNumber.':D'.$rowNumber)->getFont()->setBold(true);
        $sheet3->getStyle('A'.$rowNumber.':D'.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet3->getStyle('C'.$rowNumber.':D'.$rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // --- SHEET 4: CALCULATION ---
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('Calculation');
        $sheet4->getColumnDimension('A')->setWidth(25);
        $sheet4->getColumnDimension('B')->setWidth(25);
        $sheet4->getColumnDimension('C')->setWidth(25);
        $sheet4->getColumnDimension('D')->setWidth(25);

        $sheet4->mergeCells('A1:D1');
        $sheet4->setCellValue('A1', 'FOR BANK');
        $sheet4->getStyle('A1:D1')->getFont()->setBold(true)->setSize(18);
        $sheet4->getStyle('A1:D1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet4->setCellValue('A3', '');
        $sheet4->setCellValue('B3', 'Debtors');
        $sheet4->setCellValue('C3', 'Creditors');
        $sheet4->setCellValue('D3', 'Availability');
        $sheet4->getStyle('A3:D3')->getFont()->setBold(true);
        $sheet4->getStyle('A3:D3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet4->setCellValue('A4', 'Assesable');
        $sheet4->setCellValue('B4', FormatIndianNumber($upto90Total, 2));
        $sheet4->setCellValue('C4', FormatIndianNumber($creditorsTotal, 2));
        $sheet4->setCellValue('D4', FormatIndianNumber($debtorsAvailability, 2));

        $sheet4->setCellValue('A5', 'Stock');
        $sheet4->setCellValue('C5', FormatIndianNumber($stockTotal, 2));
        $sheet4->setCellValue('D5', FormatIndianNumber($stockAvailability, 2));

        $sheet4->mergeCells('A6:C6');
        $sheet4->setCellValue('A6', 'D.P');
        $sheet4->setCellValue('D6', FormatIndianNumber($dp, 2));

        $sheet4->getStyle('A3:D6')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet4->getStyle('A3:D6')->getFont()->setBold(true);
        $sheet4->getStyle('B4:D6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // ===== OUTPUT =====
        $writer   = new Xlsx($spreadsheet);
        $fileName = 'Complete_Report_' . $month . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // =========================================
    // DOWNLOAD PDF 
    // =========================================

    public function downloadPdf(Request $request)
    {
        $month     = $request->input('month');
        $stockType = $request->input('stock_type');
        $bankId    = (int)$request->input('bank_id');
        $limitAmount = $request->input('limit_amount', '');

        $company_id = Session::get('user_company_id');
        $company    = DB::table('companies')->where('id', $company_id)->first();
        $bank       = DB::table('banks')->where('id', $bankId)->first();
        $lastDate   = date('Y-m-t', strtotime($month));

        // ===== EDITED STOCK ROWS =====
        $stockNames  = $request->input('stock_name', []);
        $stockQtys   = $request->input('stock_qty', []);
        $stockRates  = $request->input('stock_rate', []);
        $stockValues = $request->input('stock_value', []);

        $reportData = [];
        $grandTotal = 0;

        foreach ($stockNames as $i => $name) {
            $value = (float)str_replace(',', '', $stockValues[$i] ?? 0);
            $grandTotal += $value;
            $reportData[] = [
                'sr_no' => $i + 1,
                'name'  => $name,
                'qty'   => (float)str_replace(',', '', $stockQtys[$i]  ?? 0),
                'rate'  => (float)str_replace(',', '', $stockRates[$i] ?? 0),
                'value' => $value,
            ];
        }
        $stockRows = $reportData;

        $upto90Total = (float)str_replace(
            ',',
            '',
            $request->input(
                'calc_upto90',
                $request->input('upto90_total', 0)
            )
        );
        $days91to180Total = (float)str_replace(',', '', $request->input('days91to180_total', 0));
        $moreThan180Total = (float)str_replace(',', '', $request->input('moreThan180_total', 0));
        $debtorsGrandTotal = $upto90Total + $days91to180Total + $moreThan180Total;

        $salesUptoLastMonthLacs  = (float)str_replace(',', '', $request->input('sales_upto_last_month_lacs', 0));
        $salesDuringMonthLacs    = (float)str_replace(',', '', $request->input('sales_during_month_lacs', 0));
        $totalSalesLacs          = $salesUptoLastMonthLacs + $salesDuringMonthLacs;

        $creditorsTotal = (float)str_replace(
            ',',
            '',
            $request->input(
                'calc_creditors',
                $request->input('creditors_total', 0)
            )
        );

        $purchaseUptoLastMonthLacs = (float)str_replace(',', '', $request->input('purchase_upto_last_month_lacs', 0));
        $purchaseDuringMonthLacs   = (float)str_replace(',', '', $request->input('purchase_during_month_lacs', 0));
        $totalPurchaseLacs         = $purchaseUptoLastMonthLacs + $purchaseDuringMonthLacs;

        $creditorNames    = $request->input('creditor_name', []);
        $creditorBalances = $request->input('creditor_balance', []);
        $creditorsRows    = [];
        foreach ($creditorNames as $i => $name) {
            $creditorsRows[] = [
                'sr_no'           => $i + 1,
                'account_name'    => $name,
                'closing_balance' => $creditorBalances[$i] ?? '',
            ];
        }

        $debtorParties   = $request->input('debtor_party', []);
        $debtorLess90    = $request->input('debtor_less90', []);
        $debtorGreater90 = $request->input('debtor_greater90', []);
        $debtorsRows     = [];
        $less90Total     = 0;
        $greater90Total  = 0;

        foreach ($debtorParties as $i => $party) {
            $l = (float)str_replace(',', '', $debtorLess90[$i]    ?? 0);
            $g = (float)str_replace(',', '', $debtorGreater90[$i] ?? 0);
            $less90Total    += $l;
            $greater90Total += $g;
            $debtorsRows[] = [
                'sr_no'     => $i + 1,
                'party'     => $party,
                'less90'    => $l,
                'greater90' => $g,
            ];
        }

        $stockTotal          = (float)str_replace(',', '', $request->input('stock_total', 0));
        $debtorsAvailability = (float)str_replace(',', '', $request->input('debtors_availability', 0));
        $stockAvailability   = (float)str_replace(',', '', $request->input('stock_availability', 0));
        $dp                  = $debtorsAvailability + $stockAvailability;
        $creditorsTotalBalance = $request->input('creditors_total_balance', '');
        $isPdf = true;
        // Build the view and pass all data to a PDF blade
        $html = view('ExportMonthlyReportPreview', compact(
            'company', 'bank', 'month', 'lastDate', 'stockType',
            'reportData', 'grandTotal',
            'upto90Total', 'days91to180Total', 'moreThan180Total', 'debtorsGrandTotal',
            'salesUptoLastMonthLacs', 'salesDuringMonthLacs', 'totalSalesLacs',
            'creditorsTotal', 'creditorsRows', 'creditorsTotalBalance',
            'debtorsRows', 'less90Total', 'greater90Total',
            'purchaseUptoLastMonthLacs', 'purchaseDuringMonthLacs', 'totalPurchaseLacs',
            'stockTotal', 'debtorsAvailability', 'stockAvailability', 'dp','stockRows', 'limitAmount', 'isPdf'
        ))->render();

        // Using DomPDF (install via: composer require barryvdh/laravel-dompdf)
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait');

        return $pdf->download('Complete_Report_' . $month . '.pdf');
    }
}