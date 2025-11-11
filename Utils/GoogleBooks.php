<?php
/**
 * Simple Google Books API client and filesystem cache.
 * Usage: include_once __DIR__ . '/Utils/GoogleBooks.php';
 * $items = GoogleBooksClient::search('hamlet');
 */
class GoogleBooksClient {
    protected static $cacheDir = __DIR__ . '/../cache/google_books';

    protected static function ensureCacheDir(): void {
        if (!is_dir(static::$cacheDir)) {
            @mkdir(static::$cacheDir, 0755, true);
        }
    }

    protected static function cacheGet(string $key) {
        static::ensureCacheDir();
        $path = static::$cacheDir . '/' . preg_replace('/[^a-z0-9_\-\.]/i', '_', $key) . '.json';
        if (!file_exists($path)) return null;
        $data = @file_get_contents($path);
        if ($data === false) return null;
        $obj = json_decode($data, true);
        if (!is_array($obj)) return null;
        // simple TTL support: store metadata
        if (isset($obj['_ts']) && isset($obj['_ttl'])) {
            if (time() > ($obj['_ts'] + (int)$obj['_ttl'])) {
                @unlink($path);
                return null;
            }
        }
        return $obj['payload'] ?? null;
    }

    protected static function cacheSet(string $key, $payload, int $ttl = 3600): void {
        static::ensureCacheDir();
        $path = static::$cacheDir . '/' . preg_replace('/[^a-z0-9_\-\.]/i', '_', $key) . '.json';
        $obj = [
            '_ts' => time(),
            '_ttl' => $ttl,
            'payload' => $payload,
        ];
        @file_put_contents($path, json_encode($obj));
    }

    public static function search(string $q, ?string $apiKey = null, int $maxResults = 10, int $cacheTtl = 3600): array {
        $q = trim($q);
        if ($q === '') return [];
        $key = 'search_' . $q . '_' . $maxResults . ($apiKey ? '_key' : '');
        $cached = static::cacheGet($key);
        if ($cached !== null) return $cached;

        $params = [
            'q' => $q,
            'maxResults' => min(40, max(1, $maxResults)),
        ];
        if ($apiKey) $params['key'] = $apiKey;
        $url = 'https://www.googleapis.com/books/v1/volumes?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_USERAGENT, 'StorySphere/1.0 (+https://example.local)');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($resp === false || $err) {
            return [];
        }
        $json = json_decode($resp, true);
        if (!is_array($json) || empty($json['items'])) {
            return [];
        }

        $items = [];
        foreach ($json['items'] as $item) {
            $mapped = static::mapVolume($item);
            if ($mapped) $items[] = $mapped;
        }

        static::cacheSet($key, $items, $cacheTtl);
        return $items;
    }

    public static function getByIsbn(string $isbn, ?string $apiKey = null, int $cacheTtl = 86400): ?array {
        $isbn = preg_replace('/[^0-9Xx]/', '', $isbn);
        if ($isbn === '') return null;
        $key = 'isbn_' . $isbn . ($apiKey ? '_key' : '');
        $cached = static::cacheGet($key);
        if ($cached !== null) return $cached[0] ?? null;

        $params = [
            'q' => 'isbn:' . $isbn,
            'maxResults' => 1,
        ];
        if ($apiKey) $params['key'] = $apiKey;
        $url = 'https://www.googleapis.com/books/v1/volumes?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_USERAGENT, 'StorySphere/1.0 (+https://example.local)');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $resp = curl_exec($ch);
        curl_close($ch);

        if ($resp === false) return null;
        $json = json_decode($resp, true);
        if (!is_array($json) || empty($json['items'])) return null;
        $items = [];
        foreach ($json['items'] as $item) {
            $mapped = static::mapVolume($item);
            if ($mapped) $items[] = $mapped;
        }
        static::cacheSet($key, $items, $cacheTtl);
        return $items[0] ?? null;
    }

    public static function getByGoogleId(string $googleId, ?string $apiKey = null, int $cacheTtl = 86400): ?array {
        $googleId = trim($googleId);
        if ($googleId === '') return null;
        $key = 'gid_' . $googleId . ($apiKey ? '_key' : '');
        $cached = static::cacheGet($key);
        if ($cached !== null) return $cached;

        $url = 'https://www.googleapis.com/books/v1/volumes/' . rawurlencode($googleId);
        if ($apiKey) {
            $url .= '?' . http_build_query(['key' => $apiKey]);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_USERAGENT, 'StorySphere/1.0 (+https://example.local)');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $resp = curl_exec($ch);
        curl_close($ch);

        if ($resp === false) return null;
        $json = json_decode($resp, true);
        if (!is_array($json)) return null;
        $mapped = static::mapVolume($json);
        if (!$mapped) return null;
        static::cacheSet($key, $mapped, $cacheTtl);
        return $mapped;
    }

    protected static function mapVolume(array $volume): ?array {
        if (empty($volume['volumeInfo'])) return null;
        $v = $volume['volumeInfo'];
        $title = $v['title'] ?? 'Unknown';
        $authors = $v['authors'] ?? [];
        $publisher = $v['publisher'] ?? 'N/A';
        $publishedDate = $v['publishedDate'] ?? null;
        $language = $v['language'] ?? 'N/A';
        $categories = $v['categories'] ?? [];
        $industryIds = $v['industryIdentifiers'] ?? [];
        $isbn13 = null;
        foreach ($industryIds as $id) {
            if (isset($id['type']) && strtoupper($id['type']) === 'ISBN_13') {
                $isbn13 = $id['identifier'];
                break;
            }
        }

        // Extract thumbnails if available
        $thumbnail = null;
        if (!empty($v['imageLinks'])) {
            // Prefer a larger image when available
            $thumbnail = $v['imageLinks']['thumbnail'] ?? $v['imageLinks']['smallThumbnail'] ?? null;
            // Normalize https
            if ($thumbnail !== null) {
                $thumbnail = preg_replace('#^http:#i', 'https:', $thumbnail);
            }
        }

        return [
            'google_id' => $volume['id'] ?? null,
            'title' => $title,
            'author_name' => is_array($authors) ? implode(', ', $authors) : (string)$authors,
            'publisher' => $publisher,
            'published_date' => $publishedDate,
            'language' => $language,
            'category_name' => is_array($categories) ? ($categories[0] ?? '') : ($categories ?: ''),
            'isbn' => $isbn13,
            // Best-effort: not known to local DB
            'available_copies' => 0,
            'total_copies' => 0,
            'book_condition' => 'N/A',
            // Additional Google-provided fields
            'description' => $v['description'] ?? '',
            'page_count' => isset($v['pageCount']) ? (int)$v['pageCount'] : 0,
            'thumbnail' => $thumbnail,
            'preview_link' => $v['previewLink'] ?? null,
            'info_link' => $v['infoLink'] ?? null,
            'ratings' => [ 'avg' => $v['averageRating'] ?? null, 'count' => $v['ratingsCount'] ?? 0 ],
            'source' => 'google',
        ];
    }
}
