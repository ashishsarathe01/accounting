<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use App\Models\Companies;

class SaleInvoiceMail extends Mailable
{
    public $pdfContent;
    public $sale_detail;

    public function __construct($pdfContent, $sale_detail)
    {
        $this->pdfContent = $pdfContent;
        $this->sale_detail = $sale_detail;
    }

    public function build()
    {
        $company = Companies::find($this->sale_detail->company_id);

    if (!$company) {
        throw new \Exception('Company not found for this sale.');
    }

    return $this->from($company->smtp_username, $company->smtp_from_name)
        ->subject('Invoice - ' . $this->sale_detail->voucher_no)
        ->view('emails.sale_invoice')
        ->attachData(
            $this->pdfContent,
            'SaleInvoice-' . $this->sale_detail->voucher_no . '.pdf',
            ['mime' => 'application/pdf']
        );
    }
}