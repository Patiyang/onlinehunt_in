<?php

namespace App\Models;

class ReactionMobileModel extends BaseModel
{
    protected $table = 'user_reactions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'post_id',
        'user_id',
        'reaction'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Add or update a reaction.
     */
    public function react(int $postId, int $userId, string $reaction): bool
    {
        $db = db_connect();
        $db->transStart();

        $existing = $this->where([
            'post_id' => $postId,
            'user_id' => $userId
        ])->first();

        // User already has this reaction
        if ($existing && $existing['reaction'] === $reaction) {
            $db->transComplete();
            return true;
        }

        // User changing reaction
        if ($existing) {
            $this->decrementReaction($postId, $existing['reaction']);

            $this->update($existing['id'], [
                'reaction' => $reaction
            ]);
        } else {
            // New reaction
            $this->insert([
                'post_id' => $postId,
                'user_id' => $userId,
                'reaction' => $reaction
            ]);
        }

        $this->incrementReaction($postId, $reaction);

        $db->transComplete();

        return $db->transStatus();
    }

    /**
     * Remove a reaction.
     */
    public function removeReaction(int $postId, int $userId): bool
    {
        $db = db_connect();
        $db->transStart();

        $existing = $this->where([
            'post_id' => $postId,
            'user_id' => $userId
        ])->first();

        if (!$existing) {
            return true;
        }

        $this->decrementReaction($postId, $existing['reaction']);

        $this->delete($existing['id']);

        $db->transComplete();

        return $db->transStatus();
    }

    /**
     * Get a user's reaction.
     */
    public function getUserReaction(int $postId, int $userId): ?string
    {
        $row = $this->select('reaction')
            ->where([
                'post_id' => $postId,
                'user_id' => $userId
            ])
            ->first();

        return $row['reaction'] ?? null;
    }

    /**
     * Get reaction totals.
     */
    public function getReactionSummary(int $postId): array
    {
        $rows = db_connect()
            ->table('reactions')
            ->where('post_id', $postId)
            ->get()
            ->getResultArray();

        $summary = [
            'total' => 0,
            'reactions' => []
        ];

        foreach ($rows as $row) {
            $summary['reactions'][$row['reaction']] = (int)$row['total'];
            $summary['total'] += (int)$row['total'];
        }

        return $summary;
    }

    /**
     * Increase aggregate total.
     */
    protected function incrementReaction(int $postId, string $reaction): void
    {
        $table = db_connect()->table('reactions');

        $row = $table
            ->where([
                'post_id' => $postId,
                'reaction' => $reaction
            ])
            ->get()
            ->getRowArray();

        if ($row) {
            $table
                ->where('id', $row['id'])
                ->set('total', 'total+1', false)
                ->update();
        } else {
            $table->insert([
                'post_id' => $postId,
                'reaction' => $reaction,
                'total' => 1
            ]);
        }
    }

    /**
     * Decrease aggregate total.
     */
    protected function decrementReaction(int $postId, string $reaction): void
    {
        $table = db_connect()->table('reactions');

        $row = $table
            ->where([
                'post_id' => $postId,
                'reaction' => $reaction
            ])
            ->get()
            ->getRowArray();

        if (!$row) {
            return;
        }

        $newTotal = max(0, (int)$row['total'] - 1);

        if ($newTotal === 0) {
            $table->where('id', $row['id'])->delete();
        } else {
            $table
                ->where('id', $row['id'])
                ->update([
                    'total' => $newTotal
                ]);
        }
    }
}