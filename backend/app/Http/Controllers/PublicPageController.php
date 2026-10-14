<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteAccountWebRequest;
use App\Services\AccountDeletionService;
use App\Services\PinAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Public pages that app stores require: the privacy policy and a web page
 * where people can delete their account without the app installed.
 */
class PublicPageController extends Controller
{
    public function privacy(): View
    {
        return view('public.privacy');
    }

    public function deleteAccountForm(): View
    {
        return view('public.delete-account');
    }

    public function deleteAccount(DeleteAccountWebRequest $request, PinAuthenticator $pins, AccountDeletionService $accounts): RedirectResponse
    {
        $data = $request->validated();
        $user = $pins->verify($data['phone'], $data['pin']);
        $accounts->delete($user, 'phone');

        return redirect()->route('account.delete', app()->getLocale() === 'en' ? ['lang' => 'en'] : [])->with('deleted', true);
    }
}
