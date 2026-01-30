<?php

namespace App\Domain\Services\ServiceApi;

use App\Domain\DataClasses\Moodle\MoodleUpdateUserDto;
use App\Domain\Exception\SparkforeException;
use Cache;
use Log;

class MoodleApiService implements MoodleApiServiceInterface
{
    private $baseUrl;
    private $apiPassword = "ehuaaNJGYE6_U.2";
    private $authToken;
    private $apiUsername = "webservice@sparkfore.com";



    public function __construct($baseUrl, $apiPassword = null, $apiUsername = null)
    {
        $this->baseUrl = $baseUrl;
        if ($apiPassword) {
            $this->password = $apiPassword;
        }

        if ($apiUsername) {
            $this->apiUsername = $apiUsername;
        }
    }

    private function setTokenForService($retry = 0, $useCacheToken=true)
    {
        try {
            if ($useCacheToken && $this->isTokenCached()) {
                $this->authToken = Cache::get("moodle_api_token");
                Log::debug("Authentication using cache in Moodle API. Token: " . $this->authToken);
            } else {
                Log::debug("Token not found in cache, requesting new token from Moodle API.");
                $this->requestNewToken($retry);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching token: ' . $e->getMessage());
            throw new SparkforeException('Error fetching token.', 500);
        }
    }

    private function isTokenCached()
    {
        return Cache::has("moodle_api_token") && Cache::get("moodle_api_token");
    }

    private function requestNewToken($retry)
    {
        $attempts = 0;
        do {
            $response = \Http::get("{$this->baseUrl}/login/token.php", [
                'username' => $this->apiUsername,
                'password' => $this->apiPassword,
                'service' => "core_tooling_api",
            ]);

            if ($response->successful()) {
                $this->handleSuccessfulResponse($response);
                return;
            } else {
                $this->handleFailedResponse($response, $attempts, $retry);
            }
            $attempts++;
        } while ($attempts <= $retry);
    }

    private function handleSuccessfulResponse($response)
    {
        $data = $response->json();
        Log::info('Token response received: ', [$data]);

        if (isset($data['token'])) {
            $this->authToken = $data['token'];
            Cache::put("moodle_api_token", $this->authToken, now()->addMinutes(60));
            Log::debug("New token cached: " . $this->authToken);
        } else {
            Log::error('Token not found in response: ', [$data]);
            throw new SparkforeException('Token not found in response.', 500);
        }
    }

    private function handleFailedResponse($response, $attempts, $retry)
    {
        Log::error('Failed to retrieve token. HTTP status: ' . $response->status() . '. Response: ' . $response->body());
        if ($attempts < $retry) {
            Log::info("Retrying token request in 15 seconds. Attempt: " . ($attempts + 1));
            sleep(15);
        } else {
            throw new SparkforeException('Failed to retrieve token.', 500);
        }
    }

    /**
     * Update a user in Moodle.
     *
     * This function sends a request to the Moodle API to update a user's information.
     *
     * @param MoodleUpdateUserDto $user An object representing the user to be updated.
     * @throws SparkforeException If there is an error updating the user.
     */
    public function coreUserUpdateUser(MoodleUpdateUserDto $user)
    {
        Log::info("Updating users in Moodle");
        $this->setTokenForService(4, false);

        $userDtos = [
            [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'password' => $user->getPassword(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'email' => $user->getEmail(),
            ]
        ];

        $response = \Http::asForm()->post("{$this->baseUrl}/webservice/rest/server.php", [
            'wstoken' => $this->authToken,
            'wsfunction' => 'core_user_update_users',
            'moodlewsrestformat' => 'json',
            'users' => $userDtos,
        ]);

        Log::info('Update users response: ', [$response->json()]);

        if ($response->status() === 200) {
            $data = $response->json();
            if (isset($data['warnings']) && empty($data['warnings'])) {
                Log::info('Users updated successfully: ', $data);
            } else {
                Log::error('Warnings in response: ', $data);
                throw new SparkforeException('Warnings found in response.');
            }
        } else {
            Log::error('Failed to update users: ', $response->json());
            throw new SparkforeException('Failed to update users.');
        }
    }

}
