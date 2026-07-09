<?php

use App\Models\LoggedHistory;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Get the parent ID for multi-tenancy
 * Returns current user ID for owners/super admins, or parent_id for sub-users
 */
if (! function_exists('parentId')) {
    function parentId()
    {
        if (! Auth::check()) {
            Log::info('parentId: No user logged in');

            return null;
        }

        $user = Auth::user();
        Log::info('parentId: User logged in', ['id' => $user->id, 'roles' => $user->getRoleNames()]);

        if ($user->hasRole('owner') || $user->hasRole('super-admin')) {
            Log::info('parentId: User is owner or super-admin', ['id' => $user->id]);

            return $user->id;
        }

        Log::info('parentId: User is sub-user', ['parent_id' => $user->parent_id]);

        return $user->parent_id;
    }
}

/**
 * Get a setting value or all settings
 *
 * @param  string|null  $key  Setting key to retrieve
 * @param  mixed  $default  Default value if setting not found
 * @return mixed
 */
if (! function_exists('settings')) {
    function settings($key = null, $default = null)
    {
        $pid = parentId();

        if ($pid === null) {
            return $default;
        }

        if ($key === null) {
            // Return all settings as an object
            $settings = Setting::where('parent_id', $pid)->get();
            $data = new \stdClass;

            foreach ($settings as $setting) {
                $data->{$setting->name} = $setting->value;
            }

            return $data;
        }

        // Return specific setting
        $setting = Setting::where('parent_id', $pid)
            ->where('name', $key)
            ->first();

        return $setting ? $setting->value : $default;
    }
}

/**
 * Log user login history
 * Tracks IP, browser, OS, device, and referer information
 */
if (! function_exists('userLoggedHistory')) {
    function userLoggedHistory()
    {
        if (! Auth::check()) {
            return;
        }

        $request = request();
        $user = Auth::user();

        // Get user agent info
        $userAgent = $request->header('User-Agent');

        // Parse browser
        $browser = 'Unknown';
        if (str_contains($userAgent, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($userAgent, 'Chrome')) {
            $browser = 'Chrome';
        } elseif (str_contains($userAgent, 'Safari')) {
            $browser = 'Safari';
        } elseif (str_contains($userAgent, 'Edge')) {
            $browser = 'Edge';
        } elseif (str_contains($userAgent, 'Opera') || str_contains($userAgent, 'OPR')) {
            $browser = 'Opera';
        }

        // Parse OS
        $os = 'Unknown';
        if (str_contains($userAgent, 'Windows')) {
            $os = 'Windows';
        } elseif (str_contains($userAgent, 'Mac')) {
            $os = 'macOS';
        } elseif (str_contains($userAgent, 'Linux')) {
            $os = 'Linux';
        } elseif (str_contains($userAgent, 'Android')) {
            $os = 'Android';
        } elseif (str_contains($userAgent, 'iOS') || str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            $os = 'iOS';
        }

        // Parse device
        $device = 'Desktop';
        if (str_contains($userAgent, 'Mobile') || str_contains($userAgent, 'Android')) {
            $device = 'Mobile';
        } elseif (str_contains($userAgent, 'Tablet') || str_contains($userAgent, 'iPad')) {
            $device = 'Tablet';
        }

        // Create logged history record
        LoggedHistory::create([
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'country' => null, // Can be enhanced with GeoIP service
            'browser' => $browser,
            'os' => $os,
            'device' => $device,
            'referer' => $request->header('Referer'),
        ]);
    }
}

/**
 * Replace shortcodes in email template
 *
 * @param  \App\Models\Notification  $notification  Email template
 * @param  array  $data  Data for shortcode replacement
 * @return array ['subject' => string, 'message' => string]
 */
if (! function_exists('MessageReplace')) {
    function MessageReplace($notification, $data)
    {
        $message = $notification->message;
        $subject = $notification->subject;

        foreach ($data as $key => $value) {
            $message = str_replace('{'.$key.'}', $value, $message);
            $subject = str_replace('{'.$key.'}', $value, $subject);
        }

        return ['subject' => $subject, 'message' => $message];
    }
}

/**
 * Send email using Common mailable
 *
 * @param  string  $to  Recipient email address
 * @param  string  $subject  Email subject
 * @param  string  $message  Email message content
 * @return bool Success status
 */
if (! function_exists('commonEmailSend')) {
    function commonEmailSend($to, $subject, $message)
    {
        try {
            // Enfileira o mailable (QUEUE_CONNECTION=database) para nao bloquear a request
            Mail::to($to)->queue(new \App\Mail\Common($subject, $message));

            return true;
        } catch (\Exception $e) {
            Log::error('Email send failed: '.$e->getMessage());

            return false;
        }
    }
}

/**
 * Send notification email based on module and data
 *
 * @param  string  $module  Notification module (e.g., 'user_create')
 * @param  string  $to  Recipient email
 * @param  array  $data  Data for shortcode replacement
 * @return bool Success status
 */
if (! function_exists('sendNotificationEmail')) {
    function sendNotificationEmail($module, $to, $data)
    {
        $notification = \App\Models\Notification::where('parent_id', parentId())
            ->where('module', $module)
            ->first();

        if (! $notification || ! $notification->enabled_email) {
            return false;
        }

        $email = MessageReplace($notification, $data);

        return commonEmailSend($to, $email['subject'], $email['message']);
    }
}
