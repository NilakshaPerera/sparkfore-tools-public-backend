<?php

namespace App\Domain\Services\ServiceApi;

use App\Domain\Exception\SparkforeException;
use Avency\Gitea\Client;
use Log;

class GiteaApiService implements GiteaApiServiceInterface
{
    private $gitUrl;
    private $authToken;

    /**
     * @var Client
     */
    private $client;

    public const OWNER = 'owner';
    public const REPO = 'repo';

    public function __construct($authToken = null)
    {
        $this->gitUrl = config('sparkfore.git_url');
        $this->authToken = config('sparkfore.git_auth_token');
        if ($authToken) {
            $this->authToken = $authToken;
        }

        // Create open client for communication
        $this->createClient();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function createClient()
    {
        $this->client = new Client(
            $this->gitUrl,
            [
                'type' => Client::AUTH_TOKEN,
                'auth' => $this->authToken
            ]
        );
    }

    /**
     * @param $url
     * @param $param
     * @return mixed|string
     */
    private function getParamsFromUrl($url, $param)
    {
        $path = parse_url($url, PHP_URL_PATH);
        $parts = self::cleanParts($path);
        if ($param === self::OWNER) {
            return $parts[0] ?? '';
        } elseif ($param === self::REPO) {
            return $parts[1] ?? '';
        }

        return '';
    }

    /**
     * @param $path
     * @return array
     */
    private static function cleanParts($path)
    {
        $parts = explode('/', $path);
        $cleaned = [];
        foreach ($parts as $part) {
            if (!empty(trim($part))) {
                $cleaned[] = trim($part);
            }
        }
        return $cleaned;
    }


    /**
     * @param $url
     * @param $type
     * @return array|false
     */
    public function versionsAvailable($url, $type)
    {
        $owner = $this->getParamsFromUrl($url, self::OWNER);
        $repo = $this->getParamsFromUrl($url, self::REPO);
        if ($owner && $repo) {
            if ($type == GIT_VERSION_TYPE_BRANCH) {
                return $this->client->repositories()->getBranches($owner, $repo);
            } elseif ($type == GIT_VERSION_TYPE_TAG) {
                return $this->client->repositories()->getTags($owner, $repo);
            }
        }
        return false;
    }

    public function reposAvailable($owner, $pageNumber = 1)
    {
        try {
            return $this->client->organizations()->getRepos($owner, $pageNumber);
        } catch (\Throwable $e) {
            Log::error("Error getting repos: " . $e->getMessage() . " trace: " . $e->getTraceAsString());
            return [];
        }
    }

    public function createRepo($organization, $name)
    {
        try {
            return $this->client->organizations()->createRepo($organization, $name);
        } catch (\Throwable $e) {
            Log::error("Error creating repo: " . $e->getMessage() . " trace: " . $e->getTraceAsString());
            return [];
        }
    }

    public function deleteRepo($organization, $name)
    {
        try {
            return $this->client->repositories()->delete($organization, $name);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getCode() == 404) {
                Log::error("Requested repo is not available. Marking it as deleted: ", [
                    $e->getCode(),
                    $e->getMessage()
                ]);
            }
            return true;
        } catch (\Throwable $e) {
            Log::error("Error deleting repo: " . $e->getMessage() . " trace : " . $e->getTraceAsString());
            throw new SparkforeException("Error deleteing Git repository", 422);
        }
    }

    public function getContent($url, $path, $ref = null)
    {
        $owner = $this->getParamsFromUrl($url, self::OWNER);
        $repo = $this->getParamsFromUrl($url, self::REPO);
        return $this->client->repositories()->getContent($owner, $repo, $path, $ref);
    }

    public function getContents($url, $ref = null)
    {
        $owner = $this->getParamsFromUrl($url, self::OWNER);
        $repo = $this->getParamsFromUrl($url, self::REPO);
        return $this->client->repositories()->getContents($owner, $repo, $ref);
    }

    public function getBranchContents($url, $branch, $itemSha = "")
    {
        $owner = $this->getParamsFromUrl($url, self::OWNER);
        $repo = $this->getParamsFromUrl($url, self::REPO);
        return $this->client->repositories()->getContents($owner, $repo, $branch, true, $itemSha);
    }

    public function migrateRepos($cloneUrl, $repoName, $repoOwner, $authPassword = null, $authUsername = null)
    {
        return $this->client->repositories()->migrate($cloneUrl, $repoName, $repoOwner, $authPassword, $authUsername);
    }

    public function updateRepo($url, $defaultBranch)
    {
        $owner = $this->getParamsFromUrl($url, self::OWNER);
        $repo = $this->getParamsFromUrl($url, self::REPO);
        return $this->client->repositories()->update($owner, $repo, default_branch: $defaultBranch);
    }

    public function createFile($url, $path, $content, $sha, $branch, $isUppdate = false)
    {
        $owner = $this->getParamsFromUrl($url, self::OWNER);
        $repo = $this->getParamsFromUrl($url, self::REPO);

        if ($isUppdate) {
            return $this->client->repositories()->updateContent($owner, $repo, $path, $sha, $content, branch: $branch);
        } else {
            return $this->client->repositories()->addContent($owner, $repo, $path, $content, branch: $branch);
        }
    }

    public function deleteFile($url, $path, $sha, $branch)
    {
        $owner = $this->getParamsFromUrl($url, self::OWNER);
        $repo = $this->getParamsFromUrl($url, self::REPO);

        Log::info("File deleting request from Git", [$url, $path, $branch]);
        return $this->client->repositories()->deleteContent($owner, $repo, $path, $sha, branch: $branch);
    }
}
