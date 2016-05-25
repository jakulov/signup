<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 15:05
 */

return ['routes' => [
    '/' => [
        'controller' => \jakulov\SignUp\Controller\ProfileController::class,
        'action' => 'show',
    ],
    '/sign/in' => [
        'controller' => \jakulov\SignUp\Controller\SignInController::class,
        'action' => 'signIn',
    ],
    '/sign/in/forgot' => [
        'controller' => \jakulov\SignUp\Controller\SignInController::class,
        'action' => 'forgot',
    ],
    '/sign/in/reset' => [
        'controller' => \jakulov\SignUp\Controller\SignInController::class,
        'action' => 'reset',
    ],
    '/sign/out' => [
        'controller' => \jakulov\SignUp\Controller\SignInController::class,
        'action' => 'signOut',
    ],
    '/sign/up' => [
        'controller' => \jakulov\SignUp\Controller\SignUpController::class,
        'action' => 'signUp',
    ],
    '/sign/up/validateEmail' => [
        'controller' => \jakulov\SignUp\Controller\SignUpController::class,
        'action' => 'validateEmail',
    ],
    '/image/upload' => [
        'action' => 'upload',
        'controller' => \jakulov\SignUp\Controller\ImageController::class,
    ],
    '/error' => [
        'controller' => \jakulov\SignUp\Controller\ErrorController::class,
        'action' => 'error',
    ],
]];