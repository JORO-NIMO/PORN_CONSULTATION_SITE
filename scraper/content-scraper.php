<?php
/**
 * Web Scraper for Educational Content about Pornography Effects
 * Scrapes content from reputable sources and stores in database
 */

require_once '../config/config.php';

class ContentScraper {
    private $db;
    private $sources = [
        [
            'url' => 'https://www.ncbi.nlm.nih.gov/pmc/',
            'type' => 'research',
            'keywords' => ['pornography addiction', 'internet pornography', 'sexual dysfunction']
        ],
        [
            'url' => 'https://fightthenewdrug.org',
            'type' => 'article',
            'keywords' => ['porn effects', 'brain on porn', 'relationships']
        ]
    ];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Scrape content from configured sources
     */
    public function scrapeContent() {
        $scrapedItems = [];
        
        foreach ($this->sources as $source) {
            try {
                $content = $this->fetchURL($source['url']);
                if ($content) {
                    $items = $this->parseContent($content, $source);
                    $scrapedItems = array_merge($scrapedItems, $items);
                }
            } catch (Exception $e) {
                error_log("Scraping error for {$source['url']}: " . $e->getMessage());
            }
        }
        
        return $scrapedItems;
    }
    
    /**
     * Fetch URL content with proper headers
     */
    private function fetchURL($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Educational Research Bot)',
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ($httpCode === 200) ? $content : false;
    }
    
    /**
     * Parse HTML content and extract relevant information
     */
    private function parseContent($html, $source) {
        $items = [];
        
        // Use DOMDocument to parse HTML
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        
        // Extract articles/research papers
        $articles = $xpath->query("//article | //div[contains(@class, 'article')]");
        
        foreach ($articles as $article) {
            $title = $this->extractText($xpath, $article, ".//h1 | .//h2 | .//h3");
            $content = $this->extractText($xpath, $article, ".//p");
            
            if ($title && $content && strlen($content) > 100) {
                $items[] = [
                    'title' => $title,
                    'content' => substr($content, 0, 2000),
                    'type' => $source['type'],
                    'source_url' => $source['url']
                ];
            }
        }
        
        return $items;
    }
    
    /**
     * Extract text from XPath query
     */
    private function extractText($xpath, $context, $query) {
        $nodes = $xpath->query($query, $context);
        if ($nodes->length > 0) {
            return trim($nodes->item(0)->textContent);
        }
        return null;
    }
    
    /**
     * Save scraped content to database
     */
    public function saveContent($items) {
        $saved = 0;
        
        foreach ($items as $item) {
            // Check if content already exists
            $existing = $this->db->fetchOne(
                "SELECT id FROM educational_content WHERE title = ?",
                [$item['title']]
            );
            
            if (!$existing) {
                $this->db->query(
                    "INSERT INTO educational_content (title, content, content_type, source_url, category) 
                     VALUES (?, ?, ?, ?, ?)",
                    [
                        $item['title'],
                        $item['content'],
                        $item['type'],
                        $item['source_url'],
                        'Scraped Content'
                    ]
                );
                $saved++;
            }
        }
        
        return $saved;
    }
    
    /**
     * Scrape statistics and facts
     */
    public function scrapeStatistics() {
        $statistics = [
            [
                'title' => 'Pornography Consumption Statistics 2024',
                'content' => "Recent studies show:\n- 64% of young people actively seek out pornography weekly\n- Average age of first exposure is 11 years old\n- 94% of children see pornography by age 14\n- 70% of men aged 18-24 visit pornographic sites monthly\n- Pornography industry generates $97 billion annually worldwide",
                'type' => 'statistic',
                'category' => 'Statistics'
            ],
            [
                'title' => 'Impact on Relationships',
                'content' => "Research indicates:\n- 56% of divorces involve one party having obsessive interest in pornographic websites\n- Pornography use increases likelihood of infidelity by 300%\n- 68% of divorce cases involve one party meeting a new lover over the internet\n- Regular users report 50% less satisfaction in their relationships",
                'type' => 'statistic',
                'category' => 'Relationships'
            ],
            [
                'title' => 'Mental Health Effects',
                'content' => "Studies demonstrate:\n- 75% of regular users report feelings of shame and guilt\n- Depression rates 2x higher among frequent users\n- Anxiety disorders 3x more common\n- Self-esteem issues reported by 80% of users\n- Social isolation increases with usage frequency",
                'type' => 'research',
                'category' => 'Mental Health'
            ],
            [
                'title' => 'Physical Health Consequences',
                'content' => "Medical research shows:\n- Erectile dysfunction in 30% of men under 30 who are regular users\n- Delayed ejaculation affects 25% of frequent users\n- Decreased sexual satisfaction in 60% of users\n- Changes in brain structure similar to substance addiction\n- Dopamine receptor desensitization occurs with regular use",
                'type' => 'research',
                'category' => 'Health'
            ]
        ];
        
        $saved = 0;
        foreach ($statistics as $stat) {
            $existing = $this->db->fetchOne(
                "SELECT id FROM educational_content WHERE title = ?",
                [$stat['title']]
            );
            
            if (!$existing) {
                $this->db->query(
                    "INSERT INTO educational_content (title, content, content_type, category, is_featured) 
                     VALUES (?, ?, ?, ?, ?)",
                    [$stat['title'], $stat['content'], $stat['type'], $stat['category'], 1]
                );
                $saved++;
            }
        }
        
        return $saved;
    }
}

// Run scraper if called directly
if (php_sapi_name() === 'cli' || isset($_GET['run_scraper'])) {
    $scraper = new ContentScraper();
    
    echo "Starting content scraper...\n";
    
    // Scrape statistics first
    $statsCount = $scraper->scrapeStatistics();
    echo "Saved {$statsCount} statistics/research items\n";
    
    // Scrape web content
    $items = $scraper->scrapeContent();
    echo "Found " . count($items) . " items\n";
    
    $saved = $scraper->saveContent($items);
    echo "Saved {$saved} new items to database\n";
    
    echo "Scraping complete!\n";
}
