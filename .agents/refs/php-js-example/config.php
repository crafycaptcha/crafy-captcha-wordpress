<?php

require __DIR__ . '/../../../vendor/autoload.php';

use Crafy\Captcha\CrafyCAPTCHA;

// ---------------------------------------------------------
// You can get these credentials in your Control Panel:
// https://captcha.crafy.net/panel/
// ---------------------------------------------------------
$global_config = [
    'public_key' => "pk_bbfc613f1512d5ee23a117e6ece636c0",
    'secret_key' => "sk_10def5d6042dd6fbfae48892c7ed46d304f5cb962b5fbb24196f587830ca975a",
    'signing_public_key' => "hnJXAsmGL1Bv4L0/MvOKdDmGbTN+PD6PvOrWksCRm78=",
    'js_cdn_tag' => '<script src="https://cdn.jsdelivr.net/gh/crafycaptcha/crafy-captcha-js@1.1.2/dist/CrafyCAPTCHA.min.js" integrity="sha256-oITevI0giYZbzPHm7URGgKHVyVvJp9zFueoubrYixb4=" crossorigin="anonymous"></script>'
];

// Initialize the CrafyCAPTCHA SDK instance with the credentials
$global_CrafyCAPTCHA = new CrafyCAPTCHA(
    $GLOBALS['global_config']['public_key'],
    $GLOBALS['global_config']['secret_key']
);

?>