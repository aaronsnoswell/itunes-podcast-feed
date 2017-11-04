<?php 
header('Content-Type: application/xml; charset=utf-8');

/**
 * iTunes-Compatible RSS 2.0 MP3 subscription feed script
 * Original work by Rob W of http://www.podcast411.com/
 * Updated by Aaron Snoswell (aaronsnoswell@gmail.com)
 *
 * Recurses a given directory, reading MP3 ID3 tags and generating an itunes
 * compatible RSS podcast feed.
 *
 * Save this .php file wherever you like on your server. The URL for this .php
 * file /is/ the URL of your podcast feed for subscription purposes.
 */

/*
 * CONFIGURATION VARIABLES:
 * For more info on these settings, see the instructions at
 *
 * http://www.apple.com/itunes/podcasts/specs.html
 *
 * and the RSS 2.0 spec at
 *
 * http://www.rssboard.org/rss-specification
 */


// ============================================ General Configuration Options

// Location of MP3's on server. TRAILING SLASH REQ'D.
//$files_dir = "/var/www/vhosts/nexuschurch.com.au/httpdocs/sites/all/files/sermon_audio/";
$files_dir = getcwd().'/';

// Corresponding url for accessing the above directory. TRAILING SLASH REQ'D.
//$files_url = "http://nexuschurch.com.au/sites/all/files/sermon_audio/";
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    $protocol = 'http://';
} else {
    $protocol = 'https://';
}
$base_url = $protocol . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']);
$files_url = $base_url.'/';

// Location of getid3 folder, leave blank to disable. TRAILING SLASH REQ'D.
$getid3_dir = "getid3/";

// ====================================================== Generic feed options

// Your feed's title
$feed_title = "Nexus Church Sermon Podcasts";

// 'More info' link for your feed
$feed_link = "http://nexuschurch.com.au/sermon-audio";

// Brief description
$feed_description = "A selection of messages from Nexus Church in Brisbane, Australia. For more information, check out http://nexuschurch.com.au.";

// Copyright / license information
$feed_copyright = "All content &#0169; Nexus Church " . date("Y");

// How often feed readers check for new material (in seconds) -- mostly ignored by readers
$feed_ttl = 60 * 60 * 24;

// Language locale of your feed, eg en-us, de, fr etc. See http://www.rssboard.org/rss-language-codes
$feed_lang = "en-au";


// ============================================== iTunes-specific feed options

// You, or your organisation's name
$feed_author = "Nexus Church Australia";

// Feed author's contact email address
$feed_email="info@nexuschurch.com";

// Url of a 170x170 .png image to be used on the iTunes page
$feed_image = "http://nexuschurch.com.au/sites/all/files/sermon_audio/itunes_logo.png";

// If your feed contains explicit material or not (yes, no, clean)
$feed_explicit = "clean";

// iTunes major category of your feed
$feed_category = "Religion &amp; Spirituality";

// iTunes minor category of your feed
$feed_subcategory = "Christianity";


// END OF CONFIGURATION VARIABLES

// If getid3 was requested, attempt to initialise the ID3 engine
$getid3_engine = NULL;
if(strlen($getid3_dir) != 0) {
    require_once($getid3_dir . 'getid3.php');
    $getid3_engine = new getID3;
}

// Write XML heading
echo '<?xml version="1.0" encoding="utf-8" ?>';

?>
<!-- generator="awesome-sauce/1.1" -->
<rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" version="2.0">

    <channel>
        <title><? echo $feed_title; ?></title>
        <link><? echo $feed_link; ?></link>

        <!-- iTunes-specific metadata -->
        <itunes:author><? echo $feed_author; ?></itunes:author>
        <itunes:owner>
            <itunes:name><? echo $feed_author; ?></itunes:name>
            <itunes:email><? echo $feed_email; ?></itunes:email>
        </itunes:owner>

        <itunes:image href="<? echo $feed_image; ?>" />
        <itunes:explicit><? echo $feed_explicit; ?></itunes:explicit>
        <itunes:category text="<? echo $feed_category; ?>">
            <itunes:category text="<? echo $feed_subcategory; ?>" />
        </itunes:category>

        <itunes:summary><? echo $feed_description; ?></itunes:summary>

        <!-- Non-iTunes metadata -->
        <category>Music</category>
        <description><? echo $feed_description; ?></description>
        
        <language><? echo $feed_lang; ?></language>
        <copyright><? echo $feed_copyright; ?></copyright>
        <ttl><? echo $feed_ttl; ?></ttl>

        <!-- The file listings -->
        <?php
        $directory = opendir($files_dir) or die($php_errormsg);

        // Step through file directory
        while(false !== ($file = readdir($directory))) {
            $file_path = $files_dir . $file;

            // not . or .., ends in .mp3
            if(is_file($file_path) && strrchr($file_path, '.') == ".mp3") {
                // Initialise file details to sensible defaults
                $file_title = $file;
                $file_url = $files_url . $file;
                $file_author = $feed_author;
                $file_duration = "";
                $file_description = "";
                $file_date = date(DateTime::RFC2822, filemtime($file_path));
                $file_size = filesize($file_path);

                // Read file metadata from the ID3 tags
                if(!is_null($getid3_engine)) {
                    $id3_info = $getid3_engine->analyze($file_path);
                    getid3_lib::CopyTagsToComments($id3_info);
                    
                    $file_title = $id3_info["comments_html"]["title"][0];
                    $file_author = $id3_info["comments_html"]["artist"][0];
                    $file_duration = $id3_info["playtime_string"];
                }
        ?>

        <item>
            <title><? echo $file_title; ?></title>
            <link><? echo $file_url; ?></link>
            
            <itunes:author><? echo $file_author; ?></itunes:author>
            <itunes:category text="<? echo $feed_category; ?>">
                <itunes:category text="<? echo $feed_subcategory; ?>" />
            </itunes:category>

            <category>Music</category>
            <duration><? echo $file_duration; ?></duration>
            
            <description><? echo $file_description; ?></description>
            <pubDate><? echo $file_date; ?></pubDate>

            <enclosure url="<? echo $file_url; ?>" length="<? echo $file_size; ?>" type="audio/mpeg" />
            <guid><? echo $file_url; ?></guid>
            <author><? echo $feed_email; ?></author>
        </item>
        <?
            }
        }

        closedir($files_dir);

        ?>

    </channel>
</rss>

