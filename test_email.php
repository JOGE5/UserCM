<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Illuminate\Support\Facades\Mail::raw('This is a test email to verify SMTP settings.', function ($message) {
        $message->to(env('MAIL_USERNAME'))
                ->subject('SMTP Verification Test');
    });
    echo "Email sent successfully.\n";
} catch (\Exception $e) {
    echo "Error sending email:\n";
    echo $e->getMessage() . "\n";
}
