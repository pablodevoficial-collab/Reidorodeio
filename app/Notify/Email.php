<?php

namespace App\Notify;

use App\Notify\Notifiable;
use App\Notify\NotifyProcess;
use Illuminate\Support\Facades\Mail;

class Email extends NotifyProcess implements Notifiable {

    /**
     * Throw delivery exceptions back to caller instead of swallowing them.
     *
     * @var bool
     */
    public $throwExceptions = false;

    /**
     * Email of receiver
     *
     * @var string
     */
    public $email;

    /**
     * Assign value to properties
     *
     * @return void
     */
    public function __construct() {
        $this->statusField    = 'email_status';
        $this->body           = 'email_body';
        $this->globalTemplate = 'email_template';
        $this->notifyConfig   = 'mail_config';
    }

    /**
     * Send notification
     *
     * @return void|bool
     */
    public function send() {
        if (!gs('en')) { // 'en' = Email Notification enable/disable
            if ($this->throwExceptions) {
                throw new \RuntimeException('O envio global de e-mails está desativado.');
            }
            return false;
        }

        // Get message from parent (NotifyProcess) which handles templates
        $message = $this->getMessage();

        if ($message && $this->email) {
            try {
                // Configure Mailer dynamically based on DB settings
                $this->configureMailer();

                // Log the config to be sure
                \Log::info('Attempting to send email via SMTP', [
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'username' => config('mail.mailers.smtp.username'),
                    'from' => config('mail.from.address'),
                    'to' => $this->email
                ]);

                $subject = $this->subject;
                $fromEmail = gs('email_from');
                $fromName = gs('site_name');

                Mail::html($this->finalMessage, function($msg) use ($subject, $fromEmail, $fromName) {
                    $msg->to($this->email, $this->receiverName)
                        ->subject($subject);
                    
                    if ($fromEmail) {
                        $msg->from($fromEmail, $fromName);
                    }
                });

                $this->createLog('email');
                return true;

            } catch (\Exception $e) {
                $this->createErrorLog($e->getMessage());
                session()->flash('mail_error', $e->getMessage());
                if ($this->throwExceptions) {
                    throw $e;
                }
            }
        }

        if ($this->throwExceptions) {
            throw new \RuntimeException('Não foi possível montar o e-mail para envio.');
        }

        return false;
    }

    /**
     * Configure some properties
     *
     * @return void
     */
    public function prevConfiguration() {
        if ($this->user) {
            $this->email        = $this->user->email;
            $this->receiverName = $this->user->fullname;
        }
        $this->toAddress = $this->email;
    }

    protected function configureMailer()
    {
        $config = gs('mail_config');
        if (!$config) return;

        // Support only SMTP for now via dynamic config, as it's the most common.
        if ($config->name == 'smtp') {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $config->host,
                'mail.mailers.smtp.port' => $config->port,
                'mail.mailers.smtp.encryption' => $config->enc,
                'mail.mailers.smtp.username' => $config->username,
                'mail.mailers.smtp.password' => $config->password,
                'mail.from.address' => gs('email_from'),
                'mail.from.name' => gs('site_name'),
            ]);
        }
    }
}
