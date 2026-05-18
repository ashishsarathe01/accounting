<!DOCTYPE html>
<html>
<head>
    <title>Corrugated Box Calculator</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body{
            background:#f5f6fa;
            padding:30px;
        }

        .card-box{
            background:white;
            border-radius:12px;
            padding:20px;
            margin-bottom:20px;
            box-shadow:0 2px 10px rgba(0,0,0,0.08);
        }

        .result-box{
            background:#eef7ff;
            border-left:5px solid #0d6efd;
            padding:15px;
            border-radius:10px;
            margin-bottom:10px;
        }

        .result-title{
            font-size:14px;
            color:#666;
        }

        .result-value{
            font-size:24px;
            font-weight:bold;
            color:#0d6efd;
        }

        .section-title{
            font-size:22px;
            font-weight:bold;
            margin-bottom:20px;
        }

        .sticky-result{
            position: sticky;
            top: 20px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }

    </style>

</head>
<body>

<div class="container-fluid">

    <div class="row">

        <div class="col-md-4">

            <div class="card-box">

                <div class="section-title">
                    Box Inputs
                </div>

                <div class="mb-3">
                    <label>Length (inch)</label>
                    <input type="number" step="0.01" id="length" class="form-control" value="15">
                </div>

                <div class="mb-3">
                    <label>Width (inch)</label>
                    <input type="number" step="0.01" id="width" class="form-control" value="15">
                </div>

                <div class="mb-3">
                    <label>Height (inch)</label>
                    <input type="number" step="0.01" id="height" class="form-control" value="15">
                </div>

                <div class="mb-3">
                    <label>Ply</label>
                    <select id="ply" class="form-control">
                        <option value="3">3 Ply</option>
                        <option value="5">5 Ply</option>
                        <option value="7">7 Ply</option>
                    </select>
                </div>

                <div id="paperLayers"></div>

                <div class="mb-3">
    <label>Conversion Cost Type</label>
    <select id="conversion_type" class="form-control">
        <option value="kg">Per KG</option>
        <option value="percent">Percentage</option>
    </select>
</div>

<div class="mb-3">
    <label>Conversion Cost</label>
    <input type="number" step="0.01" id="conversion_cost" class="form-control" value="5">
</div>

<div class="mb-3">
    <label>Profit Margin (%)</label>
    <input type="number" step="0.01" id="profit_margin" class="form-control" value="10">
</div>

<div class="mb-3">
    <label>GST (%)</label>
    <input type="number" step="0.01" id="gst_percent" class="form-control" value="5">
</div>

<div class="mb-3">
    <label>Boxes Per Sheet</label>
    <input type="number" step="1" id="manual_boxes_per_sheet" class="form-control" value="1">
</div>

               

                <div class="mb-3">
                    <label>Joint Allowance (mm)</label>
                    <input type="number" step="0.01" id="joint_allowance" class="form-control" value="0">
                </div>

                <div class="mb-3">
                    <label>Cutting Margin (mm)</label>
                    <input type="number" step="0.01" id="cutting_margin" class="form-control" value="50">
                </div>

                <div class="mb-3">
                    <label>Deckle Margin (mm)</label>
                    <input type="number" step="0.01" id="deckle_margin" class="form-control" value="22">
                </div>

                <div class="mb-3">
                    <label>Available Reel Width (inch)</label>
                    <input type="number" step="0.01" id="available_width" class="form-control" value="42">
                </div>

                <div class="mb-3">
                    <label>Production Quantity</label>
                    <input type="number" step="1" id="qty" class="form-control" value="1000">
                </div>

            </div>

        </div>


        <div class="col-md-8">

            <div class="card-box sticky-result">

                <div class="section-title">
                    Live Calculation Result
                </div>

               <div class="row">

    <div class="col-md-4">
        <div class="result-box">
            <div class="result-title">Cutting Length</div>
            <div class="result-value" id="cutting_length_result">0</div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="result-box">
            <div class="result-title">Deckle Width</div>
            <div class="result-value" id="deckle_result">0</div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="result-box">
            <div class="result-title">Boxes Per Sheet</div>
            <div class="result-value" id="boxes_per_sheet">0</div>
        </div>
    </div>

</div>


<div class="row">

    <div class="col-md-4">
        <div class="result-box">
            <div class="result-title">Sheet Weight</div>
            <div class="result-value" id="sheet_weight">0</div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="result-box">
            <div class="result-title">Weight Per Box</div>
            <div class="result-value" id="weight_per_box">0</div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="result-box">
            <div class="result-title">Paper Cost Per Box</div>
            <div class="result-value" id="paper_cost_per_box">0</div>
        </div>
    </div>

</div>


<div class="row">

    <div class="col-md-4">
        <div class="result-box">
            <div class="result-title">Conversion Cost Per Box</div>
            <div class="result-value" id="conversion_cost_result">0</div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="result-box">
            <div class="result-title">Total Cost Per Box</div>
            <div class="result-value" id="total_cost_per_box">0</div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="result-box">
            <div class="result-title">Sale Price Without GST</div>
            <div class="result-value" id="sale_without_gst">0</div>
        </div>
    </div>

</div>


<div class="row">

    <div class="col-md-4">
        <div class="result-box">
            <div class="result-title">Sale Price With GST</div>
            <div class="result-value" id="sale_with_gst">0</div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="result-box">
            <div class="result-title">Total Sheet Required</div>
            <div class="result-value" id="total_sheet_required">0</div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="result-box">
            <div class="result-title">Total Paper Required</div>
            <div class="result-value" id="total_paper_required">0</div>
        </div>
    </div>

</div>


<div class="row">

    <div class="col-md-4">
        <div class="result-box">
            <div class="result-title">Total Paper Cost</div>
            <div class="result-value" id="total_paper_cost">0</div>
        </div>
    </div>

</div>


                

            </div>

        </div>

    </div>

</div>


<script>

    function createLayerInputs()
{
    let ply = parseInt(document.getElementById('ply').value);

    let layers = [];

    if(ply == 3)
    {
        layers = ['Top Paper','Flute Paper','Bottom Paper'];
    }
    else if(ply == 5)
    {
        layers = [
            'Top Paper',
            'Flute 1',
            'Center Paper',
            'Flute 2',
            'Bottom Paper'
        ];
    }
    else if(ply == 7)
    {
        layers = [
            'Top Paper',
            'Flute 1',
            'Liner 1',
            'Flute 2',
            'Center Paper',
            'Flute 3',
            'Bottom Paper'
        ];
    }

    let html = '';

    layers.forEach(function(layer,index){

        html += `
        <div class="card-box mb-2">

            <h6>${layer}</h6>

            <div class="row">

                <div class="col-md-4">
                    <label>GSM</label>
                    <input type="number"
                           class="form-control gsm"
                           value="120">
                </div>

                <div class="col-md-4">
                    <label>BF</label>
                    <input type="number"
                           class="form-control bf"
                           value="18">
                </div>

             ${layer.toLowerCase().includes('flute') ? `
                        <div class="col-md-4">
                            <label>Flute Factor</label>
                            <input type="number"
                                step="0.01"
                                class="form-control flute_factor"
                                value="1.50">
                        </div>
                        ` : ''}

                        <div class="col-md-4">
                            <label>Rate ₹/KG</label>
                            <input type="number"
                                class="form-control rate"
                                value="36">
                        </div>

            </div>

        </div>
        `;
    });

    document.getElementById('paperLayers').innerHTML = html;

    bindEvents();

    calculateBox();
}



function calculateBox()
{
    let length = parseFloat(document.getElementById('length').value) || 0;
    let width = parseFloat(document.getElementById('width').value) || 0;
    let height = parseFloat(document.getElementById('height').value) || 0;

    let jointAllowance = parseFloat(document.getElementById('joint_allowance').value) || 0;
    let cuttingMargin = parseFloat(document.getElementById('cutting_margin').value) || 0;
    let deckleMargin = parseFloat(document.getElementById('deckle_margin').value) || 0;

    let qty = parseFloat(document.getElementById('qty').value) || 0;

    let boxesPerSheet =
        parseFloat(document.getElementById('manual_boxes_per_sheet').value) || 1;

    let conversionType =
        document.getElementById('conversion_type').value;

    let conversionCost =
        parseFloat(document.getElementById('conversion_cost').value) || 0;

    let profitMargin =
        parseFloat(document.getElementById('profit_margin').value) || 0;

    let gstPercent =
        parseFloat(document.getElementById('gst_percent').value) || 0;


  
    let L = length * 25.4;
    let W = width * 25.4;
    let H = height * 25.4;


    let cuttingLengthMM =
        (2 * (L + W)) + jointAllowance + cuttingMargin;

    let deckleMM =
        H + W + deckleMargin;


    let actualSheetWidthMM =
        deckleMM * boxesPerSheet;


   let effectiveGSM = 0;

let totalPaperCostPerBox = 0;

let gsmInputs = document.querySelectorAll('.gsm');

let rateInputs = document.querySelectorAll('.rate');

let fluteFactorInputs =
document.querySelectorAll('.flute_factor');

let fluteCounter = 0;

gsmInputs.forEach(function(gsmInput,index){

    let gsm =
        parseFloat(gsmInput.value) || 0;

    let fluteFactor = 1;

    let isFlute =
        index == 1 ||
        index == 3 ||
        index == 5;

    if(isFlute)
    {
        fluteFactor =
            parseFloat(
                fluteFactorInputs[fluteCounter].value
            ) || 1;

        fluteCounter++;
    }

    effectiveGSM +=
        (gsm * fluteFactor);

});

let totalWeight =
(
    (cuttingLengthMM/25.4 )*
    (actualSheetWidthMM/25.4) *
    effectiveGSM
) / 1550000;

let weightPerBox =
    totalWeight / boxesPerSheet;



    
gsmInputs.forEach(function(gsmInput,index){

    let gsm =
        parseFloat(gsmInput.value) || 0;

    let rate =
        parseFloat(rateInputs[index].value) || 0;

    let fluteFactor = 1;

    let isFlute =
        index == 1 ||
        index == 3 ||
        index == 5;

    if(isFlute)
    {
        fluteFactor =
            parseFloat(
                fluteFactorInputs[index == 1 ? 0 : index == 3 ? 1 : 2].value
            ) || 1;
    }

    let effectiveLayerGSM =
        gsm * fluteFactor;

    let layerWeightPerBox =
        (weightPerBox * effectiveLayerGSM)
        / effectiveGSM;

    let layerCost =
        layerWeightPerBox * rate;

    totalPaperCostPerBox += layerCost;

});


    let conversionAmount = 0;

    if(conversionType == 'kg')
    {
       conversionAmount =
    weightPerBox * conversionCost;
    }
    else
    {
        conversionAmount =
            (totalPaperCostPerBox * conversionCost) / 100;
    }


    let finalCost =
        totalPaperCostPerBox + conversionAmount;


    let profitAmount =
        (finalCost * profitMargin) / 100;


    let saleWithoutGST =
        finalCost + profitAmount;


    let saleWithGST =
        saleWithoutGST +
        ((saleWithoutGST * gstPercent) / 100);


    let totalSheetRequired =
        qty / boxesPerSheet;


    let totalPaperRequired =
    weightPerBox * qty;


    let totalPaperCost =
        totalPaperCostPerBox * qty;


    document.getElementById('cutting_length_result').innerHTML =
        (cuttingLengthMM / 25.4).toFixed(2) + ' in';

    document.getElementById('deckle_result').innerHTML =
        (actualSheetWidthMM / 25.4).toFixed(2) + ' in';

    document.getElementById('boxes_per_sheet').innerHTML =
        boxesPerSheet;

    document.getElementById('sheet_weight').innerHTML =
        totalWeight.toFixed(3) + ' kg';

   document.getElementById('weight_per_box').innerHTML =
    weightPerBox.toFixed(3) + ' kg';

   document.getElementById('paper_cost_per_box').innerHTML =
    '₹ ' + totalPaperCostPerBox.toFixed(2);

    document.getElementById('total_sheet_required').innerHTML =
        Math.ceil(totalSheetRequired);

    document.getElementById('total_paper_required').innerHTML =
        totalPaperRequired.toFixed(2) + ' kg';

    document.getElementById('total_paper_cost').innerHTML =
        '₹ ' + totalPaperCost.toFixed(2);

    document.getElementById('conversion_cost_result').innerHTML =
        '₹ ' + conversionAmount.toFixed(2);

        document.getElementById('total_cost_per_box').innerHTML =
    '₹ ' + finalCost.toFixed(2);

    document.getElementById('sale_without_gst').innerHTML =
        '₹ ' + saleWithoutGST.toFixed(2);

    document.getElementById('sale_with_gst').innerHTML =
        '₹ ' + saleWithGST.toFixed(2);
}

createLayerInputs();

document.getElementById('ply')
.addEventListener('change', createLayerInputs);

function bindEvents()
{
    let inputs = document.querySelectorAll('input, select');

    inputs.forEach(function(item){

        item.removeEventListener('keyup', calculateBox);

        item.removeEventListener('change', calculateBox);

        item.addEventListener('keyup', calculateBox);

        item.addEventListener('change', calculateBox);
    });
}

</script>

</body>
</html>
