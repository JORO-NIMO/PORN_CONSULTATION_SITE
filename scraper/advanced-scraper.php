<?php
/**
 * Advanced Content Scraper System
 * Fetches content from YouTube, X/Twitter, Instagram, Facebook, Research Papers, Journals
 * Run daily via cron job or Windows Task Scheduler
 */

require_once '../config/config.php';

class AdvancedContentScraper {
    private $db;
    private $apiKeys = [
        'youtube' => '', // Add your YouTube Data API v3 key
        'twitter' => '', // Add your Twitter/X API Bearer token
        'facebook' => '', // Add your Facebook Graph API token
        'instagram' => '', // Add your Instagram Basic Display API token
    ];
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->loadAPIKeys();
    }
    
    /**
     * Load API keys from config or environment
     */
    private function loadAPIKeys() {
        // Load from environment variables if available
        $this->apiKeys['youtube'] = getenv('YOUTUBE_API_KEY') ?: $this->apiKeys['youtube'];
        $this->apiKeys['twitter'] = getenv('TWITTER_BEARER_TOKEN') ?: $this->apiKeys['twitter'];
        $this->apiKeys['facebook'] = getenv('FACEBOOK_ACCESS_TOKEN') ?: $this->apiKeys['facebook'];
        $this->apiKeys['instagram'] = getenv('INSTAGRAM_ACCESS_TOKEN') ?: $this->apiKeys['instagram'];
    }
    
    /**
     * Scrape YouTube videos about pornography addiction recovery
     */
    public function scrapeYouTube() {
        if (empty($this->apiKeys['youtube'])) {
            return ['error' => 'YouTube API key not configured'];
        }
        
        $searchQueries = [
            'pornography addiction recovery',
            'quit porn addiction testimony',
            'freedom from porn addiction',
            'porn addiction effects',
            'breaking free from pornography'
        ];
        
        $videos = [];
        
        foreach ($searchQueries as $query) {
            $url = "https://www.googleapis.com/youtube/v3/search?" . http_build_query([
                'part' => 'snippet',
                'q' => $query,
                'type' => 'video',
                'maxResults' => 10,
                'order' => 'relevance',
                'key' => $this->apiKeys['youtube']
            ]);
            
            $response = $this->fetchURL($url);
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['items'])) {
                    foreach ($data['items'] as $item) {
                        $videoId = $item['id']['videoId'];
                        $videos[] = [
                            'youtube_id' => $videoId,
                            'title' => $item['snippet']['title'],
                            'description' => $item['snippet']['description'],
                            'thumbnail' => $item['snippet']['thumbnails']['high']['url'] ?? '',
                            'channel' => $item['snippet']['channelTitle'],
                            'published_at' => $item['snippet']['publishedAt']
                        ];
                    }
                }
            }
        }
        
        return $videos;
    }
    
    /**
     * Scrape research papers from PubMed/NCBI
     */
    public function scrapeResearchPapers() {
        $searchTerms = [
            'pornography addiction',
            'internet pornography effects',
            'pornography brain changes',
            'pornography relationships impact'
        ];
        
        $papers = [];
        
        foreach ($searchTerms as $term) {
            // PubMed E-utilities API (free, no key required for basic usage)
            $searchUrl = "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?" . http_build_query([
                'db' => 'pubmed',
                'term' => $term,
                'retmax' => 10,
                'retmode' => 'json',
                'sort' => 'relevance'
            ]);
            
            $searchResponse = $this->fetchURL($searchUrl);
            if ($searchResponse) {
                $searchData = json_decode($searchResponse, true);
                $ids = $searchData['esearchresult']['idlist'] ?? [];
                
                if (!empty($ids)) {
                    // Fetch details for these papers
                    $summaryUrl = "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?" . http_build_query([
                        'db' => 'pubmed',
                        'id' => implode(',', $ids),
                        'retmode' => 'json'
                    ]);
                    
                    $summaryResponse = $this->fetchURL($summaryUrl);
                    if ($summaryResponse) {
                        $summaryData = json_decode($summaryResponse, true);
                        foreach ($ids as $id) {
                            if (isset($summaryData['result'][$id])) {
                                $paper = $summaryData['result'][$id];
                                $papers[] = [
                                    'title' => $paper['title'] ?? '',
                                    'authors' => implode(', ', array_slice($paper['authors'] ?? [], 0, 3)),
                                    'journal' => $paper['source'] ?? '',
                                    'pubdate' => $paper['pubdate'] ?? '',
                                    'pmid' => $id,
                                    'url' => "https://pubmed.ncbi.nlm.nih.gov/{$id}/",
                                    'abstract' => '' // Would need separate API call for abstract
                                ];
                            }
                        }
                    }
                }
            }
            
            sleep(1); // Rate limiting
        }
        
        return $papers;
    }
    
    /**
     * Scrape articles from Fight the New Drug (ethical anti-porn organization)
     */
    public function scrapeFightTheNewDrug() {
        $articles = [];
        $url = 'https://fightthenewdrug.org/get-the-facts/';
        
        $html = $this->fetchURL($url);
        if ($html) {
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);
            
            // Extract articles
            $articleNodes = $xpath->query("//article | //div[contains(@class, 'post')]");
            
            foreach ($articleNodes as $node) {
                $titleNode = $xpath->query(".//h2 | .//h3", $node)->item(0);
                $linkNode = $xpath->query(".//a[@href]", $node)->item(0);
                $excerptNode = $xpath->query(".//p", $node)->item(0);
                
                if ($titleNode && $linkNode) {
                    $articles[] = [
                        'title' => trim($titleNode->textContent),
                        'url' => $linkNode->getAttribute('href'),
                        'excerpt' => $excerptNode ? trim($excerptNode->textContent) : '',
                        'source' => 'Fight the New Drug'
                    ];
                }
            }
        }
        
        return $articles;
    }
    
    /**
     * Scrape content from X/Twitter (requires API access)
     */
    public function scrapeTwitter() {
        if (empty($this->apiKeys['twitter'])) {
            return ['error' => 'Twitter API key not configured'];
        }
        
        $hashtags = [
            '#PornAddictionRecovery',
            '#QuitPorn',
            '#NoFap',
            '#PornFree'
        ];
        
        $tweets = [];
        
        foreach ($hashtags as $hashtag) {
            $url = "https://api.twitter.com/2/tweets/search/recent?" . http_build_query([
                'query' => $hashtag . ' -is:retweet',
                'max_results' => 10,
                'tweet.fields' => 'created_at,public_metrics'
            ]);
            
            $response = $this->fetchURL($url, [
                'Authorization: Bearer ' . $this->apiKeys['twitter']
            ]);
            
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['data'])) {
                    $tweets = array_merge($tweets, $data['data']);
                }
            }
            
            sleep(1); // Rate limiting
        }
        
        return $tweets;
    }
    
    /**
     * Save YouTube videos to database
     */
    public function saveYouTubeVideos($videos) {
        $saved = 0;
        
        foreach ($videos as $video) {
            // Check if video already exists
            $existing = $this->db->fetchOne(
                "SELECT id FROM scraped_videos WHERE youtube_id = ?",
                [$video['youtube_id']]
            );
            
            if (!$existing) {
                $this->db->query(
                    "INSERT INTO scraped_videos (youtube_id, title, description, thumbnail, channel, published_at, source_type) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [
                        $video['youtube_id'],
                        $video['title'],
                        $video['description'],
                        $video['thumbnail'],
                        $video['channel'],
                        $video['published_at'],
                        'youtube'
                    ]
                );
                $saved++;
            }
        }
        
        return $saved;
    }
    
    /**
     * Save research papers to database
     */
    public function saveResearchPapers($papers) {
        $saved = 0;
        
        foreach ($papers as $paper) {
            $existing = $this->db->fetchOne(
                "SELECT id FROM scraped_research WHERE pmid = ?",
                [$paper['pmid']]
            );
            
            if (!$existing) {
                $this->db->query(
                    "INSERT INTO scraped_research (title, authors, journal, pubdate, pmid, url, source_type) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [
                        $paper['title'],
                        $paper['authors'],
                        $paper['journal'],
                        $paper['pubdate'],
                        $paper['pmid'],
                        $paper['url'],
                        'pubmed'
                    ]
                );
                $saved++;
            }
        }
        
        return $saved;
    }
    
    /**
     * Save articles to database
     */
    public function saveArticles($articles) {
        $saved = 0;
        
        foreach ($articles as $article) {
            $existing = $this->db->fetchOne(
                "SELECT id FROM scraped_articles WHERE url = ?",
                [$article['url']]
            );
            
            if (!$existing) {
                $this->db->query(
                    "INSERT INTO scraped_articles (title, url, excerpt, source, source_type) 
                     VALUES (?, ?, ?, ?, ?)",
                    [
                        $article['title'],
                        $article['url'],
                        $article['excerpt'],
                        $article['source'],
                        'article'
                    ]
                );
                $saved++;
            }
        }
        
        return $saved;
    }
    
    /**
     * Fetch URL with optional headers
     */
    private function fetchURL($url, $headers = []) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Educational Research Bot)',
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ($httpCode === 200) ? $content : false;
    }
    
    /**
     * Run all scrapers
     */
    public function runAll() {
        $results = [
            'youtube' => 0,
            'research' => 0,
            'articles' => 0,
            'twitter' => 0
        ];
        
        // Scrape YouTube
        echo "Scraping YouTube videos...\n";
        $youtubeVideos = $this->scrapeYouTube();
        if (!isset($youtubeVideos['error'])) {
            $results['youtube'] = $this->saveYouTubeVideos($youtubeVideos);
            echo "Saved {$results['youtube']} YouTube videos\n";
        } else {
            echo "YouTube: " . $youtubeVideos['error'] . "\n";
        }
        
        // Scrape Research Papers
        echo "Scraping research papers from PubMed...\n";
        $papers = $this->scrapeResearchPapers();
        $results['research'] = $this->saveResearchPapers($papers);
        echo "Saved {$results['research']} research papers\n";
        
        // Scrape Articles
        echo "Scraping articles from Fight the New Drug...\n";
        $articles = $this->scrapeFightTheNewDrug();
        $results['articles'] = $this->saveArticles($articles);
        echo "Saved {$results['articles']} articles\n";
        
        // Scrape Twitter (optional)
        if (!empty($this->apiKeys['twitter'])) {
            echo "Scraping Twitter/X posts...\n";
            $tweets = $this->scrapeTwitter();
            if (!isset($tweets['error'])) {
                echo "Found " . count($tweets) . " tweets\n";
            }
        }
        
        return $results;
    }
}

// Run scraper if called directly
if (php_sapi_name() === 'cli' || isset($_GET['run_advanced_scraper'])) {
    echo "=== Advanced Content Scraper ===\n";
    echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";
    
    $scraper = new AdvancedContentScraper();
    $results = $scraper->runAll();
    
    echo "\n=== Scraping Complete ===\n";
    echo "Total YouTube videos: {$results['youtube']}\n";
    echo "Total research papers: {$results['research']}\n";
    echo "Total articles: {$results['articles']}\n";
    echo "Finished at: " . date('Y-m-d H:i:s') . "\n";
}
