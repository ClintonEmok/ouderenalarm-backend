<?php

// app/Console/Commands/SendPendingCancellationsEmail.php

namespace App\Console\Commands;

use App\Models\PendingCancellation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendPendingCancellationsEmail extends Command
{
    protected $signature = 'cancellations:send';
    protected $description = 'Verstuur één gecombineerde e-mail met alle pending cancellations';

    public function handle()
    {
        $cancellations = PendingCancellation::all();

        if ($cancellations->isEmpty()) {
            $this->info('Geen opzeggingen te versturen.');
            return;
        }

        $lines = $cancellations->map(function ($c) {
            return "{$c->connection_number} - {$c->customer_name} (opzegging per direct)";
        });

        Mail::raw($lines->implode("\n"), function ($message) {
            $message->to('administratie@alarmmeldnet.nl')
                ->subject('Opzeggingen in bulk');
        });

        PendingCancellation::truncate();
        $this->info('E-mail verzonden en pending cancellations geleegd.');
    }
}