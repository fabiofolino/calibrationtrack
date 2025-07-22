<?php

namespace App\Console\Commands;

use App\Mail\CalibrationReminderMail;
use App\Models\Gage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CalibrationReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calibration:reminders {--dry-run : Show what emails would be sent without actually sending them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send calibration reminder emails for gages due within 7 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        // Get gages that are due within 7 days
        $dueGages = Gage::with(['department', 'calibrationRecords'])
            ->whereDate('next_due_date', '<=', now()->addDays(7))
            ->whereDate('next_due_date', '>=', now())
            ->get();

        if ($dueGages->isEmpty()) {
            $this->info('No gages are due for calibration within the next 7 days.');
            return;
        }

        // Group gages by department manager email
        $remindersByManager = $dueGages->groupBy(function ($gage) {
            return $gage->department->manager_email;
        })->filter(function ($gages, $email) {
            return !empty($email);
        });

        if ($remindersByManager->isEmpty()) {
            $this->warn('Found ' . $dueGages->count() . ' due gages, but no departments have manager emails configured.');
            return;
        }

        $totalEmails = 0;
        $totalGages = 0;

        foreach ($remindersByManager as $managerEmail => $gages) {
            $department = $gages->first()->department;
            
            if ($isDryRun) {
                $this->info("Would send email to: {$managerEmail}");
                $this->info("Department: {$department->name}");
                $this->info("Gages due: {$gages->count()}");
                foreach ($gages as $gage) {
                    $daysUntilDue = now()->diffInDays($gage->next_due_date, false);
                    $this->line("  - {$gage->name} ({$gage->serial_number}) - Due in {$daysUntilDue} days");
                }
                $this->line('');
            } else {
                try {
                    Mail::to($managerEmail)->queue(new CalibrationReminderMail($department, $gages));
                    $this->info("Sent reminder email to {$managerEmail} for {$gages->count()} gages in {$department->name}");
                } catch (\Exception $e) {
                    $this->error("Failed to send email to {$managerEmail}: " . $e->getMessage());
                    continue;
                }
            }
            
            $totalEmails++;
            $totalGages += $gages->count();
        }

        if ($isDryRun) {
            $this->info("Dry run complete. Would send {$totalEmails} emails for {$totalGages} gages.");
        } else {
            $this->info("Queued {$totalEmails} reminder emails for {$totalGages} gages.");
        }
    }
}
