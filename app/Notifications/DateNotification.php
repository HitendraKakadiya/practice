<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Benwilkins\FCM\FcmMessage;
use App\User;
use App\storecard;

class DateNotification extends Notification
{
    use Queueable;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['fcm'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toFcm($notifiable)
    {
        \Log::info($notifiable);
        $card_name = $notifiable['cardname'];
        $expDate = $notifiable['expdate'];
        $newDate = date('d F,Y', strtotime($expDate));
        $device_token = $notifiable['user']['device_token'];
        $message = new FcmMessage();
        $message
            ->data([
                'title' => 'Expire Card',
                'body' =>
                    $notifiable['user']['name'] .
                    " Here's Your Card valid till " .
                    $newDate .
                    '. Grab the chance & use your card to get epic discount offers, Team Stocard',
                'device_token' => $device_token,
            ])
            ->priority(FcmMessage::PRIORITY_HIGH);
        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
                //
            ];
    }
}
