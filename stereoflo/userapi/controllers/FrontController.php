<?php
namespace Stereoflo\Userapi\Controllers;

use Lang;
use Mail;
use Schema;
use Validator;
use ValidationException;
use ApplicationException;
use RainLab\User\Models\User as UserModel;
use RainLab\User\Facades\Auth;
use RainLab\User\Models\Settings as UserSettings;
use Illuminate\Routing\Controller;

/**
 * Front Controller
 */
class FrontController extends Controller
{

    /**
     * @var bool
     */
    private $hasError = false;
    /**
     * @var string
     */
    private $message = '';

    /**
     * FrontController constructor.
     */
    public function __construct()
    {
        try {
            if (Schema::hasTable('users')) {
                $this->message = Lang::get('stereoflo.userapi::lang.messages.api_running');
            }
        } catch (\Exception $exception) {
            $this->hasError = true;
            $this->message = Lang::get('stereoflo.userapi::lang.messages.api_fail');
        }

    }

    /**
     * @return array
     */
    public function login()
    {
        if (Auth::check()) {
            return $this->returnMessage(Lang::get('stereoflo.userapi::lang.messages.logged'), true);
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
            return $this->returnMessage([
                'isLogged' => Auth::check(),
                'user' => Auth::getUser()
            ]);
        } catch (\Exception $exception) {
            return $this->returnMessage($exception->getMessage(), true);
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
        return $this->returnMessage([
            'isLogged' => Auth::check()
        ]);
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

        $sended = (bool)Mail::send('rainlab.user::mail.restore', $data, function ($message) use ($user) {
            $message->to($user->email, $user->full_name);
        });

        return $this->returnMessage([
            'isSended' => $sended
        ], $sended);
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

            return $this->returnMessage([
                'isLogged' => Auth::check(),
                'user' => Auth::getUser()
            ]);
        } catch (\Exception $ex) {
            return $this->returnMessage($ex->getMessage(), true);
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

            return $this->returnMessage([
                'isLogged' => Auth::check(),
                'user' => Auth::getUser()
            ]);
        } catch (\Exception $exception) {
            return $this->returnMessage($exception->getMessage(), true);
        }
    }

    /**
     * Stub for cool hackers
     * @return array
     */
    public function stub()
    {
        return $this->returnMessage($this->message, $this->hasError);
    }

    /**
     * @param mixed $message
     * @param bool $isError
     * @return array
     */
    private function returnMessage($message, $isError = false)
    {
        if (is_array($message)) {
            return [
                'error' => $isError,
                'message' => null
            ] + $message;
        }
        return [
            'error' => $isError,
            'message' => $message,
        ];
    }
}
