<!DOCTYPE html>
<html>
<head>
   <title>Account Ledger PDF</title>
   <style>
   body {
      font-family: Arial, sans-serif;
      font-size: 11px;
      margin: 0;
      padding: 20px;
      background-color: #fff;
   }

   .container {
      border: 1px solid #000;
      padding: 15px;
      width: 100%;
      box-sizing: border-box;
   }

   h2, h3, p {
      margin: 2px 0;
   }

   h3 {
      font-size: 14px;
      font-weight: bold;
   }

   h2 {
      font-size: 13px;
      font-weight: bold;
      text-decoration: underline;
      color: #b58900; /* dark yellow */
   }

   p {
      font-size: 11px;
   }

   .address {
      width: 60%;
      margin: 0 auto;
      text-align: center;
   }

   .center {
      text-align: center;
   }

   .right {
      text-align: right;
   }

   table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      font-size: 11px;
   }

   th, td {
      border: 1px solid #000;
      padding: 6px;
      text-align: left;
   }

   thead th {
      text-align: center;
      font-weight: bold;
      background-color: #f2f2f2;
   }

   td {
      vertical-align: top;
   }

   td:nth-child(5), td:nth-child(6), td:nth-child(7) {
      text-align: right;
   }

   tr:nth-child(even) {
      background-color: #fcfcfc;
   }

   tr:hover {
      background-color: #f5f5f5;
   }
   </style>
</head>
<body>
<div class="container">
<!-- <div class="bil_logo">
        @if($configuration && $configuration->company_logo_status==1 && !empty($configuration->company_logo))
            <img src="{{ URL::asset('public/images')}}/{{$configuration->company_logo}}" alt="My Logo">
        @endif
    </div> -->

   <h3 class="center">{{ $comp->company_name }}</h3>
   <p class="address">{{ $comp->address }}</p>
   <p class="center" style="font-weight:bold;">Account Ledger</p>
   <p class="center">(From: {{ \Carbon\Carbon::parse($from_date)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($to_date)->format('d/m/Y') }})</p>
   <h2 class="center">Account: {{ $accounts->account_name }}</h2>
  <p class="right">
    Opening Bal. : {{ formatIndianNumber(abs((float) str_replace(',', '', $opening))) }} 
    @if($opening < 0) CR @else DR @endif
</p>

      

   <table>
      <thead>
         <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Voucher No</th>
            <th>Account</th>
            <th>Debit</th>
            <th>Credit</th>
            <th>Balance</th>
         </tr>
      </thead>
      <tbody>
          @php $totalDebit = 0; @endphp
          @php $totalCredit = 0; @endphp

         @php $balance = (float)str_replace(',', '', $opening); @endphp
         @foreach ($ledger as $entry)
            <tr>
               <td>{{ \Carbon\Carbon::parse($entry->txn_date)->format('d-m-Y') }}</td>
               <td class="w-min-120 ">
                              <?php 
                              if($entry->entry_type==1){
                                 echo "SupO";
                              }else if($entry->entry_type==2){
                                 echo "SupI";
                              }else if($entry->entry_type==3){
                                 echo "SRet./Cr Note";
                              }else if($entry->entry_type==4){
                                 echo "Pur. Ret.";
                              }else if($entry->entry_type==5){
                                 echo "Payt.";
                              }else if($entry->entry_type==6){
                                 echo "Receipt";
                              }else if($entry->entry_type==7){
                                 echo "Journal";
                              }else if($entry->entry_type==8){
                                 echo "Contra";
                              }else if($entry->entry_type==9){
                                 echo "SRet./Cr Note";
                              }else if($entry->entry_type==10){
                                 echo "SRet./Cr Note";
                              }
                              ?>
                           </td>
               <td>{{ $entry->bill_no }}</td>
               <td>{{ $entry->account }}</td>
                @php
                 // Clean and convert to float if needed
                $debit = (float) str_replace(',', '', $entry->debit);
                $totalDebit += $debit;
               @endphp
               
               <td>{{ formatIndianNumber((float) str_replace(',', '', $entry->debit)) }} </td>
               
                @php
                   // Clean and convert to float if needed
                    $Credit = (float) str_replace(',', '', $entry->credit);
                   $totalCredit += $Credit;
                   @endphp
                   
               <td>{{ formatIndianNumber((float) str_replace(',', '', $entry->credit)) }}</td>
               
               @php $balance += $entry->debit - $entry->credit; @endphp
               <td>{{ formatIndianNumber(abs((float) str_replace(',', '', $balance))) }} {{ $balance < 0 ? 'Cr' : 'Dr' }}</td>
            </tr>
         @endforeach
         <tr>
             <td></td>
             <td></td>
             <td></td>
             <td></td>
             <td><strong>{{ formatIndianNumber($totalDebit, 2) }}</strong></td>
             <td><strong>{{ formatIndianNumber($totalCredit, 2) }}</strong></td>
             <td></td>
         </tr>
      </tbody>
     
   </table>
   @foreach ($ledger as $entry)
   <p style="text-align:right;">@if ($loop->last)
      Closing Balance: {{ formatIndianNumber(abs((float) str_replace(',', '', $balance))) }} {{ $balance < 0 ? 'Cr' : 'Dr' }}
    @endif<p>
    @endforeach
</div>
</body>
</html>

