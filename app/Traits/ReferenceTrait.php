<?php

namespace App\Traits;

trait ReferenceTrait
{
    public function reference(): string
    {
        return date('YmdHis').rand(10, 99999999);
    }

    public function appointmentReference(): string
    {
        return 'CLFY-'.$this->reference();
    }

    public function transactionReference(): string
    {
        return 'CLFY-TRANS-'.$this->reference();
    }
}
