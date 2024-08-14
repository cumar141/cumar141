<?php

namespace Modules\CryptoExchange\Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class EmailTemplateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        EmailTemplate::insert([

            // Exchange [en, ar, fr, pt, ru, es, tr, ch]
            // Notify to admin on money exchange
            [
                'name' => 'Notify Admin on Crypto Exchange',
                'alias' => 'notify-admin-on-crypto-exchange',
                'subject' => 'Crypto Exchange Notification',
                'body' => 'Hi <b>{admin}</b>,
                    <br><br>Amount <b>{amount}</b> has been Crypto Exchange by <b>{user}</br>
                    <br><br><b><u><i>Here’s a brief overview of the Deposit:</i></u></b>
                    <br><br><b><u>Crypto Exchange at:</u></b> {created_at}
                    <br><br><b><u>Crypto Exchange via:</u></b> {payment_method}
                    <br><br><b><u>Transaction ID:</u></b> {uuid}
                    <br><br><b><u>Currency:</u></b> {code}
                    <br><br><b><u>Amount:</u></b> {amount}
                    <br><br><b><u>Fee:</u></b> {fee}
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'Crypto Exchange',
                'status' => 'Active',
            ],
            ['name' => 'Notify Admin on Crypto Exchange', 'alias' => 'notify-admin-on-crypto-exchange', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify Admin on Crypto Exchange', 'alias' => 'notify-admin-on-crypto-exchange', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify Admin on Crypto Exchange', 'alias' => 'notify-admin-on-crypto-exchange', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify Admin on Crypto Exchange', 'alias' => 'notify-admin-on-crypto-exchange', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify Admin on Crypto Exchange', 'alias' => 'notify-admin-on-crypto-exchange', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify Admin on Crypto Exchange', 'alias' => 'notify-admin-on-crypto-exchange', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'Crypto Exchange', 'status' => 'Active'],
            ['name' => 'Notify Admin on Crypto Exchange', 'alias' => 'notify-admin-on-crypto-exchange', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'Crypto Exchange', 'status' => 'Active'],
        ]);
    }
}
