<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class SensitivePageController extends Controller
{
    public function __invoke(): Response
    {
        return response('Sensitive content');
    }
}
