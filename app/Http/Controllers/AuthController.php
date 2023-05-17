<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;
 
class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        //middleware for authentication
        $this->middleware('auth:api', ['except' => ['login', 'register','verify']]);
    }
 
 
    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);
 
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()], 400);
        }
 
        // Create New User
        $user = new User;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->verified = false;
        $user->verification_code = Str::random(64);
        $user->save();
        //Send mail to user
        Mail::to($user->email)->send(new VerificationEmail($user));

        return response()->json(['message' => 'Registration successful. Verification email sent.'], 200);
    }
    public function verify($code)
    {
        //find user with verification_code
        $user = User::where('verification_code', $code)->first();
    
        if (!$user) {
            return response()->json(['error' => 'Invalid verification code.'], 404);
        }
        //verified the user
        $user->verified = true;
        $user->verification_code = null;
        $user->save();
    
        return response()->json(['message' => 'User successfully verified.'], 200);
    }
 
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        //Validation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors(),'status'=>422]);
        }

        $credentials = request(['email', 'password']);
        $token = auth('api')->attempt($credentials);
        // check token
        if (! $token) {
            return response()->json(['error' => 'Unauthorized','status'=>401]);
        }

        $user = auth('api')->user();
        if ($user->verified) {
            // User is verified, generate and return the authentication token
        return $this->respondWithToken($token);

        } else {
            // User is not verified, return appropriate response
            auth('api')->logout();
            return response()->json(['error'=>"Your account is not verified.",'status'=>401]);
        }

    }
 
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(): \Illuminate\Http\JsonResponse
    {
        // return user profile
        return response()->json(auth('api')->user());
    }
 
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(): \Illuminate\Http\JsonResponse
    {
        //logout to the user
        auth('api')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
 
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(): \Illuminate\Http\JsonResponse
    {
        // refresh auth token
        return $this->respondWithToken(auth('api')->refresh());
    }
 
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token): \Illuminate\Http\JsonResponse
    {
        //response the user with token
        return response()->json([
            'access_token' => $token,
            'user'=>auth('api')->user(),
            'token_type' => 'bearer',
            //'expires_in' => auth('api')->factory()->getTTL() * 60
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}