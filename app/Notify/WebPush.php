<?php

namespace App\Notify;

use App\Notify\NotifyProcess;
use App\Models\PushSubscription;
use Minishlink\WebPush\WebPush as MinishlinkWebPush;
use Minishlink\WebPush\Subscription;

class WebPush extends NotifyProcess
{
    /**
     * Assign value to properties
     *
     * @return void
     */
    public function __construct()
    {
        $this->statusField    = 'push_status';
        $this->body           = 'push_body';
        $this->globalTemplate = 'push_template';
        $this->notifyConfig   = 'firebase_config'; // Reuse config key or create new one if needed, but we use env for VAPID
    }

    /**
     * Send notification
     *
     * @return void|bool
     */
    public function send()
    {
        // Check if push notifications are enabled globally (using same setting as before)
        if (!gs('pn')) {
            return false;
        }

        $message = $this->getMessage();
        if (!$message) {
            return false;
        }

        $title = $this->getTitle();
        $icon = siteFavicon();
        $clickAction = $this->shortCodes['url'] ?? url('/');

        $payload = [
            'title' => $title,
            'body' => $message,
            'icon' => $icon,
            'data' => [
                'url' => $clickAction
            ]
        ];

        // If specific image provided
        if ($this->pushImage) {
            $payload['image'] = asset(getFilePath('push')) . '/' . $this->pushImage;
        }

        try {
            $vapid = gs('vapid_config');
            if (!$vapid || empty($vapid->public_key) || empty($vapid->private_key)) {
                // Fallback to env or fail
                $publicKey = env('VAPID_PUBLIC_KEY');
                $privateKey = env('VAPID_PRIVATE_KEY');
                if (!$publicKey || !$privateKey) {
                    throw new \Exception('VAPID keys not configured');
                }
                $auth = [
                    'VAPID' => [
                        'subject' => url('/'),
                        'publicKey' => $publicKey,
                        'privateKey' => $privateKey,
                    ],
                ];
            } else {
                 $auth = [
                    'VAPID' => [
                        'subject' => $vapid->subject ?? url('/'),
                        'publicKey' => $vapid->public_key,
                        'privateKey' => $vapid->private_key,
                    ],
                ];
            }

            $webPush = new MinishlinkWebPush($auth);

            // Get user subscriptions
            $subscriptions = $this->user->pushSubscriptions()->active()->get();

            foreach ($subscriptions as $sub) {
                $subscription = Subscription::create($sub->toWebPushFormat());
                
                $webPush->queueNotification(
                    $subscription,
                    json_encode($payload)
                );
            }

            // Send all queued notifications
            foreach ($webPush->flush() as $report) {
                $endpoint = $report->getRequest()->getUri()->__toString();

                if ($report->isSuccess()) {
                    // Success
                    $sub = PushSubscription::where('endpoint', $endpoint)->first();
                    if ($sub) $sub->markAsUsed();
                } else {
                    // Failed
                    if ($report->isSubscriptionExpired()) {
                        $sub = PushSubscription::where('endpoint', $endpoint)->first();
                        if ($sub) $sub->delete(); // Or deactivate
                    }
                }
            }
            
            $this->createLog('push');

        } catch (\Exception $e) {
            $this->createErrorLog($e->getMessage());
        }
    }

    /**
     * Configure some properties
     *
     * @return void
     */
    public function prevConfiguration()
    {
        // Not strictly needed as we fetch subs directly in send()
    }

    private function getTitle()
    {
        return $this->replaceTemplateShortCode($this->template->push_title ?? gs('push_title'));
    }
}
