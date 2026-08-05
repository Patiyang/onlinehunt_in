<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Services\FirebaseNotificationService;

class PostSelectionModel extends Model
{
    protected $table = 'post_selections';
    protected $allowedFields = ['post_id', 'lang_id', 'selection_type', 'created_at'];
    protected $createdField = 'created_at';
    protected $postSelectionOptions;
    protected $firebaseNotificationService;

    public function __construct()
    {
        parent::__construct();

        $this->postSelectionOptions = getAppDefault('postSelectionOptions');
        $this->firebaseNotificationService =
            new FirebaseNotificationService();
    }

    /**
     * Synchronizes post selections based on the provided active types
     */
    public function syncSelections(int $postId, int $langId, array $activeTypes): bool
    {
        $newTypes = array_intersect($activeTypes, $this->postSelectionOptions);
        $newTypes = array_unique($newTypes);

        $currentTypes = $this->findAllByPostId($postId);

        // Differences
        $typesToDelete = array_diff($currentTypes, $newTypes);
        $typesToInsert = array_diff($newTypes, $currentTypes);

        $this->db->transStart();

        // Update lang_id
        $this->where('post_id', $postId)
            ->where('lang_id !=', $langId)
            ->set('lang_id', $langId)
            ->update();

        if (empty($typesToDelete) && empty($typesToInsert)) {
            $this->db->transComplete();
            return $this->db->transStatus();
        }

        if (!empty($typesToDelete)) {
            $this->where('post_id', $postId)->whereIn('selection_type', $typesToDelete)->delete();
        }

        if (!empty($typesToInsert)) {
            $dataToInsert = [];
            foreach ($typesToInsert as $type) {
                $dataToInsert[] = [
                    'post_id'        => $postId,
                    'lang_id'        => $langId,
                    'selection_type' => $type
                ];
            }
            $this->insertBatch($dataToInsert);

            $allActiveTypes = array_merge($currentTypes, $typesToInsert);
            $allActiveTypes = array_diff($allActiveTypes, $typesToDelete);

            foreach ($allActiveTypes as $type) {
                $this->enforceSelectionLimit($type, $langId);
            }
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Adds or removes posts from a specific selection
     */
    // public function updatePostSelections(int|string|array $ids, string $selectionType, string $action): bool
    // {
    //     if (empty($ids)) {
    //         return false;
    //     }

    //     $ids = is_array($ids) ? $ids : [$ids];
    //     $ids = array_filter(array_map('intval', $ids));

    //     // Remove
    //     if ($action === 'remove') {
    //         $this->where('selection_type', $selectionType)
    //             ->whereIn('post_id', $ids)
    //             ->delete();
    //         return true;
    //     }

    //     // Add
    //     if ($action === 'add') {
    //         $existingRecords = $this->select('post_id')
    //             ->where('selection_type', $selectionType)
    //             ->whereIn('post_id', $ids)
    //             ->findAll();

    //         // Extract existing IDs into a simple array
    //         $existingIds = [];
    //         foreach ($existingRecords as $record) {
    //             $existingIds[] = is_object($record) ? (int)$record->post_id : (int)$record['post_id'];
    //         }

    //         $idsToInsert = array_diff($ids, $existingIds);
    //         if (empty($idsToInsert)) {
    //             return true;
    //         }

    //         // Fetch lang_id for ALL new posts in a single query to avoid N+1 problem
    //         $postRecords = model('PostModel')
    //             ->select('id, lang_id')
    //             ->whereIn('id', $idsToInsert)
    //             ->findAll();

    //         // Create a map of post_id => lang_id for quick lookups
    //         $langMap = [];
    //         foreach ($postRecords as $record) {
    //             $langMap[$record->id] = (int)$record->lang_id;



    //             if ($selectionType == 'breaking_news') {
    //                 $this->firebaseNotificationService->sendToTopic(
    //                     'all',
    //                     $record->title,
    //                     $record->summary,
    //                     [
    //                         'type' =>$record->video_url==null? 'article':'video',
    //                         'id' => '',
    //                         'live_url' => '',
    //                         'slug' => $record->slug,
    //                         'image_url' => $record->image_url
    //                     ]
    //                 );
    //             }
    //         }

    //         $batchData = [];
    //         $affectedLangs = [];

    //         foreach ($idsToInsert as $postId) {
    //             // Get the specific language for this post, default to 1 if not found
    //             $langId = $langMap[$postId] ?? 1;

    //             // Store unique language IDs
    //             $affectedLangs[$langId] = $langId;

    //             $batchData[] = [
    //                 'post_id'        => $postId,
    //                 'lang_id'        => $langId,
    //                 'selection_type' => $selectionType
    //             ];
    //         }

    //         $inserted = (bool)$this->insertBatch($batchData);

    //         if ($inserted) {
    //             // Enforce the limit ONLY for the languages that received new posts
    //             foreach ($affectedLangs as $langId) {
    //                 $this->enforceSelectionLimit($selectionType, $langId);
    //             }
    //         }

    //         return $inserted;
    //     }

    //     return false;
    // }
    public function updatePostSelections(
        int|string|array $ids,
        string $selectionType,
        string $action
    ): bool {
        if (empty($ids)) {
            return false;
        }

        $ids = is_array($ids) ? $ids : [$ids];
        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return false;
        }

        // Remove
        if ($action === 'remove') {
            $this->where('selection_type', $selectionType)
                ->whereIn('post_id', $ids)
                ->delete();

            return true;
        }

        // Add
        if ($action === 'add') {

            // Find posts that are already selected
            $existingRecords = $this->select('post_id')
                ->where('selection_type', $selectionType)
                ->whereIn('post_id', $ids)
                ->findAll();

            $existingIds = [];

            foreach ($existingRecords as $record) {
                $existingIds[] = is_object($record)
                    ? (int)$record->post_id
                    : (int)$record['post_id'];
            }

            // Only process genuinely new selections
            $idsToInsert = array_diff($ids, $existingIds);

            if (empty($idsToInsert)) {
                return true;
            }

            /*
         * Get all required post information in one query.
         */
            $postRecords = model('PostModel')
                ->select('id, lang_id, title, summary, slug, video_url, image_url')
                ->whereIn('id', $idsToInsert)
                ->findAll();

            $langMap = [];

            foreach ($postRecords as $record) {
                $langMap[(int)$record->id] = (int)$record->lang_id;
            }

            $batchData = [];
            $affectedLangs = [];

            foreach ($idsToInsert as $postId) {

                $langId = $langMap[$postId] ?? 1;

                $affectedLangs[$langId] = $langId;

                $batchData[] = [
                    'post_id'        => $postId,
                    'lang_id'        => $langId,
                    'selection_type' => $selectionType
                ];
            }

            /*
         * Insert selections first.
         */
            $inserted = (bool)$this->insertBatch($batchData);

            if (!$inserted) {
                return false;
            }

            /*
         * Enforce selection limits.
         */
            foreach ($affectedLangs as $langId) {
                $this->enforceSelectionLimit(
                    $selectionType,
                    $langId
                );
            }

            /*
         * Send notifications only for breaking news.
         */
            if ($selectionType === 'breaking_news') {

                foreach ($postRecords as $record) {

                    try {
                        $imageUrl = (string)$record->image_url;

                        if (
                            !empty($record->video_url) &&
                            str_contains(strtolower($record->video_url), 'youtube')
                        ) {
                            $uri = parse_url($record->video_url);

                            if (!empty($uri['query'])) {
                                parse_str($uri['query'], $queryParams);

                                if (!empty($queryParams['v'])) {
                                    $imageUrl = 'https://img.youtube.com/vi/'
                                        . $queryParams['v']
                                        . '/0.jpg';
                                }
                            }
                        }
                        $this->firebaseNotificationService->sendToTopic(
                            'all',
                            $record->title,
                            $this->trimToWords($record->summary, 20),
                            [
                                'type' => empty($record->video_url)
                                    ? 'article'
                                    : 'video',

                                'id' => (string)$record->id,

                                'live_url' => '',

                                'slug' => (string)$record->slug,

                                'image_url' => $imageUrl
                            ]
                        );
                    } catch (\Throwable $e) {

                        /*
                     * Notification failure should not cause
                     * the breaking-news selection to fail.
                     */
                        log_message(
                            'error',
                            'Breaking news notification failed for post '
                                . $record->id . ': '
                                . $e->getMessage()
                        );
                    }
                }
            }

            return true;
        }

        return false;
    }
    /**
     * Retrieves active selections for a specific post
     */
    public function findAllByPostId(int $postId): array
    {
        if (empty($postId)) {
            return [];
        }

        $result = $this->where('post_id', $postId)
            ->findColumn('selection_type');

        return $result ?? [];
    }

    /**
     * Returns an array of Post IDs that match the given selection type
     */
    public function getPostIdsBySelection(string $selectionType): array
    {
        if (!in_array($selectionType, $this->postSelectionOptions)) {
            return [];
        }

        $ids = $this->where('selection_type', $selectionType)
            ->findColumn('post_id');

        return $ids ?? [];
    }

    /**
     * Enforces the maximum limit for a specific selection type by deleting older records
     */
    protected function enforceSelectionLimit(string $type, int $langId): void
    {
        $maxLimit = defined('LIMIT_SHOWCASE_POSTS') ? (int)LIMIT_SHOWCASE_POSTS : 50;

        // Fetch the IDs of the newest records that we want to KEEP
        $recordsToKeep = $this->builder()
            ->select('id')
            ->where('selection_type', $type)
            ->where('lang_id', $langId)
            ->orderBy('id', 'DESC')
            ->limit($maxLimit)
            ->get()
            ->getResultArray();

        // If there are no records, nothing to enforce
        if (empty($recordsToKeep)) {
            return;
        }

        // Extract the IDs into a simple flat array
        $idsToKeep = array_column($recordsToKeep, 'id');

        // Delete everything else of that type that is NOT in the keep list
        if (!empty($idsToKeep)) {
            $this->builder()
                ->where('selection_type', $type)
                ->where('lang_id', $langId)
                ->whereNotIn('id', $idsToKeep)
                ->delete();
        }
    }

    /**
     * Deletes expired post selections based on the duration settings
     */
    public function deleteExpiredSelections(): int
    {
        $settings = $this->config->featured_content_settings ?? new \stdClass();

        // Map the selection types directly to their duration values
        $durationSettings = [
            'slider'        => $settings->slider_duration ?? 0,
            'featured'      => $settings->featured_duration ?? 0,
            'recommended'   => $settings->recommended_duration ?? 0,
            'breaking_news' => $settings->br_news_duration ?? 0
        ];

        $totalDeleted = 0;

        foreach ($durationSettings as $type => $durationValue) {
            $days = (int)$durationValue;

            // If a valid duration (in days) is set, process the deletion
            if ($days > 0) {
                // Calculate the cutoff date (e.g., 5 days ago)
                $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));

                // Delete records older than the cutoff date for this specific selection type
                $this->builder()
                    ->where('selection_type', $type)
                    ->where('created_at <', $cutoffDate)
                    ->delete();

                // Accumulate the number of deleted rows
                $totalDeleted += $this->db->affectedRows();
            }
        }

        return $totalDeleted;
    }

    protected function trimToWords(?string $text, int $maxWords = 20): string
    {
        $text = trim((string)$text);

        if ($text === '') {
            return '';
        }

        $words = preg_split('/\s+/', $text);

        if (count($words) <= $maxWords) {
            return $text;
        }

        return implode(' ', array_slice($words, 0, $maxWords)) . '...';
    }
}
