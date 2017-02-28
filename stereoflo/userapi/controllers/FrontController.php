<?php namespace Stereoflo\Userapi\Controllers;

use ApplicationException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Lang;
use Mail;
use RainLab\User\Facades\Auth;
use RainLab\User\Models\Settings as UserSettings;
use RainLab\User\Models\User as UserModel;
use ValidationException;
use Validator;

/**
 * Front Controller
 */
class FrontController extends Controller
{

    public function __construct()
    {
        if (!Schema::hasTable('users')) {
            throw new \Exception(Lang::get('stereoflo.userapi::lang.errors.general_error'));
        }
    }

    public function login()
    {
        if (Auth::check()) {
            return ['error' => Lang::get('stereoflo.userapi::lang.messages.logged')];
        }
        try {
            $data = post();
            $rules = [];

            $rules['login'] = 'required|email|between:6,255';
            $rules['password'] = 'required|between:4,255';

            if (!array_key_exists('login', $data)) {
                $data['login'] = post('username', post('email'));
            }

            $validation = Validator::make($data, $rules);
            if ($validation->fails()) {
                throw new ValidationException($validation);
            }

            $credentials = [
                'login'    => array_get($data, 'login'),
                'password' => array_get($data, 'password')
            ];

            Auth::authenticate($credentials, true);
            return [
                'isLogged' => Auth::check(),
                'user' => Auth::getUser()
            ];
        } catch (\Exception $exception) {
            return ['error' => $exception->getMessage()];
        }
    }

    /**
     * Log out action
     *
     * @return array
     */
    public function logout()
    {
        Auth::logout();
        return [
            'isLogged' => Auth::check()
        ];
    }

    /**
     * reset password action
     * @return mixed
     * @throws ApplicationException
     * @throws ValidationException
     */
    public function resetPassword()
    {
        $rules = [
            'email' => 'required|email|between:6,255'
        ];

        $validation = Validator::make(post(), $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        $user = UserModel::findByEmail(post('email'));
        if (!$user) {
            throw new ApplicationException(trans('rainlab.user::lang.account.invalid_user'));
        }

        $code = implode('!', [$user->id, $user->getResetPasswordCode()]);

        $data = [
            'name' => $user->name,
            'code' => $code
        ];

        return Mail::send('rainlab.user::mail.restore', $data, function ($message) use ($user) {
            $message->to($user->email, $user->full_name);
        });
    }

    /**
     * register action
     *
     * @return array
     */
    public function register()
    {
        try {
            if (!UserSettings::get('allow_registration', true)) {
                throw new ApplicationException(\Lang::get('rainlab.user::lang.account.registration_disabled'));
            }

            $data = post();

            if (!array_key_exists('password_confirmation', $data)) {
                $data['password_confirmation'] = post('password');
            }

            $rules = [
                'email' => 'required|email|between:6,255',
                'password' => 'required|between:4,255|confirmed'
            ];

            if (UserSettings::get('login_attribute', UserSettings::LOGIN_EMAIL) == UserSettings::LOGIN_USERNAME) {
                $rules['username'] = 'required|between:2,255';
            }

            $validation = Validator::make($data, $rules);
            if ($validation->fails()) {
                throw new ValidationException($validation);
            }

            $user = Auth::register($data, true);
            Auth::login($user);

            return ['isLogged' => Auth::check(), 'user' => Auth::getUser()];
        } catch (\Exception $ex) {
            return [
                'error' => $ex->getMessage(),
            ];
        }
    }

    /**
     * @return array
     * @throws ApplicationException
     */
    public function update()
    {
        try {
            $user = Auth::getUser();
            if (!$user) {
                throw new ApplicationException(Lang::get('stereoflo.userapi::lang.errors.user_not_found'));
            }

            $user->fill(post());
            $user->save();
            if (mb_strlen(post('password'))) {
                Auth::login($user->reload(), true);
            }

            return [
                'isLogged' => Auth::check(),
                'user' => Auth::getUser()
            ];
        } catch (\Exception $exception) {
            return [
                'error' => $exception->getMessage()
            ];
        }
    }

    /**
     * Stub for cool hackers
     * @return array
     */
    public function stub()
    {
        return [
            'status' => Lang::get('stereoflo.userapi::lang.messages.api_running')
        ];
    }
}
