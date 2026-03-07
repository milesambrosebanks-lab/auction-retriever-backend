<?php
namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class CurdController extends Controller
{

    public function __construct()
    {
        View::share('crud', 'crud');
    }

    public function index(Request $request)
    {

    }
        
}
