<?php
class SentimentCache {
    private $cacheDir;
    private $cacheFile;

    public function __construct() {
        $this->cacheDir = dirname(__FILE__); // Use absolute path
        $this->cacheFile = $this->cacheDir . '/sentiment_cache.json';
        
        // Create cache directory if it doesn't exist
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
        
        // Create empty cache file if it doesn't exist
        if (!file_exists($this->cacheFile)) {
            file_put_contents($this->cacheFile, '{}');
        }
    }

    public function get($key, $electionId) {
        if (!file_exists($this->cacheFile)) {
            return null;
        }
        
        $cacheKey = $this->generateKey($key, $electionId);
        $cache = json_decode(file_get_contents($this->cacheFile), true);
        return isset($cache[$cacheKey]) ? $cache[$cacheKey] : null;
    }

    public function set($key, $value, $electionId) {
        $cache = [];
        if (file_exists($this->cacheFile)) {
            $cache = json_decode(file_get_contents($this->cacheFile), true);
        }
        
        $cacheKey = $this->generateKey($key, $electionId);
        $cache[$cacheKey] = $value;
        file_put_contents($this->cacheFile, json_encode($cache), LOCK_EX);
    }

    public function clear() {
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
    }

    public function clearElection($electionId) {
        if (file_exists($this->cacheFile)) {
            $cache = json_decode(file_get_contents($this->cacheFile), true);
            foreach (array_keys($cache) as $key) {
                if (strpos($key, "election_{$electionId}_") === 0) {
                    unset($cache[$key]);
                }
            }
            file_put_contents($this->cacheFile, json_encode($cache), LOCK_EX);
        }
    }

    private function generateKey($key, $electionId) {
        return "election_{$electionId}_{$key}";
    }
}