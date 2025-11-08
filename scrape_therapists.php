<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/therapist_db.php';

function scrapeTherapists($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // WARNING: This is insecure, use only for testing
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // WARNING: This is insecure, use only for testing
    $html = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception(curl_error($ch));
    }
    curl_close($ch);
    if ($html === false) {
        die("Failed to fetch URL: $url");
    }

    $therapists = [];
    // Using DOMDocument and DOMXPath for more robust HTML parsing
    $dom = new DOMDocument();
    // Suppress warnings for malformed HTML
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);

    $xpath = new DOMXPath($dom);

    // Find the JSON-LD script tag
    $jsonLdNodes = $xpath->query('//script[@type="application/ld+json"]');

    if ($jsonLdNodes->length > 0) {
        $jsonLd = $jsonLdNodes->item(0)->textContent;
        $data = json_decode($jsonLd, true);

        // Check if the data is an array of therapists or a single therapist
        if (isset($data['@graph']) && is_array($data['@graph'])) {
            foreach ($data['@graph'] as $item) {
                if (isset($item['@type']) && $item['@type'] === 'Physician') {
                    $therapists[] = [
                        'name' => $item['name'] ?? 'Unknown',
                        'medical_specialty' => is_array($item['medicalSpecialty'] ?? []) ? implode(', ', $item['medicalSpecialty']) : ($item['medicalSpecialty'] ?? ''),
                        'city' => $item['address']['addressLocality'] ?? '',
                        'country' => $item['address']['addressCountry'] ?? '',
                        'contact_email' => $item['email'] ?? '',
                        'phone' => $item['telephone'] ?? '',
                        'profile_image' => $item['image'] ?? '',
                        'source' => 'therapymantra',
                        'source_id' => md5($item['name'] . ($item['medicalSpecialty'][0] ?? '')),
                        'profile_url' => $item['url'] ?? ''
                    ];
                }
            }
        } elseif (isset($data['@type']) && $data['@type'] === 'Physician') {
            // Handle case where it's a single therapist object directly
            $therapists[] = [
                'name' => $data['name'] ?? 'Unknown',
                'medical_specialty' => implode(', ', $data['medicalSpecialty'] ?? []) ?? '',
                'city' => $data['address']['addressLocality'] ?? '',
                'country' => $data['address']['addressCountry'] ?? '',
                'contact_email' => $data['email'] ?? '',
                'phone' => $data['telephone'] ?? '',
                'profile_image' => $data['image'] ?? '',
                'source' => 'therapymantra',
                'source_id' => md5($data['name'] . ($data['medicalSpecialty'][0] ?? '')),
                'profile_url' => $data['url'] ?? ''
            ];
        }
    } else {
        echo "No JSON-LD script tag found.\n";
    }

    // Remove the old XPath query for therapist cards as we are now using JSON-LD
    // $therapistNodes = $xpath->query('//div[@class="therapist-card"]');

    // The rest of the scraping logic for HTML parsing is no longer needed if JSON-LD is present
    // foreach ($therapistNodes as $node) {
    //     $name = $xpath->query('.//h2[@class="therapist-name"]', $node)->item(0)->textContent ?? 'Unknown';
    //     $title_location = $xpath->query('.//p[@class="therapist-title-location"]', $node)->item(0)->textContent ?? '';
    //     $specialties_text = $xpath->query('.//div[@class="therapist-specialties"]', $node)->item(0)->textContent ?? '';
    //     $languages_text = $xpath->query('.//div[@class="therapist-languages"]', $node)->item(0)->textContent ?? '';
    //     $profile_image = $xpath->query('.//img[@class="therapist-image"]/@src', $node)->item(0)->nodeValue ?? '';
    //     $profile_url = $xpath->query('.//a[@class="therapist-profile-link"]/@href', $node)->item(0)->nodeValue ?? '';

    //     // Extract specialization, city, country from title_location
    //     $parts = explode(',', $title_location);
    //     $specialization = trim($parts[0] ?? '');
    //     $city = trim($parts[1] ?? '');
    //     $country = trim($parts[2] ?? '');

    //     // Extract languages
    //     $languages = str_replace('Languages:', '', $languages_text);
    //     $languages = trim($languages);

    //     if ($name === 'Unknown') {
    //         // echo "Skipping node due to unknown name.\n"; // Removed debugging
    //         continue; // Skip if no name found
    //     }

    //     // echo "Scraped: " . $name . " - " . $specialization . " - " . $city . " - " . $country . "\n"; // Removed debugging

    //     $therapists[] = [
    //         'name' => trim($name),
    //         'specialties' => trim($specialties_text),
    //         'city' => $city,
    //         'country' => $country,
    //         'contact_email' => '', // Not available in the provided HTML
    //         'phone' => '', // Not available in the provided HTML
    //         'profile_image' => trim($profile_image),
    //         'source' => 'therapymantra',
    //         'source_id' => md5($name . $specialties_text),
    //         'profile_url' => trim($profile_url)
    //     ];
    // }
    return $therapists;
}

$db = new TherapistDB();
$pdo = $db->pdo();

$scrapedTherapists = scrapeTherapists('https://therapists.therapymantra.co/therapist/uganda?');

foreach ($scrapedTherapists as $therapist) {
    try {
        $mappedTherapist = [
            'name' => $therapist['name'],
            'medical_specialty' => $therapist['medical_specialty'],
            'address_locality' => $therapist['city'] ?? null,
            'address_country' => $therapist['country'] ?? null,
            'contact_email' => $therapist['contact_email'] ?? null,
            'telephone' => $therapist['phone'] ?? null,
            'image' => $therapist['profile_image'] ?? null,
            'url' => $therapist['profile_url'] ?? null,
            'source' => $therapist['source'] ?? null,
            'source_id' => $therapist['source_id'] ?? null,
        ];

        $stmt = $pdo->prepare("
            INSERT INTO therapists (
                name, medical_specialty, address_locality, address_country, contact_email, telephone, image, url, source, source_id
            ) VALUES (
                :name, :medical_specialty, :address_locality, :address_country, :contact_email, :telephone, :image, :url, :source, :source_id
            ) ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                medical_specialty = VALUES(medical_specialty),
                address_locality = VALUES(address_locality),
                address_country = VALUES(address_country),
                contact_email = VALUES(contact_email),
                telephone = VALUES(telephone),
                image = VALUES(image),
                url = VALUES(url),
                source = VALUES(source),
                source_id = VALUES(source_id),
                updated_at = NOW()
        ");
        $stmt->execute($mappedTherapist);
        echo "Inserted/Updated: " . $therapist['name'] . "\n";
    } catch (PDOException $e) {
        echo "Error inserting/updating therapist: " . $e->getMessage() . "\n";
    }
}

echo "Scraping and import complete.\n";

?>