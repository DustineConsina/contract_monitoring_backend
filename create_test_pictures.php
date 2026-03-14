<?php
// Create minimal JPEG files for testing

$pictureNames = [
    'tenant-7-1773500213-2gmSYGw2.jpg',
    'tenant-8-1773500165-XngcaL1S.jpg',
    'tenant-9-1773500122-8VvRWB4f.jpg',
    'tenant-10-1773500085-Une8oAxT.jpg',
    'tenant-11-1773506673-OhGTVxEy.jpg'
];

// Minimal valid JPEG (1x1 pixel)
$jpegData = base64_decode(
    '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0a' .
    'HBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIy' .
    'MjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIA' .
    'AhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAn/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8VAFQEB' .
    'AQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwAA8A' .
    '/9k='
);

$dir = 'storage/app/public/profile-pictures/';
foreach ($pictureNames as $pic) {
    file_put_contents($dir . $pic, $jpegData);
    echo "Created test file: {$pic}\n";
}

echo "✓ Test profile pictures created\n";
