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
                    <input type="number" step="0.01" id="length" class="form-control" value="20">
                </div>

                <div class="mb-3">
                    <label>Width (inch)</label>
                    <input type="number" step="0.01" id="width" class="form-control" value="10">
                </div>

                <div class="mb-3">
                    <label>Height (inch)</label>
                    <input type="number" step="0.01" id="height" class="form-control" value="10">
                </div>

                <div class="mb-3">
                    <label>Paper GSM</label>
                    <input type="number" step="0.01" id="gsm" class="form-control" value="120">
                </div>

                <div class="mb-3">
                    <label>Paper Rate (₹ / KG)</label>
                    <input type="number" step="0.01" id="paper_rate" class="form-control" value="36">
                </div>

                <div class="mb-3">
                    <label>Ply</label>
                    <select id="ply" class="form-control">
                        <option value="3">3 Ply</option>
                        <option value="5">5 Ply</option>
                        <option value="7">7 Ply</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Flute Factor</label>
                    <input type="number" step="0.01" id="flute_factor" class="form-control" value="1.5">
                </div>

                <div class="mb-3">
                    <label>Joint Allowance (mm)</label>
                    <input type="number" step="0.01" id="joint_allowance" class="form-control" value="35">
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

            <div class="card-box">

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
                            <div class="result-title">Cost Per Box</div>
                            <div class="result-value" id="cost_per_box">0</div>
                        </div>
                    </div>

                </div>


                <div class="row">

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

                    <div class="col-md-4">
                        <div class="result-box">
                            <div class="result-title">Total Paper Cost</div>
                            <div class="result-value" id="total_paper_cost">0</div>
                        </div>
                    </div>

                </div>


                <div class="card-box mt-4">

                    <h4>Formula Used</h4>

                    <p>
                        <strong>Cutting Length:</strong><br>
                        2 × (Length + Width) + Joint Allowance + Cutting Margin
                    </p>

                    <p>
                        <strong>Deckle Width:</strong><br>
                        Height + Width + Deckle Margin
                    </p>

                    <p>
                        <strong>Weight Formula:</strong><br>
                        (CL mm × Deckle mm × Effective GSM) / 1550000000
                    </p>

                </div>

            </div>

        </div>

    </div>

</div>


<script>

function calculateBox()
{

    let length = parseFloat(document.getElementById('length').value) || 0;
    let width = parseFloat(document.getElementById('width').value) || 0;
    let height = parseFloat(document.getElementById('height').value) || 0;

    let gsm = parseFloat(document.getElementById('gsm').value) || 0;
    let paperRate = parseFloat(document.getElementById('paper_rate').value) || 0;

    let ply = parseFloat(document.getElementById('ply').value) || 0;
    let fluteFactor = parseFloat(document.getElementById('flute_factor').value) || 0;

    let jointAllowance = parseFloat(document.getElementById('joint_allowance').value) || 0;
    let cuttingMargin = parseFloat(document.getElementById('cutting_margin').value) || 0;
    let deckleMargin = parseFloat(document.getElementById('deckle_margin').value) || 0;

    let availableWidth = parseFloat(document.getElementById('available_width').value) || 0;

    let qty = parseFloat(document.getElementById('qty').value) || 0;


    // inch to mm

    let L = length * 25.4;
    let W = width * 25.4;
    let H = height * 25.4;


    // cutting length

    let cuttingLengthMM = (2 * (L + W)) + jointAllowance + cuttingMargin;


    // deckle width

    let deckleMM = H + W + deckleMargin;


    // convert back to inch

    let cuttingLengthInch = cuttingLengthMM / 25.4;
    let deckleInch = deckleMM / 25.4;


    // boxes per sheet

    let boxesPerSheet = Math.floor(availableWidth / deckleInch);

    if(boxesPerSheet < 1)
    {
        boxesPerSheet = 1;
    }


    // actual sheet width used

    let actualSheetWidthMM = deckleMM * boxesPerSheet;


    // effective gsm

    let effectiveGSM = gsm * ((ply - 1) + fluteFactor);


    // sheet weight

    let sheetWeight = (cuttingLengthMM * actualSheetWidthMM * effectiveGSM) / 1550000000;


    // box weight

    let boxWeight = sheetWeight / boxesPerSheet;


    // cost per box

    let costPerBox = boxWeight * paperRate;


    // total sheets

    let totalSheetRequired = qty / boxesPerSheet;


    // total paper required

    let totalPaperRequired = totalSheetRequired * sheetWeight;


    // total paper cost

    let totalPaperCost = totalPaperRequired * paperRate;


    // update ui

    document.getElementById('cutting_length_result').innerHTML = cuttingLengthInch.toFixed(2) + ' in';

    document.getElementById('deckle_result').innerHTML = (actualSheetWidthMM / 25.4).toFixed(2) + ' in';

    document.getElementById('boxes_per_sheet').innerHTML = boxesPerSheet;

    document.getElementById('sheet_weight').innerHTML = sheetWeight.toFixed(3) + ' kg';

    document.getElementById('weight_per_box').innerHTML = boxWeight.toFixed(3) + ' kg';

    document.getElementById('cost_per_box').innerHTML = '₹ ' + costPerBox.toFixed(2);

    document.getElementById('total_sheet_required').innerHTML = Math.ceil(totalSheetRequired);

    document.getElementById('total_paper_required').innerHTML = totalPaperRequired.toFixed(2) + ' kg';

    document.getElementById('total_paper_cost').innerHTML = '₹ ' + totalPaperCost.toFixed(2);

}


calculateBox();


let inputs = document.querySelectorAll('input, select');

inputs.forEach(function(item){

    item.addEventListener('keyup', calculateBox);

    item.addEventListener('change', calculateBox);

});

</script>

</body>
</html>
