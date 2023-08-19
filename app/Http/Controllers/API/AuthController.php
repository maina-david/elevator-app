<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{

    /**
     * This function registers a new User with required fields and returns a JSON response indicating
     * success or failure.
     *
     * @param Request $request An instance of the Illuminate\Http\Request class that contains the data sent
     * in the HTTP request.
     *
     * @return JsonResponse This function returns a JSON response with either a success
     * message and the newly created User data or an error message if the User could not be
     * created.
     */
    public function registerUser(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password
        ]);

        if ($user) {
            $token = $user->createToken('MyApp')->accessToken;

            return $this->success('User registered successfully!', [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]);
        } else {
            return $this->error('Error registering User!', null, 500);
        }
    }

    /**
     * This function authenticates a User by validating their email and password, and returns a JSON
     * response with the User's information and access token if successful, or an error message if
     * authentication fails.
     *
     * @param Request $request request An instance of the Illuminate\Http\Request class, which contains the HTTP
     * request information.
     *
     * @return JsonResponse This function returns a JSON response with either a success
     * message containing the authenticated User's data and access token, or an error message
     * indicating that authentication failed.
     */
    public function authenticateUser(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (auth()->attempt(['email' => $request->email, 'password' => $request->password])) {

            /** @var \App\Models\User $user **/

            $user = auth()->user();

            $token = $user->createToken('MyApp')->accessToken;

            return $this->success('User authenticated successfully!', [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]);
        }

        return $this->error('Authentication Failed!', null, 401);
    }

}