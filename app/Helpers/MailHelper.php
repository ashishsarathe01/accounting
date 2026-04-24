<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Config;

class MailHelper
{
    public static function setCompanyMailConfig($company)
    {
        Config::set('mail.default', 'smtp');

        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.host', $company->smtp_host);
        Config::set('mail.mailers.smtp.port', $company->smtp_port);
        Config::set('mail.mailers.smtp.username', $company->smtp_username);
        Config::set('mail.mailers.smtp.password', $company->smtp_password);
        Config::set('mail.mailers.smtp.encryption', $company->smtp_encryption);

        Config::set('mail.from.address', $company->smtp_username);
        Config::set('mail.from.name', $company->smtp_from_name);
    }
}