<?php

use ptlis\DiffParser\Parser;

test('diff', function () {

});

test('parses', function () {
    $parser = new Parser();

    $diff = <<<EOT
    diff --git a/config/stripe-webhooks.php b/config/stripe-webhooks.php
    index f20acc707..2be3901ca 100644
    --- a/config/stripe-webhooks.php
    +++ b/config/stripe-webhooks.php
    @@ -15,6 +15,7 @@ return [
          * https://stripe.com/docs/api#event_types.
          */
         'jobs' => [
    +        // Test
             'invoice_created' => \App\Cashier\Jobs\HandleInvoiceCreated::class,

             // 'charge_failed' => \App\Jobs\StripeWebhooks\HandleFailedCharge::class,

    EOT;

    $changeset = $parser->parse($diff, Parser::VCS_GIT);

    foreach ($changeset->files as $file) {
        dd((string) $file->hunks[0]);
    }
});
