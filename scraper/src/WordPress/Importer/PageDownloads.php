<?php

namespace Scraper\WordPress\Importer;

use Scraper\Page as ScraperPage;
use Scraper\WordPress\WordPress;
use Scraper\WordPress\Post\Attachment as WpAttachment;

class PageDownloads extends Base {
    /**
     * The ACF field key used to store page downloads.
     *
     * @var string
     */
    public static $acfFieldKey = null;

    /**
     * Import downloads for the given page.
     *
     * @param ScraperPage $page
     * @return boolean
     */
    public static function import(ScraperPage $page) {
        if (!$page->hasDownloads()) {
            return false;
        }

        $wpPost = $page->getWpPost();
        $postId = $wpPost->WP_Post->ID;

        $acfDownloads = $wpPost->getField(static::$acfFieldKey);
        if (!is_array($acfDownloads)) {
            $acfDownloads = [];
        }
        $acfDownloadAttachments = self::getAttachmentPosts($acfDownloads);

        $downloads = $page->getContent()->getDownloads();
        foreach ($downloads as $download) {
            // Check if attachment is already associated with page's ACF file field
            $existingAttachment = self::getExistingAttachment($download, $acfDownloadAttachments);
            if ($existingAttachment) {
                if (static::$skipExisting) {
                    continue;
                } else {
                    self::deleteExistingAttachment($existingAttachment, $acfDownloads);
                }
            }

            $mediaMeta = [
                'reddot_import' => 1,
                'reddot_url' => $download['relativeUrl'],
            ];
            $mediaId = null;

            $existingMedia = WpAttachment::getByMeta($mediaMeta);
            if ($existingMedia) {
                // File with same path already exists in the media library
                if (static::$skipExisting) { // Reuse it
                    $mediaId = $existingMedia->WP_Post->ID;
                } else { // Delete it, so it can be re-imported
                    $existingMedia->delete();
                    $mediaId = null;
                }
            }

            // If we don't have a $mediaId, import the file
            if (is_null($mediaId)) {
                $filePath = static::$baseFilePath . $download['relativeUrl'];
                $mediaId = WordPress::importMedia($filePath, $postId, $download['title'], $mediaMeta);
            }

            $acfDownloads[] = [
                'file' => $mediaId,
            ];
        }

        return $wpPost->saveField(static::$acfFieldKey, $acfDownloads);
    }

    /**
     * Retrieve attachment post objects for each ACF page download.
     *
     * @param $acfDownloads
     * @return array
     */
    private static function getAttachmentPosts($acfDownloads) {
        $posts = [];

        foreach ($acfDownloads as $download) {
            $obj = WpAttachment::getById($download['file']);
            if ($obj) {
                $posts[] = $obj;
            }
        }

        return $posts;
    }

    /**
     * For the given scraped download link, look for an attachment with the same title which is already
     * attached to the page as an ACF download.
     * If one exists, return the WpAttachment post object.
     *
     * @param $download
     * @param $attachments
     * @return false|WpAttachment
     */
    private static function getExistingAttachment($download, $attachments) {
        $found = false;

        foreach ($attachments as $attachment) {
            if ($attachment->WP_Post->post_title == $download['title']) {
                $found = $attachment;
            }
        }

        return $found;
    }

    /**
     * Delete the supplied download, both removing the attachment from the WordPress media library and
     * removing the attachment reference from the ACF downloads array.
     *
     * @param $attachment
     * @param $acfDownloads
     */
    private static function deleteExistingAttachment($attachment, &$acfDownloads) {
        foreach ($acfDownloads as $k => $dl) {
            if ($dl['file'] === $attachment->WP_Post->ID) {
                unset($acfDownloads[$k]);
            }
        }

        $attachment->delete();
    }

    private static function fileInMediaLibrary($download) {
        $existing = WpAttachment::getByMeta([
            'reddot_import' => 1,
            'reddot_url' => $download['relativeUrl'],
        ]);

        return $existing;
    }
}