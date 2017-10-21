<?php

namespace Twinleaf\Http\Controllers;

use Twinleaf\Account;

class AccountController extends Controller
{
    /**
     * Replace the specified account with a new one.
     *
     * @param \Twinleaf\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function replace(Account $account)
    {
        return $account->replace();
    }
}
