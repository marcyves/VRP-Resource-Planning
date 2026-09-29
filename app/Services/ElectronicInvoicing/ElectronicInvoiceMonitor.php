<?php

namespace App\Services\ElectronicInvoicing;

use App\Platforms\SuperPdp\SuperPdpConfig;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ElectronicInvoiceMonitor
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function info(string $message, array $context = []): void
    {
        $this->write('info', $message, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function warning(string $message, array $context = []): void
    {
        $this->write('warning', $message, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function error(string $message, array $context = []): void
    {
        $this->write('error', $message, $context);
    }

    /**
     * Log an error and optionally mail ops (no secrets in body).
     *
     * @param  array<string, mixed>  $context
     */
    public function alert(string $subject, string $body, array $context = []): void
    {
        $this->error($subject, $context);

        $email = config('electronic-invoicing.alert_email');
        if (! is_string($email) || $email === '') {
            return;
        }

        try {
            Mail::raw($body, function ($message) use ($email, $subject) {
                $message->to($email)->subject('[VRP e-facture] '.$subject);
            });
        } catch (Throwable $e) {
            report($e);
            $this->write('error', 'e-invoice alert mail failed', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function write(string $level, string $message, array $context): void
    {
        $context['superpdp_env'] = SuperPdpConfig::environment();

        Log::channel('e-invoice')->{$level}($message, $context);
        Log::{$level}($message, $context);
    }
}
