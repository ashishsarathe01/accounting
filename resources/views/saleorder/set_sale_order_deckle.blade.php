@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<style>
@media print {

    body * {
        visibility: hidden !important;
    }

    .print-area,
    .print-area * {
        visibility: visible !important;
    }

    .print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    button,
    .btn,
    .noprint {
        display: none !important;
    }
}
</style>
<div class="list-of-view-company ">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <div class="col-md-12 ml-sm-auto  col-lg-9 px-md-4 bg-mint">
                @if (session('error'))
                <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
                @endif
                @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
                @endif
                @php
                $status = request()->get('status');
                @endphp   
                {{-- =================== PENDING SALES ORDER =================== --}}            
                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4 noprint">
                    <h5 class="transaction-table-title m-0 py-2 noprint">Set Sales Order Deckle</h5>
                </div>
                <div class="transaction-table bg-white table-view shadow-sm mb-5 noprint">
                    <table class="table table-bordered">
                        <thead>
                            <th>Item</th>
                            <th>Size</th>
                            <th>Order Quantity</th>
                            <th>Book Quantity</th>
                            <th>Set Quantity</th>
                        </thead>
                        <tbody>                            
                            @php 
                                $size_arr = [];
                                $order_quantity_total = 0;
                                $estimate_quantity_total = 0;
                                $set_quantity_total = 0;
                            @endphp
                            @foreach($sale_order as $key => $value)
                                @php 
                                    $order_quantity = 0;$estimate_quantity = 0;
                                    foreach ($value as $k1 => $v1) {
                                        $order_quantity = $order_quantity + $v1['order_quantity'];
                                        $estimate_quantity = $estimate_quantity + $v1['estimate_quantity'];
                                    }
                                    $approx_qty = $order_quantity;
                                    if($value[0]['unit']=="KG"){
                                        $reelArr = [];$sizeArr = [];
                                        array_push($reelArr,$order_quantity);
                                        $data = explode("X",$value[0]['size']);
                                        array_push($sizeArr,$data[0]);
                                        $approx_qty = 0;                                        
                                        for($i=0;$i<count($reelArr);$i++){
                                            if($reelArr[$i]!=""){
                                               $approx_qty = $approx_qty + $reelArr[$i]/($sizeArr[$i]*15);
                                            }
                                        }
                                        $approx_qty = round($approx_qty);
                                    }
                                    $combination_size = "";
                                @endphp
                                <tr>
                                    <td>{{$value[0]['item_name']}}</td>
                                    <td>
                                        @if($value[0]['sub_unit']!="INCH")
                                            @php 
                                                $detail = explode("X",$value[0]['size']);
                                                if($value[0]['sub_unit']=="CM"){
                                                    $length_inch = round($detail['0']/2.54,2);
                                                    echo $detail['0']." CM (".$length_inch." INCH)X".$detail['1'];
                                                    $combination_size = $length_inch."X".$detail['1'];
                                                }
                                                if($value[0]['sub_unit']=="MM"){
                                                    $length_inch = round($detail['0']/25.4,2);
                                                    echo $detail['0']." MM (".$length_inch." INCH)X".$detail['1'];
                                                    $combination_size = $length_inch."X".$detail['1'];
                                                }
                                            @endphp
                                        @else
                                            {{$value[0]['size']}}
                                            @php $combination_size = $value[0]['size'];@endphp
                                        @endif
                                        
                                    </td>
                                    <td>{{$approx_qty}}</td>
                                    <td>{{$estimate_quantity}}</td>
                                    <td>{{$approx_qty - $estimate_quantity}}</td>
                                </tr>
                                @php 
                                    // Collect all sale_order_ids for this size
                                    $saleOrderIds = [];
                                    foreach ($value as $v1) {
                                        $soId = (string)$v1['sale_order_id'];
                                        if (!in_array($soId, $saleOrderIds)) {
                                            $saleOrderIds[] = $soId;
                                        }
                                    }
                                    array_push($size_arr,array(
                                        "item"=>$value[0]['item_name'],
                                        "size"=>$combination_size,
                                        "quantity"=>$approx_qty - $estimate_quantity,
                                        'actual_size'=>$value[0]['size'],
                                        'unit'=>$value[0]['sub_unit'],
                                        'sale_order_ids'=>$saleOrderIds,
                                    )); 
                                    $order_quantity_total = $order_quantity_total + $approx_qty;
                                    $estimate_quantity_total = $estimate_quantity_total + $estimate_quantity;
                                    $set_quantity_total = $set_quantity_total + ($approx_qty - $estimate_quantity);
                                @endphp
                            @endforeach
                            <tr>
                                <td><strong>Total</strong></td>
                                <td></td>
                                <td><strong>{{$order_quantity_total}}</strong></td>
                                <td><strong>{{$estimate_quantity_total}}</strong></td>
                                <td><strong>{{$set_quantity_total}}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="print-area">
                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0 py-2">Deckle Size Combination</h5>
                    <button class="btn btn-info noprint" onclick="printpage();">Print</button>
                    <button class="btn btn-success noprint" id="save_deckle">
                        Save
                    </button>
                    <button class="btn btn-primary set_range noprint" 
                            data-form_size="@if($deckle_range){{$deckle_range->from_size}}@endif" 
                            data-to_size="@if($deckle_range){{$deckle_range->to_size}}@endif">
                        @if($deckle_range) ({{$deckle_range->from_size}}-{{$deckle_range->to_size}})@endif 
                        Set Deckle Size Range
                    </button>
                    <button class="btn btn-warning set_filler_range noprint"
                        data-from="{{ $deckle_range->filler_from_size ?? '' }}"
                        data-to="{{ $deckle_range->filler_to_size ?? '' }}">
                        @if($deckle_range && $deckle_range->filler_from_size)
                            ({{ $deckle_range->filler_from_size }}-{{ $deckle_range->filler_to_size }})
                        @else
                            Set Filler Size Range
                        @endif
                    </button>
                </div>
                <div class="transaction-table bg-white table-view shadow-sm mb-5">
                    @php
                        // echo "<pre>";
                        // print_r($size_arr);
                        $targetMin = 114; $targetMax = 114;
                        if($deckle_range){
                            $targetMin = $deckle_range->from_size; 
                            $targetMax = $deckle_range->to_size;
                        }
                        /**
                         * Parse size string (e.g. "30X120") into [width, height]
                         */
                        function parseSize(string $s) {
                            [$w, $h] = explode("X", $s);
                            return [$w, $h];
                        }
                        /**
                         * Generate all exact combinations that sum to target using bounded quantities.
                         * Faster, iterative DP-style expansion.
                         */
                        function generateRangeCombos(array $sizes, $min, $max, $actual_size, $unit) {
                            $states = [0 => []]; // width_sum => list of combos

                            foreach ($sizes as $s) {

                                [$width] = explode("X", $s['size']);
                                $qty     = $s['quantity'];
                                $width   =  $width;

                                $newStates = $states;

                                foreach ($states as $sum => $comboList) {
                                    for ($i = 1; $i <= $qty; $i++) {
                                        
                                        $newSum = $sum + $width * $i;
                                        
                                        if ($newSum > $max) break;

                                        foreach ($comboList ?: [[]] as $existing) {
                                            $merged = $existing;
                                            $merged[$s['size']] = ($merged[$s['size']] ?? 0) + $i;
                                            $newStates[(string)$newSum][] = $merged;
                                        }                                        
                                        //print_r($merged);
                                        $newStates[(string)$newSum][] = $merged;
                                    }
                                }
                                $states = $newStates;
                            }
                            $valid = [];
                           
                            foreach ($states as $sum => $combos) {
                                if ($sum >= $min && $sum <= $max) {
                                    foreach ($combos as $c) {
                                        $valid[] = $c;
                                    }
                                }
                            }
                            return $valid;
                        }
                        /**
                         * Scarcity score: lower is better
                         */
                        function scarcityScore(array $combo, array $remaining) {
                            $score = 0;
                            foreach ($combo as $size => $qty) {
                                if(isset($item) && isset($remaining[$item][$size]['qty'])){
                                    $rem = $remaining[$item][$size]['qty'];
                                }else{
                                    $rem = 1;
                                }
                                $score += $qty / max(1, $rem);
                            }
                            return $score;
                        }
                        /* -------------------------------
                        GROUP SIZES
                        ---------------------------------*/
                        $grouped = [];
                        $remaining = [];

                        foreach ($size_arr as $row) {
                            [$w, $h] = parseSize($row['size']);

                            $grouped[$row['item']][$h][] = [
                                'size'     => $row['size'],
                                'quantity' => $row['quantity'],
                                'actual_size' => $row['actual_size'],
                                'unit' => $row['unit'],
                                'sale_order_ids' => $row['sale_order_ids'] ?? [],
                            ];

                            $remaining[$row['item']][$row['size']] = [
                                'qty'         => $row['quantity'],
                                'actual_size' => $row['actual_size'],
                                'unit'        => $row['unit'],
                                'sale_order_ids' => $row['sale_order_ids'] ?? [],
                            ];
                        }
                        /**
                        * Build one full deckle option (non-destructive). Modes produce different selection order.
                        */
                        function buildDeckleOption(array $grouped, array $remaining, $targetMin, $targetMax, $mode = 1) {
                            $localRemaining = unserialize(serialize($remaining));
                            $optionCombos = [];

                            foreach ($grouped as $item => $heightGroups) {
                                foreach ($heightGroups as $height => $sizes) {

                                    while (true) {
                                        // build available
                                        $available = [];
                                        foreach ($sizes as $s) {
                                            if (!empty($localRemaining[$item][$s['size']])) {
                                                $available[] = [
                                                    'size'        => $s['size'],
                                                    'quantity'    => intval($localRemaining[$item][$s['size']]['qty'] ?? 0),
                                                    'actual_size' => $s['actual_size'],
                                                    'unit'        => $s['unit'],
                                                ];
                                            }
                                        }
                                        if (empty($available)) break;

                                        // find combos (uses your existing generator)
                                        $found = generateRangeCombos($available, $targetMin, $targetMax, $sizes[0]['actual_size'], $sizes[0]['unit']);
                                        if (empty($found)) break;

                                        // different ordering strategies
                                        if ($mode === 1) {
                                            usort($found, function($a, $b) use ($localRemaining, $item) {
                                                return scarcityScore($a, $localRemaining[$item]) <=> scarcityScore($b, $localRemaining[$item]);
                                            });
                                        } elseif ($mode === 2) {
                                            usort($found, function($a, $b) use ($localRemaining, $item) {
                                                return scarcityScore($b, $localRemaining[$item]) <=> scarcityScore($a, $localRemaining[$item]);
                                            });
                                        } elseif ($mode === 4) {
                                            usort($found, function($a, $b) {
                                                $sa = 0; foreach($a as $k=>$v) $sa += intval(explode('X',$k)[0])*$v;
                                                $sb = 0; foreach($b as $k=>$v) $sb += intval(explode('X',$k)[0])*$v;
                                                return $sb <=> $sa;
                                            });
                                        } elseif ($mode === 5) {
                                            usort($found, function($a, $b) {
                                                $sa = 0; foreach($a as $k=>$v) $sa += intval(explode('X',$k)[0])*$v;
                                                $sb = 0; foreach($b as $k=>$v) $sb += intval(explode('X',$k)[0])*$v;
                                                return $sa <=> $sb;
                                            });
                                        } else {
                                            shuffle($found);
                                        }

                                        // pick first candidate respecting <=5 widths
                                        $selectedCombo = null; $selectedParts = [];
                                        foreach ($found as $candidate) {
                                            $tmpParts = [];
                                            foreach ($candidate as $size => $qty) {
                                                [$w,] = explode("X", $size);
                                                for ($i=0;$i<$qty;$i++) $tmpParts[] = $w;
                                            }
                                            if (count($tmpParts) <= 5) { $selectedCombo = $candidate; $selectedParts = $tmpParts; break; }
                                        }
                                        if ($selectedCombo === null) break;

                                        // apply on localRemaining
                                        foreach ($selectedCombo as $size => $qty) {
                                            if (isset($localRemaining[$item][$size])) {
                                                $localRemaining[$item][$size]['qty'] -= $qty;
                                                if ($localRemaining[$item][$size]['qty'] <= 0) unset($localRemaining[$item][$size]);
                                            }
                                        }

                                        // build details
                                        $comboDetails = [];
                                        foreach ($selectedCombo as $sizeKey => $qty) {
                                            foreach ($available as $av) {
                                                if ($av['size'] == $sizeKey) {
                                                    $comboDetails[] = [
                                                        'size' => $sizeKey,
                                                        'qty' => $qty,
                                                        'actual_size' => $av['actual_size'],
                                                        'unit' => $av['unit'],
                                                    ];
                                                }
                                            }
                                        }

                                        $optionCombos[$item][$height][] = ['selected'=>$selectedParts,'details'=>$comboDetails];
                                    } // end while
                                }
                            }

                            return $optionCombos;
                        }

                        /**
                        * Generate up to $maxOptions distinct deckle options.
                        */
                        function generateDeckleOptions(array $grouped, array $remaining, $targetMin, $targetMax, $maxOptions = 5) {
                            $modes = [1,2,3,4,5]; // tried strategies
                            $options = []; $seen = [];

                            foreach ($modes as $mode) {
                                if (count($options) >= $maxOptions) break;
                                $opt = buildDeckleOption($grouped, $remaining, $targetMin, $targetMax, $mode);
                                if (empty($opt)) continue;
                                // canonical key to dedupe
                                ksort($opt);
                                $key = json_encode($opt);
                                if (!isset($seen[$key])) {
                                    $seen[$key] = true;
                                    $options[] = $opt;
                                }
                            }
                            return $options;
                        }

                        // generate options (B: stop early when no more distinct combos), max 5
                        $deckleOptions = generateDeckleOptions($grouped, $remaining, $targetMin, $targetMax, 5);

                        /* -------------------------------
                        PRIORITY-BASED COMBINATION GENERATION
                        ---------------------------------*/
                        // Get priority sale_order_id
                        $prioritySaleOrderId = null;
                        $saleOrderIds = request()->sale_order ?? [];
                        if (!empty($saleOrderIds)) {
                            $saleOrderIds = is_array($saleOrderIds) ? $saleOrderIds : json_decode($saleOrderIds, true);
                            $prioritySaleOrderId = !empty($saleOrderIds) ? (string)$saleOrderIds[0] : null;
                        }

                        // Build normalized set of pending deckle combinations to avoid duplicates
                        $pendingCombinationsNormalized = [];
                        if (!empty($pending_deckle_matches)) {
                            foreach ($pending_deckle_matches as $row) {
                                // Extract combination like "13 + 12 = 25"
                                $left = explode('=', $row['combination'])[0];
                                $parts = array_map('trim', explode('+', $left));
                                // Normalize: sort numbers and join with "+"
                                $parts = array_map('intval', $parts);
                                sort($parts);
                                $normalized = implode('+', $parts);
                                $pendingCombinationsNormalized[$row['item_name']][$row['gsm']][$normalized] = true;
                            }
                        }
                        
                        // Helper function to check if combination exists in pending
                        $isCombinationInPending = function($parts, $item, $gsm) use ($pendingCombinationsNormalized) {
                            if (empty($pendingCombinationsNormalized[$item][$gsm])) {
                                return false;
                            }
                            $normalized = $parts;
                            sort($normalized);
                            $normalizedStr = implode('+', $normalized);
                            return isset($pendingCombinationsNormalized[$item][$gsm][$normalizedStr]);
                        };

                        $combos = [];
                        
                        foreach ($grouped as $item => $heightGroups) {
                            foreach ($heightGroups as $height => $sizes) {
                                
                                // PASS 1: Exact combinations using ONLY priority sale order sizes
                                while (true) {
                                    if ($prioritySaleOrderId === null) break;
                                    
                                    $available = [];
                                    foreach ($sizes as $s) {
                                        if (!empty($remaining[$item][$s['size']])) {
                                            $soIds = $remaining[$item][$s['size']]['sale_order_ids'] ?? [];
                                            // Only include if ALL sale_order_ids are priority
                                            if (count($soIds) > 0 && count($soIds) === count(array_intersect($soIds, [$prioritySaleOrderId]))) {
                                                $available[] = [
                                                    'size'     => $s['size'],
                                                    'quantity' => $remaining[$item][$s['size']]['qty'],
                                                    'actual_size' => $s['actual_size'],
                                                    'unit'     => $s['unit'],
                                                    'sale_order_ids' => $soIds,
                                                ];
                                            }
                                        }
                                    }
                                    if (empty($available)) break;

                                    $found = generateRangeCombos($available, $targetMin, $targetMax, $sizes[0]['actual_size'], $sizes[0]['unit']);
                                    if (empty($found)) break;
                                    
                                    usort($found, function($a, $b) use ($remaining, $item) {
                                        return scarcityScore($a, $remaining[$item]) <=> scarcityScore($b, $remaining[$item]);
                                    });

                                    $best = null;
                                    $parts = [];
                                    foreach ($found as $combo) {
                                        $tmpParts = [];
                                        foreach ($combo as $size => $qty) {
                                            [$w] = parseSize($size);
                                            for ($i = 0; $i < $qty; $i++) {
                                                $tmpParts[] = $w;
                                            }
                                        }
                                        if (count($tmpParts) <= 5) {
                                            $best = $combo;
                                            $parts = $tmpParts;
                                            break;
                                        }
                                    }
                                    if ($best === null) break;

                                    foreach ($best as $size => $qty) {
                                        $remaining[$item][$size]['qty'] -= $qty;
                                        if ($remaining[$item][$size]['qty'] <= 0) {
                                            unset($remaining[$item][$size]);
                                        }
                                    }

                                    $comboDetails = [];
                                    foreach ($best as $sizeKey => $qty) {
                                        foreach ($available as $av) {
                                            if ($av['size'] == $sizeKey) {
                                                $comboDetails[] = [
                                                    'size' => $sizeKey,
                                                    'qty' => $qty,
                                                    'actual_size' => $av['actual_size'],
                                                    'unit' => $av['unit'],
                                                ];
                                            }
                                        }
                                    }

                                    $combos[$item][$height][] = [
                                        'selected' => $parts,
                                        'details'  => $comboDetails
                                    ];
                                }

                                // PASS 2: Exact combinations mixing priority + other sale orders
                                while (true) {
                                    if ($prioritySaleOrderId === null) break;
                                    
                                    $available = [];
                                    foreach ($sizes as $s) {
                                        if (!empty($remaining[$item][$s['size']])) {
                                            $soIds = $remaining[$item][$s['size']]['sale_order_ids'] ?? [];
                                            // Include if has priority (allows mixing with non-priority sizes)
                                            if (in_array($prioritySaleOrderId, $soIds)) {
                                                $available[] = [
                                                    'size'     => $s['size'],
                                                    'quantity' => $remaining[$item][$s['size']]['qty'],
                                                    'actual_size' => $s['actual_size'],
                                                    'unit'     => $s['unit'],
                                                    'sale_order_ids' => $soIds,
                                                ];
                                            } elseif (count($soIds) > 0) {
                                                // Also include non-priority sizes to allow mixing
                                                $available[] = [
                                                    'size'     => $s['size'],
                                                    'quantity' => $remaining[$item][$s['size']]['qty'],
                                                    'actual_size' => $s['actual_size'],
                                                    'unit'     => $s['unit'],
                                                    'sale_order_ids' => $soIds,
                                                ];
                                            }
                                        }
                                    }
                                    if (empty($available)) break;

                                    $found = generateRangeCombos($available, $targetMin, $targetMax, $sizes[0]['actual_size'], $sizes[0]['unit']);
                                    if (empty($found)) break;
                                    
                                    usort($found, function($a, $b) use ($remaining, $item) {
                                        return scarcityScore($a, $remaining[$item]) <=> scarcityScore($b, $remaining[$item]);
                                    });

                                    $best = null;
                                    $parts = [];
                                    foreach ($found as $combo) {
                                        $tmpParts = [];
                                        foreach ($combo as $size => $qty) {
                                            [$w] = parseSize($size);
                                            for ($i = 0; $i < $qty; $i++) {
                                                $tmpParts[] = $w;
                                            }
                                        }
                                        if (count($tmpParts) <= 5) {
                                            $best = $combo;
                                            $parts = $tmpParts;
                                            break;
                                        }
                                    }
                                    if ($best === null) break;

                                    foreach ($best as $size => $qty) {
                                        $remaining[$item][$size]['qty'] -= $qty;
                                        if ($remaining[$item][$size]['qty'] <= 0) {
                                            unset($remaining[$item][$size]);
                                        }
                                    }

                                    $comboDetails = [];
                                    foreach ($best as $sizeKey => $qty) {
                                        foreach ($available as $av) {
                                            if ($av['size'] == $sizeKey) {
                                                $comboDetails[] = [
                                                    'size' => $sizeKey,
                                                    'qty' => $qty,
                                                    'actual_size' => $av['actual_size'],
                                                    'unit' => $av['unit'],
                                                ];
                                            }
                                        }
                                    }

                                    $combos[$item][$height][] = [
                                        'selected' => $parts,
                                        'details'  => $comboDetails
                                    ];
                                }

                                // PASS 4: Exact combinations using ONLY non-priority sale orders
                                while (true) {
                                    $available = [];
                                    foreach ($sizes as $s) {
                                        if (!empty($remaining[$item][$s['size']])) {
                                            $soIds = $remaining[$item][$s['size']]['sale_order_ids'] ?? [];
                                            // Only include if NO priority sale_order_id
                                            if ($prioritySaleOrderId === null || !in_array($prioritySaleOrderId, $soIds)) {
                                                $available[] = [
                                                    'size'     => $s['size'],
                                                    'quantity' => $remaining[$item][$s['size']]['qty'],
                                                    'actual_size' => $s['actual_size'],
                                                    'unit'     => $s['unit'],
                                                    'sale_order_ids' => $soIds,
                                                ];
                                            }
                                        }
                                    }
                                    if (empty($available)) break;

                                    $found = generateRangeCombos($available, $targetMin, $targetMax, $sizes[0]['actual_size'], $sizes[0]['unit']);
                                    if (empty($found)) break;
                                    
                                    usort($found, function($a, $b) use ($remaining, $item) {
                                        return scarcityScore($a, $remaining[$item]) <=> scarcityScore($b, $remaining[$item]);
                                    });

                                    $best = null;
                                    $parts = [];
                                    foreach ($found as $combo) {
                                        $tmpParts = [];
                                        foreach ($combo as $size => $qty) {
                                            [$w] = parseSize($size);
                                            for ($i = 0; $i < $qty; $i++) {
                                                $tmpParts[] = $w;
                                            }
                                        }
                                        if (count($tmpParts) <= 5) {
                                            $best = $combo;
                                            $parts = $tmpParts;
                                            break;
                                        }
                                    }
                                    if ($best === null) break;

                                    foreach ($best as $size => $qty) {
                                        $remaining[$item][$size]['qty'] -= $qty;
                                        if ($remaining[$item][$size]['qty'] <= 0) {
                                            unset($remaining[$item][$size]);
                                        }
                                    }

                                    $comboDetails = [];
                                    foreach ($best as $sizeKey => $qty) {
                                        foreach ($available as $av) {
                                            if ($av['size'] == $sizeKey) {
                                                $comboDetails[] = [
                                                    'size' => $sizeKey,
                                                    'qty' => $qty,
                                                    'actual_size' => $av['actual_size'],
                                                    'unit' => $av['unit'],
                                                ];
                                            }
                                        }
                                    }

                                    $combos[$item][$height][] = [
                                        'selected' => $parts,
                                        'details'  => $comboDetails
                                    ];
                                }

                                
                            }
                        }
                        /* ---------------------------------
                        SMART MANUAL COMBINATIONS (MERGED)
                        -----------------------------------*/
                        // Build manual combinations
                        $manualPool = [];
                        foreach ($remaining as $item => $sizeGroups) {
                            foreach ($sizeGroups as $fullSize => $info) {
                                [$w, $gsm] = explode('X', $fullSize);
                                
                                if (!isset($manualPool[$item][$gsm])) {
                                    $manualPool[$item][$gsm] = [];
                                }
                                
                                $qty = intval($info['qty'] ?? 0);
                                for ($i = 0; $i < $qty; $i++) {
                                    $manualPool[$item][$gsm][] = [
                                        'width' => (float)$w,
                                        'size' => $fullSize,
                                        'actual_size' => $info['actual_size'],
                                        'unit' => $info['unit']
                                    ];
                                }
                            }
                        }
                        // echo "<pre>";
                        //         print_r($manualPool);
                        //         echo "</pre>";

                        //New Code
                        foreach ($manualPool as $item => $gsmGroups) {
                            foreach ($gsmGroups as $gsm => $pool) {

                                // Sort pool by width ASC
                                usort($pool, fn($a, $b) => $a['width'] <=> $b['width']);

                                $n = count($pool);
                                $usedGlobal = []; // stock consumption tracker

                                while (true) {
                                    $bestCombo = null;
                                    $bestWaste = PHP_INT_MAX;

                                    // helper function
                                    $tryIndexes = function ($indexes) use (
                                        $pool, $gsm, $targetMin, $targetMax,
                                        $deckle_range, &$usedGlobal, &$bestCombo, &$bestWaste
                                    ) {
                                        // block reused stock
                                        foreach ($indexes as $idx) {
                                            if (isset($usedGlobal[$idx])) {
                                                return;
                                            }
                                        }

                                        $sum = 0;
                                        foreach ($indexes as $idx) {
                                            $sum += $pool[$idx]['width'];
                                        }

                                        if ($sum > $targetMax || count($indexes) > 4) {
                                            return;
                                        }

                                        // best-fit filler
                                        for ($f = $deckle_range->filler_from_size; $f <= $deckle_range->filler_to_size; $f++) {
                                            $total = $sum + $f;

                                            if ($total < $targetMin || $total > $targetMax) {
                                                continue;
                                            }

                                            $waste = $targetMax - $total;

                                            if ($waste < $bestWaste) {
                                                $bestWaste = $waste;
                                                $bestCombo = [
                                                    'indexes' => $indexes,
                                                    'filler'  => $f,
                                                    'total'   => $total
                                                ];
                                            }
                                        }
                                    };

                                    // 1 real width
                                    for ($i = 0; $i < $n; $i++) {
                                        $tryIndexes([$i]);
                                    }

                                    // 2 real widths (THIS enables 30 + 50 + filler)
                                    for ($i = 0; $i < $n; $i++) {
                                        for ($j = $i + 1; $j < $n; $j++) {
                                            $tryIndexes([$i, $j]);
                                        }
                                    }

                                    // 3 real widths
                                    for ($i = 0; $i < $n; $i++) {
                                        for ($j = $i + 1; $j < $n; $j++) {
                                            for ($k = $j + 1; $k < $n; $k++) {
                                                $tryIndexes([$i, $j, $k]);
                                            }
                                        }
                                    }

                                    // 4 real widths
                                    for ($i = 0; $i < $n; $i++) {
                                        for ($j = $i + 1; $j < $n; $j++) {
                                            for ($k = $j + 1; $k < $n; $k++) {
                                                for ($l = $k + 1; $l < $n; $l++) {
                                                    $tryIndexes([$i, $j, $k, $l]);
                                                }
                                            }
                                        }
                                    }

                                    // no more possible combinations
                                    if (!$bestCombo) {
                                        break;
                                    }

                                    // build final combo
                                    $selected = [];
                                    $details  = [];

                                    foreach ($bestCombo['indexes'] as $idx) {
                                        $usedGlobal[$idx] = true;

                                        $selected[] = $pool[$idx]['width'];
                                        $details[] = [
                                            'size'        => $pool[$idx]['size'],
                                            'qty'         => 1,
                                            'actual_size' => $pool[$idx]['actual_size'],
                                            'unit'        => $pool[$idx]['unit']
                                        ];
                                    }

                                    // add filler
                                    $selected[] = $bestCombo['filler'];
                                    $details[] = [
                                        'size'        => $bestCombo['filler'] . 'X' . $gsm,
                                        'qty'         => 1,
                                        'actual_size' => $bestCombo['filler'] . 'X' . $gsm,
                                        'unit'        => 'INCH',
                                        'filler'      => true
                                    ];

                                    //sort($selected);

                                    $combos[$item][$gsm][] = [
                                        'selected' => $selected,
                                        'details'  => $details,
                                        'manual'   => true,
                                        'total'    => $bestCombo['total'],
                                        'filler'    => $bestCombo['filler']
                                    ];
                                }
                            }
                        }





                        //old code
                        // foreach ($manualPool as $item => $gsmGroups) {
                        //     foreach ($gsmGroups as $gsm => $pool) {
                                
                        //         // Sort descending for better packing
                        //         usort($pool, function($a, $b) {
                        //             return $b['width'] <=> $a['width'];
                        //         });
                                
                                
                                
                                
                        //         while (!empty($pool)) {
                        //             $parts = [];
                        //             $details = [];
                        //             $sum = 0;
                                    
                        //             // Use real sizes first (max 4 to leave room for filler)
                        //             while (!empty($pool) && count($parts) < 4) {
                        //                 $candidate = $pool[0];
                                        
                        //                 if ($sum + $candidate['width'] > $targetMax) {
                        //                     break;
                        //                 }
                                        
                        //                 $parts[] = $candidate['width'];
                        //                 $details[] = [
                        //                     'size' => $candidate['size'],
                        //                     'qty' => 1,
                        //                     'actual_size' => $candidate['actual_size'],
                        //                     'unit' => $candidate['unit']
                        //                 ];
                        //                 $sum += $candidate['width'];
                        //                 array_shift($pool);
                        //             }
                                    
                        //             // Add filler once if needed
                        //             while (
                        //                 $sum < $targetMin &&
                        //                 count($parts) < 5
                        //             ) {
                        //                 $needed = $targetMin - $sum; 

                        //                 $filler = min(
                        //                     $deckle_range->filler_to_size,
                        //                     max($deckle_range->filler_from_size, $needed)
                        //                 );
                                       
                        //                 if ($filler < $deckle_range->filler_from_size) {
                                            
                        //                     break;
                        //                 }
                                       
                        //                 if ($sum + $filler > $targetMax) {
                                            
                        //                     break;
                        //                 }
                                        
                        //                 $parts[] = $filler;
                        //                 $sum += $filler;
                        //             }
                                    
                        //             // Validate range
                        //              //echo $sum."+".$targetMin."+".$targetMax;"<br>";
                        //             if ($sum < $targetMin || $sum > $targetMax) {
                                        
                        //                 continue;
                        //             }
                                    
                        //             // Skip if duplicate
                        //             $normalizedParts = $parts;
                        //             sort($normalizedParts);
                        //             $normalizedStr = implode('+', $normalizedParts);
                                    
                        //             if (!empty($pendingCombinationsNormalized[$item][$gsm]) && 
                        //                 isset($pendingCombinationsNormalized[$item][$gsm][$normalizedStr])) {
                        //                 continue;
                        //             }
                                    
                        //             $combos[$item][$gsm][] = [
                        //                 'selected' => $parts,
                        //                 'details' => $details,
                        //                 'manual' => true
                        //             ];
                        //         }
                        //     }
                        // }
                      $alreadyInjected = [];

                        if (!empty($saved_manual_sets)) {
                            foreach ($saved_manual_sets as $row) {

                                if ($row->type !== 'USER') {
                                    continue;
                                }

                                $combo = json_decode($row->combination, true);
                                $parts = $combo['filler'] ?? [];

                                if (empty($parts)) continue;

                                foreach ($combos as $itemName => $g) {

                                    // ❗ DO NOT SORT — preserve USER order
                                    $combos[$itemName][$row->gsm][] = [
                                    'selected' => array_map('floatval', $parts),
                                    'details'  => array_map(function ($p) use ($row) {
                                        return [
                                            'size' => $p . 'X' . $row->gsm,
                                            'qty'  => 1,
                                            'actual_size' => $p . 'X' . $row->gsm,
                                            'unit' => 'INCH'
                                        ];
                                    }, $parts),
                                    'manual' => true,
                                    'user'   => true
                                ];


                                    break;
                                }

                            }
                        }

                        /* ---------------------------------
                        PRIORITY-BASED SORTING OF COMBINATIONS
                        -----------------------------------*/
                        // Build mapping: size -> sale_order_ids from original data
                        $sizeToSaleOrderMap = [];
                        foreach ($sale_order as $key => $value) {
                            foreach ($value as $row) {
                                // Convert size to match combination format
                                $originalSize = $row['size'];
                                $convertedSize = $originalSize;
                                if ($row['sub_unit'] != "INCH") {
                                    $detail = explode("X", $originalSize);
                                    if ($row['sub_unit'] == "CM") {
                                        $length_inch = round($detail[0] / 2.54, 2);
                                        $convertedSize = $length_inch . "X" . $detail[1];
                                    } elseif ($row['sub_unit'] == "MM") {
                                        $length_inch = round($detail[0] / 25.4, 2);
                                        $convertedSize = $length_inch . "X" . $detail[1];
                                    }
                                }
                                $saleOrderId = (string)$row['sale_order_id'];
                                if (!isset($sizeToSaleOrderMap[$convertedSize])) {
                                    $sizeToSaleOrderMap[$convertedSize] = [];
                                }
                                if (!in_array($saleOrderId, $sizeToSaleOrderMap[$convertedSize])) {
                                    $sizeToSaleOrderMap[$convertedSize][] = $saleOrderId;
                                }
                            }
                        }

                        // Function to get priority score
                        function getCombinationPriority($combo, $sizeToSaleOrderMap, $prioritySaleOrderId) {
                            if ($prioritySaleOrderId === null) return 999;
                            
                            $isManual = isset($combo['manual']) && $combo['manual'];
                            $usedSaleOrderIds = [];
                            
                            // Get sale_order_ids from combination details
                            foreach ($combo['details'] as $detail) {
                                $size = $detail['size'];
                                if (isset($sizeToSaleOrderMap[$size])) {
                                    foreach ($sizeToSaleOrderMap[$size] as $soId) {
                                        if (!in_array($soId, $usedSaleOrderIds)) {
                                            $usedSaleOrderIds[] = $soId;
                                        }
                                    }
                                }
                            }
                            
                            $hasPriority = in_array($prioritySaleOrderId, $usedSaleOrderIds);
                            $hasNonPriority = false;
                            foreach ($usedSaleOrderIds as $soId) {
                                if ($soId !== $prioritySaleOrderId) {
                                    $hasNonPriority = true;
                                    break;
                                }
                            }
                            $isMixed = $hasPriority && $hasNonPriority;
                            
                            // Scoring: 1=Exact+Priority, 2=Exact+Mixed, 3=Manual+Priority, 4=Exact+NonPriority, 5=Manual+NonPriority
                            if (!$isManual && $hasPriority && !$hasNonPriority) return 1;
                            if (!$isManual && $isMixed) return 2;
                            if ($isManual && $hasPriority && !$hasNonPriority) return 3;
                            if (!$isManual && !$hasPriority && $hasNonPriority) return 4;
                            if ($isManual && !$hasPriority && $hasNonPriority) return 5;
                            return 6;
                        }

                        // Sort combinations within each item/gsm group
                        foreach ($combos as $item => $gsmGroups) {
                            foreach ($gsmGroups as $gsm => $comboList) {
                                usort($combos[$item][$gsm], function($a, $b) use ($sizeToSaleOrderMap, $prioritySaleOrderId) {
                                    $scoreA = getCombinationPriority($a, $sizeToSaleOrderMap, $prioritySaleOrderId);
                                    $scoreB = getCombinationPriority($b, $sizeToSaleOrderMap, $prioritySaleOrderId);
                                    if ($scoreA !== $scoreB) {
                                        return $scoreA <=> $scoreB;
                                    }
                                    return array_sum($b['selected']) <=> array_sum($a['selected']);
                                });
                            }
                        }

                                                /* ---------------------------------
                        SIZES USED IN PENDING COMBINATIONS
                        -----------------------------------*/
                        $pendingUsedSizes = [];

                        if (!empty($pending_deckle_matches)) {
                            foreach ($pending_deckle_matches as $row) {

                                $item = $row['item_name'];
                                $gsm  = $row['gsm'];

                                // "13 + 12 = 25"
                                $left = explode('=', $row['combination'])[0];
                                $sizes = array_map('trim', explode('+', $left));

                                foreach ($sizes as $sz) {
                                    $fullSize = $sz . 'X' . $gsm;   // "13X120"
                                    $pendingUsedSizes[$item][$fullSize] =
                            ($pendingUsedSizes[$item][$fullSize] ?? 0) + 1;

                                }
                            }
                        }



                        /* ---------------------------------
                        PENDING DECKLE MATCHES HANDLING
                        -----------------------------------*/
                        // Note: Pending deckle matches are handled separately in the UI
                        // Remaining items after all passes and fallback are shown in Remaining Item table

                        // print_r($remaining_sizes);
                        // die;
                        $other_size_arr = [];
                        @endphp
                        @foreach($other_sale_order as $key => $value)
                            @php 
                                $order_quantity = 0;$estimate_quantity = 0;
                                foreach ($value as $k1 => $v1) {
                                    $order_quantity = $order_quantity + $v1['order_quantity'];
                                    $estimate_quantity = $estimate_quantity + $v1['estimate_quantity'];
                                }
                                $approx_qty = $order_quantity;
                                if($value[0]['unit']=="KG"){
                                    $reelArr = [];$sizeArr = [];
                                    array_push($reelArr,$order_quantity);
                                    $data = explode("X",$value[0]['size']);
                                    array_push($sizeArr,$data[0]);
                                    $approx_qty = 0;                                        
                                    for($i=0;$i<count($reelArr);$i++){
                                        if($reelArr[$i]!=""){
                                        $approx_qty = $approx_qty + $reelArr[$i]/($sizeArr[$i]*15);
                                        }
                                    }
                                    $approx_qty = round($approx_qty);
                                }
                                $combination_size = "";
                            @endphp                        
                            @if($value[0]['sub_unit']!="INCH")
                                @php 
                                    $detail = explode("X",$value[0]['size']);
                                    if($value[0]['sub_unit']=="CM"){
                                        $length_inch = round($detail['0']/2.54,2);
                                        $detail['0']." CM (".$length_inch." INCH)X".$detail['1'];
                                        $combination_size = $length_inch."X".$detail['1'];
                                    }
                                    if($value[0]['sub_unit']=="MM"){
                                        $length_inch = round($detail['0']/25.4,2);
                                        $detail['0']." MM (".$length_inch." INCH)X".$detail['1'];
                                        $combination_size = $length_inch."X".$detail['1'];
                                    }
                                @endphp
                            @else                                    
                                @php $combination_size = $value[0]['size'];@endphp
                            @endif
                            @php 
                                array_push($other_size_arr,array(
                                    "item"=>$value[0]['item_name'],
                                    "size"=>$combination_size,
                                    "quantity"=>$approx_qty - $estimate_quantity,
                                    'actual_size'=>$value[0]['size'],
                                    'unit'=>$value[0]['sub_unit'],
                                )); 
                                
                            @endphp
                        @endforeach
                        @php                        
                        @endphp
                        <table class="table table-bordered">
                            <thead>
                                <th>Item</th>
                                <th>Gsm</th>
                                <th>Quantity</th>
                            </thead>
                            <tbody>
                                @foreach($combos as $key => $value)
                                    @foreach($value as $k1 => $v1)
                                        <tr class="deckle-row" data-item="{{$key}}" data-gsm="{{$k1}}">

                                            <td>{{$key}}</td>
                                            <td>{{$k1}}</td>
                                            <td class="quantity-cell">
                                                
                                                @php $total_reel = 0;@endphp
                                                <table class="table table-bordered">

                                                    @foreach($v1 as $set)
                                                        @php 
                                                            $sizeArr = [];
                                                            foreach($set['details'] as $detail){
                                                                if($detail['unit']!="INCH"){
                                                                    $actual_size_arr = explode("X",$detail['actual_size']);
                                                                    $inch_size_arr = explode("X",$detail['size']);
                                                                    $sizeArr[$inch_size_arr[0]] = $actual_size_arr[0]." ".$detail['unit']." (".$inch_size_arr[0]." INCH)";
                                                                }
                                                            }                                                        
                                                        @endphp
                                                        <tr class="{{ isset($set['manual']) && $set['manual'] ? 'manual-row combo-row' : 'combo-row' }}">
                                                            <th
                                                                @if(isset($set['user']) && $set['user'])
                                                                    data-user="1"
                                                                @endif
                                                                @if(isset($set['manual']) && $set['manual'])
                                                                    data-base="{{ $set['selected'][0] }}"
                                                                    data-parts='@json($set["selected"])'
                                                                    data-min="{{ $targetMin }}"
                                                                    data-max="{{ $targetMax }}" data-ss="@isset($set["filler"])@json($set["filler"])@endisset"
                                                                @endif
                                                            >

                                                                @if(isset($set['manual']) && $set['manual'])
                                                                    <span class="badge bg-warning me-2">Manual</span>
                                                                @endif

                                                                {{ implode(' + ', $set['selected']) }} = {{ array_sum($set['selected']) }}

                                                                @if(isset($set['manual']) && $set['manual'])
                                                                    <button class="btn btn-sm btn-outline-primary ms-2 edit-manual">
                                                                        Edit
                                                                    </button>
                                                                @endif

                                                            </th>
                                                        </tr>
                                                    @php $total_reel = $total_reel + count($set['selected']); @endphp
                                                @endforeach
                                                <tr class="total-row">
                                                    <th style="color: green; display:flex; justify-content:space-between; align-items:center">
                                                        <span>Total Reel : {{ $total_reel }} | Total Set : {{ count($v1) }}</span>
                                                        <div class="d-flex gap-2">
                                                            <button class="btn btn-sm btn-outline-secondary view-options-btn" 
                                                                    data-item="{{$key}}" data-gsm="{{$k1}}">
                                                                View Options
                                                            </button>

                                                            <button class="btn btn-sm btn-outline-success add-manual">
                                                                + Add Manual
                                                            </button>
                                                        </div>
                                                    </th>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                @endforeach

                            @endforeach
                            
                        </tbody>
                    </table>
                </div>
                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4 noprint">
                    <h5 class="transaction-table-title m-0 py- noprint">Remaining Item</h5>
                </div>
                <div class="transaction-table bg-white table-view shadow-sm mb-5 noprint">
                    <table class="table table-bordered" id="remainingItemTable">
                        <thead>
                            <th>Item</th>
                            <th>Size</th>
                            <th>Quantity</th>
                        </thead>
                        <tbody>
                            @foreach($remaining as $key => $value)
                                    <tr>
                                        <td>{{$key}}</td>
                                        <td>
                                            @foreach($value as $k1 => $v1)
                                                <strong>{{ $k1 }}</strong><br>
                                            @endforeach
                                        </td>
                                        <td>
                                            @php $total_reel = 0; @endphp
                                            @foreach($value as $k1 => $v1)
                                                <strong>{{$v1['qty']}}</strong><br>
                                                @php $total_reel = $total_reel + $v1['qty']; @endphp
                                            @endforeach
                                            <strong style="color: green">Total Quantity : {{ $total_reel }}</strong>
                                        </td>
                                    </tr>
                                @endforeach
                        </tbody>
                    </table>
                </div>
                @if(!empty($pending_deckle_matches))
                <div class="table-title-bottom-line bg-warning shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0">
                        Possible Deckle Combination From Pending Orders
                    </h5>
                </div>

                <div class="transaction-table bg-white table-view shadow-sm mb-5">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>GSM</th>
                                <th>Combination</th>
                                <th>From Sale Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pending_deckle_matches as $row)
                                <tr class="pending-row"
                                    data-item="{{ $row['item_name'] }}"
                                    data-gsm="{{ $row['gsm'] }}"
                                    data-combo="{{ $row['combination'] }}"
                                    data-saleorder="{{ $row['from_sale_order'] }}">
                                    <td>{{ $row['item_name'] }}</td>
                                    <td>{{ $row['gsm'] }}</td>
                                    <td>{{ $row['combination'] }}</td>
                                    <td>
                                        {{ $row['from_sale_order'] }}
                                        <br>
                                        <button class="btn btn-sm btn-success use-combo-btn"
                                            data-item="{{ $row['item_name'] }}"
                                            data-gsm="{{ $row['gsm'] }}"
                                            data-combo="{{ $row['combination'] }}"
                                            data-saleorder="{{ $row['from_sale_order'] }}">
                                            Use This Combination
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-light title-border-redius border-divider shadow-sm py-2 px-4 mt-4">
                    <h5 class="transaction-table-title m-0 py-2">Saved Set Sale Orders</h5>
                </div>

                <div class="transaction-table bg-white table-view shadow-sm mb-5">
                    <table class="table table-bordered" id="savedDeckleTable">
                        <thead>
                            <tr>
                                <th>Sale Order</th>
                                <th>Item</th>
                                <th>GSM</th>
                                <th>Combination</th>
                                <th style="width:220px">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                </div>
            </div>
            <div class="col-lg-1 d-none d-lg-flex justify-content-center px-1 noprint">
                <div class="shortcut-key ">
                <p class="font-14 fw-500 font-heading m-0">Shortcut Keys</p>
                <button class="p-2 transaction-shortcut-btn my-2 ">
                    F1
                    <span class="ps-1 fw-normal text-body">Help</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 ">
                    <span class="border-bottom-black">F1</span>
                    <span class="ps-1 fw-normal text-body">Add Account</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F2</span>
                        <span class="ps-1 fw-normal text-body">Add Item</span>
                </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        F3
                        <span class="ps-1 fw-normal text-body">Add Master</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F3</span>
                        <span class="ps-1 fw-normal text-body">Add Voucher</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F5</span>
                        <span class="ps-1 fw-normal text-body">Add Payment</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F6</span>
                        <span class="ps-1 fw-normal text-body">Add Receipt</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F7</span>
                        <span class="ps-1 fw-normal text-body">Add Journal</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F8</span>
                        <span class="ps-1 fw-normal text-body">Add Sales</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-4 ">
                        <span class="border-bottom-black">F9</span>
                        <span class="ps-1 fw-normal text-body">Add Purchase</span>
                    </button>

                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">B</span>
                        <span class="ps-1 fw-normal text-body">Balance Sheet</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">T</span>
                        <span class="ps-1 fw-normal text-body">Trial Balance</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">S</span>
                        <span class="ps-1 fw-normal text-body">Stock Status</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">L</span>
                        <span class="ps-1 fw-normal text-body">Acc. Ledger</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">I</span>
                        <span class="ps-1 fw-normal text-body">Item Summary</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">D</span>
                        <span class="ps-1 fw-normal text-body">Item Ledger</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">G</span>
                        <span class="ps-1 fw-normal text-body">GST Summary</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">U</span>
                        <span class="ps-1 fw-normal text-body">Switch User</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F</span>
                        <span class="ps-1 fw-normal text-body">Configuration</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">K</span>
                        <span class="ps-1 fw-normal text-body">Lock Program</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="ps-1 fw-normal text-body">Training Videos</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="ps-1 fw-normal text-body">GST Portal</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-4 ">
                        Search Menu
                    </button>
                </div>
            </div>
        </div>
    </section>
</div>
<!-- Modal ---for delete ---------------------------------------------------------------icon-->
<div class="modal fade" id="deckleOptionsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Deckle Options (per Item / GSM)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="deckleOptionsContainer" class="row">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="manualEditModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Edit Manual Deckle Combination</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label>Base Size (fixed)</label>
          <input type="text" class="form-control" id="baseSize" readonly>
        </div>
        <div id="manualInputs"></div>
        <div class="mt-3 fw-bold">
        Total: <span id="manualTotal">0</span>
        </div>
        <div class="mt-1 text-muted">
        Allowed Range: <span id="rangeInfo"></span>
        </div>

        <div class="text-danger mt-2 d-none" id="manualError"></div>
        <div class="text-danger mt-2 d-none" id="manualError"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-success" id="saveManualCombo">Update</button>
      </div>

    </div>
  </div>
</div>
<div class="modal fade" id="addManualModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Add Manual Deckle Combination</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div id="addManualInputs"></div>

        <div class="mt-3 fw-bold">
            Total: <span id="addManualTotal">0</span>
        </div>
        <div class="text-muted">
            Allowed Range: <span id="addManualRange"></span>
        </div>

        <div class="text-danger mt-2 d-none" id="addManualError"></div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-success" id="saveAddManual">Add</button>
      </div>

    </div>
  </div>
</div>

</body>
@include('layouts.footer')
<script>
    window.setDeckleStock = @json($size_arr);
    let stagedUserManualDeckles = [];
    let activeManualRow = null;
    let addManualTarget = null;
    let usedSizes = {}; 
    function initializeUsedSizesFromDOM() {
        usedSizes = {}; 
        document.querySelectorAll('.deckle-row').forEach(row => {
            let item = row.dataset.item;
            let gsm  = row.dataset.gsm;

                row.querySelectorAll('tr.combo-row').forEach(tr => {

                    let th = tr.querySelector('th');
                    let parts = [];

                    // MANUAL rows → reliable source
                    if (th.dataset.parts) {
                        parts = JSON.parse(th.dataset.parts).map(Number);
                    } 
                    // AUTO / PENDING rows → parse text
                    else {
                        let clean = th.cloneNode(true);
                        clean.querySelectorAll('span,button').forEach(e => e.remove());
                        let left = clean.innerText.split('=')[0];
                        parts = (left.match(/\d+(\.\d+)?/g) || []).map(parseFloat);
                    }
                    parts.forEach(w => {
                        let fullSize = w + 'X' + gsm;
                        if (existsInSetSalesOrder(item, fullSize)) {
                            useSize(item, fullSize, 1);
                        }
                    });
                });
        });
        refreshRemainingItems();
    }
    function existsInSetSalesOrder(item, fullSize) {
        return window.setDeckleStock.some(
            row => row.item === item && row.size === fullSize
        );
    }

    function normalizeComboFromParts(parts) {
        return parts.slice().sort((a,b)=>a-b).join('+');
    }
    function useSize(item, size, qty = 1) {
        if (!usedSizes[item]) usedSizes[item] = {};
        usedSizes[item][size] = (usedSizes[item][size] || 0) + qty;
    }

    function releaseSize(item, size, qty = 1) {
        if (!usedSizes[item]?.[size]) return;
        usedSizes[item][size] -= qty;
        if (usedSizes[item][size] <= 0) delete usedSizes[item][size];
    }

    function extractPartsFromRow(tr) {
        let th = tr.querySelector('th');
        if (th.dataset.parts) {
            return JSON.parse(th.dataset.parts).map(Number);
        }
        let txt = th.cloneNode(true);
        txt.querySelectorAll('span,button').forEach(e=>e.remove());
        let left = txt.innerText.split('=')[0];
        return (left.match(/\d+(\.\d+)?/g) || []).map(parseFloat);
    }

    $(document).ready(function(){
        $(".size_quantity").keyup(function(){
            let stock = $(this).attr('data-stock');
            let quantity = $(this).val();
            if(quantity>stock){
                $(this).val('');
                alert("Invalid Quantity");
            }
        });
        $(".set_range").click(function(){
            let targetMin = $(this).attr('data-form_size');
            let targetMax = $(this).attr('data-to_size');
            let html = '<div class="modal fade" id="setRangeModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">'+
                            '<div class="modal-dialog">'+
                            '<div class="modal-content">'+
                                '<div class="modal-header">'+
                                    '<h5 class="modal-title" id="exampleModalLabel">Set Deckle Size Range</h5>'+
                                    '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>'+
                                '</div>'+
                                '<div class="modal-body">'+
                                    '<form method="POST" action="{{route("save_sale_order_deckle_range")}}">'+
                                        '@csrf'+
                                        '<div class="mb-3">'+
                                            '<label for="from_size" class="col-form-label">From Size:</label>'+
                                            '<input type="text" class="form-control" id="from_size" name="from_size" value="'+targetMin+'" required>'+
                                        '</div>'+
                                        '<div class="mb-3">'+
                                            '<label for="to_size" class="col-form-label">To Size:</label>'+
                                            '<input type="text" class="form-control" id="to_size" name="to_size" value="'+targetMax+'" required>'+
                                        '</div>'+
                                        '<div class="modal-footer">'+
                                            '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>'+
                                            '<button type="submit" class="btn btn-primary">Save</button>'+
                                        '</div>'+
                                    '</form>'+
                                '</div>'+
                            '</div>'+
                            '</div>'+
                        '</div>';
            $("body").append(html);
            var myModal = new bootstrap.Modal(document.getElementById('setRangeModal'), {
               keyboard: false 
            });
            myModal.show();
            $('#setRangeModal').on('hidden.bs.modal', function () {
               $(this).remove();
            });
        });

        $(document).on('click', '.set_filler_range', function () {

            let from = $(this).data('from') || '';
            let to   = $(this).data('to') || '';

            let html = `
            <div class="modal fade" id="setFillerRangeModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Set Filler Size Range</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="{{ route('save_sale_order_filler_range') }}">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label>From Size</label>
                                    <input type="number" step="0.01" class="form-control"
                                        name="filler_from_size" value="${from}" required>
                                </div>
                                <div class="mb-3">
                                    <label>To Size</label>
                                    <input type="number" step="0.01" class="form-control"
                                        name="filler_to_size" value="${to}" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-warning" type="submit">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>`;

            $('body').append(html);

            let modal = new bootstrap.Modal(document.getElementById('setFillerRangeModal'));
            modal.show();

            $('#setFillerRangeModal').on('hidden.bs.modal', function () {
                $(this).remove();
            });
        });
        initializeUsedSizesFromDOM();
        loadSavedDeckleList();
    });
    function printpage() {
    window.print();
}

    $("#save_deckle").off("click").on("click", function () {

        if (!confirm("Are you sure you want to save it?")) return;

        let saleOrdersRaw = new URLSearchParams(window.location.search).get("sale_order");
        let itemId = new URLSearchParams(window.location.search).get("item_id");

        let saleOrderIds = JSON.parse(saleOrdersRaw);

        let systemManualDeckles = [];
        let userManualDeckles   = stagedUserManualDeckles.slice(); // 🔥 ONLY NEW

        // SYSTEM combos CAN be rebuilt (they are replace-mode)
        $('.deckle-row').each(function () {

            let gsm = $(this).data('gsm');

            $(this).find('tr.combo-row th').each(function () {

                if ($(this).data('user') === 1) return; // ❌ skip USER rows

                let clean = $(this).clone();
                clean.find('span,button').remove();
                let left = clean.text().split('=')[0];
                let parts = (left.match(/\d+(\.\d+)?/g) || []).map(Number);

                if (!parts.length) return;

                let total = parts.reduce((a,b)=>a+b,0);
                let fixedCount = getFixedCountForParts(
                    parts,
                    $(this).closest('.deckle-row').data('item'),
                    gsm
                );

                systemManualDeckles.push({
                    gsm: gsm,
                    fixed: parts.slice(0, fixedCount),
                    filler: parts.slice(fixedCount),
                    total: total
                });
            });
        });

        if (!systemManualDeckles.length && !userManualDeckles.length) {
            alert("Nothing new to save");
            return;
        }

        $.post("{{ url('save-sale-order-deckle-status') }}", {
            _token: "{{ csrf_token() }}",
            sale_order_ids: saleOrderIds,
            item_id: itemId,
            system_manual_deckles: systemManualDeckles,
            user_manual_deckles: userManualDeckles
        }, function (res) {

            if (res.status) {

                // 🔥 CRITICAL RESET
                stagedUserManualDeckles = [];

                alert("Saved successfully");
                location.reload();
            }
        });
    });

    function loadSavedDeckleList() {

        let saleOrdersRaw = `{!! request()->sale_order !!}`;
        let itemId = `{{ request()->item_id }}`;

        if (!saleOrdersRaw || !itemId) return;

        let saleOrderIds = JSON.parse(saleOrdersRaw);

        $.ajax({
            url: "{{ url('/deckle/get-saved') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                sale_order_ids: saleOrderIds,
                item_id: itemId
            },
            success: function (res) {

                let tbody = $("#savedDeckleTable tbody");
                tbody.empty();

                if (!res || res.length === 0) {
                    tbody.append(`
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No saved deckle combinations
                            </td>
                        </tr>
                    `);
                    return;
                }

                res.forEach(block => {

                    let systemSets = block.sets.filter(s => s.type !== 'USER');
                    let userSets   = block.sets.filter(s => s.type === 'USER');

                    let allSets = [...systemSets, ...userSets];
                    let rowspan = allSets.length;

                    allSets.forEach((set, index) => {

                        let isFirstRow = index === 0;
                        let isUser     = set.type === 'USER';

                        tbody.append(`
                            <tr>
                                ${isFirstRow ? `<td rowspan="${rowspan}">${block.sale_orders}</td>` : ``}
                                ${isFirstRow ? `<td rowspan="${rowspan}">${block.item_name}</td>` : ``}
                                ${isFirstRow ? `<td rowspan="${rowspan}">${block.gsm}</td>` : ``}

                                <td>
                                    ${isUser ? '<strong></strong> ' : ''}
                                    ${set.display}
                                </td>

                                <td>
                                    ${isUser ? `
                                        <button class="btn btn-sm btn-outline-danger remove-single-deckle"
                                            data-id="${set.id}">
                                            Remove
                                        </button>
                                    ` : ``}
                                </td>
                            </tr>
                        `);
                    });

                    tbody.append(`
                        <tr class="table-warning">
                            <td colspan="5" class="text-end">
                                <button class="btn btn-danger btn-sm remove-complete-gsm"
                                    data-item="${block.item_id}"
                                    data-gsm="${block.gsm}"
                                    data-so='${JSON.stringify(saleOrderIds)}'>
                                    Remove Complete GSM Combination
                                </button>
                            </td>
                        </tr>
                    `);

                });

            }
        });
    }

    $(document).on("click", ".cancelSize", function () {

        let sizeRowId = $(this).data("id");

        if (!confirm("Remove this size from saved deckle?")) {
            return;
        }

        $.ajax({
            url: "{{ url('/deckle/cancel-size') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                size_id: sizeRowId
            },
            success: function (res) {

                if (res.status) {
                    loadSavedDeckleList();
                    refreshRemainingItems(); // 🔥 bring size back
                } else {
                    alert(res.msg || "Unable to remove size");
                }
            }
        });
    });

    window.deckleOptions = {!! json_encode($deckleOptions) !!};

    document.addEventListener('click', function(e){
        if(e.target && e.target.classList.contains('view-options-btn')){
            var item = e.target.getAttribute('data-item');
            var gsm = e.target.getAttribute('data-gsm');

            var container = document.getElementById('deckleOptionsContainer');
            container.innerHTML = ''; 

            if(!window.deckleOptions || window.deckleOptions.length === 0){
                container.innerHTML = '<div class="col-12"><em>No alternatives found.</em></div>';
            } else {
                window.deckleOptions.forEach(function(opt, idx){
                    var col = document.createElement('div');
                    col.className = 'col-md-4';
                    var card = document.createElement('div');
                    card.className = 'card mb-3';
                    var header = document.createElement('div');
                    header.className = 'card-header';
                    header.innerHTML = '<strong>Option ' + (idx+1) + '</strong>';
                    var body = document.createElement('div');
                    body.className = 'card-body';

                    var setsHtml = '';
                    if(opt[item] && opt[item][gsm] && opt[item][gsm].length > 0){
                        setsHtml += '<table class="table table-sm mb-0"><tbody>';
                        var totalReel = 0;
                        opt[item][gsm].forEach(function(set){
                            setsHtml += '<tr><td>';
                            setsHtml += set['selected'].join(' + ');
                            setsHtml += ' = ' + set['selected'].reduce(function(a,b){return a + parseFloat(b);},0);
                            setsHtml += '</td></tr>';
                            totalReel += set['selected'].length;
                        });
                        setsHtml += '<tr><td style="color:green">Total Reel : ' + totalReel + ' | Total Set : ' + opt[item][gsm].length + '</td></tr>';
                        setsHtml += '</tbody></table>';
                    } else {
                        setsHtml = '<em>No sets for this option</em>';
                    }

                    body.innerHTML = setsHtml;

                    var footer = document.createElement('div');
                    footer.className = 'card-footer text-center';
                    footer.innerHTML = '<button class="btn btn-sm btn-primary apply-option" data-item="'+item+'" data-gsm="'+gsm+'" data-opt="'+idx+'">Select This Option</button>';

                    card.appendChild(header);
                    card.appendChild(body);
                    card.appendChild(footer);
                    col.appendChild(card);
                    container.appendChild(col);
                });
            }

            var modal = new bootstrap.Modal(document.getElementById('deckleOptionsModal'));
            modal.show();
        }
    });

        document.addEventListener('click', function(e){
            if(!e.target.classList.contains('apply-option')) return;

            let item = e.target.dataset.item;
            let gsm  = e.target.dataset.gsm;
            let optIndex = parseFloat(e.target.dataset.opt,10);
            let sets = window.deckleOptions[optIndex]?.[item]?.[gsm];

            if(!sets) return alert('Invalid option');

            let deckleRow = document.querySelector(
                `.deckle-row[data-item="${item}"][data-gsm="${gsm}"]`
            );
            let table = deckleRow.querySelector('.quantity-cell table');

            let originalStock = {};

            window.setDeckleStock.forEach(row => {

                if (row.item !== item) return;

                let [w, gsm] = row.size.split('X').map(Number);
                if (String(row.size.split('X')[1]) !== String(gsm)) return;

                originalStock[w] = (originalStock[w] || 0) + row.quantity;
            });

            table.querySelectorAll('tr.combo-row, tr.total-row').forEach(tr=>tr.remove());

            let used = {};

            sets.forEach(set => {

                let parts = set.selected.map(Number);

                parts.forEach(w => {

                    used[w] = (used[w] || 0) + 1;

                    let fullSize = w + 'X' + gsm;

                    if (!usedSizes[item]) usedSizes[item] = {};
                    if (existsInSetSalesOrder(item, fullSize)) {
                        useSize(item, fullSize, 1);
                    }
                });

                table.insertAdjacentHTML('beforeend', `
                    <tr class="combo-row">
                        <th>${parts.join(' + ')} = ${parts.reduce((a,b)=>a+b,0)}</th>
                    </tr>
                `);
            });

            let min = parseFloat($('.set_range').data('form_size'));
            let max = parseFloat($('.set_range').data('to_size'));

            let pool = [];
            Object.keys(originalStock).forEach(w => {
                let remaining = originalStock[w] - (used[w] || 0);
                for (let i = 0; i < remaining; i++) {
                    pool.push(parseFloat(w));
                }
            });

            pool.sort((a, b) => b - a);

            while (pool.length > 0) {
                let parts = [];
                let sum = 0;

                while (pool.length > 0 && parts.length < 4) {
                    let next = pool[0];
                    if (sum + next > max) break;
                    parts.push(next);
                    sum += next;
                    pool.shift();
                }

            let fillerMin = {{ $deckle_range->filler_from_size ?? 0 }};
            let fillerMax = {{ $deckle_range->filler_to_size ?? 0 }};

            if (sum < min && parts.length < 5) {

                let needed = min - sum;

                if (needed < fillerMin || needed > fillerMax) {
                    continue;
                }

                parts.push(needed);
                sum += needed;
            }


                if (sum < min || sum > max) break;

                table.insertAdjacentHTML('beforeend', `
                    <tr class="combo-row manual-row">
                        <th data-base="${parts[0]}"
                            data-parts='${JSON.stringify(parts)}'
                            data-min="${min}"
                            data-max="${max}">
                            <span class="badge bg-warning me-2">Manual</span>
                            ${parts.join(' + ')} = ${sum}
                            <button class="btn btn-sm btn-outline-primary ms-2 edit-manual">Edit</button>
                        </th>
                    </tr>

                `);

            }

            recalcDeckleTotals($(deckleRow.querySelector('.quantity-cell')));

            refreshRemainingItems();

            bootstrap.Modal.getInstance(
                document.getElementById('deckleOptionsModal')
            )?.hide();
        });

    function normalizeSize(sizeStr) {
        let match = sizeStr.match(/(\d+(\.\d+)?)\s*X\s*(\d+)/);
        if (!match) return null;
        return match[1] + 'X' + match[2]; 
    }

    function refreshRemainingItems() {

        let originalMap = {};

        window.setDeckleStock.forEach(row => {
            if (!originalMap[row.item]) originalMap[row.item] = {};
            originalMap[row.item][row.size] = row.quantity;
        });

        $('#remainingItemTable tbody').empty();

        Object.keys(originalMap).forEach(item => {

            let sizesHtml = '';
            let qtyHtml   = '';
            let totalQty  = 0;

            Object.keys(originalMap[item]).forEach(size => {

                let used = usedSizes[item]?.[size] || 0;
                let remaining = originalMap[item][size] - used;

                if (remaining <= 0) return;

                sizesHtml += `<strong>${size}</strong><br>`;
                qtyHtml   += `<strong>${remaining}</strong><br>`;
                totalQty  += remaining;
            });

            if (totalQty > 0) {
                $('#remainingItemTable tbody').append(`
                    <tr>
                        <td>${item}</td>
                        <td>${sizesHtml}</td>
                        <td>
                            ${qtyHtml}
                            <strong style="color:green">Total Quantity : ${totalQty}</strong>
                        </td>
                    </tr>
                `);
            }
        });
    }

    $(document).on('click', '.use-combo-btn', function () {

        let comboText = $(this).data('combo');   // "13 + 12 = 25"
        let item      = $(this).data('item');
        let gsm       = $(this).data('gsm');
        let fromSO    = $(this).data('saleorder');

        if (!confirm(`Use combination ${comboText} from Sale Order ${fromSO}?`)) {
            return;
        }

        let sizeParts = comboText
        .split('=')[0]
        .split('+')
        .map(s => parseFloat(s.trim()));

        let row = document.querySelector(
            `.deckle-row[data-item="${item}"][data-gsm="${gsm}"]`
        );

        if (!row) {
            alert('Deckle row not found');
            return;
        }

        let table = row.querySelector('.quantity-cell table');

        let manualRowsToRemove = [];

        table.querySelectorAll('tr.manual-row').forEach(manualRow => {

            let th = manualRow.querySelector('th');

            let manualGsm = row.getAttribute('data-gsm');

            let manualParts = JSON.parse(th.dataset.parts || '[]').map(Number);

            manualParts.forEach(w => {
                let fullSize = w + 'X' + manualGsm;
                releaseSize(item, fullSize, 1);
            });

            manualRowsToRemove.push(manualRow);
        });

        manualRowsToRemove.forEach(r => r.remove());


        sizeParts.forEach(sz => {
            let fullSize = sz + 'X' + gsm;
            if (!usedSizes[item]) usedSizes[item] = {};
            if (existsInSetSalesOrder(item, fullSize)) {
        useSize(item, fullSize, 1);
    }
        });

        $(this)
            .removeClass('btn-success')
            .addClass('btn-secondary')
            .text('Added')
            .prop('disabled', true);

        let totalRow = table.querySelector('tr.total-row');
        if (totalRow) totalRow.remove();

        table.insertAdjacentHTML('beforeend', `
            <tr class="combo-row">
                <th style="background:#e6f7ff">
                    ${comboText}
                    <span class="badge bg-info ms-2">From ${fromSO}</span>
                </th>
            </tr>
        `);

        let comboRows = table.querySelectorAll('tr.combo-row');
        let totalSet  = comboRows.length;
        let totalReel = 0;

        comboRows.forEach(tr => {
            let leftSide = tr.innerText.split('=')[0];
            let nums = leftSide.match(/\d+(\.\d+)?/g) || [];
            totalReel += nums.length;
        });

        table.insertAdjacentHTML('beforeend', `
            <tr class="total-row">
                <th style="color:green">
                    Total Reel : ${totalReel} | Total Set : ${totalSet}
                </th>
            </tr>
        `);

        refreshRemainingItems();

        $(this).closest('tr').fadeOut(300, function () {
            $(this).remove();
        });

    });

    function getFixedCountForParts(parts, item, gsm) {
        let stock = {};
        
        window.setDeckleStock.forEach(row => {
            if (row.item !== item) return;

            let [w, g] = row.size.split('X').map(Number);
            if (String(g) !== String(gsm)) return;

            stock[w] = (stock[w] || 0) + row.quantity;
        });

        let fixedCount = 0;
        
        for (let w of parts) {
            if (stock[w] > 0) {
                fixedCount++;
                stock[w]--;
            } else {
                break; 
            }
        }

        return fixedCount;
    }

    $(document).on('click', '.edit-manual', function () {

        let th = $(this).closest('th');
        activeManualRow = th;

        let parts = th.data('parts').map(Number);
        let min   = th.data('min');
        let max   = th.data('max');

        let isSystemManual = th.data('base') !== undefined; // 🔥 KEY LINE

        $('#rangeInfo').text(`${min} – ${max}`);
        $('#manualError').addClass('d-none');

        let html = '';
        let total = 0;

        if (isSystemManual) {
            $('#baseSize').val(parts[0]).closest('.mb-2').show();
            console.log(th.closest('.deckle-row').data('item'));
            let fixedCount = getFixedCountForParts(
                parts,
                th.closest('.deckle-row').data('item'),
                th.closest('.deckle-row').data('gsm')
            );
            
            for (let i = 1; i < 5; i++) {
                let val = parts[i] ?? '';
                let isFixed = i < fixedCount;

                if (val) total += parseFloat(val);

                html += `
                    <div class="mb-2">
                        <label>
                            Reel ${i + 1} (${isFixed ? 'Fixed' : 'Filler'})
                        </label>
                        <input type="number"
                            class="form-control manual-part"
                            value="${val}"
                            ${isFixed ? 'readonly' : ''}>
                    </div>
                `;
            }

            total += parts[0];
        } else {
            $('#baseSize').val('').closest('.mb-2').hide();

            for (let i = 0; i < 5; i++) {
                let val = parts[i] ?? '';
                if (val) total += parseFloat(val);

                html += `
                    <div class="mb-2">
                        <label>Reel ${i + 1}</label>
                        <input type="number"
                            class="form-control manual-part"
                            value="${val}">
                    </div>
                `;
            }
        }

        $('#manualInputs').html(html);
        $('#manualTotal').text(total);

        new bootstrap.Modal(
            document.getElementById('manualEditModal')
        ).show();
    });


    $(document).on('input', '.manual-part', function () {

        if (!activeManualRow) return;

        let sum = 0;

        if ($('#baseSize').is(':visible')) {
            let base = parseFloat($('#baseSize').val());
            if (!isNaN(base)) sum += base;
        }

        $('.manual-part').each(function () {
            let v = parseFloat($(this).val());
            if (!isNaN(v)) sum += v;
        });

        $('#manualTotal').text(sum);
    });

    $('#saveManualCombo').on('click', function () {

        if (!activeManualRow) return;

        let parts = [];

        if ($('#baseSize').is(':visible')) {
            let base = parseFloat($('#baseSize').val());
            if (!isNaN(base)) parts.push(base);
        }

        $('.manual-part').each(function () {
            let v = parseFloat($(this).val());
            if (!isNaN(v)) parts.push(v);
        });


        if (parts.length > 5) {
            $('#manualError').removeClass('d-none').text('Maximum 5 reels allowed.');
            return;
        }
        let fillerMin = {{ $deckle_range->filler_from_size ?? 0 }};
let fillerMax = {{ $deckle_range->filler_to_size ?? 0 }};

// 🔥 detect fixed reels (SYSTEM manual)
let fixedCount = 0;

if ($('#baseSize').is(':visible')) {
    fixedCount = getFixedCountForParts(
        parts,
        activeManualRow.closest('.deckle-row').data('item'),
        activeManualRow.closest('.deckle-row').data('gsm')
    );
}

// ✅ validate ONLY filler reels
for (let i = fixedCount; i < parts.length; i++) {
    let v = parts[i];

    if (v < fillerMin || v > fillerMax) {
        $('#manualError')
            .removeClass('d-none')
            .text(
                `Filler size ${v} must be between ${fillerMin} and ${fillerMax}`
            );
        return;
    }
}


        let min = parseFloat(activeManualRow.data('min'));
        let max = parseFloat(activeManualRow.data('max'));

        let sum = parts.reduce((a, b) => a + b, 0);

        if (sum < min || sum > max) {
            $('#manualError')
                .removeClass('d-none')
                .text(`Total must be between ${min} and ${max}`);
            return;
        }


        let isUserManual = activeManualRow.data('user') === 1;
        activeManualRow.html(`
            <span class="badge bg-warning me-2">Manual</span>
            ${parts.join(' + ')} = ${sum}
            <button class="btn btn-sm btn-outline-primary ms-2 edit-manual">Edit</button>
            ${isUserManual ? '<button class="btn btn-sm btn-outline-danger ms-1 remove-manual">Remove</button>' : ''}
        `);
        activeManualRow.data('parts', parts);

        recalcDeckleTotals(activeManualRow.closest('.quantity-cell'));

        bootstrap.Modal.getInstance(
            document.getElementById('manualEditModal')
        ).hide();
    });

    function recalcDeckleTotals(container) {

        let totalReel = 0;
        let totalSet  = 0;

        container.find('tr.combo-row').each(function () {

            let th = $(this).find('th');

            if (th.data('parts')) {
                totalReel += th.data('parts').length;
                totalSet++;
                return;
            }

            let cleanText = th.clone().children().remove().end().text();
            let left = cleanText.split('=')[0];
            let nums = left.match(/\d+(\.\d+)?/g) || [];

            totalReel += nums.length;
            totalSet++;
        });

        container.find('.total-row').remove();

        container.find('table').append(`
            <tr class="total-row">
                <th style="color: green; display:flex; justify-content:space-between; align-items:center">
                    <span>
                        Total Reel : ${totalReel} | Total Set : ${totalSet}
                    </span>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary view-options-btn"
                            data-item="${container.closest('.deckle-row').data('item')}"
                            data-gsm="${container.closest('.deckle-row').data('gsm')}">
                            View Options
                        </button>

                        <button class="btn btn-sm btn-outline-success add-manual">
                            + Add Manual
                        </button>
                    </div>
                </th>
            </tr>
        `);
    }

    $(document).on('click', '.removeDeckleItem', function () {

        let saleOrderId = $(this).data('so');
        let itemId      = $(this).data('item');

        if (!confirm("Remove this item from saved deckle?")) {
            return;
        }

        $.ajax({
            url: "{{ route('deckle.cancel-item') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                sale_order_id: saleOrderId,
                item_id: itemId
            },
            success: function (res) {
                if (res.status) {
                    loadSavedDeckleList();   
                    refreshRemainingItems(); 
                } else {
                    alert(res.msg || "Unable to remove");
                }
            }
        });
    });
    $(document).on('click', '.add-manual', function () {

        let deckleRow = $(this).closest('.deckle-row');
        let table = deckleRow.find('.quantity-cell table');

        addManualTarget = {
            table: table,
            item: deckleRow.data('item'),
            gsm: deckleRow.data('gsm')
        };

        let min = parseFloat($('.set_range').data('form_size'));
        let max = parseFloat($('.set_range').data('to_size'));

        $('#addManualRange').text(`${min} – ${max}`);
        $('#addManualTotal').text(0);
        $('#addManualError').addClass('d-none');

        let html = '';
        for (let i = 1; i <= 5; i++) {
            html += `
                <div class="mb-2">
                    <label>Reel ${i}</label>
                    <input type="number" step="0.01" class="form-control add-manual-part">
                </div>
            `;
        }

        $('#addManualInputs').html(html);

        new bootstrap.Modal(
            document.getElementById('addManualModal')
        ).show();
    });
    $(document).on('input', '.add-manual-part', function () {

        let sum = 0;
        $('.add-manual-part').each(function () {
            let v = parseFloat($(this).val());
            if (!isNaN(v)) sum += v;
        });

        $('#addManualTotal').text(sum);
    });
    $('#saveAddManual').on('click', function () {

        let parts = [];

        $('.add-manual-part').each(function () {
            let v = parseFloat($(this).val());
            if (!isNaN(v)) parts.push(v);
        });

        if (parts.length < 1 || parts.length > 5) {
            $('#addManualError')
                .removeClass('d-none')
                .text('Enter between 1 and 5 reels');
            return;
        }
let fillerMin = {{ $deckle_range->filler_from_size ?? 0 }};
let fillerMax = {{ $deckle_range->filler_to_size ?? 0 }};

// ✅ ALL reels are fillers in Add Manual
for (let v of parts) {
    if (v < fillerMin || v > fillerMax) {
        $('#addManualError')
            .removeClass('d-none')
            .text(
                `Filler size ${v} must be between ${fillerMin} and ${fillerMax}`
            );
        return;
    }
}

        let min = parseFloat($('.set_range').data('form_size'));
        let max = parseFloat($('.set_range').data('to_size'));
        let sum = parts.reduce((a,b)=>a+b,0);

        if (sum < min || sum > max) {
            $('#addManualError')
                .removeClass('d-none')
                .text(`Total must be between ${min} and ${max}`);
            return;
        }

        addManualTarget.table.find('.total-row').before(`
            <tr class="combo-row manual-row">
                <th data-user="1"
                    data-parts='${JSON.stringify(parts)}'
                    data-min="${min}"
                    data-max="${max}">
                    <span class="badge bg-warning me-2">Manual</span>
                    ${parts.join(' + ')} = ${sum}
                    <button class="btn btn-sm btn-outline-primary ms-2 edit-manual">Edit</button>
                    <button class="btn btn-sm btn-outline-danger ms-1 remove-manual">Remove</button>
                </th>
            </tr>
        `);
        stagedUserManualDeckles.push({
            gsm: addManualTarget.gsm,
            filler: parts.slice(),
            total: sum
        });

        recalcDeckleTotals(addManualTarget.table.closest('.quantity-cell'));
        refreshRemainingItems();

        bootstrap.Modal.getInstance(
            document.getElementById('addManualModal')
        ).hide();
    });
    $(document).on('click', '.remove-manual', function () {

        if (!confirm("Are you sure you want to remove this manual deckle combination?")) {
            return;
        }

        let th = $(this).closest('th');

        if (th.data('user') !== 1) return;

        let tr = th.closest('tr');
        let parts = th.data('parts') || [];

        let deckleRow = tr.closest('.deckle-row');
        let item = deckleRow.data('item');
        let gsm  = deckleRow.data('gsm');

        parts.forEach(w => {
            let fullSize = w + 'X' + gsm;
            if (existsInSetSalesOrder(item, fullSize)) {
                releaseSize(item, fullSize, 1);
            }
        });

        tr.remove();

        recalcDeckleTotals(deckleRow.find('.quantity-cell'));

        refreshRemainingItems();
    });

    $(document).on('click', '.remove-single-deckle', function () {

        let id = $(this).data('id');

        if (!confirm("Remove this manual deckle combination?")) return;

        $.post("{{ route('deckle.remove-single') }}", {
            _token: "{{ csrf_token() }}",
            id: id
        }, function (res) {
            if (res.status) {
                location.reload();  
            } else {
                alert(res.msg || "Unable to remove");
            }
        });
    });

    $(document).on('click', '.remove-complete-gsm', function () {

        if (!confirm("Remove complete combination for this GSM?")) return;

        let saleOrderIds = JSON.parse($(this).attr('data-so'));
        let itemId = $(this).data('item');
        let gsm = $(this).data('gsm');

        $.post("{{ route('deckle.remove-complete') }}", {
            _token: "{{ csrf_token() }}",
            sale_order_ids: saleOrderIds,
            item_id: itemId,
            gsm: gsm
        }, function (res) {
            if (res.status) {
                location.reload();  
            } else {
                alert(res.msg || "Unable to remove");
            }
        });
    });

</script>
@endsection