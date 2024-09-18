<?php 
declare(strict_types=1);

header('Content-Type: application/xml; charset=utf-8');

/**
 * iTunes-Compatible RSS 2.0 MP3 subscription feed script
 * Original work by Rob W of http://www.podcast411.com/
 * Updated by Aaron Snoswell (aaronsnoswell@gmail.com)
 * Further updated for PHP 8.4 compatibility
 *
 * Recurses a given directory, reading MP3 ID3 tags and generating an itunes
 * compatible RSS podcast feed.
 *
 * Save this .php file wherever you like on your server. The URL for this .php
 * file /is/ the URL of your podcast feed for subscription purposes.
 */

// Configuration variables
$files_dir = getcwd() . '/';
$protocol = empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http://' : 'https://';
$base_url = $protocol . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']);
$files_url = $base_url . '/';
$getid3_dir = "getid3/";

// Feed options
$feed_title = "Nexus Church Sermon Podcasts";
$feed_link = "http://nexuschurch.com.au/sermon-audio";
$feed_description = "A selection of messages from Nexus Church in Brisbane, Australia. For more information, check out http://nexuschurch.com.au.";
$feed_copyright = "All content &#0169; Nexus Church " . date("Y");
$feed_ttl = 60 * 60 * 24;
$feed_lang = "en-au";
$feed_author = "Nexus Church Australia";
$feed_email = "info@nexuschurch.com";
$feed_image = "http://nexuschurch.com.au/sites/all/files/sermon_audio/itunes_logo.png";
$feed_explicit = "clean";
$feed_category = "Religion &amp; Spirituality";
$feed_subcategory = "Christianity";

// Initialize getID3 if available
$getid3_engine = null;
if (strlen($getid3_dir) !== 0 && file_exists($getid3_dir . 'getid3.php')) {
    require_once($getid3_dir . 'getid3.php');
    $getid3_engine = new getID3;
}

// Output XML
echo '<?xml version="1.0" encoding="utf-8" ?>';
?>
<!-- generator="awesome-sauce/1.1" -->
<rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" version="2.0">
    <channel>
        <title><?= htmlspecialchars($feed_title) ?></title>
        <link><?= htmlspecialchars($feed_link) ?></link>
        
        <!-- iTunes-specific metadata -->
        <itunes:author><?= htmlspecialchars($feed_author) ?></itunes:author>
        <itunes:owner>
            <itunes:name><?= htmlspecialchars($feed_author) ?></itunes:name>
            <itunes:email><?= htmlspecialchars($feed_email) ?></itunes:email>
        </itunes:owner>
        <itunes:image href="<?= htmlspecialchars($feed_image) ?>" />
        <itunes:explicit><?= htmlspecialchars($feed_explicit) ?></itunes:explicit>
        <itunes:category text="<?= htmlspecialchars($feed_category) ?>">
            <itunes:category text="<?= htmlspecialchars($feed_subcategory) ?>" />
        </itunes:category>
        <itunes:summary><?= htmlspecialchars($feed_description) ?></itunes:summary>
        
        <!-- Non-iTunes metadata -->
        <category>Music</category>
        <description><?= htmlspecialchars($feed_description) ?></description>
        <language><?= htmlspecialchars($feed_lang) ?></language>
        <copyright><?= $feed_copyright ?></copyright>
        <ttl><?= $feed_ttl ?></ttl>
        
        <!-- File listings -->
        <?php
        $directory = opendir($files_dir) or die('Unable to open directory');
        
        while (($file = readdir($directory)) !== false) {
            $file_path = $files_dir . $file;
            
            if (is_file($file_path) && str_ends_with($file_path, '.mp3')) {
                $file_title = $file;
                $file_url = $files_url . $file;
                $file_author = $feed_author;
                $file_duration = "";
                $file_description = "";
                $file_date = date(DateTime::RFC2822, filemtime($file_path));
                $file_size = filesize($file_path);
                
                if ($getid3_engine !== null) {
                    $id3_info = $getid3_engine->analyze($file_path);
                    getid3_lib::CopyTagsToComments($id3_info);
                    
                    $file_title = $id3_info["comments_html"]["title"][0] ?? $file_title;
                    $file_author = $id3_info["comments_html"]["artist"][0] ?? $file_author;
                    $file_duration = $id3_info["playtime_string"] ?? "";
                }
        ?>
        <item>
            <title><?= htmlspecialchars($file_title) ?></title>
            <link><?= htmlspecialchars($file_url) ?></link>
            <itunes:author><?= htmlspecialchars($file_author) ?></itunes:author>
            <itunes:category text="<?= htmlspecialchars($feed_category) ?>">
                <itunes:category text="<?= htmlspecialchars($feed_subcategory) ?>" />
            </itunes:category>
            <category>Music</category>
            <duration><?= htmlspecialchars($file_duration) ?></duration>
            <description><?= htmlspecialchars($file_description) ?></description>
            <pubDate><?= $file_date ?></pubDate>
            <enclosure url="<?= htmlspecialchars($file_url) ?>" length="<?= $file_size ?>" type="audio/mpeg" />
            <guid><?= htmlspecialchars($file_url) ?></guid>
            <author><?= htmlspecialchars($feed_email) ?></author>
        </item>
        <?php
            }
        }
        
        closedir($directory);
        ?>
    </channel>
</rss>
