<?php

namespace App\Domain\Services\ServiceApi;

interface GiteaApiServiceInterface
{
    public function versionsAvailable($url, $type);

    public function migrateRepos($cloneUrl, $repoName, $repoOwner, $authPassword = null, $authUsername = null);

    public function updateRepo($url, $defaultBranch);

    public function getContents($url, $ref = null);

    public function getBranchContents($url, $branch, $itemSha = "");

    public function getContent($url, $path, $ref = null);

    public function createRepo($organization, $name);

    public function deleteRepo($organization, $name);

    public function reposAvailable($owner, $page);

    public function createFile($url, $path, $content, $sha, $branch, $isUpdate);

    public function deleteFile($url, $path, $sha, $branch);
}
