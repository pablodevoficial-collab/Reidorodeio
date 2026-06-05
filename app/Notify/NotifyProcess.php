<?php

namespace App\Notify;

use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;

class NotifyProcess {

    /*
    |--------------------------------------------------------------------------
    | Notification Process
    |--------------------------------------------------------------------------
    |
    | This is the core processor to send a notification to receiver. In this
    | class, find the notification template from database and build the final
    | message replacing the short codes and provide this to the method to send
    | the notification. Also notification log and error is creating here.
    |
     */

    /**
     * Template name, which contain the short codes and messages
     *
     * @var string
     */
    public $templateName;

    /**
     * Short Codes, which will be replaced
     *
     * @var array
     */
    public $shortCodes;

    /**
     * Instance of user, who will get the notification
     *
     * @var object
     */
    public $user;

    /**
     * Status field name in database of notification template
     *
     * @var string
     */
    protected $statusField;

    /**
     * Global template field name in database of notification method
     *
     * @var string
     */
    protected $globalTemplate;

    /**
     * Message body field name in database of notification
     *
     * @var string
     */
    protected $body;

    /**
     * Notification template instance
     *
     * @var object
     */
    public $template;

    /**
     * Message, if the email template doesn't exists
     *
     * @var string|null
     */
    public $message;

    /**
     * Notification log will be created or not
     *
     * @var bool
     */
    public $createLog;

    /**
     * Method configuration field name in database
     *
     * @var string
     */
    public $notifyConfig;

    /**
     * Subject of notification
     *
     * @var string
     */
    public $subject;

    /**
     * Name of receiver
     *
     * @var string
     */
    public $receiverName;

    /**
     * The relational field name like user_id, agent_id
     *
     * @var string
     */
    public $userColumn;

    /**
     * Address of receiver, like email, mobile number etc
     *
     * @var string
     */
    protected $toAddress;

    /**
     * Final message of notification
     *
     * @var string
     */
    protected $finalMessage;

    /**
     * Notification sent from
     *
     * @var string
     */
    protected $sentFrom = null;

    /**
     * Get the final message after replacing the short code.
     *
     * Also custom message will be return from here if notification template doesn't exist.
     *
     * @return string
     */
    protected function getMessage() {
        $this->prevConfiguration();

        $body           = $this->body;
        $user           = $this->user;
        $globalTemplate = $this->globalTemplate;

        //finding the notification template
        $template       = NotificationTemplate::where('act', $this->templateName)->where($this->statusField, Status::ENABLE)->first();
        $this->template = $template;

        if (!$this->template && $this->templateName) {
            return false;
        }

        //Getting the notification message from database if use and template exist
        //If not exist, get the message which have sent via method
        if ($user && $template) {
            $templateMessage = $template->$body;
            
            // 1. Replace Template Specific Shortcodes (e.g. {{code}})
            if ($this->shortCodes) {
                $templateMessage = $this->replaceTemplateShortCode($templateMessage);
            }

            // 2. Replace Global Shortcodes in the INNER message (e.g. {{fullname}})
            // This ensures that if the template body uses global variables, they are replaced.
            $templateMessage = $this->replaceGlobalVars($templateMessage, $user->fullname, $user->username);

            // 3. Get Global Template Wrapper
            $globalTpl = gs($globalTemplate);
            
            // 4. If Global Template exists, use it. Otherwise use a default wrapper.
            if (!$globalTpl) {
                $globalTpl = '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Rei do Rodeio Notification</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@800&display=swap" rel="stylesheet">
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: sans-serif;">
    <div style="max-width:600px; margin:0 auto; background:#ffffff; padding:20px; border-radius:8px; margin-top:20px;">
        <div style="text-align: center; margin-bottom: 20px;">
             <h2 style="font-family: \'Orbitron\', \'Arial Black\', sans-serif; color: #f97316; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase;">REI DO RODEIO</h2>
        </div>
        {{message}}
        <div style="margin-top:20px; font-size:12px; color:#666; text-align:center;">
            &copy; ' . date('Y') . ' Rei do Rodeio. Todos os direitos reservados.
        </div>
    </div>
</body>
</html>';
            }

            // Replace global vars in the wrapper
            $message = $this->replaceGlobalVars($globalTpl, $user->fullname, $user->username);
            // Inject the body
            $message = str_replace("{{message}}", $templateMessage, $message);

        } else {
            // For custom messages without a template record
            $message = $this->message;
            if ($this->shortCodes) {
                $message = $this->replaceTemplateShortCode($message);
            }
            // Replace global vars
            $message = $this->replaceGlobalVars($message, $this->receiverName, $this->toAddress);
            
            // Try to wrap with global template if available
            $globalTpl = gs($globalTemplate);
            if (!$globalTpl) {
                 $globalTpl = '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Rei do Rodeio</title>
</head>
<body style="font-family: sans-serif; padding: 20px;">
    {{message}}
</body>
</html>';
            }
            
            $wrapper = $this->replaceGlobalVars($globalTpl, $this->receiverName, $this->toAddress);
            $message = str_replace("{{message}}", $message, $wrapper);
        }

        //set subject to property
        $this->getSubject();

        $this->finalMessage = $message;

        //return the final message
        return $message;
    }

    /**
     * Replace global shortcodes (fullname, username) in any text
     */
    protected function replaceGlobalVars($text, $fullname, $username) {
        if (is_array($username)) {
            $username = implode(',', $username);
        }
        $text = str_replace("{{fullname}}", $fullname, $text);
        $text = str_replace("{{username}}", $username, $text);
        return $text;
    }

    /**
     * Replace the short code of global template (Legacy wrapper for compatibility)
     *
     * @return string
     */
    protected function replaceShortCode($name, $username, $template, $body) {
        // This method is kept for backward compatibility if extended classes use it,
        // but internal logic now uses replaceGlobalVars + manual body injection.
        $message = $this->replaceGlobalVars($template, $name, $username);
        $message = str_replace("{{message}}", $body, $message);
        return $message;
    }

    /**
     * Replace the short code of the template
     *
     * @return string
     */
    protected function replaceTemplateShortCode($content) {
        foreach ($this->shortCodes ?? [] as $code => $value) {
            // Handle both {{code}} and {{ code }} spaces if needed, but standard is {{code}}
            $content = str_replace('{{' . $code . '}}', $value, $content);
            $content = str_replace('{{ ' . $code . ' }}', $value, $content); // extra safety
        }
        return $content;
    }

    /**
     * Set the subject with replaced the short codes
     *
     * @return void
     */
    protected function getSubject() {
        if ($this->template) {
            $subject = $this->template->subject;
            if ($this->shortCodes) {
                foreach ($this->shortCodes as $code => $value) {
                    $subject = str_replace('{{' . $code . '}}', $value, $subject);
                }
            }
            $this->subject = $subject;
        }
    }

    /**
     * Create the notification log
     *
     * @return void
     */
    public function createErrorLog($message) {
        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = 0;
        $adminNotification->title     = $message;
        $adminNotification->click_url = '#';
        $adminNotification->save();
    }

    /**
     * Create the error log
     *
     * @return void
     */
    public function createLog($type) {
        $userColumn = $this->userColumn;
        if ($this->user && $this->createLog) {
            $notifyConfig    = $this->notifyConfig;
            $config          = gs($notifyConfig);
            $notificationLog = new NotificationLog();
            if (@$this->user->id) {
                $notificationLog->$userColumn = $this->user->id;
            }
            $notificationLog->notification_type = $type;
            $notificationLog->sender            = @$config->name ?? 'firebase';
            $notificationLog->sent_from         = $this->sentFrom;
            $notificationLog->sent_to           = $type == 'push' ? 'Firebase Token' : $this->toAddress;
            $notificationLog->subject           = $this->subject;
            $notificationLog->image             = @$this->pushImage ?? null;
            $notificationLog->message           = $type == 'email' ? $this->finalMessage : strip_tags($this->finalMessage);
            $notificationLog->save();
        }
    }

}
