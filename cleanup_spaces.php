<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$count = \App\Models\RentalSpace::count();
echo "Total rental spaces: $count\n";

if ($count > 15) {
    $spaces = \App\Models\RentalSpace::where('id', '>', 15)->get();
    echo "Deleting " . $spaces->count() . " spaces...\n";
    foreach ($spaces as $space) {
        $space->delete();
    }
    echo "Deleted successfully!\n";
    echo "Remaining: " . \App\Models\RentalSpace::count() . " spaces\n";
} else {
    echo "Already have 15 or fewer spaces.\n";
}
