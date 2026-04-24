<h2>Dear {{ $sale_detail->party_name ?? 'Customer' }},</h2>

<p>Please find attached your invoice.</p>

<p>Invoice No: {{ $sale_detail->voucher_no_prefix }}</p>

<p>Thank you for your business.</p>