<?php

declare(strict_types=1);

namespace App\Http\Responses\Fortify;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return Response
     */
    public function toResponse($request)
    {
        $user = Auth::user();
        // Ensure intended URL is not the impersonation endpoint
        $intended = session()->pull('url.intended', config('fortify.home'));

        if (is_string($intended) && \str_contains($intended, 'impersonate-finalize')) {
            $intended = config('fortify.home');
        }

        // Default behavior: go to dashboard
        return redirect()->to($intended);
    }
}
