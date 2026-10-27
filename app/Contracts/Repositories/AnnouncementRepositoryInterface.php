<?php

namespace App\Contracts\Repositories;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

interface AnnouncementRepositoryInterface
{
    public function queryForUser(User $user): Builder;

    public function find(int $id): ?Announcement;

    /**
     * @param  array{author_id: int, title: string, body: string, audience: string, attachment_path?: string|null, attachment_name?: string|null}  $attributes
     * @param  list<int>  $recipientIds
     */
    public function create(array $attributes, array $recipientIds = []): Announcement;

    /**
     * @param  array{title: string, body: string, audience?: string, attachment_path?: string|null, attachment_name?: string|null}  $attributes
     * @param  list<int>  $recipientIds
     */
    public function update(Announcement $announcement, array $attributes, array $recipientIds = []): Announcement;

    public function delete(Announcement $announcement): void;
}
