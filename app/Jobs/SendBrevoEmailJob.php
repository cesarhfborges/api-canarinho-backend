<?php

namespace App\Jobs;

use App\Services\BrevoEmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBrevoEmailJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected Mailable $mailable;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Mailable $mailable)
    {
        $this->mailable = $mailable;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(BrevoEmailService $emailService): void
    {
        $emailService->send($this->mailable);
    }
}
