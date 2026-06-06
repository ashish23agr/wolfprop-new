<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\RegisterRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\VerifyUserMail;
use Illuminate\Support\Str;
use App\Models\VerifyUser;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RegisterController extends Controller implements HasMiddleware
{
    /**
     * Laravel 11/12 way of attaching middleware to a controller.
     * The old `$this->middleware('guest:user')` inside __construct()
     * NO LONGER WORKS — the middleware() method was removed from the
     * base controller. Use this static method instead.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('guest:user'),
        ];
    }

    public function index(Request $request)
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = new User;
            $user->first_name   = $request->first_name;
            $user->last_name    = $request->last_name;
            $user->email        = $request->email;
            $user->username     = $request->username;
            $user->password     = bcrypt($request->password);
            $user->company_name = $request->company_name;
            $user->phone        = $request->phone;
            $user->address      = $request->address;
            $user->ip_address   = $request->ip();

            if ($request->has('is_notifiable')) {
                $user->is_notifiable = 1;
            }
            $user->is_active   = 0;
            $user->is_verified = 0;

            if ($user->save()) {
                $user->roles()->attach(3);
                $token = Str::uuid()->toString();
                $verifyUser = VerifyUser::updateOrCreate([
                    'user_id' => $user->id,
                    'token'   => $token,
                ]);
                Mail::to($user->email)->send(new VerifyUserMail($user, $token));
                DB::commit();

                return response()->json([
                    'status'  => 'success',
                    'message' => __('frontend.REGISTERED'),
                ], 200);
            }

            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => __('frontend.OOPS'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => __('frontend.OOPS'),
            ], 200);
        }
    }
}