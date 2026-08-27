<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $firstname;
    public $plan;
    public $plan_amount;
    public $plan_order_id;
    public $plan_payment_id;
    public $plan_expire;

    /**
     * Create a new message instance.
     */
    public function __construct($firstname, $plan, $plan_amount,$plan_order_id,$plan_payment_id,$plan_expire)
    {
        $this->firstname = $firstname;
        $this->plan = $plan;
        $this->plan_amount = $plan_amount;
        $this->plan_order_id = $plan_order_id;
        $this->plan_payment_id = $plan_payment_id;
        $this->plan_expire = $plan_expire;

    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Payment Confirmation - Thank You!')
                    ->view('payment-success')
                    ->with([
                        'name' => $this->firstname,
                        'plan' => $this->plan,
                        'amount' => $this->plan_amount,
                        'transactionId' => $this->plan_order_id,
                        'payment_id' => $this->plan_payment_id,
                        'expire' => $this->plan_expire,
                    ]);
    }
}
