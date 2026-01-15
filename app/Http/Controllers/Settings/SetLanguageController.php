<?php
namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SetLanguageController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'lang' => [
                'required',
                'string',
                Rule::in(['uz', 'ru', 'en']),
            ],
        ]);

        $user = Auth::user();

        $user->update([
            'lang' => $data['lang'],
        ]);

        app()->setLocale($data['lang']);
        session(['locale' => $data['lang']]);

        return $this->success([
            'lang' => $user->lang,
        ], __('index.settings.edited_language'));
    }
}