<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use session;
use DB;
use App\Helpers\CommonHelper;
use Carbon\Carbon;
use App\Models\Accounts;
use App\Models\ManageItems;
use App\Models\SubItem;
use App\Models\Units;
use App\Models\SparePart;
use App\Models\Purchase;
use App\Models\SparePartSupplierOffer;
use App\Models\SparePartSupplier;
use App\Models\SparePartItem;
use App\Models\SparePartItemSubItem;
use App\Models\SparePartItemSubItemSize;
use App\Models\SaleOrderSetting;
use App\Models\ItemGroups;
use App\Models\BillSundrys;
use App\Models\SparePartConfiguration;
use App\Models\SparePartTermsCondition;
use App\Models\Companies;
use App\Models\SaleInvoiceConfiguration;
use App\Models\Journal;
class SparePartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $companyId = Session::get('user_company_id');

        // Status = 1 → Pending
        $spare_parts = SparePart::with([
            'account:id,account_name',
            'items.item:id,name'
        ])
        ->where('company_id', $companyId)
        ->where('status', 1)
        ->get();

        // Status = 2 → Pending for Add Purchase
        $pending_purchase = SparePart::with([
            'account:id,account_name',
            'items.item:id,name'
        ])
        ->where('company_id', $companyId)
        ->where('status', 2)
        ->get();
        // Status = 3 → Completed Purchase
        $from_date = "";$to_date = "";
        $purchaseQuery = \App\Models\Purchase::with([
                                    'sparePart.account:id,account_name',
                                    'descriptions.item:id,name'
                                ])
                                ->whereNotNull('spare_part_id')
                                ->where('company_id', $companyId);
            // Journal Entries
        $journalQuery = Journal::with([
            'sparePart.account:id,account_name'
        ])
        ->whereNotNull('spare_part_id')
        ->where('company_id', $companyId);

        if($request->filled(['from_date', 'to_date'])) {
            $from_date = $request->from_date;
            $to_date = $request->to_date;
            $purchaseQuery->whereBetween('date', [$from_date, $to_date]);
            $journalQuery->whereBetween('date', [$from_date, $to_date]);
        } else {
            $purchaseQuery->latest()->limit(10);
            $journalQuery->latest()->limit(10);
        }
                $purchases = $purchaseQuery->get();
                $journals  = $journalQuery->get();
                $completed_purchase = $purchases->sortByDesc('date')->values();
                $completed_journal = $journals->sortByDesc('date')->values();
                // echo "<pre>";print_r($completed_journal->toArray());die;
        return view('supplier.spare_part', [
            "spare_parts" => $spare_parts,
            "pending_purchase" => $pending_purchase,
            "completed_purchase" => $completed_purchase,
            'completed_journal'=>$completed_journal,
            "from_date"=>$from_date,
            "to_date"=>$to_date,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
        public function create()
    {
        $formCompanyId   = Session::get('user_company_id');
        $formCompanyName = Companies::where('id', $formCompanyId)->value('company_name');
        $group_ids = CommonHelper::getAllChildGroupIds(3, Session::get('user_company_id'));
        array_push($group_ids, 3);

        $group_ids = array_merge($group_ids, CommonHelper::getAllChildGroupIds(11, Session::get('user_company_id')));
        array_push($group_ids, 11);
        $group_ids = array_unique($group_ids);

        $accounts = Accounts::where('delete', '0')
                            ->where('status', '1')
                            ->whereIn('company_id', [Session::get('user_company_id'), 0])
                            ->whereIn('under_group', $group_ids)
                            ->select('id', 'account_name')
                            ->orderBy('account_name')
                            ->get();

        // fetch items with unit
        $items = ManageItems::select('id', 'name', 'u_name')
                    ->where('company_id', Session::get('user_company_id'))
                    ->where('status', '1')
                    ->where('delete', '0')
                    ->orderBy('name')
                    ->get();
        $draftItems = session('manage_item_selection', []);
        $prefillItems = [];

        if (!empty($draftItems)) {
            foreach ($draftItems as $row) {

                $item = ManageItems::find($row['item_id']);

                if ($item) {
                    $prefillItems[] = [
                        'item_id'  => $item->id,
                        'quantity' => $row['quantity'],
                        'unit_id'  => $item->u_name,
                    ];
                }
            }
        }

        return view('supplier.add_spare_part', compact(
            'accounts',
            'items',
            'formCompanyId',
            'formCompanyName',
            'prefillItems'
        ));
    }
    public function store(Request $request)
    {
        if (empty($request->item_id)) {
            return back()->with('error', 'Please add at least one spare part item.');
        }
        $formCompanyId = $request->form_company_id;
        if (!$formCompanyId) {
            return back()->withError('Invalid company context');
        }
        $sparePart = SparePart::create([
            'account_id' => null,
            'status'     => 1,
            'company_id' => $formCompanyId,
            'created_by' => Session::get('user_id'),
            'source'     => 'manual',

            // ✅ Header Fields (Always stored)
            'department'             => trim($request->department ?? ''),
            'purpose'                => trim($request->purpose ?? ''),
            'department_head'        => trim($request->department_head ?? ''),
            'requirement_by'         => trim($request->requirement_by ?? ''),
            'hod'                    => trim($request->hod ?? ''),
            'approved_for_quotation' => trim($request->approved_for_quotation ?? ''),
        ]);

        foreach ($request->item_id as $index => $itemId) {

            $unitId       = $request->unit_id[$index] ?? null;
            $quantity     = $request->quantity[$index] ?? 0;
            $narration    = $request->narration[$index] ?? null;
            $requiredDate = $request->required_date[$index] ?? null;

            $unitName = $unitId
                ? Units::where('id', $unitId)->value('name')
                : null;

            SparePartItem::create([
                'spare_part_id' => $sparePart->id,
                'item_id'       => $itemId,
                'unit'          => $unitName,
                'quantity'      => $quantity,
                'narration'     => $narration,
                'required_date' => $requiredDate, 
                'price'         => 0,
                'status'        => 1,
                'company_id'    => $formCompanyId,
            ]);
        }

        session()->forget('manage_item_selection');
        return redirect()->route('spare-part.index')
            ->with('success', 'Spare Parts added successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $spare_part = SparePart::with([
            'items.item',
            'supplierOffers.account',
            'supplierOffers.item.unit'   
        ])->findOrFail($id);

        $company_data = Companies::find(session('user_company_id'));

        return view('supplier.view_spare_part', compact('spare_part', 'company_data'));
    }


    public function downloadPdf($id)
    {
        $spare_part = SparePart::with('items.item')->findOrFail($id);
        $company_data = Companies::find(session('user_company_id'));

        $pdf = \PDF::loadView('supplier.view_spare_part_pdf', compact('spare_part', 'company_data'));
        return $pdf->download('spare_part_'.$spare_part->id.'.pdf');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // Show edit page
    public function edit($id)
    {
        $sparePart = SparePart::with(['items'])->findOrFail($id);
        $formCompanyId   = $sparePart->company_id;
        $formCompanyName = Companies::where('id', $formCompanyId)->value('company_name');

        if ($formCompanyId != Session::get('user_company_id')) {
            abort(403, 'You are trying to edit a Spare Part of another company');
        }
        $items = ManageItems::select('id', 'name', 'u_name')
                            ->where('company_id', Session::get('user_company_id'))
                            ->where('status', '1')
                            ->where('delete', '0')
                            ->orderBy('name')
                            ->get();

        return view('supplier.edit_spare_part', compact(
            'sparePart',
            'items',
            'formCompanyId',
            'formCompanyName'
        ));
    }

    public function update(Request $request, $id)
    {
        $formCompanyId = $request->form_company_id;

        if (!$formCompanyId) {
            abort(403, 'Invalid company context.');
        }

        $sparePart = SparePart::with('items')->findOrFail($id);

        if ($sparePart->company_id != Session::get('user_company_id')) {
            abort(403, 'Unauthorized action.');
        }

        $sparePart->update([
            'department'             => trim($request->department ?? ''),
            'purpose'                => trim($request->purpose ?? ''),
            'department_head'        => trim($request->department_head ?? ''),
            'requirement_by'         => trim($request->requirement_by ?? ''),
            'hod'                    => trim($request->hod ?? ''),
            'approved_for_quotation' => trim($request->approved_for_quotation ?? ''),
        ]);

        $sparePart->items()->delete();

        foreach ($request->item_id as $index => $itemId) {

            $item = ManageItems::find($itemId);

            $unitName = null;

            if ($item && $item->u_name) {
                $unitName = Units::where('id', $item->u_name)->value('name');
            }

            SparePartItem::create([
                'spare_part_id' => $sparePart->id,
                'item_id'       => $itemId,
                'unit'          => $unitName ?? '',
                'quantity'      => $request->quantity[$index] ?? 0,
                'required_date' => $request->required_date[$index] ?? null,
                'narration'     => $request->narration[$index] ?? null,
                'price'         => 0,
                'status'        => 1,
                'company_id'    => $formCompanyId,
            ]);
        }

        return redirect()->route('spare-part.index')
            ->with('success', 'Spare Part updated successfully!');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $spare = SparePart::with('items')->find($id);

        if (!$spare) {
            return redirect()->back()->with('error', 'Record not found.');
        }

        // ❌ Completed Purchase → NEVER delete
        if ($spare->status == 3) {
            return redirect()->back()->with(
                'error',
                'Completed purchase cannot be deleted.'
            );
        }

        DB::transaction(function () use ($spare) {

            // 🔹 Delete items
            foreach ($spare->items as $item) {

                // ✅ Release qty ONLY if created from Manage Item
                if ($spare->source === 'manage_item') {
                    $item->delete(); // affects ordered qty
                } else {
                    // ❌ Manual quotation → delete without inventory effect
                    $item->delete();
                }
            }

            // 🔹 Delete supplier offers if Pending Purchase
            if ($spare->status == 2) {
                DB::table('spare_part_supplier_offers')
                    ->where('spare_part_id', $spare->id)
                    ->delete();
            }

            // 🔹 Delete spare part
            $spare->delete();
        });

        // ✅ Messages
        if ($spare->source === 'manage_item') {
            return redirect()->back()->with(
                'success',
                'Spare part deleted and quantity released back to To Be Order.'
            );
        }

        return redirect()->back()->with(
            'success',
            'Spare part deleted successfully.'
        );
    }

    public function getUnitName($id)
    {
        $unit = Units::where('id', $id)
                    ->where('delete', '0')  
                    ->first();

        return response()->json([
            'name' => $unit ? $unit->name : ''
        ]);
    }


    public function createStart($id)
    {
        $top_groups = [3, 11];
        $all_groups = [];

        foreach ($top_groups as $group_id) {
            $all_groups[] = $group_id;
            $all_groups = array_merge(
                $all_groups,
                CommonHelper::getAllChildGroupIds(
                    $group_id,
                    Session::get('user_company_id')
                )
            );
        }

        $all_groups = array_unique($all_groups);
        $companyId = Session::get('user_company_id');
        $company = Companies::select('id','company_name')
                    ->findOrFail($companyId);
        $accounts = Accounts::where('delete', '0')
            ->where('status', '1')
            ->whereIn('company_id', [Session::get('user_company_id'), 0])
            ->whereIn('under_group', $all_groups)
            ->select('id', 'account_name')
            ->orderBy('account_name')
            ->get();

        if ($id != 0) {

            $sparePart = SparePart::with(['items.item'])->findOrFail($id);

            // 🔹 ADD (safety)
            $sparePart->account_id = $sparePart->account_id ?? null;

            // 🔹 CHANGE orderBy (NOT filter)
            $offers = SparePartSupplierOffer::with([
                    'account:id,account_name',
                    'item:id,name,u_name'
                ])
                ->where('spare_part_id', $sparePart->id)
                ->where('company_id', Session::get('user_company_id'))
                ->orderBy('account_id') // 🔹 changed
                ->orderBy('item_id')

                ->get();
        }

        else {

            $draftItems = session('manage_item_selection');

            if (!$draftItems) {
                return redirect()->route('spare-part.items')
                    ->with('error', 'Please select items first.');
            }

            $sparePart = (object)[
                'id' => 0,
                'account_id' => null, // 🔹 ADD for blade consistency
                'items' => collect($draftItems)->map(function ($row) {

                    $item = ManageItems::findOrFail($row['item_id']);

                    return (object)[
                        'item_id'  => $row['item_id'],
                        'quantity' => $row['quantity'],
                        'unit'     => DB::table('units')
                            ->where('id', $item->u_name)
                            ->value('name'),
                        'item'     => $item,
                    ];
                })
            ];

            $draftOffers = session('draft_supplier_offers', []);
            $offers = collect();

            foreach ($draftOffers as $offer) {
                foreach ($offer['items'] as $item) {

                    $offers->push((object)[
                        'account_id'       => $offer['account_id'],
                        'account'          => Accounts::find($offer['account_id']),
                        'item_id'          => $item['item_id'],
                        'item'             => ManageItems::find($item['item_id']),
                        'offered_quantity' => $item['quantity'],
                        'offered_price'    => $item['price'],
                    ]);
                }
            }
        }

        return view(
            'supplier.start_spare_part',
            compact('accounts', 'sparePart', 'offers', 'company')
        );
    }


    public function updateStart(Request $request, $id)
    {
        $sparePart = SparePart::with('items')->findOrFail($id);

        foreach ($sparePart->items as $index => $item) {
            $item->quantity = $request->quantity[$index];
            $item->price    = $request->price[$index];
            $item->save();
        }

        return redirect()->route('spare-part.start', $id)
            ->with('success', 'Items updated successfully');
    }
    // Show the new Start page with prefilled data
    public function createStartNew($id)
    {
        $formCompanyId   = Session::get('user_company_id');
        $formCompanyName = Companies::where('id', $formCompanyId)->value('company_name');

        $sparePart = SparePart::with('items.item')->findOrFail($id);

        // ================= VEHICLE ENTRY PREFILL =================
        $vehicleEntryId = request()->query('vehicle_entry_id');
        $vehicleEntry   = null;

        if ($vehicleEntryId) {
            $vehicleEntry = \App\Models\SupplierPurchaseVehicleDetail::where('id', $vehicleEntryId)
                ->where('company_id', $formCompanyId)
                ->first();
        }

        // ================= ACCOUNTS =================
        $top_groups = [3, 11];
        $all_groups = [];

        foreach ($top_groups as $group_id) {
            $all_groups[] = $group_id;
            $all_groups = array_merge(
                $all_groups,
                CommonHelper::getAllChildGroupIds($group_id, $formCompanyId)
            );
        }

        $all_groups = array_unique($all_groups);

        $accounts = Accounts::where('delete', '0')
            ->where('status', '1')
            ->whereIn('company_id', [$formCompanyId, 0])
            ->whereIn('under_group', $all_groups)
            ->select('id', 'account_name')
            ->orderBy('account_name')
            ->get();

        $billsundry = BillSundrys::where('delete', '0')
            ->where('status', '1')
            ->whereIn('company_id', [$formCompanyId, 0])
            ->orderBy('name')
            ->get();

        return view(
            'supplier.new_start_spare_part',
            compact(
                'sparePart',
                'accounts',
                'billsundry',
                'formCompanyId',
                'formCompanyName',
                'vehicleEntry',
                'vehicleEntryId'
            )
        );
    }

    public function editStart($id)
    {
        $top_groups = [3, 11];
        $all_groups = [];

        foreach ($top_groups as $group_id) {
            $all_groups[] = $group_id;
            $all_groups = array_merge($all_groups, 
                CommonHelper::getAllChildGroupIds($group_id, Session::get('user_company_id'))
            );
        }

        $all_groups = array_unique($all_groups);

        $accounts = Accounts::where('delete', '0')
            ->where('status', '1')
            ->whereIn('company_id', [Session::get('user_company_id'), 0])
            ->whereIn('under_group', $all_groups)
            ->select('id', 'account_name')
            ->orderBy('account_name')
            ->get();

        $items = ManageItems::select('id', 'name', 'u_name')
            ->where('company_id', Session::get('user_company_id'))
            ->where('status', '1')
            ->where('delete', '0')
            ->orderBy('name')
            ->get();

        $sparePart = SparePart::with(['items.item'])->findOrFail($id);

        return view('supplier.edit_start_spare_part', compact('accounts', 'items', 'sparePart'));
    }


    public function viewStart($id)
    {
        $spare = SparePart::with([
        'items.item',
        'account.stateMaster',
        'approvedBy',
        'billToAccount.stateMaster',
        'shipToAccount.stateMaster',

        'billToCompany',
        'shipToCompany',
        ])->findOrFail($id);

        $companyId = Session::get('user_company_id');
        $company_data = Companies::find(session('user_company_id')); // fetch company
        $terms = SparePartTermsCondition::where('company_id', $companyId)
                ->where('status', 1)
                ->orderBy('sequence')
                ->get();
        return view('supplier.view_start_spare_part', compact('spare', 'company_data', 'terms'));
    }

    public function items()
    {
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');
        $formCompanyId   = $company_id;
        $formCompanyName = Companies::where('id', $company_id)->value('company_name');
        $spareGroupIds = SaleOrderSetting::where('company_id', $company_id)
            ->where('setting_type', 'PURCHASE GROUP')
            ->where('setting_for', 'PURCHASE ORDER')
            ->where('group_type', 'SPARE PART')
            ->pluck('item_id');

        $groups = ItemGroups::with(['items' => function ($q) use ($company_id) {
            $q->where('company_id', $company_id)
            ->where('delete', '0')
            ->orderBy('name')
            ->where('status', '1');
        }])->whereIn('id', $spareGroupIds)->orderBy('group_name')->get();

        foreach ($groups as $group) {
            foreach ($group->items as $item) {

                $item->current_qty = DB::table('item_ledger')
                    ->where('company_id', $company_id)
                    ->where('item_id', $item->id)
                    ->where('status', '1')
                    ->where('delete_status', '0')
                    ->selectRaw('COALESCE(SUM(in_weight),0) - COALESCE(SUM(out_weight),0)')
                    ->value(DB::raw('COALESCE(SUM(in_weight),0) - COALESCE(SUM(out_weight),0)')) ?? 0;

                $item->maintain_quantity = $item->maintain_quantity ?? 0;

                $hasPendingSparePart = DB::table('spare_parts')
                    ->where('company_id', $company_id)
                    ->where('status', 2) 
                    ->exists();

                $item->ordered_quantity = DB::table('spare_part_items')
                    ->join('spare_parts', 'spare_parts.id', '=', 'spare_part_items.spare_part_id')
                    ->where('spare_parts.company_id', $company_id)
                    ->whereIn('spare_parts.status', [1, 2]) // ✅ Pending Quotation + Pending Purchase
                    ->where('spare_part_items.item_id', $item->id)
                    ->sum('spare_part_items.quantity');
            }
        }

        return view('supplier.spare_part_items', compact('groups', 'formCompanyId', 'formCompanyName'));
    }

    public function nextFromItemList(Request $request)
    {
        $company_id = $request->form_company_id ?? Session::get('user_company_id');

        $selectedItems = [];

        foreach ($request->items ?? [] as $itemId => $row) {
            if (!empty($row['selected']) && ($row['quantity'] ?? 0) > 0) {
                $selectedItems[] = [
                    'item_id'  => $itemId,
                    'quantity' => $row['quantity'],
                ];
            }
        }

        if (empty($selectedItems)) {
            return back()->with('error', 'Please select items with order quantity.');
        }
        session([
            'manage_item_selection' => $selectedItems
        ]);

        return redirect()->route('spare-part.create');
    }

    public function saveMaintainQuantity(Request $request)
    {
        foreach ($request->maintain_qty ?? [] as $itemId => $qty) {
            ManageItems::where('id', $itemId)->update([
                'maintain_quantity' => $qty
            ]);
        }

        return redirect()
            ->route('spare-part.items')
            ->with('success', 'Maintain quantity saved successfully.');
    }

   public function saveOffers(Request $request, $id)
    {
        $sparePart = SparePart::where('id', $id)
            ->whereIn('status', [0, 1, 2])
            ->firstOrFail();

        foreach ($request->offers as $offer) {

            SparePartSupplierOffer::where('spare_part_id', $sparePart->id)
                ->where('account_id', $offer['account_id'])
                ->where('company_id', Session::get('user_company_id'))
                ->delete();

            foreach ($offer['items'] as $item) {
                SparePartSupplierOffer::create([
                    'spare_part_id'    => $sparePart->id,
                    'account_id'       => $offer['account_id'],
                    'item_id'          => $item['item_id'],
                    'offered_quantity' => $item['quantity'],
                    'offered_price'    => $item['price'],
                    'company_id'       => Session::get('user_company_id'),
                    'created_by'       => auth()->id(),
                ]);
            }
        }

        return back()->with('success', 'Supplier offers saved correctly');
    }

    public function finalizeSupplier(Request $request, $id)
    {
        $request->validate([
            'selected_account_id'   => 'required|exists:accounts,id',
            'po_date'               => 'required|date',
            'freight'             => 'required|in:0,1',
        ]);
        // BILL TO
        $billToCompanyId = null;
        $billToAccountId = null;

        if (str_starts_with($request->bill_to_selector, 'company_')) {
            $billToCompanyId = str_replace('company_', '', $request->bill_to_selector);
        } else {
            $billToAccountId = str_replace('account_', '', $request->bill_to_selector);
        }

        // SHIP TO
        $shipToCompanyId = null;
        $shipToAccountId = null;

        if (str_starts_with($request->ship_to_selector, 'company_')) {
            $shipToCompanyId = str_replace('company_', '', $request->ship_to_selector);
        } else {
            $shipToAccountId = str_replace('account_', '', $request->ship_to_selector);
        }

        DB::transaction(function () use ($request, $id) {

            /**
             * =====================================================
             * CASE 1 : DRAFT (id = 0)
             * =====================================================
             */
            if ($id == 0) {

                $draftItems  = session('manage_item_selection', []);
                $draftOffers = session('draft_supplier_offers', []);

                if (empty($draftItems) || empty($draftOffers)) {
                    abort(400, 'Draft data missing');
                }

                // 🔹 CREATE SPARE PART WITH PO DETAILS
                $sparePart = SparePart::create([
                    'account_id'           => $request->selected_account_id,
                    'status'               => 2, // Pending for Add Purchase
                    'company_id'           => Session::get('user_company_id'),
                    'created_by'           => auth()->id(),

                    // PO DETAILS
                    'po_date'              => $request->po_date,
                    'bill_to_account_id'   => $request->bill_to_account_id,
                    'ship_to_account_id'   => $request->ship_to_account_id,
                    'po_narration'         => $request->po_narration,
                ]);

                // 🔹 SET PO NUMBER AFTER ID IS GENERATED
                $poNumber = $sparePart->po_number ?? $this->generateSparePartPONumber();

                    $sparePart->update([
                        'po_number' => $poNumber
                    ]);


                $selectedOffers = collect($draftOffers)
                    ->firstWhere('account_id', $request->selected_account_id);

                if (!$selectedOffers) {
                    abort(400, 'Selected supplier not found');
                }

                foreach ($draftItems as $row) {

                    $item = ManageItems::findOrFail($row['item_id']);

                    $offerItem = collect($selectedOffers['items'])
                        ->firstWhere('item_id', $row['item_id']);

                    SparePartItem::create([
                        'spare_part_id' => $sparePart->id,
                        'item_id'       => $row['item_id'],
                        'quantity'      => $offerItem['quantity'] ?? $row['quantity'],
                        'price'         => $offerItem['price'] ?? 0,
                        'unit'          => DB::table('units')
                                                ->where('id', $item->u_name)
                                                ->value('name'),
                        'status'        => 2,
                        'company_id'    => Session::get('user_company_id'),
                    ]);
                }

                session()->forget([
                    'manage_item_selection',
                    'draft_supplier_offers'
                ]);

                return;
            }

            /**
             * =====================================================
             * CASE 2 : EXISTING SPARE PART
             * =====================================================
             */
            $sparePart = SparePart::where('id', $id)
                ->whereIn('status', [0, 1, 2])
               // ->lockForUpdate()
                ->firstOrFail();
            $poNumber = $sparePart->po_number ?: $this->generateSparePartPONumber();
            // 🔹 UPDATE SPARE PART + PO DETAILS
            $sparePart->update([
                'account_id'           => $request->selected_account_id,
                'status'               => 2,

                'po_number'            => $poNumber,
                'po_date'              => $request->po_date,
                'bill_to_company_id' => $request->bill_to_company_id,
                'bill_to_account_id' => $request->bill_to_account_id,
                'ship_to_company_id' => $request->ship_to_company_id,
                'ship_to_account_id' => $request->ship_to_account_id,
                'po_narration'         => $request->po_narration,
                'freight'            => $request->freight,
                'approved_by'            => Session::get('user_id')
            ]);
            if (
                !$request->bill_to_company_id &&
                !$request->bill_to_account_id
            ) {
                throw ValidationException::withMessages([
                    'bill_to' => 'Bill To is required'
                ]);
            }

            if (
                !$request->ship_to_company_id &&
                !$request->ship_to_account_id
            ) {
                throw ValidationException::withMessages([
                    'ship_to' => 'Ship To is required'
                ]);
            }

            $offers = SparePartSupplierOffer::where('spare_part_id', $sparePart->id)
                ->where('account_id', $request->selected_account_id)
                ->get();

            SparePartItem::where('spare_part_id', $sparePart->id)->delete();

            foreach ($offers as $offer) {

                $item = ManageItems::find($offer->item_id);

                $unitName = null;

                if ($item && $item->u_name) {
                    $unitName = Units::where('id', $item->u_name)->value('name');
                }

                SparePartItem::create([
                    'spare_part_id' => $sparePart->id,
                    'item_id'       => $offer->item_id,
                    'quantity'      => $offer->offered_quantity,
                    'price'         => $offer->offered_price,
                    'unit'          => $unitName ?? '',
                    'status'        => 2,
                    'company_id'    => Session::get('user_company_id'),
                ]);
            }
        });

        return redirect()
            ->route('spare-part.index')
            ->with('success', 'Supplier finalized successfully');
    }

    public function saveDraftOffers(Request $request)
    {
        $existingDrafts = session('draft_supplier_offers', []);

        $draftMap = [];

        foreach ($existingDrafts as $draft) {
            $draftMap[$draft['account_id']] = $draft;
        }

        foreach ($request->offers as $offer) {
            $draftMap[$offer['account_id']] = $offer;
        }

        session([
            'draft_supplier_offers' => array_values($draftMap)
        ]);

        return back()->with('success', 'Supplier offers saved temporarily');
    }
    public function fetchSupplierOffer(Request $request)
    {
        return SparePartSupplierOffer::where('spare_part_id', $request->spare_part_id)
            ->where('account_id', $request->account_id)
            ->with('item:id,name')
            ->get()
            ->map(function ($o) {
                return [
                    'item_id'          => $o->item_id,
                    'item_name'        => $o->item->name,
                    'offered_quantity' => $o->offered_quantity,
                    'offered_price'    => $o->offered_price,
                ];
            });
    }
    public function updateSupplierOffer(Request $request)
    {
        DB::transaction(function () use ($request) {

            SparePartSupplierOffer::where('spare_part_id', $request->spare_part_id)
                ->where('account_id', $request->old_account_id)
                ->delete();

            foreach ($request->items as $item) {
                SparePartSupplierOffer::create([
                    'spare_part_id'    => $request->spare_part_id,
                    'account_id'       => $request->account_id,
                    'item_id'          => $item['item_id'],
                    'offered_quantity' => $item['quantity'],
                    'offered_price'    => $item['price'],
                    'company_id'       => Session::get('user_company_id'),
                    'created_by'       => auth()->id(),
                ]);
            }
        });

        return back()->with('success', 'Supplier offer updated');
    }
    public function deleteSupplierOffer(Request $request)
    {
        SparePartSupplierOffer::where('spare_part_id', $request->spare_part_id)
            ->where('account_id', $request->account_id)
            ->delete();

        return back()->with('success', 'Supplier offer deleted');
    }

    public function subItems()
    {
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');

        $spareGroupIds = SaleOrderSetting::where('company_id', $company_id)
            ->where('setting_type', 'PURCHASE GROUP')
            ->where('setting_for', 'PURCHASE ORDER')
            ->where('group_type', 'SPARE PART')
            ->pluck('item_id');

        $groups = ItemGroups::with(['items' => function ($q) use ($company_id) {
            $q->where('company_id', $company_id)
            ->where('delete', '0')
            ->orderBy('name')
            ->where('status', '1');
        }])->whereIn('id', $spareGroupIds)->orderBy('group_name')->get();

        $itemIds = [];
        foreach ($groups as $group) {
            foreach ($group->items as $item) {
                $itemIds[] = $item->id;
            }
        }

        $subItems = SubItem::where('company_id', $company_id)
            ->where('status', 1)
            ->whereIn('parent_item_id', $itemIds)
            ->get()
            ->groupBy('parent_item_id');

        return view('supplier.sparepart_subitems', compact('groups', 'subItems'));
    }
    public function configuration()
    {
        $companyId = Session::get('user_company_id');

        $formCompanyId   = $companyId;
        $formCompanyName = Companies::where('id', $companyId)->value('company_name');

        $config = SparePartConfiguration::where('company_id', $companyId)->first();

        $terms = SparePartTermsCondition::where('company_id', $companyId)
                    ->where('status', 1)
                    ->orderBy('sequence')
                    ->get();

        return view(
            'supplier.configuration',
            compact('config', 'terms', 'formCompanyId', 'formCompanyName')
        );
    }

    public function saveConfiguration(Request $request)
    {
        // ✅ TRUST form_company_id if present (same pattern)
        $companyId = $request->form_company_id ?? Session::get('user_company_id');

        // ================= PO CONFIG =================
        SparePartConfiguration::updateOrCreate(
            ['company_id' => $companyId],
            [
                'po_prefix'         => $request->po_prefix,
                'po_start_from'     => $request->po_start_from,
                'current_po_number' => null,
            ]
        );

        // ================= TERMS =================
        SparePartTermsCondition::where('company_id', $companyId)
            ->update(['status' => 0]);

        foreach ($request->terms ?? [] as $term) {

            if (empty(trim($term['term_text']))) {
                continue;
            }

            if (!empty($term['id'])) {

                SparePartTermsCondition::where('id', $term['id'])->update([
                    'term_text' => $term['term_text'],
                    'sequence'  => $term['sequence'],
                    'status'    => 1,
                ]);

            } else {

                SparePartTermsCondition::create([
                    'company_id' => $companyId, // ✅ IMPORTANT
                    'term_text'  => $term['term_text'],
                    'sequence'   => $term['sequence'],
                    'status'     => 1,
                ]);
            }
        }

        return redirect()
            ->back()
            ->with('success', 'Spare part configuration saved successfully!');
    }

    private function generateSparePartPONumber()
    {
        $companyId = Session::get('user_company_id');

        $config = SparePartConfiguration::where('company_id', $companyId)
            //->lockForUpdate()
            ->firstOrFail();

        if (is_null($config->current_po_number)) {
            $nextNumber = (int) $config->po_start_from;
        } else {
            $nextNumber = (int) $config->current_po_number + 1;
        }

        $config->update([
            'current_po_number' => $nextNumber
        ]);

        return $config->po_prefix . $nextNumber;
    }

    public function viewSparePartSupplier()
    {
        $suppliers = SparePartSupplier::with('account:id,account_name')
            ->where('company_id', Session::get('user_company_id'))
            ->orderBy('id', 'desc')
            ->get();

        return view('supplier.view_sparepart_supplier', compact('suppliers'));
    }
    public function addSparePartSupplier()
    {
        $companyId = Session::get('user_company_id');

        // Already added Spare Part suppliers
        $alreadyAdded = SparePartSupplier::where('company_id', $companyId)
            ->pluck('account_id')
            ->toArray();

        /**
         * SAME GROUP LOGIC AS BOILER FUEL
         */
        $group_ids = CommonHelper::getAllChildGroupIds(3, $companyId);
        array_push($group_ids, 3);

        $group_ids = array_merge(
            $group_ids,
            CommonHelper::getAllChildGroupIds(11, $companyId)
        );
        array_push($group_ids, 11);

        $group_ids = array_unique($group_ids);

        /**
         * SAME ACCOUNT QUERY AS BOILER FUEL
         */
        $accounts = Accounts::where('delete', '=', '0')
            ->where('status', '=', '1')
            ->whereNotIn('id', $alreadyAdded)
            ->whereIn('company_id', [$companyId, 0])
            ->whereIn('under_group', $group_ids)
            ->select('accounts.id', 'accounts.account_name')
            ->orderBy('account_name')
            ->get();

        return view('supplier.add_sparepart_supplier', compact('accounts'));
    }

    public function storeSparePartSupplier(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
        ]);

        SparePartSupplier::create([
            'company_id' => Session::get('user_company_id'),
            'account_id' => $request->account_id,
            'status'     => 1,
            'created_by' => Session::get('user_id'),
            'created_at' => Carbon::now(),
        ]);

        return redirect()
            ->route('spare-part.suppliers')
            ->with('success', 'Spare Part Supplier added successfully');
    }
    public function deleteSparePartSupplier($id)
    {
        $supplier = SparePartSupplier::where('id', $id)
            ->where('company_id', Session::get('user_company_id'))
            ->firstOrFail();

        $supplier->delete();

        return redirect()
            ->route('spare-part.suppliers')
            ->with('success', 'Spare Part Supplier deleted successfully');
    }

    public function getPendingSparePartsForModal()
    {
        $companyId = Session::get('user_company_id');

        $spares = SparePart::with([
                'account:id,account_name',
                'items.item:id,name'
            ])
            ->where('company_id', $companyId)
            ->where('status', 2) // Pending for Add Purchase
            ->orderBy('created_at', 'desc')
            ->get();

        $rows = [];

        foreach ($spares as $spare) {
            foreach ($spare->items as $item) {
                $rows[] = [
                    'spare_part_id' => $spare->id,
                    'date'          => $spare->created_at->format('d-m-Y'),
                    'po_number'     => $spare->po_number ?? '-',
                    'account_name'  => $spare->account->account_name ?? '',
                    'item_name'     => $item->item->name ?? '',
                    'unit'          => $item->unit,
                    'quantity'      => $item->quantity,
                ];
            }
        }

        return response()->json($rows);
    }
    public function createStartNewFromVehicle($sparePartId, $vehicleEntryId)
    {
    
        return redirect()->route(
            'spare-part.start.new',
            $sparePartId
        );
    }
    public function maintainQuantityView()
    {
        $company_id = Session::get('user_company_id');

        $spareGroupIds = SaleOrderSetting::where('company_id', $company_id)
            ->where('setting_type', 'PURCHASE GROUP')
            ->where('setting_for', 'PURCHASE ORDER')
            ->where('group_type', 'SPARE PART')
            ->pluck('item_id');

        $groups = ItemGroups::with(['items' => function ($q) use ($company_id) {
                                    $q->where('company_id', $company_id)
                                    ->where('delete', '0')
                                    ->orderBy('name')
                                    ->where('status', '1');
                                }])->whereIn('id', $spareGroupIds)
                                ->orderBy('group_name')
                                ->get();

        return view('supplier.maintain_spare_part_qty', compact('groups'));
    }

}
